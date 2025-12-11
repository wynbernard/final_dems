<?php
include '../../../database/session.php'; // Adjust path to your database connection

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username'])) {
	$username = trim($_POST['username']);
	
	// Validate input
	if (empty($username)) {
		echo "invalid";
		exit();
	}
	
	if (strlen($username) < 3 || strlen($username) > 50) {
		echo "invalid";
		exit();
	}

	$query = "SELECT * FROM admin_table WHERE username = ?";
	$stmt = $conn->prepare($query);
	
	if (!$stmt) {
		error_log("Username validation query prepare failed: " . $conn->error);
		echo "error";
		exit();
	}
	
	$stmt->bind_param("s", $username);
	$stmt->execute();
	$result = $stmt->get_result();

	if ($result->num_rows > 0) {
		echo "taken"; // Ensure NO extra spaces or HTML tags
	} else {
		echo "available";
	}

	$stmt->close();
}
