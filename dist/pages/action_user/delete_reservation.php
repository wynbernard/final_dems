<?php
include '../../../database/user_session.php'; // DB connection & session

// Check if user is logged in
if (!isset($_SESSION['pre_reg_id'])) {
    $_SESSION['error'] = 'Please log in to cancel your reservation.';
    header("Location: ../user_page/room_reservation.php");
    exit();
}

$pre_reg_id = $_SESSION['pre_reg_id'];

// Check if form was submitted and room_id is set
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['room_id']) && !empty($_POST['room_id'])) {
    $room_id = $_POST['room_id'];

    // Step 1: Get family_id of the logged-in user
    $stmt = $conn->prepare("SELECT family_id FROM pre_reg_table WHERE pre_reg_id = ?");
    $stmt->bind_param("i", $pre_reg_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['error'] = 'Family information not found.';
        header("Location: ../user_page/room_reservation.php");
        exit();
    }

    $family_id = $result->fetch_assoc()['family_id'];

    // Step 2: Get all pre_reg_id of family members
    $stmt = $conn->prepare("SELECT pre_reg_id FROM pre_reg_table WHERE family_id = ?");
    $stmt->bind_param("i", $family_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $family_members = [];
    while ($row = $result->fetch_assoc()) {
        $family_members[] = $row['pre_reg_id'];
    }

    if (empty($family_members)) {
        $_SESSION['error'] = 'No family members found to cancel reservation.';
        header("Location: ../user_page/room_reservation.php");
        exit();
    }

    // Step 3: Delete reservation for all family members
    $placeholders = implode(',', array_fill(0, count($family_members), '?'));
    $types = str_repeat('i', count($family_members) + 1); // All integers
    $params = array_merge([$room_id], $family_members);

    $query = "DELETE FROM room_reservation_table WHERE room_id = ? AND pre_reg_id IN ($placeholders)";
    $stmt = $conn->prepare($query);

    // Bind parameters dynamically
    $stmt->bind_param($types, ...$params);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $_SESSION['success'] = 'Reservation successfully cancelled for all family members.';
    } else {
        $_SESSION['error'] = 'No reservation found or already cancelled.';
    }
} else {
    $_SESSION['error'] = 'Invalid request.';
}

// Redirect back to room reservation page
header("Location: ../user_page/room_reservation.php");
exit();
