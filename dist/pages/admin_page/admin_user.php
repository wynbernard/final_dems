<?php
include '../../../database/session.php';
include '../layout/head_links.php';

$currentUser = $_SESSION['username'];

// Get all admins except current user with their assigned locations
$query = "SELECT * , al.name AS assigned_locations
          FROM admin_table a
          LEFT JOIN evac_loc_table al ON a.evac_loc_id = al.evac_loc_id 
          WHERE a.username != ? 
          GROUP BY a.admin_id
          ORDER BY a.f_name, a.l_name";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $currentUser);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<title>Admin Management</title>
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
							<i class="bi bi-person-gear fs-2 text-primary"></i>
							<h3 class="mb-0">Admin User's</h3>
						</div>
						<div class="col-sm-6">
							<ol class="breadcrumb float-sm-end">
								<li class="breadcrumb-item"><a href="../dashboard/">Home</a></li>
								<li class="breadcrumb-item active" aria-current="page">Admin Users</li>
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
								<input type="text" id="searchBox" class="form-control me-2" placeholder="Search....." style="max-width: 300px;">
								<button type="button" class="btn btn-primary btn-sm ms-auto" data-bs-toggle="modal" data-bs-target="#addAdminModal">
									<i class="fas fa-user-plus"></i> Add Admin
								</button>
							</div>

							<div class="card-body">
								<div class="table-responsive">
									<table id="adminTable" class="searchable-table table table-sm table-hover">
										<thead class="table-success sticky-header">
											<tr>
												<th>No.</th>
												<th><i class="bi bi-person-fill"></i> Name</th>
												<th><i class="bi bi-briefcase-fill"></i> Position</th>
												<th><i class="bi bi-person-lines-fill"></i> Username</th>
												<th><i class="bi bi-geo-alt-fill"></i> Assigned Locations</th>
												<th class="justify-content-center text-center"><i class="bi bi-gear-fill"></i> Actions</th>

											</tr>
										</thead>
										<tbody>
											<?php
											$counter = 1;
											while ($admin = mysqli_fetch_assoc($result)):
												// Get assigned locations as a comma-separated string
												$locations = !empty($admin['assigned_locations']) ? $admin['assigned_locations'] : '';
											?>
												<tr>
													<td class="align-middle px-2 py-1" style="font-size: 0.85rem;"><?php echo $counter++; ?>.</td>
													<td class="align-middle px-2 py-1" style="font-size: 0.85rem;"><?php echo htmlspecialchars($admin['f_name'] . ' ' . $admin['l_name']); ?></td>
													<td class="align-middle px-2 py-1" style="font-size: 0.85rem; max-width: 120px;">
														<div style="max-width: 120px; overflow-x: auto; white-space: nowrap;">
															<?php echo htmlspecialchars($admin['role']); ?>
														</div>
													</td>
													<td class="align-middle px-2 py-1" style="font-size: 0.85rem; max-width: 120px;">
														<div style="max-width: 120px; overflow-x: auto; white-space: nowrap; text-overflow: ellipsis;">
															<span style="display: inline-block; max-width: 100%; overflow: hidden; text-overflow: ellipsis; vertical-align: bottom;">
																<?php echo htmlspecialchars($admin['username']); ?>
															</span>
														</div>
													</td>
													<td class="align-middle px-2 py-1" style="font-size: 0.85rem; max-width: 140px;">
														<div style="max-width: 140px; overflow-x: auto; white-space: nowrap; text-overflow: ellipsis;">
															<span style="display: inline-block; max-width: 100%; overflow: hidden; text-overflow: ellipsis; vertical-align: bottom;">
																<?php if (!empty($locations)): ?>
																	<?php echo htmlspecialchars($admin['assigned_locations']); ?>
																<?php else: ?>
																	<span class="no-locations">None assigned</span>
																<?php endif; ?>
															</span>
														</div>
													</td>
													<td class="align-middle px-2 py-1" style="font-size: 0.85rem;">
														<div class="d-flex justify-content-center flex-wrap gap-1">
															<!-- Edit Button -->
															<button type="button" class="btn btn-outline-success edit-btn btn-sm shadow"
																style="min-width:50px;"
																data-id="<?php echo $admin['admin_id']; ?>"
																data-username="<?php echo htmlspecialchars($admin['username']); ?>"
																data-password="<?php echo htmlspecialchars($admin['password']); ?>"
																data-fname="<?php echo htmlspecialchars($admin['f_name']); ?>"
																data-lname="<?php echo htmlspecialchars($admin['l_name']); ?>"
																data-role="<?php echo htmlspecialchars($admin['role']); ?>"
																data-bs-toggle="modal" data-bs-target="#editAdminModal">
																<i class="fas fa-edit me-1"></i> Edit
															</button>

															<!-- Assign Location Button -->
															<button type="button"
																class="btn btn-outline-primary assign-location-btn shadow"
																style="min-width:50px;"
																data-id="<?php echo $admin['admin_id']; ?>"
																data-name="<?php echo htmlspecialchars($admin['f_name'] . ' ' . $admin['l_name']); ?>"
																data-location-id="<?php echo htmlspecialchars($admin['evac_loc_id'] ?? ''); ?>"
																data-location-lat="<?php echo htmlspecialchars($admin['latitude'] ?? ''); ?>"
																data-location-lng="<?php echo htmlspecialchars($admin['longitude'] ?? ''); ?>"
																data-bs-toggle="modal"
																data-bs-target="#locationAssignmentModal">
																<i class="fas fa-map-marker-alt me-1"></i> Locations
															</button>

															<!-- Delete Button -->
															<button type="button" class="btn btn-outline-danger btn-sm delete-btn shadow"
																style="min-width:50px;"
																data-id="<?php echo $admin['admin_id']; ?>"
																data-name="<?php echo htmlspecialchars($admin['f_name'] . ' ' . $admin['l_name']); ?>"
																data-bs-toggle="modal" data-bs-target="#deleteAdminModal">
																<i class="fas fa-trash me-1"></i> Delete
															</button>
														</div>

													</td>
												</tr>
											<?php endwhile; ?>
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

	<?php
	include '../modal/admin_user_modal.php';
	?>
	<script src="../scripts/scripts.js"></script>
	<script src="../scripts/admin_script/admin_user.js"></script>
	<style>
		.table-responsive {
			max-height: 400px;
			overflow-y: auto;
		}

		#adminTable thead th {
			position: sticky;
			top: 0;
			z-index: 10;
			background: #d1e7dd;
			/* Match your table-success bg color */
		}
	</style>

</body>


</html>