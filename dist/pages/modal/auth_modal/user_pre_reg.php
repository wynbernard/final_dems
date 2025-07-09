<?php
include "../css/modal_pre_reg.php";
include "../scripts/auth_script/user_pre_reg.php";
?>
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<form id="confirmForm" method="POST" action="../action/auth_action/user_pre_reg.php" enctype="multipart/form-data">
				<div class="modal-header">
					<h5 class="modal-title" id="confirmModalLabel">Confirm Your Details</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="info-grid">
						<!-- Personal Information Section -->
						<div class="info-section">
							<div class="section-header">
								<i class="fas fa-user"></i>
								<h3>Personal Information</h3>
							</div>

							<div class="info-item">
								<strong>First Name:</strong>
								<span id="review_f_name">Maria</span>
							</div>

							<div class="info-item">
								<strong>Middle Name:</strong>
								<span id="review_m_name">Santos</span>
							</div>

							<div class="info-item">
								<strong>Last Name:</strong>
								<span id="review_l_name">Cruz</span>
							</div>

							<div class="info-item">
								<strong>Name Extension:</strong>
								<span id="review_name_extension">N/A</span>
							</div>

							<div class="info-item">
								<strong>Gender:</strong>
								<span id="review_gender">Female</span>
							</div>

							<div class="info-item">
								<strong>Date of Birth:</strong>
								<span id="review_dob">May 15, 1985</span>
							</div>

							<div class="info-item">
								<strong>Civil Status:</strong>
								<span id="review_civil_status">Married</span>
							</div>

							<div class="info-item">
								<strong>Religion:</strong>
								<span id="review_religion">Roman Catholic</span>
							</div>

							<div class="info-item">
								<strong>Place of Birth:</strong>
								<span id="review_pob">Manila, Philippines</span>
							</div>

							<div class="info-item">
								<strong>Mother Maiden Name:</strong>
								<span id="review_mmn">Cecilia Reyes</span>
							</div>

							<div class="info-item">
								<strong>Password:</strong>
								<div class="password-container">
									<span id="review_password">********</span>
									<button class="toggle-password">Show</button>
								</div>
							</div>
						</div>

						<!-- Contact & Address Section -->
						<div class="info-section">
							<div class="section-header">
								<i class="fas fa-address-book"></i>
								<h3>Contact & Address</h3>
							</div>

							<div class="info-item">
								<strong>Contact No:</strong>
								<span id="review_contact_no">+63 917 123 4567</span>
							</div>

							<div class="info-item">
								<strong>Email:</strong>
								<span id="review_email">maria.cruz@example.com</span>
							</div>

							<div class="info-item">
								<strong>Region:</strong>
								<span id="review_region">National Capital Region (NCR)</span>
							</div>

							<div class="info-item">
								<strong>Province:</strong>
								<span id="review_province">Metro Manila</span>
							</div>

							<div class="info-item">
								<strong>City:</strong>
								<span id="review_city">Quezon City</span>
							</div>

							<div class="info-item">
								<strong>District:</strong>
								<span id="review_district">4th District</span>
							</div>

							<div class="info-item">
								<strong>Barangay:</strong>
								<span id="review_barangay">Kamuning</span>
							</div>

							<div class="info-item">
								<strong>House Block No.:</strong>
								<span id="review_block_number">45</span>
							</div>

							<div class="info-item">
								<strong>Street:</strong>
								<span id="review_street">Maple Street</span>
							</div>

							<div class="info-item">
								<strong>Subdivision Village:</strong>
								<span id="review_sub_div">Sunrise Village</span>
							</div>

							<div class="info-item">
								<strong>Zip Code:</strong>
								<span id="review_zip_code">1103</span>
							</div>

							<div class="info-item">
								<strong>Purok:</strong>
								<span id="review_purok">7B</span>
							</div>
						</div>

						<!-- Education & Identification Section -->
						<div class="info-section">
							<div class="section-header">
								<i class="fas fa-id-card"></i>
								<h3>Education & Identification</h3>
							</div>

							<div class="info-item">
								<strong>Highest Education Attainment:</strong>
								<span id="review_education_attainment">Bachelor's Degree</span>
							</div>

							<div class="info-item">
								<strong>Occupation:</strong>
								<span id="review_occupation">Marketing Manager</span>
							</div>

							<div class="info-item">
								<strong>Monthly Income:</strong>
								<span id="review_monthly_income">PHP 85,000</span>
							</div>

							<div class="info-item">
								<strong>Registration Type:</strong>
								<span id="review_registration_type">Regular</span>
							</div>

							<div class="info-item">
								<strong>ID Card Presented:</strong>
								<span id="review_icp">Driver's License</span>
							</div>

							<div class="info-item">
								<strong>ID Card Number:</strong>
								<span id="review_icn">DL-987654321</span>
							</div>

							<div class="id-image-container">
								<strong>ID Card Image:</strong>
								<img id="review_id_image" src="#" alt="ID Card Image Preview">
								<p>ID image preview</p>
							</div>

							<div class="info-item">
								<strong>4Ps Beneficiary:</strong>
								<span id="review_benefiicary">No</span>
							</div>

							<div class="info-item">
								<strong>IP's:</strong>
								<span id="review_ip">No</span>
							</div>

							<div class="info-item">
								<strong>Ethnicity:</strong>
								<span id="review_ethnicity">Tagalog</span>
							</div>
						</div>

						<!-- Financial Information Section -->
						<div class="info-section">
							<div class="section-header">
								<i class="fas fa-wallet"></i>
								<h3>Financial Information</h3>
							</div>

							<div class="info-item">
								<strong>Bank/E-Wallet:</strong>
								<span id="review_wallet">BDO</span>
							</div>

							<div class="info-item">
								<strong>Account Name:</strong>
								<span id="review_account_name">Maria Santos Cruz</span>
							</div>

							<div class="info-item">
								<strong>Account Type:</strong>
								<span id="review_account_type">Savings Account</span>
							</div>

							<div class="info-item">
								<strong>Account Number:</strong>
								<span id="review_account_number">**** **** **** 1234</span>
							</div>

							<!-- Hidden Inputs to Submit Data -->
							<input type="hidden" name="f_name" id="hidden_f_name">
							<input type="hidden" name="m_name" id="hidden_m_name">
							<input type="hidden" name="l_name" id="hidden_l_name">
							<input type="hidden" name="name_extension" id="hidden_name_extension">
							<input type="hidden" name="contact_no" id="hidden_contact_no">
							<input type="hidden" name="email" id="hidden_email">
							<input type="hidden" name="education_attainment" id="hidden_education_attainment">
							<input type="hidden" name="pob" id="hidden_pob">
							<input type="hidden" name="mmn" id="hidden_mmn">
							<input type="hidden" name="religion" id="hidden_religion">
							<input type="hidden" name="occupation" id="hidden_occupation">
							<input type="hidden" name="monthly_income" id="hidden_monthly_income">
							<input type="hidden" name="civil_status" id="hidden_civil_status">
							<input type="hidden" name="icp" id="hidden_icp">
							<input type="hidden" name="icn" id="hidden_icn">
							<input type="hidden" name="ic_image" id="hidden_ic_image">
							<input type="hidden" name="beneficiary" id="hidden_beneficiary">
							<input type="hidden" name="ip" id="hidden_ip">
							<input type="hidden" name="ethnicity" id="hidden_ethnicity">
							<input type="hidden" name="region" id="hidden_region">
							<input type="hidden" name="province" id="hidden_province">
							<input type="hidden" name="city" id="hidden_city">
							<input type="hidden" name="district" id="hidden_district">
							<input type="hidden" name="barangay" id="hidden_barangay">
							<input type="hidden" name="block_number" id="hidden_block_number">
							<input type="hidden" name="street" id="hidden_street">
							<input type="hidden" name="sub_div" id="hidden_sub_div">
							<input type="hidden" name="zip_code" id="hidden_zip_code">
							<input type="hidden" name="purok" id="hidden_purok">
							<input type="hidden" name="gender" id="hidden_gender">
							<input type="hidden" name="registration_type" id="hidden_registration_type">
							<input type="hidden" name="dob" id="hidden_dob">
							<input type="hidden" name="password" id="hidden_password">
							<input type="hidden" name="wallet" id="hidden_wallet">
							<input type="hidden" name="account_name" id="hidden_account_name">
							<input type="hidden" name="account_type" id="hidden_account_type">
							<input type="hidden" name="account_number" id="hidden_account_number">
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
					<button type="submit" class="btn btn-success">Confirm</button>
				</div>
			</form>
		</div>
	</div>
</div>