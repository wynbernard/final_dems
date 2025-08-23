<?php
// update_location_status.php
include '../../../database/session.php';
require '../../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $evac_loc_id = intval($_POST['evac_loc_id'] ?? 0);
    $new_status = ($_POST['status'] === 'Active') ? 'Active' : 'Inactive';
    if ($evac_loc_id > 0) {
        $stmt = $conn->prepare("UPDATE evac_loc_table SET status = ? WHERE evac_loc_id = ?");
        $stmt->bind_param("si", $new_status, $evac_loc_id);
        if ($stmt->execute()) {
            // If status activated, notify recommended users by email
            if ($new_status === 'Active') {
                // First: assign nearest available active centers to users without recommended_location
                // Fetch active centers with current recommended counts
                $centers = [];
                $centerSql = "SELECT e.evac_loc_id, e.latitude, e.longitude, e.total_capacity, (
                    SELECT COUNT(*) FROM pre_reg_table pr WHERE pr.recommended_location = e.evac_loc_id
                ) AS total_recommended FROM evac_loc_table e WHERE e.status = 'Active'";
                $cStmt = $conn->prepare($centerSql);
                $cStmt->execute();
                $cRes = $cStmt->get_result();
                while ($cRow = $cRes->fetch_assoc()) {
                    $avail = intval($cRow['total_capacity']) - intval($cRow['total_recommended']);
                    if ($avail > 0) {
                        $centers[] = [
                            'id' => intval($cRow['evac_loc_id']),
                            'lat' => floatval($cRow['latitude']),
                            'lng' => floatval($cRow['longitude']),
                            'capacity' => intval($cRow['total_capacity']),
                            'recommended' => intval($cRow['total_recommended']),
                            'available' => $avail
                        ];
                    }
                }
                $cStmt->close();

                // If centers available, assign nearest to unassigned users
                if (!empty($centers)) {
                    // Fetch users without recommended_location (0 or NULL)
                    $uSql = "SELECT pre_reg_id, registered_as, family_id, solo_address_id FROM pre_reg_table WHERE COALESCE(recommended_location, 0) = 0";
                    $uStmt = $conn->prepare($uSql);
                    $uStmt->execute();
                    $uRes = $uStmt->get_result();

                    // helper: haversine
                    function _haversine($lat1, $lng1, $lat2, $lng2)
                    {
                        $R = 6371; // km
                        $dLat = deg2rad($lat2 - $lat1);
                        $dLon = deg2rad($lng2 - $lng1);
                        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
                        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
                        return $R * $c;
                    }

                    // Pre-cache barangay coords if needed
                    while ($uRow = $uRes->fetch_assoc()) {
                        $userId = intval($uRow['pre_reg_id']);
                        $registeredAs = strtolower($uRow['registered_as'] ?? 'solo');
                        $familyId = intval($uRow['family_id']);
                        $soloAddrId = intval($uRow['solo_address_id']);

                        // Get user's coordinates: prefer family/solo table lat/lng
                        $userLat = null;
                        $userLng = null;
                        if ($registeredAs === 'family' && $familyId > 0) {
                            $fstmt = $conn->prepare("SELECT latitude, longitude FROM family_table WHERE family_id = ? LIMIT 1");
                            $fstmt->bind_param('i', $familyId);
                            $fstmt->execute();
                            $fres = $fstmt->get_result();
                            if ($frow = $fres->fetch_assoc()) {
                                if (!empty($frow['latitude']) && !empty($frow['longitude'])) {
                                    $userLat = floatval($frow['latitude']);
                                    $userLng = floatval($frow['longitude']);
                                }
                            }
                            $fstmt->close();
                        } else {
                            if ($soloAddrId > 0) {
                                $sstmt = $conn->prepare("SELECT latitude, longitude FROM solo_address_table WHERE solo_address_id = ? LIMIT 1");
                                $sstmt->bind_param('i', $soloAddrId);
                                $sstmt->execute();
                                $sres = $sstmt->get_result();
                                if ($srow = $sres->fetch_assoc()) {
                                    if (!empty($srow['latitude']) && !empty($srow['longitude'])) {
                                        $userLat = floatval($srow['latitude']);
                                        $userLng = floatval($srow['longitude']);
                                    }
                                }
                                $sstmt->close();
                            }
                        }

                        // Fallback: if no lat/lng, try to get barangay coords from pre_reg joins
                        if ($userLat === null || $userLng === null) {
                            $bstmt = $conn->prepare("SELECT br.latitude AS b_lat, br.longitude AS b_lng FROM pre_reg_table pr LEFT JOIN solo_address_table sat ON pr.solo_address_id = sat.solo_address_id LEFT JOIN family_table ft ON pr.family_id = ft.family_id LEFT JOIN barangay_manegement_table br ON COALESCE(sat.barangay_id, ft.barangay_id) = br.barangay_id WHERE pr.pre_reg_id = ? LIMIT 1");
                            $bstmt->bind_param('i', $userId);
                            $bstmt->execute();
                            $bres = $bstmt->get_result();
                            if ($brow = $bres->fetch_assoc()) {
                                if (!empty($brow['b_lat']) && !empty($brow['b_lng'])) {
                                    $userLat = floatval($brow['b_lat']);
                                    $userLng = floatval($brow['b_lng']);
                                }
                            }
                            $bstmt->close();
                        }

                        if ($userLat === null || $userLng === null) {
                            // Cannot determine user location; skip assignment
                            continue;
                        }

                        // Find nearest center with available slot
                        $bestCenterId = null;
                        $bestDist = PHP_INT_MAX;
                        foreach ($centers as $ci => $center) {
                            if ($center['available'] <= 0) continue;
                            // If center has no coords, skip
                            if ($center['lat'] == 0 && $center['lng'] == 0) continue;
                            $d = _haversine($userLat, $userLng, $center['lat'], $center['lng']);
                            if ($d < $bestDist) {
                                $bestDist = $d;
                                $bestCenterId = $center['id'];
                                $bestCenterIdx = $ci;
                            }
                        }

                        if ($bestCenterId !== null) {
                            // Assign: for family, update all members by family_id; for solo, update single pre_reg
                            if ($registeredAs === 'family' && $familyId > 0) {
                                $up = $conn->prepare("UPDATE pre_reg_table SET recommended_location = ? WHERE family_id = ?");
                                $up->bind_param('ii', $bestCenterId, $familyId);
                                $up->execute();
                                $up->close();
                            } else {
                                $up = $conn->prepare("UPDATE pre_reg_table SET recommended_location = ? WHERE pre_reg_id = ?");
                                $up->bind_param('ii', $bestCenterId, $userId);
                                $up->execute();
                                $up->close();
                            }
                            // Decrement available count locally to avoid over-allocating in this run
                            if (isset($bestCenterIdx)) {
                                $centers[$bestCenterIdx]['available'] -= 1;
                            }
                        }
                    }
                    $uStmt->close();
                }

                // Fetch evac location details to include in email
                $locStmt = $conn->prepare("SELECT e.name, e.city, e.purok, e.latitude, e.longitude, b.barangay_name FROM evac_loc_table e LEFT JOIN barangay_manegement_table b ON e.barangay_id = b.barangay_id WHERE e.evac_loc_id = ? LIMIT 1");
                $locStmt->bind_param('i', $evac_loc_id);
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

                // Fetch users with recommended_location equal to this evac_loc_id
                $q = $conn->prepare("SELECT pre_reg_id, f_name, l_name, email_address FROM pre_reg_table WHERE recommended_location = ?");
                $q->bind_param('i', $evac_loc_id);
                $q->execute();
                $res = $q->get_result();
                if ($res && $res->num_rows > 0) {
                    while ($row = $res->fetch_assoc()) {
                        $toEmail = $row['email_address'];
                        $toName = trim($row['f_name'] . ' ' . $row['l_name']);
                        // Prepare email body with location details
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

                        // Send email using PHPMailer
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
                            // Log but don't fail the status update
                            error_log('Mail error to ' . $toEmail . ': ' . $e->getMessage());
                        }
                    }
                }
                $q->close();
            }
            echo 'success';
        } else {
            echo 'error';
        }
        $stmt->close();
    } else {
        echo 'invalid';
    }
} else {
    echo 'invalid';
}
