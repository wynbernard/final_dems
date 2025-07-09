<script>
	function confirmIntent() {
		Swal.fire({
			title: 'Confirm Your Intention',
			text: "By confirming, you are stating your intention to use this evacuation center. This helps authorities with planning.",
			icon: 'question',
			showCancelButton: true,
			confirmButtonColor: '#28a745',
			cancelButtonColor: '#6c757d',
			confirmButtonText: 'Yes, I confirm',
			cancelButtonText: 'Cancel',
			backdrop: 'rgba(0,0,0,0.1)'
		}).then((result) => {
			if (result.isConfirmed) {
				document.getElementById('intentForm').submit();
			}
		});
	}

	// Initialize map (example using Leaflet)
	document.addEventListener('DOMContentLoaded', function() {
		<?php if ($user_reservation): ?>
			// This would be replaced with actual map initialization code
			console.log('Initializing map for location');
		<?php endif; ?>

		// Add confirmation for cancel reservation
		document.querySelectorAll('.cancel-reservation-form').forEach(form => {
			form.addEventListener('submit', function(e) {
				e.preventDefault();
				Swal.fire({
					title: 'Cancel Reservation?',
					text: "Are you sure you want to cancel this reservation?",
					icon: 'warning',
					showCancelButton: true,
					confirmButtonColor: '#d33',
					cancelButtonColor: '#6c757d',
					confirmButtonText: 'Yes, cancel it!',
					cancelButtonText: 'No, keep it'
				}).then((result) => {
					if (result.isConfirmed) {
						form.submit();
					}
				});
			});
		});
	});
</script><!-- Add these to your head section if not already present -->