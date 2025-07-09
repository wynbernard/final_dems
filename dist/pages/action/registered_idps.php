<?php
// Include the database connection file
include '../../../database/session.php'; // Adjust the path to your session/database connection file

try {
	// Check if all required fields are provided
	if (
		!isset($_POST['pre_reg_id']) || empty($_POST['pre_reg_id']) ||
		!isset($_POST['evac_loc_id']) || empty($_POST['evac_loc_id']) ||
		!isset($_POST['room_id']) || empty($_POST['room_id']) ||
		!isset($_POST['disaster_id']) || empty($_POST['disaster_id'])
	) {
		throw new Exception("All fields are required.");
	}

	// Sanitize input to prevent SQL injection
	$pre_reg_id = intval($_POST['pre_reg_id']);
	$evac_loc_id = intval($_POST['evac_loc_id']);
	$room_id = intval($_POST['room_id']);
	$disaster_id = intval($_POST['disaster_id']);

	// Insert into evac_reg_table
	$query = "INSERT INTO evac_reg_table (pre_reg_id, evac_loc_id, room_id, disaster_id, date_reg)
              VALUES (?, ?, ?, ?, CURDATE())";

	$stmt = $conn->prepare($query);

	if (!$stmt) {
		throw new Exception("Failed to prepare query: " . $conn->error);
	}

	// Bind parameters and execute the query
	$stmt->bind_param("iiii", $pre_reg_id, $evac_loc_id, $room_id, $disaster_id);

	if ($stmt->execute()) {
		// Get the ID of the newly inserted record
		$evac_reg_id = $stmt->insert_id;

		// Log success
		$logAction = "Register IDP";
		$logStatus = "In";
		$logMessage = "IDP registered successfully. Pre-reg ID: $pre_reg_id, Location ID: $evac_loc_id, Room ID: $room_id, Disaster ID: $disaster_id";

		// Return success response
		echo json_encode(['success' => true, 'message' => 'IDP registered successfully!']);
		$_SESSION['success'] = "✅ Registration Successful!";
	} else {
		throw new Exception("Query execution failed: " . $stmt->error);
	}

	// Close the statement
	$stmt->close();
} catch (Exception $e) {
	// Log failure
	$logAction = "Register IDP";
	$logStatus = "failure";
	$logMessage = $e->getMessage();

	// Handle errors and return an error message as JSON
	header('Content-Type: application/json');
	echo json_encode(['error' => $e->getMessage()]);
}

// Insert log entry into logs_table
try {
	$logQuery = "INSERT INTO logs_table (evac_reg_id, status ,date_time) VALUES (?, ? , NOW())";
	$logStmt = $conn->prepare($logQuery);

	if (!$logStmt) {
		throw new Exception("Failed to prepare log query: " . $conn->error);
	}

	// If registration was successful, include the evac_reg_id; otherwise, set it to NULL
	$evac_reg_id = isset($evac_reg_id) ? $evac_reg_id : null;
	$logStmt->bind_param("is", $evac_reg_id, $logStatus);
	$logStmt->execute();
	$logStmt->close();
} catch (Exception $logError) {
	// If logging fails, log it to the server error log
	error_log("Failed to log registration attempt: " . $logError->getMessage());
}

// Close the database connection
$conn->close();
