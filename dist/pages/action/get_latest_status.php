<?php
header('Content-Type: application/json');
include '../../../database/session.php';

$preRegId = intval($_GET['pre_reg_id'] ?? 0);
if ($preRegId <= 0) {
    echo json_encode(['error' => 'Invalid ID']);
    exit;
}

// Get evac_reg_id from evac_reg_table
$sql = "SELECT evac_reg_id 
        FROM evac_reg_table 
        WHERE pre_reg_id = ? 
        ORDER BY evac_reg_id DESC 
        LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $preRegId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    echo json_encode(['latest_status' => null]);
    exit;
}

$evacRegId = $row['evac_reg_id'];

// Get the latest log for that evac_reg_id
$sql = "SELECT status 
        FROM logs_table 
        WHERE evac_reg_id = ? 
        ORDER BY date_time DESC, logs_id DESC 
        LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $evacRegId);
$stmt->execute();
$result = $stmt->get_result();
$log = $result->fetch_assoc();

echo json_encode([
    'latest_status' => $log['status'] ?? null
]);
