<?php
include '../../../database/session.php';
include '../../../database/conn.php';

// Ensure PHPMailer autoload is available (same pattern as single update endpoint)
require '../../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// helper haversine (guard against redeclare if this file is included multiple times)
if (!function_exists('_haversine_local')) {
	function _haversine_local($lat1, $lng1, $lat2, $lng2)
	{
		$R = 6371;
		$dLat = deg2rad($lat2 - $lat1);
		$dLon = deg2rad($lng2 - $lng1);
		$a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lng1)) * sin($dLon / 2) * sin($dLon / 2);
		$c = 2 * atan2(sqrt($a), sqrt(1 - $a));
		return $R * $c;
	}
}

// Helper to retrieve user's coordinates, preferring family/solo and falling back to barangay
if (!function_exists('get_user_coords')) {
	function get_user_coords($userId, $registeredAs, $familyId, $soloAddrId, $conn)
	{
		$lat = null;
		$lng = null;

		if (strtolower($registeredAs ?? 'solo') === 'family' && intval($familyId) > 0) {
			$fstmt = $conn->prepare("SELECT latitude, longitude FROM family_table WHERE family_id = ? LIMIT 1");
			$fstmt->bind_param('i', $familyId);
			$fstmt->execute();
			$fres = $fstmt->get_result();
			if ($frow = $fres->fetch_assoc()) {
				if (!empty($frow['latitude']) && !empty($frow['longitude'])) {
					$lat = floatval($frow['latitude']);
					$lng = floatval($frow['longitude']);
				}
			}
			$fstmt->close();
		} else {
			if (intval($soloAddrId) > 0) {
				$sstmt = $conn->prepare("SELECT latitude, longitude FROM solo_address_table WHERE solo_address_id = ? LIMIT 1");
				$sstmt->bind_param('i', $soloAddrId);
				$sstmt->execute();
				$sres = $sstmt->get_result();
				if ($srow = $sres->fetch_assoc()) {
					if (!empty($srow['latitude']) && !empty($srow['longitude'])) {
						$lat = floatval($srow['latitude']);
						$lng = floatval($srow['longitude']);
					}
				}
				$sstmt->close();
			}
		}

		if ($lat === null || $lng === null) {
			$bstmt = $conn->prepare("SELECT br.latitude AS b_lat, br.longitude AS b_lng FROM pre_reg_table pr LEFT JOIN solo_address_table sat ON pr.solo_address_id = sat.solo_address_id LEFT JOIN family_table ft ON pr.family_id = ft.family_id LEFT JOIN barangay_manegement_table br ON COALESCE(sat.barangay_id, ft.barangay_id) = br.barangay_id WHERE pr.pre_reg_id = ? LIMIT 1");
			$bstmt->bind_param('i', $userId);
			$bstmt->execute();
			$bres = $bstmt->get_result();
			if ($brow = $bres->fetch_assoc()) {
				if (!empty($brow['b_lat']) && !empty($brow['b_lng'])) {
					$lat = floatval($brow['b_lat']);
					$lng = floatval($brow['b_lng']);
				}
			}
			$bstmt->close();
		}

		return ['lat' => $lat, 'lng' => $lng];
	}
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	echo 'invalid';
	exit;
}

$ids_raw = isset($_POST['ids']) ? trim($_POST['ids']) : '';
$status = isset($_POST['status']) ? trim($_POST['status']) : '';

if ($ids_raw === '' || $status === '') {
	echo 'missing';
	exit;
}

$ids = array_filter(array_map('intval', explode(',', $ids_raw)));
if (empty($ids)) {
	echo 'no_ids';
	exit;
}

// Prepare update
$stmt = $conn->prepare("UPDATE evac_loc_table SET status = ? WHERE evac_loc_id = ?");
if (!$stmt) {
	echo 'prepare_error';
	exit;
}

foreach ($ids as $id) {
	$stmt->bind_param('si', $status, $id);
	if ($stmt->execute()) {
		// If we activated this center, perform capacity check and notify nearby residents
		if ($status === 'Active') {
			// fetch center details
			$cstmt = $conn->prepare("SELECT evac_loc_id, latitude, longitude, total_capacity FROM evac_loc_table WHERE evac_loc_id = ? LIMIT 1");
			$cstmt->bind_param('i', $id);
			$cstmt->execute();
			$cres = $cstmt->get_result();
			$center = $cres ? $cres->fetch_assoc() : null;
			$cstmt->close();

			if ($center) {
				// count current recommended assigned to this center
				$rstmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM pre_reg_table WHERE recommended_location = ?");
				$rstmt->bind_param('i', $id);
				$rstmt->execute();
				$rres = $rstmt->get_result();
				$rc = ($rres && $rrow = $rres->fetch_assoc()) ? intval($rrow['cnt']) : 0;
				$rstmt->close();

				$capacity = max(1, intval($center['total_capacity']));
				$available = max(0, $capacity - $rc);
				// thresholds similar to single update: nearby radius and near-full fraction
				$NEARBY_RADIUS_KM = 5.0;
				$NEARLY_FULL_THRESHOLD = 0.10;

				if ($available > 0 && ($available / $capacity) > $NEARLY_FULL_THRESHOLD) {
					// assign nearby unassigned users within radius up to $available
					// use guarded helper _haversine_local declared at top

					$uSql = "SELECT pre_reg_id, f_name, l_name, registered_as, family_id, solo_address_id FROM pre_reg_table WHERE COALESCE(recommended_location, 0) = 0";
					$uStmt = $conn->prepare($uSql);
					$uStmt->execute();
					$uRes = $uStmt->get_result();

					$assigned = 0;
					while ($uRow = $uRes->fetch_assoc()) {
						if ($assigned >= $available) break;
						$userId = intval($uRow['pre_reg_id']);
						$registeredAs = strtolower($uRow['registered_as'] ?? 'solo');
						$familyId = intval($uRow['family_id']);
						$soloAddrId = intval($uRow['solo_address_id']);

						// get user's coordinates with barangay fallback
						$coords = get_user_coords($userId, $registeredAs, $familyId, $soloAddrId, $conn);
						$userLat = $coords['lat'];
						$userLng = $coords['lng'];

						if ($userLat === null || $userLng === null) continue;

						$d = _haversine_local($userLat, $userLng, floatval($center['latitude']), floatval($center['longitude']));
						if ($d <= $NEARBY_RADIUS_KM) {
							// assign
							if ($registeredAs === 'family' && $familyId > 0) {
								$up = $conn->prepare("UPDATE pre_reg_table SET recommended_location = ? WHERE family_id = ?");
								$up->bind_param('ii', $id, $familyId);
								$up->execute();
								$up->close();
							} else {
								$up = $conn->prepare("UPDATE pre_reg_table SET recommended_location = ? WHERE pre_reg_id = ?");
								$up->bind_param('ii', $id, $userId);
								$up->execute();
								$up->close();
							}
							$assigned++;
						}
					}
					$uStmt->close();

					// send emails to assigned users for this center
					$q = $conn->prepare("SELECT pre_reg_id, f_name, l_name, email_address FROM pre_reg_table WHERE recommended_location = ?");
					$q->bind_param('i', $id);
					$q->execute();
					$res = $q->get_result();
					if ($res && $res->num_rows > 0) {
						// fetch location details for email
						$locStmt = $conn->prepare("SELECT e.name, e.city, e.purok, e.latitude, e.longitude, b.barangay_name FROM evac_loc_table e LEFT JOIN barangay_manegement_table b ON e.barangay_id = b.barangay_id WHERE e.evac_loc_id = ? LIMIT 1");
						$locStmt->bind_param('i', $id);
						$locStmt->execute();
						$locRes = $locStmt->get_result();
						$loc = $locRes ? $locRes->fetch_assoc() : null;
						$locStmt->close();

						$locName = $loc['name'] ?? 'Evacuation Center';
						$locCity = $loc['city'] ?? '';
						$locBarangay = $loc['barangay_name'] ?? '';
						$locPurok = $loc['purok'] ?? '';
						$locLat = $loc['latitude'] ?? '';
						$locLng = $loc['longitude'] ?? '';

						$mapsLink = '';
						if ($locLat !== '' && $locLng !== '') {
							$mapsLink = "https://www.google.com/maps/search/?api=1&query=" . urlencode($locLat . ',' . $locLng);
						}

						while ($row = $res->fetch_assoc()) {
							$toEmail = $row['email_address'];
							$toName = trim($row['f_name'] . ' ' . $row['l_name']);
							$bodyHtml = "<p>Dear " . htmlspecialchars($toName) . ",</p>";
							$bodyHtml .= "<p>The evacuation center recommended for you is now <strong>Active</strong>:</p>";
							$bodyHtml .= "<p><strong>" . htmlspecialchars($locName) . "</strong><br>";
							$bodyHtml .= htmlspecialchars($locCity) . "<br>Barangay: " . htmlspecialchars($locBarangay) . "<br>Purok: " . htmlspecialchars($locPurok) . "</p>";
							if ($mapsLink) {
								$bodyHtml .= "<p><a href=\"" . $mapsLink . "\" target=\"_blank\">Open location in Google Maps</a></p>";
							}
							$bodyHtml .= "<p>Please check your account for more details and proceed to the recommended location if necessary.</p>";
							$bodyHtml .= "<p>Thank you,<br>DEMS Team</p>";

							$bodyText = "Dear {$toName},\nThe evacuation center recommended for you is now ACTIVE:\n";
							$bodyText .= "{$locName} - {$locCity} - Barangay: {$locBarangay} - Purok: {$locPurok}\n";
							if ($mapsLink) $bodyText .= "Maps: {$mapsLink}\n";

							try {
								$mail = new PHPMailer(true);
								$mail->isSMTP();
								$mail->Host       = 'smtp.hostinger.com';
								$mail->SMTPAuth   = true;
								$mail->Username   = 'dems_info@bccbsis.com';
								$mail->Password   = '[nAgc/#^Jj7';
								$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
								$mail->Port       = 465;

								$mail->setFrom('dems_info@bccbsis.com', 'DEMS Notification');
								$mail->addAddress($toEmail, $toName);
								$mail->isHTML(true);
								$mail->Subject = 'Recommended Evacuation Center is now Active';
								$mail->Body = $bodyHtml;
								$mail->AltBody = $bodyText;
								$mail->send();
							} catch (Exception $e) {
								error_log('Mail error to ' . $toEmail . ': ' . $e->getMessage());
							}
						}
					}
					$q->close();
				} else {
					// near full or no available slots: skip emailing for this center
					// (Optional) could implement assigning to other centers here
				}
			}
		}
	} else {
		// log failure for this id
	}
}
$stmt->close();

echo 'success';
