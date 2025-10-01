<?php
// Helper to log activity into activity_log_table
// Usage: require_once 'log_activity.php'; log_activity($conn, 'Action Name', 'Description text');
function log_activity($conn, $action, $description)
{
	// Ensure session is started and session fields are available
	if (session_status() !== PHP_SESSION_ACTIVE) {
		@session_start();
	}

	$admin_name = trim(($_SESSION['f_name'] ?? '') . ' ' . ($_SESSION['l_name'] ?? ''));
	$admin_role = $_SESSION['role'] ?? '';
	$created = date('Y-m-d H:i:s');

	$query = "INSERT INTO activity_log_table (admin_name, action, description, created, role) VALUES (?, ?, ?, ?, ?)";
	if ($stmt = mysqli_prepare($conn, $query)) {
		mysqli_stmt_bind_param($stmt, 'sssss', $admin_name, $action, $description, $created, $admin_role);
		mysqli_stmt_execute($stmt); // best-effort
		mysqli_stmt_close($stmt);
	}
}
