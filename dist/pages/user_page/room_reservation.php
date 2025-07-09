<!doctype html>
<html lang="en">

<head>
	<?php include '../../../database/user_session.php'; ?>
	<?php include '../layout_user/head_links.php'; ?>
	<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
</head>

<?php
$evac_loc_id = 1;

$query = "
	SELECT 
		l.evac_loc_id,
		l.name AS location_name,
		r.room_id,
		r.room_name,
		r.room_capacity,
		COUNT(DISTINCT p.pre_reg_id) AS idp_count,
		SUM(COALESCE(p.family_id, 1)) AS total_family_members
	FROM evac_loc_table l
	LEFT JOIN room_table r ON l.evac_loc_id = r.evac_loc_id
	LEFT JOIN room_reservation_table e ON r.room_id = e.room_id
	LEFT JOIN pre_reg_table p ON e.pre_reg_id = p.pre_reg_id
	LEFT JOIN family_table f ON p.family_id = f.family_id
	LEFT JOIN age_class_table a ON p.age_class_id = a.age_class_id
	WHERE l.evac_loc_id = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $evac_loc_id);
$stmt->execute();
$result = $stmt->get_result();

$rooms = [];
while ($row = $result->fetch_assoc()) {
	$rooms[] = $row;
}

$user_reservation_query = "
    SELECT 
        r.room_id,
        r.room_name,
        l.name AS location_name,
        e.status,
        e.date_time,
        (SELECT COUNT(*) FROM pre_reg_table p WHERE p.family_id = (
            SELECT family_id FROM pre_reg_table WHERE pre_reg_id = ?
        )) AS family_member_count
    FROM room_reservation_table e
    JOIN room_table r ON e.room_id = r.room_id
    JOIN evac_loc_table l ON r.evac_loc_id = l.evac_loc_id
    WHERE e.pre_reg_id = ?
    GROUP BY r.room_id, r.room_name, l.name, e.status, e.date_time
    LIMIT 1;
";

$user_stmt = $conn->prepare($user_reservation_query);
$user_stmt->bind_param("ii", $_SESSION['pre_reg_id'], $_SESSION['pre_reg_id']);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_reservation = $user_result->fetch_assoc();
?>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
	<div class="app-wrapper">
		<?php include '../layout_user/header.php'; ?>
		<?php include '../layout_user/sidebar.php'; ?>
		<?php include '../alert/warning.php'; ?>

		<main class="app-main bg-light">

			<!-- Main Content -->
			<div class="content container-fluid py-4">
				<div class="row mt-2">
					<div class="col-12">
						<div class="card rounded-4 shadow-sm border-0 overflow-hidden">
							<!-- Card Header with Icon -->
							<div class="card-header bg-white border-0 py-3">
								<div class="d-flex justify-content-between align-items-center flex-wrap">
									<div class="d-flex align-items-center">
										<div class="icon-shape bg-primary-light rounded-3 p-3 me-3">
											<i class="bi bi-house-check-fill text-primary fs-4"></i>
										</div>
										<div>
											<h5 class="mb-0 fw-bold text-dark">Room Availability</h5>
											<p class="text-muted mb-0">Check available rooms and make reservations</p>
										</div>
									</div>
									<!-- <a href="location_availability.php" class="btn btn-primary btn-lg rounded-pill px-4 py-2 d-inline-flex align-items-center shadow-sm mt-2 mt-sm-0">
										<i class="bi bi-calendar-check me-2"></i> Check Availability
									</a> -->
								</div>
							</div>

							<!-- Card Body -->
							<div class="card-body p-4">
								<?php if ($user_reservation): ?>
									<div class="row g-4">
										<!-- Map Column -->
										<div class="col-lg-6">
											<div class="position-relative rounded-4 overflow-hidden shadow-sm h-100 min-h-300">
												<div id="reservationMap" class="w-100 h-100">
													<!-- Map will be rendered here -->
												</div>
												<div class="position-absolute top-0 end-0 m-3">
													<span class="badge bg-success bg-opacity-90 text-white px-3 py-2 rounded-pill shadow-sm">
														<i class="bi bi-check-circle-fill me-1"></i> Reserved
													</span>
												</div>
											</div>
										</div>

										<!-- Reservation Info -->
										<div class="col-lg-6">
											<div class="bg-light rounded-4 p-4 h-100 d-flex flex-column">
												<div class="mb-4">
													<h6 class="fw-bold text-dark mb-3 d-flex align-items-center">
														<i class="bi bi-house-door-fill text-primary me-2"></i>
														Your Reservation Details
													</h6>

													<div class="d-flex mb-3">
														<div class="me-3">
															<div class="bg-primary-light rounded-3 p-2 text-center" style="width: 60px;">
																<i class="bi bi-building text-primary fs-4"></i>
															</div>
														</div>
														<div>
															<h6 class="fw-bold mb-1"><?= htmlspecialchars($user_reservation['location_name']); ?></h6>
															<p class="text-muted mb-0"><?= htmlspecialchars($user_reservation['room_name']); ?></p>
														</div>
													</div>

													<div class="d-flex mb-3">
														<div class="me-3">
															<div class="bg-primary-light rounded-3 p-2 text-center" style="width: 60px;">
																<i class="bi bi-calendar-date text-primary fs-4"></i>
															</div>
														</div>
														<div>
															<h6 class="fw-bold mb-1">Reservation Date</h6>
															<p class="text-muted mb-0"><?= date('F j, Y', strtotime($user_reservation['date_time'])); ?></p>
														</div>
													</div>

													<div class="d-flex">
														<div class="me-3">
															<div class="bg-primary-light rounded-3 p-2 text-center" style="width: 60px;">
																<i class="bi bi-people-fill text-primary fs-4"></i>
															</div>
														</div>
														<div>
															<h6 class="fw-bold mb-1">Family Members</h6>
															<p class="text-muted mb-0"><?= $user_reservation['family_member_count']; ?> persons</p>
														</div>
													</div>
												</div>

												<div class="mt-auto pt-3">
													<div class="d-flex gap-3 flex-wrap">
														<?php if ($user_reservation['status'] === 'Intended'): ?>
															<button class="btn btn-success px-4 rounded-pill disabled">
																<i class="bi bi-check-circle-fill me-1"></i> Intention Confirmed
															</button>
														<?php else: ?>
															<form id="intentForm" action="../action_user/reservation_intent.php" method="POST">
																<input type="hidden" name="room_id" value="<?= $user_reservation['room_id']; ?>">
																<button type="button" class="btn btn-success px-4 rounded-pill shadow-sm" onclick="confirmIntent()">
																	<i class="bi bi-check-circle me-1"></i> Confirm Intention
																</button>
															</form>
														<?php endif; ?>

														<form class="cancel-reservation-form" method="POST" action="../action_user/delete_reservation.php">
															<input type="hidden" name="room_id" value="<?= $user_reservation['room_id']; ?>">
															<button type="submit" class="btn btn-outline-danger px-4 rounded-pill">
																<i class="bi bi-trash me-1"></i> Cancel Reservation
															</button>
														</form>
													</div>
												</div>
											</div>
										</div>
									</div>
								<?php else: ?>
									<!-- Empty State -->
									<div class="text-center py-5">
										<div class="mb-4">
											<i class="bi bi-house-exclamation-fill text-muted fs-1 opacity-25"></i>
										</div>
										<h5 class="fw-bold text-dark mb-3">No Active Reservations</h5>
										<p class="text-muted mb-4">You currently don't have any room reservations. Check availability to book a safe space.</p>
										<a href="location_availability.php" class="btn btn-primary rounded-pill px-4 py-2">
											<i class="bi bi-search me-1"></i> Find Available Rooms
										</a>
									</div>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</main>

		<?php include '../layout_user/footer.php';
		include '../scripts/room_reservation.php';
		include '../css/room_reservation.php'; ?>
	</div>





	<?php include '../scripts/scripts.php'; ?>
	<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

	<?php if ($user_reservation): ?>
		<script>
			document.addEventListener('DOMContentLoaded', () => {
				const locationName = <?= json_encode($user_reservation['location_name']); ?>;
				fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(locationName)}`)
					.then(res => res.json())
					.then(data => {
						if (data.length > 0) {
							const lat = parseFloat(data[0].lat);
							const lon = parseFloat(data[0].lon);

							const map = L.map('reservationMap').setView([lat, lon], 15);
							L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
								attribution: '&copy; OpenStreetMap contributors'
							}).addTo(map);

							L.marker([lat, lon])
								.addTo(map)
								.bindPopup(`<b>Your Reserved Location</b><br>${locationName}`)
								.openPopup();
						} else {
							console.warn("Location not found.");
						}
					})
					.catch(err => console.error("Geocoding error:", err));
			});
		</script>
	<?php endif; ?>
</body>

</html>