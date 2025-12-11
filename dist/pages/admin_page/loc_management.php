<?php
include '../../../database/session.php';
include '../layout/head_links.php';

$query = "SELECT elt.latitude , elt.longitude , elt.evac_loc_id , elt.city , elt.purok , bmt.barangay_name , elt.name , elt.total_capacity , elt.status FROM evac_loc_table as elt
LEFT JOIN barangay_manegement_table as bmt ON elt.barangay_id = bmt.barangay_id";
$result = $conn->query($query);

if (!$result) {
	error_log("Location management query failed: " . $conn->error);
	die("Query failed. Please contact administrator."); // Secure error message
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
							<div class="card-header d-flex align-items-center gap-2">
								<input type="text" id="searchBox" class="form-control me-2" placeholder="Search..." style="max-width: 300px;">
								<div class="btn-group">
									<button id="activateSelectedBtn" class="btn btn-success btn-sm">Activate selected</button>
									<button id="deactivateSelectedBtn" class="btn btn-secondary btn-sm">Deactivate selected</button>
								</div>
								<button type="button" class="btn btn-primary btn-sm ms-auto" data-bs-toggle="modal" data-bs-target="#addLocationModal">
									<i class="fas fa-plus-circle"></i> Add Location
								</button>
							</div>

							<div class="card-body">
								<div class="table-responsive">
									<table id="locationTable" class="table table-sm">
										<thead class="table-success">
											<tr class="justify-content-center text-center">
												<th style="width:32px"><input type="checkbox" id="selectAllCheckbox" /></th>
												<th> No.</th>
												<th><i class="bi bi-geo-alt-fill"></i> Location</th>
												<th><i class="bi bi-house-door-fill"></i> Address</th>
												<th><i class="bi bi-people-fill"></i> Available/Total Capacity</th>
												<th><i class="bi bi-person-fill"></i>Status</th>
												<th><i class="bi bi-gear-fill"></i> Actions</th>

											</tr>
										</thead>
										<tbody>
											<?php
											$counter = 1;
											if ($result->num_rows > 0) {
												while ($location = $result->fetch_assoc()):
													$address = $location['city'] . ', ' . $location['barangay_name'] . ' ,' . $location['purok'];
											?>
													<tr>
														<td class="text-center"><input type="checkbox" class="row-select" data-id="<?php echo $location['evac_loc_id']; ?>" /></td>
														<td><?php echo $counter++; ?>.</td>
														<td class="location-name">
															<?php echo htmlspecialchars($location['name']); ?>
														</td>
														<td class="location-address">
															<?php echo htmlspecialchars($location['city']); ?> ,Brgy. <?php echo htmlspecialchars($location['barangay_name']); ?> Prk. <?php echo htmlspecialchars($location['purok']); ?>
														</td>
														<td class="location-capacity">
															<?php
															// compute current evacuees at this location excluding infants (age <= 2)
															$locId = intval($location['evac_loc_id']);
															$nonInfantCount = 0;
															$countQuery = "SELECT COUNT(*) AS cnt FROM evac_reg_table ert LEFT JOIN pre_reg_table prt ON ert.pre_reg_id = prt.pre_reg_id WHERE ert.evac_loc_id = ? AND ert.status IN ('Evacuated','IN') AND (TIMESTAMPDIFF(YEAR, prt.date_of_birth, CURDATE()) > 2 OR prt.date_of_birth IS NULL)";
															if ($cntStmt = $conn->prepare($countQuery)) {
																$cntStmt->bind_param('i', $locId);
																$cntStmt->execute();
																$cntRes = $cntStmt->get_result();
																if ($cntRes && $cntRow = $cntRes->fetch_assoc()) {
																	$nonInfantCount = intval($cntRow['cnt']);
																}
																$cntStmt->close();
															}
															$available = max(0, intval($location['total_capacity']) - $nonInfantCount);
															echo htmlspecialchars($available);
															?>/<?php echo htmlspecialchars($location['total_capacity']); ?>
														</td>
														<td class="location-status">
															<span class="badge bg-<?php echo $location['status'] === 'Active' ? 'success' : 'secondary'; ?>" id="status-badge-<?php echo $location['evac_loc_id']; ?>">
																<?php echo htmlspecialchars($location['status']); ?>
															</span>
															<!-- <button class="btn btn-sm btn-outline-<?php echo $location['status'] === 'Active' ? 'secondary' : 'success'; ?> ms-2 status-toggle-btn"
																data-id="<?php echo $location['evac_loc_id']; ?>"
																data-status="<?php echo $location['status']; ?>">
																Set <?php echo $location['status'] === 'Active' ? 'Inactive' : 'Active'; ?>
															</button> -->
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
												echo "<tr><td colspan='7' class='text-center'>No location records found.</td></tr>";
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

	<!-- SweetAlert2 -->
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

	<!-- Search Script -->
	<script>
		document.addEventListener('DOMContentLoaded', function() {
			const searchBox = document.getElementById('searchBox');
			const locationTable = document.getElementById('locationTable');

			if (searchBox && locationTable) {
				searchBox.addEventListener('input', function() {
					const searchTerm = this.value.toLowerCase().trim();
					const rows = locationTable.querySelectorAll('tbody tr');

					rows.forEach(function(row) {
						const rowText = row.textContent.toLowerCase();
						if (rowText.includes(searchTerm)) {
							row.style.display = '';
						} else {
							row.style.display = 'none';
						}
					});
				});
			}
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
	<script>
		document.addEventListener('DOMContentLoaded', function() {
			document.querySelectorAll('.status-toggle-btn').forEach(function(btn) {
				btn.addEventListener('click', function(e) {
					e.preventDefault();
					const id = this.getAttribute('data-id');
					const currentStatus = this.getAttribute('data-status');
					const newStatus = currentStatus === 'Active' ? 'Inactive' : 'Active';
					const badge = document.getElementById('status-badge-' + id);
					const button = this;
					button.disabled = true;
					fetch('update_location_status.php', {
							method: 'POST',
							headers: {
								'Content-Type': 'application/x-www-form-urlencoded'
							},
							body: `evac_loc_id=${id}&status=${newStatus}`
						})
						.then(res => res.text())
						.then(data => {
							if (data.trim() === 'success') {
								badge.textContent = newStatus;
								badge.className = 'badge bg-' + (newStatus === 'Active' ? 'success' : 'secondary');
								button.textContent = 'Set ' + (newStatus === 'Active' ? 'Inactive' : 'Active');
								button.className = 'btn btn-sm btn-outline-' + (newStatus === 'Active' ? 'secondary' : 'success') + ' ms-2 status-toggle-btn';
								button.setAttribute('data-status', newStatus);
							} else {
								alert('Failed to update status.');
							}
							button.disabled = false;
						})
						.catch(() => {
							alert('Failed to update status.');
							button.disabled = false;
						});
				});
			});
		});
	</script>

	<script>
		document.addEventListener('DOMContentLoaded', function() {
			const selectAll = document.getElementById('selectAllCheckbox');
			const rowCheckboxes = document.querySelectorAll('.row-select');
			const activateBtn = document.getElementById('activateSelectedBtn');
			const deactivateBtn = document.getElementById('deactivateSelectedBtn');

			function getSelectedIds() {
				const ids = [];
				const checkboxes = document.querySelectorAll('.row-select:checked');
				console.log('Found checked checkboxes:', checkboxes.length);
				checkboxes.forEach(cb => {
					const id = cb.getAttribute('data-id');
					console.log('Checkbox ID:', id);
					ids.push(id);
				});
				console.log('Selected IDs:', ids);
				return ids;
			}

			if (selectAll) {
				selectAll.addEventListener('change', function() {
					const checked = !!this.checked;
					// Only select checkboxes in visible rows
					document.querySelectorAll('.row-select').forEach(cb => {
						const row = cb.closest('tr');
						if (row && row.style.display !== 'none') {
							cb.checked = checked;
						}
					});
				});
			}

			async function bulkUpdate(status) {
				const ids = getSelectedIds();
				if (!ids.length) {
					Swal.fire({
						icon: "warning",
						title: "No Selection",
						text: "Please select at least one location.",
						confirmButtonColor: "#3085d6"
					});
					return;
				}

				const result = await Swal.fire({
					title: "Are you sure?",
					text: `Set ${ids.length} location(s) to ${status}?`,
					icon: "question",
					showCancelButton: true,
					confirmButtonText: "Yes, update",
					cancelButtonText: "Cancel",
					confirmButtonColor: "#28a745",
					cancelButtonColor: "#d33"
				});

				if (!result.isConfirmed) return;

				const btn = status === 'Active' ? activateBtn : deactivateBtn;
				btn.disabled = true;

				// Show loading modal
				Swal.fire({
					title: "Updating...",
					text: `Please wait while we update ${ids.length} location(s).`,
					allowOutsideClick: false,
					didOpen: () => {
						Swal.showLoading();
					}
				});

				try {
					const res = await fetch('update_location_status_bulk.php', {
						method: 'POST',
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded'
						},
						body: 'ids=' + encodeURIComponent(ids.join(',')) + '&status=' + encodeURIComponent(status)
					});
					const text = await res.text();
					if (text.trim() === 'success') {
						// update UI badges and buttons
						ids.forEach(id => {
							const badge = document.getElementById('status-badge-' + id);
							const btn = document.querySelector('.status-toggle-btn[data-id="' + id + '"]');
							if (badge) {
								badge.textContent = status;
								badge.className = 'badge bg-' + (status === 'Active' ? 'success' : 'secondary');
							}
							if (btn) {
								btn.textContent = 'Set ' + (status === 'Active' ? 'Inactive' : 'Active');
								btn.className = 'btn btn-sm btn-outline-' + (status === 'Active' ? 'secondary' : 'success') + ' ms-2 status-toggle-btn';
								btn.setAttribute('data-status', status);
							}
						});
						Swal.fire({
							icon: "success",
							title: "Updated!",
							text: "The locations have been updated successfully.",
							timer: 2000,
							showConfirmButton: false
						});
					} else {
						Swal.fire({
							icon: "error",
							title: "Update Failed",
							text: text
						});
					}
				} catch (e) {
					console.error(e);
					Swal.fire({
						icon: "error",
						title: "Error",
						text: "Bulk update failed due to a network/server issue."
					});
				} finally {
					btn.disabled = false;
				}
			}
			if (activateBtn) {
				console.log('Activate button found, adding event listener');
				activateBtn.addEventListener('click', function() {
					console.log('Activate button clicked');
					bulkUpdate('Active');
				});
			} else {
				console.log('Activate button NOT found');
			}

			if (deactivateBtn) {
				console.log('Deactivate button found, adding event listener');
				deactivateBtn.addEventListener('click', function() {
					console.log('Deactivate button clicked');
					bulkUpdate('Inactive');
				});
			} else {
				console.log('Deactivate button NOT found');
			}
		});
	</script>
	<script src="../scripts/scripts.js"></script>
</body>

</html>