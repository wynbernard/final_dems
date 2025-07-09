<?php
include '../../../database/session.php';
include '../layout/head_links.php';

$query = "SELECT evac_loc_id, name, address, total_capacity FROM evac_loc_table";
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
						<div class="col-sm-6">
							<h3 class="mb-0">Locations</h3>
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
										<thead class="table-primary">
											<tr>
												<th> No.</th>
												<th><i class="fas fa-map-marker-alt" style="color: #007BFF; margin-right: 6px;"></i> Location</th>
												<th><i class="fas fa-home" style="color: #007BFF; margin-right: 6px;"></i> Address</th>
												<th><i class="fas fa-users" style="color: #007BFF; margin-right: 6px;"></i> Total Capacity</th>
												<th><i class="fas fa-cogs" style="color: #007BFF; margin-right: 6px;"></i> Actions</th>
											</tr>
										</thead>
										<tbody>
											<?php
											$counter = 1;
											if (mysqli_num_rows($result) > 0) {
												while ($location = mysqli_fetch_assoc($result)): ?>
													<tr>
														<td class="cell-number"><?php echo $counter++; ?>.</td>
														<td class="cell-location"><?php echo htmlspecialchars($location['name']); ?></td>
														<td class="cell-address"><?php echo htmlspecialchars($location['address']); ?></td>
														<td class="cell-capacity"><?php echo htmlspecialchars($location['total_capacity']); ?></td>

														<td>
															<a href="#" class="btn btn-success btn-sm edit-btn"
																data-id="<?php echo $location['evac_loc_id']; ?>"
																data-name="<?php echo htmlspecialchars($location['name']); ?>"
																data-address="<?php echo htmlspecialchars($location['address']); ?>"
																data-capacity="<?php echo htmlspecialchars($location['total_capacity']); ?>"
																data-bs-toggle="modal" data-bs-target="#editLocationModal">
																<i class="fas fa-edit"></i> Edit
															</a>

															<a href="#" class="btn btn-danger btn-sm delete-btn"
																data-id="<?php echo $location['evac_loc_id']; ?>"
																data-bs-toggle="modal" data-bs-target="#deleteLocationModal">
																<i class="fas fa-trash"></i> Delete
															</a>

															<a href="room_reservation.php?evac_loc_id=<?php echo $location['evac_loc_id']; ?>" class="btn btn-primary btn-sm">
																<i class="fas fa-eye"></i> View Rooms
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
		include '../modal/modal_location.php';
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
		/* General Table Styling */
		td {
			padding: 12px 15px;
			font-size: 16px;
			color: #333;
			vertical-align: middle;
			border-bottom: 1px solid #eaeaea;
			text-align: left;
		}

		/* Specific Column Styling */
		.cell-number {
			text-align: center;
			color: #888;
			font-weight: bold;
		}

		.cell-location {
			font-weight: 600;
			color: #212529;
		}

		.cell-address {
			color: #555;
			font-style: italic;
		}

		.cell-capacity {
			color: #007BFF;
			/* Blue for capacity */
			font-weight: 600;
			text-align: center;
		}

		/* Optional Hover Effect */
		tbody tr:hover {
			background-color: #f6f9fc;
			transition: background-color 0.3s ease;
		}

		/* Optional Styling for Date and Time (if applicable) */
		.cell-date {
			font-style: italic;
			color: #0069d9;
		}
	</style>

</body>

</html>