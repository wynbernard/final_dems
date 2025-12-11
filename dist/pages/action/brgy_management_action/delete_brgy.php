<?php

include '../../../../database/session.php';
require_once '../../../../database/csrf.php';

// Validate CSRF token
csrf_validate_or_die();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// Sanitize and validate input
	$id = isset($_POST['barangay_id']) ? intval($_POST['barangay_id']) : 0;

	if ($id > 0) {
		// Fetch signature image path before deleting
		$stmt_img = $conn->prepare("SELECT signature_brgy_captain FROM barangay_manegement_table WHERE barangay_id = ?");
		$stmt_img->bind_param("i", $id);
		$stmt_img->execute();
		$stmt_img->bind_result($signaturePath);
		$stmt_img->fetch();
		$stmt_img->close();

		// Delete the barangay record
		$stmt = $conn->prepare("DELETE FROM barangay_manegement_table WHERE barangay_id = ?");
		$stmt->bind_param("i", $id);

		if ($stmt->execute()) {
			// Delete the signature image file if it exists and is not empty
			if (!empty($signaturePath)) {
				// Remove leading slashes and 'uploads/' if present
				$cleanPath = ltrim($signaturePath, '/');
				if (strpos($cleanPath, 'uploads/') === 0) {
					$cleanPath = substr($cleanPath, strlen('uploads/'));
				}
				$signatureFullPath = '../../../uploads/' . $cleanPath;
				if (file_exists($signatureFullPath)) {
					unlink($signatureFullPath);
				}
			}
			$_SESSION['success'] = "<span style='color:mint;'><i class='bi bi-check-circle-fill'></i> Delete Barangay Successfully!</span>";
		} else {
			error_log("Failed to delete barangay: " . $stmt->error);
			$_SESSION['error'] = "<span style='color:mint;'><i class='bi bi-exclamation-circle-fill'></i> Failed to Delete Barangay. Please try again.</span>";
		}

		$stmt->close();
	} else {
		$_SESSION['error'] = "<span style='color:mint;'><i class='bi bi-exclamation-circle-fill'></i> Invalid ID provided.</span>";
	}
} else {
	$_SESSION['error'] = "<span style='color:mint;'><i class='bi bi-exclamation-circle-fill'></i> Invalid request method.</span>";
}

header("Location: ../../admin_page/barangay_management.php");
exit();
