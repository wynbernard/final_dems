<?php
// Include the database connection file
include '../../../database/session.php'; // Adjust the path to your session/database connection file

// Collect form data
$locationId = $_POST['location_id'] ?? '';
$preRegId = $_POST['pre_reg_id'] ?? '';
$room = $_POST['room'] ?? '';
$disasterId = $_POST['disasterDropdown'] ?? '';

// Validate required fields
if (empty($locationId) || empty($preRegId) || empty($room) || empty($disasterId)) {
	die("Error: All fields are required.");
}

// Prepare the SQL query to insert data into the database
$query = "INSERT INTO evac_reg_table (evac_loc_id, pre_reg_id, room_id, disaster_id, date_reg) 
          VALUES (?, ?, ?, ? , CURDATE())";

$stmt = $conn->prepare($query);
if (!$stmt) {
	die("Error: Failed to prepare SQL statement.");
}

// Bind parameters to prevent SQL injection
$stmt->bind_param("isii", $locationId, $preRegId, $room, $disasterId);

// Execute the query
if ($stmt->execute()) {
$_SESSION['success'] = "<span style='color: green;'><i class='bi bi-check-circle-fill'></i></span> Registration Successfull!!!";
	echo json_encode(['success' => true, 'message' => 'IDP registered successfully!']);
} else {
	$_SESSION['error'] = "<span style='color: red;'><i class='bi bi-exclamation-triangle-fill'></i></span> Registration Failed!!!";
}

$logStatus = "In"; // Assuming "In" is the status for successful registration

$querylog = "INSERT INTO logs_table (evac_reg_id, status , date_time) VALUES (?, ?, NOW())";
$stmtlog = $conn->prepare($querylog);
$evac_reg_id = $stmt->insert_id; // Get the ID of the newly inserted record
$stmtlog->bind_param("is", $evac_reg_id, $logStatus);
$stmtlog->execute();
$stmtlog->close();
// Close the statement and database connection
$stmt->close();
$conn->close();
header('Location: ../admin_page/idps_user.php?location_id=' . $locationId . '&room=' . $room . '&disaster_id=' . $disasterId);
exit;
?>
// echo json_encode(['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()]);