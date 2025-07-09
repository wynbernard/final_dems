<?php
include '../../../database/session.php'; // Ensure session and DB connection are included

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	// Get form data
	$evac_loc_id = intval($_POST['evac_loc_id']); // Selected location ID
	$room_name = mysqli_real_escape_string($conn, $_POST['room_name']);
	$room_capacity = intval($_POST['room_capacity']);

	// Insert the new room into the database under the selected location
	$query = "INSERT INTO room_table (evac_loc_id, room_name, room_capacity) VALUES (?, ?, ?)";
	$stmt = mysqli_prepare($conn, $query);
	mysqli_stmt_bind_param($stmt, "isi", $evac_loc_id, $room_name, $room_capacity);

	if (mysqli_stmt_execute($stmt)) {
		$_SESSION['success'] = "<span style='color: green;'><i class='bi bi-check-circle-fill'></i></span> Add room Successfull!!!";
		header("Location: ../admin_page/rooms.php?evac_loc_id=$evac_loc_id");
		exit();
	} else {
		$_SESSION['error'] = "<span style='color:red;'><i class='bi bi-exclamation-circle-fill'></i></span> Add room Failed!!!" . mysqli_error($conn);
		header("Location: ../admin_page/rooms.php?evac_loc_id=$evac_loc_id");
		exit();
	}
}
