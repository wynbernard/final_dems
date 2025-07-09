<?php
include '../../../database/session.php';
include '../layout/head_links.php';

// Query to fetch all logs and evacuation registration data
$query = "SELECT 
				evac_reg_table.*,
				pre_reg_table.f_name,
				pre_reg_table.l_name,
				logs_table.date_time,
				room_table.room_name,
				evac_loc_table.name,
				logs_table.status
			FROM evac_reg_table
			LEFT JOIN logs_table 
			ON evac_reg_table.evac_reg_id = logs_table.evac_reg_id
			LEFT JOIN pre_reg_table 
			ON evac_reg_table.pre_reg_id = pre_reg_table.pre_reg_id
			LEFT JOIN room_table 
			ON evac_reg_table.room_id = room_table.room_id
			LEFT JOIN evac_loc_table
			ON evac_reg_table.evac_loc_id = evac_loc_table.evac_loc_id
			ORDER BY logs_table.date_time DESC"; // Order by date_time in descending order

$result = mysqli_query($conn, $query);

if (!$result) {
	die("Query failed: " . mysqli_error($conn)); // Debugging for SQL errors
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<title>Log Management</title>
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
							<i class="bi bi-journal-text fs-2 text-primary"></i>
							<h3 class="mb-0">Logs Record</h3>
						</div>

						<div class="col-sm-6">
							<ol class="breadcrumb float-sm-end">
								<li class="breadcrumb-item"><a href="#">Home</a></li>
								<li class="breadcrumb-item active" aria-current="page">Log Records</li>
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
								<input type="text" id="searchBox" class="form-control me-2" placeholder="Search....." style="max-width: 300px;">
								<!-- <button type="button" class="btn btn-primary btn-sm ms-auto" data-bs-toggle="modal" data-bs-target="#addLogModal">
									<i class="fas fa-plus-circle"></i> Add Log
								</button> -->
							</div>

							<div class="card-body">
								<div class="table-responsive log-table-scroll" style="max-height: 400px; overflow-y: auto;">
									<table id="logTable" class="table table-sm">
										<thead class="table-success">
											<tr>
												<th> No.</th>
												<th><i class="bi bi-person-fill"></i> Name</th>
												<th><i class="bi bi-geo-alt-fill"></i> Location</th>
												<th><i class="bi bi-door-closed-fill"></i> Room Name</th>
												<th><i class="bi bi-calendar-check-fill"></i> Date & Time</th>
												<th><i class="bi bi-info-circle-fill"></i> Status</th>

											</tr>
										</thead>
										<tbody>
											<?php
											$counter = 1;
											if (mysqli_num_rows($result) > 0) {
												while ($row = mysqli_fetch_assoc($result)): $formattedDate = date('F j, Y, g:i A', strtotime($row['date_time']));
											?>
													<tr>
														<td class="cell-number"><?php echo $counter++; ?></td>
														<td class="cell-name"><?php echo htmlspecialchars($row['f_name'] . " " . $row['l_name']); ?></td>
														<td class="cell-location"><?php echo htmlspecialchars($row['name'] ?? 'N/A'); ?></td>
														<td class="cell-room"><?php echo htmlspecialchars($row['room_name'] ?? 'N/A'); ?></td>
														<td class="cell-date"><?php echo htmlspecialchars($formattedDate ?? 'N/A'); ?></td>
														<td class="cell-status"><?php echo htmlspecialchars($row['status'] ?? 'N/A'); ?></td>

													</tr>
											<?php endwhile;
											} else {
												echo "<tr><td colspan='8' class='text-center'>No records found.</td></tr>";
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
		// include '../modal/modal_log.php'; Assuming modal file is updated for log management
		?>
	</div>
	<script src="../scripts/admin_script/idps_log.js"></script>
</body>

</html>