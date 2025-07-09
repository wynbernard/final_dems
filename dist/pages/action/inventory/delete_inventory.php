<?php

include '../../../../database/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$resource_name = trim($_POST['resource_name'] ?? '');

	if ($resource_name !== '') {
		$stmt = $conn->prepare("DELETE FROM resource_allocation_table WHERE resource_name = ?");
		if ($stmt) {
			$stmt->bind_param("s", $resource_name);

			if ($stmt->execute()) {
				$_SESSION['success'] = "<span style='color: green;'><i class='bi bi-check-circle-fill'></i></span> Inventory deleted successfully!";
			} else {
				$_SESSION['error'] = "<span style='color:red;'><i class='bi bi-x-circle-fill'></i></span> Failed to delete inventory. " . $stmt->error;
			}
			$stmt->close();
		} else {
			$_SESSION['error'] = "Failed to prepare the delete statement.";
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
