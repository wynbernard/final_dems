<?php
include '../../../database/user_session.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$family_id = isset($_POST['family_id']) ? intval($_POST['family_id']) : 0;
	$city = trim($_POST['city']); // Now getting city name directly
	$barangay = trim($_POST['barangay']); // Now getting barangay name directly
	$purok = trim($_POST['purok']);

	// Basic validation
	if ($family_id <= 0 || empty($city) || empty($barangay) || empty($purok)) {
		$_SESSION['error'] = "⚠️ All address fields are required.";
		header("Location: ../some_page.php");
		exit();
	}

	// Now you can directly use the names without lookup
	$new_address = "$purok, $barangay, $city";

	$update_sql = "UPDATE family_table SET address = ? WHERE family_id = ?";
	$stmt = $conn->prepare($update_sql);
	$stmt->bind_param("si", $new_address, $family_id);

	if ($stmt->execute()) {
		$_SESSION['success'] = "✅ Address updated successfully!";
	} else {
		$_SESSION['error'] = "❌ Failed to update address. Please try again.";
	}

	$stmt->close();
	$conn->close();

	header("Location: ../user_page/family.php");
	exit();
} else {
	header("Location: ../user_page/family.php");
	exit();
}
