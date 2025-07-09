<?php
include '../../../database/session.php';
header('Content-Type: application/json');

// For debugging (disable in production)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// For production
ini_set('display_errors', 0);
error_reporting(0);

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	echo json_encode(['success' => false, 'message' => 'Only POST method allowed']);
	exit;
}

// Check if required parameters are present
if (!isset($_POST['pre_reg_id'], $_POST['room_id'], $_POST['date_reg'])) {
	http_response_code(400);
	echo json_encode(['success' => false, 'message' => 'Missing required fields']);
	exit;
}

// Validate and sanitize inputs
$room_id = filter_var($_POST['room_id'], FILTER_VALIDATE_INT);
$date_reg = date('Y-m-d H:i:s', strtotime($_POST['date_reg'])); // Ensure proper date format

if ($room_id === false || $room_id <= 0) {
	http_response_code(400);
	echo json_encode(['success' => false, 'message' => 'Invalid room_id']);
	exit;
}

// Decode and validate pre_reg_ids
$pre_reg_ids = json_decode($_POST['pre_reg_id']);

if (json_last_error() !== JSON_ERROR_NONE || !is_array($pre_reg_ids)) {
	http_response_code(400);
	echo json_encode(['success' => false, 'message' => 'Invalid pre_reg_id format. Expected JSON array']);
	exit;
}

// Sanitize all pre_reg_ids
$pre_reg_ids = array_filter(array_map(function ($id) {
	return filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
}, $pre_reg_ids));

if (empty($pre_reg_ids)) {
	http_response_code(400);
	echo json_encode(['success' => false, 'message' => 'No valid pre_reg_ids provided']);
	exit;
}

// Prepare SQL statement
$sql = "INSERT INTO evac_reg_table (pre_reg_id, room_id, date_reg) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
	http_response_code(500);
	echo json_encode(['success' => false, 'message' => 'Database prepare failed: ' . $conn->error]);
	exit;
}

// Begin transaction
$conn->begin_transaction();

try {
	foreach ($pre_reg_ids as $pre_reg_id) {
		$stmt->bind_param("iis", $pre_reg_id, $room_id, $date_reg);
		if (!$stmt->execute()) {
			throw new Exception('Execute failed for pre_reg_id ' . $pre_reg_id . ': ' . $stmt->error);
		}
	}

	$conn->commit();
	echo json_encode(['success' => true, 'message' => 'Registration successful for all members']);
} catch (Exception $e) {
	$conn->rollback();
	http_response_code(500);
	echo json_encode(['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()]);
} finally {
	// Always clean up
	if (isset($stmt)) $stmt->close();
	if (isset($conn)) $conn->close();
}
