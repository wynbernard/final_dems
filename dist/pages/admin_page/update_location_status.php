<?php
// update_location_status.php
include '../../../database/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $evac_loc_id = intval($_POST['evac_loc_id'] ?? 0);
    $new_status = ($_POST['status'] === 'Active') ? 'Active' : 'Inactive';
    if ($evac_loc_id > 0) {
        $stmt = $conn->prepare("UPDATE evac_loc_table SET status = ? WHERE evac_loc_id = ?");
        $stmt->bind_param("si", $new_status, $evac_loc_id);
        if ($stmt->execute()) {
            echo 'success';
        } else {
            echo 'error';
        }
        $stmt->close();
    } else {
        echo 'invalid';
    }
} else {
    echo 'invalid';
}
?>
