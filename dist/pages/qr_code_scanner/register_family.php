<?php
header("Content-Type: application/json");
include '../../../database/session.php'; // Assumes $conn (MySQLi) is set

try {
	// Decode the incoming JSON request
	$data = json_decode(file_get_contents("php://input"), true);

	// Extract parameters from the request
	$roomId = $data['room_id'] ?? null;
	$memberIds = $data['member_ids'] ?? [];
	$locationId = $data['location_id'] ?? null;

	// Check if all required parameters are present
	if (!$roomId || !is_array($memberIds) || count($memberIds) === 0 || !$locationId) {
		http_response_code(400);
		throw new Exception("Invalid input data: missing room, members, or location.");
	}

	// Begin the transaction
	$conn->begin_transaction();

	$successCount = 0;
	$skipped = [];

	// Prepare statements
	$checkStmt = $conn->prepare("SELECT pre_reg_id FROM evac_reg_table WHERE pre_reg_id = ?");
	$insertStmt = $conn->prepare("INSERT INTO evac_reg_table (room_id, pre_reg_id, evac_loc_id, date_reg) VALUES (?, ?, ?, CURDATE())");
	$logStmt = $conn->prepare("INSERT INTO logs_table (evac_reg_id, status, date_time) VALUES (?, ?, NOW())");

	if (!$checkStmt || !$insertStmt || !$logStmt) {
		throw new Exception("Statement preparation failed: " . $conn->error);
	}

	// Loop through the selected members and register them
	foreach ($memberIds as $memberId) {
		// Check if the member is already registered
		$checkStmt->bind_param("i", $memberId);
		$checkStmt->execute();
		$checkStmt->store_result();

		if ($checkStmt->num_rows === 0) {
			// Register the member
			$insertStmt->bind_param("iii", $roomId, $memberId, $locationId);
			if (!$insertStmt->execute()) {
				throw new Exception("Insert failed for member ID $memberId: " . $insertStmt->error);
			}
			$evacRegId = $insertStmt->insert_id;

			// Log the registration
			$status = 'In';
			$logStmt->bind_param("is", $evacRegId, $status);
			if (!$logStmt->execute()) {
				throw new Exception("Logging failed for evac_reg_id $evacRegId: " . $logStmt->error);
			}

			$successCount++;
		} else {
			$skipped[] = $memberId;
		}
	}

	// Commit the transaction
	$conn->commit();

	echo json_encode([
		'success' => true,
		'success_count' => $successCount,
		'skipped_members' => $skipped,
		'message' => 'Members registered successfully.'
	]);
} catch (Exception $e) {
	if ($conn->errno) $conn->rollback();
	error_log("Registration Error: " . $e->getMessage());
	http_response_code(500);
	echo json_encode([
		'success' => false,
		'error' => $e->getMessage()
	]);
}

$conn->close();
