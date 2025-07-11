 <?php
	include '../../../database/session.php';
	include '../layout/head_links.php';
	$query = "
SELECT 
  pre_reg_table.*,
  ast.district AS solo_district,
  ast.city_municipality AS solo_city,
  ast.province AS solo_province,
  ast.region AS solo_region,
  bm2.barangay_name AS solo_barangay,

  ft.district AS family_district,
  ft.city_municipality AS family_city,
  ft.province AS family_province,
  ft.region AS family_region,
  bm1.barangay_name AS family_barangay


FROM pre_reg_table
LEFT JOIN solo_address_table AS ast ON pre_reg_table.solo_address_id = ast.solo_address_id
LEFT JOIN barangay_manegement_table AS bm2 ON ast.barangay_id = bm2.barangay_id
LEFT JOIN family_table AS ft ON pre_reg_table.family_id = ft.family_id
LEFT JOIN barangay_manegement_table AS bm1 ON ft.barangay_id = bm1.barangay_id
";


	$result = mysqli_query($conn, $query);
	if (!$result) {
		die("Query failed: " . mysqli_error($conn)); // Debugging for SQL errors
	}
	?>
 <!DOCTYPE html>
 <html lang="en">

 <head>
 	<title>Pre-Registration Management</title>
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
 							<h3 class="mb-0">Pre Registration</h3>
 						</div>
 						<div class="col-sm-6">
 							<ol class="breadcrumb float-sm-end">
 								<li class="breadcrumb-item"><a href="#">Home</a></li>
 								<li class="breadcrumb-item active" aria-current="page">Pre-Registration Records</li>
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
 							<div class="card-header d-flex align-items-center flex-wrap gap-2">
 								<!-- Search Box -->
 								<input type="text" id="searchBox" class="form-control me-auto" placeholder="Search..." style="max-width: 300px;">
 								<?php
									// Count solo
									$soloQuery = "SELECT COUNT(*) AS solo_count FROM pre_reg_table WHERE registered_as = 'Solo'";
									$soloResult = mysqli_query($conn, $soloQuery);
									$soloCount = ($soloResult && mysqli_num_rows($soloResult) > 0) ? mysqli_fetch_assoc($soloResult)['solo_count'] : 0;

									$totalQuery = "SELECT COUNT(*) AS total_count FROM pre_reg_table;";
									$totalResult = mysqli_query($conn, $totalQuery);
									$totalCount = ($totalResult && mysqli_num_rows($totalResult) > 0) ? mysqli_fetch_assoc($totalResult)['total_count'] : 0;

									// Count family
									$familyQuery = "SELECT COUNT(*) AS family_count FROM pre_reg_table WHERE relation_to_family = 'Head of Family' AND Registered_as = 'Family'";
									$familyResult = mysqli_query($conn, $familyQuery);
									$familyCount = ($familyResult && mysqli_num_rows($familyResult) > 0) ? mysqli_fetch_assoc($familyResult)['family_count'] : 0;
									?>
 								<!-- Display counts -->
 								<div class="d-flex align-items-center gap-2">
 									<span class="badge bg-primary">Solo: <?= $soloCount ?></span>
 									<span class="badge bg-success">Family: <?= $familyCount ?></span>
 									<span class="badge bg-danger">Total Individuals: <?= $totalCount ?></span>
 								</div>
 							</div>


 							<div class="card-body">
 								<div class="table-responsive">
 									<div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
 										<table id="preRegTable" class="table table-sm">
 											<thead class="table-success" style="position: sticky; top: 0; z-index: 1; background: #343a40;">
 												<tr>
 													<th> No.</th>
 													<th><i class="bi bi-image"></i> Profile Pic</th>
 													<th><i class="bi bi-person-fill"></i> Full Name</th>
 													<th><i class="bi bi-house-door-fill"></i> Address</th>
 													<th><i class="bi bi-gear-fill"></i> Action</th>

 												</tr>
 											</thead>
 											<tbody>
 												<?php
													$counter = 1;
													if (mysqli_num_rows($result) > 0) {
														while ($row = mysqli_fetch_assoc($result)):
															$age = date_diff(date_create($row['date_of_birth']), date_create('now'))->y;

															// Use solo address if available, otherwise use family address
															$barangayName = $row['solo_barangay'] ?? $row['family_barangay'];
															$district     = $row['solo_district'] ?? $row['family_district'];
															$city         = $row['solo_city'] ?? $row['family_city'];
															$province     = $row['solo_province'] ?? $row['family_province'];
															$region       = $row['solo_region'] ?? $row['family_region'];
															$profilePic = !empty($row['profile_pic']) ? '../uploads/' . htmlspecialchars($row['profile_pic']) : '../../../dist/assets/img/user2-160x160.jpg';

													?>
 														<tr>
 															<td><?= $counter++; ?>.</td>
 															<td>
 																<img src="<?= $profilePic; ?>" alt="Profile Picture" class="img-fluid rounded-circle border" style="width:36px; height:36px; object-fit:cover;">
 															</td>
 															<td><?= htmlspecialchars($row['f_name'] . " " . $row['m_name'] . " " . $row['l_name']); ?></td>
 															<td>
 																<?= htmlspecialchars($barangayName) . ", " .
																		htmlspecialchars($district) . ", " .
																		htmlspecialchars($city) . ", " .
																		htmlspecialchars($province) . ", " .
																		htmlspecialchars($region); ?>
 															</td>
 															<td>
 																<button
 																	class="btn btn-sm btn-outline-info view-details-btn shadow"
 																	data-id="<?= $row['pre_reg_id']; ?>"
 																	data-name="<?= htmlspecialchars($row['f_name'] . ' ' . $row['m_name'] . ' ' . $row['l_name']); ?>"
 																	data-gender="<?= htmlspecialchars($row['gender']); ?>"
 																	data-contact="<?= htmlspecialchars($row['contact_no']); ?>"
 																	data-dob="<?= htmlspecialchars($row['date_of_birth']); ?>"
 																	data-age="<?= $age; ?>"
 																	data-address="<?= htmlspecialchars($barangayName . ', ' . $district . ', ' . $city . ', ' . $province . ', ' . $region); ?>">
 																	<i class="fas fa-eye me-1"></i> View
 																</button>
 															</td>
 														</tr>
 												<?php endwhile;
													} else {
														echo "<tr><td colspan='5' class='text-center'>No pre-registration records found.</td></tr>";
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
			include '../modal/idps_management/pre_reg.php'; // Update this modal file accordingly
			?>
 	</div>
 	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

 	<!-- Search Script -->
 	<script>
 		$(document).ready(function() {
 			$("#searchBox").on("keyup", function() {
 				var searchTerm = $(this).val().toLowerCase().trim();

 				$("#preRegTable tbody tr").each(function() {
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

 </body>

 </html>