<?php
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 1);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
	// Verify session.php exists
	if (!file_exists('../../../database/session.php')) {
		throw new Exception('session.php not found');
	}

	include '../../../database/session.php';

	if (!isset($conn) || !($conn instanceof mysqli)) {
		throw new Exception('Database connection failed or not initialized');
	}

	// Accept pre_reg_id and return either only the scanned person (if solo) or their family members
	$inputRaw = file_get_contents('php://input');
	$input = json_decode($inputRaw, true);
	$preRegId = isset($input['pre_reg_id']) ? (int)$input['pre_reg_id'] : (isset($_GET['pre_reg_id']) ? (int)$_GET['pre_reg_id'] : 0);

	// If no pre_reg_id provided, return empty data (for modal initialization)
	if ($preRegId <= 0) {
		echo json_encode(['success' => true, 'data' => []]);
		exit;
	}

	// Get the person's details first
	$pstmt = $conn->prepare("SELECT pre_reg_id, family_id, solo_address_id, f_name, l_name, date_of_birth, gender, relation_to_family FROM pre_reg_table WHERE pre_reg_id = ? LIMIT 1");
	$pstmt->bind_param('i', $preRegId);
	$pstmt->execute();
	$personRes = $pstmt->get_result();
	if ($personRes->num_rows === 0) {
		echo json_encode(['success' => false, 'error' => 'Evacuee not found']);
		exit;
	}
	$person = $personRes->fetch_assoc();
	$familyId = $person['family_id'];
	
	// Check if family_id is 0, null, or empty - treat as solo member
	if (empty($familyId) || $familyId == 0 || $familyId == '0') {
		// Solo member: display only 1 member (the scanned person) with no other members
		$payload = [
			'family_id' => null,
			'members' => [[
				'id' => (int)$person['pre_reg_id'],
				'name' => trim(($person['f_name'] ?? '') . ' ' . ($person['l_name'] ?? '')),
				'date_of_birth' => $person['date_of_birth'],
				'gender' => $person['gender'],
				'relation_to_family' => $person['relation_to_family'] ?? 'Solo',
				'isRegistered' => false,
				'isPresent' => true
			]]
		];
		echo json_encode(['success' => true, 'data' => [$payload]]);
		exit;
	}

	// Family member: return all family members
	$mstmt = $conn->prepare("SELECT pre_reg_id, f_name, l_name, date_of_birth, gender, relation_to_family FROM pre_reg_table WHERE family_id = ? ORDER BY pre_reg_id");
	$mstmt->bind_param('i', $familyId);
	$mstmt->execute();
	$mres = $mstmt->get_result();
	$members = [];
	while ($m = $mres->fetch_assoc()) {
		$members[] = [
			'id' => (int)$m['pre_reg_id'],
			'name' => trim(($m['f_name'] ?? '') . ' ' . ($m['l_name'] ?? '')),
			'date_of_birth' => $m['date_of_birth'],
			'gender' => $m['gender'],
			'relation_to_family' => $m['relation_to_family'],
			'isRegistered' => false,
			'isPresent' => true
		];
	}

	echo json_encode(['success' => true, 'data' => [[ 'family_id' => $familyId, 'members' => $members ]]]);
} catch (mysqli_sql_exception $e) {
	error_log("DB Error: " . $e->getMessage());
	echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
	error_log("Error: " . $e->getMessage());
	echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
} finally {
	if (isset($conn) && $conn instanceof mysqli) {
		$conn->close();
	}
}
