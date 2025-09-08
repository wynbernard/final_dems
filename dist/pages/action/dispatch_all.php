<?php
header('Content-Type: application/json');
include '../../../database/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	echo json_encode(['success' => false, 'message' => 'Invalid request method']);
	exit;
}

$locationId = $_POST['location_id'] ?? '';
if (empty($locationId)) {
	echo json_encode(['success' => false, 'message' => 'Missing location_id']);
	exit;
}

$locationId = (int)$locationId;
// Select evac_reg ids where status is 'Evacuated' for the given location
$sel = $conn->prepare("SELECT evac_reg_id FROM evac_reg_table WHERE evac_loc_id = ? AND status = 'Evacuated'");
$sel->bind_param('i', $locationId);
$sel->execute();
$res = $sel->get_result();
$ids = [];
while ($r = $res->fetch_assoc()) $ids[] = $r['evac_reg_id'];

if (empty($ids)) {
	echo json_encode(['success' => true, 'message' => 'No evacuees to dispatch']);
	exit;
}

$conn->begin_transaction();
try {
	$upd = $conn->prepare("UPDATE evac_reg_table SET status = 'Dispatched' WHERE evac_reg_id = ?");
	$log = $conn->prepare("INSERT INTO logs_table (evac_reg_id, date_time, status) VALUES (?, NOW(), 'IN')");

	foreach ($ids as $id) {
		$upd->bind_param('i', $id);
		$upd->execute();

		$log->bind_param('i', $id);
		$log->execute();
	}

	// Close evacuation record(s) for this location by setting end_date = NOW()
	$locStmt = $conn->prepare("SELECT name FROM evac_loc_table WHERE evac_loc_id = ?");
	if ($locStmt) {
		$locStmt->bind_param('i', $locationId);
		$locStmt->execute();
		$locRes = $locStmt->get_result();
		if ($locRow = $locRes->fetch_assoc()) {
			$locationName = $locRow['name'];
			$closeStmt = $conn->prepare("UPDATE evacuation_record_table SET end_date = NOW() WHERE evacuation_location = ? AND end_date IS NULL");
			if ($closeStmt) {
				$closeStmt->bind_param('s', $locationName);
				$closeStmt->execute();
			}
		}
	}

	$conn->commit();
	echo json_encode(['success' => true, 'message' => 'Dispatched', 'count' => count($ids)]);
} catch (Exception $e) {
	$conn->rollback();
	echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
