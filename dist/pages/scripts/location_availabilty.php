<script>
	function confirmReservation(roomId) {
		Swal.fire({
			title: 'Confirm Reservation',
			html: `<div class="text-center">
                      <i class="bi bi-shield-check text-primary fs-1 mb-3"></i>
                      <p class="mb-3">Are you sure you want to reserve this room?</p>
                   </div>`,
			showCancelButton: true,
			confirmButtonText: 'Yes, reserve it!',
			cancelButtonText: 'Cancel',
			confirmButtonColor: '#4e73df',
			cancelButtonColor: '#6c757d',
			customClass: {
				popup: 'rounded-3',
				confirmButton: 'btn-lg rounded-pill px-4',
				cancelButton: 'btn-lg rounded-pill px-4'
			}
		}).then((result) => {
			if (result.isConfirmed) {
				window.location.href = `../action_user/reservation.php?room_id=${roomId}`;
			}
		});
	}

	// Search functionality
	document.getElementById('searchBox').addEventListener('input', function() {
		const searchTerm = this.value.toLowerCase();
		const accordionItems = document.querySelectorAll('.accordion-item');

		accordionItems.forEach(item => {
			const locationName = item.querySelector('.accordion-button h5').textContent.toLowerCase();
			const rooms = item.querySelectorAll('.card');
			let hasVisibleRooms = false;

			rooms.forEach(room => {
				const roomName = room.querySelector('.card-header h6').textContent.toLowerCase();
				if (roomName.includes(searchTerm)) {
					room.style.display = '';
					hasVisibleRooms = true;
				} else {
					room.style.display = 'none';
				}
			});

			// Show/hide location based on search results
			if (locationName.includes(searchTerm)) {
				item.style.display = '';
				if (searchTerm) {
					const collapse = item.querySelector('.accordion-collapse');
					if (collapse) {
						new bootstrap.Collapse(collapse, {
							toggle: false
						}).show();
					}
				}
			} else {
				item.style.display = hasVisibleRooms ? '' : 'none';
			}
		});
	});
</script>

<style>
	.bg-gradient-primary {
		background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
	}

	.accordion-button:not(.collapsed) {
		color: inherit;
		background-color: white;
		box-shadow: none;
	}

	.accordion-button:focus {
		box-shadow: none;
		border-color: rgba(0, 0, 0, .125);
	}

	.accordion-item {
		border-radius: 0.5rem !important;
	}

	.accordion-arrow {
		transition: transform 0.2s ease-in-out;
	}

	.accordion-button:not(.collapsed) .accordion-arrow {
		transform: rotate(180deg);
	}

	.card {
		border-radius: 0.5rem;
		transition: transform 0.2s, box-shadow 0.2s;
	}

	.card:hover {
		transform: translateY(-2px);
		box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, .1) !important;
	}

	.btn-primary {
		background-color: #4e73df;
		border-color: #4e73df;
	}

	.btn-primary:hover {
		background-color: #2e59d9;
		border-color: #2653d4;
	}

	.rounded-lg {
		border-radius: 1rem !important;
	}
</style>