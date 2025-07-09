<?php
header('Content-Type: application/json');
include '../../../database/session.php';


// Check connection
if ($conn->connect_error) {
	http_response_code(500);
	echo json_encode([
		'status' => 'error',
		'message' => 'Connection failed: ' . $conn->connect_error
	]);
	exit;
}

$sql = "
	SELECT id, room_number, room_type, capacity 
	FROM rooms 
	WHERE status = 'Available'
	ORDER BY room_type, room_number
";

$result = $conn->query($sql);

if ($result) {
	$rooms = [];
	while ($row = $result->fetch_assoc()) {
		$rooms[] = $row;
	}

	echo json_encode([
		'status' => 'success',
		'rooms' => $rooms
	]);
} else {
	http_response_code(500);
	echo json_encode([
		'status' => 'error',
		'message' => 'Query error: ' . $conn->error
	]);
}

$conn->close();
