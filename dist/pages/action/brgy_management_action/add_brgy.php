<?php

include '../../../../database/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// Sanitize and validate input
	$barangay_name = trim($_POST['barangay_name'] ?? '');
	$captain_name = trim($_POST['barangay_captain_name'] ?? '');
	$latitude = floatval($_POST['latitude'] ?? 0);
	$longitude = floatval($_POST['longitude'] ?? 0);
	$signature_option = $_POST['signature_option'] ?? 'draw';

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

	// Insert into database
	$stmt = $conn->prepare("INSERT INTO barangay_manegement_table (barangay_name, barangay_captain_name, latitude, longitude, signature_brgy_captain) VALUES (?, ?, ?, ?, ?)");
	$stmt->bind_param("ssdds", $barangay_name, $captain_name, $latitude, $longitude, $signature_path);

	if ($stmt->execute()) {
		$_SESSION['success'] = "<span style='color:green;'><i class='bi bi-check-circle-fill'></i> Add Barangay Successfully!</span>";
	} else {
		$_SESSION['error'] = "<span style='color:red;'><i class='bi bi-exclamation-circle-fill'></i> Failed to Add Barangay.</span>";
	}

	$stmt->close();
	$conn->close();

	header("Location: ../../admin_page/barangay_management.php");
	exit();
} else {
	echo "Invalid request.";
}
