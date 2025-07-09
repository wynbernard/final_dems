<!-- Pre-Registration Details Modal -->
<div class="modal fade" id="viewDetailsModal" tabindex="-1">
	<div class="modal-dialog modal-lg modal-dialog-centered">
		<div class="modal-content shadow-lg rounded-4">
			<div class="modal-header bg-info text-white rounded-top-4">
				<h5 class="modal-title">
					<i class="fas fa-user-circle me-2"></i> Pre-Registration Details
				</h5>
				<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
			</div>

			<div class="modal-body p-4">
				<div class="row g-3">
					<div class="col-md-6">
						<div class="card border-0 shadow-sm rounded-3 h-100">
							<div class="card-body">
								<h6 class="text-muted"><i class="fas fa-id-badge me-2 text-info"></i> Full Name</h6>
								<p class="mb-0 fw-semibold" id="modal-name">Loading...</p>
							</div>
						</div>
					</div>

					<div class="col-md-3">
						<div class="card border-0 shadow-sm rounded-3 h-100">
							<div class="card-body">
								<h6 class="text-muted"><i class="fas fa-venus-mars me-2 text-info"></i> Gender</h6>
								<p class="mb-0 fw-semibold" id="modal-gender">Loading...</p>
							</div>
						</div>
					</div>

					<div class="col-md-3">
						<div class="card border-0 shadow-sm rounded-3 h-100">
							<div class="card-body">
								<h6 class="text-muted"><i class="fas fa-phone me-2 text-info"></i> Contact No.</h6>
								<p class="mb-0 fw-semibold" id="modal-contact">Loading...</p>
							</div>
						</div>
					</div>

					<div class="col-md-4">
						<div class="card border-0 shadow-sm rounded-3 h-100">
							<div class="card-body">
								<h6 class="text-muted"><i class="fas fa-birthday-cake me-2 text-info"></i> Date of Birth</h6>
								<p class="mb-0 fw-semibold" id="modal-dob">Loading...</p>
							</div>
						</div>
					</div>

					<div class="col-md-2">
						<div class="card border-0 shadow-sm rounded-3 h-100">
							<div class="card-body">
								<h6 class="text-muted"><i class="fas fa-hourglass-half me-2 text-info"></i> Age</h6>
								<p class="mb-0 fw-semibold" id="modal-age">Loading...</p>
							</div>
						</div>
					</div>

					<div class="col-md-6">
						<div class="card border-0 shadow-sm rounded-3 h-100">
							<div class="card-body">
								<h6 class="text-muted"><i class="fas fa-map-marker-alt me-2 text-info"></i> Address</h6>
								<p class="mb-0 fw-semibold" id="modal-address">Loading...</p>
							</div>
						</div>
					</div>
				</div> <!-- /row -->
			</div>

			<div class="modal-footer border-top-0 rounded-bottom-4">
				<button class="btn btn-outline-secondary" data-bs-dismiss="modal">
					<i class="fas fa-times me-1"></i> Close
				</button>
			</div>
		</div>
	</div>
</div>



<script>
	document.addEventListener('DOMContentLoaded', function() {
		document.querySelectorAll('.view-details-btn').forEach(function(button) {
			button.addEventListener('click', function() {
				const name = this.dataset.name || 'N/A';
				const gender = this.dataset.gender || 'N/A';
				const contact = this.dataset.contact || 'Not provided';
				const dob = this.dataset.dob || '';
				const age = this.dataset.age || 'N/A';
				const address = this.dataset.address || 'N/A';

				// Format DOB
				const formattedDob = (() => {
					const birthDate = new Date(dob);
					if (isNaN(birthDate)) return 'Invalid Date';
					return birthDate.toLocaleDateString('en-US', {
						year: 'numeric',
						month: 'long',
						day: 'numeric'
					});
				})();

				const genderIcon = gender.toLowerCase() === 'male' ?
					'<i class="fas fa-mars text-primary me-1"></i>' :
					gender.toLowerCase() === 'female' ?
					'<i class="fas fa-venus text-pink me-1"></i>' :
					'<i class="fas fa-question-circle text-muted me-1"></i>';

				// Populate modal fields
				document.getElementById('modal-name').textContent = name;
				document.getElementById('modal-gender').innerHTML = genderIcon + gender;
				document.getElementById('modal-contact').textContent = contact;
				document.getElementById('modal-dob').textContent = formattedDob;
				document.getElementById('modal-age').innerHTML = `${age} <small class="text-muted">years</small>`;
				document.getElementById('modal-address').textContent = address;

				// Show modal
				const modal = new bootstrap.Modal(document.getElementById('viewDetailsModal'));
				modal.show();
			});
		});
	});
</script>