<?php
// fetch_idps.php

include '../../../database/session.php'; // Your database connection

$room_id = intval($_GET['room_id'] ?? 0);

if ($room_id > 0) {
	$stmt = $conn->prepare("
          SELECT 
            e.evac_reg_id,
            CONCAT(p.f_name, ' ', p.l_name) AS full_name,
            p.gender,
            TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) AS age,
            e.date_reg AS arrival_date,
            f.family_id
        FROM evac_reg_table e
        LEFT JOIN pre_reg_table p ON e.pre_reg_id = p.pre_reg_id
        LEFT JOIN family_table f ON p.family_id = f.family_id
        WHERE e.room_id = ?
    ");


	$stmt->bind_param("i", $room_id);
	$stmt->execute();
	$result = $stmt->get_result();

	$residents = [];
	while ($row = $result->fetch_assoc()) {
		$residents[] = $row;
	}

	echo json_encode($residents);
} else {
	echo json_encode([]);
}
