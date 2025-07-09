<?php
header('Content-Type: application/json');

// Database connection with error handling
try {
	$sessionPath = __DIR__ . '/../../../database/session.php';
	if (file_exists($sessionPath)) {
		include $sessionPath;
	} else {
		throw new Exception("Database configuration file not found");
	}

	if (!isset($conn) || !$conn instanceof mysqli) {
		throw new Exception("Database connection not established");
	}
} catch (Exception $e) {
	echo json_encode(['error' => $e->getMessage()]);
	exit;
}

$locationId = $_GET['location_id'] ?? 0;

try {
	$query = "SELECT 
                l.evac_loc_id,
                l.name AS location_name,
                r.room_id,
                r.room_name,
                r.room_capacity,
                COUNT(e.evac_reg_id) AS idp_count
            FROM evac_loc_table l
            LEFT JOIN room_table r ON l.evac_loc_id = r.evac_loc_id
            LEFT JOIN evac_reg_table e ON r.room_id = e.room_id
            WHERE l.evac_loc_id = ?
            GROUP BY l.evac_loc_id, l.name, r.room_id, r.room_name, r.room_capacity
            ORDER BY r.room_name ASC";

	$stmt = $conn->prepare($query);
	if (!$stmt) {
		throw new Exception("Prepare failed: " . $conn->error);
	}

	$stmt->bind_param("i", $locationId);
	$stmt->execute();
	$result = $stmt->get_result();

	$rooms = [];
	while ($row = $result->fetch_assoc()) {
		$rooms[] = [
			'location_id' => $row['evac_loc_id'],
			'location_name' => $row['location_name'],
			'id' => $row['room_id'],
			'name' => $row['room_name'],
			'capacity' => $row['room_capacity'],
			'current_occupancy' => $row['idp_count']
		];
	}

	echo json_encode($rooms);
} catch (Exception $e) {
	echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
