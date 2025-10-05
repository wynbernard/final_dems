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
								<button type="button" class="btn btn-info me-md-2 mb-2 mb-md-0" data-bs-toggle="modal" data-bs-target="#idCardModal">
							<i class="fas fa-id-card me-2"></i>View ID Card
						</button>
 								<?php
									// Count solo
									$soloQuery = "SELECT COUNT(*) AS solo_count FROM pre_reg_table WHERE registered_as = 'Solo'";
									$soloResult = mysqli_query($conn, $soloQuery);
									$soloCount = ($soloResult && mysqli_num_rows($soloResult) > 0) ? mysqli_fetch_assoc($soloResult)['solo_count'] : 0;

									$totalQuery = "SELECT COUNT(*) AS total_count FROM pre_reg_table;";
									$totalResult = mysqli_query($conn, $totalQuery);
									$totalCount = ($totalResult && mysqli_num_rows($totalResult) > 0) ? mysqli_fetch_assoc($totalResult)['total_count'] : 0;

									// Count family
									$familyQuery = "SELECT COUNT(*) AS family_count FROM pre_reg_table WHERE registered_as = 'Family' AND relation_to_family = 'Head of Family' AND family_id IS NOT NULL";
									$familyResult = mysqli_query($conn, $familyQuery);
									$familyCount = ($familyResult && mysqli_num_rows($familyResult) > 0) ? mysqli_fetch_assoc($familyResult)['family_count'] : 0;
									?>
 								<!-- Display counts and Registration button -->
 								<div class="d-flex align-items-center gap-2">
 									<span class="badge bg-primary">Solo: <?= $soloCount ?></span>
 									<span class="badge bg-success">Family: <?= $familyCount ?></span>
 									<span class="badge bg-danger">Total Individuals: <?= $totalCount ?></span>
 									<button type="button" class="btn btn-sm btn-success ms-2" id="openRegistrationBtn">
 										<i class="bi bi-person-plus-fill me-1"></i> Registration
 									</button>
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

<!-- Registration Modal with inline form (does not load user_registration.php) -->
<div class="modal fade" id="registrationModal" tabindex="-1" aria-labelledby="registrationModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-centered modal-fullscreen-sm-down">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="registrationModalLabel">Registration Form</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<!-- Inline registration form posts to the same handler as user_registration.php -->
				<form id="inlineRegistrationForm" method="POST" action="../action/action_pre_reg.php" enctype="multipart/form-data">
					<div class="row g-3">
					<div class="row" mt-1>
						<div class="col-md-3">
							<div class="mb-3">
								<label for="f_name" class="form-label">
									First Name <span class="text-danger">*</span>
								</label>
								<input type="text" name="f_name" id="f_name" class="form-control" placeholder="Enter First Name" required>
							</div>
						</div>

						<div class="col-md-3">
							<div class="mb-3">
								<label class="form-label">Middle Name <span class="text-danger">*</span></label>
								<input type="text" name="m_name" id="m_name" class="form-control" placeholder="Enter Middle Name" required>
							</div>
						</div>
						<div class="col-md-3">
							<div class="mb-3">
								<label class="form-label">Last Name <span class="text-danger">*</span></label>
								<input type="text" name="l_name" id="l_name" class="form-control" placeholder="Enter Last Name" required>
								<small id="nameFeedback"></small>
							</div>
						</div>
						<div class="col-md-3">
							<div class="mb-2">
								<label class="form-label">Name Extension</label>
								<select class="form-control" name="name_extension" id="name_extension">
									<option value="" disabled selected>-- Select Extension --</option>
									<option value="">None</option>
									<option value="jr">Jr.</option>
									<option value="sr">Sr.</option>
									<option value="i">I</option>
									<option value="ii">II</option>
									<option value="iii">III</option>
								</select>
							</div>
						</div>
						<script>
							document.addEventListener("DOMContentLoaded", function() {
								const fName = document.getElementById("f_name");
								const mName = document.getElementById("m_name");
								const lName = document.getElementById("l_name");
								const nameExt = document.getElementById("name_extension"); // matches your <select>
								const feedback = document.getElementById("nameFeedback");

								// Add input/change listeners
								[fName, mName, lName, nameExt].forEach(field => {
									field.addEventListener("input", validateName);
									field.addEventListener("change", validateName); // For <select>
								});

								function validateName() {
									const first = fName.value.trim();
									const middle = mName.value.trim();
									const last = lName.value.trim();
									const extension = nameExt.value || ""; // Handles "" if not selected

									if (!first || !middle || !last) {
										feedback.innerHTML = "";
										feedback.className = "";
										[fName, mName, lName, nameExt].forEach(f => f.classList.remove("is-valid", "is-invalid"));
										return;
									}

									checkNameAvailability(first, middle, last, extension);
								}

								function checkNameAvailability(first, middle, last, extension) {
									fetch("../check_validation/name_validation.php", {
											method: "POST",
											headers: {
												"Content-Type": "application/x-www-form-urlencoded",
											},
											body: "f_name=" + encodeURIComponent(first) +
												"&m_name=" + encodeURIComponent(middle) +
												"&l_name=" + encodeURIComponent(last) +
												"&name_ext=" + encodeURIComponent(extension),
										})
										.then((response) => response.text())
										.then((data) => {
											const result = data.trim();

											if (result === "taken") {
												feedback.innerHTML = "Full name already registered.";
												feedback.className = "text-danger";
												[fName, mName, lName, nameExt].forEach(field => {
													field.classList.add("is-invalid");
													field.classList.remove("is-valid");
												});
											} else if (result === "available") {
												feedback.innerHTML = "Name is unique.";
												feedback.className = "text-success";
												[fName, mName, lName, nameExt].forEach(field => {
													field.classList.add("is-valid");
													field.classList.remove("is-invalid");
												});
											} else {
												feedback.innerHTML = "Error checking name.";
												feedback.className = "text-warning";
											}
										})
										.catch((error) => {
											console.error("Error:", error);
											feedback.innerHTML = "Server error.";
											feedback.className = "text-warning";
										});
								}
							});
						</script>
					</div>
					<div class="row">
						<div class="col-md-4">
							<div class="mb-3">
								<label class="form-label">Contact No.<span class="text-danger">*</span></label>
								<input type="number" name="contact_no" id="contact_no" class="form-control" placeholder="Enter Contact No." required pattern="[0-9]{10,15}">
								<small id="contactError" class="text-danger"></small>
							</div>
						</div>
						<div class="col-md-4">
							<div class="mb-3">
								<label class="form-label">Email Address <span class="text-danger">*</span></label>
								<input type="email" name="email" id="email" class="form-control" placeholder="Enter Email" required>
								<small id="emailFeedback"></small>
							</div>
						</div>
						<div class="col-md-4">
							<div class="mb-3">
								<label class="form-label">Highest Education Attainment <span class="text-danger">*</span></label>
								<select name="education_attainment" id="education_attainment" class="form-control" required>
									<option value="" selected disabled>Select Education Attainment</option>
									<option value="No Formal Education">No Formal Education</option>
									<option value="Some Elementary">Some Elementary</option>
									<option value="Completed Elementary">Completed Elementary</option>
									<option value="Some High School">Some High School</option>
									<option value="Completed High School">Completed High School / Secondary</option>
									<option value="Some College">Some College</option>
									<option value="Completed Vocational">Completed Vocational / Technical</option>
									<option value="Associate Degree">Associate Degree</option>
									<option value="Bachelor’s Degree">Bachelor’s Degree</option>
									<option value="Some Graduate Studies">Some Graduate Studies</option>
									<option value="Master’s Degree">Master’s Degree</option>
									<option value="Doctorate Degree">Doctorate Degree (Ph.D., Ed.D., etc.)</option>
									<option value="Prefer Not to Say">Prefer Not to Say</option>
								</select>
								<small id="educationAttainmentFeedback"></small>
						</div>
						</div>
						<div class="col-md-4">
							<div class="mb-3 position-relative">
								<label class="form-label">Password <span class="text-danger">*</span></label>
								<div class="input-group">
									<input type="password" name="password" id="password" class="form-control" placeholder="Enter Password" required onkeyup="validatePassword()">
									<span class="input-group-text" onclick="toggleVisibility('password', this)" style="cursor: pointer;">
										<i class="fa fa-eye-slash"></i>
									</span>
								</div>
								<small id="passwordHelp" class="form-text text-danger mt-1 d-block"></small>
							</div>
						</div>
						<div class="col-md-4">
							<div class="mb-3 position-relative">
								<label class="form-label">Confirm Password <span class="text-danger">*</span></label>
								<div class="input-group">
									<input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Confirm Password" required onkeyup="validatePassword()">
									<span class="input-group-text border-0" onclick="toggleVisibility('confirm_password', this)" style="cursor: pointer;">
										<i class="fa fa-eye-slash"></i>
									</span>
								</div>
								<small id="passwordMatchMessage" class="text-danger mt-1 d-block"></small>
							</div>
						</div>
						<div class="col-md-4">
							<div class="mb-3">
								<label class="form-label">Signature <span class="text-danger">*</span></label>
								<div class="mt-0">
									<input type="file" name="signature_file" id="signature_file" class="form-control" accept="image/*" required onchange="previewSignature(event)">
						</div>
								<div class="mt-2">
									<img id="signaturePreview" src="#" alt="Signature Preview" style="max-width: 200px; display: none; border: 1px solid #ddd; padding: 5px;" />
								</div>
							</div>
						</div>
						<script>
							function previewSignature(event) {
								const input = event.target;
								const preview = document.getElementById('signaturePreview');

								if (input.files && input.files[0]) {
									const reader = new FileReader();

									reader.onload = function(e) {
										preview.src = e.target.result;
										preview.style.display = 'block';
									};

									reader.readAsDataURL(input.files[0]);
								} else {
									preview.src = '#';
									preview.style.display = 'none';
								}
							}
						</script>
						<div class="col-md-4">
							<div class="mb-3">
								<label class="form-label">Date of Birth <span class="text-danger">*</span></label>
								<input type="date" name="dob" id="dob" class="form-control" required>
							</div>
						</div>
						<div class="col-md-4">
							<div class="mb-3">
								<label class="form-label">Place Of Birth <span class="text-danger">*</span></label>
								<input type="text" name="pob" id="pob" class="form-control" placeholder="Enter Place of Birth" required>
							</div>
						</div>
						<div class="col-md-4">
							<div class="mb-3">
								<label class="form-label">Mother Maiden Name <span class="text-danger">*</span></label>
								<input type="text" name="mmn" id="mmn" class="form-control" placeholder="Enter Mother Maiden Name" required>
							</div>
						</div>
						<div class="col-md-4">
							<div class="mb-3">
								<label for="religion" class="form-label">Religion <span class="text-danger">*</span></label>
								<select class="form-select" id="religion" name="religion" required>
									<option value="" disabled selected>Select your religion</option>
									<option value="Roman Catholic">Roman Catholic</option>
									<option value="Iglesia ni Cristo">Iglesia ni Cristo</option>
									<option value="Evangelical">Evangelical (Born Again)</option>
									<option value="Seventh-day Adventist">Seventh-day Adventist</option>
									<option value="Other Christian">Other Christian (e.g., Baptist, Methodist, Pentecostal)</option>
									<option value="Islam">Islam</option>
									<option value="Aglipayan">Aglipayan (Philippine Independent Church)</option>
									<option value="Jehovah's Witnesses">Jehovah's Witnesses</option>
									<option value="Buddhism">Buddhism</option>
									<option value="Hinduism">Hinduism</option>
									<option value="Indigenous Beliefs">Indigenous / Ethnic Beliefs</option>
									<option value="None">None / No Religion</option>
									<option value="Other">Other (please specify)</option>
								</select>
							</div>
						</div>
						<div class="col-md-4">
							<div class="mb-3">
								<label class="form-label">Occupation</label>
								<input type="text" name="occupation" id="occupation" class="form-control" placeholder="Enter Occupation">
							</div>
						</div>
						<div class="col-md-4">
							<div class="mb-3">
								<label class="form-label">Monthly Income</label>
								<!-- Visible input with commas -->
								<input type="text" id="monthly_income_display" class="form-control" placeholder="₱0.00" oninput="formatWithCommas()" required>
								<!-- Hidden number input for form submission -->
								<input type="number" name="monthly_income" id="monthly_income" hidden>
							</div>
						</div>
						<div class="col-md-4">
							<div class="mb-3">
								<label class="form-label">Gender <span class="text-danger">*</span></label>
								<select name="gender" id="gender" class="form-control" required>
									<option value="" disabled selected>-- Select Gender --</option>
								<option value="Male">Male</option>
								<option value="Female">Female</option>
							</select>
						</div>
						</div>
						<div class="col-md-4">
							<div class="mb-3">
								<label class="form-label">Civil Status <span class="text-danger">*</span></label>
								<select name="civil_status" id="civil_status" class="form-control" required>
									<option value="" disabled selected>-- Select Civil Status --</option>
									<option value="single">Single</option>
									<option value="married">Married</option>
									<option value="widowed">Widowed</option>
									<option value="divorced">Divorced</option>
									<option value="separated">Separated</option>
									<option value="annulled">Annulled</option>
									<option value="others">Others</option>
								</select>
							</div>
						</div>
						<div class="col-md-4">
							<div class="mb-3">
								<label class="form-label">Registration Type <span class="text-danger">*</span></label>
								<select name="registration_type" id="registration_type" class="form-control" required>
									<option value="" disabled selected>-- Select Registration Type --</option>
								<option value="Solo">Solo</option>
								<option value="Family">Family</option>
							</select>
							</div>
						</div>
						<div class="row" id="familyMembersSection" style="display:none;">
							<div class="col-12">
								<div class="mb-3">
									<label for="numFamilyMembers" class="form-label">Number of Family Members (excluding head):</label>
									<input type="number" min="1" max="20" class="form-control" id="numFamilyMembers" name="numFamilyMembers">
								</div>
								<div id="familyMembersFields"></div>
							</div>
						</div>

						<div class="col-md-4">
							<div class="mb-3">
								<label class="form-label fw-semibold">Upload Image of ID Card Presented <span class="text-danger">*</span></label>
								<input type="file" name="ic_image" id="ic_image" class="form-control" accept="image/*" required onchange="previewIdCard(event)">
								<div class="mt-2">
									<img id="idCardPreview" src="#" alt="ID Card Preview" style="max-width: 100%; max-height: 200px; display: none; border: 1px solid #ccc; padding: 5px;" />
						</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="mb-3">
								<label for="icp" class="form-label">ID Card Presented <span class="text-danger">*</span></label>
								<select name="icp" id="icp" class="form-select" required onchange="updateIDCardFormat()">
									<option value="" disabled selected>Select ID Card</option>
									<option value="Philippine National ID">Philippine National ID (PhilSys)</option>
									<option value="Passport">Passport</option>
									<option value="Driver's License">Driver’s License (LTO)</option>
									<option value="UMID">UMID (Unified Multi-Purpose ID)</option>
									<option value="SSS ID">SSS ID</option>
									<option value="PRC ID">PRC (Professional Regulation Commission) ID</option>
									<option value="Voter's ID">Voter’s ID / Certificate</option>
									<option value="TIN ID">TIN ID (BIR)</option>
									<option value="PhilHealth ID">PhilHealth ID</option>
								</select>
						</div>
						</div>
						<div class="col-md-4">
							<div class="mb-3">
								<label class="form-label">ID Card Number <span class="text-danger">*</span></label>
								<input type="text" name="icn" id="icn" class="form-control" placeholder="Enter ID Card Number" required>
							</div>
						</div>
						<!-- ID Upload Input -->
						<script>
							function previewIdCard(event) {
								const input = event.target;
								const preview = document.getElementById('idCardPreview');

								if (input.files && input.files[0]) {
									const reader = new FileReader();

									reader.onload = function(e) {
										preview.src = e.target.result;
										preview.style.display = 'block';
									};

									reader.readAsDataURL(input.files[0]);
								} else {
									preview.src = '#';
									preview.style.display = 'none';
								}
							}
						</script>
						<!-- <script src="https://cdn.jsdelivr.net/npm/tesseract.js@2.1.5/dist/tesseract.min.js"></script> -->
						<!-- SweetAlert2 CSS -->
						<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
						<!-- SweetAlert2 JS -->
						<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
						<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
						<!-- <script src="tesseract.js"></script> -->
						<div class="col-md-2">
							<div class="mb-3">
								<label class="form-label">Others</label>
								<div class="form-check mt-2">
									<input type="checkbox" name="beneficiary" id="beneficiary" class="form-check-input">
									<label for="beneficiary" class="form-check-label">4Ps Beneficiary</label>
								</div>
							</div>
						</div>
						<div class="col-md-2">
							<div class="mb-3">
								<label class="form-label" style="color:white;">&nbsp;</label>
								<div class="form-check mt-2">
									<input type="checkbox" name="ip" id="ip" class="form-check-input" onchange="toggleEthnicity()">
									<label for="ip" class="form-check-label">IP's</label>
								</div>
							</div>
						</div>

						<!-- Hidden Ethnicity Input -->
						<div class="col-md-4" id="ethnicityField" style="display: none;">
							<div class="mb-3">
								<label for="ethnicity" class="form-label">Ethnicity</label>
								<input type="text" name="ethnicity" id="ethnicity" class="form-control" placeholder="Enter Ethnicity">
							</div>
						</div>

						<!-- PROFILE PICTURE -->

						<!-- Profile Preview - Fully Centered -->
						<div id="profilePicPreview" class="mt-3 d-none d-flex justify-content-center">
							<div class="d-flex flex-column align-items-center">
								<div class="border rounded-circle overflow-hidden" style="width: 150px; height: 150px; margin-bottom: 30px;">
									<img id="profileImage" src="" alt="Profile Picture" class="img-fluid h-100 w-100 object-fit-cover">
								</div>
							</div>
						</div>

						<!-- Button -->
						<button type="button" class="btn btn-primary" id="selectImageBtn">
							Set Profile Picture
						</button>
						<!-- Hidden file input -->
						<input type="file" id="imageInput" accept="image/*" name="profile_pic" class="d-none">

						<script>
							const selectImageBtn = document.getElementById('selectImageBtn');
							const imageInput = document.getElementById('imageInput');
							const profileImage = document.getElementById('profileImage');
							const profilePicPreview = document.getElementById('profilePicPreview');

							selectImageBtn.addEventListener('click', () => {
								imageInput.click();
							});

							imageInput.addEventListener('change', function() {
								const file = this.files[0];
								if (file && file.type.startsWith('image/')) {
									const reader = new FileReader();
									reader.onload = function(e) {
										profileImage.src = e.target.result;
										profilePicPreview.classList.remove('d-none');
									};
									reader.readAsDataURL(file);
								}
							});
						</script>
					</div>
					<div class="row">
						<!-- Personal Information -->
						<div class="col-12 my-5 position-relative">
							<div class="border-top"></div>
							<span class="position-absolute start-50 translate-middle bg-white px-3 text-muted"
								style="top: -12px; font-size: 0.9rem">
								Address Information
							</span>
						</div>
						<!-- Address Fields -->
						<div class="col-md-4">
							<div class="mb-3">
								<label class="form-label">Region <span class="text-danger">*</span></label>
								<input type="text" name="region" id="region" class="form-control" value="Region VI (Western Visayas)" readonly>
							</div>
						</div>
						<div class="col-md-4">
							<div class="mb-3">
								<label class="form-label">Province <span class="text-danger">*</span></label>
								<input type="text" name="province" id="province" class="form-control" value="Negros Occidental" readonly>
						</div>
						</div>
						<div class="col-md-4">
							<div class="mb-3">
								<label class="form-label">City <span class="text-danger">*</span></label>
								<input type="text" name="city" id="city" class="form-control" value="Bago City" readonly>
							</div>
						</div>
						<div class="col-md-4">
							<div class="mb-3">
								<label class="form-label">District <span class="text-danger">*</span></label>
								<input type="text" name="district" id="district" class="form-control" value="4th district" readonly>
							</div>
						</div>
						<div class="col-md-4">
							<div class="mb-3">
								<label class="form-label">Barangay <span class="text-danger">*</span></label>
								<select name="barangay" id="barangay" class="form-control" required>
									<option value="">Select Barangay</option>
								</select>
							</div>
						</div>
						<div class="col-md-4">
							<div class="mb-3">
								<label class="form-label">House Block Number</label>
								<input type="text" name="block_number" id="block_number" class="form-control" placeholder="Enter Block Number">
							</div>
						</div>
						<div class="col-md-4">
							<div class="mb-3">
								<label class="form-label">Street</label>
								<input type="text" name="street" id="street" class="form-control" placeholder="Enter street">
							</div>
						</div>
						<div class="col-md-4">
							<div class="mb-3">
								<label class="form-label">Subdivision Village</label>
								<input type="text" name="sub_div" id="sub_div" class="form-control" placeholder="Enter Subdivision Village">
							</div>
						</div>
						<div class="col-md-4">
							<div class="mb-3">
								<label class="form-label">Zip Code</label>
								<input type="text" name="zip_code" id="zip_code" value="6101" class="form-control" readonly>
							</div>
						</div>
						<div class="col-md-6">
							<div class="mb-3">
								<label class="form-label">Purok <span class="text-danger">*</span></label>
								<select name="purok" id="purok" class="form-control" required>
									<option value="">Select Purok</option>
								</select>
							</div>
						</div>
					</div>
					<div class="col-12 my-4 position-relative">
						<div class="border-top"></div>
						<span class="position-absolute start-50 translate-middle bg-white px-3 text-muted"
							style="top: -12px; font-size: 0.9rem">
							Pick Up Point Information
						</span>
					</div>
					<div class="row">
						<div class="col-md-6">
							<div class="mb-3">
								<label class="form-label">Pick-up Point Name</label>
								<input type="text" name="pickup_name" id="pickup_name" class="form-control" placeholder="Enter Pick-up Point Name" readonly>
							</div>
						</div>
						<div class="col-md-6">
							<div class="mb-3">
								<label class="form-label">Have Vehicle</label>
								<select name="have_vehicle" id="have_vehicle" class="form-control" onchange="toggleVehicleField()">
									<option value="" selected disabled>-- Select --</option>
									<option value="Yes">Yes</option>
									<option value="No">No</option>
								</select>
							</div>
						</div>
						<div class="col-md-6" id="vehicle_type_field" style="display:none;">
							<div class="mb-3">
								<label class="form-label">What Kind of Vehicle</label>
								<select name="vehicle_type" id="vehicle_type" class="form-control">
									<option value="" selected disabled>-- Select Vehicle Type --</option>
									<option value="Car">Car</option>
									<option value="Motorcycle">Motorcycle</option>
									<option value="Van">Van</option>
									<option value="Truck">Truck</option>
									<option value="Jeepney">Jeepney</option>
									<option value="Tricycle">Tricycle</option>
									<option value="Other">Other</option>
								</select>
							</div>
						</div>
						<script>
							function toggleVehicleField() {
								const haveVehicle = document.getElementById("have_vehicle").value;
								const vehicleField = document.getElementById("vehicle_type_field");
								
								if (haveVehicle === "Yes") {
									vehicleField.style.display = "block";
								} else {
									vehicleField.style.display = "none";
								}
							}
						</script>
						<div class="col-md-6">
							<div class="mb-3">
								<label class="form-label">Intend to Go to Evacuation Center</label>
								<select name="intend_evac" id="intend_evac" class="form-control" onchange="toggleWhereToGo()">
									<option value="" selected disabled>-- Select --</option>
									<option value="Yes">Yes</option>
									<option value="No">No</option>
									<option value="No">Undecided</option>
								</select>
						</div>
						</div>
						<div class="col-md-6" id="where_to_go_field" style="display:none;">
							<div class="mb-3">
								<label class="form-label">If No, Where Will You Go?</label>
								<select name="where_to_go" id="where_to_go" class="form-control">
									<option value="" selected disabled>-- Select Option --</option>
									<option value="Parinti">Parinti</option>
									<option value="Abyan">Abyan</option>
									<option value="Iban pa">Iban pa</option>
								</select>
							</div>
						</div>
						<script>
						function toggleWhereToGo() {
							const intendEvac = document.getElementById("intend_evac").value;
							const whereField = document.getElementById("where_to_go_field");

							if (intendEvac === "No") {
								whereField.style.display = "block";
							} else {
								whereField.style.display = "none";
								document.getElementById("where_to_go").value = ""; // clear value if hidden
							}
						}
						</script>
						<div class="col-md-6">
							<div class="mb-3">
								<label class="form-label">Have Special Needs</label>
								<select name="have_special_needs" id="have_special_needs" class="form-control" onchange="toggleSpecialNeeds()">
									<option value="" selected disabled>-- Select --</option>
									<option value="Yes">Yes</option>
									<option value="No">No</option>
								</select>
							</div>
						</div>

						<div class="col-md-6" id="special_needs_field" style="display:none;">
							<div class="mb-3">
								<label class="form-label">Please Specify</label>
								<input type="text" name="special_needs" id="special_needs" class="form-control" placeholder="Enter Special Needs (e.g., Wheelchair, Medication, Assistance)">
							</div>
						</div>
						<script>
							function toggleSpecialNeeds() {
								const haveNeeds = document.getElementById("have_special_needs").value;
								const needsField = document.getElementById("special_needs_field");

								if (haveNeeds === "Yes") {
									needsField.style.display = "block";
								} else {
									needsField.style.display = "none";
									document.getElementById("special_needs").value = ""; // clear when hidden
								}
							}
						</script>
					</div>
					<div class="col-12 my-4 position-relative">
						<div class="border-top"></div>
						<span class="position-absolute start-50 translate-middle bg-white px-3 text-muted"
							style="top: -12px; font-size: 0.9rem">
							Account Information
						</span>
					</div>
					<div class="row">
						<div class="col-md-6">
							<div class="mb-3">
								<label class="form-label">Bank/E-Wallet</label>
								<input type="text" name="wallet" id="wallet" class="form-control" placeholder="Enter Bank/E-Wallet">
							</div>
						</div>
						<div class="col-md-6">
							<div class="mb-3">
								<label class="form-label">Account Name</label>
								<input type="text" name="account_name" id="account_name" class="form-control" placeholder="Enter Account Name">
							</div>
						</div>
						<div class="col-md-6">
							<div class="mb-3">
								<label class="form-label">Account Type</label>
								<input type="text" name="account_type" id="account_type" class="form-control" placeholder="Enter Account Type">
							</div>
						</div>
						<div class="col-md-6">
							<div class="mb-3">
								<label class="form-label">Account Number</label>
								<input type="number" name="account_number" id="account_number" class="form-control" placeholder="Enter Account Number">
							</div>
						</div>
					</div>
					<!-- Responsive Button Layout -->
					<div class="d-grid gap-2 d-md-flex">
						<button type="submit" id="submitBtn" class="btn btn-success">Submit</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

<?php
// Get evacuees data for modal
$evacueesQuery = "SELECT 
	prt.pre_reg_id,
	prt.registered_as,
	prt.f_name,
	prt.m_name,
	prt.l_name,
	prt.name_ext,
	prt.contact_no,
	prt.special_needs,
	prt.intend_evacuation,
	qr.code AS qr_code,
	prt.pickup_point_name,
	prt.vehicle_type,
	prt.have_vehicle,
	prt.family_id,
	prt.relation_to_family,
	-- Address information
	CASE 
		WHEN prt.registered_as = 'Solo' THEN CONCAT(sat.purok, ', ', bmt.barangay_name, ', ', sat.city_municipality)
		WHEN prt.registered_as = 'Family' THEN CONCAT(ft.purok, ', ', bmt2.barangay_name, ', ', ft.city_municipality)
		ELSE ''
	END AS full_address,
	-- Calculate family member count dynamically
	(SELECT COUNT(*) FROM pre_reg_table prt2 
	 WHERE prt2.family_id = prt.family_id AND prt2.status = ' ') AS member_count,
	-- Card type for display
	CASE 
		WHEN prt.registered_as = 'Solo' THEN 'SOLO'
		WHEN prt.relation_to_family = 'Head of Family' THEN 'FAMILY HEAD'
		ELSE 'FAMILY MEMBER'
	END AS card_type
FROM pre_reg_table prt
LEFT JOIN solo_address_table sat ON prt.solo_address_id = sat.solo_address_id
LEFT JOIN family_table ft ON prt.family_id = ft.family_id
LEFT JOIN barangay_manegement_table bmt ON sat.barangay_id = bmt.barangay_id
LEFT JOIN barangay_manegement_table bmt2 ON ft.barangay_id = bmt2.barangay_id
LEFT JOIN qr_table qr ON prt.qr_id = qr.qr_id
WHERE prt.status = '' 
AND (prt.registered_as = 'Solo' OR prt.relation_to_family = 'Head of Family')
ORDER BY prt.registered_as, prt.family_id, prt.relation_to_family, prt.l_name, prt.f_name";

$evacueesResult = mysqli_query($conn, $evacueesQuery);

// Additional query to get purok leader details
$purokLeaderQuery = "SELECT `purok_id`, `purok_name`, `barangay_id`, `purok_leader`, `pickup_point_name` FROM `purok_table` WHERE purok_id = purok_id";
$purokLeaderResult = mysqli_query($conn, $purokLeaderQuery);
$evacuees = [];
if ($evacueesResult && mysqli_num_rows($evacueesResult) > 0) {
	while ($row = mysqli_fetch_assoc($evacueesResult)) {
		$evacuees[] = $row;
	}
}

$totalEvacuees = count($evacuees);
$soloCount = count(array_filter($evacuees, function($e) { return $e['registered_as'] === 'Solo'; }));
$familyHeadCount = count(array_filter($evacuees, function($e) { return $e['relation_to_family'] === 'Head of Family'; }));
?>

<!-- ID Card Modal -->
<div class="modal fade" id="idCardModal" tabindex="-1" aria-labelledby="idCardModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-scrollable">
		<div class="modal-content">
			<!-- Modal Header -->
			<div class="modal-header">
				<h5 class="modal-title" id="idCardModalLabel">
					ID Card Layout Preview 
					<span class="badge bg-primary ms-2"><?= $totalEvacuees ?> Cards</span>
					<small class="text-muted ms-2">
						(<?= $soloCount ?> Solo, <?= $familyHeadCount ?> Family Heads)
					</small>
				</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
			</div>

			<!-- Modal Body -->
			<div class="modal-body" id="idCardContent">
				<div class="container-fluid">
					<div class="row g-3">
						<!-- Generate cards for each evacuee -->
						<?php foreach ($evacuees as $index => $evacuee): ?>
                            <div class="col-md-6">
                                    <div class="id-card <?= ($evacuee['registered_as'] === 'Family') ? 'family-card' : 'solo-card' ?>">
                                        <!-- Header -->
                                        <div class="card-header">
                                            <div class="card-title">KANLAON EVACUATION PLAN</div>
                                            <div class="card-subtitle">BAKWIT CARD</div>
                                            <div class="registration-type">
                                                <?= ($evacuee['registered_as'] === 'Family') ? 'FAMILY' : 'INDIVIDUAL' ?>
                                            </div>
                                        </div>

                                        <!-- Main Information Section -->
                                        <div class="form-section">
                                            <table class="form-table">
                                                <tr>
                                                    <td>
                                                        HOUSEHOLD HEAD:
                                                        <span class="form-label-local">(PANGULO SANG PANIMALAY)</span>
                                                    </td>
                                                    <td><?= htmlspecialchars(trim(($evacuee['f_name'] ?? '') . ' ' . ($evacuee['m_name'] ?? '') . ' ' . ($evacuee['l_name'] ?? '') . ' ' . ($evacuee['name_ext'] ?? ''))) ?></td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        NO. OF HOUSEHOLD MEMBER:
                                                        <span class="form-label-local">(KADAMUON/KADAGHANON SA PANIMALAY)</span>
                                                    </td>
                                                    <td>
                                                        <?= ($evacuee['registered_as'] === 'Family') ? htmlspecialchars($evacuee['member_count'] ?? '1') : '1' ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        ADDRESS:
                                                        <span class="form-label-local">(PULOY-AN/PUY-ANAN)</span>
                                                    </td>
                                                    <td><?= htmlspecialchars($evacuee['full_address'] ?: '________________') ?></td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        COLLECTION POINT/PICKUP POINT:
                                                        <span class="form-label-local">(TILIPUNAN PARA SA BAKWIT)</span>
                                                    </td>
                                                    <td><?= htmlspecialchars($evacuee['pickup_point_name'] ?: '________________') ?></td>
                                                <tr>
                                                    <td>
                                                        ASSIGNED EVACUATION CENTER:
                                                        <span class="form-label-local">(GINTALANA NGA EVACUATION CENTER)</span>
                                                    </td>
                                                    <td>________________</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        PHONE NUMBER OF FAMILY LEADER:
                                                        <span class="form-label-local">(NUMERO SA SELPON SANG PANGULO SANG PANIMALAY)</span>
                                                    </td>
                                                    <td><?= htmlspecialchars($evacuee['contact_no'] ?: '________________') ?></td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        PERSONS WITH SPECIAL NEEDS:
                                                        <span class="form-label-local">(MIYEMBRO NGA MAY ESPESYAL NGA PANGINAHANGLANON)</span>
                                                    </td>
                                                    <td><?= htmlspecialchars($evacuee['special_needs'] ?: '________________') ?></td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        STAYING INSIDE EVACUATION CENTER?:
                                                        <span class="form-label-local">(MUSULOD BA MO SA EVACUATION CENTER?)</span>
                                                    </td>
                                                    <td colspan="2">
                                                        <div class="checkbox-group" style="display: flex; align-items: center; gap: 8px; white-space: nowrap;">
                                                            <!-- YES -->
                                                            <div class="checkbox-item" style="display: flex; align-items: center; gap: 5px;">
                                                                <div class="checkbox-box <?= ($evacuee['intend_evacuation'] === 'Yes') ? 'checked' : '' ?>"></div>
                                                                <span>YES (Oo)</span>
                                                            </div>

                                                            <!-- NO -->
                                                            <div class="checkbox-item" style="display: flex; align-items: center; gap: 5px;">
                                                                <div class="checkbox-box <?= ($evacuee['intend_evacuation'] !== 'Yes') ? 'checked' : '' ?>"></div>
                                                                <span>NO (Indi)</span>
                                                            </div>

                                                            <!-- Divider Line -->
                                                            <div style="border-left: 2px solid #000; height: 40px; margin: 0 10px;"></div>

                                                            <!-- QR Code Label & Image -->
                                                            <span class="qr-label">QR CODE:</span>
                                                            <div class="checkbox-item" style="display: flex; align-items: center; gap: 5px;">
                                                                <img class="qr" src="<?= htmlspecialchars('../../../' . ltrim($evacuee['qr_code'] ?? '')) ?>" alt="QR Code" style="width: 28mm; height: 28mm; object-fit: contain; display: block;">
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>

                                            <!-- Control Number Section -->
                                            <!-- <div class="control-number-section">
                                        <table class="control-number-table">
                                            <tr>
                                                <td>CONTROL NUMBER:</td>
                                                <td>
                                                    <div class="control-number-box">
                                                        <?= htmlspecialchars($reg['control_number']) ?> 
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </div> -->
                                        </div>
                                        <!-- Authority Section -->
                                        <div class="authority-section">
                                            <div class="logo-placeholder" style="display:flex;align-items:center;justify-content:center;width:80px;height:80px;border-radius:50%;overflow:hidden;background:#fff;">
                                                <img src="../../../src/images/bago_city.png" alt="LGU Logo" style="width:100%;height:100%;object-fit:cover;" />
                                            </div>

                                            <div class="authority-list">
                                                <div class="authority-item">
                                                    <div class="authority-name">LDRRMO</div>
                                                    <div class="authority-line"></div>
                                                </div>
                                                <div class="authority-item">
                                                    <div class="authority-name">PUNONG BARANGAY</div>
                                                    <div class="authority-line"></div>
                                                </div>
                                                <div class="authority-item">
                                                    <div class="authority-name">PUROK LEADER</div>
                                                    <div class="authority-line"></div>
                                                </div>
                                                <div class="authority-item">
                                                    <div class="authority-name">LOCAL POLICE STATION</div>
                                                    <div class="authority-line"></div>
                                                </div>
                                                <div class="authority-item">
                                                    <div class="authority-name">CDRRMO</div>
                                                    <div class="authority-line">
                                                        <span class="authority-phone">09956112342 / 09177040134</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>

			<!-- Modal Footer -->
			<div class="modal-footer">
				<button class="btn btn-success" id="printCardBtn"><i class="fas fa-print me-1"></i> Print</button>
				<button class="btn btn-danger" id="downloadCardBtn"><i class="fas fa-file-pdf me-1"></i> Download PDF</button>
				<button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
		var openBtn = document.getElementById('openRegistrationBtn');
		var regModalEl = document.getElementById('registrationModal');

		openBtn.addEventListener('click', function() {
				var regModal = new bootstrap.Modal(regModalEl);
				regModal.show();
		});

		// Set max date to today for DOB
		const today = new Date().toISOString().split("T")[0];
		document.getElementById("dob").setAttribute("max", today);

		// Load barangays
		loadBarangays();

		// Email validation
		const emailInput = document.getElementById("email");
		const emailFeedback = document.getElementById("emailFeedback");

		if (emailInput) {
			emailInput.addEventListener("input", validateEmail);
			emailInput.addEventListener("blur", validateEmail);THE
		}

		function validateEmail() {
			const email = emailInput.value.trim();
			
			if (!email) {
				emailFeedback.innerHTML = "";
				emailFeedback.className = "";
				emailInput.classList.remove("is-valid", "is-invalid");
				return;
			}

			// Basic email format validation
			const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
			if (!emailRegex.test(email)) {
				emailFeedback.innerHTML = "Please enter a valid email address.";
				emailFeedback.className = "text-danger";
				emailInput.classList.add("is-invalid");
				emailInput.classList.remove("is-valid");
				return;
			}

			// Check email availability
			checkEmailAvailability(email);
		}

		function checkEmailAvailability(email) {
			fetch("../check_validation/user_email.php", {
					method: "POST",
					headers: {
						"Content-Type": "application/x-www-form-urlencoded",
					},
					body: "email_address=" + encodeURIComponent(email),
				})
				.then((response) => response.text())
				.then((data) => {
					const result = data.trim();

					if (result === "taken") {
						emailFeedback.innerHTML = "This email is already registered.";
						emailFeedback.className = "text-danger";
						emailInput.classList.add("is-invalid");
						emailInput.classList.remove("is-valid");
					} else if (result === "available") {
						emailFeedback.innerHTML = "Email is available.";
						emailFeedback.className = "text-success";
						emailInput.classList.add("is-valid");
						emailInput.classList.remove("is-invalid");
					} else {
						emailFeedback.innerHTML = "Error checking email availability.";
						emailFeedback.className = "text-warning";
					}
				})
				.catch((error) => {
					console.error("Error:", error);
					emailFeedback.innerHTML = "Server error checking email.";
					emailFeedback.className = "text-warning";
				});
		}
});

// Password validation
function validatePassword() {
	const password = document.getElementById('password').value;
	const confirmPassword = document.getElementById('confirm_password').value;
	const passwordHelp = document.getElementById('passwordHelp');
	const passwordMatchMessage = document.getElementById('passwordMatchMessage');

	// Password strength validation
	const minLength = 8;
	const hasUpperCase = /[A-Z]/.test(password);
	const hasLowerCase = /[a-z]/.test(password);
	const hasNumbers = /\d/.test(password);
	const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);

	let messages = [];
	
	if (password.length < minLength) {
		messages.push("At least 8 characters");
	}
	if (!hasUpperCase) {
		messages.push("One uppercase letter");
	}
	if (!hasLowerCase) {
		messages.push("One lowercase letter");
	}
	if (!hasNumbers) {
		messages.push("One number");
	}
	if (!hasSpecialChar) {
		messages.push("One special character");
	}

	if (messages.length > 0) {
		passwordHelp.innerHTML = "Password must contain: " + messages.join(", ");
		passwordHelp.className = "form-text text-danger mt-1 d-block";
	} else {
		passwordHelp.innerHTML = "Password is strong!";
		passwordHelp.className = "form-text text-success mt-1 d-block";
	}

	// Password match validation
	if (confirmPassword && password !== confirmPassword) {
		passwordMatchMessage.innerHTML = "Passwords do not match.";
		passwordMatchMessage.className = "text-danger mt-1 d-block";
	} else if (confirmPassword && password === confirmPassword) {
		passwordMatchMessage.innerHTML = "Passwords match!";
		passwordMatchMessage.className = "text-success mt-1 d-block";
	} else {
		passwordMatchMessage.innerHTML = "";
	}
}

// Toggle password visibility
function toggleVisibility(fieldId, iconElement) {
	const field = document.getElementById(fieldId);
	const icon = iconElement.querySelector('i');
	
	if (field.type === 'password') {
		field.type = 'text';
		icon.className = 'fa fa-eye';
	} else {
		field.type = 'password';
		icon.className = 'fa fa-eye-slash';
	}
}

// Toggle ethnicity field
function toggleEthnicity() {
	const ipCheckbox = document.getElementById('ip');
	const ethnicityField = document.getElementById('ethnicityField');
	
	if (ipCheckbox.checked) {
		ethnicityField.style.display = 'block';
	} else {
		ethnicityField.style.display = 'none';
		document.getElementById('ethnicity').value = '';
	}
}

// Format monthly income with commas
function formatWithCommas() {
	const displayInput = document.getElementById('monthly_income_display');
	const hiddenInput = document.getElementById('monthly_income');
	
	let value = displayInput.value.replace(/[^\d.]/g, ''); // Remove non-numeric characters except decimal
	
	if (value) {
		const numericValue = parseFloat(value);
		if (!isNaN(numericValue)) {
			displayInput.value = '₱' + numericValue.toLocaleString('en-US', {
				minimumFractionDigits: 2,
				maximumFractionDigits: 2
			});
			hiddenInput.value = numericValue;
		}
	} else {
		displayInput.value = '';
		hiddenInput.value = '';
	}
}

// Update ID card format placeholder
function updateIDCardFormat() {
	const idSelect = document.getElementById('icp');
	const idInput = document.getElementById('icn');
	
	const placeholders = {
		'Philippine National ID': 'XXXX-XXXXXXX-X',
		'Passport': 'XXXXXXXXX',
		"Driver's License": 'XXX-XX-XXXXXX',
		'UMID': 'XXXX-XXXXXXX-X',
		'SSS ID': 'XX-XXXXXXX-X',
		'PRC ID': 'XXXXXXX',
		"Voter's ID": 'XXXX-XXXX-XXXX',
		'TIN ID': 'XXX-XXX-XXX-XXX',
		'PhilHealth ID': 'XX-XXXXXXXXX-X'
	};
	
	const selectedValue = idSelect.value;
	if (placeholders[selectedValue]) {
		idInput.placeholder = placeholders[selectedValue];
	} else {
		idInput.placeholder = 'Enter ID Card Number';
	}
}

// Load barangays
function loadBarangays() {
    const barangaySelect = document.getElementById('barangay');
    // Global-like map for other handlers
    window.barangayNameToId = {};

    if (!barangaySelect) return;

    // Clear existing options
    barangaySelect.innerHTML = '<option value="">Select Barangay</option>';

    // Load barangays from database
    fetch('../action/brgy_management_action/list_barangay_map.php')
        .then(r => r.json())
        .then(res => {
            if (!res || !res.success || !Array.isArray(res.data)) return;

            res.data.forEach(row => {
                if (!row || !row.barangay_name) return;
                const option = document.createElement('option');
                option.value = row.barangay_name;
                option.textContent = row.barangay_name;
                barangaySelect.appendChild(option);
                // fill map
                window.barangayNameToId[row.barangay_name] = row.barangay_id;
            });
        })
        .catch(() => {});
}

// Family members functionality
document.addEventListener('DOMContentLoaded', function() {
    const barangaySelect = document.getElementById('barangay');
    const purokSelect = document.getElementById('purok');

    if (barangaySelect && purokSelect) {
        barangaySelect.addEventListener('change', function() {
            const selectedName = this.value || '';
            // Reset purok options
            purokSelect.innerHTML = '<option value="">Select Purok</option>';
            if (!selectedName) return;

            // Resolve barangay_id
            const barangayId = (typeof barangayNameToId !== 'undefined') ? barangayNameToId[selectedName] : undefined;
            if (!barangayId) return;

            fetch(`../action/brgy_management_action/list_purok.php?barangay_id=${encodeURIComponent(barangayId)}`)
                .then(r=>r.json())
                .then(res=>{
                    if (res && res.success && Array.isArray(res.data)) {
                        res.data.forEach(p => {
                            const opt = document.createElement('option');
                            opt.value = p.purok_name;
                            opt.textContent = p.purok_name;
                            opt.dataset.pickupPoint = p.pickup_point_name || '';
                            purokSelect.appendChild(opt);
                        });
                    }
                })
                .catch(()=>{});
        });

        // Add purok change event listener to auto-fill pickup point
        purokSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const pickupPointField = document.getElementById('pickup_name');
            
            if (selectedOption && selectedOption.dataset.pickupPoint && pickupPointField) {
                pickupPointField.value = selectedOption.dataset.pickupPoint;
            }
        });
    }
	const registrationTypeSelect = document.getElementById('registration_type');
	const familyMembersSection = document.getElementById('familyMembersSection');
	const numFamilyMembersInput = document.getElementById('numFamilyMembers');
	const familyMembersFields = document.getElementById('familyMembersFields');

	if (registrationTypeSelect) {
		registrationTypeSelect.addEventListener('change', function() {
			if (this.value === 'Family') {
				familyMembersSection.style.display = 'block';
			} else {
				familyMembersSection.style.display = 'none';
				familyMembersFields.innerHTML = '';
				numFamilyMembersInput.value = '';
			}
		});
	}

	if (numFamilyMembersInput) {
		numFamilyMembersInput.addEventListener('input', function() {
			const numMembers = parseInt(this.value) || 0;
			familyMembersFields.innerHTML = '';

			for (let i = 1; i <= numMembers; i++) {
				const memberDiv = document.createElement('div');
				memberDiv.className = 'row mb-3 border p-3 rounded';
				memberDiv.innerHTML = `
					<div class="col-12">
						<h6>Family Member ${i}</h6>
					</div>
					<div class="col-md-3">
						<label class="form-label">First Name</label>
						<input type="text" name="member_fname_${i}" id="member_fname_${i}" class="form-control" required onchange="validateMemberName(${i})">
					</div>
					<div class="col-md-3">
						<label class="form-label">Middle Name</label>
						<input type="text" name="member_mname_${i}" id="member_mname_${i}" class="form-control" onchange="validateMemberName(${i})">
					</div>
					<div class="col-md-3">
						<label class="form-label">Last Name</label>
						<input type="text" name="member_lname_${i}" id="member_lname_${i}" class="form-control" required onchange="validateMemberName(${i})">
					</div>
					<div class="col-md-3">
						<label class="form-label">Name Extension</label>
						<select class="form-control" name="member_name_extension_${i}" id="member_name_extension_${i}" onchange="validateMemberName(${i})">
							<option value="" disabled selected>-- Select Extension --</option>
							<option value="">None</option>
							<option value="jr">Jr.</option>
							<option value="sr">Sr.</option>
							<option value="i">I</option>
							<option value="ii">II</option>
							<option value="iii">III</option>
						</select>
					</div>
					<div class="col-md-4">
						<label class="form-label">Date of Birth</label>
						<input type="date" name="member_dob_${i}" id="member_dob_${i}" class="form-control" required max="${new Date().toISOString().split('T')[0]}">
					</div>
					<div class="col-md-4">
						<label class="form-label">Gender</label>
						<select name="member_gender_${i}" id="member_gender_${i}" class="form-control" required>
							<option value="">Select Gender</option>
							<option value="Male">Male</option>
							<option value="Female">Female</option>
						</select>
					</div>
					<div class="col-md-4">
						<label class="form-label">Relation to Head</label>
						<select name="member_relation_${i}" id="member_relation_${i}" class="form-control" required>
							<option value="">Select Relation</option>
							<option value="Spouse">Spouse</option>
							<option value="Child">Child</option>
							<option value="Parent">Parent</option>
							<option value="Sibling">Sibling</option>
							<option value="Grandparent">Grandparent</option>
							<option value="Grandchild">Grandchild</option>
							<option value="Other Relative">Other Relative</option>
						</select>
					</div>
					<div class="col-12">
						<small id="member_name_feedback_${i}" class="form-text"></small>
					</div>
				`;
				familyMembersFields.appendChild(memberDiv);
			}
		});
	}

	// Handle form submission
	const registrationForm = document.getElementById('inlineRegistrationForm');
	if (registrationForm) {
		registrationForm.addEventListener('submit', function(e) {
			e.preventDefault();
			
			const formData = new FormData(this);
			const submitBtn = document.getElementById('submitBtn');
			
			// Disable submit button during processing
			submitBtn.disabled = true;
			submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
			
			fetch('../action/action_pre_reg.php', {
				method: 'POST',
				body: formData
			})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					// Show success message
					Swal.fire({
						icon: 'success',
						title: 'Registration Successful!',
						text: data.message,
						showConfirmButton: true,
						confirmButtonText: 'Add Another Person',
						showCancelButton: true,
						cancelButtonText: 'Close',
						confirmButtonColor: '#28a745',
						cancelButtonColor: '#6c757d'
					}).then((result) => {
						if (result.isConfirmed) {
							// Reset form for another registration
							resetRegistrationForm();
						} else {
							// Close modal
							const modal = bootstrap.Modal.getInstance(document.getElementById('registrationModal'));
							modal.hide();
						}
					});
				} else {
					// Show error message
					Swal.fire({
						icon: 'error',
						title: 'Registration Failed',
						text: data.message,
						confirmButtonColor: '#dc3545'
					});
				}
			})
			.catch(error => {
				console.error('Error:', error);
				Swal.fire({
					icon: 'error',
					title: 'Error',
					text: 'An unexpected error occurred. Please try again.',
					confirmButtonColor: '#dc3545'
				});
			})
			.finally(() => {
				// Re-enable submit button
				submitBtn.disabled = false;
				submitBtn.innerHTML = 'Submit';
			});
		});
	}

	// Function to reset the registration form
	function resetRegistrationForm() {
		const form = document.getElementById('inlineRegistrationForm');
		if (form) {
			form.reset();
			
			// Clear validation feedback
			const feedbackElements = form.querySelectorAll('.text-danger, .text-success, .text-warning');
			feedbackElements.forEach(el => {
				el.innerHTML = '';
				el.className = '';
			});
			
			// Remove validation classes
			const inputElements = form.querySelectorAll('.is-valid, .is-invalid');
			inputElements.forEach(el => {
				el.classList.remove('is-valid', 'is-invalid');
			});
			
			// Hide image previews
			const imagePreviewElements = ['signaturePreview', 'idCardPreview', 'profilePicPreview'];
			imagePreviewElements.forEach(id => {
				const element = document.getElementById(id);
				if (element) {
					if (id === 'profilePicPreview') {
						element.classList.add('d-none');
					} else {
						element.style.display = 'none';
						element.src = '#';
					}
				}
			});
			
			// Hide conditional fields
			const conditionalFields = ['ethnicityField', 'vehicle_type_field', 'where_to_go_field', 'special_needs_field', 'familyMembersSection'];
			conditionalFields.forEach(id => {
				const element = document.getElementById(id);
				if (element) {
					element.style.display = 'none';
				}
			});
			
			// Clear family members fields
			const familyMembersFields = document.getElementById('familyMembersFields');
			if (familyMembersFields) {
				familyMembersFields.innerHTML = '';
			}
			
			// Reset file inputs
			const fileInputs = form.querySelectorAll('input[type="file"]');
			fileInputs.forEach(input => {
				input.value = '';
			});
			
			// Reload barangays
			loadBarangays();
		}
	}
});

// Family member name validation
function validateMemberName(memberIndex) {
	const fname = document.getElementById(`member_fname_${memberIndex}`)?.value.trim();
	const mname = document.getElementById(`member_mname_${memberIndex}`)?.value.trim();
	const lname = document.getElementById(`member_lname_${memberIndex}`)?.value.trim();
	const nameExt = document.getElementById(`member_name_extension_${memberIndex}`)?.value || '';
	const feedback = document.getElementById(`member_name_feedback_${memberIndex}`);

	// Clear previous validation
	clearMemberValidation(memberIndex);

	if (!fname || !lname) {
		return; // Don't validate incomplete names
	}

	// Check for duplicates within family members
	const isDuplicateInFamily = checkDuplicateInFamilyMembers(memberIndex, fname, mname, lname, nameExt);
	
	if (isDuplicateInFamily) {
		feedback.innerHTML = "⚠️ This name is already used by another family member.";
		feedback.className = "form-text text-danger";
		markMemberNameAsInvalid(memberIndex);
		return;
	}

	// Check against head of family
	const headFname = document.getElementById('f_name')?.value.trim();
	const headMname = document.getElementById('m_name')?.value.trim();
	const headLname = document.getElementById('l_name')?.value.trim();
	const headNameExt = document.getElementById('name_extension')?.value || '';

	if (headFname && headLname && 
		fname.toLowerCase() === headFname.toLowerCase() &&
		(mname || '').toLowerCase() === (headMname || '').toLowerCase() &&
		lname.toLowerCase() === headLname.toLowerCase() &&
		(nameExt || '').toLowerCase() === (headNameExt || '').toLowerCase()) {
		feedback.innerHTML = "⚠️ This name matches the head of family.";
		feedback.className = "form-text text-danger";
		markMemberNameAsInvalid(memberIndex);
		return;
	}

	// Check if name exists in database
	checkMemberNameInDatabase(memberIndex, fname, mname, lname, nameExt);
}

function checkDuplicateInFamilyMembers(currentIndex, fname, mname, lname, nameExt) {
	const numMembersInput = document.getElementById('numFamilyMembers');
	const numMembers = parseInt(numMembersInput?.value) || 0;

	for (let i = 1; i <= numMembers; i++) {
		if (i === currentIndex) continue; // Skip self

		const otherFname = document.getElementById(`member_fname_${i}`)?.value.trim();
		const otherMname = document.getElementById(`member_mname_${i}`)?.value.trim();
		const otherLname = document.getElementById(`member_lname_${i}`)?.value.trim();
		const otherNameExt = document.getElementById(`member_name_extension_${i}`)?.value || '';

		if (otherFname && otherLname &&
			fname.toLowerCase() === otherFname.toLowerCase() &&
			(mname || '').toLowerCase() === (otherMname || '').toLowerCase() &&
			lname.toLowerCase() === otherLname.toLowerCase() &&
			(nameExt || '').toLowerCase() === (otherNameExt || '').toLowerCase()) {
			return true;
		}
	}
	return false;
}

function checkMemberNameInDatabase(memberIndex, fname, mname, lname, nameExt) {
	const feedback = document.getElementById(`member_name_feedback_${memberIndex}`);
	
	fetch("../check_validation/name_validation.php", {
		method: "POST",
		headers: {
			"Content-Type": "application/x-www-form-urlencoded",
		},
		body: "f_name=" + encodeURIComponent(fname) +
			"&m_name=" + encodeURIComponent(mname) +
			"&l_name=" + encodeURIComponent(lname) +
			"&name_ext=" + encodeURIComponent(nameExt || ''),
	})
	.then((response) => response.text())
	.then((data) => {
		const result = data.trim();

		if (result === "taken") {
			feedback.innerHTML = "⚠️ This name is already registered in the system.";
			feedback.className = "form-text text-danger";
			markMemberNameAsInvalid(memberIndex);
		} else if (result === "available") {
			feedback.innerHTML = "✓ Name is unique.";
			feedback.className = "form-text text-success";
			markMemberNameAsValid(memberIndex);
		} else {
			feedback.innerHTML = "⚠️ Error checking name availability.";
			feedback.className = "form-text text-warning";
		}
	})
	.catch((error) => {
		console.error("Error:", error);
		feedback.innerHTML = "⚠️ Server error checking name.";
		feedback.className = "form-text text-warning";
	});
}

function clearMemberValidation(memberIndex) {
	const inputs = [`member_fname_${memberIndex}`, `member_mname_${memberIndex}`, `member_lname_${memberIndex}`, `member_name_extension_${memberIndex}`];
	inputs.forEach(id => {
		const element = document.getElementById(id);
		if (element) {
			element.classList.remove('is-valid', 'is-invalid');
		}
	});
}

function markMemberNameAsInvalid(memberIndex) {
	const inputs = [`member_fname_${memberIndex}`, `member_mname_${memberIndex}`, `member_lname_${memberIndex}`, `member_name_extension_${memberIndex}`];
	inputs.forEach(id => {
		const element = document.getElementById(id);
		if (element) {
			element.classList.add('is-invalid');
			element.classList.remove('is-valid');
		}
	});
}

function markMemberNameAsValid(memberIndex) {
	const inputs = [`member_fname_${memberIndex}`, `member_mname_${memberIndex}`, `member_lname_${memberIndex}`, `member_name_extension_${memberIndex}`];
	inputs.forEach(id => {
		const element = document.getElementById(id);
		if (element) {
			element.classList.add('is-valid');
			element.classList.remove('is-invalid');
		}
	});
}

// ID Card Modal functionality
document.addEventListener('DOMContentLoaded', function() {
	// Update card preview when modal is shown
	document.getElementById('idCardModal').addEventListener('show.bs.modal', function() {
		updateCardPreview();
	});

	// Print functionality
	document.getElementById('printCardBtn').addEventListener('click', function() {
		printCards();
	});

	// Download PDF functionality
	document.getElementById('downloadCardBtn').addEventListener('click', function() {
		downloadPDF();
	});
});

function updateCardPreview() {
	// Cards are now populated with actual database data
	// No need to update form data since we're showing real evacuee information
	console.log('Card preview updated with database data');
}

function getFullName(fnameId, mnameId, lnameId, extId) {
	const fname = document.getElementById(fnameId)?.value || '';
	const mname = document.getElementById(mnameId)?.value || '';
	const lname = document.getElementById(lnameId)?.value || '';
	const ext = document.getElementById(extId)?.value || '';
	
	let fullName = fname;
	if (mname) fullName += ' ' + mname;
	fullName += ' ' + lname;
	if (ext) fullName += ' ' + ext;
	
	return fullName.trim();
}

function getFullAddress() {
	const street = document.getElementById('street')?.value || '';
	const subDiv = document.getElementById('sub_div')?.value || '';
	const purok = document.getElementById('purok')?.selectedOptions[0]?.text || '';
	const barangay = document.getElementById('barangay')?.selectedOptions[0]?.text || '';
	const zipCode = document.getElementById('zip_code')?.value || '';
	
	let address = '';
	if (street) address += street;
	if (subDiv) address += (address ? ', ' : '') + subDiv;
	if (purok && purok !== 'Select Purok') address += (address ? ', ' : '') + purok;
	if (barangay && barangay !== 'Select Barangay') address += (address ? ', ' : '') + barangay;
	if (zipCode) address += (address ? ', ' : '') + zipCode;
	
	return address;
}

function getVehicleInfo() {
	const haveVehicle = document.getElementById('have_vehicle')?.value || '';
	const vehicleType = document.getElementById('vehicle_type')?.value || '';
	
	if (haveVehicle === 'Yes' && vehicleType) {
		return vehicleType;
	} else if (haveVehicle === 'No') {
		return 'No Vehicle';
	}
	return '';
}

function printCards() {
	// Collect each id-card element and wrap into a printable item
	const cardNodes = Array.from(document.querySelectorAll('#idCardContent .id-card'));
	if (cardNodes.length === 0) return alert('No ID cards found to print.');

	let itemsHtml = cardNodes.map(card => {
		// remove interactive attributes that shouldn't be printed
		const clone = card.cloneNode(true);
		// remove modal-specific classes on clone
		clone.querySelectorAll('[data-bs-toggle]').forEach(n => n.removeAttribute('data-bs-toggle'));
		return `<div class="id-card-print">${clone.innerHTML}</div>`;
	}).join('');

	let printWindow = window.open("", "", "width=900,height=650");

	// Build print HTML that forces two cards per row on A4
	let printContent = `
		<html>
		<head>
			<title>Print ID Cards</title>
			<style>
                                /* Page setup */
                                @page { size: A4 portrait; margin: 8mm; }
                                body { font-family: Arial, sans-serif; margin: 0; padding: 6mm; background: white; }
                                .print-container { display: grid; grid-template-columns: 1fr 1fr; gap: 6mm; width: calc(100% - 12mm); }

                                /* Compact printed card (preserve recent size choices) */
                                .id-card-print {
                                    background: white;
                                    border: 2px solid #000; /* match design border */
                                    padding: 2mm;            /* keep compact */
                                    font-size: 6px;         /* preserve compact font */
                                    line-height: 1.05;
                                    box-sizing: border-box;
                                }

                                /* Header / Title styling from provided design */
                                .id-card-print .card-header {
                                    text-align: center;
                                    margin-bottom: 4px;
                                    border-bottom: 2px solid #000;
                                    padding-bottom: 2px;
                                }
                                .id-card-print .card-title {
                                    font-size: 9px; /* slightly larger but still compact */
                                    font-weight: bold;
                                    margin: 0;
                                    color: #000;
                                    text-transform: uppercase;
                                }
                                .id-card-print .card-subtitle {
                                    font-size: 8px;
                                    font-weight: bold;
                                    margin: 2px 0 0 0;
                                    color: #dc3545;
                                    text-transform: uppercase;
                                }

                                /* Table styling from provided design but keep compact paddings */
                                .id-card-print .form-section { margin-bottom: 4px; }
                                .id-card-print .form-table { width: 100%; border-collapse: collapse; border: 1px solid #000; }
                                .id-card-print .form-table td { padding: 0 0; vertical-align: top; }
                                .id-card-print .form-table td:first-child {
                                    width: 28%;
                                    font-size: 5px;
                                    font-weight: bold;
                                    text-transform: uppercase;
                                    padding: 2px 3px;
                                    text-align: center;
                                    vertical-align: middle;
                                    border-right: 1px solid #000;
                                    border-bottom: 1px solid #000;
                                    background: #f2f6ff; /* subtle accent */
                                }
                                .id-card-print .form-table td:last-child {
                                    width: 72%;
                                    font-size: 6px;
                                    padding: 2px 4px;
                                    border-bottom: 1px solid #000;
                                    vertical-align: middle;
                                    background: #ffffff;
                                }
                                .id-card-print .form-label-local { font-size: 4px; color: #666; font-style: italic; }

                                /* Checkbox visuals kept small */
                                // .id-card-print .checkbox-box { width: 10px; height: 5px; border: 1px solid #000; }
                                // .id-card-print .checkbox-box.checked { background: #000; }

                                /* Force QR prominence (preserve recent larger size) */
                                // .id-card-print img[alt="QR Code"] { width: 50mm !important; height: 50mm !important; object-fit: contain !important; display:block; }
                                // .id-card-print img { max-width: 100% !important; height: auto !important; }

                                /* Authority / footer design for PRINT ONLY */
                                .id-card-print .authority-section {
                                    display: flex;
                                    align-items: center;
                                    gap: 8px;
                                    margin-top: 6px;
                                    padding: 6px;
                                    background: #f4a460;
                                    border: 1px solid #000;
                                    border-radius: 4px;
                                }
                                .id-card-print .logo-placeholder {
                                    width: 50px;
                                    height: 50px;
                                    border: 1px dashed #8b4513;
                                    border-radius: 50%;
                                    display:flex; align-items:center; justify-content:center;
                                    background:#deb887; color:#8b4513; font-size:10px;
                                    overflow: hidden;
                                }
                                .id-card-print .authority-list { display: flex; flex-direction: column; gap: 4px; width: 100%; }
                                .id-card-print .authority-item { display: flex; align-items: center; gap: 8px; }
                                .id-card-print .authority-name { font-weight: bold; text-transform: uppercase; font-size: 7px; min-width: 120px; }
                                .id-card-print .authority-line { border-bottom: 1px solid #000; flex: 1; height: 0; }

                                .id-card-print .footer { margin-top: 4px; background-color: lightblue; padding: 0; box-sizing: border-box; }
                                .id-card-print .footer-text { font-weight: bold; text-transform: uppercase; font-size: 8px; text-align: center; }

                                /* Subtitle emphasis */
                                .id-card-print .card-subtitle { color: #dc3545; font-weight: 700; }

                                @media print {
                                    .print-container { page-break-inside: avoid; }
                                    .id-card-print { page-break-inside: avoid; }
                                }
			</style>
		</head>
		<body>
			<div class="print-container">
				${itemsHtml}
			</div>
		</body>
		</html>
	`;

	printWindow.document.write(printContent);
	printWindow.document.close();

	printWindow.onload = function() {
		printWindow.focus();
		printWindow.print();
	};
}

function downloadPDF() {
	const { jsPDF } = window.jspdf; // Get jsPDF from UMD
	html2canvas(document.getElementById("idCardContent")).then(canvas => {
		const imgData = canvas.toDataURL("image/png");
		const pdf = new jsPDF({
			orientation: "portrait",
			unit: "mm",
			format: "a4"
		});

		const imgProps = pdf.getImageProperties(imgData);
		const pdfWidth = pdf.internal.pageSize.getWidth();
		const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

		pdf.addImage(imgData, "PNG", 0, 0, pdfWidth, pdfHeight);
		pdf.save("Evacuation_ID_Card.pdf");
	});
}
</script>

<!-- Add SweetAlert2 for better notifications -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Add required CDN links for PDF generation -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<!-- Add styling for input groups -->
<style>
	 .container {
                                max-width: 900px;
                                margin: 0 auto;
                                background: white;
                                padding: 15px;
                                border-radius: 8px;
                                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                            }

                            .preview-header {
                                text-align: center;
                                margin-bottom: 15px;
                                padding: 10px;
                                background: #f8f9fa;
                                border-radius: 8px;
                            }

                            .preview-header h1 {
                                color: #333;
                                margin-bottom: 5px;
                                font-size: 1.2rem;
                            }

                            .preview-header p {
                                color: #666;
                                margin: 3px 0;
                                font-size: 0.9rem;
                            }

                            .id-card {
                                background: white;
                                color: black;
                                padding: 1rem;
                                border: 2px solid #000;
                                border-radius: 0;
                                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
                                position: relative;
                                margin-bottom: 20px;
                                font-family: Arial, sans-serif;
                                font-size: 12px;
                                line-height: 1.3;
                            }

                            .card-header {
                                text-align: center;
                                margin-bottom: 1rem;
                                border-bottom: 2px solid #000;
                                padding-bottom: 0.5rem;
                            }

                            .card-title {
                                font-size: 1.1rem;
                                font-weight: bold;
                                margin: 0;
                                color: #000;
                                text-transform: uppercase;
                            }

                            .card-subtitle {
                                font-size: 1rem;
                                font-weight: bold;
                                margin: 0.3rem 0 0 0;
                                color: #dc3545;
                                text-transform: uppercase;
                            }

                            .form-section {
                                margin-bottom: 1rem;
                                height: 400px;
                            }

                            .form-table {
                                width: 100%;
                                border-collapse: collapse;
                                border: 1px solid #000;
                            }

                            .form-table td {
                                padding: 0rem 0;
                                vertical-align: top;
                            }


                            .form-table td:first-child {
                                width: 25%;
                                font-size: 7px;
                                font-weight: bold;
                                text-transform: uppercase;
                                padding: 2px;
                                text-align: center;
                                vertical-align: middle;
                                border-right: 1px solid #000;
                                /* Added vertical line between columns */
                                border-bottom: 1px solid #000;
                                /* Added horizontal line */
                            }

                            .form-table td:last-child {
                                width: 75%;
                                /* Adjusted to total 100% with first-child */
                                font-size: 9px;
                                padding: 2px 2px 2px 5px;
                                /* Added padding */
                                border-bottom: 1px solid #000;
                                /* Added horizontal line */
                                vertical-align: middle;
                                /* Ensure consistent vertical alignment */
                            }

                            .form-label-local {
                                font-size: 0.4rem;
                                color: #666;
                                font-style: italic;
                                text-transform: none;
                                font-weight: normal;
                                display: block;
                                margin-top: 0.1rem;
                            }

                            .checkbox-group {
                                display: flex;
                                align-items: center;
                                gap: 0.5rem;
                            }

                            .checkbox-item {
                                display: flex;
                                align-items: center;
                                gap: 0.3rem;
                            }

                            .checkbox-box {
                                width: 16px;
                                height: 16px;
                                border: 1px solid #000;
                                display: inline-block;
                                position: relative;
                            }

                            .checkbox-box.checked {
                                background: #000;
                            }

                            .checkbox-box.checked::after {
                                content: '✓';
                                position: absolute;
                                top: -2px;
                                left: 1px;
                                font-weight: bold;
                                color: white;
                                font-size: 0.8rem;
                            }

                            .control-number-section {
                                margin-top: 0.5rem;
                                padding-top: 0.5rem;
                                border-top: 1px solid #ccc;
                            }

                            .control-number-table {
                                width: 100%;
                                border-collapse: collapse;
                            }

                            .control-number-table td {
                                padding: 0.3rem 0;
                                vertical-align: middle;
                            }

                            .control-number-table td:first-child {
                                width: 30%;
                                font-weight: bold;
                                text-transform: uppercase;
                                padding-right: 0.5rem;
                            }

                            .control-number-table td:last-child {
                                width: 70%;
                            }

                            .control-number-box {
                                border: 1px solid #000;
                                padding: 0.3rem 0.5rem;
                                text-align: center;
                                font-weight: bold;
                                background: #f8f9fa;
                                display: inline-block;
                                min-width: 150px;
                                font-size: 0.9rem;
                            }

                            .authority-section {
                                display: flex;
                                justify-content: space-between;
                                margin-top: -100px;
                                padding: 0.8rem;
                                background: #f4a460;
                                border: 1px solid #000;
                            }

                            .logo-placeholder {
                                width: 80px;
                                height: 80px;
                                border: 1px dashed #8b4513;
                                border-radius: 50%;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                background: #deb887;
                                font-size: 0.6rem;
                                color: #8b4513;
                                text-align: center;
                                flex-shrink: 0;
                            }

                            .authority-list {
                                flex-grow: 1;
                                margin-left: 1rem;
                            }

                            .authority-item {
                                display: flex;
                                justify-content: space-between;
                                align-items: center;
                                margin-bottom: 0.3rem;
                            }

                            .authority-name {
                                font-weight: bold;
                                text-transform: uppercase;
                                font-size: 0.7rem;
                            }

                            .authority-line {
                                border-bottom: 1px solid #000;
                                flex-grow: 1;
                                margin-left: 0.5rem;
                                min-width: 100px;
                            }

                            .authority-phone {
                                font-size: 0.6rem;
                                color: #666;
                            }

                            .footer {
                                margin-top: 0.5rem;
                                border-radius: 20px;
                                border-right: 60px solid white;
                                border-top: 20px solid white;
                                border-bottom: 20px solid white;
                                border-left: 30px solid white;
                                background-color: lightblue;
                                padding: 0;
                                /* Remove all internal padding */
                                box-sizing: border-box;
                                /* Include border in width calculation */
                            }

                            .footer-content {
                                display: flex;
                                justify-content: space-between;
                                align-items: center;
                                gap: 0;
                                /* Ensures no gap between items */
                            }

                            .footer-text {
                                font-weight: bold;
                                text-transform: uppercase;
                                font-size: 0.8rem;
                                text-align: center;
                                flex-grow: 1;
                                padding-right: 0;
                                /* Removed padding */
                                /* margin-right: -10px; Pulls logo closer by negative margin */
                            }

                            .volcano-logo {
                                width: 80px;
                                height: 80px;
                                border: 1px solid #000;
                                border-radius: 50%;
                                background: #ff8c00;
                                position: relative;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                margin-left: 0;
                                /* Removed margin */
                                transform: translateX(4px);
                                /* Fine-tune positioning */
                                /* margin-right:100px; */
                            }

                            /* Keep volcano logo details the same */
                            .volcano-logo::before {
                                content: '🌋';
                                position: absolute;
                                font-size: 3rem;
                            }

                            .volcano-logo::after {
                                content: 'TASK FORCE\A KANLAON';
                                position: absolute;
                                bottom: 2px;
                                left: 50%;
                                transform: translateX(-50%);
                                font-size: 0.3rem;
                                text-align: center;
                                line-height: 1;
                                white-space: pre-line;

                            }

                            .print-info {
                                background: #e9ecef;
                                padding: 10px;
                                border-radius: 8px;
                                margin-bottom: 15px;
                                font-size: 12px;
                            }

                            .print-info h3 {
                                margin-top: 0;
                                color: #333;
                                font-size: 1rem;
                            }

                            .print-info ul {
                                margin: 8px 0;
                                padding-left: 15px;
                            }

                            .print-info li {
                                margin: 3px 0;
                                font-size: 0.8rem;
                            }

                            @media print {
                                .footer {
                                    background-color: turquoise;
                                }
                            }
</style>