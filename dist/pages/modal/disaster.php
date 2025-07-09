<!-- Example Bootstrap Modal -->
<div class="modal fade" id="editDisasterModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog">
		<form method="POST" action="../action/edit_disaster.php">
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