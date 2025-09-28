<?php
include '../../../database/session.php';
include '../layout/head_links.php';

$query = "SELECT * FROM disaster_table"; // Adjust table name if needed
$result = mysqli_query($conn, $query);

if (!$result) {
	die("Query failed: " . mysqli_error($conn)); // Debugging for SQL errors
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<title>Disaster Management</title>
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
	<div class="app-wrapper">
		<?php include '../layout/header.php';
		include '../layout/sidebar.php';
		include '../alert/warning.php';
		// include '../pagination/pages_disaster_list.php'; 
		?>

		<main class="app-main">
			<div class="app-content-header">
				<div class="container-fluid">
					<div class="row">
						<div class="col-sm-6 d-flex align-items-center gap-2">
							<i class="bi bi-cloud-lightning-rain fs-2 text-primary"></i>
							<h3 class="mb-0">Disaster Records</h3>
						</div>
						<div class="col-sm-6">
							<ol class="breadcrumb float-sm-end">
								<li class="breadcrumb-item"><a href="#">Home</a></li>
								<li class="breadcrumb-item active" aria-current="page">Disaster Records</li>
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
								<button type="button" class="btn btn-primary btn-sm ms-auto" data-bs-toggle="modal" data-bs-target="#addDisasterModal">
									<i class="fas fa-plus-circle"></i> Add Disaster Record
								</button>
								<button type="button" class="btn btn-success btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#visualizeModal">
									<i class="fas fa-map-marked-alt"></i> Show Map & Graph
								</button>
							</div>

							<div class="card-body">
								<div class="table-responsive">
									<table id="disasterTable" class="table table-sm">
										<thead class="table-success">
											<tr>
											<tr>
												<th> No.</th>
												<th><i class="bi bi-exclamation-triangle-fill"></i> Disaster Type</th>
												<th><i class="bi bi-calendar-event-fill"></i> Date</th>
												<th><i class="bi bi-scale"></i> Scale(1-10)</th>
												<th><i class="bi bi-check-circle-fill"></i> Status</th>
												<th><i class="bi bi-gear-fill"></i> Actions</th>

											</tr>
											</tr>
										</thead>
										<tbody>
											<?php
											$counter = 1;
											if (mysqli_num_rows($result) > 0) {
												while ($disaster = mysqli_fetch_assoc($result)): ?>
													<tr>
														<td class="cell-number"><?php echo $counter++; ?>.</td>
														<td class="cell-disaster-name"><?php echo htmlspecialchars($disaster['disaster_name']); ?></td>
														<td class="cell-date"><?php echo htmlspecialchars($disaster['date']); ?></td>
														<td class="cell-scale"><?php echo htmlspecialchars($disaster['level']); ?></td>
														<td class="cell-status">
															<?php if ($disaster['status'] === 'Ongoing'): ?>
																<span class="badge bg-danger">Ongoing</span>
															<?php else: ?>
																<span class="badge bg-success">Resolved</span>
															<?php endif; ?>

														</td>
														<td>
															<a href="#" class="btn btn-outline-success btn-sm edit-btn"
																data-id="<?php echo $disaster['disaster_id']; ?>"
																data-type="<?php echo htmlspecialchars($disaster['disaster_name']); ?>"
																data-level="<?php echo htmlspecialchars($disaster['level']); ?>"
																data-date="<?php echo htmlspecialchars($disaster['date']); ?>"
																data-status="<?php echo htmlspecialchars($disaster['status']); ?>"
																data-bs-toggle="modal" data-bs-target="#editDisasterModal">
																<i class="fas fa-edit"></i> Edit
															</a>

															<a href="#" class="btn btn-outline-danger btn-sm delete-btn"
																data-id="<?php echo $disaster['disaster_id']; ?>"
																data-bs-toggle="modal" data-bs-target="#deleteDisasterModal">
																<i class="fas fa-trash"></i> Delete
															</a>
														</td>
													</tr>
											<?php endwhile;
											} else {
												echo "<tr><td colspan='5' class='text-center'>No disaster records found.</td></tr>";
											}
											?>
										</tbody>
									</table>
								</div>

								<!-- Pagination -->
								<!-- <div class="card-footer clearfix">
									<ul class="pagination pagination-sm m-0 float-end" style="font-size: 12px; line-height: 1; height: 20px;">
										<?php if ($page > 1) : ?>
											<li class="page-item">
												<a class="page-link px-1 py-0" style="padding: 3px 6px;" href="?page=<?php echo ($page - 1); ?>">&laquo;</a>
											</li>
										<?php endif; ?>
										<?php for ($i = 1; $i <= $totalPages; $i++) : ?>
											<li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
												<a class="page-link px-1 py-0" style="padding: 3px 6px;" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
											</li>
										<?php endfor; ?>
										<?php if ($page < $totalPages) : ?>
											<li class="page-item">
												<a class="page-link px-1 py-0" style="padding: 3px 6px;" href="?page=<?php echo ($page + 1); ?>">&raquo;</a>
											</li>
										<?php endif; ?>
									</ul>
								</div> -->
							</div>
						</div>
					</div>
				</div>
			</div>
		</main>

		<?php include '../layout/footer.php';
		include '../modal/disaster.php'; ?>
	</div>
	<?php
	// include '../modal/add_disaster.php';
	// include '../scripts/scripts.php';
	// include '../modal/edit_disaster.php';
	// include '../modal/delete_disaster.php'; 
	?>

	<!-- Visualize Modal: Map, Graph, and Barangay Dropdown -->
	<div class="modal fade" id="visualizeModal" tabindex="-1" aria-hidden="true">
		<div class="modal-dialog modal-xl modal-dialog-scrollable">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title"><i class="fas fa-chart-bar me-2"></i>Disaster Impact Visualization</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="row g-3">
						<div class="col-12 col-md-4">
							<label class="form-label">Select Barangay</label>
							<select id="barangaySelect" class="form-select form-select-sm">
								<option value="" selected disabled>Choose barangay...</option>
							</select>
						</div>
						<div class="col-12 col-md-8 d-flex align-items-end">
							<div class="ms-auto small text-muted" id="vizSummary"></div>
						</div>
						<div class="col-12 col-lg-7">
							<div id="map" style="width: 100%; height: 420px; border-radius: 8px; border: 1px solid #e5e7eb;"></div>
						</div>
						<div class="col-12 col-lg-5">
							<canvas id="affectedChart" height="420"></canvas>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>

	<!-- Leaflet & Chart.js CDN -->
	<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
	<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
	<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

	<script>
		(function(){
			let map, markerLayer, chart;

			async function loadBarangays() {
				try {
					const resp = await fetch('../../../address_json/barangays.json');
					const data = await resp.json();
					const select = document.getElementById('barangaySelect');
					select.innerHTML = '<option value="" disabled selected>Choose barangay...</option>';
					// Heuristic: expect array of barangay names or objects with name
					const list = Array.isArray(data) ? data : (data.barangays || data.data || []);
					list.forEach(item => {
						const name = typeof item === 'string' ? item : (item.name || item.barangay || item.brgy || '');
						if (!name) return;
						const opt = document.createElement('option');
						opt.value = name;
						opt.textContent = name;
						select.appendChild(opt);
					});
				} catch(e) {
					console.error('Failed to load barangays.json', e);
				}
			}

			function initMap() {
				if (map) return;
				map = L.map('map').setView([16.4023, 120.5960], 12);
				L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
				markerLayer = L.layerGroup().addTo(map);
			}

			function updateMapForBarangay(name){
				markerLayer.clearLayers();
				// Placeholder geocoding bounds; in real setup, map barangays to coords/polygons
				// Center shifts based on hash of name for visual differentiation
				const baseLat = 16.4023, baseLng = 120.5960;
				const hash = Array.from(name).reduce((a,c)=>a+c.charCodeAt(0),0);
				const lat = baseLat + ((hash % 100) - 50) * 0.0005;
				const lng = baseLng + ((hash % 80) - 40) * 0.0005;
				map.setView([lat, lng], 14);
				L.marker([lat,lng]).addTo(markerLayer).bindPopup('<b>'+name+'</b>');
			}

			function initChart(){
				if (chart) return chart;
				const ctx = document.getElementById('affectedChart');
				chart = new Chart(ctx, {
					type: 'bar',
					data: {
						labels: ['Houses Damaged', 'Families Affected', 'Evacuated', 'Casualties'],
						datasets: [{
							label: 'Affected Area Stats',
							data: [0,0,0,0],
							backgroundColor: ['#ef4444','#f59e0b','#3b82f6','#10b981']
						}]
					},
					options: {
						responsive: true,
						plugins: { legend: { display: false } },
						scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
					}
				});
				return chart;
			}

			function updateChartForBarangay(name){
				const ch = initChart();
				// Placeholder deterministic numbers by name; replace with real data when available
				const seed = Array.from(name).reduce((a,c)=>((a<<5)-a)+c.charCodeAt(0)|0,0);
				const rng = (n)=> Math.abs((seed*n) % 50);
				ch.data.datasets[0].data = [rng(3), rng(5), rng(7), Math.floor(rng(11)/5)];
				ch.update();
				document.getElementById('vizSummary').textContent = 'Showing approximated stats for '+name+'.';
			}

			// Events
			document.addEventListener('shown.bs.modal', function(ev){
				if (ev.target && ev.target.id === 'visualizeModal'){
					initMap();
					initChart();
					loadBarangays();
					setTimeout(()=>{ map.invalidateSize(); }, 200);
				}
			});

			document.addEventListener('change', function(ev){
				if (ev.target && ev.target.id === 'barangaySelect' && ev.target.value){
					updateMapForBarangay(ev.target.value);
					updateChartForBarangay(ev.target.value);
				}
			});
		})();
	</script>

	<!-- Search Script -->
	<script>
		$(document).ready(function() {
			$("#searchBox").on("keyup", function() {
				var searchTerm = $(this).val().toLowerCase().trim();

				$("#disasterTable tbody tr").each(function() {
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
		}

		/* Specific Column Styling */
		.cell-number {
			text-align: center;
			color: #888;
			font-weight: bold;
		}

		.cell-disaster-name {
			font-weight: 600;
			color: #212529;
		}

		.cell-severity {
			color: #ff9800;
			/* Orange for Severity */
			font-weight: 500;
		}

		.cell-date {
			color: #007BFF;
			/* Blue for Date */
			font-style: italic;
		}

		/* Optional Hover Effect */
		tbody tr:hover {
			background-color: #f6f9fc;
			transition: background-color 0.3s ease;
		}

		/* Optional Styling for Date */
		.cell-date {
			font-style: italic;
			color: #0069d9;
		}
	</style>

</body>

</html>
		
</body>

</html>