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

	// Prepare SQL query to fetch only unregistered pre_reg members
	$stmt = $conn->prepare("
        SELECT 
            p.family_id,
            p.pre_reg_id,
            p.f_name,
            p.l_name,
            p.date_of_birth,
            p.gender
        FROM pre_reg_table p
        WHERE p.pre_reg_id NOT IN (
            SELECT pre_reg_id FROM pre_reg_table
        )
        ORDER BY p.family_id, p.pre_reg_id
    ");

	$stmt->execute();
	$result = $stmt->get_result();

	// Group by family
	$families = [];
	while ($row = $result->fetch_assoc()) {
		$family_id = $row['family_id'];
		if (!isset($families[$family_id])) {
			$families[$family_id] = [
				'family_id' => $family_id,
				'members' => []
			];
		}

		$families[$family_id]['members'][] = [
			'id' => $row['pre_reg_id'],
			'name' => trim($row['f_name'] . ' ' . $row['l_name']),
			'date_of_birth' => $row['date_of_birth'],
			'gender' => $row['gender'],
			'isRegistered' => false,
			'isPresent' => true
		];
	}

	// Return JSON response
	echo json_encode([
		'success' => true,
		'data' => array_values($families)
	]);
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
