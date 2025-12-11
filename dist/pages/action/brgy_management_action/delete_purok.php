<?php
include '../../../../database/session.php';

// CSRF Protection
require_once '../../../../database/csrf.php';
csrf_validate_or_die();

// Check if it's an AJAX request
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }
    $_SESSION['error'] = 'Invalid request method';
    header('Location: ../../admin_page/barangay_management.php');
    exit;
}

$purok_id = intval($_POST['purok_id'] ?? 0);

if ($purok_id <= 0) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Missing purok ID']);
        exit;
    }
    $_SESSION['error'] = "<span style='color:red;'><i class='bi bi-exclamation-circle-fill'></i> Missing purok ID.</span>";
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

$stmt = $conn->prepare("DELETE FROM purok_table WHERE purok_id = ?");
$stmt->bind_param('i', $purok_id);

if ($stmt->execute()) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Purok deleted successfully!', 'barangay_id' => $barangay_id]);
        $stmt->close();
        $conn->close();
        exit;
    }
    $_SESSION['success'] = "<span style='color:green;'><i class='bi bi-check-circle-fill'></i> Purok deleted successfully!</span>";
} else {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Failed to delete Purok: ' . $stmt->error]);
        $stmt->close();
        $conn->close();
        exit;
    }
    $_SESSION['error'] = "<span style='color:red;'><i class='bi bi-exclamation-circle-fill'></i> Failed to delete Purok.</span>";
}

$stmt->close();
$conn->close();

if (!$isAjax) {
    header('Location: ../../admin_page/barangay_view.php?id=' . $barangay_id);
    exit;
}
