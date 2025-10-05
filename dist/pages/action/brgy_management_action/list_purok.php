<?php
include '../../../../database/conn.php';
header('Content-Type: application/json');

if (!isset($_GET['barangay_id'])) {
	echo json_encode(['success' => false, 'message' => 'Missing barangay_id']);
	exit;
}

$barangay_id = intval($_GET['barangay_id']);

$stmt = $conn->prepare("SELECT purok_id, purok_name, purok_leader , pickup_point_name FROM purok_table WHERE barangay_id = ? ORDER BY purok_name ASC");
$stmt->bind_param('i', $barangay_id);
$stmt->execute();
$res = $stmt->get_result();
$puroks = [];
while ($row = $res->fetch_assoc()) {
	$puroks[] = $row;
}

echo json_encode(['success' => true, 'data' => $puroks]);
$stmt->close();
$conn->close();
