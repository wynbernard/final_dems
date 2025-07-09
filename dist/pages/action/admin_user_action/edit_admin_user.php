<?php
require '../../../../database/session.php'; // Include your database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$admin_id = $_POST['admin_id'];
	$username = trim($_POST['username']);
	$password = trim($_POST['password']);
	$f_name = trim($_POST['f_name']);
	$l_name = trim($_POST['l_name']);
	$role = trim($_POST['role']);


	// Prepare SQL update statement to prevent SQL injection
	$query = "UPDATE admin_table SET username = ?, password = ?, f_name = ?, l_name = ?, role = ? WHERE admin_id = ?";
	$stmt = mysqli_prepare($conn, $query);

	if ($stmt) {
		mysqli_stmt_bind_param($stmt, "sssssi", $username, $password, $f_name, $l_name, $role, $admin_id);
		$execute = mysqli_stmt_execute($stmt);

		if ($execute) {
			$_SESSION['success'] = "<span style='color:green;'><i class='bi bi-check-circle-fill'></i></span> Update User Successfull!!!";
		} else {
			$_SESSION['error'] = "<span style='color:red;'><i class='bi bi-exclamation-circle-fill'></i></span> Update User Failed!!!" . mysqli_error($conn);
		}

		mysqli_stmt_close($stmt);
	} else {
		$_SESSION['error'] = "Failed to prepare statement.";
	}

	mysqli_close($conn);

	// Redirect back to the dashboard
	header("Location: ../../admin_page/admin_user.php");
	exit();
}
