<?php
include '../../../database/session.php';
header('Content-Type: application/json');


try {
    $input = json_decode(file_get_contents('php://input'), true);
    $pre_reg_id = $input['pre_reg_id'] ?? null;
    $staff_evac_loc_id = $input['staff_evac_loc_id'] ?? null;
    
    if (!$pre_reg_id || !$staff_evac_loc_id) {
        echo json_encode(['success' => false, 'message' => 'Missing required data']);
        exit;
    }
    
    // Query to get evacuee's current registration and location information
    $query = "SELECT ert.evac_loc_id,ert.status, 
                     prt.l_name,
                     el.name as evacuee_location_name
              FROM evac_reg_table ert
              LEFT JOIN pre_reg_table prt ON ert.pre_reg_id = prt.pre_reg_id
              LEFT JOIN evac_loc_table el ON ert.evac_loc_id = el.evac_loc_id 
              WHERE ert.pre_reg_id = ? AND ert.status = 'Evacuated'
              ORDER BY ert.date_reg DESC 
              LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $pre_reg_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $registration = $result->fetch_assoc();
    
    if (!$registration) {
        // No active evac location: allow distribution (treated as no active location)
        echo json_encode([
            'success' => true,
            'no_active_location' => true,
            'evacuee_evac_loc_id' => null,
            'evacuee_location_name' => null
        ]);
        exit;
    }
    
    // Get staff location name
    $staffLocationQuery = "SELECT name FROM evac_loc_table WHERE evac_loc_id = ?";
    $staffLocationStmt = $conn->prepare($staffLocationQuery);
    $staffLocationStmt->bind_param("i", $staff_evac_loc_id);
    $staffLocationStmt->execute();
    $staffLocationResult = $staffLocationStmt->get_result();
    $staffLocation = $staffLocationResult->fetch_assoc();
    
    // Check if location IDs match
    if ($registration['evac_loc_id'] != $staff_evac_loc_id) {
        echo json_encode([
            'success' => false,
            'message' => "Location mismatch. Evacuee is registered at {$registration['evacuee_location_name']}, but you are assigned to {$staffLocation['name']}."
        ]);
        exit;
    }
    
    // Check if evacuee is still active
    if ($registration['status'] !== 'Evacuated') {
        echo json_encode([
            'success' => false,
            'message' => "Evacuee registration is not active (Status: {$registration['status']})"
        ]);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'evacuee_evac_loc_id' => $registration['evac_loc_id'],
        'evacuee_location_name' => $registration['evacuee_location_name'],
        'staff_location_name' => $staffLocation['name'],
        'evacuee_name' => $registration['l_name']
        // 'room_id' => $registration['room_id']
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>