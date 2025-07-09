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
			l.latitude,
			l.longitude,
		COUNT(CASE WHEN a.classification != 'Infant' THEN e.evac_reg_id END) AS idp_count
		FROM evac_loc_table l
		LEFT JOIN room_table r ON l.evac_loc_id = r.evac_loc_id
		LEFT JOIN evac_reg_table e ON r.room_id = e.room_id
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

$latitude = $location['latitude'] ?? 0;
$longitude = $location['longitude'] ?? 0;

if (!$location) {
	die("Location not found.");
}

$location_name = $location['location_name'] ?? "Unknown Location";

// Reset the result set for further use
mysqli_data_seek($result, 0);
?>
<!DOCTYPE html>
<html lang="en">
<link rel="stylesheet" href="../css/evac_location/rooms.css">

<head>
	<title>Location Management</title>
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
						<div class="col-sm-9">
							<h3>
								<i class="fas fa-map-marker-alt text-danger me-2 fs-2"></i>
								Location : <strong><?php echo htmlspecialchars($location['location_name']); ?></strong>
							</h3>
						</div>
						<!-- <div class="col-sm-6">
							<ol class="breadcrumb float-sm-end">
								<li class="breadcrumb-item"><a href="#">Home</a></li>
								<li class="breadcrumb-item active" aria-current="page">Registered location</li>
							</ol>
						</div> -->
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
										<script>
											const latitude = <?php echo floatval($latitude); ?>;
											const longitude = <?php echo floatval($longitude); ?>;
											const locationName = "<?php echo addslashes($location_name); ?>";
										</script>
									</small>
								</div>
							</div>
						</div>
					</div>
					<div class="location-rooms-col">
						<div class="card">
							<div class="card-header d-flex align-items-center">
								<div class="input-group input-group-sm" style="width: 250px;">
									<span class="input-group-text bg-white border-end-0"><i class="fas fa-search"></i></span>
									<input type="text" class="form-control border-start-0" placeholder="Search rooms...">
								</div>
								<button type="button" class="btn btn-primary btn-sm ms-auto" data-bs-toggle="modal" data-bs-target="#addRoomModal">
									<i class="fas fa-plus-circle"></i> Add Room
								</button>
							</div>

							<div class="card-body">
								<div class="table-responsive">
									<table id="locationTable" class="table table-sm">
										<thead class="table-primary">
											<tr>
												<th><i class="bi bi-hash"></i> No.</th>
												<th><i class="bi bi-door-closed-fill"></i> Room Name</th>
												<th><i class="bi bi-people-fill"></i> Total Capacity</th>
												<th class="text-center" style="text-align: center; vertical-align: middle;">
													<i class="bi bi-gear-fill"></i> Actions
												</th>

											</tr>
										</thead>
										<tbody>
											<?php if (mysqli_num_rows($result) > 0): ?>
												<?php mysqli_data_seek($result, 0); ?>
												<?php $counter = 1; ?>
												<?php while ($room = mysqli_fetch_assoc($result)): ?>
													<tr>
														<td><?php echo $counter++; ?>.</td>
														<td><?php echo htmlspecialchars($room['room_name'] ?? 'No Room Assigned'); ?></td>
														<td>
															<?php
															$capacity = $room['idp_count'] ?? 0;
															$max_capacity = $room['room_capacity'] ?? 0; // Added null coalescing
															$percentage = ($max_capacity > 0) ? ($capacity / $max_capacity) * 100 : 0; // Division protection
															?>
															<div>
																<div class="d-flex justify-content-between">
																	<span><?php echo htmlspecialchars($capacity); ?></span>
																	<span class="text-muted">/<?php echo $max_capacity; ?></span>
																</div>
																<div class="progress" style="height: 10px;">
																	<div class="progress-bar text-white text-center" role="progressbar"
																		style="width: <?php echo $percentage; ?>%; font-size: 12px;"
																		aria-valuenow="<?php echo $percentage; ?>"
																		aria-valuemin="0" aria-valuemax="100">
																		<?php echo round($percentage); ?>%
																	</div>
																</div>
															</div>
														</td>
														<td>
															<div class="action-buttons">
																<?php if (!empty($room['room_id'])): ?>
																	<a href="#" class="btn btn-outline-success btn-sm edit-btn shadow"
																		data-id="<?php echo $room['room_id']; ?>"
																		data-name="<?php echo htmlspecialchars($room['room_name']); ?>"
																		data-capacity="<?php echo htmlspecialchars($room['room_capacity']); ?>"
																		data-bs-toggle="modal" data-bs-target="#editRoomModal">
																		<i class="fas fa-edit"></i> Edit
																	</a>
																	<a href="#" class="btn btn-outline-danger btn-sm delete-btn shadow"
																		data-id="<?php echo $room['room_id']; ?>"
																		data-bs-toggle="modal" data-bs-target="#deleteRoomModal">
																		<i class="fas fa-trash"></i> Delete
																	</a>
																	<a href="#" class="btn btn-outline-primary btn-sm shadow view-idp-btn"
																		data-id="<?php echo $room['room_id']; ?>"
																		data-location="<?php echo htmlspecialchars($room['location_name'] ?? 'Unknown Location'); ?>"
																		data-bs-toggle="modal" data-bs-target="#viewIDPModal">
																		<i class="fas fa-users"></i> View IDPs (<?php echo $room['idp_count'] ?? 0 ?>)
																	</a>
																<?php else: ?>
																	<span class="text-muted">No actions available</span>
																<?php endif; ?>
															</div>
														</td>
													</tr>
												<?php endwhile; ?>
											<?php else: ?>
												<tr>
													<td colspan="4" class="text-center py-3 text-muted">
														<i class="fas fa-door-closed me-2"></i> No rooms assigned to this location
													</td>
												</tr>
											<?php endif; ?>
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
		//include '../scripts/rooms.php'; 
		?>
	</div>

	<!-- Include Leaflet.js (for interactive maps) -->
	<script src="../scripts/evac_location_script/rooms.js?v=<?php echo time(); ?>"></script>
	<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
	<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

</body>

</html>