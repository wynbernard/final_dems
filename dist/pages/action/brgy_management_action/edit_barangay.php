<?php
include '../../../../database/session.php';

// CSRF Protection
require_once '../../../../database/csrf.php';
csrf_validate_or_die();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$barangay_id = $_POST['barangay_id'];
	$barangay_name = trim($_POST['barangay_name']);
	$captain_name = trim($_POST['barangay_captain_name']);
	$total_population = intval($_POST['total_population'] ?? 0);
	
	// Handle multiple disaster-prone types (only for JSON file)
	$disaster_prone_types = [];
	if (isset($_POST['disaster_prone_type']) && is_array($_POST['disaster_prone_type'])) {
		$disaster_prone_types = array_filter(array_map('trim', $_POST['disaster_prone_type']));
		// Remove "None" if other types are selected
		if (count($disaster_prone_types) > 1 && in_array('None', $disaster_prone_types)) {
			$disaster_prone_types = array_filter($disaster_prone_types, function($v) { return $v !== 'None'; });
		}
	}
	
	$latitude = $_POST['latitude'];
	$longitude = $_POST['longitude'];
	$current_signature = $_POST['current_signature'];
	$boundary_json = $_POST['boundary_json'] ?? '';

	$new_signature_path = $current_signature;

	if (isset($_FILES['signature_file']) && $_FILES['signature_file']['error'] === UPLOAD_ERR_OK) {
		$file_tmp = $_FILES['signature_file']['tmp_name'];
		$file_name = basename($_FILES['signature_file']['name']);
		$upload_dir = '../../signature_brgy_captain/';

		if (!file_exists($upload_dir)) {
			mkdir($upload_dir, 0777, true);
		}

		$file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
		$new_file_name = 'signature_brgy_captain' . uniqid() . '.' . $file_ext;
		$target_path = $upload_dir . $new_file_name;

		if (move_uploaded_file($file_tmp, $target_path)) {
			$new_signature_path = 'signature_brgy_captain/' . $new_file_name;

			// Delete previous signature file if it's different
			$old_signature_path = '../../' . $current_signature;
			if (file_exists($old_signature_path) && is_file($old_signature_path)) {
				unlink($old_signature_path);
			}
		} else {
			error_log("Failed to upload signature in edit_barangay.php");
			$_SESSION['error'] = "<span style='color:red;'><i class='bi bi-exclamation-circle-fill'></i> Failed to upload signature.</span>";
			header("Location: ../../admin_page/barangay_management.php");
			exit();
		}
	}

	$stmt = $conn->prepare("UPDATE barangay_manegement_table SET barangay_name=?, barangay_captain_name=?, latitude=?, longitude=?, signature_brgy_captain=?, total_population=? WHERE barangay_id=?");
	$stmt->bind_param("ssddsis", $barangay_name, $captain_name, $latitude, $longitude, $new_signature_path, $total_population, $barangay_id);

	if ($stmt->execute()) {
		// Handle boundary coordinates
		$boundaryFile = dirname(__DIR__, 4) . '/address_json/barangay_boundaries.json';
		$boundaries = [];
		
		// Create directory if it doesn't exist
		$boundaryDir = dirname($boundaryFile);
		if (!is_dir($boundaryDir)) {
			mkdir($boundaryDir, 0755, true);
		}
		
		// Load existing boundaries
		if (file_exists($boundaryFile)) {
			$existingData = @file_get_contents($boundaryFile);
			if ($existingData !== false) {
				$decoded = json_decode($existingData, true);
				if (is_array($decoded)) {
					$boundaries = $decoded;
				}
			}
		}
		
		if (!empty($boundary_json)) {
			// Parse and validate boundary coordinates
			$coords = json_decode($boundary_json, true);
			if (is_array($coords) && count($coords) >= 2) {
				$validCoords = [];
				foreach ($coords as $coord) {
					if (is_array($coord) && count($coord) >= 2) {
						$lat = filter_var($coord[0], FILTER_VALIDATE_FLOAT);
						$lng = filter_var($coord[1], FILTER_VALIDATE_FLOAT);
						if ($lat !== false && $lng !== false) {
							$validCoords[] = ['lat' => round($lat, 6), 'lng' => round($lng, 6)];
						}
					}
				}
				
				if (count($validCoords) >= 2) {
					$boundaries[$barangay_name] = [
						'type' => count($validCoords) >= 3 ? 'polygon' : 'polyline',
						'coordinates' => $validCoords,
						'disaster_prone_types' => $disaster_prone_types
					];
				}
			} else {
				// Even without boundary, save disaster-prone types if provided
				if (!empty($disaster_prone_types)) {
					if (!isset($boundaries[$barangay_name])) {
						$boundaries[$barangay_name] = [];
					}
					$boundaries[$barangay_name]['disaster_prone_types'] = $disaster_prone_types;
				}
			}
		} else {
			// If boundary_json is empty, preserve existing boundary but update disaster-prone types
			if (isset($boundaries[$barangay_name])) {
				// Preserve existing boundary data (coordinates, type) and update disaster-prone types
				if (!empty($disaster_prone_types)) {
					$boundaries[$barangay_name]['disaster_prone_types'] = $disaster_prone_types;
				} else {
					// If no disaster-prone types provided, remove that field but keep boundary
					if (isset($boundaries[$barangay_name]['disaster_prone_types'])) {
						unset($boundaries[$barangay_name]['disaster_prone_types']);
					}
				}
			} else {
				// No existing entry, create one if we have disaster-prone types
				if (!empty($disaster_prone_types)) {
					$boundaries[$barangay_name] = [
						'disaster_prone_types' => $disaster_prone_types
					];
				}
			}
		}
		
		// Save updated boundaries to file
		$encoded = json_encode($boundaries, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
		if ($encoded !== false) {
			$writeResult = file_put_contents($boundaryFile, $encoded);
			if ($writeResult === false) {
				error_log("Failed to write boundary file: " . $boundaryFile);
			}
		}
		
		$_SESSION['success'] = "<span style='color:mint;'><i class='bi bi-check-circle-fill'></i> Edit Barangay Successfully!</span>";
	} else {
		$_SESSION['error'] = "<span style='color:mint;'><i class='bi bi-exclamation-circle-fill'></i> Failed to Edit Barangay.</span>";
	}

	header("Location: ../../admin_page/barangay_management.php");
	exit();
} else {
	$_SESSION['error'] = "<span style='color:red;'><i class='bi bi-exclamation-circle-fill'></i> Invalid request method.</span>";
	header("Location: ../../admin_page/barangay_management.php");
	exit();
}
