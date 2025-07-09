<?php

include '../../../database/session.php'; // Include your database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$username = mysqli_real_escape_string($conn, $_POST['username']);
	$first_name = mysqli_real_escape_string($conn, $_POST['f_name']);
	$last_name = mysqli_real_escape_string($conn, $_POST['l_name']);

	// Check if password is entered
	if (!empty($_POST['password'])) {
		$password = mysqli_real_escape_string($conn, $_POST['password']); // Store password as plain text
		$query = "UPDATE admin_table SET username='$username', f_name='$first_name', l_name='$last_name', password='$password' WHERE admin_id= $admin_id"; // Change `id=1` based on session user
	} else {
		$query = "UPDATE admin_table SET username='$username', f_name='$first_name', l_name='$last_name' WHERE admin_id= $admin_id";
	}

	if (mysqli_query($conn, $query)) {
		$_SESSION['success'] = "<span style='color:white'><i class='bi bi-check-circle-fill'></i></span> Profile updated successfully!";
	} else {
		$_SESSION['error'] = "<span style='color: white);'><i class='bi bi-exclamation-circle-fill'></i></span> Profile updated Failed!" . mysqli_error($conn);
	}

	header("Location: ../admin_page/profile_admin.php"); // Redirect back to profile page
	exit();
}
