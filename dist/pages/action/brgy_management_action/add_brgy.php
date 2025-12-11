<?php

include '../../../../database/session.php';

// CSRF Protection
require_once '../../../../database/csrf.php';
csrf_validate_or_die();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// Sanitize and validate input
	$barangay_name = trim($_POST['barangay_name'] ?? '');
	$captain_name = trim($_POST['barangay_captain_name'] ?? '');
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
	
	$latitude = floatval($_POST['latitude'] ?? 0);
	$longitude = floatval($_POST['longitude'] ?? 0);
	$signature_option = $_POST['signature_option'] ?? 'draw';
	$boundary_json = $_POST['boundary_json'] ?? '';

	$signature_path = '';
	$upload_dir = '../../../../uploads/signature_brgy_captain/';

	// Ensure upload directory exists
	if (!file_exists($upload_dir)) {
		mkdir($upload_dir, 0777, true);
	}

	// Handle Signature Input
	if ($signature_option === 'draw') {
		$signature_data = $_POST['signature_data'] ?? '';

		if (!empty($signature_data)) {
			$signature_data = str_replace('data:image/png;base64,', '', $signature_data);
			$signature_data = str_replace(' ', '+', $signature_data);
			$decoded_signature = base64_decode($signature_data);

			$file_name = 'signature_' . time() . '.png';
			$file_path = $upload_dir . $file_name;

			if (file_put_contents($file_path, $decoded_signature)) {
				$signature_path = 'signature_brgy_captain/' . $file_name;
			} else {
				$_SESSION['error'] = "Failed to save drawn signature.";
				header("Location: ../../admin_page/barangay_management.php");
				exit();
			}
		}
	} elseif ($signature_option === 'upload' && isset($_FILES['signature_file'])) {
		if ($_FILES['signature_file']['error'] === UPLOAD_ERR_OK) {
			$original_name = basename($_FILES['signature_file']['name']);
			$file_name = 'signature_' . time() . '_' . $original_name;
			$target_file = $upload_dir . $file_name;

			if (move_uploaded_file($_FILES['signature_file']['tmp_name'], $target_file)) {
				$signature_path = 'signature_brgy_captain/' . $file_name;
			} else {
				$_SESSION['error'] = "Failed to move uploaded signature file.";
				header("Location: ../../admin_page/barangay_management.php");
				exit();
			}
		} else {
			$_SESSION['error'] = "Upload error code: " . $_FILES['signature_file']['error'];
			header("Location: ../../admin_page/barangay_management.php");
			exit();
		}
	}

	// Insert into database (without disaster_prone_type - saved only in JSON)
	$stmt = $conn->prepare("INSERT INTO barangay_manegement_table (barangay_name, barangay_captain_name, latitude, longitude, signature_brgy_captain, total_population) VALUES (?, ?, ?, ?, ?, ?)");
	$stmt->bind_param("ssddsi", $barangay_name, $captain_name, $latitude, $longitude, $signature_path, $total_population);

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
			// If boundary_json is empty, save disaster-prone types if provided
			if (!empty($disaster_prone_types)) {
				if (!isset($boundaries[$barangay_name])) {
					$boundaries[$barangay_name] = [];
				}
				$boundaries[$barangay_name]['disaster_prone_types'] = $disaster_prone_types;
			}
			// Note: We don't remove existing entries here for add operation
		}
		
		// Save updated boundaries to file
		$encoded = json_encode($boundaries, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
		if ($encoded !== false) {
			$writeResult = file_put_contents($boundaryFile, $encoded);
			if ($writeResult === false) {
				error_log("Failed to write boundary file: " . $boundaryFile);
			}
		}
		
		$_SESSION['success'] = "<span style='color:green;'><i class='bi bi-check-circle-fill'></i> Add Barangay Successfully!</span>";
	} else {
		$_SESSION['error'] = "<span style='color:red;'><i class='bi bi-exclamation-circle-fill'></i> Failed to Add Barangay.</span>";
	}

	$stmt->close();
	$conn->close();

	header("Location: ../../admin_page/barangay_management.php");
	exit();
} else {
	$_SESSION['error'] = "<span style='color:red;'><i class='bi bi-exclamation-circle-fill'></i> Invalid request method.</span>";
	header("Location: ../../admin_page/barangay_management.php");
	exit();
}
