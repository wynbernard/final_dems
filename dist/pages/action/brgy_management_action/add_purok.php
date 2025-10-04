<?php
include '../../../../database/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method';
    header('Location: ../../admin_page/barangay_management.php');
    exit;
}

$purok_name = trim($_POST['purok_name'] ?? '');
$barangay_id = intval($_POST['barangay_id'] ?? 0);
$purok_leader = trim($_POST['purok_leader'] ?? '');
$pickup_point_name = trim($_POST['pickup_point_name'] ?? '');

if ($purok_name === '' || $barangay_id <= 0) {
    $_SESSION['error'] = "<span style='color:red;'><i class='bi bi-exclamation-circle-fill'></i> Missing required fields.</span>";
    header('Location: ../../admin_page/barangay_view.php?id=' . $barangay_id);
    exit;
}

$stmt = $conn->prepare("INSERT INTO purok_table (purok_name, barangay_id, purok_leader, pickup_point_name) VALUES (?, ?, ?, ?)");
$stmt->bind_param('siss', $purok_name, $barangay_id, $purok_leader, $pickup_point_name);
if ($stmt->execute()) {
    $_SESSION['success'] = "<span style='color:green;'><i class='bi bi-check-circle-fill'></i> Purok added successfully!</span>";
} else {
    $_SESSION['error'] = "<span style='color:red;'><i class='bi bi-exclamation-circle-fill'></i> Failed to add Purok.</span>";
}

$stmt->close();
$conn->close();

header('Location: ../../admin_page/barangay_view.php?id=' . $barangay_id);
exit;
