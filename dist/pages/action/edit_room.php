<?php
include '../../../database/session.php'; // Ensure session and DB connection are included

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	// Get form data
	$evac_loc_id = isset($_POST['evac_loc_id']) ? intval($_POST['evac_loc_id']) : 0;
	$room_id = isset($_POST['room_id']) ? intval($_POST['room_id']) : 0;
	$room_name = isset($_POST['room_name']) ? mysqli_real_escape_string($conn, $_POST['room_name']) : '';
	$room_capacity = isset($_POST['room_capacity']) ? intval($_POST['room_capacity']) : 0;

	// Validate input
	if ($evac_loc_id === 0 || $room_id === 0 || empty($room_name)) {
		$_SESSION['error'] = "<span style='color:white;'><i class='bi bi-exclamation-circle-fill'></i></span> Invalid data provided!";
		header("Location: ../admin_page/rooms.php");
		exit();
	}

	// Start transaction
	mysqli_begin_transaction($conn);

	try {
		// Update the room
		$query = "UPDATE room_table SET room_name = ?, room_capacity = ? WHERE room_id = ?";
		$stmt = mysqli_prepare($conn, $query);
		mysqli_stmt_bind_param($stmt, "sii", $room_name, $room_capacity, $room_id);

		if (!mysqli_stmt_execute($stmt)) {
			throw new Exception("Room update failed: " . mysqli_error($conn));
		}

		// Calculate new total capacity for the location
		$capacity_query = "SELECT SUM(room_capacity) as total_capacity FROM room_table WHERE evac_loc_id = ?";
		$stmt_capacity = mysqli_prepare($conn, $capacity_query);
		mysqli_stmt_bind_param($stmt_capacity, "i", $evac_loc_id);
		mysqli_stmt_execute($stmt_capacity);
		$result = mysqli_stmt_get_result($stmt_capacity);
		$row = mysqli_fetch_assoc($result);
		$total_capacity = $row['total_capacity'] ?? 0;

		// Update the location's capacity
		$update_loc_query = "UPDATE evac_loc_table SET total_capacity = ? WHERE evac_loc_id = ?";
		$stmt_loc = mysqli_prepare($conn, $update_loc_query);
		mysqli_stmt_bind_param($stmt_loc, "ii", $total_capacity, $evac_loc_id);

		if (!mysqli_stmt_execute($stmt_loc)) {
			throw new Exception("Location capacity update failed: " . mysqli_error($conn));
		}

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
