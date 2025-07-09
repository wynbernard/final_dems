<!-- ADD DISASTER MODAL -->
<div class="modal fade" id="addDisasterModal" tabindex="-1" aria-labelledby="addDisasterModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<form action="../action/disaster/add_disaster.php" method="POST">
				<div class="modal-header bg-primary text-white">
					<h5 class="modal-title" id="addDisasterModalLabel">Add Disaster Record</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="mb-3">
						<label for="disaster_name" class="form-label">Disaster Type</label>
						<input type="text" class="form-control" id="disaster_name" name="disaster_name" required>
					</div>

					<div class="mb-3">
						<label for="level" class="form-label">Level</label>
						<input type="text" class="form-control" id="level" name="level" required>
					</div>

					<div class="mb-3">
						<label for="date" class="form-label">Date</label>
						<input type="date" class="form-control" id="date" name="date" required>
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
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Edit Disaster</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
				</div>
				<div class="modal-body">
					<input type="hidden" id="edit-disaster-id" name="disaster_id">
					<div class="mb-3">
						<label for="edit-disaster-name" class="form-label">Disaster Name</label>
						<input type="text" class="form-control" id="edit-disaster-type" name="disaster_name">
					</div>
					<div class="mb-3">
						<label for="edit-disaster-level" class="form-label">Severity</label>
						<input type="text" class="form-control" id="edit-disaster-level" name="level">
					</div>
					<div class="mb-3">
						<label for="edit-disaster-date" class="form-label">Date</label>
						<input type="date" class="form-control" id="edit-disaster-date" name="date">
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary">Save Changes</button>
				</div>
			</div>
		</form>
	</div>
</div>
<!-- DELETE DISASTER MODAL -->
<div class="modal fade" id="deleteDisasterModal" tabindex="-1" aria-labelledby="deleteDisasterModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<form action="../action/disaster/delete_disaster.php" method="POST">
				<div class="modal-header bg-danger text-white">
					<h5 class="modal-title" id="deleteDisasterModalLabel">Confirm Deletion</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<p>Are you sure you want to delete this disaster record?</p>
					<input type="hidden" name="disaster_id" id="delete-disaster-id">
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
					<button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Delete</button>
				</div>
			</form>
		</div>
	</div>
</div>



<script>
	document.addEventListener("DOMContentLoaded", function() {
		const editButtons = document.querySelectorAll('.edit-btn');

		editButtons.forEach(button => {
			button.addEventListener('click', function() {
				// get the data
				const disasterId = this.getAttribute('data-id');
				const disasterType = this.getAttribute('data-type');
				const disasterLevel = this.getAttribute('data-level');
				const disasterDate = this.getAttribute('data-date');

				// Field Modal 
				document.getElementById('edit-disaster-id').value = disasterId;
				document.getElementById('edit-disaster-type').value = disasterType;
				document.getElementById('edit-disaster-level').value = disasterLevel;
				document.getElementById('edit-disaster-date').value = disasterDate;
			});
		});
	});
</script>

<script>
	document.addEventListener("DOMContentLoaded", function() {
		const deleteButtons = document.querySelectorAll(".delete-btn");

		deleteButtons.forEach(button => {
			button.addEventListener("click", function() {
				const disasterId = this.getAttribute("data-id");
				document.getElementById("delete-disaster-id").value = disasterId;
			});
		});
	});
</script>