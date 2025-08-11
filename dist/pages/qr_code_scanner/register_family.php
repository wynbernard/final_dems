<?php
header("Content-Type: application/json");
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php-error.log');
error_reporting(E_ALL);

include '../../../database/session.php'; // $conn must be available

try {
    $data = json_decode(file_get_contents("php://input"), true);

    $roomId = $data['room_id'] ?? null;
    $memberIds = $data['member_ids'] ?? [];
    $locationId = $data['location_id'] ?? null;

    if (!$roomId || !is_array($memberIds) || count($memberIds) === 0 || !$locationId) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => "Missing required input: room, members, or location."
        ]);
        exit;
    }

    $conn->begin_transaction();

    $successCount = 0;
    $soloCount = 0;
    $familyCount = 0;
    $skipped = [];
    $ageClassCounts = [
        'Infant' => 0,
        'Toddler' => 0,
        'Pre-School' => 0,
        'School-Age' => 0,
        'Teenage' => 0,
        'Adult' => 0,
        'Senior' => 0
    ];

    // Prepare statements
    $checkStmt = $conn->prepare("SELECT pre_reg_id FROM evac_reg_table WHERE pre_reg_id = ?");
    $insertStmt = $conn->prepare("INSERT INTO evac_reg_table (room_id, pre_reg_id, evac_loc_id, date_reg, status) VALUES (?, ?, ?, CURDATE(), 'Evacuated')");
    $logStmt = $conn->prepare("INSERT INTO logs_table (evac_reg_id, status, date_time) VALUES (?, ?, NOW())");
    $typeStmt = $conn->prepare("SELECT registered_as FROM pre_reg_table WHERE pre_reg_id = ?");
    $recordCheck = $conn->prepare("SELECT evacuation_record_id FROM evacuation_record_table WHERE evacuation_location = ? AND end_date IS NULL");
    $recordInsert = $conn->prepare("INSERT INTO evacuation_record_table (evacuation_location, start_date, total_solo, total_family, total_evacuation, total_infant, total_toddler, total_pre_school, total_school_age, total_teenage, total_adult, total_seniors) VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $recordUpdate = $conn->prepare("UPDATE evacuation_record_table SET total_solo = total_solo + ?, total_family = total_family + ?, total_evacuation = total_evacuation + ?, total_infant = total_infant + ?, total_toddler = total_toddler + ?, total_pre_school = total_pre_school + ?, total_school_age = total_school_age + ?, total_teenage = total_teenage + ?, total_adult = total_adult + ?, total_seniors = total_seniors + ? WHERE evacuation_record_id = ?");
    $locationStmt = $conn->prepare("SELECT name FROM evac_loc_table WHERE evac_loc_id = ?");

    if (!$checkStmt || !$insertStmt || !$logStmt || !$typeStmt || !$recordCheck || !$recordInsert || !$recordUpdate || !$locationStmt) {
        throw new Exception("Statement preparation failed: " . $conn->error);
    }

    foreach ($memberIds as $memberId) {
        $checkStmt->bind_param("i", $memberId);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows === 0) {
            // Insert new registration
            $insertStmt->bind_param("iii", $roomId, $memberId, $locationId);
            if (!$insertStmt->execute()) {
                throw new Exception("Insert failed for member ID $memberId: " . $insertStmt->error);
            }

            $evacRegId = $insertStmt->insert_id;
            $status = 'IN';
            $logStmt->bind_param("is", $evacRegId, $status);
            $logStmt->execute();

            // Check registration type
            $typeStmt->bind_param("i", $memberId);
            $typeStmt->execute();
            $typeResult = $typeStmt->get_result();

            if ($typeResult && $typeRow = $typeResult->fetch_assoc()) {
                $groupType = strtolower($typeRow['registered_as']);
                if ($groupType === 'solo') {
                    $soloCount++;
                } else if ($groupType === 'family') {
                    $familyCount++;  // This counts family members individually
                }
            }

            $successCount++;
        } else {
            $skipped[] = $memberId;
        }
    }

    // Final individual count
    $totalEvacuees = $successCount;

    // Get location name
    $locationStmt->bind_param("i", $locationId);
    $locationStmt->execute();
    $locationResult = $locationStmt->get_result();
    if ($locationRow = $locationResult->fetch_assoc()) {
        $locationName = $locationRow['name'];
    } else {
        throw new Exception("Location name not found for ID: $locationId");
    }

    // Update or insert evacuation record
    $recordCheck->bind_param("s", $locationName);
    $recordCheck->execute();
    $recordCheck->store_result();

    if ($recordCheck->num_rows > 0) {
        $recordCheck->bind_result($evacuationId);
        $recordCheck->fetch();
        $recordUpdate->bind_param("iiii", $soloCount, $familyCount, $totalEvacuees, $evacuationId);
        $recordUpdate->execute();
    } else {
        $recordInsert->bind_param("siii", $locationName, $soloCount, $familyCount, $totalEvacuees);
        $recordInsert->execute();
    }

    $conn->commit();

    echo json_encode([
        'success' => true,
        'success_count' => $successCount,
        'skipped_members' => $skipped,
        'message' => 'Registration complete.'
    ]);
} catch (Exception $e) {
    if ($conn->errno) {
        $conn->rollback();
    }

    error_log("Register error: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => "Server error: " . $e->getMessage()
    ]);
}

$conn->close();
?>
