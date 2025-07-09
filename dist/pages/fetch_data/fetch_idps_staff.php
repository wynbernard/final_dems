<?php
// fetch_suggestions.php
header('Content-Type: application/json');

// Database connection
include '../../../database/session.php'; // Adjust the path to your session/database connection file

$query = $_GET['query'] ?? '';
$results = [];

if (strlen($query) >= 2) { // Only search if query is at least 2 characters
	// Sanitize the query to prevent SQL injection
	$searchQuery = "%" . $conn->real_escape_string($query) . "%";

	// Corrected query to search in both last name and first name
	$sql = "SELECT p.pre_reg_id, CONCAT(p.f_name, ' ', p.l_name) AS name 
            FROM pre_reg_table p
            LEFT JOIN evac_reg_table e ON p.pre_reg_id = e.pre_reg_id
            WHERE e.pre_reg_id IS NULL 
              AND (p.f_name LIKE ? OR p.l_name LIKE ?)
            LIMIT 10";

	// Prepare the statement
	$stmt = $conn->prepare($sql);
	if (!$stmt) {
		die(json_encode(["error" => "Failed to prepare the SQL statement"]));
	}

	// Bind parameters
	$stmt->bind_param("ss", $searchQuery, $searchQuery); // Two parameters for both fields
	$stmt->execute();
	$result = $stmt->get_result();

	// Fetch results
	while ($row = $result->fetch_assoc()) {
		$results[] = [
			'id' => $row['pre_reg_id'],
			'name' => $row['name']
		];
	}

	$stmt->close();
}

echo json_encode($results);
