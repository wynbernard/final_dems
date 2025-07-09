<?php
header('Content-Type: application/json');

include '../../../database/session.php';

if ($conn->connect_error) {
	http_response_code(500);
	echo json_encode(['error' => 'Database connection failed']);
	exit;
}

// Get input as raw POST body
$rawInput = file_get_contents("php://input");

// Assume input is just the `pre_reg_id` as plain text
$preRegId = trim($rawInput);

if (!$preRegId || !is_numeric($preRegId)) {
	http_response_code(400);
	echo json_encode(['error' => 'Missing or invalid pre_reg_id']);
	exit;
}

// Query using pre_reg_id
$stmt = $conn->prepare("SELECT * FROM pre_reg_table
                        LEFT JOIN family_table ON pre_reg_table.family_id = family_table.id
                        WHERE pre_reg_table.pre_reg_id = ?");
$stmt->bind_param("s", $preRegId); // Still safe for now

$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
	echo json_encode($row);
} else {
	http_response_code(404);
	echo json_encode(['error' => 'Member not found for this QR code']);
}

$stmt->close();
$conn->close();
