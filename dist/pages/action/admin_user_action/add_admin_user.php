<?php
include '../../../../database/session.php'; // Ensure this file contains your database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$username = trim($_POST['username']);
	$f_name = trim($_POST['f_name']);
	$l_name = trim($_POST['l_name']);
	$password = trim($_POST['password']);
	$role = $_POST['role'];

	// Prepare the SQL statement
	$query = "INSERT INTO admin_table (username, f_name, l_name, password, role) 
              VALUES (?, ?, ?, ?, ?)";

	if ($stmt = mysqli_prepare($conn, $query)) {
		// Bind parameters
		mysqli_stmt_bind_param($stmt, "sssss", $username, $f_name, $l_name, $password, $role);

		// Execute the statement
		if (mysqli_stmt_execute($stmt)) {
			$_SESSION['success'] = "<span style='color: green;'><i class='bi bi-check-circle-fill'></i></span> Add User Successfull!!!";
		} else {
			$_SESSION['error'] = "<span style='color:red;'><i class='bi bi-exclamation-circle-fill'></i></span> Add User Failed!!!" . mysqli_error($conn);
		}

		// Close the statement
		mysqli_stmt_close($stmt);
	} else {
		$_SESSION['error'] = "Database error: Unable to prepare statement.";
	}

	// Redirect back to admin_users.php
	header("Location: ../../admin_page/admin_user.php");
	exit();
}
