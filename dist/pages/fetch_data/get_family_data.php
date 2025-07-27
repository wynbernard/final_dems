<?php
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 1);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
	// Check session file existence
	if (!file_exists('../../../database/session.php')) {
		throw new Exception('session.php not found');
	}

	include '../../../database/session.php';

	// Validate DB connection
	if (!isset($conn) || !($conn instanceof mysqli)) {
		throw new Exception('Database connection failed or not initialized');
	}

	// Validate GET input
	$preRegId = $_GET['pre_reg_id'] ?? null;
	if (!$preRegId || !is_numeric($preRegId)) {
		http_response_code(400);
		echo json_encode(['success' => false, 'error' => 'Invalid or missing pre_reg_id']);
		exit;
	}

	// Get family_id for this pre_reg_id
	$stmt = $conn->prepare("SELECT family_id FROM pre_reg_table WHERE pre_reg_id = ?");
	$stmt->bind_param("i", $preRegId);
	$stmt->execute();
	$result = $stmt->get_result();

	if ($result->num_rows === 0) {
		http_response_code(404);
		echo json_encode(['success' => false, 'error' => 'No family found for this member.']);
		exit;
	}

	$familyRow = $result->fetch_assoc();
	$familyId = $familyRow['family_id'];
	$stmt->close();

	// Fetch all members of the same family (excluding address)
	$stmt = $conn->prepare("
        SELECT pre_reg_id, f_name, l_name, date_of_birth, gender
        FROM pre_reg_table
        WHERE family_id = ?
    ");
	$stmt->bind_param("i", $familyId);
	$stmt->execute();
	$result = $stmt->get_result();

	function calculateAge($dob)
	{
		if (empty($dob)) return null;
		$birthDate = new DateTime($dob);
		$today = new DateTime();
		return $today->diff($birthDate)->y;
	}

	$members = [];
	while ($row = $result->fetch_assoc()) {
		$members[] = [
			'id' => $row['pre_reg_id'],
			'name' => $row['f_name'] . ' ' . $row['l_name'],
			'date_of_birth' => $row['date_of_birth'],
			'age' => calculateAge($row['date_of_birth']),
			'gender' => $row['gender']
		];
	}

	$stmt->close();
	$conn->close();

	echo json_encode([
		'success' => true,
		'family_id' => $familyId,
		'family_members' => $members
	]);
} catch (Exception $e) {
	http_response_code(500);
	echo json_encode([
		'success' => false,
		'error' => 'Server error: ' . $e->getMessage()
	]);
	exit;
}
