<?php
include '../../../database/user_session.php'; // For session and DB connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// Get and sanitize input
	$pre_reg_id = isset($_POST['pre_reg_id']) ? intval($_POST['pre_reg_id']) : 0;

	if ($pre_reg_id <= 0) {
		$_SESSION['error'] = "⚠️ Invalid member ID.";
		header("Location: ../user_page/family.php");
		exit();
	}

	// Prepare SQL to delete the family member
	$stmt = $conn->prepare("DELETE FROM pre_reg_table WHERE pre_reg_id = ?");
	$stmt->bind_param("i", $pre_reg_id);

	if ($stmt->execute()) {
		$_SESSION['success'] = "✅ Family member deleted successfully.";
	} else {
		$_SESSION['error'] = "❌ Failed to delete family member. Please try again.";
	}

	$stmt->close();
	$conn->close();

	// Redirect to the family page
	header("Location: ../user_page/family.php");
	exit();
} else {
	// Prevent direct access
	header("Location: ../user_page/family.php");
	exit();
}
