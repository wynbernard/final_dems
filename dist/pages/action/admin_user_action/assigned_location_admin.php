<?php
include '../../../../database/session.php'; // DB connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$admin_id = isset($_POST['admin_id']) ? intval($_POST['admin_id']) : 0;
	$evac_loc_id = isset($_POST['evac_loc_id']) ? intval($_POST['evac_loc_id']) : 0;

	if ($admin_id > 0 && $evac_loc_id > 0) {
		$stmt = $conn->prepare("UPDATE admin_table SET evac_loc_id = ? WHERE admin_id = ?");
		$stmt->bind_param("ii", $evac_loc_id, $admin_id);

		if ($stmt->execute()) {
			$_SESSION['success'] = "<span style='color:green;'><i class='bi bi-check-circle-fill'></i> Location assigned Successfull.</span>";
		} else {
			$_SESSION['error'] = "<span style='color:red;'><i class='bi bi-exclamation-circle-fill'></i> Failed to assign location: " . $stmt->error . "</span>";
		}

		$stmt->close();
	} else {
		$_SESSION['error'] = "<span style='color:red;'><i class='bi bi-exclamation-circle-fill'></i> Invalid admin or location selection.</span>";
	}

	$conn->close();
	header("Location: ../../admin_page/admin_user.php");
	exit();
} else {
	$_SESSION['error'] = "<span style='color:red;'><i class='bi bi-exclamation-circle-fill'></i> Invalid request method.</span>";
	header("Location: ../../admin_page/admin_user.php");
	exit();
}
