<?php
include '../../../database/conn.php';

if (!isset($_SESSION['admin_id'])) {
	die("Unauthorized Access!");
}

$admin_id = $_SESSION['admin_id']; // Get the logged-in admin's ID

$sql = "SELECT * FROM admin_table WHERE admin_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

// Close connection
$stmt->close();
$conn->close();
