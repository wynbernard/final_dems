<?php

include '../../../../database/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$disaster_id = intval($_POST['disaster_id'] ?? 0);

	if ($disaster_id > 0) {
		$stmt = $conn->prepare("DELETE FROM disaster_table WHERE disaster_id = ?");

		if ($stmt) {
			$stmt->bind_param("i", $disaster_id);

			if ($stmt->execute()) {
				$_SESSION['success'] = "<span style='color: green;'><i class='bi bi-check-circle-fill'></i></span> Disaster deleted successfully!";
			} else {
				$_SESSION['error'] = "<span style='color:red;'><i class='bi bi-x-circle-fill'></i></span> Failed to delete disaster. " . $stmt->error;
			}

			$stmt->close();
		} else {
			$_SESSION['error'] = "<span style='color:red;'><i class='bi bi-x-circle-fill'></i></span> Failed to prepare the delete statement.";
		}
	} else {
		$_SESSION['error'] = "<span style='color:red;'><i class='bi bi-info-circle-fill'></i></span> Invalid disaster ID.";
	}

	header("Location: ../../admin_page/disaster.php");
	exit();
} else {
	header("Location: ../../admin_page/disaster.php");
	exit();
}
