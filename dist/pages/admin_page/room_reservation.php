<?php
include '../../../database/session.php';
include '../layout/head_links.php';

// Get the selected evacuation location ID
$evac_loc_id = isset($_GET['evac_loc_id']) ? intval($_GET['evac_loc_id']) : 0;

if ($evac_loc_id <= 0) {
	die("Invalid evacuation location ID.");
}

// Fetch location details along with rooms
$query = "
    SELECT 
        l.evac_loc_id,
        l.name AS location_name,
        r.room_id,
        r.room_name,
        r.room_capacity,
        COUNT(DISTINCT p.pre_reg_id) AS idp_count,
        (r.room_capacity - COUNT(DISTINCT p.pre_reg_id)) AS available_space
    FROM evac_loc_table l
    LEFT JOIN room_table r ON l.evac_loc_id = r.evac_loc_id
    LEFT JOIN room_reservation_table e ON r.room_id = e.room_id
    LEFT JOIN pre_reg_table p ON e.pre_reg_id = p.pre_reg_id
    LEFT JOIN age_class_table a ON p.age_class_id = a.age_class_id
    WHERE l.evac_loc_id = ?
    GROUP BY l.evac_loc_id, l.name, r.room_id, r.room_name, r.room_capacity
    ORDER BY r.room_name ASC";


$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $evac_loc_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Fetch the first row separately for the location name
$location = mysqli_fetch_assoc($result);

if (!$location) {
	die("Location not found.");
}

$location_name = $location['location_name'] ?? "Unknown Location";

// Reset the result set for further use
mysqli_data_seek($result, 0);
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<title>Location Management</title>
	<style>
		.location-container {
			display: flex;
			flex-direction: column;
			gap: 20px;
		}

		.location-header {
			background-color: #f8f9fa;
			padding: 20px;
			border-radius: 8px;
			box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
		}

		.location-content {
			display: flex;
			flex-wrap: wrap;
			gap: 20px;
		}

		.location-map-col {
			flex: 1 1 100%;
		}

		.location-rooms-col {
			flex: 1 1 100%;
		}

		@media (min-width: 992px) {
			.location-content {
				flex-wrap: nowrap;
			}

			.location-map-col {
				flex: 0 0 40%;
				max-width: 40%;
			}

			.location-rooms-col {
				flex: 0 0 60%;
				max-width: 60%;
			}
		}

		.card {
			height: 100%;
			display: flex;
			flex-direction: column;
		}

		.card-body {
			flex-grow: 1;
		}

		.table-responsive {
			overflow-x: auto;
		}

		.action-buttons {
			display: flex;
			flex-wrap: wrap;
			gap: 5px;
		}

		.action-buttons .btn {
			white-space: nowrap;
		}

		.progress-bar {
			text-align: center;
			font-size: 12px;
			color: #fff;
		}

		.location-map-col .card {
			transition: transform 0.2s ease, box-shadow 0.2s ease;
		}

		.location-map-col .card:hover {
			transform: translateY(-2px);
			box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1) !important;
		}

		#locationMap {
			min-height: 300px;
			width: 100%;
		}
	</style>
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
	<div class="app-wrapper">
		<?php include '../layout/header.php';
		include '../layout/sidebar.php';
		include '../alert/warning.php';
		?>

		<main class="app-main">
			<div class="app-content-header">
				<div class="container-fluid">
					<div class="row">
						<div class="col-sm-6">
							<h3>Location : <strong><?php echo htmlspecialchars($location['location_name']); ?></strong></h3>
						</div>
						<div class="col-sm-6">
							<ol class="breadcrumb float-sm-end">
								<li class="breadcrumb-item"><a href="#">Home</a></li>
								<li class="breadcrumb-item active" aria-current="page">Location Reservation</li>
							</ol>
						</div>
					</div>
				</div>
			</div>

			<div class="container location-container">
				<div class="location-content">
					<div class="location-map-col">
						<div class="card border-0 shadow-sm mb-4">
							<div class="card-header bg-white border-bottom-0 pb-0">
								<div class="d-flex justify-content-between align-items-center">
									<h5 class="card-title mb-0 font-weight-bold text-primary">
										<i class="fas fa-map-marker-alt mr-2"></i>Location
									</h5>
									<button class="btn btn-sm btn-outline-secondary" data-toggle="tooltip" title="Get directions">
										<i class="fas fa-directions"></i>
									</button>
								</div>
								<div class="mt-2">
									<p class="text-dark font-weight-medium">Address: <?php echo htmlspecialchars($location_name); ?></p>
								</div>
							</div>
							<div class="card-body p-0">
								<div id="locationMap" style="height: 300px; border-bottom-left-radius: 0.25rem; border-bottom-right-radius: 0.25rem;"></div>
								<div class="p-3 bg-light border-top d-flex justify-content-between align-items-center">
									<small class="text-muted">
										<i class="fas fa-info-circle mr-1"></i> Click and drag to explore the area
									</small>
								</div>
							</div>
						</div>
					</div>
					<div class="location-rooms-col">
						<div class="card">
							<div class="card-header d-flex align-items-center">
								<input type="text" id="searchBox" class="form-control me-2" placeholder="Search..." style="max-width: 300px;">
								<button type="button" class="btn btn-primary btn-sm ms-auto" data-bs-toggle="modal" data-bs-target="#addRoomModal">
									<i class="fas fa-plus-circle"></i> Add Room
								</button>
							</div>

							<div class="card-body">
								<div class="table-responsive">
									<table id="locationTable" class="table table-sm">
										<thead class="table-primary">
											<tr>
												<th>No.</th>
												<th><i class="fas fa-door-open" style="margin-right: 6px; color: #007BFF;"></i>Room Name</th>
												<th><i class="fas fa-users" style="margin-right: 6px; color: #007BFF;"></i>Total Capacity</th>
												<th><i class="fas fa-cogs" style="margin-right: 6px; color: #007BFF;"></i>Actions</th>

											</tr>
										</thead>
										<tbody>
											<?php
											if (mysqli_num_rows($result) > 0) {
												mysqli_data_seek($result, 0);
												$counter = 1;

												while ($room = mysqli_fetch_assoc($result)): ?>
													<tr>
														<td><?php echo $counter++; ?>.</td>
														<td><?php echo htmlspecialchars($room['room_name'] ?? 'No Room Assigned'); ?></td>
														<td>
															<?php
															$capacity = $room['idp_count'] ?? 0; // Current occupancy
															$max_capacity = $room['room_capacity']; // Maximum room capacity
															$percentage = ($max_capacity > 0) ? ($capacity / $max_capacity) * 100 : 0; // Calculate the percentage of occupancy
															?>

															<div>
																<!-- Display current capacity and max capacity -->
																<div class="d-flex justify-content-between">
																	<span><?php echo htmlspecialchars($capacity); ?></span>
																	<span class="text-muted">/<?php echo $max_capacity; ?></span>
																</div>

																<!-- Progress bar showing the occupancy percentage -->
																<div class="progress" style="height: 10px;">
																	<div class="progress-bar" role="progressbar"
																		style="width: <?php echo $percentage; ?>%;"
																		aria-valuenow="<?php echo $percentage; ?>"
																		aria-valuemin="0" aria-valuemax="100">
																		<?php echo round($percentage); ?>%
																	</div>
																</div>
															</div>
														</td>


														<td>
															<div class="action-buttons">
																<?php if ($room['room_id']): ?>
																	<a href="#" class="btn btn-info btn-sm view-idp-btn"
																		data-id="<?php echo $room['room_id']; ?>"
																		data-location="<?php echo htmlspecialchars($room['location_name'] ?? 'Unknown Location'); ?>"
																		data-bs-toggle="modal" data-bs-target="#viewReservationModal">
																		<i class="fas fa-users"></i>
																		(<?php echo $room['idp_count']; ?> / <?php echo $room['room_capacity']; ?>)
																	</a>

																<?php else: ?>
																	<span class="text-muted">No actions available</span>
																<?php endif; ?>
															</div>
														</td>
													</tr>
											<?php endwhile;
											} else {
												echo "<tr><td colspan='4' class='text-center'>No rooms found for this location.</td></tr>";
											}
											?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</main>

		<?php
		include '../layout/footer.php';
		include '../modal/modal_room.php';
		include '../modal/view_idps_reservation.php';
		include '../scripts/rooms.php'; ?>
	</div>

	<!-- Include Leaflet.js (for interactive maps) -->
	<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
	<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

</body>

</html>