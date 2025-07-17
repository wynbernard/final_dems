<?php
ob_start();  // Start output buffering to prevent "headers already sent" errors
session_start();

// Include database connection
include 'conn.php';

// Ensure session variables exist
$admin_id = $_SESSION['admin_id'] ?? null;
$session_token = $_SESSION['session_token'] ?? null;

// If admin is not logged in, redirect to login
if (!$admin_id || !$session_token) {
    header("Location:../auth/log_in.php");
    exit();
}

// Validate session token from the database
$stmt = $conn->prepare("SELECT * FROM admin_table WHERE admin_id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

// If session token doesn't match or is missing, log the user out
if (!$admin || $admin['session_token'] !== $session_token) {
    session_destroy();
    header("Location: log_in.php");
    exit();
}

$_SESSION['username'] = $admin['username'];
$_SESSION['f_name'] = $admin['f_name'];
$_SESSION['l_name'] = $admin['l_name'];
$_SESSION['role'] = $admin['role'];
$_SESSION['evac_loc_id'] = $admin['evac_loc_id'];

ob_end_flush(); // Send output to browser
