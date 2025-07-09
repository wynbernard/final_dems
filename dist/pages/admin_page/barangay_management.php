<?php
include '../../../database/session.php';
include '../layout/head_links.php';

$query = "SELECT * FROM barangay_manegement_table";
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
							<i class="fas fa-city fs-2 text-primary"></i>
							<h3 class="mb-0">Barangay Management</h3>
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
									<table id="locationTable" class="searchable-table table table-sm table-hover w-100">
										<thead class="table-success sticky-header">
											<tr>
												<th> No.</th>
												<th><i class="bi bi-geo-alt-fill"></i> Location</th>
												<th><i class="bi bi-person-badge-fill"></i> Barangay Captain</th>
												<th class="text-center" style="text-align: center; vertical-align: middle;">
													<i class="bi bi-gear-fill"></i> Actions
												</th>

											</tr>
										</thead>
										<tbody>
											<?php
											$counter = 1;
											if (mysqli_num_rows($result) > 0) {
												while ($barangay = mysqli_fetch_assoc($result)): ?>
													<tr>
														<td class="cell-number"><?php echo $counter++; ?>.</td>
														<td class="cell-location "><?php echo htmlspecialchars($barangay['barangay_name']); ?></td>
														<td class="cell-address justify-content-center text-centerz"><?php echo htmlspecialchars($barangay['barangay_captain_name']); ?></td>
														<!-- <td class="cell-signature">
															<?php if (!empty($barangay['signature_brgy_captain'])): ?>
																<img src="../<?php echo htmlspecialchars($barangay['signature_brgy_captain']); ?>" alt="Captain Signature" style="height: 50px; width: auto;">
															<?php else: ?>
																<span style="height: 50px; width: auto;">No signature</span>
															<?php endif; ?>
														</td> -->
														<td class="justify-content-center text-center">
															<a href="#" class="btn btn-outline-success btn-sm edit-btn shadow"
																data-id="<?php echo $barangay['barangay_id']; ?>"
																data-name="<?php echo htmlspecialchars($barangay['barangay_name']); ?>"
																data-captain="<?php echo htmlspecialchars($barangay['barangay_captain_name']); ?>"
																data-signature="<?php echo htmlspecialchars($barangay['signature_brgy_captain']); ?>"
																data-latitude="<?php echo htmlspecialchars($barangay['latitude']); ?>"
																data-longitude="<?php echo htmlspecialchars($barangay['longitude']); ?>"
																data-bs-toggle="modal" data-bs-target="#editLocationModal">
																<i class="fas fa-edit"></i> Edit
															</a>

															<a href="#" class="btn btn-outline-danger btn-sm delete-btn shadow"
																data-id="<?php echo $barangay['barangay_id']; ?>"
																data-bs-toggle="modal" data-bs-target="#deleteLocationModal">
																<i class="fas fa-trash"></i> Delete
															</a>

															<a href="#"
																class="btn btn-outline-primary btn-sm shadow view-barangay-btn"
																data-bs-toggle="modal"
																data-bs-target="#viewBarangayModal"
																data-name1="<?php echo htmlspecialchars($barangay['barangay_name']); ?>"
																data-captain="<?php echo htmlspecialchars($barangay['barangay_captain_name']); ?>"
																data-signature="<?php echo htmlspecialchars($barangay['signature_brgy_captain']); ?>"
																data-latitude="<?php echo htmlspecialchars($barangay['latitude']); ?>"
																data-longitude="<?php echo htmlspecialchars($barangay['longitude']); ?>">
																<i class="fas fa-eye"></i> View
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
		include '../modal/evac_location/barangay_management_modal.php';
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
		.table-responsive {
			max-height: 400px;
			overflow-y: auto;
		}

		#locationTable thead th {
			position: sticky;
			top: 0;
			z-index: 10;
			background: #d1e7dd;
			/* Use your table-success bg color */
		}
	</style>

</body>

</html>