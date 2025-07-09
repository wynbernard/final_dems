$(document).ready(function() {
		// Search functionality
		$("#searchBox").on("keyup", function() {
			var searchTerm = $(this).val().toLowerCase().trim();
			$(".searchable-table tbody tr").each(function() {
				var rowText = $(this).text().toLowerCase();
				$(this).toggle(rowText.includes(searchTerm));
			});
		});



		// Location assignment modal
		$(document).on('click', '.assign-location-btn', function() {
			var adminId = $(this).data('id');
			var adminName = $(this).data('name');
			$('#assignAdminId').val(adminId);
			$('#locationAssignmentModalLabel').text('Assign Locations to ' + adminName);

			// Fetch current assignments
			$.ajax({
				url: 'fetch_admin_locations.php',
				type: 'POST',
				data: {
					admin_id: adminId
				},
				dataType: 'json',
				success: function(response) {
					$('#locationSelect').val(response.assigned_locations);
				},
				error: function(xhr, status, error) {
					console.error('Error fetching locations:', error);
				}
			});
		});

		
		// Save location assignments
		$('#saveLocationsBtn').click(function() {
			var formData = $('#locationAssignmentForm').serialize();

			$.ajax({
				url: 'save_admin_locations.php',
				type: 'POST',
				data: formData,
				dataType: 'json',
				beforeSend: function() {
					$('#saveLocationsBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');
				},
				success: function(response) {
					if (response.success) {
						toastr.success('Location assignments updated successfully');
						setTimeout(function() {
							location.reload();
						}, 1500);
					} else {
						toastr.error('Error: ' + response.message);
					}
				},
				error: function(xhr, status, error) {
					toastr.error('Error saving locations: ' + error);
				},
				complete: function() {
					$('#saveLocationsBtn').prop('disabled', false).html('Save Assignments');
				}
			});
		});

		// Initialize tooltips
		$('[data-bs-toggle="tooltip"]').tooltip();
	});