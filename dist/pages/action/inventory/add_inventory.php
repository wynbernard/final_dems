<?php

include '../../../../database/session.php';
require_once '../../../../database/csrf.php';

// Validate CSRF token
csrf_validate_or_die();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$resource_name = trim($_POST['resource_name'] ?? '');
	$quantity = intval($_POST['quantity'] ?? 0);
	$measurement_unit = trim($_POST['unit'] ?? '');

	if ($resource_name !== '' && $quantity >= 0 && $measurement_unit !== '') {
		$stmt = $conn->prepare("INSERT INTO resource_allocation_table (resource_name, quantity, measurement_unit) VALUES (?, ?, ?)");
		$stmt->bind_param("sis", $resource_name, $quantity, $measurement_unit);
		// If using expiration date:
		// $stmt = $conn->prepare("INSERT INTO resource_inventory (resource_name, quantity, expiration_date) VALUES (?, ?, ?)");
		// $stmt->bind_param("sis", $resource_name, $quantity, $expiration_date);

		if ($stmt->execute()) {
			$_SESSION['success'] = "<span style='color: green;'><i class='bi bi-check-circle-fill'></i></span> Add inventory Successfull!!!";
		} else {
			error_log("Add inventory failed: " . $stmt->error);
			$_SESSION['error'] = "<span style='color:red;'><i class='bi bi-exclamation-circle-fill'></i></span> Add inventory Failed!!! Please try again.";
		}
		$stmt->close();
	} else {
		$_SESSION['error'] = "Please provide valid resource details.";
	}
	header("Location: ../../admin_page/resource_inventory.php");
	exit();
} else {
	header("Location: resource_inventory.php");
	exit();
}
