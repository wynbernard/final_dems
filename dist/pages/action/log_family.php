<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

include '../../../database/session.php';

try {
	if (!isset($_POST['pre_reg_id'], $_POST['logType'])) {
		throw new Exception("Required data missing.");
	}

	$preRegId = intval($_POST['pre_reg_id']);
	$logType = strtoupper(trim($_POST['logType'])); // IN or OUT

	if (!isset($_SESSION['evac_loc_id'])) {
		throw new Exception("Staff location not set in session.");
	}
	$staffLocId = intval($_SESSION['evac_loc_id']);

	// Step 1: Check if evacuee is actively registered and fetch location
	$check = $conn->prepare("
		SELECT 
			pre_reg_table.l_name AS name, 
			pre_reg_table.registered_as AS type, 
			evac_reg_table.evac_reg_id,
			evac_reg_table.evac_loc_id
		FROM evac_reg_table
		LEFT JOIN pre_reg_table 
			ON evac_reg_table.pre_reg_id = pre_reg_table.pre_reg_id
		WHERE evac_reg_table.pre_reg_id = ? 
		AND evac_reg_table.status = 'Evacuated'
	");
	$check->bind_param("i", $preRegId);
	$check->execute();
	$result = $check->get_result();

	if ($result->num_rows === 0) {
		throw new Exception("Evacuee not found or event is not active.");
	}

	$evacuee = $result->fetch_assoc();
	$name = $evacuee['name'];
	$type = $evacuee['type'];
	$evacRegId = $evacuee['evac_reg_id'];
	$evacLocId = $evacuee['evac_loc_id'];

	// 🔐 Step 1.5: Compare staff's location to evacuee's location
	if ($evacLocId !== $staffLocId) {
		throw new Exception("This evacuee is registered at a different evacuation center.");
	}

	// Step 2: Get latest log of the day
	$logCheck = $conn->prepare("
		SELECT status 
		FROM logs_table 
		WHERE evac_reg_id = ? AND DATE(date_time) = CURDATE()
		ORDER BY date_time DESC
		LIMIT 1
	");
	$logCheck->bind_param("i", $evacRegId);
	$logCheck->execute();
	$logResult = $logCheck->get_result();

	if ($logResult->num_rows > 0) {
		$lastStatus = $logResult->fetch_assoc()['status'];

		if ($logType === $lastStatus) {
			throw new Exception("Already scanned for $logType today.");
		}

		if ($logType === "OUT" && $lastStatus !== "IN") {
			throw new Exception("Cannot log OUT without a previous IN scan.");
		}
	} else {
		if ($logType === "OUT") {
			throw new Exception("Cannot log OUT without an IN scan first.");
		}
	}

	// Step 3: Insert log
	$insert = $conn->prepare("
		INSERT INTO logs_table (evac_reg_id, status, date_time) 
		VALUES (?, ?, NOW())
	");
	$insert->bind_param("is", $evacRegId, $logType);
	$insert->execute();

	// Step 4: Respond
	echo json_encode([
		"success" => true,
		"name" => $name,
		"type" => $type,
		"log_type" => $logType
	]);

} catch (Exception $e) {
	echo json_encode([
		"success" => false,
		"message" => $e->getMessage()
	]);
}
?>
