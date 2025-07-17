<?php
include '../../../database/conn.php';
header('Content-Type: application/json');

if (!isset($_GET['evac_loc_id'])) {
	echo json_encode(['success' => false, 'message' => 'No location provided']);
	exit;
}

$evacLocId = $_GET['evac_loc_id'];
$stmt = $conn->prepare("SELECT evac_loc_table.name AS location_name, room_table.room_id AS total_rooms, room_table.room_capacity AS capacity_per_room, evac_loc_table.total_capacity AS available_rooms FROM room_table
LEFT JOIN evac_loc_table ON room_table.evac_loc_id = evac_loc_table.evac_loc_id
 WHERE evac_loc_table.evac_loc_id = ?");
$stmt->bind_param("s", $evacLocId);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
	echo json_encode([
		'success' => true,
		'location' => $row['location_name'],
		'total_rooms' => $row['total_rooms'],
		'available_rooms' => $row['available_rooms'],
		'capacity_per_room' => $row['capacity_per_room']
	]);
} else {
	echo json_encode(['success' => false, 'message' => 'Location not found']);
}
