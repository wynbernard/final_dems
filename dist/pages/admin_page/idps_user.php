<?php
include '../../../database/session.php'; // Include your database connection file
include '../layout/head_links.php'; // Include any necessary CSS or JS files


if ($_SESSION['role'] == 'admin') {
	// SQL query to fetch all data from evac_reg_table
	$query = "SELECT * FROM evac_reg_table
LEFT JOIN evac_loc_table ON evac_reg_table.evac_loc_id = evac_loc_table.evac_loc_id
LEFT JOIN disaster_table ON evac_reg_table.disaster_id = disaster_table.disaster_id
LEFT JOIN room_table ON evac_reg_table.room_id = room_table.room_id
LEFT JOIN pre_reg_table ON evac_reg_table.pre_reg_id = pre_reg_table.pre_reg_id
LEFT JOIN age_class_table ON pre_reg_table.age_class_id = age_class_table.age_class_id
";

	// Execute the query
	$result = mysqli_query($conn, $query);

	if (!$result) {
		die("Query failed: " . mysqli_error($conn));
	}

	// Check if there are any rows in the result
	$hasRecords = mysqli_num_rows($result) > 0;

	// Fetch column names dynamically
	$columns = [];
	if ($hasRecords) {
		$columns = array_keys(mysqli_fetch_assoc($result)); // Get column names
		mysqli_data_seek($result, 0); // Reset the pointer to the beginning of the result set
	}
} else {
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<title>Evacuation Registration Data</title>
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
	<div class="app-wrapper">
		<?php include '../layout/header.php';
		include '../layout/sidebar.php';
		include '../alert/warning.php'; ?>

		<main class="app-main">
			<!-- Page Header -->
			<div class="app-content-header ">
				<div class="row">
					<div class="col-sm-6 d-flex align-items-center gap-2">
						<i class="fas fa-people-roof fs-2 text-primary"></i>
						<h3 class="mb-0">Registration</h3>
					</div>
					<div class="col-md-6 text-md-end">
						<ol class="breadcrumb justify-content-md-end">
							<li class="breadcrumb-item"><a href="#">Home</a></li>
							<li class="breadcrumb-item active" aria-current="page">Evacuation Registration Data</li>
						</ol>
					</div>
				</div>
			</div>

			<!-- Selection and Results Card -->
			<div class="content">
				<div class="row">
					<div class="col-md-12">
						<div class="card shadow-sm">
							<div class="card-header d-flex align-items-center flex-wrap">
								<!-- Search and Location Filter -->
								<form method="GET" id="locationForm" class="d-flex flex-wrap align-items-center w-100 mb-2 ms-auto">
									<!-- Location Dropdown -->
									<select name="location_id" id="locationSelect" class="form-select me-2 mb-2" style="max-width: 250px;" required>
										<?php
										// For staff users - show ONLY their assigned location
										if ($_SESSION['role'] == 'Staff') {
											$staff_location_id = $_SESSION['evac_loc_id'];
											$query = mysqli_query($conn, "SELECT evac_loc_table.evac_loc_id, evac_loc_table.name 
											FROM admin_table
											JOIN evac_loc_table ON admin_table.evac_loc_id = evac_loc_table.evac_loc_id
											WHERE admin_table.evac_loc_id = '$staff_location_id'");

											if ($query && mysqli_num_rows($query) > 0) {
												$loc = mysqli_fetch_assoc($query);
												echo '<option value="' . htmlspecialchars($loc['evac_loc_id']) . '" selected>' . htmlspecialchars($loc['name']) . '</option>';
											} else {
												echo '<option value="" selected>Location Not Found</option>';
											}
										}
										// For admin users - show all locations
										else {
											echo '<option value="">All Locations</option>';
											$query = mysqli_query($conn, "SELECT evac_loc_id, name FROM evac_loc_table ORDER BY name ASC");
											if ($query) {
												while ($loc = mysqli_fetch_assoc($query)) {
													$selected = (isset($_GET['location_id']) && $_GET['location_id'] == $loc['evac_loc_id']) ? 'selected' : '';
													echo '<option value="' . htmlspecialchars($loc['evac_loc_id']) . '" ' . $selected . '>' . htmlspecialchars($loc['name']) . '</option>';
												}
											}
										}
										?>
									</select>

									<!-- Register Button -->
									<button type="button" class="btn btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#registerChoiceModal">
										<i class="fas fa-user-plus me-1"></i> Register IDP
									</button>

									<?php
									$locationId = $_GET['location_id'] ?? '';

									// Age classification query (filter only if location selected)
									$ageQuery = "
												SELECT 
													SUM(CASE WHEN a.classification = 'Child' THEN 1 ELSE 0 END) AS Child,
													SUM(CASE WHEN a.classification = 'Teen' THEN 1 ELSE 0 END) AS Teen,
													SUM(CASE WHEN a.classification = 'Adult' THEN 1 ELSE 0 END) AS Adult,
													SUM(CASE WHEN a.classification = 'Senior' THEN 1 ELSE 0 END) AS Senior,
													COUNT(*) AS total
												FROM evac_reg_table er
												INNER JOIN pre_reg_table pr ON er.pre_reg_id = pr.pre_reg_id
												LEFT JOIN age_class_table a ON pr.age_class_id = a.age_class_id
												INNER JOIN room_table r ON er.room_id = r.room_id
											";

									if (!empty($locationId)) {
										$ageQuery .= " WHERE r.evac_loc_id = '$locationId'";
									}

									$ageResult = mysqli_query($conn, $ageQuery);
									$ageData = mysqli_fetch_assoc($ageResult);
									?>

									<!-- Age Classification Counts -->
									<div class="age-classification ms-2 mb-2 d-flex align-items-center">
										<div class="badge bg-info me-1" title="Children (0-12)">
											<i class="fas fa-child me-1"></i>Children: <?= $ageData['Child'] ?? 0 ?>
										</div>
										<div class="badge bg-primary me-1" title="Teens (13-17)">
											<i class="fas fa-user me-1"></i>Teens: <?= $ageData['Teen'] ?? 0 ?>
										</div>
										<div class="badge bg-success me-1" title="Adults (18-59)">
											<i class="fas fa-user me-1"></i>Adults: <?= $ageData['Adult'] ?? 0 ?>
										</div>
										<div class="badge bg-warning me-1" title="Seniors (60+)">
											<i class="fas fa-user-tie me-1"></i>Senior: <?= $ageData['Senior'] ?? 0 ?>
										</div>
										<div class="badge bg-dark" title="Total">
											<i class="fas fa-users me-1"></i>Total: <?= $ageData['total'] ?? 0 ?>
										</div>
									</div>

									<!-- Search Box -->
									<input type="text" id="searchBox" class="form-control me-2 ms-auto" placeholder="Search IDPs..." style="max-width: 240px;">
								</form>

							</div>

							<?php
							// Get the selected location ID
							$locationId = $_GET['location_id'] ?? '';

							// Query to fetch IDPs (with location filter if specified)
							$query = "
									SELECT 
										l.evac_loc_id AS location_id,
										l.name AS location_name,
										r.room_name,
										er.evac_reg_id,
										er.date_reg,
										pr.f_name,
										pr.l_name
									FROM evac_loc_table l
									INNER JOIN room_table r ON l.evac_loc_id = r.evac_loc_id
									INNER JOIN evac_reg_table er ON r.room_id = er.room_id
									INNER JOIN pre_reg_table pr ON er.pre_reg_id = pr.pre_reg_id
								";


							// Initialize base query
							$query = "SELECT r.evac_reg_id, p.f_name, p.l_name, p.m_name, l.name AS location_name, rm.room_name , r.date_reg 
									FROM evac_reg_table r
									LEFT JOIN pre_reg_table p ON r.pre_reg_id = p.pre_reg_id
									JOIN evac_loc_table l ON r.evac_loc_id = l.evac_loc_id
									LEFT JOIN room_table rm ON r.room_id = rm.room_id";

							// For staff users - always filter by their assigned location
							if ($_SESSION['role'] == 'Staff') {
								$staff_location_id = $_SESSION['evac_loc_id'];
								$query .= " WHERE r.evac_loc_id = '$staff_location_id'";
							}
							// For admin users - filter by selected location if specified
							elseif (!empty($locationId)) {
								$query .= " WHERE r.evac_loc_id = '$locationId'";
							}

							$query .= " ORDER BY l.name, rm.room_name, p.l_name";
							$result = mysqli_query($conn, $query);

							if (!$result) {
								die('Query failed: ' . mysqli_error($conn));
							}
							?>

							<div class="card-body">
								<div class="table-responsive">
									<!-- Scrollable Table Wrapper -->
									<div style="max-height: 400px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: .375rem;">
										<table class="table table-sm table-hover mb-0" id="usertable" style="min-width: 600px;">
											<thead class="table-success text-nowrap sticky-top" style="top: 0; z-index: 1;">
												<tr>
													<th>No.</th>
													<th><i class="bi bi-person-fill"></i> Full Name</th>
													<th><i class="bi bi-geo-alt-fill"></i> Location</th>
													<th><i class="bi bi-door-closed-fill"></i> Assigned Room</th>
													<th><i class="bi bi-calendar-event-fill"></i> Date</th>
													<th><i class="bi bi-gear-fill"></i> Action</th>

												</tr>
											</thead>
											<tbody>
												<?php if (mysqli_num_rows($result) > 0):
													$i = 1;
													while ($row = mysqli_fetch_assoc($result)): ?>
														<tr>
															<td><?= $i++ ?>.</td>
															<td><?= htmlspecialchars($row['f_name'] . " " . $row['m_name'] . " " . $row['l_name']) ?></td>
															<td><?= htmlspecialchars($row['location_name']) ?></td>
															<td><?= htmlspecialchars($row['room_name']) ?></td>
															<td><?= htmlspecialchars($row['date_reg']) ?></td>
															<td>
																<button
																	class="btn btn-sm btn-info view-idp-btn"
																	data-bs-toggle="modal"
																	data-bs-target="#idpDetailsModal"
																	data-id="<?= $row['evac_reg_id'] ?>">
																	<i class="fas fa-eye me-1"></i> View Details
																</button>
															</td>
														</tr>
													<?php endwhile;
												else: ?>
													<tr>
														<td colspan="5" class="text-center">
															<?= empty($locationId) ? 'No IDPs found in the system.' : 'No IDPs found for this location.' ?>
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
			</div>

			<?php include '../modal/details_idps.php'; ?>
		</main>
		<?php include '../modal/registered_idps.php';
		include '../layout/footer.php'; ?>
	</div>
	<script src="../scripts/scripts.js"></script>
	<script src="../scripts/admin_script/idps_user.js"></script>
</body>

</html>