<?php
include '../../../../database/session.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	echo json_encode(['success' => false, 'message' => 'Invalid request method']);
	exit;
}

$purok_name = trim($_POST['purok_name'] ?? '');
$barangay_id = intval($_POST['barangay_id'] ?? 0);
$purok_leader = trim($_POST['purok_leader'] ?? '');

if ($purok_name === '' || $barangay_id <= 0) {
	echo json_encode(['success' => false, 'message' => 'Missing required fields']);
	exit;
}

$stmt = $conn->prepare("INSERT INTO purok_table (purok_name, barangay_id, purok_leader) VALUES (?, ?, ?)");
$stmt->bind_param('sis', $purok_name, $barangay_id, $purok_leader);
if ($stmt->execute()) {
	echo json_encode(['success' => true, 'purok_id' => $conn->insert_id]);
} else {
	echo json_encode(['success' => false, 'message' => 'DB insert failed']);
}

$stmt->close();
$conn->close();
