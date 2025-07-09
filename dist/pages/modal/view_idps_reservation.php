<!-- Modal to View IDPs -->
<!-- Modal to View IDPs -->
<div class="modal fade" id="viewReservationModal" tabindex="-1" aria-labelledby="viewReservationModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header bg-info text-white">
				<h5 class="modal-title" id="viewReservationModalLabel">
					<i class="fas fa-users me-2"></i>
					IDPs in <span id="modalRoomName"></span> at <span id="modalLocationName"></span>
				</h5>
				<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="table-responsive">
					<table class="table table-striped table-hover">
						<thead class="table-light">
							<tr>
								<th>Name</th>
								<th>Age</th>
								<th>Gender</th>
								<th>Date Reservation</th>
								<th>Stattus</th>
							</tr>
						</thead>
						<tbody id="idpListBody">
							<!-- Data will be loaded here via AJAX -->
						</tbody>
					</table>
				</div>
				<div class="d-flex justify-content-between mt-3">
					<div class="text-muted" id="idpCountDisplay">
						Showing <span id="currentCount">0</span> of <span id="totalCount">0</span> IDPs
					</div>
					<div id="loadingSpinner" class="d-none">
						<div class="spinner-border spinner-border-sm text-info" role="status">
							<span class="visually-hidden">Loading...</span>
						</div>
						Loading...
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<script>
	document.addEventListener('DOMContentLoaded', function() {
		// Initialize the modal
		const viewReservationModal = new bootstrap.Modal(document.getElementById('viewReservationModal'));

		// Handle view IDP button clicks
		document.querySelectorAll('.view-idp-btn').forEach(button => {
			button.addEventListener('click', function(e) {
				e.preventDefault();

				const roomId = this.getAttribute('data-id');
				const roomName = this.getAttribute('data-location');
				const locationName = this.getAttribute('data-location-name');

				// Update modal titles
				document.getElementById('modalRoomName').textContent = roomName;
				document.getElementById('modalLocationName').textContent = locationName;

				// Show loading state
				const idpListBody = document.getElementById('idpListBody');
				const loadingSpinner = document.getElementById('loadingSpinner');
				idpListBody.innerHTML = '';
				loadingSpinner.classList.remove('d-none');

				// Fetch IDP data
				fetch(`../fetch_data/fetch_idps_reservation.php?room_id=${roomId}`)
					.then(response => {
						if (!response.ok) {
							throw new Error('Network response was not ok');
						}
						return response.json();
					})
					.then(data => {
						loadingSpinner.classList.add('d-none');

						if (data.error) {
							idpListBody.innerHTML = `
                            <tr>
                                <td colspan="6" class="text-center text-danger py-4">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    ${data.error}
                                </td>
                            </tr>
                        `;
							return;
						}

						if (data.idps.length === 0) {
							idpListBody.innerHTML = `
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-info-circle me-2"></i>
                                    No IDPs currently in this room
                                </td>
                            </tr>
                        `;
						} else {
							data.idps.forEach(idp => {
								const row = document.createElement('tr');
								row.innerHTML = `
                                <td>${escapeHtml(idp.name)}</td>
                                <td>${idp.age || 'N/A'}</td>
                                <td>${idp.gender || 'N/A'}</td>
                                <td>${idp.reservation_date ? formatDate(idp.reservation_date) : 'N/A'}</td>
								<td>${idp.status || 'N/A'}</td>
                            `;
								idpListBody.appendChild(row);
							});
						}

						// Update count display
						document.getElementById('currentCount').textContent = data.idps.length;
						document.getElementById('totalCount').textContent = data.idps.length;
					})
					.catch(error => {
						console.error('Error:', error);
						loadingSpinner.classList.add('d-none');
						idpListBody.innerHTML = `
                        <tr>
                            <td colspan="6" class="text-center text-danger py-4">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                Failed to load IDP data. Please try again.
                            </td>
                        </tr>
                    `;
					});

				// Show the modal
				viewReservationModal.show();
			});
		});

		// Helper function to escape HTML
		function escapeHtml(unsafe) {
			return unsafe
				.replace(/&/g, "&amp;")
				.replace(/</g, "&lt;")
				.replace(/>/g, "&gt;")
				.replace(/"/g, "&quot;")
				.replace(/'/g, "&#039;");
		}

		// Helper function to format date
		function formatDate(dateString) {
			const options = {
				year: 'numeric',
				month: 'short',
				day: 'numeric',
				hour: '2-digit',
				minute: '2-digit'
			};
			return new Date(dateString).toLocaleDateString(undefined, options);
		}
	});
</script>