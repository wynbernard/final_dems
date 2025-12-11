<?php
include '../../../database/session.php';
include '../layout/head_links.php';

// Fetch activity log entries
$query = "SELECT *
FROM activity_log_table
ORDER BY activity_log_id DESC";
$result = $conn->query($query);

if (!$result) {
	error_log("Activity log query failed: " . $conn->error);
	die("Query failed. Please contact administrator."); // Secure error message
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<title>Activity Log</title>
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
							<h3 class="mb-0">Activity Log</h3>
						</div>
						<div class="col-sm-6">
							<ol class="breadcrumb float-sm-end">
								<li class="breadcrumb-item"><a href="../dashboard/">Home</a></li>
								<li class="breadcrumb-item active" aria-current="page">Activity Log</li>
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
									<table id="activityLogTable" class="searchable-table table table-sm table-hover">
										<thead class="table-info sticky-header">
											<tr>
												<th>#</th>
												<th>Admin Name</th>
												<th>Role</th>
												<th>Description</th>
												<th>Action</th>
												<th>Date</th>
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
														<?php echo htmlspecialchars($row['admin_name']); ?>
													</td>
													<td class="align-middle px-2 py-1" style="font-size: 0.85rem;">
														<?php echo htmlspecialchars($row['role']); ?>
													</td>
													<td class="align-middle px-2 py-1" style="font-size: 0.9rem;">
														<?php echo htmlspecialchars($row['description']); ?>
													</td>
													<td class="align-middle px-2 py-1" style="font-size: 0.9rem;">
														<?php echo htmlspecialchars($row['action']); ?>
													</td>
													<td class="align-middle px-2 py-1" style="font-size: 0.85rem;">
														<?php echo date('M d, Y h:i A', strtotime($row['created'])); ?>
													</td>
												</tr>
											<?php 
												endwhile;
											} else {
												echo '<tr><td colspan="6" class="text-center">No activity log records found.</td></tr>';
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
		// Simple search filter for activity log table
		document.getElementById("searchBox").addEventListener("keyup", function() {
			let filter = this.value.toLowerCase();
			let rows = document.querySelectorAll("#activityLogTable tbody tr");
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

		#activityLogTable thead th {
			position: sticky;
			top: 0;
			z-index: 10;
			background: #cff4fc;
		}
	</style>

</body>

</html>