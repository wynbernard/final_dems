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
								<input type="text" name="purok" id="purok" class="form-control" placeholder="Enter Purok" required>
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
								<input type="text" name="pickup_name" id="pickup_name" class="form-control" placeholder="Enter Pick-up Point Name">
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
					<div class="d-grid gap-2">
						<button type="submit" id="submitBtn" class="btn btn-success">Submit</button>
						</div>
					</div>
				</form>
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
			emailInput.addEventListener("blur", validateEmail);
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
	const targetCityCode = "064502"; // City code for Bago City
	const barangaySelect = document.getElementById('barangay');

	// Load barangays JSON
	fetch('../../../address_json/barangays.json')
		.then(response => response.json())
		.then(barangayList => {
			// Filter barangays with matching city_code
			const filteredBarangays = barangayList.filter(b => b.city_code === targetCityCode);

			// Clear existing options except the first one
			barangaySelect.innerHTML = '<option value="">Select Barangay</option>';

			// Populate the select dropdown
			filteredBarangays.forEach(barangay => {
				const option = document.createElement('option');
				option.value = barangay.brgy_name;
				option.textContent = barangay.brgy_name;
				barangaySelect.appendChild(option);
			});
		})
		.catch(error => {
			console.error('Failed to load barangays:', error);
		});
}

// Family members functionality
document.addEventListener('DOMContentLoaded', function() {
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
</script>

<!-- Add SweetAlert2 for better notifications -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Add required CDN links for FontAwesome and other dependencies -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<!-- Add styling for input groups -->
<style>
	.input-group {
		position: relative;
	}

	.input-group input {
		padding-right: 2.5rem;
	}

	.input-group .input-group-text {
		position: absolute;
		top: 50%;
		right: 10px;
		transform: translateY(-50%);
		cursor: pointer;
		color: #6c757d;
		background: none;
		border: none;
		z-index: 10;
	}

	label {
		font: bold 14px Arial, sans-serif;
	}
</style>