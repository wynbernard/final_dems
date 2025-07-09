<?php

include '../../../../database/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$original_name = trim($_POST['original_resource_name'] ?? '');
	$new_name = trim($_POST['resource_name'] ?? '');
	$quantity = intval($_POST['quantity'] ?? 0);

	if ($original_name !== '' && $new_name !== '' && $quantity >= 0) {
		$stmt = $conn->prepare("UPDATE resource_allocation_table SET resource_name = ?, quantity = ? WHERE resource_name = ?");
		if ($stmt) {
			$stmt->bind_param("sis", $new_name, $quantity, $original_name);

			if ($stmt->execute()) {
				$_SESSION['success'] = "<span style='color: green;'><i class='bi bi-check-circle-fill'></i></span> Inventory updated successfully!";
			} else {
				$_SESSION['error'] = "<span style='color:red;'><i class='bi bi-x-circle-fill'></i></span> Failed to update inventory. " . $stmt->error;
			}
			$stmt->close();
		} else {
			$_SESSION['error'] = "Failed to prepare the update statement.";
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
