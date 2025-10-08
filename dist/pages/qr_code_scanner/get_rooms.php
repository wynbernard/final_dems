<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
require_once '../../../database/session.php';

try {
    if (!isset($_GET['location_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing location_id parameter']);
        exit;
    }

    $locationId = (int)$_GET['location_id'];

    $stmt = $conn->prepare("
        SELECT 
            r.room_id AS id,
            r.room_name AS name,
            r.room_capacity,
            COALESCE((
                SELECT COUNT(DISTINCT rt.pre_reg_id)
                FROM evac_reg_table rt
                WHERE rt.room_id = r.room_id and rt.status = 'Evacuated'
            ), 0) AS current_occupancy
        FROM room_table r
        WHERE r.evac_loc_id = ?
        ORDER BY r.room_name
    ");

    $stmt->bind_param('i', $locationId);
    $stmt->execute();
    $result = $stmt->get_result();

    $rooms = [];
    while ($row = $result->fetch_assoc()) {
        $rooms[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'capacity' => (int)$row['room_capacity'],
            'current_occupancy' => (int)$row['current_occupancy']
        ];
    }

    echo json_encode([
        'success' => true,
        'rooms' => $rooms
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
