<!-- Modal -->
<div class="modal fade" id="idpDetailsModal" tabindex="-1" aria-labelledby="idpDetailsModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="idpDetailsModalLabel">IDP Details</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body" id="idpDetailsBody">
				<!-- IDP details will be loaded here dynamically -->
				Loading...
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
	$(document).ready(function() {
		// Handle click event on the "View Details" button
		$('.view-idp-btn').on('click', function() {
			const evacRegId = $(this).data('id'); // Get the evac_reg_id from the data attribute

			// Fetch IDP details from the server
			$.ajax({
				url: '../fetch_data/idps_staff.php', // Endpoint to fetch IDP details
				method: 'GET',
				data: {
					id: evacRegId
				},
				success: function(response) {
					// Update the modal body with the fetched data
					$('#idpDetailsBody').html(response);
				},
				error: function() {
					$('#idpDetailsBody').html('<p class="text-danger">Failed to load IDP details.</p>');
				}
			});
		});
	});
</script>