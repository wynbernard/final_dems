<?php
// Include the database connection file
include '../../../database/session.php'; // Adjust the path to your session/database connection file

try {
	// Check if evac_loc_id is provided in the request
	if (!isset($_GET['evac_loc_id']) || empty($_GET['evac_loc_id'])) {
		throw new Exception("Location ID is required.");
	}

	// Get the evac_loc_id from the request
	$evac_loc_id = intval($_GET['evac_loc_id']); // Sanitize input to prevent SQL injection

	// Query to fetch rooms for the selected location
	$query = "SELECT room_id, room_name FROM room_table WHERE evac_loc_id = ?";
	$stmt = $conn->prepare($query);

	if (!$stmt) {
		throw new Exception("Failed to prepare query: " . $conn->error);
	}

	// Bind the parameter and execute the query
	$stmt->bind_param("i", $evac_loc_id);
	$stmt->execute();
	$result = $stmt->get_result();

	if (!$result) {
		throw new Exception("Query execution failed: " . $stmt->error);
	}

	// Fetch all rows as an associative array
	$rooms = [];
	while ($row = $result->fetch_assoc()) {
		$rooms[] = $row;
	}

	// Return the rooms as a JSON response
	header('Content-Type: application/json');
	echo json_encode($rooms);

	// Close the statement
	$stmt->close();
} catch (Exception $e) {
	// Handle errors and return an error message as JSON
	header('Content-Type: application/json');
	echo json_encode(['error' => $e->getMessage()]);
}

// Close the database connection
$conn->close();
