<?php
header('Content-Type: application/json');
include '../../../database/session.php'; // Adjust the path to your session/database connection file

try {
	// Query to fetch pre-registered IDPs who are not yet registered
	$query = "SELECT pr.pre_reg_id, pr.f_name, pr.l_name
              FROM pre_reg_table AS pr
              LEFT JOIN evac_reg_table AS er ON pr.pre_reg_id = er.pre_reg_id
              WHERE er.pre_reg_id IS NULL";

	$result = mysqli_query($conn, $query);

	if (!$result) {
		throw new Exception("Query failed: " . mysqli_error($conn));
	}

	// Fetch all rows as an associative array
	$idps = [];
	while ($row = mysqli_fetch_assoc($result)) {
		$idps[] = $row;
	}

	// Return the IDPs as a JSON response
	header('Content-Type: application/json');
	echo json_encode($idps);
} catch (Exception $e) {
	// Handle errors and return an error message as JSON
	header('Content-Type: application/json');
	echo json_encode(['error' => $e->getMessage()]);
}

// Close the database connection
$conn->close();
