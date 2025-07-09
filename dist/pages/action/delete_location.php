<?php
include '../../../database/session.php'; // Ensure session and DB connection are included

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	// Get location ID from the form
	$evac_loc_id = intval($_POST['evac_loc_id']);

	// Check if the location exists
	$check_query = "SELECT * FROM evac_loc_table WHERE evac_loc_id = ?";
	$check_stmt = mysqli_prepare($conn, $check_query);
	mysqli_stmt_bind_param($check_stmt, "i", $evac_loc_id);
	mysqli_stmt_execute($check_stmt);
	$check_result = mysqli_stmt_get_result($check_stmt);

	if (mysqli_num_rows($check_result) > 0) {
		// Delete the location
		$delete_query = "DELETE FROM evac_loc_table WHERE evac_loc_id = ?";
		$delete_stmt = mysqli_prepare($conn, $delete_query);
		mysqli_stmt_bind_param($delete_stmt, "i", $evac_loc_id);

		if (mysqli_stmt_execute($delete_stmt)) {
			$_SESSION['success'] = "<span style='color: white;'><i class='bi bi-check-circle-fill'></i></span> Location deleted successfully!";
		} else {
			$_SESSION['error'] = "<span style='color:white;'><i class='bi bi-exclamation-circle-fill'></i></span> Delete failed: " . mysqli_error($conn);
		}
	} else {
		$_SESSION['error'] = "<span style='color:white;'><i class='bi bi-exclamation-circle-fill'></i></span> Location not found!";
	}

	header("Location: ../admin_page/loc_management.php");
	exit();
}
