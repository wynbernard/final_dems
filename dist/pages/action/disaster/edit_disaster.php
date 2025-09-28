<?php
include '../../../../database/session.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {

	$disaster_id = intval($_POST['disaster_id']);
	$disaster_type = trim($_POST['disaster_name']);
	$disaster_date = trim($_POST['date']);
	$disaster_level = intval($_POST['level'] ?? 0);
	$disaster_status = trim($_POST['status'] ?? '');

	if (empty($disaster_id) || empty($disaster_type) || empty($disaster_date) || $disaster_level <= 0 || $disaster_level > 10 || ($disaster_status !== 'Ongoing' && $disaster_status !== 'Resolved')) {
		$_SESSION['error'] = "<span style='color:white;'><i class='bi bi-exclamation-circle-fill'></i></span> Invalid data provided!";
		header("Location: ../../admin_page/disaster.php");
	}

	$sql = "UPDATE disaster_table SET disaster_name = ?, date = ?, level = ?, status = ? WHERE disaster_id = ?";
	$stmt = mysqli_prepare($conn, $sql);
	mysqli_stmt_bind_param($stmt, "ssisi", $disaster_type, $disaster_date, $disaster_level, $disaster_status, $disaster_id);

	if (mysqli_stmt_execute($stmt)) {
		$_SESSION['success'] = "<span style='color: green;'><i class='bi bi-check-circle-fill'></i></span> Disaster updated successfully!";
	} else {
		$_SESSION['error'] = "<span style='color:red;'><i class='bi bi-exclamation-circle-fill'></i></span> Update failed: " . mysqli_error($conn);
	}

	header("Location: ../../admin_page/disaster.php");
}
