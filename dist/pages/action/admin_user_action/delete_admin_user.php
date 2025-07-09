<?php
include '../../../../database/session.php'; // Database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$admin_id = $_POST['admin_id'];

	if (empty($admin_id)) {
		$_SESSION['error'] = "Invalid admin ID.";
		header("Location: ../../admin_page/admin_user.php");
		exit();
	}

	// Prepare delete statement
	$query = "DELETE FROM admin_table WHERE admin_id = ?";
	$stmt = mysqli_prepare($conn, $query);

	if ($stmt) {
		mysqli_stmt_bind_param($stmt, "i", $admin_id);
		$execute = mysqli_stmt_execute($stmt);

		if ($execute) {
			$_SESSION['success'] = "<span style='color: green;'><i class='bi bi-check-circle-fill'></i></span> Delete User Successfull!!!";
		} else {
			$_SESSION['error'] = "<span style='color:red;'><i class='bi bi-exclamation-circle-fill'></i></span> Delete User Failed!!!" . mysqli_error($conn);
		}

		mysqli_stmt_close($stmt);
	} else {
		$_SESSION['error'] = "Failed to prepare statement.";
	}

	mysqli_close($conn);

	// Redirect back to dashboard
	header("Location: ../../admin_page/admin_user.php");
	exit();
}
