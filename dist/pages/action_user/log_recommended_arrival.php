<?php
// log_recommended_arrival.php
include '../../../database/user_session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $evac_loc_id = intval($_POST['evac_loc_id'] ?? 0);
    $user_id = intval($_POST['pre_reg_id'] ?? 0); // Should be provided by session or POST
    if ($evac_loc_id > 0 && $user_id > 0) {
        // Check if the location exists
        $check = $conn->prepare("SELECT evac_loc_id FROM evac_loc_table WHERE evac_loc_id = ? LIMIT 1");
        $check->bind_param("i", $evac_loc_id);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            $check->close();
            // Get the family_id for the current user
            $fam_stmt = $conn->prepare("SELECT family_id FROM pre_reg_table WHERE pre_reg_id = ? LIMIT 1");
            $fam_stmt->bind_param("i", $user_id);
            $fam_stmt->execute();
            $fam_result = $fam_stmt->get_result();
            if ($fam_row = $fam_result->fetch_assoc()) {
                $family_id = $fam_row['family_id'];
                $fam_stmt->close();
                // If status=arrive, update status as well
                $status = isset($_POST['status']) && $_POST['status'] === 'arrive' ? 'arrive' : null;
                if ($status) {
                    $update_stmt = $conn->prepare("UPDATE pre_reg_table SET recommended_location = ?, status = ? WHERE family_id = ?");
                    $update_stmt->bind_param("isi", $evac_loc_id, $status, $family_id);
                } else {
                    $update_stmt = $conn->prepare("UPDATE pre_reg_table SET recommended_location = ? WHERE family_id = ?");
                    $update_stmt->bind_param("ii", $evac_loc_id, $family_id);
                }
                if ($update_stmt->execute()) {
                    if ($update_stmt->affected_rows > 0) {
                        echo 'success';
                    } else {
                        echo 'no_change';
                    }
                } else {
                    echo 'error: ' . $update_stmt->error;
                }
                $update_stmt->close();
            } else {
                echo 'error: family_id not found';
            }
        } else {
            echo 'invalid_location';
        }
    } else {
        echo 'invalid: evac_loc_id=' . $evac_loc_id . ', pre_reg_id=' . $user_id;
    }
} else {
    echo 'invalid';
}
?>
