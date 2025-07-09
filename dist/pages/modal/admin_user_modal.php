<!-- Add Admin Modal -->
<div class="modal fade" id="addAdminModal" tabindex="-1" aria-labelledby="addAdminModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="addAdminModalLabel">Add New Admin</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form action="../action/admin_user_action/add_admin_user.php" method="POST">
					<div class="mb-3">
						<label for="username" class="form-label">Username</label>
						<input type="text" class="form-control" id="username" name="username" required>
						<small id="usernameFeedback1" class="text-danger"></small>
					</div>

					<!-- Include jQuery -->
					<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
					<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

					<script>
						$(document).ready(function() {
							let typingTimer;
							let doneTypingInterval = 500; // 0.5s delay for real-time update

							$('#username').on('input', function() {
								clearTimeout(typingTimer);
								let username = $(this).val().trim();

								if (username === "") {
									$('#usernameFeedback1').html('').css('color', '');
									$('#username').removeClass('is-valid is-invalid');
									$('#addAdminBtn').prop('disabled', true); // Disable button by default
									return;
								}

								typingTimer = setTimeout(function() {
									checkUsername(username);
								}, doneTypingInterval);
							});

							function checkUsername(username) {
								$.ajax({
									url: '../check_validation/admin_username.php',
									type: 'POST',
									data: {
										username: username
									},
									success: function(response) {
										console.log("Server Response:", response); // Debugging log
										response = response.trim(); // Remove spaces

										if (response === 'taken') {
											$('#usernameFeedback1').html('<i class="fas fa-exclamation-circle"></i> Username is already taken.')
												.css({
													'color': 'red',
													'font-weight': 'bold'
												});
											$('#username').addClass('is-invalid').removeClass('is-valid');
											$('#addAdminBtn').prop('disabled', true); // Disable button
										} else if (response === 'available') {
											$('#usernameFeedback1').html('<i class="fas fa-check-circle" style="color: green;"></i> Username available.')
												.css({
													'color': 'green',
													'font-weight': 'bold'
												});
											$('#username').addClass('is-valid').removeClass('is-invalid');
											$('#addAdminBtn').prop('disabled', false); // Enable button
										} else {
											$('#usernameFeedback1').html('<i class="fas fa-exclamation-triangle"></i> Error checking username.')
												.css({
													'color': 'orange',
													'font-weight': 'bold'
												});
											$('#username').addClass('is-invalid').removeClass('is-valid');
											$('#addAdminBtn').prop('disabled', true); // Disable button
										}
									},
									error: function(xhr, status, error) {
										console.log("AJAX Error:", status, error); // Debugging log
										$('#usernameFeedback1').html('<i class="fas fa-exclamation-triangle"></i> Error connecting to server.')
											.css({
												'color': 'orange',
												'font-weight': 'bold'
											});
										$('#username').addClass('is-invalid').removeClass('is-valid');
										$('#addAdminBtn').prop('disabled', true); // Disable button
									}
								});
							}
						});
					</script>

					<div class="mb-3">
						<label for="f_name" class="form-label">First Name</label>
						<input type="text" class="form-control" id="f_name" name="f_name" required>
					</div>
					<div class="mb-3">
						<label for="l_name" class="form-label">Last Name</label>
						<input type="text" class="form-control" id="l_name" name="l_name" required>
					</div>
					<div class="mb-3">
						<label for="password" class="form-label">Password</label>
						<input type="password" class="form-control" id="password" name="password" required>
					</div>
					<div class="mb-3">
						<label for="role" class="form-label">Role</label>
						<select class="form-select" id="role" name="role" required>
							<option value="Admin">Admin</option>
							<option value="Staff">Staff</option>
						</select>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
						<button type="submit" class="btn btn-success" id="addAdminBtn" disabled>Add Admin</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

<!-- Location Assignment Modal -->
<div class="modal fade" id="locationAssignmentModal" tabindex="-1" aria-labelledby="locationAssignmentModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered"> <!-- Larger modal -->
		<div class="modal-content">
			<div class="modal-header bg-info text-white">
				<h5 class="modal-title" id="locationAssignmentModalLabel">Assign Location</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<form action="../action/admin_user_action/assigned_location_admin.php" method="POST">
				<div class="modal-body">
					<input type="hidden" name="admin_id" id="modal-admin-id">

					<div class="row">
						<!-- Map (left column) -->
						<div class="col-md-6 mb-3">
							<label class="form-label">Map Preview</label>
							<div id="location-map" style="height: 350px; border-radius: 8px;"></div>
						</div>

						<!-- Form (right column) -->
						<div class="col-md-6">
							<div class="mb-3">
								<label for="admin-name" class="form-label">Admin Name</label>
								<input type="text" class="form-control" id="admin-name" readonly>
							</div>

							<div class="mb-3">
								<label for="location-select" class="form-label">Select Location</label>
								<select name="evac_loc_id" id="location-select" class="form-select" required>
									<option value="" disabled selected>Select a location</option>
									<?php
									include '../../../database/session.php';

									if ($conn->connect_error) {
										die("Connection failed: " . $conn->connect_error);
									}

									$result = $conn->query("SELECT evac_loc_id, name, latitude, longitude FROM evac_loc_table ORDER BY name ASC");

									if ($result && $result->num_rows > 0) {
										while ($row = $result->fetch_assoc()) {
											echo '<option value="' . htmlspecialchars($row['evac_loc_id']) . '" data-lat="' . htmlspecialchars($row['latitude']) . '" data-lng="' . htmlspecialchars($row['longitude']) . '">' . htmlspecialchars($row['name']) . '</option>';
										}
									} else {
										echo '<option disabled>No locations found</option>';
									}

									$conn->close();
									?>
								</select>
							</div>
						</div>
					</div>
				</div>

				<div class="modal-footer">
					<button type="submit" class="btn btn-primary">Assign</button>
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
				</div>
			</form>
		</div>
	</div>
</div>


<!-- LOCATION SCRIPT -->

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
	document.addEventListener("DOMContentLoaded", () => {
		window.map = L.map('location-map').setView([12.8797, 121.774], 5); // Center Philippines
		L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
			attribution: '&copy; OpenStreetMap contributors'
		}).addTo(window.map);

		window.marker = L.marker([12.8797, 121.774]).addTo(window.map);

		document.getElementById("location-select").addEventListener("change", function() {
			const selected = this.options[this.selectedIndex];
			const lat = parseFloat(selected.getAttribute("data-lat"));
			const lng = parseFloat(selected.getAttribute("data-lng"));

			if (!isNaN(lat) && !isNaN(lng)) {
				window.map.setView([lat, lng], 13);
				window.marker.setLatLng([lat, lng]).bindPopup('<span style="font-size:16px;">' + selected.text + '</span>').openPopup();
			} else {
				window.map.setView([12.8797, 121.774], 5);
				window.marker.setLatLng([12.8797, 121.774]).bindPopup('<span style="font-size:12px;">No location assigned</span>').openPopup();
			}
		});
		// Refresh map size when modal is shown
		const modal = document.getElementById('locationAssignmentModal');
		modal.addEventListener('shown.bs.modal', () => {
			window.map.invalidateSize();

			// Get the currently selected option
			const select = document.getElementById("location-select");
			const selected = select.options[select.selectedIndex];
			const lat = parseFloat(selected.getAttribute("data-lat"));
			const lng = parseFloat(selected.getAttribute("data-lng"));

			if (!isNaN(lat) && !isNaN(lng)) {
				// Normal font size for assigned location
				window.marker.setLatLng([lat, lng]).bindPopup('<span style="font-size:16px;">' + selected.text + '</span>').openPopup();
			} else {
				// Small font size for "No location assigned"
				window.marker.setLatLng([12.8797, 121.774]).bindPopup('<span style="font-size:12px;">No location assigned</span>').openPopup();
			}
		});
	});
</script>

<!-- POPULATED THE LOCATION -->

<script>
	document.querySelectorAll('.assign-location-btn').forEach(button => {
		button.addEventListener('click', function() {
			const adminId = this.getAttribute('data-id');
			const adminName = this.getAttribute('data-name');
			const locationId = this.getAttribute('data-location-id');
			const locationLat = this.getAttribute('data-location-lat');
			const locationLng = this.getAttribute('data-location-lng');

			document.getElementById('modal-admin-id').value = adminId;
			document.getElementById('admin-name').value = adminName;

			const select = document.getElementById('location-select');
			const mapDiv = document.getElementById('location-map');

			if (locationId) {
				select.value = locationId;
				select.dispatchEvent(new Event('change'));
			} else {
				select.selectedIndex = 0;
				// Reset the map to default view (Philippines)
				if (window.map && window.marker) {
					window.map.setView([12.8797, 121.774], 5);
					window.marker.setLatLng([12.8797, 121.774]).bindPopup('No location assigned').openPopup();
				}
			}
		});
	});
</script>


<script>
	document.querySelectorAll('.assign-location-btn').forEach(button => {
		button.addEventListener('click', function() {
			const adminId = this.getAttribute('data-id');
			const adminName = this.getAttribute('data-name');

			document.getElementById('modal-admin-id').value = adminId;
			document.getElementById('admin-name').value = adminName;
		});
	});
</script>

<!-- EDIT MODAL -->
<div class="modal fade" id="editAdminModal" tabindex="-1" aria-labelledby="editAdminModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="editAdminModalLabel">Edit Admin</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form action="../action/admin_user_action/edit_admin_user.php" method="POST">
					<input type="hidden" name="admin_id" id="edit-admin-id">

					<div class="mb-3">
						<label class="form-label">Username</label>
						<input type="text" class="form-control" name="username" id="edit-username" required>
						<small id="usernameFeedback1" class="text-danger"></small>
					</div>

					<!-- Include jQuery -->
					<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
					<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

					<script>
						$(document).ready(function() {
							let typingTimer;
							let doneTypingInterval = 500; // 0.5s delay for real-time update

							$('#edit-username').on('input', function() {
								clearTimeout(typingTimer);
								let username = $(this).val().trim();

								if (username === "") {
									$('#usernameFeedback1').html('').css('color', '');
									$('#edit-username').removeClass('is-valid is-invalid');
									return;
								}

								typingTimer = setTimeout(function() {
									checkUsername(username);
								}, doneTypingInterval);
							});

							function checkUsername(username) {
								$.ajax({
									url: '../check_validation/admin_username.php',
									type: 'POST',
									data: {
										username: username
									},
									success: function(response) {
										console.log("Server Response:", response); // Debugging log
										response = response.trim(); // Remove spaces

										if (response === 'taken') {
											$('#usernameFeedback1').html('<i class="fas fa-exclamation-circle"></i> Username is already taken.')
												.css({
													'color': 'red',
													'font-weight': 'bold'
												});
											$('#edit-username').addClass('is-invalid').removeClass('is-valid');
										} else if (response === 'available') {
											$('#usernameFeedback1').html('<i class="fas fa-check-circle" style="color: green;"></i> Username available.')
												.css({
													'color': 'green',
													'font-weight': 'bold'
												});
											$('#edit-username').addClass('is-valid').removeClass('is-invalid');
										} else {
											$('#usernameFeedback1').html('<i class="fas fa-exclamation-triangle"></i> Error checking username.')
												.css({
													'color': 'orange',
													'font-weight': 'bold'
												});
											$('#edit-username').addClass('is-invalid').removeClass('is-valid');
										}
									},
									error: function(xhr, status, error) {
										console.log("AJAX Error:", status, error); // Debugging log
										$('#usernameFeedback1').html('<i class="fas fa-exclamation-triangle"></i> Error connecting to server.')
											.css({
												'color': 'orange',
												'font-weight': 'bold'
											});
										$('#edit-username').addClass('is-invalid').removeClass('is-valid');
									}
								});
							}
						});
					</script>
					<div class="mb-3">
						<label class="form-label">New Password</label>
						<input type="password" class="form-control" name="password" id="edit-password" required>
					</div>
					<div class="mb-3">
						<label class="form-label">First Name</label>
						<input type="text" class="form-control" name="f_name" id="edit-fname" required>
					</div>
					<div class="mb-3">
						<label class="form-label">Last Name</label>
						<input type="text" class="form-control" name="l_name" id="edit-lname" required>
					</div>
					<div class="mb-3">
						<label for="role" class="form-label">Role</label>
						<select class="form-select" id="edit-role" name="role" required>
							<option value="Admin">Admin</option>
							<option value="Staff">Staff</option>
						</select>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
						<button type="submit" class="btn btn-primary" id="addAdminBtn">
							<i class="fas fa-save me-1"></i> Update Profile
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

<!-- JavaScript to Populate Modal with Data -->
<script>
	document.addEventListener("DOMContentLoaded", function() {
		document.querySelectorAll('.edit-btn').forEach(button => {
			button.addEventListener('click', function() {
				document.getElementById('edit-admin-id').value = this.dataset.id;
				document.getElementById('edit-username').value = this.dataset.username;
				document.getElementById('edit-password').value = this.dataset.password;
				document.getElementById('edit-fname').value = this.dataset.fname;
				document.getElementById('edit-lname').value = this.dataset.lname;
				document.getElementById('edit-role').value = this.dataset.role;
			});
		});
	});
</script>

<!-- DELETE MODAL -->
<div class="modal fade" id="deleteAdminModal" tabindex="-1" aria-labelledby="deleteAdminModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="deleteAdminModalLabel">Confirm Deletion</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<p>Are you sure you want to delete this admin?</p>
				<form id="deleteForm" action="../action/admin_user_action/delete_admin_user.php" method="POST">
					<input type="hidden" name="admin_id" id="delete-admin-id">
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
				<button type="submit" class="btn btn-danger" onclick="document.getElementById('deleteForm').submit();">
					<i class="fas fa-trash"></i> Delete
				</button>
			</div>
		</div>
	</div>
</div>

<script>
	document.addEventListener("DOMContentLoaded", function() {
		document.querySelectorAll('.delete-btn').forEach(button => {
			button.addEventListener('click', function() {
				let adminId = this.getAttribute("data-id");
				document.getElementById("delete-admin-id").value = adminId;
			});
		});
	});
</script>