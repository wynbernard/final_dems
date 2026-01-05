<?php
include '../../../../database/session.php'; // Ensure this file contains your database connection
require_once '../../../../database/csrf.php';

// Validate CSRF token
csrf_validate_or_die();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$username = trim($_POST['username']);
	$f_name = trim($_POST['f_name']);
	$l_name = trim($_POST['l_name']);
	$password = trim($_POST['password']);
	$role = $_POST['role'];

	// Hash the password before storing
	$hashed_password = password_hash($password, PASSWORD_DEFAULT);

	// Prepare the SQL statement
	$query = "INSERT INTO admin_table (username, f_name, l_name, password, role) 
              VALUES (?, ?, ?, ?, ?)";

	if ($stmt = mysqli_prepare($conn, $query)) {
		// Bind parameters
		mysqli_stmt_bind_param($stmt, "sssss", $username, $f_name, $l_name, $hashed_password, $role);

		// Execute the statement
		if (mysqli_stmt_execute($stmt)) {
			$_SESSION['success'] = "<span style='color: green;'><i class='bi bi-check-circle-fill'></i></span> Add User Successfull!!!";

			// Log this action into activity_log_table
			require_once __DIR__ . '/../../../../database/log_activity.php';
			$action = 'Add Admin User';
			$description = "Added admin user: {$username} ({$f_name} {$l_name})";
			log_activity($conn, $action, $description);
		} else {
			error_log("Failed to add admin user: " . mysqli_error($conn));
			$_SESSION['error'] = "<span style='color:red;'><i class='bi bi-exclamation-circle-fill'></i></span> Add User Failed!!! Please try again.";
		}

		// Close the statement
		mysqli_stmt_close($stmt);
	} else {
		error_log("Failed to prepare add admin user statement: " . mysqli_error($conn));
		$_SESSION['error'] = "Database error: Unable to prepare statement. Please try again.";
	}

	// Redirect back to admin_users.php
	header("Location: ../../admin_page/admin_user.php");
	exit();
}
