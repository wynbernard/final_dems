<?php
include '../../../database/session.php'; // Adjust path to your database connection

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username'])) {
	$username = mysqli_real_escape_string($conn, trim($_POST['username']));

	$query = "SELECT * FROM admin_table WHERE username = ?";
	$stmt = mysqli_prepare($conn, $query);
	mysqli_stmt_bind_param($stmt, "s", $username);
	mysqli_stmt_execute($stmt);
	$result = mysqli_stmt_get_result($stmt);

	if (mysqli_num_rows($result) > 0) {
		echo "taken"; // Ensure NO extra spaces or HTML tags
	} else {
		echo "available";
	}

	mysqli_stmt_close($stmt);
	mysqli_close($conn);
}
