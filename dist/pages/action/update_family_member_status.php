<?php
header('Content-Type: application/json');
include '../../../database/session.php';

// CSRF Protection
require_once '../../../database/csrf.php';

// Check if it's an AJAX request
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Validate CSRF token (use AJAX validation for AJAX requests)
if ($isAjax) {
    if (!csrf_validate_ajax()) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'CSRF token validation failed. Please refresh the page and try again.'
        ]);
        exit();
    }
} else {
    csrf_validate_or_die();
}


try {
    // Validate request
    if (!isset($_POST['pre_reg_id']) || !isset($_POST['action']) || !isset($_POST['evac_reg_id'])) {
        throw new Exception("Missing required parameters.");
    }

    $preRegId = intval($_POST['pre_reg_id']);
    $action = $_POST['action']; // 'mark_present' or 'mark_absent'
    $evacRegId = intval($_POST['evac_reg_id']); // Head's evac_reg_id

    // Validate action
    if (!in_array($action, ['mark_present', 'mark_absent'])) {
        throw new Exception("Invalid action specified.");
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        if ($action === 'mark_present') {
            // Get head's registration details
            $headQuery = "SELECT evac_loc_id, room_id, disaster_id, date_reg FROM evac_reg_table WHERE evac_reg_id = ? LIMIT 1";
            $headStmt = $conn->prepare($headQuery);
            $headStmt->bind_param("i", $evacRegId);
            $headStmt->execute();
            $headResult = $headStmt->get_result();
            
            if ($headResult->num_rows === 0) {
                throw new Exception("Head registration not found.");
            }
            
            $head = $headResult->fetch_assoc();
            $evacLocId = $head['evac_loc_id'];
            $roomId = $head['room_id'];
            $disasterId = $head['disaster_id'];
            $dateReg = date('Y-m-d'); // Use current date for registration

            // Check if member has an existing evacuation record (with any status)
            $checkQuery = "SELECT evac_reg_id FROM evac_reg_table WHERE pre_reg_id = ? LIMIT 1";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bind_param("i", $preRegId);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();

            if ($checkResult->num_rows > 0) {
                // Member has an existing record, update it
                $existingEvacRegId = $checkResult->fetch_assoc()['evac_reg_id'];
                $updateQuery = "UPDATE evac_reg_table SET evac_loc_id = ?, room_id = ?, date_reg = ?, disaster_id = ?, status = 'Evacuated' WHERE evac_reg_id = ?";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->bind_param("iiisi", $evacLocId, $roomId, $dateReg, $disasterId, $existingEvacRegId);
                $updateStmt->execute();
                $memberEvacRegId = $existingEvacRegId;
                $updateStmt->close();
                $checkStmt->close();
            } else {
                // Insert new record into evac_reg_table
                $insertQuery = "INSERT INTO evac_reg_table (pre_reg_id, evac_loc_id, room_id, date_reg, disaster_id, status) VALUES (?, ?, ?, ?, ?, 'Evacuated')";
                $insertStmt = $conn->prepare($insertQuery);
                $insertStmt->bind_param("iiisi", $preRegId, $evacLocId, $roomId, $dateReg, $disasterId);
                $insertStmt->execute();
                $memberEvacRegId = $insertStmt->insert_id;
                $insertStmt->close();
                $checkStmt->close();
            }

            // Insert log entry
            $logQuery = "INSERT INTO logs_table (evac_reg_id, status, date_time) VALUES (?, 'IN', NOW())";
            $logStmt = $conn->prepare($logQuery);
            $logStmt->bind_param("i", $memberEvacRegId);
            $logStmt->execute();
            $logStmt->close();

            // Get barangay for this member
            $brgyQuery = "SELECT b.barangay_name 
                FROM pre_reg_table pr 
                LEFT JOIN solo_address_table sat ON pr.solo_address_id = sat.solo_address_id 
                LEFT JOIN family_table ft ON pr.family_id = ft.family_id 
                LEFT JOIN barangay_manegement_table b ON COALESCE(sat.barangay_id, ft.barangay_id) = b.barangay_id 
                WHERE pr.pre_reg_id = ? LIMIT 1";
            $brgyStmt = $conn->prepare($brgyQuery);
            $brgyStmt->bind_param("i", $preRegId);
            $brgyStmt->execute();
            $brgyResult = $brgyStmt->get_result();
            $barangay = $brgyResult->num_rows > 0 ? $brgyResult->fetch_assoc()['barangay_name'] : null;
            $brgyStmt->close();

            // Update barangay record if barangay found
            if ($barangay) {
                $recordCheck = $conn->prepare("SELECT evacuation_id FROM evacuation_record_table WHERE evacuation_location = ? AND end_date IS NULL");
                $recordCheck->bind_param("s", $barangay);
                $recordCheck->execute();
                $recordResult = $recordCheck->get_result();

                if ($recordResult->num_rows > 0) {
                    $recordId = $recordResult->fetch_assoc()['evacuation_id'];
                    
                    // Get member's age class
                    $ageQuery = "SELECT age_class_id FROM pre_reg_table WHERE pre_reg_id = ?";
                    $ageStmt = $conn->prepare($ageQuery);
                    $ageStmt->bind_param("i", $preRegId);
                    $ageStmt->execute();
                    $ageResult = $ageStmt->get_result();
                    $ageClassId = $ageResult->num_rows > 0 ? $ageResult->fetch_assoc()['age_class_id'] : null;
                    $ageStmt->close();

                    if ($ageClassId) {
                        $recordUpdate = $conn->prepare("UPDATE evacuation_record_table SET total_evacuation = total_evacuation + 1 WHERE evacuation_id = ?");
                        $recordUpdate->bind_param("i", $recordId);
                        $recordUpdate->execute();
                        $recordUpdate->close();
                    }
                }
                $recordCheck->close();
            }

            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Member marked as present successfully.']);

        } else if ($action === 'mark_absent') {
            // Get member's evac_reg_id and related information
            $memberQuery = "SELECT evac_reg_id FROM evac_reg_table WHERE pre_reg_id = ? AND status = 'Evacuated' LIMIT 1";
            $memberStmt = $conn->prepare($memberQuery);
            $memberStmt->bind_param("i", $preRegId);
            $memberStmt->execute();
            $memberResult = $memberStmt->get_result();

            if ($memberResult->num_rows === 0) {
                $conn->rollback();
                echo json_encode(['success' => false, 'message' => 'Member is already marked as absent.']);
                exit();
            }

            $memberEvacRegId = $memberResult->fetch_assoc()['evac_reg_id'];
            $memberStmt->close();

            // Insert OUT log before deleting
            $logQuery = "INSERT INTO logs_table (evac_reg_id, status, date_time) VALUES (?, 'OUT', NOW())";
            $logStmt = $conn->prepare($logQuery);
            $logStmt->bind_param("i", $memberEvacRegId);
            $logStmt->execute();
            $logStmt->close();

            // Update status to 'Left' instead of deleting
            $updateQuery = "UPDATE evac_reg_table SET status = 'Left' WHERE evac_reg_id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("i", $memberEvacRegId);
            $updateStmt->execute();
            $updateStmt->close();

            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Member marked as absent successfully.']);

        }

    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    error_log("Error in update_family_member_status.php: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'An error occurred while updating member status. Please try again.'
    ]);
}
?>
