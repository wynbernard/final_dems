<!-- Pre-Registration Details Modal -->
<div class="modal fade" id="viewDetailsModal" tabindex="-1">
<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content shadow rounded-4">
        <div class="modal-header details-modal-header w-100 rounded-top-4">
            <div class="w-100 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h5 class="modal-title d-flex align-items-center gap-2 m-0"><i class="fas fa-user-circle text-primary"></i> <span>Pre-Registration Details</span></h5>
                <div class="d-flex align-items-center gap-2 flex-wrap" id="preRegChips"></div>
            </div>
            <button type="button" class="btn-close ms-2" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body details-modal-body p-4">
				<div class="row g-3">
					<div class="col-md-6">
                    <div class="details-card h-100">
							<div class="card-body">
                                <h6 class="text-muted"><i class="fas fa-id-badge me-2 text-info"></i> Full Name</h6>
								<p class="mb-0 fw-semibold" id="modal-name">Loading...</p>
							</div>
						</div>
					</div>

					<div class="col-md-3">
                    <div class="details-card h-100">
							<div class="card-body">
								<h6 class="text-muted"><i class="fas fa-venus-mars me-2 text-info"></i> Gender</h6>
								<p class="mb-0 fw-semibold" id="modal-gender">Loading...</p>
							</div>
						</div>
					</div>

					<div class="col-md-3">
                    <div class="details-card h-100">
							<div class="card-body">
								<h6 class="text-muted"><i class="fas fa-phone me-2 text-info"></i> Contact No.</h6>
								<p class="mb-0 fw-semibold" id="modal-contact">Loading...</p>
							</div>
						</div>
					</div>

					<div class="col-md-4">
                    <div class="details-card h-100">
							<div class="card-body">
								<h6 class="text-muted"><i class="fas fa-birthday-cake me-2 text-info"></i> Date of Birth</h6>
								<p class="mb-0 fw-semibold" id="modal-dob">Loading...</p>
							</div>
						</div>
					</div>

					<div class="col-md-2">
                    <div class="details-card h-100">
							<div class="card-body">
								<h6 class="text-muted"><i class="fas fa-hourglass-half me-2 text-info"></i> Age</h6>
								<p class="mb-0 fw-semibold" id="modal-age">Loading...</p>
							</div>
						</div>
					</div>

					<div class="col-md-6">
                    <div class="details-card h-100">
							<div class="card-body">
								<h6 class="text-muted"><i class="fas fa-map-marker-alt me-2 text-info"></i> Address</h6>
								<p class="mb-0 fw-semibold" id="modal-address">Loading...</p>
							</div>
						</div>
					</div>
				</div> <!-- /row -->
			</div>

        <div class="modal-footer details-modal-footer rounded-bottom-4">
            <button class="btn btn-light border" data-bs-dismiss="modal">
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
                const registered = this.dataset.registered || '';
                // Fill header chips
                const chips = [];
                if (name) chips.push('<span class="chip"><i class="fas fa-user"></i>' + name + '</span>');
                if (registered) chips.push('<span class="chip"><i class="fas fa-id-badge"></i>' + registered + '</span>');
                if (address) chips.push('<span class="chip"><i class="fas fa-map-marker-alt"></i>' + address + '</span>');
                if (gender) chips.push('<span class="chip"><i class="fas fa-venus-mars"></i>' + gender + '</span>');
                if (age) chips.push('<span class="chip"><i class="fas fa-hourglass-half"></i>' + age + ' yrs</span>');
                const chipsEl = document.getElementById('preRegChips');
                if (chipsEl) chipsEl.innerHTML = chips.join('');

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

                // Replace body content with pre-rendered content (family or individual) to match idps_user
                const pre = document.getElementById('family-members-pre-' + this.dataset.id);
                const body = document.querySelector('#viewDetailsModal .modal-body');
                if (body) {
                    const html = pre ? pre.innerHTML : (
                        '<div class="container bg-white p-3">'
                        + '<h6 class="fw-bold mb-2"><i class="fas fa-id-card me-2 text-primary"></i>Individual Details</h6>'
                        + '<div class="row g-3">'
                        +   '<div class="col-md-6">'
                        +     '<p class="mb-1"><strong>Name:</strong> ' + (name ? name : 'N/A') + '</p>'
                        +     '<p class="mb-1"><strong>Gender:</strong> ' + (gender ? gender : 'N/A') + '</p>'
                        +     '<p class="mb-1"><strong>Age:</strong> ' + (age ? age : '') + '</p>'
                        +   '</div>'
                        +   '<div class="col-md-6">'
                        +     '<p class="mb-1"><strong>Address:</strong> ' + (address ? address : 'N/A') + '</p>'
                        +   '</div>'
                        + '</div>'
                        + '</div>'
                    );
                    body.innerHTML = '<div class="details-card">' + html + '</div>';
                }

				// Show modal
				const modal = new bootstrap.Modal(document.getElementById('viewDetailsModal'));
				modal.show();
			});
		});
	});
</script>

<style>
    /* Reuse details modal look */
    #viewDetailsModal .details-modal-header {
        background: linear-gradient(90deg, rgba(13,110,253,0.10) 0%, rgba(13,110,253,0.02) 100%);
        border-bottom: 1px solid #e9ecef;
    }
    #viewDetailsModal .details-modal-body { background-color: #f8f9fb; }
    #viewDetailsModal .details-card {
        background: #fff; border: 1px solid #e9ecef; border-radius: .75rem; box-shadow: 0 10px 24px rgba(0,0,0,.05); padding: 1rem;
    }
    #viewDetailsModal h6 { letter-spacing: .2px; text-transform: uppercase; font-size: .9rem; color: #334155; }
    #viewDetailsModal .details-modal-footer { background: #fff; border-top: 1px solid #e9ecef; }
    #viewDetailsModal .chip { display:inline-flex; align-items:center; gap:.35rem; padding:.25rem .5rem; border-radius:999px; background:#f8fafc; border:1px solid #e2e8f0; font-size:.8rem; color:#334155; }
    #viewDetailsModal .chip i { color:#64748b; }
    @media (max-width: 576px) { #viewDetailsModal .details-card { padding:.75rem; } }
</style>