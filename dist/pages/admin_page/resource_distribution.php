<?php
include '../../../database/session.php';
include '../layout/head_links.php';

// Modified query for resource distribution
$query = "SELECT *
          FROM resource_distribution_table d
          LEFT JOIN evac_reg_table i ON d.evac_reg_id = i.evac_reg_id
		  LEFT JOIN pre_reg_table p ON i.pre_reg_id = p.pre_reg_id
          ORDER BY d.date_time DESC";
$result = mysqli_query($conn, $query);

if (!$result) {
	die("Query failed: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<title>Resource Distribution Management</title>
	<style>
		.distribution-info {
			font-size: 0.9rem;
		}

		.action-btns {
			white-space: nowrap;
		}
	</style>
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
							<i class="bi bi-box-seam fs-2 text-primary"></i>
							<h3 class="mb-0">Resource Distribution Records</h3>
						</div>

						<div class="col-sm-6">
							<ol class="breadcrumb float-sm-end">
								<li class="breadcrumb-item"><a href="#">Home</a></li>
								<li class="breadcrumb-item active" aria-current="page">Distribution Management</li>
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
								<input type="text" id="searchBox" class="form-control me-2" placeholder="Search distributions..." style="max-width: 300px;">
								<button type="button" class="btn btn-primary btn-sm ms-auto" data-bs-toggle="modal" data-bs-target="#addDistributionModal">
									<i class="fas fa-plus-circle"></i> New Distribution
								</button>
							</div>

							<div class="card-body">
								<div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
									<table id="distributionTable" class="table table-sm">
										<thead class="table-success" style="position: sticky; top: 0; z-index: 1;">
											<tr>
												<th>No.</th>
												<th><i class="bi bi-person-fill"></i> IDP Name</th>
												<th><i class="bi bi-box-seam"></i> Resource</th>
												<th><i class="bi bi-stack"></i> Quantity</th>
												<th><i class="bi bi-calendar-check-fill"></i> Distribution Date</th>
												<th><i class="bi bi-gear-fill"></i> Actions</th>

											</tr>
										</thead>
										<tbody>
											<?php
											$counter = 1;
											if (mysqli_num_rows($result) > 0) {
												while ($row = mysqli_fetch_assoc($result)) {
													$distribution_date = date('M d, Y H:i', strtotime($row['date_time']));
											?>
													<tr>
														<td><?= $counter++ ?></td>
														<td><?= htmlspecialchars($row['f_name'] . ' ' . $row['l_name']) ?></td>
														<td><?= htmlspecialchars($row['resource_name']) ?></td>
														<td><?= htmlspecialchars($row['quantity']) ?></td>
														<td class="distribution-info"><?= $distribution_date ?></td>
														<td class="action-btns">
															<button class="btn btn-sm btn-info view-distribution" data-id="<?= $row['distribution_id'] ?>">
																<i class="fas fa-eye"></i>
															</button>
															<button class="btn btn-sm btn-warning edit-distribution" data-id="<?= $row['distribution_id'] ?>">
																<i class="fas fa-edit"></i>
															</button>
														</td>
													</tr>
											<?php }
											} else {
												echo '<tr><td colspan="7" class="text-center">No distribution records found.</td></tr>';
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

	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script>
		$(document).ready(function() {
			// Search functionality
			$("#searchBox").on("keyup", function() {
				var searchTerm = $(this).val().toLowerCase().trim();
				$("#distributionTable tbody tr").each(function() {
					const rowText = $(this).text().toLowerCase();
					$(this).toggle(rowText.includes(searchTerm));
				});
			});

			// View distribution details
			$('.view-distribution').click(function() {
				const distributionId = $(this).data('id');
				window.location.href = `view_distribution.php?id=${distributionId}`;
			});

			// Edit distribution record
			$('.edit-distribution').click(function() {
				const distributionId = $(this).data('id');
				window.location.href = `edit_distribution.php?id=${distributionId}`;
			});
		});
	</script>

</body>

</html>