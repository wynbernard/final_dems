<?php
require '../../../../database/session.php'; // Include your database connection file
require_once '../../../../database/csrf.php';

// Validate CSRF token
csrf_validate_or_die();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$admin_id = $_POST['admin_id'];
	$username = trim($_POST['username']);
	$password = trim($_POST['password']);
	$f_name = trim($_POST['f_name']);
	$l_name = trim($_POST['l_name']);
	$role = trim($_POST['role']);

	// Hash the password if provided (only update password if not empty)
	if (!empty($password)) {
		$hashed_password = password_hash($password, PASSWORD_DEFAULT);
		// Prepare SQL update statement with password
		$query = "UPDATE admin_table SET username = ?, password = ?, f_name = ?, l_name = ?, role = ? WHERE admin_id = ?";
		$stmt = mysqli_prepare($conn, $query);

		if ($stmt) {
			mysqli_stmt_bind_param($stmt, "sssssi", $username, $hashed_password, $f_name, $l_name, $role, $admin_id);
		}
	} else {
		// Update without password (password field not provided or empty)
		$query = "UPDATE admin_table SET username = ?, f_name = ?, l_name = ?, role = ? WHERE admin_id = ?";
		$stmt = mysqli_prepare($conn, $query);

		if ($stmt) {
			mysqli_stmt_bind_param($stmt, "ssssi", $username, $f_name, $l_name, $role, $admin_id);
		}
	}

	if ($stmt) {
		$execute = mysqli_stmt_execute($stmt);

		if ($execute) {
			$_SESSION['success'] = "<span style='color:green;'><i class='bi bi-check-circle-fill'></i></span> Update User Successfull!!!";

			require_once __DIR__ . '/../../../../database/log_activity.php';
			$action = 'Edit Admin User';
			$description = "Edited admin user: {$username} ({$f_name} {$l_name})";
			log_activity($conn, $action, $description);
		} else {
			error_log("Failed to update admin user: " . mysqli_error($conn));
			$_SESSION['error'] = "<span style='color:red;'><i class='bi bi-exclamation-circle-fill'></i></span> Update User Failed!!! Please try again.";
		}

		mysqli_stmt_close($stmt);
	} else {
		error_log("Failed to prepare update statement: " . mysqli_error($conn));
		$_SESSION['error'] = "Failed to prepare statement. Please try again.";
	}

	mysqli_close($conn);

	// Redirect back to the dashboard
	header("Location: ../../admin_page/admin_user.php");
	exit();
}
