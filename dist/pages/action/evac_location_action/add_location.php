<?php
include '../../../../database/session.php'; // Include session and database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	// Get and sanitize input values
	$location_name = trim($_POST['location_name']);
	$city = isset($_POST['city']) ? trim($_POST['city']) : '';
	$barangay = isset($_POST['barangay']) ? trim($_POST['barangay']) : '';
	$purok = isset($_POST['purok']) ? trim($_POST['purok']) : '';
	$total_capacity = isset($_POST['total_capacity']) ? intval($_POST['total_capacity']) : 0;
	$latitude = $_POST['latitude'];
	$longitude = $_POST['longitude'];

	// Validate input data
	if (empty($location_name) || empty($city) || empty($barangay) || empty($purok) || $total_capacity <= 0) {
		$_SESSION['error'] = "<span style='color:white;'><i class='bi bi-exclamation-circle-fill'></i> Please fill in all fields correctly.</span>";
		header("Location: ../../admin_page/loc_management.php");
		exit();
	}

	// Check if the Barangay exist
	$check_sql = "SELECT barangay_id FROM barangay_manegement_table WHERE barangay_name = ?";
	$check_stmt = $conn->prepare($check_sql);
	$check_stmt->bind_param("s", $barangay);
	$check_stmt->execute();
	$check_stmt->store_result();

	if ($check_stmt->num_rows > 0) {
		// Barangay already exists, get the ID
		$check_stmt->bind_result($barangay_id);
		$check_stmt->fetch();
	} else {
		// Insert new barangay
		$barangay_sql = "INSERT INTO barangay_manegement_table (barangay_name) VALUES (?)";
		$barangay_stmt = $conn->prepare($barangay_sql);
		$barangay_stmt->bind_param("s", $barangay);

		if ($barangay_stmt->execute()) {
			$barangay_id = $barangay_stmt->insert_id;
		} else {
			$_SESSION['error'] = "Failed to insert barangay!";
			header("Location: ../../admin_page/loc_management.php");
			exit();
		}
	}


	// Check for duplicate location name
	$check_query = "SELECT evac_loc_id FROM evac_loc_table WHERE name = ?";
	$check_stmt = mysqli_prepare($conn, $check_query);
	mysqli_stmt_bind_param($check_stmt, "s", $location_name);
	mysqli_stmt_execute($check_stmt);
	mysqli_stmt_store_result($check_stmt);

	if (mysqli_stmt_num_rows($check_stmt) > 0) {
		$_SESSION['error'] = "<span style='color:white;'><i class='bi bi-exclamation-circle-fill'></i> Location name already exists.</span>";
		header("Location: ../../admin_page/loc_management.php");
		exit();
	}
	mysqli_stmt_close($check_stmt);

	// Insert new location into the database
	$query = "INSERT INTO evac_loc_table (name, city ,barangay_id, purok, total_capacity, latitude , longitude) VALUES (?, ?, ?, ?, ?, ?, ?)";

	$stmt = mysqli_prepare($conn, $query);

	if ($stmt) {
		mysqli_stmt_bind_param($stmt, "ssisiss", $location_name, $city, $barangay_id, $purok, $total_capacity, $latitude, $longitude);
		$execute = mysqli_stmt_execute($stmt);

		if ($execute) {
			$_SESSION['success'] = "<span style='color:black;'><i class='bi bi-check-circle-fill'></i> Location added successfully!</span>";
		} else {
			$_SESSION['error'] = "<span style='color:white;'><i class='bi bi-exclamation-circle-fill'></i> Failed to add location.</span>";
		}

		mysqli_stmt_close($stmt);
	} else {
		$_SESSION['error'] = "<span style='color:black;'><i class='bi bi-exclamation-circle-fill'></i> Database error: " . mysqli_error($conn) . "</span>";
	}

	// Redirect back to locations page
	header("Location: ../../admin_page/loc_management.php");
	exit();
}
