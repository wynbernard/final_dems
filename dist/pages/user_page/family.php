<!doctype html>
<html lang="en">

<head>
	<?php include '../../../database/user_session.php'; ?>
	<?php include '../layout_user/head_links.php'; ?>
	<!-- Leaflet CSS -->
	<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css"
		integrity="sha256-sA+e2qXtK6ks6ChhyzMhzsHle0Gx6x7vJnxTJYBdB6s="
		crossorigin="" />
	<!-- Leaflet CSS -->
	<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" crossorigin="" />

	<!-- Leaflet JS -->
	<script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js" crossorigin=""></script>

	<?php include '../css/family.php'; ?>
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
	<div class="app-wrapper">
		<?php include '../layout_user/header.php'; ?>
		<?php include '../layout_user/sidebar.php'; ?>
		<?php include '../alert/warning.php';
		$user1 = $_SESSION['pre_reg_id']; // Assuming user ID is stored in session
		?>

		<main class="app-main">
			<div class="content container-fluid">
				<div class="row g-4">
					<!-- Left Column: Location Card -->
					<div class="col-lg-4 col-md-5 mt-5">
						<div class="card rounded-3 overflow-hidden profile-card sticky-top" style="top: 20px;">
							<div class="card-header">
								<h5 class="card-title text-white mb-0"><i class="fas fa-map-marker-alt me-2"></i> Family Location</h5>
							</div>
							<div class="card-body">
								<div id="locationMap" style="height: 250px;"></div>
								<div id="coordinatesDisplay" class="mt-2 text-center text-muted"></div>
								<div class="mt-3">
									<div class="d-flex justify-content-between align-items-center">
										<h6 class="fw-bold mb-2"><i class="fas fa-home me-2"></i>Address</h6>
										<!-- Edit Button -->
										<a href="#" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editAddressModal">
											<i class="fas fa-edit me-1"></i>Edit
										</a>
									</div>
									<p class="text-muted mb-0" id="addressText">
										<?php
										$familyQuery = "SELECT
											pre_reg_table.family_id,
											pre_reg_table.f_name,
											pre_reg_table.l_name,
											family_table.*,
											barangay_manegement_table.barangay_name
											FROM pre_reg_table 
											LEFT JOIN family_table ON pre_reg_table.family_id = family_table.family_id
											LEFT JOIN barangay_manegement_table ON family_table.barangay_id = barangay_manegement_table.barangay_id
											WHERE pre_reg_id = ?";
										if ($familyStmt = $conn->prepare($familyQuery)) {
											$familyStmt->bind_param("i", $user1);
											$familyStmt->execute();
											$result = $familyStmt->get_result();
											$row = $result->fetch_assoc();
											$family_id = $row['family_id'];
											$address = $row['region'] . $row['province'] . $row['city_municipality'] . " , Bgry." . $row['barangay_name'] . $row['street'];
											$OSM_address = $row['street'] . ", " . $row['barangay_name'] . ", " . $row['city_municipality'] . ", " . $row['province'] . ", " . "Philippines";
											$head_name = $row['f_name'] . ' ' . $row['l_name'];
											$familyStmt->close();
											$fullAddress = $address;
											echo htmlspecialchars($address) . '<br>';
											echo '<small class="text-primary"><i class="fas fa-crown me-1"></i>Head of The Family: ' . htmlspecialchars($head_name) . '</small>';
										} else {
											echo "Address not available";
										}
										?>
									</p>
								</div>
							</div>
						</div>
					</div>

					<!-- Right Column: Family Members Section -->
					<div class="col-lg-8 col-md-7 pt-3">
						<div class="card rounded-3 overflow-hidden">
							<div class="card-header d-flex justify-content-between align-items-center">
								<h5 class="card-title text-white mb-0"><i class="fas fa-users me-2"></i> Family Members</h5>
								<button class="btn btn-primary add-member-btn btn-sm ms-auto" data-bs-toggle="modal" data-bs-target="#addFamilyMemberModal">
									<i class="fas fa-plus me-1"></i> Add Member
								</button>
							</div>
							<div class="card-body">
								<?php
								// Fetch all family members with the same family_id (excluding current user)
								$query = "SELECT * FROM pre_reg_table 
											LEFT JOIN age_class_table ON pre_reg_table.age_class_id = age_class_table.age_class_id
											WHERE family_id = ? AND pre_reg_id != ?"; // Added condition to exclude current user
								$familyStmt = $conn->prepare($query);
								$familyStmt->bind_param("ii", $family_id, $user1); // Assuming $user1 contains current user's ID
								$familyStmt->execute();
								$familyResult = $familyStmt->get_result();
								?>

								<?php if ($familyResult->num_rows > 0) : ?>
									<div class="table-responsive">
										<div style="height: 400px; overflow-y: auto;">
											<table class="table table-hover align-middle" style="position: relative;">
												<thead class="table-light" style="position: sticky; top: 0; z-index: 1; background-color: white;">
													<tr>
														<th style="width: 60px">Photo</th>
														<th>Name</th>
														<th>Age</th>
														<th>Gender</th>
														<th style="width: 120px">Actions</th>
													</tr>
												</thead>
												<tbody>
													<?php while ($member = $familyResult->fetch_assoc()) :
														$dob = new DateTime($member['date_of_birth']);
														$today = new DateTime();
														$age = $dob->diff($today)->y;
													?>
														<tr>
															<td>
																<img src="../../../dist/assets/img/user2-160x160.jpg" class="rounded-circle member-avatar" alt="Member Avatar">
															</td>
															<td>
																<strong><?= htmlspecialchars($member['f_name'] . ' ' . $member['l_name']) ?></strong>
																<br>
																<small class="text-muted"><?= htmlspecialchars($member['contact_no']) ?></small>
															</td>
															<td>
																<span class="badge bg-info"><?= $age ?> years</span>
															</td>
															<td>
																<?php
																$genderIcon = ($member['gender'] == 'Male') ? 'mars' : 'venus';
																$genderColor = ($member['gender'] == 'Male') ? 'text-primary' : 'text-pink';
																?>
																<i class="fas fa-<?= $genderIcon ?> <?= $genderColor ?> me-1"></i>
																<?= htmlspecialchars($member['gender']) ?>
															</td>
															<td class="text-nowrap">
																<button class="btn btn-sm btn-outline-info view-family-btn"
																	data-id="<?= $member['pre_reg_id'] ?>"
																	data-name="<?= htmlspecialchars($member['f_name'] . ' ' . $member['l_name']) ?>"
																	data-gender="<?= htmlspecialchars($member['gender']) ?>"
																	data-contact_no="<?= htmlspecialchars($member['contact_no']) ?>"
																	data-dob="<?= htmlspecialchars($member['date_of_birth']) ?>"
																	data-relation="<?= $member['relation_to_family'] ?>">
																	<i class="fas fa-eye me-1"></i> View
																</button>
																<button class="btn btn-sm btn-outline-danger"
																	data-bs-toggle="modal"
																	data-bs-target="#deleteFamilyMemberModal<?= $member['pre_reg_id'] ?>">
																	<i class="fas fa-trash"></i>
																</button>
															</td>
														</tr>
													<?php endwhile; ?>
												</tbody>
											</table>
										</div>
									</div>
								<?php else : ?>
									<div class="empty-state">
										<i class="fas fa-users-slash"></i>
										<h5 class="mt-3">No Other Family Members Added</h5> <!-- Updated message -->
										<p class="text-muted">Start by adding family members to manage them here.</p>
										<button class="btn btn-primary add-member-btn" data-bs-toggle="modal" data-bs-target="#addFamilyMemberModal">
											<i class="fas fa-plus me-1"></i> Add Family Member
										</button>
									</div>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</main>

		<?php
		include '../modal_user/family_details.php';
		include '../layout_user/footer.php';
		?>
	</div>

	<script>
		document.addEventListener("DOMContentLoaded", function() {
			const locationName = <?= json_encode($OSM_address ?? '') ?>;
			const mapElement = document.getElementById("locationMap");
			const coordinatesDisplay = document.getElementById("coordinatesDisplay");

			if (!locationName || locationName.trim() === "") {
				mapElement.innerHTML = `
				<div class="location-placeholder">
					<div class="text-center">
						<i class="fas fa-map-marked-alt"></i>
						<p class="mt-2 mb-0">No address provided</p>
					</div>
				</div>
			`;
				return;
			}

			mapElement.innerHTML = `
			<div class="location-placeholder">
				<div class="text-center">
					<div class="spinner-border text-primary" role="status">
						<span class="visually-hidden">Loading...</span>
					</div>
					<p class="mt-2 mb-0">Loading map...</p>
				</div>
			</div>
		`;

			fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(locationName)}`)
				.then(response => {
					if (!response.ok) throw new Error("Network response was not ok");
					return response.json();
				})
				.then(data => {
					if (data.length === 0) {
						throw new Error("Location not found");
					}

					const lat = parseFloat(data[0].lat);
					const lon = parseFloat(data[0].lon);

					// Display coordinates
					coordinatesDisplay.innerHTML = `<strong>Coordinates:</strong> ${lat.toFixed(6)}, ${lon.toFixed(6)}`;

					// Initialize map
					mapElement.innerHTML = ""; // Clear loader
					const map = L.map('locationMap').setView([lat, lon], 15);

					L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
						attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
					}).addTo(map);

					const customIcon = L.icon({
						iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
						iconSize: [25, 41],
						iconAnchor: [12, 41],
						popupAnchor: [1, -34]
					});

					L.marker([lat, lon], {
							icon: customIcon
						}).addTo(map)
						.bindPopup(`<strong>Family Location</strong><br>${locationName}`)
						.openPopup();
				})
				.catch(error => {
					console.error("Error:", error);
					mapElement.innerHTML = `
					<div class="location-placeholder">
						<div class="text-center">
							<i class="fas fa-map-marked-alt"></i>
							<p class="mt-2 mb-0">Map unavailable</p>
							<small class="text-muted">${locationName}</small>
						</div>
					</div>
				`;
					coordinatesDisplay.textContent = "";
				});
		});
	</script>
	<!-- <?php include '../scripts/scripts.php'; ?> -->
</body>

</html>