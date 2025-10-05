<?php include '../layout/head_links.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="../css/auth/user_registration.css">
	<!-- <script src="../scripts/auth_script/user_registration.js"></script> -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
	<title>Registration Form</title>
</head>
<style>
	label {
		font: bold 14px Arial, sans-serif;
	}
</style>

<body class="d-flex justify-content-center align-items-center bg-light">
	<!-- Toast Notification -->
	<div id="notificationToast" class="toast position-fixed top-0 start-50 translate-middle-x mt-3 text-white bg-danger" role="alert" aria-live="assertive" aria-atomic="true">
		<div class="d-flex">
			<div class="toast-body" id="toastMessage"></div>
			<button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
		</div>
	</div>
	<?php include '../alert/warning.php'; ?>

	<div class="container">
		<div class="row justify-content-center">
			<div class="col-12 col-md-12 col-lg-10 p-4 bg-white shadow rounded">

				<img src="../../../src/images/bago_city.png" alt="Logo"
					class="img-fluid d-block mx-auto mb-3 logo-img"
					style="max-width: 100px;">
				<!-- Make sure "Register" is always visible -->
				<h3 class="text-center text-primary fw-bold">Pre-Registration</h3>

				<form id="registrationForm" method="POST" action="../action/auth_action/user_pre_reg.php" enctype="multipart/form-data">
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
								<select name="education_attainment" id="education_attainment" class="form-control">
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
						<script>
							// Set max date to today
							document.addEventListener("DOMContentLoaded", function() {
								const today = new Date().toISOString().split("T")[0];
								document.getElementById("dob").setAttribute("max", today);
							});
						</script>
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
								<label class="form-label">Registration Type <span class="text-danger">*</span></label>
								<select name="registration_type" id="registration_type" class="form-control" required>
									<option value="" disabled selected>-- Select Registration Type --</option>
									<option value="Solo">Solo</option>
									<option value="Family">Family</option>
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
						<!-- Modal for Image Processing -->
						<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
							<div class="modal-dialog modal-dialog-centered">
								<div class="modal-content shadow rounded-4">
									<div class="modal-header bg-primary text-white rounded-top">
										<h5 class="modal-title" id="imageModalLabel">🆔 ID Image Validation</h5>
										<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
									</div>
									<div class="modal-body text-center">
										<!-- Step status -->
										<div id="image-validation-msg" class="fw-bold mb-3 fs-5 text-dark">🔄 Waiting for image...</div>

										<!-- Circular Progress + Spinner/Icon -->
										<div class="circular-progress-container mb-3">
											<svg class="circular-progress" viewBox="0 0 100 100">
												<circle class="bg" cx="50" cy="50" r="45"></circle>
												<circle class="progress" id="circularProgress" cx="50" cy="50" r="45"></circle>
											</svg>
											<div class="center-spinner" id="spinnerWrapper">
												<div class="spinner-border text-primary" id="loadingSpinner" role="status">
													<span class="visually-hidden">Loading...</span>
												</div>
												<div id="successIcon" class="d-none text-success fs-1">✔️</div>
												<div id="errorIcon" class="d-none text-danger fs-1">❌</div>
											</div>
											<canvas id="ocrCanvas" style="display:none;"></canvas>
										</div>
									</div>
								</div>
							</div>
						</div>
						<script src="https://cdn.jsdelivr.net/npm/tesseract.js@2.1.5/dist/tesseract.min.js"></script>
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
									<option value="Undecided">Undecided</option>
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
						<div class="text-center mt-3">
							<small class="text-muted">
								Already have an account?
								<a href="../auth/log_in.php" class="text-decoration-none">Sign In</a>
							</small>
						</div>
					</div>

				</form>
			</div>
		</div>
	</div>
	<script src="../scripts/auth_script/ocr.js"></script>
	<script src="../scripts/auth_script/user_registration.js"></script>
	<script src="../scripts/auth_script/required.js"></script>
	<script src="../scripts/auth_script/address_api.js"></script>
	
	<script>
		// Global variable to store barangay name to ID mapping
		window.barangayNameToId = {};
		
		// Load barangays and handle dependent dropdowns
		document.addEventListener('DOMContentLoaded', function() {
			// Load barangays from database
			loadBarangays();
			
			// Handle barangay and purok dependent dropdowns
			const barangaySelect = document.getElementById('barangay');
			const purokSelect = document.getElementById('purok');
			
			if (barangaySelect && purokSelect) {
				// Barangay change event - load puroks based on selected barangay name
				barangaySelect.addEventListener('change', function() {
					const selectedBarangayName = this.value || '';
					console.log('Barangay selected:', selectedBarangayName);
					
					// Reset purok options
					purokSelect.innerHTML = '<option value="">Select Purok</option>';
					if (!selectedBarangayName) return;
					
					// Get barangay ID from the selected barangay name
					const barangayId = window.barangayNameToId[selectedBarangayName];
					console.log('Barangay ID for', selectedBarangayName, ':', barangayId);
					
					if (!barangayId) {
						console.error('Barangay ID not found for:', selectedBarangayName);
						purokSelect.innerHTML = '<option value="">Barangay ID not found</option>';
						return;
					}
					
					// Show loading state
					purokSelect.innerHTML = '<option value="">Loading puroks...</option>';
					
					// Fetch puroks for the selected barangay ID
					fetch(`../action/brgy_management_action/list_purok.php?barangay_id=${encodeURIComponent(barangayId)}`)
						.then(response => {
							console.log('Purok response status:', response.status);
							return response.json();
						})
						.then(res => {
							console.log('Purok response:', res);
							if (res && res.success && Array.isArray(res.data)) {
								purokSelect.innerHTML = '<option value="">Select Purok</option>';
								res.data.forEach(p => {
									const opt = document.createElement('option');
									opt.value = p.purok_name;
									opt.textContent = p.purok_name;
									opt.dataset.pickupPoint = p.pickup_point_name || '';
									purokSelect.appendChild(opt);
								});
								console.log('Puroks loaded for barangay ID', barangayId, ':', res.data.length);
							} else {
								purokSelect.innerHTML = '<option value="">No puroks found</option>';
								console.error('Invalid purok response format:', res);
							}
						})
						.catch(error => {
							console.error('Error loading puroks:', error);
							purokSelect.innerHTML = '<option value="">Error loading puroks</option>';
						});
				});
				
				// Purok change event - auto-fill pickup point
				purokSelect.addEventListener('change', function() {
					const selectedOption = this.options[this.selectedIndex];
					const pickupPointField = document.getElementById('pickup_name');
					
					if (selectedOption && selectedOption.dataset.pickupPoint && pickupPointField) {
						pickupPointField.value = selectedOption.dataset.pickupPoint;
						console.log('Pickup point auto-filled:', selectedOption.dataset.pickupPoint);
					}
				});
			}
		});
		
		// Load barangays function - Database Driven
		function loadBarangays() {
			const barangaySelect = document.getElementById('barangay');
			
			if (!barangaySelect) {
				console.error('Barangay select element not found');
				return;
			}
			
			// Clear existing options and show loading state
			barangaySelect.innerHTML = '<option value="">Loading barangays from database...</option>';
			barangaySelect.disabled = true;
			
			console.log('Fetching barangays from database...');
			
			// Load barangays from database via API
			fetch('../action/brgy_management_action/list_barangay_map.php', {
				method: 'GET',
				headers: {
					'Content-Type': 'application/json',
					'Cache-Control': 'no-cache'
				}
			})
			.then(response => {
				console.log('Database response status:', response.status);
				if (!response.ok) {
					throw new Error(`Database connection failed! Status: ${response.status}`);
				}
				return response.json();
			})
			.then(res => {
				console.log('Database response received:', res);
				
				// Clear loading state
				barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
				barangaySelect.disabled = false;
				
				// Validate database response
				if (!res || !res.success) {
					console.error('Database query failed:', res.message || 'Unknown error');
					barangaySelect.innerHTML = '<option value="">Database error - Please refresh</option>';
					return;
				}
				
				if (!Array.isArray(res.data)) {
					console.error('Invalid data format from database:', res);
					barangaySelect.innerHTML = '<option value="">Invalid data format</option>';
					return;
				}
				
				if (res.data.length === 0) {
					console.warn('No barangays found in database table');
					barangaySelect.innerHTML = '<option value="">No barangays in database</option>';
					return;
				}
				
				// Populate dropdown with database data and create name-to-ID mapping
				let loadedCount = 0;
				res.data.forEach(row => {
					if (!row || !row.barangay_name || !row.barangay_id) {
						console.warn('Invalid barangay data:', row);
						return;
					}
					
					const option = document.createElement('option');
					option.value = row.barangay_name; // Use barangay name as value
					option.textContent = row.barangay_name;
					option.dataset.barangayId = row.barangay_id; // Store ID as data attribute
					barangaySelect.appendChild(option);
					
					// Store mapping: barangay name -> barangay ID
					window.barangayNameToId[row.barangay_name] = row.barangay_id;
					loadedCount++;
				});
				
				console.log(`✅ Barangays loaded successfully from database: ${loadedCount} items`);
				console.log('Barangay name-to-ID mapping created:', window.barangayNameToId);
				
				// Show success message in console
				if (loadedCount > 0) {
					console.log('🎉 Barangay dropdown is now database-driven!');
				}
			})
			.catch(error => {
				console.error('❌ Database connection error:', error);
				barangaySelect.innerHTML = '<option value="">Database connection failed</option>';
				barangaySelect.disabled = false;
				
				// Show error details
				console.error('Error details:', {
					message: error.message,
					stack: error.stack
				});
			});
		}
	</script>
</body>

</html>

<style>
	.input-group {
		position: relative;
	}

	.input-group input {
		padding-right: 2.5rem;
		/* space for the icon */
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
		/* ensure it's above the input */
	}
</style>