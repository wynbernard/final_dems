<?php
include '../../../database/session.php'; // Ensure session and DB connection are included

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Get form data
        $evac_loc_id = intval($_POST['evac_loc_id']); // Selected location ID
        $room_name = mysqli_real_escape_string($conn, $_POST['room_name']);
        $room_capacity = intval($_POST['room_capacity']);

        // Insert the new room into the database under the selected location
        $query = "INSERT INTO room_table (evac_loc_id, room_name, room_capacity) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "isi", $evac_loc_id, $room_name, $room_capacity);

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Add room failed: " . mysqli_error($conn));
        }

        // Calculate new total capacity for the location
        $capacity_query = "SELECT SUM(room_capacity) as total_capacity FROM room_table WHERE evac_loc_id = ?";
        $stmt_capacity = mysqli_prepare($conn, $capacity_query);
        mysqli_stmt_bind_param($stmt_capacity, "i", $evac_loc_id);
        mysqli_stmt_execute($stmt_capacity);
        $result = mysqli_stmt_get_result($stmt_capacity);
        $row = mysqli_fetch_assoc($result);
        $total_capacity = $row['total_capacity'] ?? 0;

        // Update the location's capacity
        $update_loc_query = "UPDATE evac_loc_table SET total_capacity = ? WHERE evac_loc_id = ?";
        $stmt_loc = mysqli_prepare($conn, $update_loc_query);
        mysqli_stmt_bind_param($stmt_loc, "ii", $total_capacity, $evac_loc_id);

        if (!mysqli_stmt_execute($stmt_loc)) {
            throw new Exception("Location capacity update failed: " . mysqli_error($conn));
        }

        $_SESSION['success'] = "<span style='color: green;'><i class='bi bi-check-circle-fill'></i></span> Add room Successful!";
        header("Location: ../admin_page/rooms.php?evac_loc_id=$evac_loc_id");
        exit();

    } catch (Exception $e) {
        $_SESSION['error'] = "<span style='color:red;'><i class='bi bi-exclamation-circle-fill'></i></span> " . $e->getMessage();
        header("Location: ../admin_page/rooms.php?evac_loc_id=$evac_loc_id");
        exit();
    }
}
