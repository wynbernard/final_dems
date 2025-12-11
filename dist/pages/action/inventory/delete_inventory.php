<?php

include '../../../../database/session.php';
require_once '../../../../database/csrf.php';

// Validate CSRF token
csrf_validate_or_die();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$resource_name = trim($_POST['resource_name'] ?? '');

	if ($resource_name !== '') {
		$stmt = $conn->prepare("DELETE FROM resource_allocation_table WHERE resource_name = ?");
		if ($stmt) {
			$stmt->bind_param("s", $resource_name);

			if ($stmt->execute()) {
				$_SESSION['success'] = "<span style='color: green;'><i class='bi bi-check-circle-fill'></i></span> Inventory deleted successfully!";
			} else {
				error_log("Delete inventory failed: " . $stmt->error);
				$_SESSION['error'] = "<span style='color:red;'><i class='bi bi-x-circle-fill'></i></span> Failed to delete inventory. Please try again.";
			}
			$stmt->close();
		} else {
			error_log("Failed to prepare delete statement: " . $conn->error);
			$_SESSION['error'] = "Failed to prepare the delete statement. Please try again.";
		}
	} else {
		$_SESSION['error'] = "No resource specified for deletion.";
	}

	header("Location: ../../admin_page/resource_inventory.php");
	exit();
} else {
	header("Location: ../../admin_page/resource_inventory.php");
	exit();
}
