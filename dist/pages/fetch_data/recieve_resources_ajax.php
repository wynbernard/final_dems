<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header("Content-Type: application/json");

include '../../../database/conn.php';

try {
	if (!isset($_POST['pre_reg_id'], $_POST['resources']) || !is_array($_POST['resources'])) {
		throw new Exception("Invalid or incomplete data.");
	}

	$preRegId = (int) $_POST['pre_reg_id'];
	$resourceIds = array_map('intval', $_POST['resources']);
	$quantities = $_POST['quantity'] ?? [];
	$distributionType = isset($_POST['distribution_type']) ? trim($_POST['distribution_type']) : 'family';

	// 1. Get evac_reg_id from pre_reg_id
	$checkStmt = $conn->prepare("SELECT evac_reg_id FROM evac_reg_table WHERE pre_reg_id = ?");
	$checkStmt->bind_param("i", $preRegId);
	$checkStmt->execute();
	$result = $checkStmt->get_result();

	if ($result->num_rows === 0) {
		throw new Exception("This person is not registered in the evacuation list.");
	}

	$row = $result->fetch_assoc();
	$evacRegId = $row['evac_reg_id'];

	// 2. Insert non-duplicate resources
	$inserted = [];

	foreach ($resourceIds as $resourceId) {
		$qty = isset($quantities[$resourceId]) ? max(1, (int)$quantities[$resourceId]) : 1;

		// Check if already distributed
		$checkDup = $conn->prepare("SELECT 1 FROM resource_distribution_table WHERE evac_reg_id = ? AND resource_id = ?");
		$checkDup->bind_param("ii", $evacRegId, $resourceId);
		$checkDup->execute();
		$dupResult = $checkDup->get_result();

		if ($dupResult->num_rows === 0) {
			$stmt = $conn->prepare("INSERT INTO resource_distribution_table (evac_reg_id, resource_id, quantity, date_time ,distribution_type) VALUES (?, ?, ?, NOW(), ?)");
			$stmt->bind_param("iiis", $evacRegId, $resourceId, $qty, $distributionType);
			$stmt->execute();
			$inserted[] = $resourceId;
		}
	}

	// 3. Get family name
	$nameStmt = $conn->prepare("SELECT l_name FROM pre_reg_table WHERE pre_reg_id = ?");
	$nameStmt->bind_param("i", $preRegId);
	$nameStmt->execute();
	$nameResult = $nameStmt->get_result();
	$nameRow = $nameResult->fetch_assoc();

	if (empty($inserted)) {
		echo json_encode([
			"success" => false,
			"message" => "Resources already distributed to this evacuee."
		]);
		exit;
	}

	echo json_encode([
		"success" => true,
		"name" => $nameRow['l_name'] ?? "Family",
		"inserted" => $inserted
	]);
} catch (Exception $e) {
	echo json_encode([
		"success" => false,
		"message" => $e->getMessage()
	]);
}
