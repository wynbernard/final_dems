<?php

include '../../../database/user_session.php';

// Ensure the user is logged in
if (!isset($_SESSION['pre_reg_id'])) {
	$_SESSION['error'] = 'Please log in to proceed.';
	header("Location: ../user_page/room_reservation.php");
	exit();
}

$pre_reg_id = $_SESSION['pre_reg_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['room_id']) && !empty($_POST['room_id'])) {
	$room_id = $_POST['room_id'];

	// Step 1: Get the family code for the logged-in user
	$family_query = $conn->prepare("SELECT family_id FROM pre_reg_table WHERE pre_reg_id = ?");
	$family_query->bind_param("i", $pre_reg_id);
	$family_query->execute();
	$family_result = $family_query->get_result();

	if ($family_result->num_rows === 1) {
		$family_code = $family_result->fetch_assoc()['family_id'];

		// Step 2: Update all reservations for this family for the given room
		$update_stmt = $conn->prepare("
			UPDATE room_reservation_table 
			JOIN pre_reg_table ON room_reservation_table.pre_reg_id = pre_reg_table.pre_reg_id 
			SET room_reservation_table.status = 'Intended' 
			WHERE pre_reg_table.family_id = ? AND room_reservation_table.room_id = ?
		");
		$update_stmt->bind_param("si", $family_code, $room_id);
		$update_stmt->execute();

		if ($update_stmt->affected_rows > 0) {
			$_SESSION['success'] = 'Intent to go has been recorded for your family.';
		} else {
			$_SESSION['error'] = 'No matching reservations found or already marked as intended.';
		}
	} else {
		$_SESSION['error'] = 'Family information not found.';
	}
} else {
	$_SESSION['error'] = 'Invalid request.';
}

// Redirect back
header("Location: ../user_page/room_reservation.php");
exit();
