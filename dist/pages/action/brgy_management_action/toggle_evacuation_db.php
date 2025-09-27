<?php
include '../../../../database/session.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	echo json_encode(['ok'=>false,'error'=>'invalid_method']);
	exit;
}

$barangay_id = intval($_POST['barangay_id'] ?? 0);
$needed = intval($_POST['needed'] ?? 0) === 1 ? 1 : 0;

if ($barangay_id <= 0) {
	echo json_encode(['ok'=>false,'error'=>'invalid_id']);
	exit;
}

// Ensure column exists in DB: evacuation_needed TINYINT(1) NOT NULL DEFAULT 0
$stmt = $conn->prepare("UPDATE barangay_manegement_table SET evacuation_needed=? WHERE barangay_id=?");
if (!$stmt) {
	echo json_encode(['ok'=>false,'error'=>'prepare_failed']);
	exit;
}

$stmt->bind_param('ii', $needed, $barangay_id);
$ok = $stmt->execute();
$stmt->close();

echo json_encode(['ok'=>(bool)$ok]);
