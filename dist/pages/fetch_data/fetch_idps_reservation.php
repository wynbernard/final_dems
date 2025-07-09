<?php
include '../../../database/session.php';
header('Content-Type: application/json');

// Check if room_id is set in the query
if (isset($_GET['room_id']) && !empty($_GET['room_id'])) {
	$room_id = intval($_GET['room_id']);

	// Query to fetch all IDPs in this room
	$query = "
        SELECT 
			p.pre_reg_id,
             p.f_name AS first_name,
       		 p.l_name AS last_name,
			 e.date_time,
            TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) AS age, 
            p.gender,
			e.status,
            COUNT(f.family_id) AS family_size
        FROM room_reservation_table e
        LEFT JOIN pre_reg_table p ON e.pre_reg_id = p.pre_reg_id
        LEFT JOIN family_table f ON p.family_id = f.family_id
        WHERE e.room_id = ?
        GROUP BY p.pre_reg_id
    ";

	$stmt = $conn->prepare($query);
	$stmt->bind_param("i", $room_id);
	$stmt->execute();
	$result = $stmt->get_result();

	// Fetch all IDPs
	$idps = [];
	while ($row = $result->fetch_assoc()) {
		$idps[] = [
			'name' => ($row['first_name'] . ' ' . $row['last_name']),
			'age' => $row['age'],
			'gender' => $row['gender'],
			'pre_reg_id' => $row['pre_reg_id'],
			'family_members' => $row['family_size'],
			'reservation_date' => $row['date_time'] ? date('Y-m-d H:i:s', strtotime($row['date_time'])) : null,
			'status' => $row['status'] ?? 'N/A', // Assuming status is a column in your table
		];
	}

	// Return data as JSON
	echo json_encode(['idps' => $idps]);
} else {
	// Invalid or missing room_id
	echo json_encode(['error' => 'Room ID is missing or invalid']);
}
