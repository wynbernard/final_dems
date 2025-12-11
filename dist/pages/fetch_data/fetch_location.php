<?php
include '../../../database/session.php'; // Adjust the path to your session/database connection file

// Check if the connection is successful
if (!$conn) {
	error_log("Database connection failed in fetch_location.php");
	header('Content-Type: application/json');
	echo json_encode(['error' => 'Database connection failed']);
	exit();
}

try {
	$query = "SELECT evac_loc_id, name FROM evac_loc_table ORDER BY name ASC";
	$result = $conn->query($query);

	if (!$result) {
		error_log("Database query failed in fetch_location.php: " . $conn->error);
		throw new Exception("Failed to fetch locations");
	}

	$locations = [];
	while ($row = $result->fetch_assoc()) {
		$locations[] = [
			'evac_loc_id' => (int)$row['evac_loc_id'],
			'name' => $row['name']
		];
	}

	header('Content-Type: application/json');
	echo json_encode($locations);
} catch (Exception $e) {
	error_log("Error in fetch_location.php: " . $e->getMessage());
	header('Content-Type: application/json');
	echo json_encode(['error' => 'An error occurred while fetching locations']);
}
