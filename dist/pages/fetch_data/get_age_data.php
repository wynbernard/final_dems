<?php
header('Content-Type: application/json');
include '../../../database/conn.php';

// Validate and sanitize source parameter (whitelist approach)
$source = $_GET['source'] ?? 'pre';
$allowedSources = ['pre', 'evac'];
if (!in_array($source, $allowedSources)) {
    $source = 'pre'; // Default to 'pre' if invalid
}

$ageGroups = [
  'Infant' => 0,
  'Child' => 0,
  'Teen' => 0,
  'Adult' => 0,
  'Senior' => 0,
];

try {
    if ($source === 'evac') {
        $ageQuery = "
            SELECT act.classification, COUNT(*) as total
            FROM evac_reg_table ert
            LEFT JOIN pre_reg_table prt ON ert.pre_reg_id = prt.pre_reg_id
            LEFT JOIN age_class_table act ON prt.age_class_id = act.age_class_id
            GROUP BY act.classification
        ";
    } else {
        $ageQuery = "
            SELECT act.classification, COUNT(*) as total
            FROM pre_reg_table prt
            LEFT JOIN age_class_table act ON prt.age_class_id = act.age_class_id
            GROUP BY act.classification
        ";
    }

    $result = $conn->query($ageQuery);
    
    if (!$result) {
        error_log("Database query failed in get_age_data.php: " . $conn->error);
        throw new Exception("Failed to fetch age data");
    }
    
    while ($row = $result->fetch_assoc()) {
        $label = $row['classification'];
        $count = (int)$row['total'];
        if (isset($ageGroups[$label])) {
            $ageGroups[$label] = $count;
        }
    }

    echo json_encode($ageGroups);
    
} catch (Exception $e) {
    error_log("Error in get_age_data.php: " . $e->getMessage());
    echo json_encode([
        'error' => 'An error occurred while fetching age data'
    ]);
}
?>
