<?php
include '../../../database/session.php';
include '../layout/head_links.php';

// Modified query for inventory
$query = "SELECT resource_name, quantity
          FROM resource_allocation_table";
$result = mysqli_query($conn, $query);

if (!$result) {
	die("Query failed: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<title>Resource Inventory Management</title>
	<style>
		.stock-status {
			font-weight: bold;
		}

		.in-stock {
			color: #28a745;
		}

		.low-stock {
			color: #ffc107;
		}

		.out-of-stock {
			color: #dc3545;
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
							<i class="bi bi-boxes fs-2 text-primary"></i>
							<h3 class="mb-0">Resource Inventory</h3>
						</div>

						<div class="col-sm-6">
							<ol class="breadcrumb float-sm-end">
								<li class="breadcrumb-item"><a href="#">Home</a></li>
								<li class="breadcrumb-item active" aria-current="page">Inventory Management</li>
							</ol>
						</div>
					</div>
				</div>
			</div>

			<div class="container mt-0"></div>

			<div class="content">
				<div class="row">
					<div class="col-md-12">
						<div class="card">
							<div class="card-header d-flex align-items-center">
								<input type="text" id="searchBox" class="form-control me-2" placeholder="Search resources..." style="max-width: 300px;">
								<button type="button" class="btn btn-primary btn-sm ms-auto" data-bs-toggle="modal" data-bs-target="#addInventoryModal">
									<i class="fas fa-plus-circle"></i> Add New Resource
								</button>
							</div>

							<div class="card-body">
								<div class="table-responsive">
									<div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
										<table id="inventoryTable" class="table table-sm">
											<thead class="table-success" style="position: sticky; top: 0; z-index: 1;">
												<tr>
													<th> No.</th>
													<th><i class="bi bi-box-seam"></i> Resource Name</th>
													<th><i class="bi bi-stack"></i> Quantity</th>
													<th><i class="bi bi-gear-fill"></i> Action</th>
												</tr>
											</thead>
											<tbody>
												<?php
												$counter = 1;
												if (mysqli_num_rows($result) > 0) {
													while ($row = mysqli_fetch_assoc($result)) {
														$status_class = '';
														if ($row['quantity'] >= 50) {
															$status = 'In Stock';
															$status_class = 'in-stock';
														} elseif ($row['quantity'] > 0 && $row['quantity'] < 50) {
															$status = 'Low Stock';
															$status_class = 'low-stock';
														} else {
															$status = 'Out of Stock';
															$status_class = 'out-of-stock';
														}

														// $exp_date = $row['expiration_date'] ? date('M d, Y', strtotime($row['expiration_date'])) : 'N/A';
												?>
														<tr>
															<td><?= $counter++ ?></td>
															<td><?= htmlspecialchars($row['resource_name']) ?></td>
															<td><?= htmlspecialchars($row['quantity']) ?></td>
															<td>
																<a href="#" class="btn btn-outline-primary btn-sm edit-btn"
																	data-resource="<?= htmlspecialchars($row['resource_name']) ?>"
																	data-quantity="<?= htmlspecialchars($row['quantity']) ?>"
																	data-bs-toggle="modal" data-bs-target="#editInventoryModal">
																	<i class="bi bi-pencil-square"></i> Edit
																</a>

																<a href="#" class="btn btn-outline-danger btn-sm delete-btn"
																	data-resource="<?= htmlspecialchars($row['resource_name']) ?>"
																	data-bs-toggle="modal" data-bs-target="#deleteInventoryModal">
																	<i class="bi bi-trash"></i> Delete
																</a>
															</td>
														</tr>
												<?php }
												} else {
													echo '<tr><td colspan="7" class="text-center">No inventory records found.</td></tr>';
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
			</div>
		</main>

		<?php include '../layout/footer.php';
		include '../modal/inventory_modal.php';
		?>
	</div>

	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script>
		$(document).ready(function() {
			$("#searchBox").on("keyup", function() {
				var searchTerm = $(this).val().toLowerCase().trim();
				$("#inventoryTable tbody tr").each(function() {
					var rowText = $(this).text().toLowerCase();
					$(this).toggle(rowText.includes(searchTerm));
				});
			});
		});
	</script>

</body>

</html>