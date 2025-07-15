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

				<img src="../../../src/images/logo/images.png" alt="Logo"
					class="img-fluid d-block mx-auto mb-3 logo-img"
					style="max-width: 100px;">
				<!-- Make sure "Register" is always visible -->
				<h3 class="text-center text-primary fw-bold">Pre-Registration</h3>

				<form id="registrationForm" method="POST" action="../action/auth_action/user_pre_reg.php" enctype="multipart/form-data">
					<div class="row">
						<div class="col-md-3">
							<div class="mb-3">
								<label class="form-label">First Name <span class="text-danger">*</span></label>
								<input type="text" name="f_name" id="f_name" class="form-control" placeholder="Enter First Name" required>
							</div>
						</div>
						<div class="col-md-3">
							<div class="mb-3">
								<label class="form-label">Middle Name</label>
								<input type="text" name="m_name" id="m_name" class="form-control" placeholder="Enter Middle Name" required>
							</div>
						</div>
						<div class="col-md-3">
							<div class="mb-3">
								<label class="form-label">Last Name</label>
								<input type="text" name="l_name" id="l_name" class="form-control" placeholder="Enter Last Name" required>
							</div>
						</div>
						<div class="col-md-3">
							<div class="mb-2">
								<label class="form-label">Name Extension</label>
								<select class="form-control" name="name_extension" id="name_extension">
									<option value="" disabled selected>-- Select Extension --</option>
									<option value="jr">Jr.</option>
									<option value="sr">Sr.</option>
									<option value="i">I</option>
									<option value="ii">II</option>
									<option value="iii">III</option>
								</select>
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-4">
							<div class="mb-3">
								<label class="form-label">Contact No.</label>
								<input type="number" name="contact_no" id="contact_no" class="form-control" placeholder="Enter Contact No." required pattern="[0-9]{10,15}">
								<small id="contactError" class="text-danger"></small>
							</div>
						</div>
						<div class="col-md-4">
							<div class="mb-3">
								<label class="form-label">Email Address</label>
								<input type="email" name="email" id="email" class="form-control" placeholder="Enter Email" required>
								<small id="emailFeedback"></small>
							</div>
						</div>
						<div class="col-md-4">
							<div class="mb-3">
								<label class="form-label">Highest Education Attainment</label>
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
								<label class="form-label">Password</label>
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
								<label class="form-label">Confirm Password</label>
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
								<label class="form-label">Signature</label>
								<!-- Signature Option Selector -->
								<!-- <div class="form-check">
									<input class="form-check-input" type="radio" name="signature_option" id="option_draw" value="draw" checked onchange="toggleSignatureInput()">
									<label class="form-check-label" for="option_draw">Draw Signature</label>
								</div> -->
								<!-- <div class="form-check">
									<input class="form-check-input" type="radio" name="signature_option" id="option_upload" value="upload" onchange="toggleSignatureInput()">
									<label class="form-check-label" for="option_upload">Upload Signature</label>
								</div> -->
								<!-- Draw Signature Canvas -->
								<!-- <div id="signature-draw" class="mt-2">
									<canvas id="signature-pad" style="width: 100%; height: 150px; border: 1px solid #ccc;"></canvas>
									<input type="hidden" name="signature_data" id="signature_data">
									<div class="mt-2">
										<button type="button" class="btn btn-sm btn-secondary" onclick="clearSignature()">Clear Signature</button>
									</div>
								</div> -->
								<!-- Upload Signature File -->
								<div id="signature-upload" class="mt-0">
									<input type="file" name="signature_file" id="signature_file" class="form-control" accept="image/*">
								</div>
							</div>
						</div>

						<div class="col-md-4">
							<div class="mb-3">
								<label class="form-label">Date of Birth</label>
								<input type="date" name="dob" id="dob" class="form-control" required>
							</div>
						</div>
						<div class="col-md-4">
							<div class="mb-3">
								<label class="form-label">Place Of Birth</label>
								<input type="text" name="pob" id="pob" class="form-control" placeholder="Enter Place of Birth" required>
							</div>
						</div>
						<div class="col-md-4">
							<div class="mb-3">
								<label class="form-label">Mother Maiden Name</label>
								<input type="text" name="mmn" id="mmn" class="form-control" placeholder="Enter Mother Maiden Name" required>
							</div>
						</div>
						<div class="col-md-4">
							<div class="mb-3">
								<label for="religion" class="form-label">Religion</label>
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
								<input type="text" name="occupation" id="occupation" class="form-control" placeholder="Enter Occupation" required>
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
								<label class="form-label">Gender</label>
								<select name="gender" id="gender" class="form-control" required>
									<option value="" disabled selected>-- Select Gender --</option>
									<option value="Male">Male</option>
									<option value="Female">Female</option>
								</select>
							</div>
						</div>
						<div class="col-md-4">
							<div class="mb-3">
								<label class="form-label">Registration Type</label>
								<select name="registration_type" id="registration_type" class="form-control" required>
									<option value="" disabled selected>-- Select Registration Type --</option>
									<option value="Solo">Solo</option>
									<option value="Family">Family</option>
								</select>
							</div>
						</div>
						<div class="col-md-4">
							<div class="mb-3">
								<label class="form-label">Civil Status</label>
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
								<label for="icp" class="form-label">ID Card Presented</label>
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
								<label class="form-label">ID Card Number</label>
								<input type="text" name="icn" id="icn" class="form-control" placeholder="Enter ID Card Number" required>
							</div>
						</div>
						<!-- ID Upload Input -->
						<div class="col-md-4">
							<div class="mb-3">
								<label class="form-label fw-semibold">Upload Image of ID Card Presented</label>
								<input type="file" name="ic_image" id="ic_image" class="form-control" accept="image/*" required>
							</div>
						</div>

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
										</div>
									</div>
								</div>
							</div>
						</div>

						<!-- <script src="https://cdn.jsdelivr.net/npm/tesseract.js@2.1.5/dist/tesseract.min.js"></script> -->
						<!-- SweetAlert2 CSS -->
						<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
						<!-- SweetAlert2 JS -->
						<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

						<script src="tesseract.js"></script>
						<script>
							document.getElementById('ic_image').addEventListener('change', function() {
								const file = this.files[0];
								const fname = document.getElementById('f_name').value.trim().toLowerCase();
								const lname = document.getElementById('l_name').value.trim().toLowerCase();

								if (!file || !fname || !lname) {
									Swal.fire({
										icon: 'warning',
										title: 'Missing Input',
										text: 'Please enter both first and last name before uploading the ID.'
									});
									this.value = "";
									return;
								}

								// Show loading modal
								Swal.fire({
									title: 'Scanning ID...',
									html: 'Please wait while we extract text from the image.<br><b>This may take a few seconds.</b>',
									allowOutsideClick: false,
									didOpen: () => {
										Swal.showLoading();
									}
								});

								Tesseract.recognize(file, 'eng', {
									logger: m => {
										if (m.status === "recognizing text") {
											Swal.update({
												html: `Recognizing text... <b>${Math.round(m.progress * 100)}%</b>`
											});
										}
									}
								}).then(({
									data: {
										text
									}
								}) => {
									const lowerText = text.toLowerCase();
									const fnameMatch = lowerText.includes(fname);
									const lnameMatch = lowerText.includes(lname);

									if (fnameMatch && lnameMatch) {
										Swal.fire({
											icon: 'success',
											title: 'Match Found',
											text: '✅ Name matched successfully!',
											confirmButtonColor: '#198754'
										});
									} else {
										Swal.fire({
											icon: 'error',
											title: 'Name Mismatch',
											text: '❌ Name on the ID does not match the input.',
											confirmButtonColor: '#dc3545'
										});
										document.getElementById('ic_image').value = "";
									}
								}).catch(err => {
									console.error(err);
									Swal.fire({
										icon: 'error',
										title: 'OCR Error',
										text: '⚠️ Error processing the image. Please try again.',
										confirmButtonColor: '#dc3545'
									});
								});
							});
						</script>


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
								<label class="form-label">Region</label>
								<input type="text" name="region" id="region" class="form-control" value="Region VI (Western Visayas)" readonly>
							</div>
						</div>
						<div class="col-md-4">
							<div class="mb-3">
								<label class="form-label">Province</label>
								<input type="text" name="province" id="province" class="form-control" value="Negros Occidental" readonly>
							</div>
						</div>
						<div class="col-md-4">
							<div class="mb-3">
								<label class="form-label">City</label>
								<input type="text" name="city" id="city" class="form-control" value="Bago City" readonly>
							</div>
						</div>
						<div class="col-md-4">
							<div class="mb-3">
								<label class="form-label">District</label>
								<input type="text" name="district" id="district" class="form-control" value="4th district" readonly>
							</div>
						</div>
						<div class="col-md-4">
							<div class="mb-3">
								<label class="form-label">Barangay</label>
								<select name="barangay" id="barangay" class="form-control" required>
									<option value="">Select Barangay</option>
								</select>
							</div>
						</div>
						<div class="col-md-4">
							<div class="mb-3">
								<label class="form-label">House Block Number</label>
								<input type="text" name="block_number" id="block_number" class="form-control" placeholder="Enter Block Number" required>
							</div>
						</div>
						<div class="col-md-4">
							<div class="mb-3">
								<label class="form-label">Street</label>
								<input type="text" name="street" id="street" class="form-control" placeholder="Enter street" required>
							</div>
						</div>
						<div class="col-md-4">
							<div class="mb-3">
								<label class="form-label">Subdivision Village</label>
								<input type="text" name="sub_div" id="sub_div" class="form-control" placeholder="Enter Subdivision Village" required>
							</div>
						</div>
						<div class="col-md-4">
							<div class="mb-3">
								<label class="form-label">Zip Code</label>
								<input type="text" name="zip_code" id="zip_code" class="form-control" placeholder="Enter Zip Code" required>
							</div>
						</div>
						<div class="col-md-6">
							<div class="mb-3">
								<label class="form-label">Purok</label>
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
								<input type="text" name="wallet" id="wallet" class="form-control" placeholder="Enter Bank/E-Wallet" required>
							</div>
						</div>
						<div class="col-md-6">
							<div class="mb-3">
								<label class="form-label">Account Name</label>
								<input type="text" name="account_name" id="account_name" class="form-control" placeholder="Enter Account Name" required>
							</div>
						</div>
						<div class="col-md-6">
							<div class="mb-3">
								<label class="form-label">Account Type</label>
								<input type="text" name="account_type" id="account_type" class="form-control" placeholder="Enter Account Type" required>
							</div>
						</div>
						<div class="col-md-6">
							<div class="mb-3">
								<label class="form-label">Account Number</label>
								<input type="number" name="account_number" id="account_number" class="form-control" placeholder="Enter Account Number" required>
							</div>
						</div>
					</div>
					<!-- Responsive Button Layout -->
					<div class="d-grid gap-2">
						<button type="submit" id="submitBtn" class="btn btn-success" disabled>Submit</button>
						<button type="button" class="btn btn-secondary btn-lg btn-sm-mobile" onclick="window.location.href='../auth/log_in.php'">Back</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	<script src="../scripts/auth_script/user_registration.js"></script>
	<script src="../scripts/auth_script/address_api.js"></script>
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