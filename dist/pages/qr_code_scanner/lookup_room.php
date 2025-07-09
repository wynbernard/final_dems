<?php
header('Content-Type: application/json');

// Error reporting for development (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

include '../../../database/session.php';

try {
	// Validate input
	if (!isset($_GET['pre_reg_id'])) {
		throw new Exception('Missing pre_reg_id parameter');
	}

	$pre_reg_id = filter_var($_GET['pre_reg_id'], FILTER_VALIDATE_INT);
	if ($pre_reg_id === false || $pre_reg_id <= 0) {
		throw new Exception('Invalid pre_reg_id format');
	}

	// Prepare SQL query
	$sql = "
        SELECT r.room_id
        FROM room_reservation_table r
        LEFT JOIN pre_reg_table p ON r.pre_reg_id = p.pre_reg_id
        LEFT JOIN family_table f ON p.family_id = f.family_id
        WHERE r.pre_reg_id = ?
    ";

	$stmt = $conn->prepare($sql);
	if (!$stmt) {
		throw new Exception('Prepare failed: ' . $conn->error);
	}

	// Bind and execute
	$stmt->bind_param("i", $pre_reg_id);
	if (!$stmt->execute()) {
		throw new Exception('Execute failed: ' . $stmt->error);
	}

	$result = $stmt->get_result();

	if ($row = $result->fetch_assoc()) {
		$response = [
			'success' => true,
			'room_id' => (int)$row['room_id']
		];
	} else {
		$response = [
			'success' => false,
			'message' => 'No room found for this pre_reg_id'
		];
	}

	echo json_encode($response);
} catch (Exception $e) {
	http_response_code(400);
	echo json_encode([
		'success' => false,
		'message' => $e->getMessage()
	]);
} finally {
	// Clean up resources
	if (isset($stmt)) $stmt->close();
	if (isset($conn)) $conn->close();
}
