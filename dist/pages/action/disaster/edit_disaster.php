<?php
include '../../../../database/session.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {

	$disaster_id = intval($_POST['disaster_id']);
	$disaster_type = trim($_POST['disaster_name']);
	$disaster_level = trim($_POST['level']);
	$disaster_date = trim($_POST['date']);

	if (empty($disaster_id) || empty($disaster_type) || empty($disaster_level) || empty($disaster_date)) {
		$_SESSION['error'] = "<span style='color:white;'><i class='bi bi-exclamation-circle-fill'></i></span> Invalid data provided!";
		header("Location: ../admin_page/disaster.php");
	}

	$sql = "UPDATE disaster_table SET disaster_name = ?, level = ?, date = ? WHERE disaster_id = ?";
	$stmt = mysqli_prepare($conn, $sql);
	mysqli_stmt_bind_param($stmt, "sssi", $disaster_type, $disaster_level, $disaster_date, $disaster_id);

	if (mysqli_stmt_execute($stmt)) {
		$_SESSION['success'] = "<span style='color: green;'><i class='bi bi-check-circle-fill'></i></span> Disaster updated successfully!";
	} else {
		$_SESSION['error'] = "<span style='color:red;'><i class='bi bi-exclamation-circle-fill'></i></span> Update failed: " . mysqli_error($conn);
	}

	header("Location: ../../admin_page/disaster.php");
}
