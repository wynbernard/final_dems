<?php

include '../../../../database/session.php';
require_once '../../../../database/csrf.php';

// Validate CSRF token
csrf_validate_or_die();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$original_name = trim($_POST['original_resource_name'] ?? '');
	$new_name = trim($_POST['resource_name'] ?? '');
	$quantity = intval($_POST['quantity'] ?? 0);
	$measurement_unit = trim($_POST['unit'] ?? ''); // Uncomment if you want to update measurement unit

	if ($original_name !== '' && $new_name !== '' && $quantity >= 0) {
		$stmt = $conn->prepare("UPDATE resource_allocation_table SET resource_name = ?, quantity = ?, measurement_unit = ? WHERE resource_name = ?");
		if ($stmt) {
			$stmt->bind_param("siss", $new_name, $quantity, $measurement_unit, $original_name);

			if ($stmt->execute()) {
				$_SESSION['success'] = "<span style='color: green;'><i class='bi bi-check-circle-fill'></i></span> Inventory updated successfully!";
			} else {
				error_log("Edit inventory failed: " . $stmt->error);
				$_SESSION['error'] = "<span style='color:red;'><i class='bi bi-x-circle-fill'></i></span> Failed to update inventory. Please try again.";
			}
			$stmt->close();
		} else {
			error_log("Failed to prepare update statement: " . $conn->error);
			$_SESSION['error'] = "Failed to prepare the update statement. Please try again.";
		}
	} else {
		$_SESSION['error'] = "Invalid input. Please check the fields.";
	}

	header("Location: ../../admin_page/resource_inventory.php");
	exit();
} else {
	header("Location: ../../admin_page/resource_inventory.php");
	exit();
}
