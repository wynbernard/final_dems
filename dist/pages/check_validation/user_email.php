<?php
include '../../../database/conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$email = trim($_POST['email_address']);

	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		echo "invalid";
		exit;
	}

	$stmt = $conn->prepare("SELECT * FROM pre_reg_table WHERE email_address = ?");
	$stmt->bind_param("s", $email);
	$stmt->execute();
	$stmt->store_result();

	if ($stmt->num_rows > 0) {
		echo "taken"; // Email exists
	} else {
		echo "available"; // Email is available
	}

	$stmt->close();
	$conn->close();
}
