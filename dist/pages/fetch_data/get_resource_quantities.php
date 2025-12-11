<?php
header("Content-Type: application/json");
include '../../../database/conn.php';

try {
    $query = "SELECT resource_id, resource_name, quantity, measurement_unit FROM resource_allocation_table ORDER BY resource_name ASC";
    $result = $conn->query($query);
    
    if (!$result) {
        error_log("Database query failed: " . $conn->error);
        throw new Exception("Failed to fetch resource data");
    }
    
    $resources = [];
    while ($row = $result->fetch_assoc()) {
        $resources[] = [
            'resource_id' => (int)$row['resource_id'],
            'resource_name' => $row['resource_name'],
            'quantity' => (int)$row['quantity'],
            'measurement_unit' => $row['measurement_unit']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'resources' => $resources
    ]);
    
} catch (Exception $e) {
    error_log("Error in get_resource_quantities.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while fetching resource data'
    ]);
}
?>
