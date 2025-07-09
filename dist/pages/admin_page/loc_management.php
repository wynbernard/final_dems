<?php
include '../../../database/session.php';
include '../layout/head_links.php';

$query = "SELECT elt.latitude , elt.longitude , elt.evac_loc_id , elt.city , elt.purok , bmt.barangay_name , elt.name , elt.total_capacity FROM evac_loc_table as elt
LEFT JOIN barangay_manegement_table as bmt ON elt.barangay_id = bmt.barangay_id";
$result = mysqli_query($conn, $query);

if (!$result) {
	die("Query failed: " . mysqli_error($conn)); // Debugging for SQL errors	
}
?>
<!DOCTYPE html>
<html lang="en">

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
						<div class="col-sm-6 d-flex align-items-center gap-2">
							<i class="bi bi-map fs-2 text-primary"></i>
							<h3 class="mb-0">Evacuation Locations</h3>
						</div>
						<div class="col-sm-6">
							<ol class="breadcrumb float-sm-end">
								<li class="breadcrumb-item"><a href="#">Home</a></li>
								<li class="breadcrumb-item active" aria-current="page">Location Records</li>
							</ol>
						</div>
					</div>
				</div>
			</div>

			<!-- Search Box -->
			<div class="container mt-0"></div>

			<div class="content">
				<div class="row">
					<div class="col-md-12">
						<div class="card">
							<div class="card-header d-flex align-items-center">
								<input type="text" id="searchBox" class="form-control me-2" placeholder="Search..." style="max-width: 300px;">
								<button type="button" class="btn btn-primary btn-sm ms-auto" data-bs-toggle="modal" data-bs-target="#addLocationModal">
									<i class="fas fa-plus-circle"></i> Add Location
								</button>
							</div>

							<div class="card-body">
								<div class="table-responsive">
									<table id="locationTable" class="table table-sm">
										<thead class="table-success">
											<tr class="justify-content-center text-center">
												<th> No.</th>
												<th><i class="bi bi-geo-alt-fill"></i> Location</th>
												<th><i class="bi bi-house-door-fill"></i> Address</th>
												<th><i class="bi bi-people-fill"></i> Total Capacity</th>
												<th><i class="bi bi-gear-fill"></i> Actions</th>

											</tr>
										</thead>
										<tbody>
											<?php
											$counter = 1;
											if (mysqli_num_rows($result) > 0) {
												while ($location = mysqli_fetch_assoc($result)):
													$address = $location['city'] . ', ' . $location['barangay_name'] . ' ,' . $location['purok'];
											?>
													<tr>
														<td><?php echo $counter++; ?>.</td>
														<td class="location-name">
															<?php echo htmlspecialchars($location['name']); ?>
														</td>
														<td class="location-address">
															<?php echo htmlspecialchars($location['city']); ?> ,Brgy. <?php echo htmlspecialchars($location['barangay_name']); ?> Prk. <?php echo htmlspecialchars($location['purok']); ?>
														</td>
														<td class="location-capacity">
															<?php echo htmlspecialchars($location['total_capacity']); ?>
														</td>

														<td>
															<a href="#" class="btn btn-sm btn-outline-success edit-btn shadow"
																data-id="<?php echo $location['evac_loc_id']; ?>"
																data-name="<?php echo htmlspecialchars($location['name']); ?>"
																data-city="<?php echo htmlspecialchars($location['city']); ?>"
																data-barangay="<?php echo htmlspecialchars($location['barangay_name']); ?>"
																data-purok="<?php echo htmlspecialchars($location['purok']); ?>"
																data-longitude="<?php echo htmlspecialchars($location['longitude']); ?>"
																data-latitude="<?php echo htmlspecialchars($location['latitude']); ?>"
																data-capacity="<?php echo htmlspecialchars($location['total_capacity']); ?>"
																data-bs-toggle="modal" data-bs-target="#editLocationModal">
																<i class="fas fa-edit me-1"></i> Edit
															</a>

															<a href="#" class="btn btn-sm btn-outline-danger delete-btn shadow"
																data-id="<?php echo $location['evac_loc_id']; ?>"
																data-bs-toggle="modal" data-bs-target="#deleteLocationModal">
																<i class="fas fa-trash me-1"></i> Delete
															</a>

															<a href="rooms.php?evac_loc_id=<?php echo $location['evac_loc_id']; ?>" class="btn btn-sm btn-outline-primary view-details-btn shadow">
																<i class="fas fa-eye me-1"></i> View Rooms
															</a>
														</td>

													</tr>
											<?php endwhile;
											} else {
												echo "<tr><td colspan='5' class='text-center'>No location records found.</td></tr>";
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

		<?php include '../layout/footer.php';
		include '../modal/evac_location/modal_location.php';
		?>
	</div>

	<!-- Search Script -->
	<script>
		$(document).ready(function() {
			$("#searchBox").on("keyup", function() {
				var searchTerm = $(this).val().toLowerCase().trim();

				$("#locationTable tbody tr").each(function() {
					var rowText = $(this).text().toLowerCase();

					if (rowText.includes(searchTerm)) {
						$(this).fadeIn();
					} else {
						$(this).fadeOut();
					}
				});
			});
		});
	</script>
	<style>
		td {
			padding: 12px 16px;
			vertical-align: middle;
			font-size: 15px;
			color: #333;
			border-bottom: 1px solid #e0e0e0;
		}

		.location-name {
			font-weight: 600;
			color: #007BFF;
		}

		.location-address {
			font-style: italic;
			color: #555;
		}

		.location-capacity {
			text-align: center;
			font-weight: bold;
			color: #28a745;
			/* green tone for capacity */
		}
	</style>
	<script src="../scripts/scripts.js"></script>
</body>

</html>