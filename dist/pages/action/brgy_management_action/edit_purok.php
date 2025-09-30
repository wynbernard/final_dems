<?php
include '../../../../database/session.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	echo json_encode(['success' => false, 'message' => 'Invalid request method']);
	exit;
}

$purok_id = intval($_POST['purok_id'] ?? 0);
$purok_name = trim($_POST['purok_name'] ?? '');
$purok_leader = trim($_POST['purok_leader'] ?? '');

if ($purok_id <= 0 || $purok_name === '') {
	echo json_encode(['success' => false, 'message' => 'Missing required fields']);
	exit;
}

$stmt = $conn->prepare("UPDATE purok_table SET purok_name = ?, purok_leader = ? WHERE purok_id = ?");
$stmt->bind_param('ssi', $purok_name, $purok_leader, $purok_id);
if ($stmt->execute()) {
	echo json_encode(['success' => true]);
} else {
	echo json_encode(['success' => false, 'message' => 'DB update failed']);
}

$stmt->close();
$conn->close();
