<?php
include '../../../database/session.php';
include '../layout/head_links.php';

// Fetch predictive forecast results from barangay forecasts
$query = "SELECT `brgy_forecast_id`, `date`, `barangay_name`, `period`, `scale_range`, `forecast`, `lower_bound`, `upper_bound`, `created_at` FROM `brgy_forecasts` ORDER BY `date` DESC, `barangay_name`";
$result = $conn->query($query);

if (!$result) {
	error_log("Predictive forecast query failed: " . $conn->error);
	die("Query failed. Please contact administrator."); // Secure error message
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<title>Predictive Forecast Reports</title>
	<style>
		.location-badge {
			margin-right: 4px;
			margin-bottom: 4px;
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
					<div class="row">
						<div class="col-sm-6 d-flex align-items-center gap-2">
							<i class="bi bi-bar-chart-fill fs-2 text-success"></i>
							<h3 class="mb-0">Predictive Forecast</h3>
						</div>
						<div class="col-sm-6">
							<ol class="breadcrumb float-sm-end">
								<li class="breadcrumb-item"><a href="../dashboard/">Home</a></li>
								<li class="breadcrumb-item active" aria-current="page">Forecast Reports</li>
							</ol>
						</div>
					</div>
				</div>
			</div>

			<div class="content">
				<div class="row">
					<div class="col-md-12">
						<div class="card">
							<div class="card-header d-flex align-items-center">
								<input type="text" id="searchBox" class="form-control me-2" placeholder="Search location..." style="max-width: 300px;">
							</div>

							<div class="card-body">
								<div class="table-responsive">
									<table id="forecastTable" class="searchable-table table table-sm table-hover">
										<thead class="table-info sticky-header">
											<tr>
												<th>No.</th>
												<th><i class="bi bi-geo-alt-fill"></i> Barangay</th>
												<th><i class="bi bi-calendar-event"></i> Date</th>
												<th><i class="bi bi-activity"></i> Scale Range</th>
												<th><i class="bi bi-people-fill"></i> Forecast</th>
												<th><i class="bi bi-arrows-fullscreen"></i> CI</th>
												<th><i class="bi bi-calendar-plus"></i> Generated</th>
											</tr>
										</thead>
										<tbody>
											<?php
											$counter = 1;
											if ($result->num_rows > 0) {
												while ($row = $result->fetch_assoc()):
											?>
												<tr>
													<td class="align-middle px-2 py-1" style="font-size: 0.85rem;">
														<?php echo $counter++; ?>.
													</td>
													<td class="align-middle px-2 py-1" style="font-size: 0.85rem;">
														<?php echo htmlspecialchars($row['barangay_name']); ?>
													</td>
													<td class="align-middle px-2 py-1" style="font-size: 0.85rem;">
														<?php echo htmlspecialchars($row['date']); ?>
													</td>
													<td class="align-middle px-2 py-1 text-danger fw-bold" style="font-size: 0.9rem;">
														<?php echo htmlspecialchars($row['scale_range']); ?>
													</td>
													<td class="align-middle px-2 py-1 fw-bold text-primary" style="font-size: 0.9rem;">
														<?php echo number_format($row['forecast'], 2); ?>
													</td>
													<td class="align-middle px-2 py-1 text-success" style="font-size: 0.85rem;">
														<?php echo number_format($row['lower_bound'], 2); ?> – <?php echo number_format($row['upper_bound'], 2); ?>
													</td>
													<td class="align-middle px-2 py-1" style="font-size: 0.8rem;">
														<?php echo htmlspecialchars($row['created_at']); ?>
													</td>
												</tr>
											<?php 
												endwhile;
											} else {
												echo '<tr><td colspan="7" class="text-center">No forecast records found.</td></tr>';
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
	</div>

	<script src="../scripts/scripts.js"></script>
	<script>
		// Simple search filter for forecast table
		document.getElementById("searchBox").addEventListener("keyup", function() {
			let filter = this.value.toLowerCase();
			let rows = document.querySelectorAll("#forecastTable tbody tr");
			rows.forEach(row => {
				let text = row.textContent.toLowerCase();
				row.style.display = text.includes(filter) ? "" : "none";
			});
		});
	</script>

	<style>
		.table-responsive {
			max-height: 400px;
			overflow-y: auto;
		}

		#forecastTable thead th {
			position: sticky;
			top: 0;
			z-index: 10;
			background: #cff4fc;
		}
	</style>

</body>

</html>