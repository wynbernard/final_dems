<?php
include '../../../database/session.php'; // Ensure session and DB connection are included
require_once '../../../database/csrf.php';

// Validate CSRF token
csrf_validate_or_die();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	// Get form data
	$evac_loc_id = isset($_POST['evac_loc_id']) ? intval($_POST['evac_loc_id']) : 0;
	$room_id = isset($_POST['room_id']) ? intval($_POST['room_id']) : 0;
	$room_name = isset($_POST['room_name']) ? trim($_POST['room_name']) : '';
	$room_capacity = isset($_POST['room_capacity']) ? intval($_POST['room_capacity']) : 0;

	// Validate input
	if ($evac_loc_id <= 0 || $room_id <= 0) {
		$_SESSION['error'] = "<span style='color:white;'><i class='bi bi-exclamation-circle-fill'></i></span> Invalid location or room ID provided!";
		header("Location: ../admin_page/rooms.php");
		exit();
	}
	if (empty($room_name)) {
		$_SESSION['error'] = "<span style='color:white;'><i class='bi bi-exclamation-circle-fill'></i></span> Room name is required!";
		header("Location: ../admin_page/rooms.php?evac_loc_id=" . urlencode($evac_loc_id));
		exit();
	}
	if (strlen($room_name) > 100) {
		$_SESSION['error'] = "<span style='color:white;'><i class='bi bi-exclamation-circle-fill'></i></span> Room name must be 100 characters or less!";
		header("Location: ../admin_page/rooms.php?evac_loc_id=" . urlencode($evac_loc_id));
		exit();
	}
	if ($room_capacity <= 0) {
		$_SESSION['error'] = "<span style='color:white;'><i class='bi bi-exclamation-circle-fill'></i></span> Room capacity must be greater than 0!";
		header("Location: ../admin_page/rooms.php?evac_loc_id=" . urlencode($evac_loc_id));
		exit();
	}

	// Start transaction
	mysqli_begin_transaction($conn);

	try {
		// Update the room
		$query = "UPDATE room_table SET room_name = ?, room_capacity = ? WHERE room_id = ?";
		$stmt = $conn->prepare($query);
		
		if (!$stmt) {
			error_log("Edit room prepare failed: " . $conn->error);
			throw new Exception("Failed to update room. Please try again.");
		}
		
		$stmt->bind_param("sii", $room_name, $room_capacity, $room_id);

		if (!$stmt->execute()) {
			error_log("Edit room execute failed: " . $stmt->error);
			throw new Exception("Failed to update room. Please try again.");
		}
		$stmt->close();

		// Calculate new total capacity for the location
		$capacity_query = "SELECT SUM(room_capacity) as total_capacity FROM room_table WHERE evac_loc_id = ?";
		$stmt_capacity = $conn->prepare($capacity_query);
		
		if (!$stmt_capacity) {
			error_log("Capacity query prepare failed: " . $conn->error);
			throw new Exception("Failed to calculate location capacity. Please try again.");
		}
		
		$stmt_capacity->bind_param("i", $evac_loc_id);
		$stmt_capacity->execute();
		$result = $stmt_capacity->get_result();
		$row = $result->fetch_assoc();
		$total_capacity = $row['total_capacity'] ?? 0;
		$stmt_capacity->close();

		// Update the location's capacity
		$update_loc_query = "UPDATE evac_loc_table SET total_capacity = ? WHERE evac_loc_id = ?";
		$stmt_loc = $conn->prepare($update_loc_query);
		
		if (!$stmt_loc) {
			error_log("Location update prepare failed: " . $conn->error);
			throw new Exception("Failed to update location capacity. Please try again.");
		}
		
		$stmt_loc->bind_param("ii", $total_capacity, $evac_loc_id);

		if (!$stmt_loc->execute()) {
			error_log("Location update execute failed: " . $stmt_loc->error);
			throw new Exception("Failed to update location capacity. Please try again.");
		}
		$stmt_loc->close();

		// Commit transaction
		mysqli_commit($conn);
		$_SESSION['success'] = "<span style='color: green;'><i class='bi bi-check-circle-fill'></i></span> Room and location capacity updated successfull!";
	} catch (Exception $e) {
		// Rollback transaction on error
		mysqli_rollback($conn);
		$_SESSION['error'] = "<span style='color:red;'><i class='bi bi-exclamation-circle-fill'></i></span> " . $e->getMessage();
	}

	// Redirect back
	header("Location: ../admin_page/rooms.php?evac_loc_id=" . urlencode($evac_loc_id));
	exit();
}
