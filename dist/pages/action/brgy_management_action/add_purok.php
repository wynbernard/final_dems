<?php
include '../../../../database/session.php';

// CSRF Protection
require_once '../../../../database/csrf.php';

// Check if it's an AJAX request
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Validate CSRF token (use AJAX validation for AJAX requests)
if ($isAjax) {
    if (!csrf_validate_ajax()) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'CSRF token validation failed. Please refresh the page and try again.'
        ]);
        exit();
    }
} else {
    csrf_validate_or_die();
}

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

$purok_name = trim($_POST['purok_name'] ?? '');
$barangay_id = intval($_POST['barangay_id'] ?? 0);
$purok_leader = trim($_POST['purok_leader'] ?? '');
$pickup_point_name = trim($_POST['pickup_point_name'] ?? '');

if ($purok_name === '' || $barangay_id <= 0) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }
    $_SESSION['error'] = "<span style='color:red;'><i class='bi bi-exclamation-circle-fill'></i> Missing required fields.</span>";
    header('Location: ../../admin_page/barangay_view.php?id=' . $barangay_id);
    exit;
}

$stmt = $conn->prepare("INSERT INTO purok_table (purok_name, barangay_id, purok_leader, pickup_point_name) VALUES (?, ?, ?, ?)");
$stmt->bind_param('siss', $purok_name, $barangay_id, $purok_leader, $pickup_point_name);

if ($stmt->execute()) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Purok added successfully!']);
        $stmt->close();
        $conn->close();
        exit;
    }
    $_SESSION['success'] = "<span style='color:green;'><i class='bi bi-check-circle-fill'></i> Purok added successfully!</span>";
} else {
    error_log("Failed to add Purok in add_purok.php: " . $stmt->error);
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Failed to add Purok. Please try again.']);
        $stmt->close();
        $conn->close();
        exit;
    }
    $_SESSION['error'] = "<span style='color:red;'><i class='bi bi-exclamation-circle-fill'></i> Failed to add Purok.</span>";
}

$stmt->close();
$conn->close();

if (!$isAjax) {
    header('Location: ../../admin_page/barangay_view.php?id=' . $barangay_id);
    exit;
}
