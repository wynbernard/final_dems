<?php
session_start();
include '../../../database/conn.php';

// Determine role and assigned location
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'Staff';
$assignedLocation = isset($_SESSION['evac_loc_id']) ? intval($_SESSION['evac_loc_id']) : 0;

// Build query to fetch distribution data (match the working query from reports.php)
$sql = "SELECT 
            pr.f_name AS evacuee_name,
            r.resource_name,
            d.quantity AS quantity,
            r.measurement_unit AS unit,
            d.date_time AS date_received,
            evt.city AS evacuation_center,
            evt.evac_loc_id
        FROM resource_distribution_table d
        LEFT JOIN pre_reg_table pr ON d.pre_reg_id = pr.pre_reg_id
        LEFT JOIN evac_reg_table e ON e.pre_reg_id = pr.pre_reg_id
        LEFT JOIN admin_table a ON a.admin_id = a.admin_id
        LEFT JOIN evac_loc_table evt ON e.evac_loc_id = evt.evac_loc_id
        LEFT JOIN resource_allocation_table r ON d.resource_id = r.resource_id";

if (strtolower(trim($role)) === 'Staff' && $assignedLocation > 0) {
    $sql .= " WHERE a.evac_loc_id = ?";
}

$sql .= " GROUP BY pr.pre_reg_id, r.resource_name, d.date_time, d.quantity, r.measurement_unit, evt.name, evt.evac_loc_id
          ORDER BY d.date_time DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo 'Failed to prepare export query.';
    exit;
}

if (strtolower(trim($role)) === 'Staff' && $assignedLocation > 0) {
    $stmt->bind_param('i', $assignedLocation);
}

$stmt->execute();
$res = $stmt->get_result();

// Check if we have any data
$rowCount = $res->num_rows;
if ($rowCount === 0) {
    // Still create a CSV with headers but no data
    $filename = 'resource_distribution_' . date('Ymd_His') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    
    $output = fopen('php://output', 'w');
    fwrite($output, "\xEF\xBB\xBF");
    fputcsv($output, ['Evacuee Name', 'Resource Name', 'Evacuation Center', 'Quantity', 'Date Received']);
    fputcsv($output, ['No distribution data found']);
    fclose($output);
    $stmt->close();
    $conn->close();
    exit;
}

// Set headers for CSV download (Excel-compatible)
$filename = 'resource_distribution_' . date('Ymd_His') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');

// Optionally write BOM for Excel to properly detect UTF-8
fwrite($output, "\xEF\xBB\xBF");

// CSV Header row (match on-screen table order)
fputcsv($output, ['Evacuee Name', 'Resource Name', 'Evacuation Center', 'Quantity', 'Date Received']);

// Rows
while ($row = $res->fetch_assoc()) {
    $name = $row['evacuee_name'] ?? 'Unknown';
    $resource = $row['resource_name'] ?? 'Unknown';
    $qty = isset($row['quantity']) ? (string)$row['quantity'] : '0';
    $unit = $row['unit'] ?? '';
    $date = !empty($row['date_received']) && $row['date_received'] !== '0000-00-00 00:00:00'
        ? date('Y-m-d H:i:s', strtotime($row['date_received']))
        : '';
    $center = $row['evacuation_center'] ?? '';

    $resourceWithUnit = trim($resource . ($unit !== '' ? ' ' . $unit : ''));
    fputcsv($output, [$name, $resourceWithUnit, $center, $qty, $date]);
}

fclose($output);
$stmt->close();
$conn->close();
exit;
?>


