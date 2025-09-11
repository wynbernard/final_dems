<?php
include '../../../database/user_session.php';
include '../../../database/conn.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	echo json_encode(['success' => false, 'error' => 'Invalid request method']);
	exit;
}

$latitude = isset($_POST['latitude']) ? trim($_POST['latitude']) : null;
$longitude = isset($_POST['longitude']) ? trim($_POST['longitude']) : null;

if ($latitude === null || $longitude === null) {
	echo json_encode(['success' => false, 'error' => 'Missing coordinates']);
	exit;
}

$pre_reg_id = $_SESSION['pre_reg_id'] ?? null;
if (!$pre_reg_id) {
	echo json_encode(['success' => false, 'error' => 'Not authenticated']);
	exit;
}

// Get user record to find registered_as and family/solo IDs
$stmt = $conn->prepare("SELECT registered_as, solo_address_id, family_id FROM pre_reg_table WHERE pre_reg_id = ?");
$stmt->bind_param('i', $pre_reg_id);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$stmt->close();

if (!$user) {
	echo json_encode(['success' => false, 'error' => 'User not found']);
	exit;
}

$registered_as = strtolower($user['registered_as']);

if ($registered_as === 'solo') {
	$solo_id = intval($user['solo_address_id']);
	if ($solo_id > 0) {
		$update = $conn->prepare("UPDATE solo_address_table SET latitude = ?, longitude = ? WHERE solo_address_id = ?");
		$update->bind_param('ddi', $latitude, $longitude, $solo_id);
		if ($update->execute()) {
			echo json_encode(['success' => true, 'updated' => 'solo', 'solo_id' => $solo_id]);
			$update->close();
			exit;
		} else {
			echo json_encode(['success' => false, 'error' => $update->error]);
			$update->close();
			exit;
		}
	} else {
		// No solo record exists for this user; insert minimal record and link to pre_reg_table
		$ins = $conn->prepare("INSERT INTO solo_address_table (pre_reg_id, latitude, longitude) VALUES (?, ?, ?)");
		$ins->bind_param('idd', $pre_reg_id, $latitude, $longitude);
		if ($ins->execute()) {
			$new_id = $ins->insert_id;
			$ins->close();
			$up = $conn->prepare("UPDATE pre_reg_table SET solo_address_id = ? WHERE pre_reg_id = ?");
			$up->bind_param('ii', $new_id, $pre_reg_id);
			$up->execute();
			$up->close();
			echo json_encode(['success' => true, 'inserted' => 'solo', 'solo_id' => $new_id]);
			exit;
		} else {
			echo json_encode(['success' => false, 'error' => $ins->error]);
			$ins->close();
			exit;
		}
	}
} elseif ($registered_as === 'family') {
	$family_id = intval($user['family_id']);
	if ($family_id > 0) {
		$update = $conn->prepare("UPDATE family_table SET latitude = ?, longitude = ? WHERE family_id = ?");
		$update->bind_param('ddi', $latitude, $longitude, $family_id);
		if ($update->execute()) {
			echo json_encode(['success' => true, 'updated' => 'family', 'family_id' => $family_id]);
			$update->close();
			exit;
		} else {
			echo json_encode(['success' => false, 'error' => $update->error]);
			$update->close();
			exit;
		}
	} else {
		echo json_encode(['success' => false, 'error' => 'No family record to update']);
		exit;
	}
} else {
	echo json_encode(['success' => false, 'error' => 'Unknown registration type']);
	exit;
}
