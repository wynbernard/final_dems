<?php
include '../../../database/session.php'; // Ensure session and DB connection are included
require_once '../../../database/csrf.php';

// Validate CSRF token
csrf_validate_or_die();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Get form data
        $evac_loc_id = intval($_POST['evac_loc_id'] ?? 0);
        $room_name = trim($_POST['room_name'] ?? '');
        $room_capacity = intval($_POST['room_capacity'] ?? 0);

        // Validate input
        if ($evac_loc_id <= 0) {
            throw new Exception("Invalid location selected.");
        }
        if (empty($room_name)) {
            throw new Exception("Room name is required.");
        }
        if (strlen($room_name) > 100) {
            throw new Exception("Room name must be 100 characters or less.");
        }
        if ($room_capacity <= 0) {
            throw new Exception("Room capacity must be greater than 0.");
        }

        // Insert the new room into the database under the selected location
        $query = "INSERT INTO room_table (evac_loc_id, room_name, room_capacity) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            error_log("Add room prepare failed: " . $conn->error);
            throw new Exception("Failed to add room. Please try again.");
        }
        
        $stmt->bind_param("isi", $evac_loc_id, $room_name, $room_capacity);

        if (!$stmt->execute()) {
            error_log("Add room execute failed: " . $stmt->error);
            throw new Exception("Failed to add room. Please try again.");
        }

        // Calculate new total capacity for the location
        $capacity_query = "SELECT SUM(room_capacity) as total_capacity FROM room_table WHERE evac_loc_id = ?";
        $stmt_capacity = $conn->prepare($capacity_query);
        
        if (!$stmt_capacity) {
            error_log("Capacity query prepare failed: " . $conn->error);
            throw new Exception("Failed to update location capacity. Please try again.");
        }
        
        $stmt_capacity->bind_param("i", $evac_loc_id);
        $stmt_capacity->execute();
        $result = $stmt_capacity->get_result();
        $row = $result->fetch_assoc();
        $total_capacity = $row['total_capacity'] ?? 0;
        $stmt_capacity->close();

        // Update the location's capacity
        $update_loc_query = "UPDATE evac_loc_table SET total_capacity = ? WHERE evac_loc_id = ?";
        $stmt_loc = $conn->prepare($update_loc_query);
        
        if (!$stmt_loc) {
            error_log("Location update prepare failed: " . $conn->error);
            throw new Exception("Failed to update location capacity. Please try again.");
        }
        
        $stmt_loc->bind_param("ii", $total_capacity, $evac_loc_id);

        if (!$stmt_loc->execute()) {
            error_log("Location update execute failed: " . $stmt_loc->error);
            throw new Exception("Failed to update location capacity. Please try again.");
        }
        
        $stmt_loc->close();
        $stmt->close();

        $_SESSION['success'] = "<span style='color: green;'><i class='bi bi-check-circle-fill'></i></span> Add room Successful!";
        header("Location: ../admin_page/rooms.php?evac_loc_id=$evac_loc_id");
        exit();

    } catch (Exception $e) {
        $_SESSION['error'] = "<span style='color:red;'><i class='bi bi-exclamation-circle-fill'></i></span> " . $e->getMessage();
        header("Location: ../admin_page/rooms.php?evac_loc_id=$evac_loc_id");
        exit();
    }
}
