<?php
ob_start();
session_start(); // Start session

// Include database connection
include 'conn.php';

// Admin Session Management
$pre_reg_id = $_SESSION['pre_reg_id'] ?? null;
$user_session_token = $_SESSION['user_session_token'] ?? null;

if (!$pre_reg_id || !$user_session_token) {
	header("Location: ../auth/log_in.php");
	exit();
}

// Validate admin session
$stmt = $conn->prepare("SELECT * FROM pre_reg_table WHERE pre_reg_id = ?");
$stmt->bind_param("i", $pre_reg_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || $user['user_session_token'] !== $user_session_token) {
	session_destroy();
	header("Location: log_in.php");
	exit();
}

// Store admin session details
$_SESSION['email_address'] = $user['email_address'];
$_SESSION['f_name'] = $user['f_name'];
$_SESSION['l_name'] = $user['l_name'];
$_SESSION['gender'] = $user['gender'];
$_SESSION['registered_as'] = $user['registered_as'];
$_SESSION['pre_reg_id'] = $user['pre_reg_id'];

ob_end_flush(); // Send output to browser
