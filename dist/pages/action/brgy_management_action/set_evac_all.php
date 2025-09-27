<?php
include '../../../../database/session.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	echo json_encode(['ok'=>false,'error'=>'invalid_method']);
	exit;
}

$needed = intval($_POST['needed'] ?? 0) === 1 ? 1 : 0;

$stmt = $conn->prepare("UPDATE barangay_manegement_table SET evacuation_needed=?");
if (!$stmt) {
	echo json_encode(['ok'=>false,'error'=>'prepare_failed']);
	exit;
}
$stmt->bind_param('i', $needed);
$ok = $stmt->execute();
$stmt->close();

echo json_encode(['ok'=>(bool)$ok]);
