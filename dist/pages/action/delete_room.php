<?php
include '../../../database/session.php'; // Include session and database connection
require_once '../../../database/csrf.php';

// Validate CSRF token
csrf_validate_or_die();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	// Get room ID
	$evac_loc_id = isset($_POST['evac_loc_id']) ? intval($_POST['evac_loc_id']) : 0; // Ensure evac_loc_id is set
	$room_id = isset($_POST['room_id']) ? intval($_POST['room_id']) : 0;

	if ($room_id === 0) {
		$_SESSION['error'] = "<span style='color:white;'><i class='bi bi-exclamation-circle-fill'></i></span> Invalid Room ID!";
		header("Location: ../admin_page/rooms.php?evac_loc_id=" . urlencode($evac_loc_id));
		exit();
	}

	// Fetch evac_loc_id before deleting the room
	$evac_loc_id = 0;
	$query = "SELECT evac_loc_id FROM room_table WHERE room_id = ?";
	$stmt = mysqli_prepare($conn, $query);
	mysqli_stmt_bind_param($stmt, "i", $room_id);
	mysqli_stmt_execute($stmt);
	mysqli_stmt_bind_result($stmt, $evac_loc_id);
	mysqli_stmt_fetch($stmt);
	mysqli_stmt_close($stmt);

	if ($evac_loc_id === 0) {
		$_SESSION['error'] = "<span style='color:white;'><i class='bi bi-exclamation-circle-fill'></i></span> Room not found!";
		header("Location: ../admin_page/rooms.php?evac_loc_id=" . urlencode($evac_loc_id));
		exit();
	}

	// Delete all IDPs registered in this room before deleting the room
	$delete_idps_query = "DELETE FROM evac_reg_table WHERE room_id = ?";
	$delete_idps_stmt = mysqli_prepare($conn, $delete_idps_query);
	mysqli_stmt_bind_param($delete_idps_stmt, "i", $room_id);
	mysqli_stmt_execute($delete_idps_stmt);
	mysqli_stmt_close($delete_idps_stmt);

	// Delete the room
	$query = "DELETE FROM room_table WHERE room_id = ?";
	$stmt = mysqli_prepare($conn, $query);
	mysqli_stmt_bind_param($stmt, "i", $room_id);

	if (mysqli_stmt_execute($stmt)) {
		$_SESSION['success'] = "<span style='color:green;'><i class='bi bi-check-circle-fill'></i></span> Room deleted successfully!";
	} else {
		error_log("Failed to delete room: " . mysqli_error($conn));
		$_SESSION['error'] = "<span style='color:red;'><i class='bi bi-exclamation-circle-fill'></i></span> Failed to delete room. Please try again.";
	}

	// Recalculate total_capacity for the evac location and update evac_loc_table
	$sumQuery = "SELECT COALESCE(SUM(room_capacity), 0) AS total_cap FROM room_table WHERE evac_loc_id = ?";
	if ($sumStmt = mysqli_prepare($conn, $sumQuery)) {
		mysqli_stmt_bind_param($sumStmt, 'i', $evac_loc_id);
		mysqli_stmt_execute($sumStmt);
		$sumRes = mysqli_stmt_get_result($sumStmt);
		if ($sumRes && $sumRow = mysqli_fetch_assoc($sumRes)) {
			$newTotalCap = intval($sumRow['total_cap']);
			$updateQuery = "UPDATE evac_loc_table SET total_capacity = ? WHERE evac_loc_id = ?";
			if ($updStmt = mysqli_prepare($conn, $updateQuery)) {
				mysqli_stmt_bind_param($updStmt, 'ii', $newTotalCap, $evac_loc_id);
				mysqli_stmt_execute($updStmt);
				mysqli_stmt_close($updStmt);
			}
		}
		mysqli_stmt_close($sumStmt);
	}

	// Redirect to the correct evac_loc_id
	header("Location: ../admin_page/rooms.php?evac_loc_id=" . urlencode($evac_loc_id));
	exit();
}
