<?php
include '../../../database/session.php'; // Adjust the path to your session/database connection file

// Debugging: Check if the connection is successful
if (!$conn) {
	die("Database connection failed: " . mysqli_connect_error());
}

try {
	$query = "SELECT evac_loc_id, name FROM evac_loc_table";
	$result = mysqli_query($conn, $query);

	if (!$result) {
		throw new Exception("Query failed: " . mysqli_error($conn));
	}

	$locations = [];
	while ($row = mysqli_fetch_assoc($result)) {
		$locations[] = $row;
	}

	header('Content-Type: application/json');
	echo json_encode($locations);
} catch (Exception $e) {
	header('Content-Type: application/json');
	echo json_encode(['error' => $e->getMessage()]);
}
