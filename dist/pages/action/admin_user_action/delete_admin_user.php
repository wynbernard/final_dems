<?php
include '../../../../database/session.php'; // Database connection
require_once '../../../../database/csrf.php';

// Validate CSRF token
csrf_validate_or_die();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$admin_id = $_POST['admin_id'];

	if (empty($admin_id)) {
		$_SESSION['error'] = "Invalid admin ID.";
		header("Location: ../../admin_page/admin_user.php");
		exit();
	}

	// Fetch admin details first so we can log the name after deletion
	$selectQuery = "SELECT username, f_name, l_name FROM admin_table WHERE admin_id = ? LIMIT 1";
	if ($selectStmt = mysqli_prepare($conn, $selectQuery)) {
		mysqli_stmt_bind_param($selectStmt, "i", $admin_id);
		mysqli_stmt_execute($selectStmt);
		$result = mysqli_stmt_get_result($selectStmt);
		$adminRow = mysqli_fetch_assoc($result);
		mysqli_stmt_close($selectStmt);
	} else {
		$adminRow = null;
	}

	// Prepare delete statement
	$query = "DELETE FROM admin_table WHERE admin_id = ?";
	$stmt = mysqli_prepare($conn, $query);

	if ($stmt) {
		mysqli_stmt_bind_param($stmt, "i", $admin_id);
		$execute = mysqli_stmt_execute($stmt);

		if ($execute) {
			$_SESSION['success'] = "<span style='color: green;'><i class='bi bi-check-circle-fill'></i></span> Delete User Successfull!!!";

			require_once __DIR__ . '/../../../../database/log_activity.php';
			$action = 'Delete Admin User';
			if ($adminRow) {
				$deletedUsername = $adminRow['username'] ?? '';
				$deletedF = $adminRow['f_name'] ?? '';
				$deletedL = $adminRow['l_name'] ?? '';
				$description = "Deleted admin user: {$deletedUsername} ({$deletedF} {$deletedL})";
			} else {
				$description = "Deleted admin with ID: {$admin_id}";
			}
			log_activity($conn, $action, $description);
		} else {
			error_log("Failed to delete admin user: " . mysqli_error($conn));
			$_SESSION['error'] = "<span style='color:red;'><i class='bi bi-exclamation-circle-fill'></i></span> Delete User Failed!!! Please try again.";
		}

		mysqli_stmt_close($stmt);
	} else {
		error_log("Failed to prepare delete statement: " . mysqli_error($conn));
		$_SESSION['error'] = "Failed to prepare statement. Please try again.";
	}

	mysqli_close($conn);

	// Redirect back to dashboard
	header("Location: ../../admin_page/admin_user.php");
	exit();
}
