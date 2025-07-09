<?php
include '../../../database/conn.php';

// Ensure user is logged in
if (!isset($_SESSION['pre_reg_id'])) {
        die("Unauthorized Access!");
}

$pre_reg_id = $_SESSION['pre_reg_id']; // Get the logged-in user's ID

// Fetch user details including QR code path
$sql = "SELECT pre_reg_table.*, qr_table.code 
        FROM pre_reg_table
        LEFT JOIN qr_table ON pre_reg_table.pre_reg_id = qr_table.pre_reg_id 
        WHERE pre_reg_table.pre_reg_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $pre_reg_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$conn->close();

// Set default QR code path if none exists
$qrCodePath = !empty($user['code']) ? $user['code'] : '../../qrcodes/default-qr.png';
