<?php

include '../../../../database/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$resource_name = trim($_POST['resource_name'] ?? '');
	$quantity = intval($_POST['quantity'] ?? 0);
	// $expiration_date = !empty($_POST['expiration_date']) ? $_POST['expiration_date'] : null; // Uncomment if using expiration date

	if ($resource_name !== '' && $quantity >= 0) {
		$stmt = $conn->prepare("INSERT INTO resource_allocation_table (resource_name, quantity) VALUES (?, ?)");
		$stmt->bind_param("si", $resource_name, $quantity);
		// If using expiration date:
		// $stmt = $conn->prepare("INSERT INTO resource_inventory (resource_name, quantity, expiration_date) VALUES (?, ?, ?)");
		// $stmt->bind_param("sis", $resource_name, $quantity, $expiration_date);

		if ($stmt->execute()) {
			$_SESSION['success'] = "<span style='color: green;'><i class='bi bi-check-circle-fill'></i></span> Add inventory Successfull!!!";
		} else {
			$_SESSION['error'] = "<span style='color:red;'><i class='bi bi-exclamation-circle-fill'></i></span> Add inventory Failed!!! " . $stmt->error;
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
