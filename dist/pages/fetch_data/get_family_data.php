<?php
header('Content-Type: application/json');
include '../../../database/session.php'; // Database connection

// Check DB connection
if (!$conn) {
	http_response_code(500);
	echo json_encode(['error' => 'Database connection failed']);
	exit;
}

// Validate pre_reg_id from query string
$preRegId = $_GET['pre_reg_id'] ?? null;

if (!$preRegId || !is_numeric($preRegId)) {
	http_response_code(400);
	echo json_encode(['error' => 'Invalid pre_reg_id']);
	exit;
}

// Step 1: Get the family_id based on pre_reg_id
$stmt = $conn->prepare("SELECT family_id FROM pre_reg_table WHERE pre_reg_id = ?");
$stmt->bind_param("i", $preRegId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
	http_response_code(404);
	echo json_encode(['error' => 'No family found for this member.']);
	exit;
}

$familyRow = $result->fetch_assoc();
$familyId = $familyRow['family_id'];
$stmt->close();

// Step 2: Get all members from the same family
$stmt = $conn->prepare("
	SELECT pre_reg_id, f_name, l_name, date_of_birth, gender 
	FROM pre_reg_table 
	WHERE family_id = ?
");
$stmt->bind_param("i", $familyId);
$stmt->execute();
$result = $stmt->get_result();

// Function to calculate age
function calculateAge($dob)
{
	if (empty($dob)) return 0;
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

// Final output
echo json_encode([
	'success' => true,
	'family_id' => $familyId,
	'family_members' => $members
]);
