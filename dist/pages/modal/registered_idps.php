<!-- Register IDP Modal -->
<div class="modal fade" id="registerIDPModal" tabindex="-1" aria-labelledby="registerIDPModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="registerIDPModalLabel">Register New IDP</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form id="idpRegistrationForm" method="POST" action="../action/registration_backend.php" enctype="multipart/form-data">
					<div id="assignedRoomDisplay" class="alert alert-success mb-3" style="display:none; font-weight:bold;"></div>
					<div class="d-flex align-items-center mb-3">
						<div class="alert alert-info mb-3 me-3">
							<strong>Location:</strong>
							<?php
							// Safely get parameters with null coalescing operator
							$selectedLocationId = $_GET['location_id'] ?? $_SESSION['evac_loc_id'] ?? '';

							if ($selectedLocationId) {
								// Query location name using prepared statement
								$stmt = $conn->prepare("SELECT name FROM evac_loc_table WHERE evac_loc_id = ?");
								$stmt->bind_param("s", $selectedLocationId);
								$stmt->execute();
								$result = $stmt->get_result();

								if ($result->num_rows > 0) {
									$location = $result->fetch_assoc();
									echo htmlspecialchars($location['name']);
								} else {
									echo "Unknown Location (ID: " . htmlspecialchars($selectedLocationId) . ")";
								}
							} else {
								// Check if evac_loc_id exists but wasn't captured
								$fallbackEvacId = $_GET['evac_loc_id'] ?? '';
								if ($fallbackEvacId) {
									// Use prepared statement to prevent SQL injection
									$stmt = $conn->prepare("SELECT evac_loc_table.name FROM admin_table
								LEFT JOIN evac_loc_table ON admin_table.evac_loc_id = evac_loc_table.evac_loc_id
								 WHERE evac_loc_id = ?");
									$stmt->bind_param("s", $fallbackEvacId);

									if ($stmt->execute()) {
										$result = $stmt->get_result();

										if ($result->num_rows > 0) {
											$location = $result->fetch_assoc();
											echo htmlspecialchars($location['name']);
										} else {
											echo "Unknown Location (ID: " . htmlspecialchars($fallbackEvacId) . ")";
										}
									} else {
										// Handle query error
										echo "Location ID: " . htmlspecialchars($fallbackEvacId) . " (Error fetching details)";
									}

									$stmt->close();
								} else {
									echo "No location selected";
								}
							}
							?>
							<input type="hidden" name="location_id" value="<?php echo htmlspecialchars($selectedLocationId); ?>">
						</div>
						<div class="alert alert-info mb-3">
							<strong>Disaster Event:</strong>
							<span id="disasterEventName">
								<?php
								$selectedDisasterId = $_GET['disasterId'] ?? '';
								$disasterName = '';
								if ($selectedDisasterId) {
									$stmt = $conn->prepare("SELECT disaster_name FROM disaster_table WHERE disaster_id = ?");
									$stmt->bind_param("i", $selectedDisasterId);
									$stmt->execute();
									$result = $stmt->get_result();
									if ($result && $result->num_rows > 0) {
										$row = $result->fetch_assoc();
										$disasterName = $row['disaster_name'];
										echo htmlspecialchars($disasterName);
									} else {
										echo "Unknown Disaster (ID: " . htmlspecialchars($selectedDisasterId) . ")";
									}
									$stmt->close();
								} else {
									echo "No disaster selected";
								}
								?>
							</span>
							<input type="hidden" name="disasterId" id="disasterIdHidden" value="<?php echo htmlspecialchars($selectedDisasterId); ?>">
						</div>
						<div class="ms-3">
							<label for="room_id" class="form-label"><strong>Room :</strong></label>
							<select name="room_id" id="room" class="form-select" style="min-width:180px;display:inline-block;" <?php if (empty($selectedLocationId) && empty($fallbackEvacId)) echo 'disabled'; ?>>
								<option value="" disabled selected>
									<?php if (empty($selectedLocationId) && empty($fallbackEvacId)) {
										echo 'Select a location first';
									} else {
										echo 'Select a room';
									} ?>
								</option>
								<!-- Room options will be populated by JS -->
							</select>
						</div>
					</div>
					<div class="row">
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
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="../scripts/auth_script/user_registration.js"></script>
<script src="../scripts/auth_script/address_api.js"></script>
<script>
	document.addEventListener('DOMContentLoaded', function() {
		const regType = document.getElementById('registration_type');
		const familySection = document.getElementById('familyMembersSection');
		const numMembersInput = document.getElementById('numFamilyMembers');
		const familyFieldsDiv = document.getElementById('familyMembersFields');

		if (regType) {
			regType.addEventListener('change', function() {
				if (this.value === 'Family') {
					familySection.style.display = 'block';
				} else {
					familySection.style.display = 'none';
					familyFieldsDiv.innerHTML = '';
					if (numMembersInput) numMembersInput.value = '';
				}
			});
		}

		function autoSelectRoom() {
			const count = parseInt(numMembersInput.value) || 0;
			const totalPeople = count + 1;
			const roomDropdown = document.getElementById('room');
			const assignedRoomDisplay = document.getElementById('assignedRoomDisplay');
			let assignedText = '';
			if (roomDropdown) {
				let found = false;
				for (let opt of roomDropdown.options) {
					if (opt.value && !opt.disabled) {
						// Try to extract occupancy/capacity from option text: "Room Name (Occupied/Capacity)"
						const match = opt.textContent.match(/\((\d+)[^\d]+(\d+)\)/);
						if (match) {
							const occupied = parseInt(match[1]);
							const capacity = parseInt(match[2]);
							if ((capacity - occupied) >= totalPeople) {
								roomDropdown.value = opt.value;
								assignedText = `Assigned Room: <b>${opt.textContent}</b>`;
								found = true;
								break;
							}
						}
					}
				}
				if (!found) {
					roomDropdown.value = '';
					assignedText = '<span class="text-danger">No available room can accommodate this family size.</span>';
				}
			}
			if (assignedRoomDisplay) {
				assignedRoomDisplay.innerHTML = assignedText;
				assignedRoomDisplay.style.display = assignedText ? 'block' : 'none';
			}
		}

		// Also update display if user manually changes the room
		document.addEventListener('DOMContentLoaded', function() {
			const roomDropdown = document.getElementById('room');
			if (roomDropdown) {
				roomDropdown.addEventListener('change', function() {
					const assignedRoomDisplay = document.getElementById('assignedRoomDisplay');
					let selected = roomDropdown.options[roomDropdown.selectedIndex];
					if (selected && selected.value) {
						assignedRoomDisplay.innerHTML = `Assigned Room: <b>${selected.textContent}</b>`;
						assignedRoomDisplay.style.display = 'block';
					} else {
						assignedRoomDisplay.innerHTML = '';
						assignedRoomDisplay.style.display = 'none';
					}
				});
			}
		});

		if (numMembersInput) {
			numMembersInput.addEventListener('input', function() {
				const count = parseInt(this.value) || 0;
				familyFieldsDiv.innerHTML = '';
				for (let i = 1; i <= count; i++) {
					familyFieldsDiv.innerHTML += `
					<div class="card mb-2 p-2">
						<h6>Member #${i}</h6>
						<div class="row">
							<div class="col-md-4 mb-2">
								<input type="text" class="form-control" name="member_fname_${i}" placeholder="First Name" required>
							</div>
							<div class="col-md-4 mb-2">
								<input type="text" class="form-control" name="member_mname_${i}" placeholder="Middle Name" required>
							</div>
							<div class="col-md-4 mb-2">
								<input type="text" class="form-control" name="member_lname_${i}" placeholder="Last Name" required>
							</div>
						</div>
						<div class="row">
							<div class="col-md-4 mb-2">
								<input type="date" class="form-control" name="member_dob_${i}" placeholder="Date of Birth" required>
							</div>
							<div class="col-md-4 mb-2">
								<select class="form-control" name="member_gender_${i}" required>
									<option value="">Gender</option>
									<option value="Male">Male</option>
									<option value="Female">Female</option>
								</select>
							</div>
							<div class="col-md-4 mb-2">
								<input type="text" class="form-control" name="member_relation_${i}" placeholder="Relation to Head" required>
							</div>
						</div>
					</div>
				`;
				}
				// Initial auto-select after fields are rendered
				autoSelectRoom();
				// Add real-time auto-select on all member fields
				setTimeout(() => {
					const memberInputs = familyFieldsDiv.querySelectorAll('input, select');
					memberInputs.forEach(inp => {
						inp.addEventListener('input', autoSelectRoom);
						inp.addEventListener('change', autoSelectRoom);
					});
				}, 0);
			});
		}
	});
</script>
<script>
	function toggleVisibility(fieldId, iconSpan) {
		const input = document.getElementById(fieldId);
		const icon = iconSpan.querySelector('i');
		if (input.type === 'password') {
			input.type = 'text';
			icon.classList.remove('fa-eye-slash');
			icon.classList.add('fa-eye');
		} else {
			input.type = 'password';
			icon.classList.remove('fa-eye');
			icon.classList.add('fa-eye-slash');
		}
	}
</script>
<script>
	document.addEventListener('DOMContentLoaded', function() {
		const regType = document.getElementById('registration_type');
		const familySection = document.getElementById('familyMembersSection');
		const numMembersInput = document.getElementById('numFamilyMembers');
		const familyFieldsDiv = document.getElementById('familyMembersFields');

		if (regType) {
			regType.addEventListener('change', function() {
				if (this.value === 'Family') {
					familySection.style.display = 'block';
				} else {
					familySection.style.display = 'none';
					familyFieldsDiv.innerHTML = '';
					if (numMembersInput) numMembersInput.value = '';
				}
			});
		}

		if (numMembersInput) {
			numMembersInput.addEventListener('input', function() {
				const count = parseInt(this.value) || 0;
				familyFieldsDiv.innerHTML = '';
				for (let i = 1; i <= count; i++) {
					familyFieldsDiv.innerHTML += `
					<div class="card mb-2 p-2">
						<h6>Member #${i}</h6>
						<div class="row">
							<div class="col-md-4 mb-2">
								<input type="text" class="form-control" name="member_fname_${i}" placeholder="First Name" required>
							</div>
							<div class="col-md-4 mb-2">
								<input type="text" class="form-control" name="member_mname_${i}" placeholder="Middle Name" required>
							</div>
							<div class="col-md-4 mb-2">
								<input type="text" class="form-control" name="member_lname_${i}" placeholder="Last Name" required>
							</div>
						</div>
						<div class="row">
							<div class="col-md-4 mb-2">
								<input type="date" class="form-control" name="member_dob_${i}" placeholder="Date of Birth" required>
							</div>
							<div class="col-md-4 mb-2">
								<select class="form-control" name="member_gender_${i}" required>
									<option value="">Gender</option>
									<option value="Male">Male</option>
									<option value="Female">Female</option>
								</select>
							</div>
							<div class="col-md-4 mb-2">
								<input type="text" class="form-control" name="member_relation_${i}" placeholder="Relation to Head" required>
							</div>
						</div>
					</div>
				`;
				}

				// After inserting fields, set up validation and auto-select behavior
				autoSelectRoom();
				setupMemberValidation();
			});

			// Validate duplicate names among members and against the head
			function setupMemberValidation() {
				const submitBtn = document.getElementById('submitBtn');
				const count = parseInt(numMembersInput.value) || 0;

				// Attach listeners to member inputs
				for (let i = 1; i <= count; i++) {
					const fname = document.querySelector(`[name='member_fname_${i}']`);
					const mname = document.querySelector(`[name='member_mname_${i}']`);
					const lname = document.querySelector(`[name='member_lname_${i}']`);
					if (fname) fname.addEventListener('input', validateDuplicates);
					if (mname) mname.addEventListener('input', validateDuplicates);
					if (lname) lname.addEventListener('input', validateDuplicates);
				}

				// Attach listeners to head name fields so changes trigger re-validation in real-time
				const headFirstEl = document.getElementById('f_name');
				const headMiddleEl = document.getElementById('m_name');
				const headLastEl = document.getElementById('l_name');
				[headFirstEl, headMiddleEl, headLastEl].forEach(h => {
					if (h) h.addEventListener('input', validateDuplicates);
				});

				// Perform initial validation
				validateDuplicates();

				function validateDuplicates() {
					// Clear previous feedback
					const feedbackEls = familyFieldsDiv.querySelectorAll('.duplicate-feedback');
					feedbackEls.forEach(el => el.remove());

					// Remove invalid classes
					const allMemberInputs = familyFieldsDiv.querySelectorAll("input[name^='member_fname_'], input[name^='member_mname_'], input[name^='member_lname_']");
					allMemberInputs.forEach(inp => inp.classList.remove('is-invalid'));

					const seen = {}; // key -> array of indexes
					let hasDup = false;

					for (let i = 1; i <= count; i++) {
						const fnEl = document.querySelector(`[name='member_fname_${i}']`);
						const mnEl = document.querySelector(`[name='member_mname_${i}']`);
						const lnEl = document.querySelector(`[name='member_lname_${i}']`);
						const fn = normalize(fnEl ? fnEl.value : '');
						const mn = normalize(mnEl ? mnEl.value : '');
						const ln = normalize(lnEl ? lnEl.value : '');

						// Skip empty rows
						if (!fn && !mn && !ln) continue;

						const key = `${fn}|${mn}|${ln}`;

						// Check against head (re-read head fields each time for real-time validation)
						const headFirst = normalize(document.getElementById('f_name') ? document.getElementById('f_name').value : '');
						const headMiddle = normalize(document.getElementById('m_name') ? document.getElementById('m_name').value : '');
						const headLast = normalize(document.getElementById('l_name') ? document.getElementById('l_name').value : '');
						if (headFirst || headMiddle || headLast) {
							if (fn === headFirst && mn === headMiddle && ln === headLast) {
								hasDup = true;
								[fnEl, mnEl, lnEl].forEach(el => el && el.classList.add('is-invalid'));
								attachFeedback(fnEl || mnEl || lnEl, 'Duplicate of head name');
							}
						}

						if (seen[key]) {
							// mark both current and previous entries as duplicates
							hasDup = true;
							[fnEl, mnEl, lnEl].forEach(el => el && el.classList.add('is-invalid'));
							// mark previous ones
							seen[key].forEach(prevIdx => {
								const pfn = document.querySelector(`[name='member_fname_${prevIdx}']`);
								const pmn = document.querySelector(`[name='member_mname_${prevIdx}']`);
								const pln = document.querySelector(`[name='member_lname_${prevIdx}']`);
								[pfn, pmn, pln].forEach(el => el && el.classList.add('is-invalid'));
								attachFeedback(pfn || pmn || pln, 'Duplicate name among members');
							});
							attachFeedback(fnEl || mnEl || lnEl, 'Duplicate name among members');
							seen[key].push(i);
						} else {
							seen[key] = [i];
						}
					}

					// Disable submit if duplicates exist
					const submit = document.getElementById('submitBtn');
					if (submit) submit.disabled = hasDup;
				}

				function attachFeedback(el, msg) {
					if (!el) return;
					// Find the name inputs row to place feedback below the name fields
					const nameRow = el.closest('.row');
					const container = nameRow && nameRow.parentElement ? nameRow.parentElement : el.parentElement;
					if (!container) return;
					// Don't create duplicate feedback nodes
					let fb = container.querySelector('.duplicate-feedback');
					if (!fb) {
						fb = document.createElement('div');
						fb.className = 'form-text text-danger duplicate-feedback';
						fb.style.marginTop = '4px';
						// Insert after the name row if possible
						if (nameRow && nameRow.nextSibling) {
							nameRow.parentElement.insertBefore(fb, nameRow.nextSibling);
						} else if (nameRow) {
							nameRow.parentElement.appendChild(fb);
						} else {
							container.appendChild(fb);
						}
					}
					fb.textContent = msg;
				}
			}
		}
	});
</script>
<script>
	document.addEventListener("DOMContentLoaded", function() {
		const inputField = document.getElementById("idpName");
		const suggestionsDiv = document.getElementById("nameSuggestions");
		const preRegIdField = document.getElementById("preRegId");

		// Function to fetch suggestions from database via AJAX
		function fetchSuggestions(query) {
			return new Promise((resolve, reject) => {
				if (query.length < 2) { // Don't search for very short queries
					resolve([]);
					return;
				}

				$.ajax({
					url: '../fetch_data/fetch_idps_staff.php', // Create this file
					method: 'GET',
					data: {
						query: query
					},
					dataType: 'json',
					success: function(data) {
						resolve(data);
					},
					error: function(xhr, status, error) {
						console.error("Error fetching suggestions:", error);
						resolve([]);
					}
				});
			});
		}

		// Function to display suggestions
		function displaySuggestions(suggestions) {
			suggestionsDiv.innerHTML = "";
			const registerBtn = document.getElementById("registerBtn");
			const warning = document.getElementById("noMatchWarning");

			if (suggestions.length === 0) {
				const noResultItem = document.createElement("button");
				noResultItem.className = "list-group-item list-group-item-action disabled";
				noResultItem.textContent = "No IDPs found";
				noResultItem.disabled = true;
				suggestionsDiv.appendChild(noResultItem);
				suggestionsDiv.style.display = "block";

				// Show warning
				warning.classList.remove("d-none");

				// Disable the Register button
				registerBtn.disabled = true;
				return;
			}

			// Hide warning if there are suggestions
			warning.classList.add("d-none");

			// Disable Register until one is selected
			registerBtn.disabled = true;

			suggestions.forEach(suggestion => {
				const suggestionItem = document.createElement("button");
				suggestionItem.className = "list-group-item list-group-item-action";
				suggestionItem.textContent = suggestion.name;

				suggestionItem.addEventListener("click", () => {
					inputField.value = suggestion.name;
					if (suggestion.id) {
						preRegIdField.value = suggestion.id;
					}
					suggestionsDiv.style.display = "none";

					// Enable Register
					registerBtn.disabled = false;
					warning.classList.add("d-none");
				});

				suggestionsDiv.appendChild(suggestionItem);
			});

			suggestionsDiv.style.display = "block";
		}


		// Debounce function to limit API calls
		function debounce(func, wait) {
			let timeout;
			return function(...args) {
				clearTimeout(timeout);
				timeout = setTimeout(() => func.apply(this, args), wait);
			};
		}

		// Debounced input handler
		const handleInput = debounce(async function() {
			const query = inputField.value.trim();
			if (query.length === 0) {
				suggestionsDiv.style.display = "none";
				return;
			}

			const suggestions = await fetchSuggestions(query);
			displaySuggestions(suggestions);
		}, 300);

		// Event listener for input changes
		inputField.addEventListener("input", handleInput);

		// Hide suggestions when clicking outside
		document.addEventListener("click", function(event) {
			if (!suggestionsDiv.contains(event.target) && event.target !== inputField) {
				suggestionsDiv.style.display = "none";
			}
		});
	});
</script>

<style>
	#nameSuggestions {
		background-color: white;
		border: 1px solid #ced4da;
		border-top: none;
	}

	#nameSuggestions button {
		cursor: pointer;
		text-align: left;
		border-radius: 0 !important;
		border-left: none;
		border-right: none;
		padding: 8px 12px;
	}

	#nameSuggestions button:hover {
		background-color: #f8f9fa;
	}

	#nameSuggestions button:active {
		background-color: #e2e6ea;
	}
</style>
<!-- for the room dropdown -->
<script>
	document.addEventListener("DOMContentLoaded", function() {
		const roomDropdown = document.getElementById("room");

		async function fetchRooms(locationId) {
			try {
				const response = await fetch(`../fetch_data/fetch_room_staff.php?location_id=${locationId}`);
				if (!response.ok) throw new Error("Failed to fetch rooms");
				const rooms = await response.json();

				roomDropdown.innerHTML = '<option value="" disabled selected>Select a room</option>';

				rooms.forEach(room => {
					const option = document.createElement("option");
					option.value = room.id;
					// Display format: "Room Name (Occupied/Capacity)"
					option.textContent = `${room.name} (${room.current_occupancy}/${room.capacity})`;

					// Disable option if room is full
					if (room.current_occupancy >= room.capacity) {
						option.disabled = true;
						option.textContent += " - FULL";
					}

					roomDropdown.appendChild(option);
				});
			} catch (error) {
				console.error("Error fetching rooms:", error);
				roomDropdown.innerHTML = '<option value="" disabled selected>Error loading rooms</option>';
			}
		}

		const locationId = document.querySelector("input[name='location_id']").value;
		if (locationId) {
			fetchRooms(locationId);
		} else {
			roomDropdown.innerHTML = '<option value="" disabled selected>No location selected</option>';
		}
	});
</script>
<div class="modal fade" id="registerChoiceModal" aria-hidden="true" tabindex="-1">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="registerChoiceModalLabel">Register IDP</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<!-- Button for Manual Registration -->
				<button type="button"
					class="btn btn-success w-100 mb-2"
					id="manualRegistrationBtn"
					data-bs-toggle="modal"
					data-bs-target="#registerIDPModal">
					<i class="fas fa-user-plus me-2"></i> Manual Registration
				</button>
				<!-- Button for Scanning QR Code -->
				<button type="button" class="btn btn-info w-100" id="scanQRBtn">
					<i class="fas fa-qrcode me-2"></i> Scan QR Code
				</button>
			</div>
		</div>
	</div>
</div>

<!-- QR Scanner Modal -->
<div class="modal fade" id="qrScannerModal" tabindex="-1" aria-labelledby="qrScannerModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<div class="modal-header bg-primary text-white">
				<h5 class="modal-title" id="qrScannerModalLabel">Family Member Registration</h5>
				<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-6">
						<!-- QR Scanner Section -->
						<div class="card mb-4">
							<div class="card-header bg-info text-white">
								<h6 class="mb-0"><i class="fas fa-qrcode me-2"></i>Scan QR Code</h6>
							</div>
							<div class="card-body text-center">
								<div id="qrScanner" style="width: 100%; height: 300px; border: 2px dashed #ccc;"></div>
								<div class="mt-3">
									<button id="startScannerBtn" class="btn btn-primary me-2">
										<i class="fas fa-play me-1"></i> Start Scanner
									</button>
									<button id="stopScannerBtn" class="btn btn-danger" disabled>
										<i class="fas fa-stop me-1"></i> Stop
									</button>
								</div>
								<div id="scannerStatus" class="mt-2 small text-muted"></div>
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="container-fluid">
							<div class="row g-3">
								<div class="col-12">
									<div class="id-card family-card">
										<div class="card-header">
											<div class="card-title">KANLAON EVACUATION PLAN</div>
											<div class="card-subtitle">BAKWIT CARD</div>
											<!-- <div class="registration-type">FAMILY</div> -->
										</div>

										<div class="form-section">
											<table class="form-table">
												<tr>
													<td>
														HOUSEHOLD HEAD:
														<span class="form-label-local">(PANGULO SANG PANIMALAY)</span>
													</td>
													<td id="householdHeadCell"></td>
												</tr>
												<tr>
													<td>
														NO. OF HOUSEHOLD MEMBER:
														<span class="form-label-local">(KADAMUON/KADAGHANON SA PANIMALAY)</span>
													</td>
													<td id="memberCountCell"></td>
												</tr>
												<tr>
													<td>
														ADDRESS:
														<span class="form-label-local">(PULOY-AN/PUY-ANAN)</span>
													</td>
													<td id="addressCell"></td>
												</tr>
												<tr>
													<td>
														COLLECTION POINT/PICKUP POINT:
														<span class="form-label-local">(TILIPUNAN PARA SA BAKWIT)</span>
													</td>
													<td id="collectionPointCell"></td>
												</tr>
												<tr>
													<td>
														ASSIGNED EVACUATION CENTER:
														<span class="form-label-local">(GINTALANA NGA EVACUATION CENTER)</span>
													</td>
													<td id="evacuationCenterCell"></td>
												</tr>
												<tr>
													<td>
														PHONE NUMBER OF FAMILY LEADER:
														<span class="form-label-local">(NUMERO SA SELPON SANG PANGULO SANG PANIMALAY)</span>
													</td>
													<td id="phoneNumberCell"></td>
												</tr>
												<tr>
													<td>
														PERSONS WITH SPECIAL NEEDS:
														<span class="form-label-local">(MIYEMBRO NGA MAY ESPESYAL NGA PANGINAHANGLANON)</span>
													</td>
													<td>N/A</td>
												</tr>
												<tr>
													<td>STAYING IN CENTER?</td>
													<td>
														<div class="checkbox-group" style="display: flex; gap: 20px; align-items: center;">
															<div class="checkbox-item">
																<div class="checkbox-box checked"></div>
																<span>YES</span>
															</div>
															<div class="checkbox-item">
																<div class="checkbox-box"></div>
																<span>NO</span>
															</div>
															<div style="border-left: 2px solid #000; height: 40px; margin: 0 10px;"></div>
															<span>QR CODE:</span>
															<img id="qrCodeImg" src="../../../qrcodes/default.png" alt="QR Code" style="width: 80px; height: 80px;">
														</div>
													</td>
												</tr>
											</table>
										</div>
										<div class="authority-section">
											<div class="logo-placeholder">
												Place LGU logo here
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
													<div class="authority-name">OFFICE OF CIVIL DEFENSE NIR</div>
													<div class="authority-line">
														<span class="authority-phone">09956112342 / 09177040134</span>
													</div>
												</div>
											</div>
										</div>

										<!-- Footer -->
										<div class="footer">
											<div class="footer-content" style="border-radius:30px">
												<div class="footer-text">REGIONAL TASK FORCE KANLAON</div>
												<div class="volcano-logo"></div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<!-- <div class="col-md-6"> -->
						<!-- Family Member Information -->


						<!-- </div> -->

					</div>
					<div class="card">
						<div class="card-header bg-success text-white">
							<h6 class="mb-0"><i class="fas fa-users me-2"></i>Family Members</h6>
						</div>
						<div class="card-body">
							<div id="familyInfo" class="text-center py-4">
								<i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
								<p class="text-muted">No family member scanned yet</p>
							</div>
							<div id="familyList" style="max-height: 300px; overflow-y: auto; display: none;">
								<!-- Dynamic content will appear here -->
							</div>
						</div>
					</div>
				</div>
				<!-- Selected Family Members -->
				<div class="card mt-3">
					<div class="card-header bg-warning">
						<h6 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Selected for Registration</h6>
					</div>
					<div class="card-body">
						<div id="selectedFamily" class="alert alert-info mb-0">
							No family members selected yet
						</div>
						<div class="mt-3">
							<label class="form-label">Assign to Room:</label>
							<select id="familyRoomAssignment" class="form-select" disabled>
								<option value="">Select room after scanning</option>
							</select>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
				<button type="button" id="registerFamilyBtn" class="btn btn-success" disabled>
					<i class="fas fa-user-plus me-1"></i> Register Selected Members
				</button>
			</div>
		</div>
	</div>
</div>

<!-- JavaScript for QR Scanner Functionality -->
<script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.4/dist/html5-qrcode.min.js"></script>

<?php include '../scripts/scanner.php'; ?>
<?php include '../css/scanner_ui.php'; ?>

<script>
	document.addEventListener("DOMContentLoaded", function() {
		// Utility to get location_id from hidden input
		function getLocationId() {
			const input = document.querySelector("input[name='location_id']");
			return input ? input.value : null;
		}

		// Utility to check if location name is shown
		function isLocationNameShown() {
			const locationAlert = document.querySelector('.alert.alert-info.mb-3');
			if (!locationAlert) return false;
			const text = locationAlert.textContent || "";
			// Check for fallback messages or empty
			if (
				text.includes("No location selected") ||
				text.includes("Unknown Location") ||
				text.trim() === ""
			) {
				return false;
			}
			return true;
		}

		// Prevent opening registerChoiceModal if location is empty or name not shown
		const registerChoiceModal = document.getElementById('registerChoiceModal');
		if (registerChoiceModal) {
			registerChoiceModal.addEventListener('show.bs.modal', function(e) {
				if (!getLocationId() || !isLocationNameShown()) {
					e.preventDefault();
					Swal.fire({
						icon: 'warning',
						title: 'No Location Selected',
						text: 'Please select a location first.',
						confirmButtonColor: '#3085d6'
					});
					return false;
				}
			});
		}
	});
</script>

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
</style>


<script>
	document.addEventListener('DOMContentLoaded', function() {
		var disasterSelect = document.getElementById('disasterSelect');
		var form = document.getElementById('idpRegistrationForm');
		if (disasterSelect) {
			disasterSelect.addEventListener('change', function() {
				var selectedValue = disasterSelect.value;
				// If the selected value is empty or the 'No disaster events found' option
				if (!selectedValue || selectedValue === '' || disasterSelect.options[disasterSelect.selectedIndex].text === 'No disaster events found') {
					Swal.fire({
						icon: 'warning',
						title: 'No Disaster Selected',
						text: 'Please select a valid disaster event before proceeding.'
					});
					disasterSelect.selectedIndex = 0; // Reset to default
				}
			});
		}
		if (form) {
			form.addEventListener('submit', function(e) {
				var selectedValue = disasterSelect ? disasterSelect.value : '';
				if (!selectedValue || selectedValue === '' || (disasterSelect && disasterSelect.options[disasterSelect.selectedIndex].text === 'No disaster events found')) {
					e.preventDefault();
					Swal.fire({
						icon: 'warning',
						title: 'No Disaster Selected',
						text: 'Please select a valid disaster event before submitting the form.'
					});
					return false;
				}
			});
		}
	});
</script>
<script>
	document.addEventListener('DOMContentLoaded', function() {
		var form = document.getElementById('idpRegistrationForm');
		if (form) {
			form.addEventListener('submit', function(e) {
				e.preventDefault();
				var formData = new FormData(form);
				fetch(form.action, {
						method: 'POST',
						body: formData
					})
					.then(response => response.json())
					.then(data => {
						if (data.success) {
							Swal.fire({
								icon: 'success',
								title: 'Success',
								text: data.message
							}).then(() => {
								form.reset();
								// Optionally, reset custom UI fields (e.g., previews, dynamic fields)
								var assignedRoomDisplay = document.getElementById('assignedRoomDisplay');
								if (assignedRoomDisplay) {
									assignedRoomDisplay.innerHTML = '';
									assignedRoomDisplay.style.display = 'none';
								}
								var profilePicPreview = document.getElementById('profilePicPreview');
								if (profilePicPreview) {
									profilePicPreview.classList.add('d-none');
								}
								var signaturePreview = document.getElementById('signaturePreview');
								if (signaturePreview) {
									signaturePreview.src = '#';
									signaturePreview.style.display = 'none';
								}
								// Reset family members section if present
								var familySection = document.getElementById('familyMembersSection');
								var familyFieldsDiv = document.getElementById('familyMembersFields');
								var numMembersInput = document.getElementById('numFamilyMembers');
								if (familySection && familyFieldsDiv && numMembersInput) {
									familySection.style.display = 'none';
									familyFieldsDiv.innerHTML = '';
									numMembersInput.value = '';
								}
								// Reset room dropdown
								var roomDropdown = document.getElementById('room');
								if (roomDropdown) {
									roomDropdown.selectedIndex = 0;
								}
								// Reset disaster event display
								var disasterEventName = document.getElementById('disasterEventName');
								if (disasterEventName) {
									disasterEventName.textContent = 'No disaster selected';
								}
								var disasterIdHidden = document.getElementById('disasterIdHidden');
								if (disasterIdHidden) {
									disasterIdHidden.value = '';
								}
							});
						} else {
							Swal.fire({
								icon: 'error',
								title: 'Error',
								text: data.message
							});
						}
					})
					.catch(error => {
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: 'An error occurred while submitting the form.'
						});
					});
			});
		}
	});
</script>