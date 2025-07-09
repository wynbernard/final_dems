<!-- View Family Member Modal -->
<div class="modal fade" id="viewFamilyMemberModal" tabindex="-1" aria-labelledby="viewFamilyMemberModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-lg">
		<div class="modal-content border-0 shadow-lg">
			<!-- Gradient Header -->
			<div class="modal-header bg-gradient-primary text-white rounded-top-4">
				<div class="d-flex align-items-center">
					<i class="fas fa-user-circle me-3 fs-3"></i>
					<h5 class="modal-title fw-semibold mb-0" id="viewFamilyMemberModalLabel">Family Member Details</h5>
				</div>
				<button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>

			<!-- Modal Body -->
			<div class="modal-body px-4 py-4">
				<div class="row align-items-center mb-4">
					<div class="col-md-3 text-center">
						<div class="avatar-xxl position-relative d-inline-block">
							<img src="../../../dist/assets/img/user2-160x160.jpg" class="rounded-circle border border-4 border-white shadow" width="120" height="120" alt="Member Avatar">
							<span class="position-absolute bottom-0 end-0 bg-primary rounded-circle p-2 border border-3 border-white">
								<i class="fas fa-check text-white fs-6"></i>
							</span>
						</div>
					</div>
					<div class="col-md-9">
						<div class="row g-3">
							<div class="col-sm-6">
								<div class="d-flex align-items-center mb-3">
									<div class="bg-light-primary rounded p-2 me-3">
										<i class="fas fa-user text-primary fs-5"></i>
									</div>
									<div>
										<p class="mb-0 text-muted small">Full Name</p>
										<h6 class="mb-0 fw-semibold" id="modal-member-name">Loading...</h6>
									</div>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="d-flex align-items-center mb-3">
									<div class="bg-light-info rounded p-2 me-3">
										<i class="fas fa-birthday-cake text-info fs-5"></i>
									</div>
									<div>
										<p class="mb-0 text-muted small">Age</p>
										<h6 class="mb-0 fw-semibold" id="modal-member-age_class">Loading...</h6>
									</div>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="d-flex align-items-center mb-3">
									<div class="bg-light-pink rounded p-2 me-3">
										<i class="fas fa-venus-mars text-pink fs-5"></i>
									</div>
									<div>
										<p class="mb-0 text-muted small">Gender</p>
										<h6 class="mb-0 fw-semibold" id="modal-member-gender">Loading...</h6>
									</div>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="d-flex align-items-center mb-3">
									<div class="bg-light-success rounded p-2 me-3">
										<i class="fas fa-phone-alt text-success fs-5"></i>
									</div>
									<div>
										<p class="mb-0 text-muted small">Contact No</p>
										<h6 class="mb-0 fw-semibold" id="modal-member-contact_no">Loading...</h6>
									</div>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="d-flex align-items-center">
									<div class="bg-light-warning rounded p-2 me-3">
										<i class="fas fa-calendar-day text-warning fs-5"></i>
									</div>
									<div>
										<p class="mb-0 text-muted small">Birthdate</p>
										<h6 class="mb-0 fw-semibold" id="modal-member-dob">Loading...</h6>
									</div>
								</div>
							</div>
							<!-- Relation to Head of Family -->
							<div class="col-sm-6">
								<div class="d-flex align-items-center mb-3">
									<div class="bg-light-secondary rounded p-2 me-3">
										<i class="fas fa-users text-secondary fs-5"></i>
									</div>
									<div>
										<p class="mb-0 text-muted small">Relation to Head</p>
										<h6 class="mb-0 fw-semibold" id="modal-member-relation">Loading...</h6>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Modal Footer -->
			<div class="modal-footer bg-light rounded-bottom-4">
				<button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
					<i class="fas fa-times me-2"></i> Close
				</button>
				<button type="button" class="btn btn-primary px-4">
					<i class="fas fa-edit me-2"></i> Edit Profile
				</button>
			</div>
		</div>
	</div>
</div>

<script>
	document.addEventListener('DOMContentLoaded', function() {
		// Select all buttons with the class 'view-family-btn'
		document.querySelectorAll('.view-family-btn').forEach(function(button) {
			button.addEventListener('click', function() {
				// Get data attributes from the clicked button
				const memberId = this.getAttribute('data-id');
				const memberName = this.getAttribute('data-name');
				const memberGender = this.getAttribute('data-gender');
				const memberContactNo = this.getAttribute('data-contact_no');
				const memberDob = this.getAttribute('data-dob');
				const memberRelation = this.getAttribute('data-relation');

				// 🎂 Calculate age from DOB with proper formatting
				function calculateAge(dob) {
					const birthDate = new Date(dob);
					const today = new Date();
					let age = today.getFullYear() - birthDate.getFullYear();
					const monthDiff = today.getMonth() - birthDate.getMonth();

					if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
						age--;
					}

					// Format DOB for display
					const options = {
						year: 'numeric',
						month: 'long',
						day: 'numeric'
					};
					const formattedDob = birthDate.toLocaleDateString('en-US', options);

					return {
						age: age,
						formattedDob: formattedDob
					};
				}

				const ageData = calculateAge(memberDob);
				const genderIcon = memberGender === 'Male' ? '<i class="fas fa-mars text-primary me-1"></i>' :
					'<i class="fas fa-venus text-pink me-1"></i>';

				// Populate the modal with the data
				document.getElementById('modal-member-name').textContent = memberName;
				document.getElementById('modal-member-age_class').innerHTML = `${ageData.age} <small class="text-muted">years</small>`;
				document.getElementById('modal-member-gender').innerHTML = genderIcon + memberGender;
				document.getElementById('modal-member-contact_no').textContent = memberContactNo || 'Not provided';
				document.getElementById('modal-member-dob').textContent = ageData.formattedDob;
				document.getElementById('modal-member-relation').textContent = memberRelation || 'Not specified';

				// Show the modal programmatically
				const modal = new bootstrap.Modal(document.getElementById('viewFamilyMemberModal'));
				modal.show();
			});
		});
	});
</script>

<!-- Add Family Member Modal -->
<div class="modal fade" id="addFamilyMemberModal" tabindex="-1" aria-labelledby="addFamilyMemberModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content border-0 shadow-lg">
			<!-- Gradient Header -->
			<div class="modal-header bg-gradient-primary text-white rounded-top-4">
				<div class="d-flex align-items-center">
					<i class="fas fa-user-plus me-3 fs-4"></i>
					<h5 class="modal-title fw-semibold mb-0" id="addFamilyMemberModalLabel">Add Family Member</h5>
				</div>
				<button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>

			<!-- Modal Body -->
			<div class="modal-body px-4 py-4">
				<form action="../action_user/new_member.php" method="POST" class="needs-validation" novalidate>
					<div class="mb-4">
						<label for="f_name" class="form-label fw-medium text-muted">First Name</label>
						<div class="input-group">
							<span class="input-group-text bg-light-primary border-primary">
								<i class="fas fa-user text-primary"></i>
							</span>
							<input type="text" class="form-control border-start-0 shadow-none" id="f_name" name="f_name" placeholder="Enter first name" required>
							<div class="invalid-feedback">
								Please provide a first name
							</div>
						</div>
					</div>

					<div class="mb-4">
						<label for="l_name" class="form-label fw-medium text-muted">Last Name</label>
						<div class="input-group">
							<span class="input-group-text bg-light-primary border-primary">
								<i class="fas fa-user text-primary"></i>
							</span>
							<input type="text" class="form-control border-start-0 shadow-none" id="l_name" name="l_name" placeholder="Enter last name" required>
							<div class="invalid-feedback">
								Please provide a last name
							</div>
						</div>
					</div>

					<div class="mb-4">
						<label for="birth_date" class="form-label fw-medium text-muted">Birthdate</label>
						<div class="input-group">
							<span class="input-group-text bg-light-warning border-warning">
								<i class="fas fa-calendar-alt text-warning"></i>
							</span>
							<input type="date" class="form-control border-start-0 shadow-none" id="birth_date" name="birth_date" required>
							<div class="invalid-feedback">
								Please select a birthdate
							</div>
						</div>
					</div>

					<div class="mb-4">
						<label for="contact_no" class="form-label fw-medium text-muted">Contact No.</label>
						<div class="input-group">
							<span class="input-group-text bg-light-success border-success">
								<i class="fas fa-phone-alt text-success"></i>
							</span>
							<input type="tel" class="form-control border-start-0 shadow-none" id="contact_no" name="contact_no" placeholder="Enter contact number">
						</div>
					</div>

					<div class="mb-4">
						<label for="gender" class="form-label fw-medium text-muted">Gender</label>
						<div class="input-group">
							<span class="input-group-text bg-light-pink border-pink">
								<i class="fas fa-venus-mars text-pink"></i>
							</span>
							<select class="form-select border-start-0 shadow-none" id="gender" name="gender" required>
								<option value="" disabled selected>Select Gender</option>
								<option value="Male">Male</option>
								<option value="Female">Female</option>
							</select>
							<div class="invalid-feedback">
								Please select a gender
							</div>
						</div>
					</div>
					<div class="mb-4">
						<label for="relation" class="form-label fw-medium text-muted">Relation to Head</label>
						<div class="input-group">
							<span class="input-group-text bg-light-secondary border-secondary">
								<i class="fas fa-users text-secondary"></i>
							</span>
							<select class="form-select border-start-0 shadow-none" id="relation" name="relation" required>
								<option value="" disabled selected>Select Relation</option>
								<option value="Head of Family">Head of Family</option>
								<option value="Spouse">Spouse</option>
								<option value="Son">Son</option>
								<option value="Daughter">Daughter</option>
								<option value="Father">Father</option>
								<option value="Mother">Mother</option>
								<option value="Brother">Brother</option>
								<option value="Sister">Sister</option>
								<option value="Other">Other</option>
							</select>
						</div>
						<input type="text" class="form-control mt-2 d-none" id="relation_other" name="relation_other" placeholder="Please specify relation">
						<div class="invalid-feedback">
							Please select the relation to the head of the family
						</div>
					</div>
					<script>
						document.addEventListener('DOMContentLoaded', function() {
							const relationSelect = document.getElementById('relation');
							const relationOther = document.getElementById('relation_other');
							relationSelect.addEventListener('change', function() {
								if (this.value === 'Other') {
									relationOther.classList.remove('d-none');
									relationOther.required = true;
								} else {
									relationOther.classList.add('d-none');
									relationOther.required = false;
									relationOther.value = '';
								}
							});
						});
					</script>

					<!-- Hidden Family ID -->
					<input type="hidden" id="family_id" name="family_id" value="<?= $family_id; ?>">

					<!-- Modal Footer -->
					<div class="modal-footer bg-light rounded-bottom-4">
						<button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
							<i class="fas fa-times me-2"></i> Cancel
						</button>
						<button type="submit" class="btn btn-primary px-4">
							<i class="fas fa-plus me-2"></i> Add Member
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

<!-- Custom CSS for the modals -->
<style>
	/* Gradient background */
	.bg-gradient-primary {
		background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
	}

	/* Light background colors for icons */
	.bg-light-primary {
		background-color: rgba(106, 17, 203, 0.1);
	}

	.bg-light-info {
		background-color: rgba(23, 162, 184, 0.1);
	}

	.bg-light-pink {
		background-color: rgba(232, 62, 140, 0.1);
	}

	.bg-light-success {
		background-color: rgba(40, 167, 69, 0.1);
	}

	.bg-light-warning {
		background-color: rgba(255, 193, 7, 0.1);
	}

	/* Text colors */
	.text-primary {
		color: #6a11cb !important;
	}

	.text-info {
		color: #17a2b8 !important;
	}

	.text-pink {
		color: #e83e8c !important;
	}

	.text-success {
		color: #28a745 !important;
	}

	.text-warning {
		color: #ffc107 !important;
	}

	/* Modal rounded corners */
	.rounded-top-4 {
		border-top-left-radius: 1rem !important;
		border-top-right-radius: 1rem !important;
	}

	.rounded-bottom-4 {
		border-bottom-left-radius: 1rem !important;
		border-bottom-right-radius: 1rem !important;
	}

	/* Avatar styling */
	.avatar-xxl {
		width: 120px;
		height: 120px;
	}

	/* Form validation styling */
	.needs-validation .form-control:invalid,
	.needs-validation .form-select:invalid {
		border-color: #dc3545;
	}

	.needs-validation .form-control:valid,
	.needs-validation .form-select:valid {
		border-color: #28a745;
	}

	/* Input group styling */
	.input-group-text {
		transition: all 0.3s ease;
	}

	.input-group:focus-within .input-group-text {
		background-color: rgba(106, 17, 203, 0.2);
	}
</style>

<script>
	// Form validation
	(function() {
		'use strict'

		// Fetch all the forms we want to apply custom Bootstrap validation styles to
		var forms = document.querySelectorAll('.needs-validation')

		// Loop over them and prevent submission
		Array.prototype.slice.call(forms)
			.forEach(function(form) {
				form.addEventListener('submit', function(event) {
					if (!form.checkValidity()) {
						event.preventDefault()
						event.stopPropagation()
					}

					form.classList.add('was-validated')
				}, false)
			})
	})()
</script>
<!-- Bootstrap Modal for Address Update -->
<div class="modal fade" id="editAddressModal" tabindex="-1" aria-labelledby="editAddressModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<form action="../action_user/family_address.php" method="POST">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="editAddressModalLabel">Edit Address</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<input type="hidden" name="family_id" value="<?php echo htmlspecialchars($family_id); ?>">

					<!-- CITY -->
					<div class="mb-3">
						<label class="form-label">City</label>
						<select name="city" id="city" class="form-control" required>
							<option value="">Select City</option>
							<!-- Dynamically populate with JS or PHP -->
						</select>
					</div>

					<!-- BARANGAY -->
					<div class="mb-3">
						<label class="form-label">Barangay</label>
						<select name="barangay" id="barangay" class="form-control" required>
							<option value="">Select Barangay</option>
							<!-- Dynamically populate with JS or PHP -->
						</select>
					</div>

					<!-- PUROK -->
					<div class="mb-3">
						<label class="form-label">Purok</label>
						<input type="text" name="purok" id="purok" class="form-control" placeholder="Enter Purok" required>
					</div>
				</div>


				<div class="modal-footer">
					<button type="submit" class="btn btn-primary">Save Changes</button>
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
				</div>
			</div>
		</form>
	</div>
</div>
<?php
// Reset the result pointer to loop again for modals
$familyResult->data_seek(0);
while ($member = $familyResult->fetch_assoc()) :
?>
	<!-- Delete Confirmation Modal for each member -->
	<div class="modal fade" id="deleteFamilyMemberModal<?= $member['pre_reg_id'] ?>" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header bg-danger text-white">
					<h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
					<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<p>Are you sure you want to delete this family member?</p>
					<p><strong><?= htmlspecialchars($member['f_name'] . ' ' . htmlspecialchars($member['l_name'])) ?></strong></p>
					<p class="text-danger">This action cannot be undone.</p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
					<form action="../action_user/delete_family_member.php" method="POST">
						<input type="hidden" name="pre_reg_id" value="<?= $member['pre_reg_id'] ?>">
						<button type="submit" class="btn btn-danger">Delete Permanently</button>
					</form>
				</div>
			</div>
		</div>
	</div>
<?php endwhile; ?>