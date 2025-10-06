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
    $disasterId = $data['disaster_id'] ?? null; // New disaster_id input

    if (!$roomId || !is_array($memberIds) || count($memberIds) === 0 || !$locationId || !$disasterId) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => "Missing required input: room, members, location, or disaster."
        ]);
        exit;
    }

    $conn->begin_transaction();

    $successCount = 0;
    $soloCount = 0;
    $familyCount = 0;
    $skipped = [];

    // Age group counters
    $infantCount = 0;
    $toddlerCount = 0;
    $preschoolCount = 0;
    $schoolAgeCount = 0;
    $teenageCount = 0;
    $adultCount = 0;
    $seniorCount = 0;

    // Load age classifications mapping
    $ageClassMap = [];
    $ageClassQuery = $conn->prepare("SELECT age_class_id, classification FROM age_class_table");
    $ageClassQuery->execute();
    $ageClassResult = $ageClassQuery->get_result();

    while ($row = $ageClassResult->fetch_assoc()) {
        $ageClassMap[$row['age_class_id']] = trim($row['classification']); // Keep original case
    }

    // Prepare statements
    // Check latest evac_reg entry for the given pre_reg_id (allow re-register if status = 'Dispatched')
    $checkStmt = $conn->prepare("SELECT evac_reg_id, status FROM evac_reg_table WHERE pre_reg_id = ? ORDER BY evac_reg_id DESC LIMIT 1");
    $insertStmt = $conn->prepare("INSERT INTO evac_reg_table (room_id, pre_reg_id, evac_loc_id, disaster_id, date_reg, status) VALUES (?, ?, ?, ?, CURDATE(), 'Evacuated')");
    $logStmt = $conn->prepare("INSERT INTO logs_table (evac_reg_id, status, date_time) VALUES (?, ?, NOW())");
    $typeStmt = $conn->prepare("SELECT registered_as,age_class_id FROM pre_reg_table WHERE pre_reg_id = ?");
    // Barangay lookup for brgy_record_table increment (based on member's address)
    $brgyStmt = $conn->prepare("SELECT b.barangay_name FROM pre_reg_table pr 
        LEFT JOIN solo_address_table sat ON pr.solo_address_id = sat.solo_address_id 
        LEFT JOIN family_table ft ON pr.family_id = ft.family_id 
        LEFT JOIN barangay_manegement_table b ON COALESCE(sat.barangay_id, ft.barangay_id) = b.barangay_id 
        WHERE pr.pre_reg_id = ? LIMIT 1");
    $recordCheck = $conn->prepare("SELECT evacuation_id FROM evacuation_record_table WHERE evacuation_location = ? AND end_date IS NULL");
    $recordInsert = $conn->prepare("INSERT INTO evacuation_record_table (evacuation_location, start_date, total_solo, total_family, total_evacuation, total_infant, total_toddler, total_pre_school, total_school_age, total_teenage, total_adult, total_senior) VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $recordUpdate = $conn->prepare("UPDATE evacuation_record_table SET total_solo = total_solo + ?, total_family = total_family + ?, total_evacuation = total_evacuation + ?, total_infant = total_infant + ?, total_toddler = total_toddler + ?, total_pre_school = total_pre_school + ?, total_school_age = total_school_age + ?, total_teenage = total_teenage + ?, total_adult = total_adult + ?, total_senior = total_senior + ? WHERE evacuation_id = ?");
    $locationStmt = $conn->prepare("SELECT name FROM evac_loc_table WHERE evac_loc_id = ?");

    if (!$checkStmt || !$insertStmt || !$logStmt || !$typeStmt || !$recordCheck || !$recordInsert || !$recordUpdate || !$locationStmt || !$brgyStmt) {
        throw new Exception("Statement preparation failed: " . $conn->error);
    }

    // Function to map age classification to standard age groups
    function getAgeGroupFromClassification($classification)
    {
        // Don't convert to lowercase, use case-insensitive comparison
        $classification = trim($classification);

        // Use case-insensitive string search
        if (stripos($classification, 'Infant') !== false) {
            return 'infant';
        } else if (stripos($classification, 'Toddler') !== false) {
            return 'toddler';
        } else if (stripos($classification, 'Pre_School') !== false || stripos($classification, 'Preschool') !== false) {
            return 'pre_school';
        } else if (stripos($classification, 'School_Age') !== false || stripos($classification, 'School Age') !== false) {
            return 'school_age';
        } else if (stripos($classification, 'Teenage') !== false || stripos($classification, 'Teen') !== false) {
            return 'teenage';
        } else if (stripos($classification, 'Adult') !== false) {
            return 'adult';
        } else if (stripos($classification, 'Senior') !== false) {
            return 'senior';
        }

        // Return default if no match found
        return 'adult';
    }

    foreach ($memberIds as $memberId) {
        $checkStmt->bind_param("i", $memberId);
        $checkStmt->execute();
        $checkRes = $checkStmt->get_result();

        // Allow insert when there's no existing record, or the latest record status is 'Dispatched'
        $allowInsert = false;
        if (!$checkRes || $checkRes->num_rows === 0) {
            $allowInsert = true;
        } else {
            $existing = $checkRes->fetch_assoc();
            $existingStatus = strtolower(trim($existing['status'] ?? ''));
            if ($existingStatus === 'dispatched') {
                $allowInsert = true;
            }
        }   

        if ($allowInsert) {
            // Insert new registration
            $insertStmt->bind_param("iiii", $roomId, $memberId, $locationId , $disasterId);
            if (!$insertStmt->execute()) {
                throw new Exception("Insert failed for member ID $memberId: " . $insertStmt->error);
            }

            $evacRegId = $insertStmt->insert_id;
            $status = 'IN';
            $logStmt->bind_param("is", $evacRegId, $status);
            $logStmt->execute();

            // Increment barangay record for this member (per disaster)
            $barangayName = null;
            $brgyStmt->bind_param("i", $memberId);
            $brgyStmt->execute();
            $br = $brgyStmt->get_result();
            if ($br && $brRow = $br->fetch_assoc()) {
                $barangayName = trim($brRow['barangay_name'] ?? '');
            }
            if ($barangayName !== null && $barangayName !== '') {
                // UPDATE then INSERT-if-missing (UPSERT behavior)
                $upd = $conn->prepare("UPDATE brgy_record_table SET total_evacuess = total_evacuess + 1, date = CURDATE(), status = 'Evacuated' WHERE barangay_name = ? AND disaster_id = ?");
                if ($upd) {
                    $upd->bind_param("si", $barangayName, $disasterId);
                    $upd->execute();
                    $affected = $upd->affected_rows;
                    $upd->close();
                    if ($affected === 0) {
                        $ins = $conn->prepare("INSERT INTO brgy_record_table (barangay_name, total_evacuess, disaster_id, scale, date, status) VALUES (?, 1, ?, '', CURDATE(), 'Evacuated')");
                        if ($ins) {
                            $ins->bind_param("si", $barangayName, $disasterId);
                            $ins->execute();
                            $ins->close();
                        }
                    }
                }
            }

            // Check registration type and age classification
            $typeStmt->bind_param("i", $memberId);
            $typeStmt->execute();
            $typeResult = $typeStmt->get_result();

            if ($typeResult && $typeRow = $typeResult->fetch_assoc()) {
                $groupType = strtolower(trim($typeRow['registered_as'])); // Convert to lowercase for comparison
                $ageClassId = intval($typeRow['age_class_id']); // Ensure it's an integer

                // Count by registration type (fixed case comparison)
                if ($groupType === 'solo') {
                    $soloCount++;
                } else if ($groupType === 'family') {
                    $familyCount++;
                }

                // Count by age group using age classification
                if (isset($ageClassMap[$ageClassId]) && !empty($ageClassId)) {
                    $classification = $ageClassMap[$ageClassId];
                    $ageGroup = getAgeGroupFromClassification($classification);

                    // Debug logging
                    error_log("Processing Member ID: $memberId, Age Class ID: $ageClassId, Classification: '$classification', Mapped to: '$ageGroup'");

                    switch ($ageGroup) {
                        case 'infant':
                            $infantCount++;
                            break;
                        case 'toddler':
                            $toddlerCount++;
                            break;
                        case 'pre_school':
                            $preschoolCount++;
                            break;
                        case 'school_age':
                            $schoolAgeCount++;
                            break;
                        case 'teenage':
                            $teenageCount++;
                            break;
                        case 'adult':
                            $adultCount++;
                            break;
                        case 'senior':
                            $seniorCount++;
                            break;
                        default:
                            $adultCount++; // Fallback
                            error_log("Unknown age group '$ageGroup' for member $memberId, defaulting to adult");
                            break;
                    }
                } else {
                    // Default to adult if age class not found
                    $adultCount++;
                    error_log("Age class ID $ageClassId not found in map for member $memberId, defaulting to adult");
                }
            } else {
                error_log("No registration data found for member ID: $memberId");
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
        $recordUpdate->bind_param("iiiiiiiiiii", $soloCount, $familyCount, $totalEvacuees, $infantCount, $toddlerCount, $preschoolCount, $schoolAgeCount, $teenageCount, $adultCount, $seniorCount, $evacuationId);
        $recordUpdate->execute();
    } else {
        $recordInsert->bind_param("siiiiiiiiii", $locationName, $soloCount, $familyCount, $totalEvacuees, $infantCount, $toddlerCount, $preschoolCount, $schoolAgeCount, $teenageCount, $adultCount, $seniorCount);
        $recordInsert->execute();
    }

    $conn->commit();

    echo json_encode([
        'success' => true,
        'success_count' => $successCount,
        'skipped_members' => $skipped,
        'registration_breakdown' => [
            'solo' => $soloCount,
            'family' => $familyCount
        ],
        'age_breakdown' => [
            'infant' => $infantCount,
            'toddler' => $toddlerCount,
            'pre_school' => $preschoolCount,
            'school_age' => $schoolAgeCount,
            'teenage' => $teenageCount,
            'adult' => $adultCount,
            'senior' => $seniorCount
        ],
        'total_evacuees' => $totalEvacuees,
        'age_class_mapping' => $ageClassMap, // Debug info
        'message' => 'Registration complete with detailed age breakdown.'
    ]);
} catch (Exception $e) {
    if ($conn->errno) {
        $conn->rollback();
    }

    $errorDetails = sprintf(
        "Register error in %s at line %d: %s",
        $e->getFile(),
        $e->getLine(),
        $e->getMessage()
    );

    error_log($errorDetails);

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $errorDetails . " | Database error: " . $conn->error
    ]);
}

$conn->close();
