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
							</div>

							<div class="card-body">
								<div class="table-responsive">
									<table id="disasterTable" class="table table-sm">
										<thead class="table-success">
											<tr>
											<tr>
												<th> No.</th>
												<th><i class="bi bi-exclamation-triangle-fill"></i> Disaster Type</th>
												<th><i class="bi bi-bar-chart-fill"></i> Severity Level</th>
												<th><i class="bi bi-calendar-event-fill"></i> Date</th>
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
														<td class="cell-severity"><?php echo htmlspecialchars($disaster['level']); ?></td>
														<td class="cell-date"><?php echo htmlspecialchars($disaster['date']); ?></td>

														<td>
															<a href="#" class="btn btn-outline-success btn-sm edit-btn"
																data-id="<?php echo $disaster['disaster_id']; ?>"
																data-type="<?php echo htmlspecialchars($disaster['disaster_name']); ?>"
																data-level="<?php echo htmlspecialchars($disaster['level']); ?>"
																data-date="<?php echo htmlspecialchars($disaster['date']); ?>"
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