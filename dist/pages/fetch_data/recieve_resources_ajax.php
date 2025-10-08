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
	$staffEvacLocId = isset($_POST['staff_evac_loc_id']) ? (int)$_POST['staff_evac_loc_id'] : null;
	$resourceIds = array_map('intval', $_POST['resources']);
	$quantities = $_POST['quantity'] ?? [];
	$distributionType = isset($_POST['distribution_type']) ? trim($_POST['distribution_type']) : 'family';

	// 1. Ensure pre_reg_id exists and use it for distribution
	$existsStmt = $conn->prepare("SELECT 1 FROM pre_reg_table WHERE pre_reg_id = ? LIMIT 1");
	$existsStmt->bind_param("i", $preRegId);
	$existsStmt->execute();
	$existsRes = $existsStmt->get_result();
	if ($existsRes->num_rows === 0) {
		echo json_encode(['success' => false, 'message' => 'Evacuee not found in registry.']);
		exit;
	}

	// 1b. Server-side guard: block if evacuee registered at a different active location
	if ($staffEvacLocId) {
		$regStmt = $conn->prepare("SELECT evac_loc_id, status FROM evac_reg_table WHERE pre_reg_id = ? AND status = 'Evacuated' ORDER BY date_reg DESC LIMIT 1");
		$regStmt->bind_param('i', $preRegId);
		$regStmt->execute();
		$regRes = $regStmt->get_result();
		if ($regRes && ($row = $regRes->fetch_assoc())) {
			if ((int)$row['evac_loc_id'] !== (int)$staffEvacLocId) {
				echo json_encode(['success' => false, 'message' => 'Location mismatch: evacuee is registered at a different evacuation location.']);
				exit;
			}
		}
	}

	// 2. Insert non-duplicate resources
	// Compute claim status once per request (On-site/Off-site)
	$claimStatus = 'Off-site';
	if ($staffEvacLocId) {
		$regChkPre = $conn->prepare("SELECT evac_loc_id FROM evac_reg_table WHERE pre_reg_id = ? AND status = 'Evacuated' ORDER BY date_reg DESC LIMIT 1");
		$regChkPre->bind_param('i', $preRegId);
		$regChkPre->execute();
		$regChkPreRes = $regChkPre->get_result();
		if ($regChkPreRes && ($rrp = $regChkPreRes->fetch_assoc())) {
			if ((int)$rrp['evac_loc_id'] === (int)$staffEvacLocId) {
				$claimStatus = 'On-site';
			}
		}
	}

	$inserted = [];

	foreach ($resourceIds as $resourceId) {
		$qty = isset($quantities[$resourceId]) ? max(1, (int)$quantities[$resourceId]) : 1;

		// Check if already distributed for the same distribution type (by pre_reg_id)
		$checkDup = $conn->prepare("SELECT 1 FROM resource_distribution_table WHERE pre_reg_id = ? AND resource_id = ? AND distribution_type = ?");
		$checkDup->bind_param("iis", $preRegId, $resourceId, $distributionType);
		$checkDup->execute();
		$dupResult = $checkDup->get_result();

		if ($dupResult->num_rows === 0) {
			$stmt = $conn->prepare("INSERT INTO resource_distribution_table (pre_reg_id, resource_id, quantity, date_time, distribution_type, status) VALUES (?, ?, ?, NOW(), ?, ?)");
			$stmt->bind_param("iiiss", $preRegId, $resourceId, $qty, $distributionType, $claimStatus);
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

	// 3b. claimStatus already computed above; include in response

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
		"inserted" => $inserted,
		"claim_status" => $claimStatus
	]);
} catch (Exception $e) {
	echo json_encode([
		"success" => false,
		"message" => $e->getMessage()
	]);
}
