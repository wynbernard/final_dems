<?php
session_start();
include '../../../database/conn.php'; // Adjust the path as needed

// Prevent browser from caching
header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Regenerate session ID for security
session_regenerate_id(true);

// Get admin ID before destroying session
$admin_id = $_SESSION['admin_id'] ?? null;

if ($admin_id) {
	// Remove the session token from the database
	$stmt = $conn->prepare("UPDATE admin_table SET session_token = NULL WHERE admin_id = ?");
	$stmt->bind_param("i", $admin_id);
	$stmt->execute();
}

// Destroy session and remove all session variables
$_SESSION = [];
session_unset();
session_destroy();

// Delete session token cookie if it exists
setcookie("session_token", "", time() - 3600, "/", "", false, true);

// Redirect to login page
header("Location: log_in.php");
exit();
