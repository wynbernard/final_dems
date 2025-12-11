<?php
// Include the database connection file
include '../../../database/session.php'; // Adjust the path to your session/database connection file

// CSRF Protection
require_once '../../../../database/csrf.php';
csrf_validate_or_die();

// Collect form data
$locationId = $_POST['location_id'] ?? '';
$preRegId = $_POST['pre_reg_id'] ?? '';
$room = $_POST['room'] ?? '';
$disasterId = $_POST['disasterDropdown'] ?? '';

// Validate required fields
if (empty($locationId) || empty($preRegId) || empty($room) || empty($disasterId)) {
	$_SESSION['error'] = "<span style='color: red;'><i class='bi bi-exclamation-triangle-fill'></i></span> All fields are required.";
	header('Location: ../admin_page/idps_user.php');
	exit();
}

// Prepare the SQL query to insert data into the database
$query = "INSERT INTO evac_reg_table (evac_loc_id, pre_reg_id, room_id, disaster_id, date_reg) 
          VALUES (?, ?, ?, ? , CURDATE())";

$stmt = $conn->prepare($query);
if (!$stmt) {
	error_log("Failed to prepare SQL statement in registration_staff.php: " . $conn->error);
	$_SESSION['error'] = "<span style='color: red;'><i class='bi bi-exclamation-triangle-fill'></i></span> Registration Failed!!!";
	header('Location: ../admin_page/idps_user.php');
	exit();
}

// Bind parameters to prevent SQL injection
$stmt->bind_param("isii", $locationId, $preRegId, $room, $disasterId);

// Execute the query
$executeSuccess = $stmt->execute();
if ($executeSuccess) {
	$_SESSION['success'] = "<span style='color: green;'><i class='bi bi-check-circle-fill'></i></span> Registration Successfull!!!"; 
	
	// Log the registration
	$logStatus = "In"; // Assuming "In" is the status for successful registration
	$querylog = "INSERT INTO logs_table (evac_reg_id, status , date_time) VALUES (?, ?, NOW())";
	$stmtlog = $conn->prepare($querylog);
	if ($stmtlog) {
		$evac_reg_id = $stmt->insert_id; // Get the ID of the newly inserted record
		$stmtlog->bind_param("is", $evac_reg_id, $logStatus);
		$stmtlog->execute();
		$stmtlog->close();
	}
} else {
	error_log("Registration failed in registration_staff.php: " . $stmt->error);
	$_SESSION['error'] = "<span style='color: red;'><i class='bi bi-exclamation-triangle-fill'></i></span> Registration Failed!!!";
}

// Close the statement
$stmt->close();

// Redirect with success/error message
header('Location: ../admin_page/idps_user.php?location_id=' . urlencode($locationId) . '&room=' . urlencode($room) . '&disaster_id=' . urlencode($disasterId));
exit();
?>