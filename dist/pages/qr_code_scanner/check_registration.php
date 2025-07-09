<?php
header("Content-Type: application/json");
include '../../../database/session.php';

$pre_reg_id = $_GET['pre_reg_id'] ?? null;
$location_id = $_GET['location_id'] ?? null;

if (!$pre_reg_id || !$location_id) {
	echo json_encode(['success' => false, 'error' => 'Missing parameters']);
	exit;
}

try {
	$stmt = $conn->prepare("
        SELECT 1 
        FROM evac_reg_table r
        WHERE r.pre_reg_id = ? AND r.room_id IN (
            SELECT room_id FROM room_table WHERE evac_loc_id = ?
        )
        LIMIT 1
    ");
	$stmt->bind_param('ii', $pre_reg_id, $location_id);
	$stmt->execute();
	$result = $stmt->get_result();
	echo json_encode(['success' => true, 'isRegistered' => $result->num_rows > 0]);
} catch (Exception $e) {
	echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
