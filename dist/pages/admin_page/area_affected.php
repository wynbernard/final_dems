<?php
include '../../../database/session.php';
include '../layout/head_links.php';

// Load affected statistics from evacuation records
$statsQuery = "SELECT evacuation_location, SUM(total_evacuation) AS total_evacuees, SUM(total_family) AS total_families, SUM(total_solo) AS total_solos FROM evacuation_record_table GROUP BY evacuation_location ORDER BY evacuation_location";
$statsResult = mysqli_query($conn, $statsQuery);

$affectedStats = [];
$barangaysWithData = [];
if ($statsResult) {
	while ($row = mysqli_fetch_assoc($statsResult)) {
		$barangay = trim($row['evacuation_location'] ?? '');
		if ($barangay === '') continue;
		$barangaysWithData[] = $barangay;
		$affectedStats[] = [
			'barangay' => $barangay,
			'total_evacuees' => (int)($row['total_evacuees'] ?? 0),
			'total_families' => (int)($row['total_families'] ?? 0),
			'total_solos' => (int)($row['total_solos'] ?? 0)
		];
	}
}

// Attempt to load complete barangay list from JSON (optional)
$barangayOptions = [];
$barangayJsonPath = realpath(__DIR__ . '/../../../address_json/barangays.json');
if ($barangayJsonPath && file_exists($barangayJsonPath)) {
	$jsonContent = @file_get_contents($barangayJsonPath);
	if ($jsonContent !== false) {
		$decoded = json_decode($jsonContent, true);
		if (is_array($decoded)) {
			if (isset($decoded['barangays']) && is_array($decoded['barangays'])) {
				$barangayOptions = $decoded['barangays'];
			} elseif (isset($decoded['data']) && is_array($decoded['data'])) {
				$barangayOptions = $decoded['data'];
			} elseif (array_keys($decoded) === range(0, count($decoded) - 1)) {
				$barangayOptions = $decoded;
			}
		}
	}
}

if (empty($barangayOptions)) {
	$barangayOptions = $barangaysWithData;
}

$barangayOptions = array_values(array_unique(array_filter(array_map('trim', $barangayOptions))));
sort($barangayOptions);

$defaultBarangay = $barangaysWithData[0] ?? ($barangayOptions[0] ?? '');
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<title>Affected Areas Map</title>
	<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="anonymous">
	<style>
		#map {
			height: 460px;
			border-radius: 12px;
			border: 1px solid #e5e7eb;
			box-shadow: 0 12px 24px rgba(15, 23, 42, 0.08);
		}

		.summary-card {
			border-radius: 12px;
			border: 1px solid rgba(148, 163, 184, 0.2);
			background: #f8fafc;
			padding: 12px 16px;
		}

		.summary-card h6 {
			font-size: 0.85rem;
			margin-bottom: 2px;
			color: #0f172a;
		}

		.summary-card span {
			font-weight: 600;
			color: #1e293b;
		}

		#affectedChart {
			max-height: 320px;
		}
	</style>
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
	<div class="app-wrapper">
		<?php
		include '../layout/header.php';
		include '../layout/sidebar.php';
		include '../alert/warning.php';
		?>

		<main class="app-main">
			<div class="app-content-header">
				<div class="container-fluid">
					<div class="row align-items-center">
						<div class="col-sm-7 d-flex align-items-center gap-2">
							<i class="bi bi-geo-alt-fill fs-2 text-danger"></i>
							<h3 class="mb-0">Affected Areas Overview</h3>
						</div>
						<div class="col-sm-5">
							<ol class="breadcrumb float-sm-end">
								<li class="breadcrumb-item"><a href="./Dashboard.php">Home</a></li>
								<li class="breadcrumb-item active" aria-current="page">Affected Areas</li>
							</ol>
						</div>
					</div>
				</div>
			</div>

			<div class="content">
				<div class="row g-3">
					<div class="col-12">
						<div class="card shadow-sm">
							<div class="card-header bg-white d-flex flex-wrap gap-3 align-items-center">
								<div>
									<h5 class="card-title mb-0">Visualize Affected Barangays</h5>
									<small class="text-muted">Select a barangay to plot on the map and review the aggregated impact.</small>
								</div>
								<div class="ms-auto">
									<select id="barangaySelect" class="form-select form-select-sm" style="min-width: 220px;">
										<option value="" disabled selected>Select barangay...</option>
										<?php foreach ($barangayOptions as $barangay): ?>
											<option value="<?php echo htmlspecialchars($barangay); ?>" <?php echo ($barangay === $defaultBarangay) ? 'selected' : ''; ?>>
												<?php echo htmlspecialchars($barangay); ?><?php echo in_array($barangay, $barangaysWithData, true) ? '' : ' (no data)'; ?>
											</option>
										<?php endforeach; ?>
									</select>
								</div>
							</div>
							<div class="card-body">
								<div class="row g-3">
									<div class="col-12 col-xl-7">
										<div id="map"></div>
									</div>
									<div class="col-12 col-xl-5 d-flex flex-column gap-3">
										<div class="summary-card">
											<h6 id="summaryBarangay">&nbsp;</h6>
											<div class="d-flex flex-wrap gap-3">
												<div>
													<small class="text-muted">Total Evacuees</small>
													<div><span id="summaryEvacuees">0</span></div>
												</div>
												<div>
													<small class="text-muted">Families</small>
													<div><span id="summaryFamilies">0</span></div>
												</div>
												<div>
													<small class="text-muted">Solo Evacuees</small>
													<div><span id="summarySolos">0</span></div>
												</div>
											</div>
											<div class="mt-2 small text-muted" id="dataNotice"></div>
										</div>
										<div class="card border-0 shadow-sm">
											<div class="card-body">
												<canvas id="affectedChart"></canvas>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</main>
	</div>
	<?php include '../layout/footer.php'; ?>

	<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
	<script>
		const affectedStats = <?php echo json_encode($affectedStats, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
		const statsByBarangay = affectedStats.reduce((acc, item) => {
			acc[item.barangay.toLowerCase()] = item;
			return acc;
		}, {});
		const defaultBarangay = <?php echo json_encode($defaultBarangay, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

		let mapInstance;
		let markersLayer;
		let chartInstance;

		function initMap() {
			if (mapInstance) return mapInstance;
			const BAGUIO_CENTER = [16.4023, 120.5960];
			mapInstance = L.map('map').setView(BAGUIO_CENTER, 13);
			L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(mapInstance);
			markersLayer = L.layerGroup().addTo(mapInstance);
			L.circle(BAGUIO_CENTER, {
				radius: 4500,
				color: '#2563eb',
				fillColor: '#60a5fa',
				fillOpacity: 0.08
			}).addTo(mapInstance).bindTooltip('Baguio City');
			return mapInstance;
		}

		async function updateMapForBarangay(name) {
			initMap();
			markersLayer.clearLayers();
			const BAGUIO_CENTER = [16.4023, 120.5960];
			if (!name) {
				mapInstance.setView(BAGUIO_CENTER, 13);
				return;
			}
			try {
				const resp = await fetch(`https://nominatim.openstreetmap.org/search?format=json&limit=1&q=${encodeURIComponent(name + ', Baguio City, Philippines')}`, {
					headers: { 'Accept': 'application/json' }
				});
				const places = await resp.json();
				if (Array.isArray(places) && places.length > 0) {
					const lat = parseFloat(places[0].lat);
					const lon = parseFloat(places[0].lon);
					mapInstance.setView([lat, lon], 15);
					L.marker([lat, lon]).addTo(markersLayer).bindPopup(`<strong>${name}</strong>`);
					return;
				}
			} catch (error) {
				console.warn('Geocoding failed', error);
			}
			mapInstance.setView(BAGUIO_CENTER, 13);
			L.marker(BAGUIO_CENTER).addTo(markersLayer).bindPopup(`<strong>${name}</strong><br><em>Approximate location</em>`);
		}

		function initChart() {
			if (chartInstance) return chartInstance;
			const ctx = document.getElementById('affectedChart');
			chartInstance = new Chart(ctx, {
				type: 'bar',
				data: {
					labels: ['Total Evacuees', 'Families', 'Solo Evacuees'],
					datasets: [{
						label: 'Affected Population',
						data: [0, 0, 0],
						backgroundColor: ['#ef4444', '#3b82f6', '#22c55e']
					}]
				},
				options: {
					responsive: true,
					plugins: {
						legend: { display: false }
					},
					scales: {
						y: { beginAtZero: true }
					}
				}
			});
			return chartInstance;
		}

		function updateSummary(barangay, stats) {
			document.getElementById('summaryBarangay').textContent = barangay ? `Barangay: ${barangay}` : 'Barangay: —';
			document.getElementById('summaryEvacuees').textContent = stats ? stats.total_evacuees.toLocaleString() : '0';
			document.getElementById('summaryFamilies').textContent = stats ? stats.total_families.toLocaleString() : '0';
			document.getElementById('summarySolos').textContent = stats ? stats.total_solos.toLocaleString() : '0';
			const notice = document.getElementById('dataNotice');
			if (!stats || (stats.total_evacuees === 0 && stats.total_families === 0 && stats.total_solos === 0)) {
				notice.textContent = 'No evacuation records found for this barangay. The chart shows zero values.';
			} else {
				notice.textContent = 'Aggregated totals based on evacuation_record_table.';
			}
		}

		function updateChart(stats) {
			const chart = initChart();
			const data = stats ? [stats.total_evacuees, stats.total_families, stats.total_solos] : [0, 0, 0];
			chart.data.datasets[0].data = data;
			chart.update();
		}

		async function handleBarangayChange(barangay) {
			const stats = barangay ? statsByBarangay[barangay.toLowerCase()] : null;
			updateSummary(barangay, stats || null);
			updateChart(stats || null);
			await updateMapForBarangay(barangay);
		}

		document.addEventListener('DOMContentLoaded', () => {
			initMap();
			initChart();
			const select = document.getElementById('barangaySelect');
			select.addEventListener('change', (event) => {
				handleBarangayChange(event.target.value);
			});
			if (defaultBarangay) {
				select.value = defaultBarangay;
				handleBarangayChange(defaultBarangay);
			}
		});
	</script>
</body>

</html>