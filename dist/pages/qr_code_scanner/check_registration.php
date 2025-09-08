<?php
header("Content-Type: application/json");
include '../../../database/session.php';

$pre_reg_id = isset($_GET['pre_reg_id']) ? (int)$_GET['pre_reg_id'] : null;
$location_id = isset($_GET['location_id']) ? (int)$_GET['location_id'] : null;

if (!$pre_reg_id || !$location_id) {
	echo json_encode(['success' => false, 'error' => 'Missing parameters']);
	exit;
}

try {
	// Check if registered in the current location
	$stmt = $conn->prepare("SELECT *
		FROM evac_reg_table r
		WHERE r.pre_reg_id = ? AND r.room_id IN (
			SELECT room_id FROM room_table WHERE evac_loc_id = ?
		) AND r.status = 'Evacuated'
		LIMIT 1");

	if (!$stmt) {
		echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . $conn->error]);
		exit;
	}

	$stmt->bind_param('ii', $pre_reg_id, $location_id);
	$stmt->execute();
	$stmt->store_result();
	$isRegisteredHere = $stmt->num_rows > 0;
	$stmt->free_result();
	$stmt->close();

	// Find other locations where this pre_reg_id is registered
	$stmt2 = $conn->prepare("SELECT rt.evac_loc_id, el.name
		FROM evac_reg_table r
		JOIN room_table rt ON r.room_id = rt.room_id
		JOIN evac_loc_table el ON rt.evac_loc_id = el.evac_loc_id
		WHERE r.pre_reg_id = ? AND rt.evac_loc_id != ? AND r.status = 'Evacuated'");

	if (!$stmt2) {
		echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . $conn->error]);
		exit;
	}

	$stmt2->bind_param('ii', $pre_reg_id, $location_id);
	$stmt2->execute();
	$stmt2->bind_result($evac_loc_id, $location_name);

	$otherLocations = [];
	while ($stmt2->fetch()) {
		$otherLocations[] = [
			'evac_loc_id' => $evac_loc_id,
			'location_name' => $location_name
		];
	}
	$stmt2->close();

	echo json_encode([
		'success' => true,
		'isRegisteredHere' => $isRegisteredHere,
		'registeredOtherLocations' => $otherLocations
	]);
} catch (Exception $e) {
	echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
