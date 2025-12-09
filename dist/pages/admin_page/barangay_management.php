<?php
include '../../../database/session.php';
include '../layout/head_links.php';

// Load boundary data (keep fencing intact)
$barangayBoundaries = [];
$boundaryFile = dirname(__DIR__, 3) . '/address_json/barangay_boundaries.json';
if (file_exists($boundaryFile)) {
	$boundaryContent = @file_get_contents($boundaryFile);
	if ($boundaryContent !== false) {
		$decoded = json_decode($boundaryContent, true);
		if (is_array($decoded)) {
			$barangayBoundaries = $decoded;
		}
	}
}

$query = "SELECT b.*, IFNULL(b.evacuation_needed, 0) AS evacuation_needed,
	(
		SELECT COUNT(*)
		FROM pre_reg_table pr
		LEFT JOIN solo_address_table sat ON pr.solo_address_id = sat.solo_address_id
		LEFT JOIN family_table ft ON pr.family_id = ft.family_id
		WHERE (sat.barangay_id = b.barangay_id) OR (ft.barangay_id = b.barangay_id)
	) AS pre_reg_count
	FROM barangay_manegement_table b";

$result = mysqli_query($conn, $query);

if (!$result) {
	die("Query failed: " . mysqli_error($conn));
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

			<div class="content">
				<div class="row">
					<div class="col-md-12">
						<div class="card">
							<div class="card-header d-flex align-items-center gap-2">
								<input type="text" id="searchBox" class="form-control me-2" placeholder="Search..." style="max-width: 300px;">
								<button type="button" class="btn btn-sm btn-outline-primary" id="showProneBelow">Show Prone Areas Below</button>
								<button type="button" class="btn btn-sm btn-outline-info" id="showMapBtn" data-bs-toggle="modal" data-bs-target="#barangayMapModal">
									<i class="fas fa-map"></i> View Map
								</button>
								<button type="button" class="btn btn-sm btn-outline-success ms-auto" id="markAllEvac">Mark all evacuation</button>
								<button type="button" class="btn btn-sm btn-outline-secondary" id="clearAllEvac">Clear all</button>
								<button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addLocationModal">
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
												<th><i class="bi bi-people-fill"></i> Total Population</th>
												<th><i class="bi bi-exclamation-triangle-fill"></i> Disaster-Prone Type</th>
												<th><i class="bi bi-list-check"></i> Pre-registered</th>
												<th><i class="bi bi-exclamation-triangle"></i> Evacuation Needed</th>
												<th class="text-center" style="text-align: center; vertical-align: middle;">
													<i class="bi bi-gear-fill"></i> Actions
												</th>
											</tr>
										</thead>
										<tbody>
											<?php
											$counter = 1;
											$barangayMapData = []; // Store barangay data for map
											mysqli_data_seek($result, 0); // Reset result pointer
											if (mysqli_num_rows($result) > 0) {
												while ($barangay = mysqli_fetch_assoc($result)): 
													// Store data for map
													if (!empty($barangay['latitude']) && !empty($barangay['longitude'])) {
														$barangayMapData[] = [
															'name' => $barangay['barangay_name'],
															'captain' => $barangay['barangay_captain_name'],
															'population' => (int)$barangay['total_population'],
															'latitude' => (float)$barangay['latitude'],
															'longitude' => (float)$barangay['longitude']
														];
													}
												?>
													<tr>
														<td class="cell-number"><?php echo $counter++; ?>.</td>
														<td class="cell-location "><?php echo htmlspecialchars($barangay['barangay_name']); ?></td>
														<td class="cell-address justify-content-center text-centerz"><?php echo htmlspecialchars($barangay['barangay_captain_name']); ?></td>
														<td class="cell-population justify-content-center text-centerz"><?php echo number_format((int)$barangay['total_population']); ?></td>
														<td class="cell-disaster-prone text-center">
															<?php 
															$proneTypes = [];
															// Load from JSON file only
															if (isset($barangayBoundaries[$barangay['barangay_name']]) && 
																isset($barangayBoundaries[$barangay['barangay_name']]['disaster_prone_types'])) {
																$proneTypes = $barangayBoundaries[$barangay['barangay_name']]['disaster_prone_types'];
															}
															$proneTypeDisplay = !empty($proneTypes) ? implode(', ', array_map('htmlspecialchars', $proneTypes)) : 'Not specified';
															echo $proneTypeDisplay;
															?>
														</td>
														<td class="cell-prereg text-center"><?php echo isset($barangay['pre_reg_count']) ? number_format((int)$barangay['pre_reg_count'], 0) : 0; ?></td>
														<td>
															<div class="form-check form-switch">
																<input class="form-check-input evac-toggle" type="checkbox" role="switch" data-id="<?php echo (int)$barangay['barangay_id']; ?>" <?php echo ((int)$barangay['evacuation_needed'] === 1) ? 'checked' : ''; ?>>
															</div>
														</td>
														<td class="justify-content-center text-center">
															<a href="#" class="btn btn-outline-success btn-sm edit-btn shadow"
																data-id="<?php echo (int)$barangay['barangay_id']; ?>"
																data-name="<?php echo htmlspecialchars($barangay['barangay_name']); ?>"
																data-captain="<?php echo htmlspecialchars($barangay['barangay_captain_name']); ?>"
																data-signature="<?php echo htmlspecialchars($barangay['signature_brgy_captain']); ?>"
																data-population="<?php echo (int)$barangay['total_population']; ?>"
																data-latitude="<?php echo htmlspecialchars($barangay['latitude']); ?>"
																data-longitude="<?php echo htmlspecialchars($barangay['longitude']); ?>"
																data-bs-toggle="modal" data-bs-target="#editLocationModal">
																<i class="fas fa-edit"></i> Edit
															</a>

															<a href="#" class="btn btn-outline-danger btn-sm delete-btn shadow"
																data-id="<?php echo (int)$barangay['barangay_id']; ?>"
																data-bs-toggle="modal" data-bs-target="#deleteLocationModal">
																<i class="fas fa-trash"></i> Delete
															</a>

							<a href="#" 
								class="btn btn-outline-primary btn-sm shadow view-btn"
								data-id="<?php echo (int)$barangay['barangay_id']; ?>"
								data-name1="<?php echo htmlspecialchars($barangay['barangay_name']); ?>"
								data-captain="<?php echo htmlspecialchars($barangay['barangay_captain_name']); ?>"
								data-signature="<?php echo htmlspecialchars($barangay['signature_brgy_captain']); ?>"
								data-latitude="<?php echo htmlspecialchars($barangay['latitude']); ?>"
								data-longitude="<?php echo htmlspecialchars($barangay['longitude']); ?>"
								data-bs-toggle="modal" data-bs-target="#viewBarangayModal">
								<i class="fas fa-eye"></i> View
							</a>
														</td>
													</tr>
											<?php endwhile;
											} else {
												echo "<tr><td colspan='8' class='text-center'>No location records found.</td></tr>";
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

		<?php include '../layout/footer.php'; ?>

		<script>
			// Pass boundary data to JavaScript
			window.barangayBoundaries = <?php echo json_encode($barangayBoundaries, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
			// Pass barangay map data to JavaScript
			window.barangayMapData = <?php echo json_encode($barangayMapData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
			console.log('Loaded boundary data:', window.barangayBoundaries);
			console.log('Loaded barangay map data:', window.barangayMapData);
		</script>
	</div>

	<?php include '../modal/evac_location/barangay_management_modal.php'; ?>

	<!-- Barangay Map Modal -->
	<div class="modal fade" id="barangayMapModal" tabindex="-1" aria-labelledby="barangayMapModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered" style="max-width: 95%; width: 95%;">
			<div class="modal-content">
				<div class="modal-header bg-primary text-white">
					<h5 class="modal-title" id="barangayMapModalLabel">
						<i class="fas fa-map"></i> Barangay Map & Prone Areas
					</h5>
					<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body p-0">
					<div id="barangayMap" style="height: 600px; width: 100%;"></div>
					<div class="p-3 bg-light border-top">
						<div class="row">
							<div class="col-md-8">
								<h6 class="mb-2"><i class="fas fa-info-circle"></i> Legend</h6>
								<div class="row">
									<div class="col-md-6">
										<div class="d-flex align-items-center mb-2">
											<i class="fas fa-map-marker-alt text-primary me-2" style="font-size: 20px;"></i>
											<span>Barangay Location</span>
										</div>
										<div class="d-flex align-items-center mb-2">
											<div class="me-2" style="width: 20px; height: 20px; background-color: rgba(0, 102, 255, 0.4); border: 2px solid #0066ff;"></div>
											<span>Flood-prone Area</span>
										</div>
										<div class="d-flex align-items-center mb-2">
											<div class="me-2" style="width: 20px; height: 20px; background-color: rgba(255, 153, 0, 0.4); border: 2px solid #ff9900;"></div>
											<span>Earthquake-prone Area</span>
										</div>
									</div>
									<div class="col-md-6">
										<div class="d-flex align-items-center mb-2">
											<div class="me-2" style="width: 20px; height: 20px; background-color: rgba(139, 69, 19, 0.4); border: 2px solid #8b4513;"></div>
											<span>Landslide-prone Area</span>
										</div>
										<div class="d-flex align-items-center mb-2">
											<div class="me-2" style="width: 20px; height: 20px; background-color: rgba(255, 0, 255, 0.4); border: 2px solid #ff00ff;"></div>
											<span>Typhoon-prone Area</span>
										</div>
										<div class="d-flex align-items-center mb-2">
											<div class="me-2" style="width: 20px; height: 20px; background-color: rgba(255, 0, 0, 0.4); border: 2px solid #ff0000;"></div>
											<span>Other Prone Areas</span>
										</div>
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<h6 class="mb-2"><i class="fas fa-filter"></i> Controls</h6>
								<button type="button" class="btn btn-sm btn-outline-primary me-2" id="toggleBarangayMarkers">
									<i class="fas fa-map-marker-alt"></i> Toggle Barangays
								</button>
								<button type="button" class="btn btn-sm btn-outline-danger" id="toggleProneAreas">
									<i class="fas fa-exclamation-triangle"></i> Toggle Prone Areas
								</button>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>

	<!-- Leaflet CSS and JS -->
	<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
	<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

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
	<script>
		// Single toggle
		document.addEventListener('DOMContentLoaded', function() {
			document.querySelectorAll('.evac-toggle').forEach(function(cb) {
				cb.addEventListener('change', async function() {
					const id = this.getAttribute('data-id');
					const prev = !this.checked; // remember previous state to revert if needed
					const needed = this.checked ? 1 : 0;
					this.disabled = true;
					try {
						const resp = await fetch('../action/brgy_management_action/toggle_evacuation_db.php', {
							method: 'POST',
							headers: {
								'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
							},
							body: 'barangay_id=' + encodeURIComponent(id) + '&needed=' + needed
						});
						const data = await resp.json().catch(() => ({
							ok: false,
							error: 'invalid_json'
						}));
						if (!resp.ok || !data.ok) {
							console.warn('Save failed', data);
							this.checked = prev; // revert
							alert('Failed to save evacuation flag.');
						}
					} catch (e) {
						console.warn('Request error', e);
						this.checked = prev; // revert
						alert('Network error while saving.');
					} finally {
						this.disabled = false;
					}
				});
			});
			// Bulk buttons
			document.getElementById('markAllEvac').addEventListener('click', async function() {
				try {
					const resp = await fetch('../action/brgy_management_action/set_evac_all.php', {
						method: 'POST',
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
						},
						body: 'needed=1'
					});
					const data = await resp.json().catch(() => ({
						ok: false
					}));
					if (resp.ok && data.ok) {
						document.querySelectorAll('.evac-toggle').forEach(cb => {
							cb.checked = true;
						});
					} else {
						alert('Failed to mark all.');
					}
				} catch (e) {
					console.warn(e);
					alert('Network error while marking all.');
				}
			});
			document.getElementById('clearAllEvac').addEventListener('click', async function() {
				try {
					const resp = await fetch('../action/brgy_management_action/set_evac_all.php', {
						method: 'POST',
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
						},
						body: 'needed=0'
					});
					const data = await resp.json().catch(() => ({
						ok: false
					}));
					if (resp.ok && data.ok) {
						document.querySelectorAll('.evac-toggle').forEach(cb => {
							cb.checked = false;
						});
					} else {
						alert('Failed to clear all.');
					}
				} catch (e) {
					console.warn(e);
					alert('Network error while clearing all.');
				}
			});

			// Prone Areas Filter
			document.getElementById('showProneBelow').addEventListener('click', async function() {
				const btn = this;
				const originalText = btn.textContent;

				if (btn.classList.contains('active')) {
					// Reset filter - show all barangays
					btn.classList.remove('active');
					btn.textContent = 'Show Prone Areas Below';
					$("#locationTable tbody tr").show();
					return;
				}

				btn.textContent = 'Loading...';

				try {
					// Fetch flood prone boundaries
					const response = await fetch('../../../address_json/barangay_boundaries.json', {
						cache: 'no-cache'
					});
					const proneData = await response.json();
					const proneBarangays = Object.keys(proneData || {});

					if (proneBarangays.length === 0) {
						alert('No barangays with flood-prone boundaries found.');
						btn.textContent = originalText;
						return;
					}

					// Filter table rows
					$("#locationTable tbody tr").each(function() {
						const barangayName = $(this).find('.cell-location').text().trim();
						if (proneBarangays.includes(barangayName)) {
							$(this).show();
						} else {
							$(this).hide();
						}
					});

					btn.classList.add('active');
					btn.textContent = 'Show All Barangays';

				} catch (error) {
					console.error('Error loading prone areas:', error);
					alert('Error loading flood-prone areas data.');
					btn.textContent = originalText;
				}
			});

			// Barangay Map Modal
			let barangayMap = null;
			let barangayMarkers = [];
			let proneAreaLayers = [];
			let showBarangays = true;
			let showProneAreas = true;

			const mapModal = document.getElementById('barangayMapModal');
			mapModal.addEventListener('shown.bs.modal', function() {
				if (!barangayMap) {
					// Initialize map centered on Philippines (adjust based on your location)
					barangayMap = L.map('barangayMap').setView([10.5351, 122.8357], 12);
					
					L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
						attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
						maxZoom: 19
					}).addTo(barangayMap);

					// Load barangay data from table
					loadBarangaysOnMap();
					loadProneAreasOnMap();
				} else {
					// Resize map if already initialized
					setTimeout(() => {
						barangayMap.invalidateSize();
					}, 100);
				}
			});

			function loadBarangaysOnMap() {
				// Clear existing markers
				barangayMarkers.forEach(marker => barangayMap.removeLayer(marker));
				barangayMarkers = [];

				// Get barangay data from PHP
				const barangayData = window.barangayMapData || [];

				barangayData.forEach(barangay => {
					const lat = barangay.latitude;
					const lng = barangay.longitude;
					
					if (!isNaN(lat) && !isNaN(lng) && lat !== 0 && lng !== 0) {
						// Create custom icon
						const customIcon = L.icon({
							iconUrl: 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png',
							iconSize: [32, 32],
							iconAnchor: [16, 32],
							popupAnchor: [0, -32]
						});

						const marker = L.marker([lat, lng], { icon: customIcon })
							.addTo(barangayMap)
							.bindPopup(`
								<div style="min-width: 200px;">
									<h6 class="mb-2"><strong>${barangay.name}</strong></h6>
									<p class="mb-1"><i class="fas fa-user"></i> <strong>Captain:</strong> ${barangay.captain || 'N/A'}</p>
									<p class="mb-1"><i class="fas fa-users"></i> <strong>Population:</strong> ${barangay.population.toLocaleString()}</p>
									<p class="mb-0"><i class="fas fa-map-marker-alt"></i> <strong>Location:</strong> ${lat.toFixed(6)}, ${lng.toFixed(6)}</p>
								</div>
							`);

						barangayMarkers.push(marker);
					}
				});

			}

			function loadProneAreasOnMap() {
				// Clear existing prone area layers
				proneAreaLayers.forEach(layer => {
					if (barangayMap.hasLayer(layer)) {
						barangayMap.removeLayer(layer);
					}
				});
				proneAreaLayers = [];

				// Get boundary data from JSON
				const boundaries = window.barangayBoundaries || {};
				console.log('Loading prone areas, boundaries data:', boundaries);
				console.log('Number of boundaries:', Object.keys(boundaries).length);

				let polygonsCreated = 0;
				Object.keys(boundaries).forEach(barangayName => {
					const barangayData = boundaries[barangayName];
					
					// Check if coordinates exist (from barangay_boundaries.json structure)
					if (barangayData.coordinates && Array.isArray(barangayData.coordinates) && barangayData.coordinates.length > 0) {
						// Convert coordinates from {lat, lng} format to [lat, lng] array for Leaflet
						const latlngs = barangayData.coordinates.map(coord => {
							// Handle both formats: {lat, lng} or [lat, lng]
							if (coord.lat !== undefined && coord.lng !== undefined) {
								return [parseFloat(coord.lat), parseFloat(coord.lng)];
							} else if (Array.isArray(coord) && coord.length >= 2) {
								return [parseFloat(coord[0]), parseFloat(coord[1])];
							}
							return null;
						}).filter(coord => coord !== null);

						if (latlngs.length > 0) {
							// Get disaster prone types
							const proneTypes = barangayData.disaster_prone_types || [];
							console.log(`Barangay: ${barangayName}, Prone Types:`, proneTypes);
							
							// Determine color based on prone types (prioritize first type, or use combined color for multiple)
							let fillColor = '#ff0000'; // Default red
							let borderColor = '#ff0000';
							
							if (proneTypes.length > 0) {
								// If multiple types, use a gradient or the first type's color
								if (proneTypes.includes('Flood-prone')) {
									fillColor = '#0066ff'; // Blue for flood
									borderColor = '#0044cc';
								} else if (proneTypes.includes('Earthquake-prone')) {
									fillColor = '#ff9900'; // Orange for earthquake
									borderColor = '#cc7700';
								} else if (proneTypes.includes('Landslide-prone')) {
									fillColor = '#8b4513'; // Brown for landslide
									borderColor = '#6b3410';
								} else if (proneTypes.includes('Typhoon-prone')) {
									fillColor = '#ff00ff'; // Magenta for typhoon
									borderColor = '#cc00cc';
								}
								
								// If multiple types, make it slightly more transparent
								const opacity = proneTypes.length > 1 ? 0.35 : 0.4;
								
								// Create polygon for prone area boundary
								const polygon = L.polygon(latlngs, {
									color: borderColor,
									fillColor: fillColor,
									fillOpacity: opacity,
									weight: 3,
									opacity: 0.9,
									interactive: true
								});

								// Add to map
								polygon.addTo(barangayMap);

								// Create popup with barangay info and prone types
								const proneTypesList = proneTypes.length > 0 ? 
									proneTypes.map(type => `<li><strong>${type}</strong></li>`).join('') : 
									'<li><em>Not specified</em></li>';
								
								polygon.bindPopup(`
									<div style="min-width: 280px;">
										<h6 class="mb-2"><strong><i class="fas fa-map-marker-alt text-primary"></i> ${barangayName}</strong></h6>
										<hr class="my-2">
										<p class="mb-2"><i class="fas fa-exclamation-triangle text-danger"></i> <strong>Disaster Prone Types:</strong></p>
										<ul class="mb-0" style="padding-left: 20px; list-style-type: disc;">
											${proneTypesList}
										</ul>
										${proneTypes.length > 1 ? '<p class="mb-0 mt-2 text-info small"><i class="fas fa-info-circle"></i> Multiple disaster types detected</p>' : ''}
										<p class="mb-0 mt-2 text-muted small"><i class="fas fa-info-circle"></i> Click to view boundary area</p>
									</div>
								`);

								// Add tooltip on hover
								polygon.bindTooltip(`${barangayName} - ${proneTypes.length > 0 ? proneTypes.join(', ') : 'Prone Area'}`, {
									permanent: false,
									direction: 'center',
									className: 'custom-tooltip'
								});

								proneAreaLayers.push(polygon);
								polygonsCreated++;
							} else {
								// Even if no prone types specified, show the boundary
								const polygon = L.polygon(latlngs, {
									color: '#666666',
									fillColor: '#cccccc',
									fillOpacity: 0.3,
									weight: 2,
									opacity: 0.6,
									interactive: true
								}).addTo(barangayMap);

								polygon.bindPopup(`
									<div style="min-width: 250px;">
										<h6 class="mb-2"><strong><i class="fas fa-map-marker-alt"></i> ${barangayName}</strong></h6>
										<p class="mb-0 text-muted"><em>No disaster prone types specified</em></p>
									</div>
								`);

								proneAreaLayers.push(polygon);
								polygonsCreated++;
							}
						}
					} else if (barangayData.disaster_prone_types && barangayData.disaster_prone_types.length > 0) {
						// Barangay has prone types but no coordinates - log for debugging
						console.warn(`Barangay "${barangayName}" has prone types but no coordinates:`, barangayData.disaster_prone_types);
					}
				});

				console.log(`Created ${polygonsCreated} prone area polygons`);
				console.log('Prone area layers:', proneAreaLayers.length);

				// Fit map to show all prone areas if any exist
				if (proneAreaLayers.length > 0) {
					const proneGroup = new L.featureGroup(proneAreaLayers);
					// Only adjust bounds if we have markers too, otherwise fit to prone areas
					if (barangayMarkers.length === 0) {
						barangayMap.fitBounds(proneGroup.getBounds().pad(0.1));
					} else {
						// Fit to both markers and prone areas
						const allFeatures = new L.featureGroup([...barangayMarkers, ...proneAreaLayers]);
						barangayMap.fitBounds(allFeatures.getBounds().pad(0.1));
					}
				}
			}

			// Toggle buttons
			document.getElementById('toggleBarangayMarkers').addEventListener('click', function() {
				showBarangays = !showBarangays;
				barangayMarkers.forEach(marker => {
					if (showBarangays) {
						barangayMap.addLayer(marker);
					} else {
						barangayMap.removeLayer(marker);
					}
				});
				this.textContent = showBarangays ? 
					'<i class="fas fa-map-marker-alt"></i> Hide Barangays' : 
					'<i class="fas fa-map-marker-alt"></i> Show Barangays';
			});

			document.getElementById('toggleProneAreas').addEventListener('click', function() {
				showProneAreas = !showProneAreas;
				proneAreaLayers.forEach(layer => {
					if (showProneAreas) {
						barangayMap.addLayer(layer);
					} else {
						barangayMap.removeLayer(layer);
					}
				});
				this.textContent = showProneAreas ? 
					'<i class="fas fa-exclamation-triangle"></i> Hide Prone Areas' : 
					'<i class="fas fa-exclamation-triangle"></i> Show Prone Areas';
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
		}

		/* Custom tooltip styling for prone areas */
		.custom-tooltip {
			background-color: rgba(0, 0, 0, 0.8);
			color: white;
			padding: 5px 10px;
			border-radius: 4px;
			font-size: 12px;
			font-weight: bold;
			border: 1px solid #fff;
		}

		/* Ensure polygons are visible */
		.leaflet-interactive {
			cursor: pointer;
		}
	</style>

</body>

</html>