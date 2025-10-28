<?php
header("Content-Type: application/json");
include '../../../database/conn.php';

try {
    $query = "SELECT resource_id, resource_name, quantity, measurement_unit FROM resource_allocation_table ORDER BY resource_name ASC";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        throw new Exception("Database query failed: " . mysqli_error($conn));
    }
    
    $resources = [];
    while ($row = mysqli_fetch_assoc($result)) {
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
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
