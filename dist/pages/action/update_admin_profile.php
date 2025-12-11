<?php

include '../../../database/session.php';
require_once '../../../database/csrf.php';

// Validate CSRF token
csrf_validate_or_die();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	// Get admin_id from session and validate
	$admin_id = isset($_SESSION['admin_id']) ? (int)$_SESSION['admin_id'] : 0;
	
	if ($admin_id <= 0) {
		$_SESSION['error'] = "<span style='color:white'><i class='bi bi-exclamation-circle-fill'></i></span> Invalid session. Please log in again.";
		header("Location: ../admin_page/profile_admin.php");
		exit();
	}

	// Sanitize and validate input
	$username = trim($_POST['username'] ?? '');
	$first_name = trim($_POST['f_name'] ?? '');
	$last_name = trim($_POST['l_name'] ?? '');
	$password = $_POST['password'] ?? '';

	// Validate required fields
	if (empty($username) || empty($first_name) || empty($last_name)) {
		$_SESSION['error'] = "<span style='color:white'><i class='bi bi-exclamation-circle-fill'></i></span> All fields are required!";
		header("Location: ../admin_page/profile_admin.php");
		exit();
	}

	// Validate username length
	if (strlen($username) < 3 || strlen($username) > 50) {
		$_SESSION['error'] = "<span style='color:white'><i class='bi bi-exclamation-circle-fill'></i></span> Username must be between 3 and 50 characters.";
		header("Location: ../admin_page/profile_admin.php");
		exit();
	}

	// Use prepared statements to prevent SQL injection
	if (!empty($password)) {
		// Hash password if provided
		$hashed_password = password_hash($password, PASSWORD_DEFAULT);
		$query = "UPDATE admin_table SET username = ?, f_name = ?, l_name = ?, password = ? WHERE admin_id = ?";
		$stmt = $conn->prepare($query);
		
		if ($stmt) {
			$stmt->bind_param("ssssi", $username, $first_name, $last_name, $hashed_password, $admin_id);
		}
	} else {
		// Update without password
		$query = "UPDATE admin_table SET username = ?, f_name = ?, l_name = ? WHERE admin_id = ?";
		$stmt = $conn->prepare($query);
		
		if ($stmt) {
			$stmt->bind_param("sssi", $username, $first_name, $last_name, $admin_id);
		}
	}

	if ($stmt && $stmt->execute()) {
		$_SESSION['success'] = "<span style='color:white'><i class='bi bi-check-circle-fill'></i></span> Profile updated successfully!";
	} else {
		// Log error instead of exposing it
		error_log("Profile update failed for admin_id $admin_id: " . ($stmt ? $stmt->error : $conn->error));
		$_SESSION['error'] = "<span style='color:white'><i class='bi bi-exclamation-circle-fill'></i></span> Profile update failed. Please try again.";
	}

	if ($stmt) {
		$stmt->close();
	}

	header("Location: ../admin_page/profile_admin.php");
	exit();
}
