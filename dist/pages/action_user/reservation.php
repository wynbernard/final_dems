<?php

include '../../../database/user_session.php';  // DB connection

if (!isset($_SESSION['pre_reg_id'])) {
	$_SESSION['error'] = 'Please log in to reserve.';
	header("Location: login.php");
	exit();
}

if (!isset($_GET['room_id']) || empty($_GET['room_id'])) {
	$_SESSION['error'] = 'Invalid room selection.';
	header("Location: ../pages_user/location_availability.php");
	exit();
}

$room_id = $_GET['room_id'];
$user_id = $_SESSION['pre_reg_id'];

// 🔍 Get user's family_id
$family_query = "SELECT family_id FROM pre_reg_table WHERE pre_reg_id = ?";
$stmt = $conn->prepare($family_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
	$_SESSION['error'] = 'Family data not found.';
	header("Location: ../pages_user/location_availability.php");
	exit();
}

$family_id = $result->fetch_assoc()['family_id'];

// 🧍‍♂️🧍‍♀️ Get all family members
$members_query = "SELECT pre_reg_id FROM pre_reg_table WHERE family_id = ?";
$stmt = $conn->prepare($members_query);
$stmt->bind_param("i", $family_id);
$stmt->execute();
$members_result = $stmt->get_result();

$family_members = [];
while ($row = $members_result->fetch_assoc()) {
	$family_members[] = $row['pre_reg_id'];
}

// ❌ Check if any family member already has a reservation
$placeholders = implode(',', array_fill(0, count($family_members), '?'));
$types = str_repeat('i', count($family_members));

$check_sql = "SELECT pre_reg_id FROM room_reservation_table WHERE pre_reg_id IN ($placeholders)";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param($types, ...$family_members);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
	$_SESSION['error'] = 'Already have a reservation.';
	header("Location: ../user_page/location_availability.php");
	exit();
}

// 🔢 Check total available slots
$room_query = "SELECT room_capacity FROM room_table WHERE room_id = ?";
$stmt = $conn->prepare($room_query);
$stmt->bind_param("i", $room_id);
$stmt->execute();
$room_result = $stmt->get_result();
$room = $room_result->fetch_assoc();
$room_capacity = $room['room_capacity'];

$occupied = getOccupiedSlots($room_id);
$available = $room_capacity - $occupied;

if (count($family_members) > $available) {
	$_SESSION['error'] = 'Not enough space for the whole family.';
	header("Location: ../user_page/location_availability.php");
	exit();
}

// ✅ Reserve for each family member
$reserve_stmt = $conn->prepare("INSERT INTO room_reservation_table (pre_reg_id, room_id, date_time) VALUES (?, ?, NOW())");

foreach ($family_members as $member_id) {
	$reserve_stmt->bind_param("ii", $member_id, $room_id);
	$reserve_stmt->execute();
}

$_SESSION['show_modal'] = true;
$_SESSION['success'] = 'Reservation successful for all family members.';
header("Location: ../user_page/room_reservation.php"); // Go back to the room availability list
exit();


// 🔁 Helper
function getOccupiedSlots($room_id)
{
	global $conn;
	$query = "SELECT COUNT(*) AS occupied FROM room_reservation_table WHERE room_id = ?";
	$stmt = $conn->prepare($query);
	$stmt->bind_param("i", $room_id);
	$stmt->execute();
	$result = $stmt->get_result();
	return $result->fetch_assoc()['occupied'];
}
?>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header bg-success text-white">
				<h5 class="modal-title" id="confirmationModalLabel">Reservation Confirmed</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<?= $_SESSION['success'] ?? 'Your reservation was successful.' ?>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<?php if (isset($_SESSION['show_modal']) && $_SESSION['show_modal']): ?>
	<script>
		document.addEventListener("DOMContentLoaded", function() {
			let confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
			confirmationModal.show();
		});
	</script>
<?php
	// Clear modal session so it doesn’t show again on refresh
	unset($_SESSION['show_modal']);
	unset($_SESSION['success']);
endif; ?>