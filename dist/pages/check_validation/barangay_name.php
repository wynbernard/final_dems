<?php
include '../../../database/session.php';
if (isset($_POST['barangay_name'])) {
	$barangay_name = trim($_POST['barangay_name']);

	// Simple validation
	if ($barangay_name === '') {
		echo 'invalid';
		exit;
	}

	// Prepare and execute query
	$stmt = $conn->prepare("SELECT COUNT(*) FROM barangay_manegement_table WHERE barangay_name = ?");
	$stmt->bind_param("s", $barangay_name);
	$stmt->execute();
	$stmt->bind_result($count);
	$stmt->fetch();
	$stmt->close();

	echo ($count > 0) ? 'taken' : 'available';
}
