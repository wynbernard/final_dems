<?php
include '../../../../database/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method';
    header('Location: ../../admin_page/barangay_management.php');
    exit;
}

$purok_id = intval($_POST['purok_id'] ?? 0);
$purok_name = trim($_POST['purok_name'] ?? '');
$purok_leader = trim($_POST['purok_leader'] ?? '');
$pickup_point_name = trim($_POST['pickup_point_name'] ?? '');

if ($purok_id <= 0 || $purok_name === '') {
    $_SESSION['error'] = "<span style='color:red;'><i class='bi bi-exclamation-circle-fill'></i> Missing required fields.</span>";
    header('Location: ../../admin_page/barangay_view.php?id=' . $barangay_id);
    exit;
}

// Get barangay_id for redirect
$stmt = $conn->prepare("SELECT barangay_id FROM purok_table WHERE purok_id = ?");
$stmt->bind_param('i', $purok_id);
$stmt->execute();
$result = $stmt->get_result();
$barangay_id = 0;
if ($row = $result->fetch_assoc()) {
    $barangay_id = $row['barangay_id'];
}
$stmt->close();

$stmt = $conn->prepare("UPDATE purok_table SET purok_name = ?, purok_leader = ? , pickup_point_name = ? WHERE purok_id = ? ");
$stmt->bind_param('sssi', $purok_name, $purok_leader, $pickup_point_name, $purok_id);
if ($stmt->execute()) {
    $_SESSION['success'] = "<span style='color:green;'><i class='bi bi-check-circle-fill'></i> Purok updated successfully!</span>";
} else {
    $_SESSION['error'] = "<span style='color:red;'><i class='bi bi-exclamation-circle-fill'></i> Failed to update Purok.</span>";
}

$stmt->close();
$conn->close();

header('Location: ../../admin_page/barangay_view.php?id=' . $barangay_id);
exit;
