<!-- ADD DISASTER MODAL -->
<div class="modal fade" id="addDisasterModal" tabindex="-1" aria-labelledby="addDisasterModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<form action="../action/disaster/add_disaster.php" method="POST">
				<?php require_once '../../../database/csrf.php'; echo csrf_token_field(); ?>
				<div class="modal-header bg-primary text-white">
					<h5 class="modal-title" id="addDisasterModalLabel">Add Disaster Record</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="mb-3">
						<label for="disaster_name" class="form-label">Disaster Name</label>
						<input type="text" class="form-control" id="disaster_name" name="disaster_name" required>
					</div>
					<div class="mb-3">
						<label for="date" class="form-label">Date</label>
						<input type="date" class="form-control" id="date" name="date" required>
					</div>
					<div class="mb-3">
						<label for="level" class="form-label">Scale (1-10)</label>
						<select class="form-control" id="level" name="level">
							<option value="0" disabled selected>Select scale</option>
							<option value="1">1</option>
							<option value="2">2</option>
							<option value="3">3</option>
							<option value="4">4</option>
							<option value="5">5</option>
							<option value="6">6</option>
							<option value="7">7</option>
							<option value="8">8</option>
							<option value="9">9</option>
							<option value="10">10</option>
						</select>
					</div>
					<div>
						<label for="disaster_type" class="form-label">Disaster Type</label>
						<select class="form-control" id="disaster_type" name="disaster_type" required>
							<option value="" disabled selected>Select disaster type</option>
							<option value="Earthquake">Earthquake</option>
							<option value="Typhoon">Typhoon</option>
							<option value="Volcanic Eruption">Volcanic Eruption</option>
						</select>
					</div>
					<div class="mb-3">
						<label for="status" class="form-label">Status</label>
						<select class="form-control" id="status" name="status" required>
							<option value="" disabled selected>Select status</option>
							<option value="Ongoing">Ongoing</option>
							<option value="Resolved">Resolved</option>
						</select>
					</div>
				</div>
				
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Edit Disaster -->
<div class="modal fade" id="editDisasterModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog">
		<form method="POST" action="../action/disaster/edit_disaster.php">
			<?php require_once '../../../database/csrf.php'; echo csrf_token_field(); ?>
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Edit Disaster</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
				</div>
				<div class="modal-body">
					<input type="hidden" id="edit-disaster-id" name="disaster_id">
					<div class="mb-3">
						<label for="edit-disaster-name" class="form-label">Disaster Name</label>
						<input type="text" class="form-control" id="edit-disaster-name" name="disaster_name">
					</div>
					<div class="mb-3">
						<label for="edit-disaster-date" class="form-label">Date</label>
						<input type="date" class="form-control" id="edit-disaster-date" name="date">
					</div>
					<div class="mb-3">
						<label for="edit-disaster-level" class="form-label">Scale (1-10)</label>
						<select class="form-control" id="edit-disaster-level" name="level" required>
							<option value="" disabled selected>Select scale</option>
							<option value="1">1</option>
							<option value="2">2</option>
							<option value="3">3</option>
							<option value="4">4</option>
							<option value="5">5</option>
							<option value="6">6</option>
							<option value="7">7</option>
							<option value="8">8</option>
							<option value="9">9</option>
							<option value="10">10</option>
						</select>
					</div>
					<div>
						<label for="edit-disaster-type" class="form-label">Disaster Type</label>
						<select class="form-control" id="edit-disaster-type" name="disaster_type" required>
							<option value="" disabled selected>Select disaster type</option>
							<option value="Earthquake">Earthquake</option>
							<option value="Typhoon">Typhoon</option>
							<option value="Volcanic Eruption">Volcanic Eruption</option>
						</select>
					</div>
					<div>
						<label for="edit-disaster-status" class="form-label">Status</label>
						<select class="form-control" id="edit-disaster-status" name="status" required>
							<option value="" disabled selected>Select status</option>
							<option value="Ongoing">Ongoing</option>
							<option value="Resolved">Resolved</option>
						</select>
					</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary">Save Changes</button>
				</div>
			</div>
		</form>
	</div>
</div>





<script>
	document.addEventListener("DOMContentLoaded", function() {
		const editButtons = document.querySelectorAll('.edit-btn');

		editButtons.forEach(button => {
			button.addEventListener('click', function() {
				// get the data
				const disasterId = this.getAttribute('data-id');
				const disasterName = this.getAttribute('data-name');
				const disasterDate = this.getAttribute('data-date');
				const disasterLevel = this.getAttribute('data-level');
				const disasterStatus = this.getAttribute('data-status');
				const disasterType = this.getAttribute('data-type');

				// Field Modal 
				document.getElementById('edit-disaster-id').value = disasterId;
				document.getElementById('edit-disaster-name').value = disasterName;
				document.getElementById('edit-disaster-date').value = disasterDate;
				document.getElementById('edit-disaster-level').value = disasterLevel;
				document.getElementById('edit-disaster-status').value = disasterStatus;
				document.getElementById('edit-disaster-type').value = disasterType;
			});
		});
	});
</script>

<script>
	document.addEventListener("DOMContentLoaded", function() {
		// Handle modal show event to set the disaster ID
		const deleteModal = document.getElementById('deleteDisasterModal');
		if (deleteModal) {
			deleteModal.addEventListener('show.bs.modal', function(event) {
				// Get the button that triggered the modal
				const button = event.relatedTarget;
				if (button) {
					const disasterId = button.getAttribute("data-id");
					const hiddenInput = document.getElementById("delete-disaster-id");
					if (hiddenInput && disasterId) {
						hiddenInput.value = disasterId;
					}
				}
			});
			
			// Ensure modal dialog is visible when shown
			deleteModal.addEventListener('shown.bs.modal', function() {
				const modalDialog = this.querySelector('.modal-dialog');
				if (modalDialog) {
					modalDialog.style.display = 'block';
					modalDialog.style.visibility = 'visible';
					modalDialog.style.opacity = '1';
				}
			});
		}
	});
</script>