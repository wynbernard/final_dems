<?php
// include '../../../../database/session.php';
include '../../../../database/conn.php';
header('Content-Type: application/json');

try {
    $result = $conn->query("SELECT barangay_id, barangay_name FROM barangay_manegement_table ORDER BY barangay_name ASC");
    $data = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'barangay_id' => (int)$row['barangay_id'],
                'barangay_name' => $row['barangay_name']
            ];
        }
    }
    echo json_encode(['success' => true, 'data' => $data]);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to load barangays']);
}

if (isset($result) && $result instanceof mysqli_result) {
    $result->free();
}
$conn->close();

