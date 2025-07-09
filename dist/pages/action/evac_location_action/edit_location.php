<?php
include '../../../../database/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// Sanitize input
	$evac_loc_id = intval($_POST['evac_loc_id']);
	$location_name = trim($_POST['location_name']);
	$location_city = trim($_POST['location_city']);
	$barangay_name = trim($_POST['barangay']); // This is the name
	$location_purok = trim($_POST['location_purok']);
	$total_capacity = intval($_POST['total_capacity']);
	$latitude = floatval($_POST['latitude']);
	$longitude = floatval($_POST['longitude']);

	// Validate required fields
	if (
		empty($evac_loc_id) || empty($location_name) || empty($location_city) || empty($location_purok) || empty($total_capacity)
	) {
		die("Missing required fields.");
	}

	// Get barangay_id from barangay_manegement_table
	$getBrgy = $conn->prepare("SELECT barangay_id FROM barangay_manegement_table WHERE barangay_name = ?");
	$getBrgy->bind_param("s", $barangay_name);
	$getBrgy->execute();
	$getBrgy->bind_result($barangay_id);
	$getBrgy->fetch();
	$getBrgy->close();

	if (empty($barangay_id)) {
		$_SESSION['error'] = "<span style='color:white;'><i class='bi bi-exclamation-circle-fill'></i> Barangay not found.</span>";
		header("Location: ../../admin_page/loc_management.php");
		exit();
	}

	// Update query
	$query = "UPDATE evac_loc_table SET 
					name = ?,
					city = ?,
					barangay_id = ?,
					purok = ?,
					total_capacity = ?,
					latitude = ?,
					longitude = ?
			  WHERE evac_loc_id = ?";

	$stmt = $conn->prepare($query);
	if ($stmt === false) {
		die("Prepare failed: " . htmlspecialchars($conn->error));
	}

	$stmt->bind_param(
		"sssidddi",
		$location_name,
		$location_city,
		$barangay_id,      // Use the ID, not name
		$location_purok,
		$total_capacity,
		$latitude,
		$longitude,
		$evac_loc_id
	);

	if ($stmt->execute()) {
		$_SESSION['success'] = "<span style='color:black;'><i class='bi bi-check-circle-fill'></i> Location updated successfully!</span>";
	} else {
		$_SESSION['error'] = "<span style='color:white;'><i class='bi bi-exclamation-circle-fill'></i> Failed to update location.</span>";
	}

	$stmt->close();
	header("Location: ../../admin_page/loc_management.php");
	exit();
} else {
	echo "Invalid request method.";
}
