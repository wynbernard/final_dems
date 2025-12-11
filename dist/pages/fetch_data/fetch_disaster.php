<?php
// Include the database connection file
include '../../../database/session.php'; // Adjust the path to your session/database connection file

try {
	// Query to fetch all disasters
	$query = "SELECT disaster_id, disaster_name FROM disaster_table ORDER BY disaster_name ASC";
	$result = $conn->query($query);

	if (!$result) {
		error_log("Database query failed in fetch_disaster.php: " . $conn->error);
		throw new Exception("Failed to fetch disasters");
	}

	// Fetch all rows as an associative array
	$disasters = [];
	while ($row = $result->fetch_assoc()) {
		$disasters[] = [
			'disaster_id' => (int)$row['disaster_id'],
			'disaster_name' => $row['disaster_name']
		];
	}

	// Return the disasters as a JSON response
	header('Content-Type: application/json');
	echo json_encode($disasters);
} catch (Exception $e) {
	// Handle errors and return an error message as JSON
	error_log("Error in fetch_disaster.php: " . $e->getMessage());
	header('Content-Type: application/json');
	echo json_encode(['error' => 'An error occurred while fetching disasters']);
}

// Note: Connection will be closed automatically when script ends
// No need to explicitly close unless you want to free resources early
