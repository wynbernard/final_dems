<?php
include '../../../../database/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$barangay_id = $_POST['barangay_id'];
	$barangay_name = trim($_POST['barangay_name']);
	$captain_name = trim($_POST['barangay_captain_name']);
	$latitude = $_POST['latitude'];
	$longitude = $_POST['longitude'];
	$current_signature = $_POST['current_signature'];

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
			die("Failed to upload signature.");
		}
	}

	$stmt = $conn->prepare("UPDATE barangay_manegement_table SET barangay_name=?, barangay_captain_name=?, latitude=?, longitude=?, signature_brgy_captain=? WHERE barangay_id=?");
	$stmt->bind_param("ssddsi", $barangay_name, $captain_name, $latitude, $longitude, $new_signature_path, $barangay_id);

	if ($stmt->execute()) {
		$_SESSION['success'] = "<span style='color:mint;'><i class='bi bi-check-circle-fill'></i> Edit Barangay Successfully!</span>";
	} else {
		$_SESSION['error'] = "<span style='color:mint;'><i class='bi bi-exclamation-circle-fill'></i> Failed to Edit Barangay.</span>";
	}

	header("Location: ../../admin_page/barangay_management.php");
	exit();
} else {
	echo "Invalid request.";
}
