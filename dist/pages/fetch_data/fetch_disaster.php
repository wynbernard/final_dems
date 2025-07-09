<?php
// Include the database connection file
include '../../../database/session.php'; // Adjust the path to your session/database connection file

try {
	// Query to fetch all disasters
	$query = "SELECT disaster_id, disaster_name FROM disaster_table";
	$result = mysqli_query($conn, $query);

	if (!$result) {
		throw new Exception("Query failed: " . mysqli_error($conn));
	}

	// Fetch all rows as an associative array
	$disasters = [];
	while ($row = mysqli_fetch_assoc($result)) {
		$disasters[] = $row;
	}

	// Return the disasters as a JSON response
	header('Content-Type: application/json');
	echo json_encode($disasters);
} catch (Exception $e) {
	// Handle errors and return an error message as JSON
	header('Content-Type: application/json');
	echo json_encode(['error' => $e->getMessage()]);
}

// Close the database connection
$conn->close();
