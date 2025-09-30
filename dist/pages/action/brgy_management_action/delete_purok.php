<?php
include '../../../../database/session.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	echo json_encode(['success' => false, 'message' => 'Invalid request method']);
	exit;
}

$purok_id = intval($_POST['purok_id'] ?? 0);

if ($purok_id <= 0) {
	echo json_encode(['success' => false, 'message' => 'Missing purok_id']);
	exit;
}

$stmt = $conn->prepare("DELETE FROM purok_table WHERE purok_id = ?");
$stmt->bind_param('i', $purok_id);
if ($stmt->execute()) {
	echo json_encode(['success' => true]);
} else {
	echo json_encode(['success' => false, 'message' => 'DB delete failed']);
}

$stmt->close();
$conn->close();
