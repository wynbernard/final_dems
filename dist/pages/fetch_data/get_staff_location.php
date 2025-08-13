<?php
include '../../../database/session.php';
header('Content-Type: application/json');


try {
    // Get staff location from session or database
    $staff_id = $_SESSION['admin_id'] ?? null;
    
    if (!$staff_id) {
        echo json_encode(['success' => false, 'message' => 'Staff not logged in']);
        exit;
    }
    
    // Query to get staff assigned evacuation location ID
    $query = "SELECT evac_loc_id FROM admin_table WHERE admin_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $staff_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $staff = $result->fetch_assoc();
    
    if ($staff) {
        echo json_encode([
            'success' => true,
            'evac_loc_id' => $staff['evac_loc_id']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Staff not found']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>