<?php

include '../../../../database/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// Sanitize and validate input
	$disaster_name = trim($_POST['disaster_name'] ?? '');
	$level = trim($_POST['level'] ?? '');
	$date = trim($_POST['date'] ?? '');

	if ($disaster_name !== '' && $level !== '' && $date !== '') {
		$stmt = $conn->prepare("INSERT INTO disaster_table (disaster_name, level, date) VALUES (?, ?, ?)");

		if ($stmt) {
			$stmt->bind_param("sss", $disaster_name, $level, $date);

			if ($stmt->execute()) {
				$_SESSION['success'] = "<span style='color: green;'><i class='bi bi-check-circle-fill'></i></span> Disaster added successfully!";
			} else {
				$_SESSION['error'] = "<span style='color:red;'><i class='bi bi-x-circle-fill'></i></span> Failed to add disaster. " . $stmt->error;
			}

			$stmt->close();
		} else {
			$_SESSION['error'] = "<span style='color:red;'><i class='bi bi-x-circle-fill'></i></span> Failed to prepare the insert statement.";
		}
	} else {
		$_SESSION['error'] = "<span style='color:red;'><i class='bi bi-info-circle-fill'></i></span> All fields are required.";
	}

	header("Location: ../../admin_page/disaster.php");
	exit();
} else {
	header("Location: ../../admin_page/disaster.php");
	exit();
}
