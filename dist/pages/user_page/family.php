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
								<div class="mt-2 text-center">
									<button id="saveCurrentCoordsBtn" class="btn btn-sm btn-success">Save My Position AS My Address</button>
									<span id="saveCoordsStatus" class="ms-2 text-muted small"></span>
								</div>
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
											barangay_manegement_table.barangay_name,
											barangay_manegement_table.latitude AS barangay_lat,
											barangay_manegement_table.longitude AS barangay_lng
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
											$coordinates = '';
											$coordinates_source = null; // 'exact' or 'barangay'
											if (!empty($row['latitude']) && !empty($row['longitude']) && $row['latitude'] != 0 && $row['longitude'] != 0) {
												$coordinates = $row['latitude'] . ", " . $row['longitude'];
												$coordinates_source = 'exact';
											} elseif (!empty($row['barangay_lat']) && !empty($row['barangay_lng']) && $row['barangay_lat'] != 0 && $row['barangay_lng'] != 0) {
												// fallback to barangay center if the family address has no saved coords
												$coordinates = $row['barangay_lat'] . ", " . $row['barangay_lng'];
												$coordinates_source = 'barangay';
											}
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
								$query = "SELECT * , qr.code FROM pre_reg_table 
											LEFT JOIN age_class_table ON pre_reg_table.age_class_id = age_class_table.age_class_id
											LEFT JOIN qr_table AS qr ON pre_reg_table.qr_id = qr.qr_id
											WHERE family_id = ? AND pre_reg_table.pre_reg_id != ?"; // Added condition to exclude current user
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
																	data-relation="<?= $member['relation_to_family'] ?>"
																	data-qr="<?= $member['code'] ?>">
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
			const coordinates = <?= json_encode($coordinates ?? '') ?>;
			const coordinatesSource = <?= json_encode($coordinates_source ?? '') ?>;
			const mapElement = document.getElementById("locationMap");
			const coordinatesDisplay = document.getElementById("coordinatesDisplay");

			// Define bounds for Negros Occidental
			const negrosBounds = L.latLngBounds(
				[9.4, 122.2], // Southwest
				[11.0, 123.2] // Northeast
			);

			// If coordinates are available, use them
			if (coordinates && coordinates.trim() !== '' && coordinates !== ',') {
				const [latStr, lonStr] = coordinates.split(',').map(s => s.trim());
				const lat = parseFloat(latStr);
				const lon = parseFloat(lonStr);
				if (!isNaN(lat) && !isNaN(lon) && lat !== 0 && lon !== 0) {
					displayMap(lat, lon, locationName);
					return;
				}
			}

			if (!locationName || locationName.trim() === "") {
				mapElement.innerHTML = `
				<div class="location-placeholder text-center py-4">
					<i class="fas fa-map-marked-alt fa-2x"></i>
					<p class="mt-2 mb-0">No address provided</p>
				</div>`;
				return;
			}

			mapElement.innerHTML = `
			<div class="location-placeholder text-center py-4">
				<div class="spinner-border text-primary" role="status">
					<span class="visually-hidden">Loading...</span>
				</div>
				<p class="mt-2 mb-0">Loading map...</p>
			</div>`;

			// Try forward geocoding
			fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(locationName)}`, {
					headers: {
						"User-Agent": "MyApp/1.0 (email@example.com)"
					}
				})
				.then(response => {
					if (!response.ok) throw new Error("Network error");
					return response.json();
				})
				.then(data => {
					if (data.length === 0) throw new Error("Location not found");

					const lat = parseFloat(data[0].lat);
					const lon = parseFloat(data[0].lon);
					displayMap(lat, lon, locationName);
				})
				.catch(error => {
					console.warn("Forward geocoding failed:", error.message);

					mapElement.innerHTML = "";

					// Default to center of Negros Occidental
					const fallbackLat = 10.4;
					const fallbackLon = 122.95;

					const map = L.map('locationMap', {
						maxBounds: negrosBounds,
						maxBoundsViscosity: 1.0,
						minZoom: 7,
						maxZoom: 18
					}).setView([fallbackLat, fallbackLon], 9);

					L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
						attribution: '&copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a>'
					}).addTo(map);

					let marker;

					map.on('click', function(e) {
						const lat = e.latlng.lat.toFixed(6);
						const lon = e.latlng.lng.toFixed(6);

						if (marker) map.removeLayer(marker);
						marker = L.marker(e.latlng).addTo(map)
							.bindPopup(`Pinned location:<br><strong>${lat}, ${lon}</strong>`)
							.openPopup();

						coordinatesDisplay.innerHTML = `<strong>Coordinates (manual):</strong> ${lat}, ${lon}`;
					});

					coordinatesDisplay.innerHTML = `
					<strong>Note:</strong> Address "<em>${locationName}</em>" not found. Click the map to pin the location.`;
				});

			function displayMap(lat, lon, label) {
				const note = (coordinatesSource === 'barangay') ? ' <small>(Barangay center)</small>' : '';
				coordinatesDisplay.innerHTML = `<strong>Coordinates:</strong> ${lat.toFixed(6)}, ${lon.toFixed(6)}${note}`;
				mapElement.innerHTML = "";

				const map = L.map('locationMap', {
					maxBounds: negrosBounds,
					maxBoundsViscosity: 1.0,
					minZoom: 7,
					maxZoom: 18
				}).setView([lat, lon], 15);

				L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
					attribution: '&copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a>',
					maxZoom: 19
				}).addTo(map);

				const customIcon = L.icon({
					iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
					iconSize: [25, 41],
					iconAnchor: [12, 41],
					popupAnchor: [1, -34],
				});

				// Only add a pin when this is the exact saved address; if it's the barangay fallback, just center the map.
				if (!coordinatesSource || coordinatesSource !== 'barangay') {
					L.marker([lat, lon], {
							icon: customIcon
						})
						.addTo(map)
						.bindPopup(`<strong>Family Location</strong><br>${label}<br><span style='font-size:12px;'>${lat.toFixed(6)}, ${lon.toFixed(6)}</span>`)
						.openPopup();
				}
			}
		});

		document.getElementById("saveCurrentCoordsBtn").addEventListener("click", function() {
			const statusEl = document.getElementById("saveCoordsStatus");

			if (!navigator.geolocation) {
				Swal.fire("Error", "Geolocation is not supported by your browser.", "error");
				return;
			}

			// Ask user first
			Swal.fire({
				title: "Save My Location?",
				text: "Do you want to save your current location now?",
				icon: "question",
				showCancelButton: true,
				confirmButtonText: "Yes, save it",
				cancelButtonText: "Cancel"
			}).then((result) => {
				if (!result.isConfirmed) {
					statusEl.textContent = "Action cancelled.";
					return;
				}

				statusEl.textContent = "Getting location...";

				navigator.geolocation.getCurrentPosition(
					function(position) {
						const lat = position.coords.latitude;
						const lng = position.coords.longitude;

						// Prepare form data
						const formData = new FormData();
						formData.append("latitude", lat);
						formData.append("longitude", lng);

						// Send to backend
						fetch("../action_user/save_coordinates.php", {
								method: "POST",
								body: formData
							})
							.then(res => res.json())
							.then(data => {
								console.log("Response:", data); // for debugging

								if (data.success) {
									statusEl.textContent = `✅ Location saved at ${lat.toFixed(5)}, ${lng.toFixed(5)}`;
									Swal.fire({
										icon: "success",
										title: "Location Saved",
										text: "Your location has been saved successfully.",
										timer: 2000,
										showConfirmButton: false
									});
								} else {
									statusEl.textContent = "⚠️ Failed to save location.";
									Swal.fire("Error", data.error || "Failed to save coordinates.", "error");
								}
							})
							.catch(() => {
								statusEl.textContent = "⚠️ Network error.";
								Swal.fire("Error", "Could not connect to the server.", "error");
							});
					},
					function(error) {
						let msg;
						switch (error.code) {
							case error.PERMISSION_DENIED:
								msg = "❌ Permission denied.";
								break;
							case error.POSITION_UNAVAILABLE:
								msg = "⚠️ Position unavailable.";
								break;
							case error.TIMEOUT:
								msg = "⌛ Request timed out.";
								break;
							default:
								msg = "⚠️ An unknown error occurred.";
								break;
						}
						statusEl.textContent = msg;
						Swal.fire("Error", msg, "error");
					}
				);
			});
		})
	</script>

	<!-- <?php include '../scripts/scripts.php'; ?> -->
</body>

</html>