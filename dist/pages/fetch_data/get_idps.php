<?php
include '../../../database/session.php';

// Get the selected room ID
$room_id = isset($_GET['room_id']) ? intval($_GET['room_id']) : 0;

// Fetch IDP details for the selected room where age class is 'infant'
$query = "
    SELECT p.f_name, p.l_name, a.classification
    FROM evac_reg_table e
    LEFT JOIN pre_reg_table p ON e.pre_reg_id = p.pre_reg_id
    LEFT JOIN age_class_table a ON p.age_class_id = a.age_class_id
    WHERE e.room_id = ? AND a.classification != 'Infant'
";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $room_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);


$idps = [];
while ($row = mysqli_fetch_assoc($result)) {
    $idps[] = $row;
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($idps);
