<?php
header('Content-Type: application/json');

include '../../../database/session.php'; // Make sure this connects to your DB

if (!$conn) {
	http_response_code(500);
	echo json_encode(['error' => 'Database connection failed']);
	exit;
}

$preRegId = $_GET['pre_reg_id'] ?? null;

if (!$preRegId || !is_numeric($preRegId)) {
	http_response_code(400);
	echo json_encode(['error' => 'Invalid pre_reg_id']);
	exit;
}

// First, get family_id from pre_reg_table using pre_reg_id
$stmt = $conn->prepare("SELECT family_id FROM pre_reg_table WHERE pre_reg_id = ?");
$stmt->bind_param("i", $preRegId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
	echo json_encode(['family_members' => []]);
	exit;
}

$familyRow = $result->fetch_assoc();
$familyId = $familyRow['family_id'];

// Now fetch all members in the same family
$stmt = $conn->prepare("SELECT pre_reg_id, f_name, l_name,date_of_birth,gender
                        FROM pre_reg_table 
                        WHERE family_id = ?");
$stmt->bind_param("i", $familyId);
$stmt->execute();
$result = $stmt->get_result();

function calculateAge($dob)
{
	if (empty($dob)) return 0;

	$birthDate = new DateTime($dob);
	$today = new DateTime();
	$age = $today->diff($birthDate)->y;

	return $age;
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

echo json_encode([
	'family_id' => $familyId,
	'family_members' => $members
]);

$stmt->close();
$conn->close();
