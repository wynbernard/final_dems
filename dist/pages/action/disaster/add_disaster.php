<?php

include '../../../../database/session.php';
require_once '../../../../database/csrf.php';

// Validate CSRF token
csrf_validate_or_die();
	
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// Sanitize and validate input
	$disaster_name = trim($_POST['disaster_name'] ?? '');
	$date = trim($_POST['date'] ?? '');
	$level = intval($_POST['level'] ?? 0);
	$disaster_type = trim($_POST['disaster_type'] ?? '');
	$status = trim($_POST['status'] ?? '');

	if ($disaster_name !== '' && $date !== '' &&  ($status === 'Ongoing' || $status === 'Resolved')) {
		$stmt = $conn->prepare("INSERT INTO disaster_table (disaster_name,date,level,status,kind_of_disaster) VALUES (?,?,?,?,?)");

		if ($stmt) {
			$stmt->bind_param("ssiss", $disaster_name, $date, $level, $status, $disaster_type);

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
