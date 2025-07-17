<?php
include '../../../database/conn.php';
header('Content-Type: application/json');

if (!isset($_GET['evac_loc_id'])) {
	echo json_encode(['success' => false, 'message' => 'No location provided']);
	exit;
}

$evacLocId = $_GET['evac_loc_id'];

// 1. Get evac location info
$infoStmt = $conn->prepare("
	SELECT 
		evac_loc_table.name AS location_name,
		COUNT(room_table.room_id) AS total_rooms,
		evac_loc_table.total_capacity AS available_rooms,
		MAX(room_table.room_capacity) AS capacity_per_room
	FROM room_table
	LEFT JOIN evac_loc_table ON room_table.evac_loc_id = evac_loc_table.evac_loc_id
	WHERE evac_loc_table.evac_loc_id = ?
");
$infoStmt->bind_param("s", $evacLocId);
$infoStmt->execute();
$infoResult = $infoStmt->get_result();
$infoRow = $infoResult->fetch_assoc();

// 2. Get room names
$roomsStmt = $conn->prepare("
	SELECT room_name FROM room_table
	WHERE evac_loc_id = ?
");
$roomsStmt->bind_param("s", $evacLocId);
$roomsStmt->execute();
$roomsResult = $roomsStmt->get_result();

$rooms = [];
while ($roomRow = $roomsResult->fetch_assoc()) {
	$rooms[] = ['name' => $roomRow['room_name']];
}

if ($infoRow) {
	echo json_encode([
		'success' => true,
		'location' => $infoRow['location_name'],
		'total_rooms' => $infoRow['total_rooms'],
		'available_rooms' => $infoRow['available_rooms'],
		'capacity_per_room' => $infoRow['capacity_per_room'],
		'rooms' => $rooms
	]);
} else {
	echo json_encode(['success' => false, 'message' => 'Location not found']);
}
?>
