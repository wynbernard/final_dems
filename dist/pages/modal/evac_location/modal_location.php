<div class="modal fade" id="addLocationModal" tabindex="-1" aria-labelledby="addLocationModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-centered">
		<div class="modal-content shadow rounded-4 border-0">
			<div class="modal-header border-0 pb-0">
				<h5 class="modal-title fw-semibold text-primary" id="addLocationModalLabel">🗺️ Add New Location</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="row g-4">
					<!-- Right side: Form (col-md-5) -->
					<div class="col-md-5 order-md-last">
						<form action="../action/evac_location_action/add_location.php" method="POST" class="needs-validation" novalidate>

							<!-- Location Name -->
							<div class="mb-3">
								<label for="locationName" class="form-label fw-medium">📍 Location Name</label>
								<input type="text" class="form-control rounded-3 shadow-sm" id="locationName" name="location_name" required>
							</div>

							<!-- City -->
							<div class="mb-3">
								<label class="form-label fw-medium">🏙️ City</label>
								<input type="text" class="form-control rounded-3 shadow-sm" name="city" value ="Bago City" readonly>
							</div>

							<!-- Barangay -->
							<div class="mb-3">
								<label class="form-label fw-medium">📌 Barangay</label>
								<select name="barangay" id="barangay_id" class="form-select rounded-3 shadow-sm" required>
									<option value="">Select Barangay</option>
									<?php
									include '../../../../database/session.php';
									$query = "SELECT barangay_id, barangay_name, latitude, longitude FROM barangay_manegement_table ORDER BY barangay_name ASC";
									$result = mysqli_query($conn, $query);

									if ($result && mysqli_num_rows($result) > 0) {
										while ($row = mysqli_fetch_assoc($result)) {
											echo '<option value="' . htmlspecialchars($row['barangay_name']) . '" data-lat="' . $row['latitude'] . '" data-lng="' . $row['longitude'] . '">'
												. htmlspecialchars($row['barangay_name']) . '</option>';
										}
									}
									?>
								</select>
							</div>
							<!-- Purok -->
							<div class="mb-3">
								<label for="purokLocation" class="form-label fw-medium">🏘️ Purok</label>
								<input type="text" class="form-control rounded-3 shadow-sm" id="purokLocation" name="purok" required>
							</div>

							<!-- Capacity -->
							<div class="mb-3">
								<label for="totalCapacity" class="form-label fw-medium">👥 Total Capacity</label>
								<input type="number" class="form-control rounded-3 shadow-sm" id="totalCapacity" name="total_capacity" required>
							</div>

							<!-- Hidden Fields for Latitude & Longitude -->
							<input type="hidden" id="latitude" name="latitude">
							<input type="hidden" id="longitude" name="longitude">

							<!-- Submit Section -->
							<div class="d-flex justify-content-end pt-2">
								<button type="button" class="btn btn-outline-secondary me-2 rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
								<button type="submit" class="btn btn-primary rounded-pill px-4">Add Location</button>
							</div>
						</form>
					</div>
					<!-- Left side: Map (col-md-7) -->
					<div class="col-md-7 order-md-first">
						<label class="form-label fw-medium">🗺️ Location Map</label>
						<div id="locationMap" class="rounded-4 shadow-sm border" style="height: 400px; width: 100%;"></div>

						<!-- Display coordinates -->
						<div class="mt-3">
							<strong>Selected Coordinates:</strong>
							<p id="coordinatesDisplay">Click on the map to select a location.</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Edit Location Modal -->
<div class="modal fade" id="editLocationModal" tabindex="-1" aria-labelledby="editLocationModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="editLocationModalLabel">Edit Location</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form action="../action/evac_location_action/edit_location.php" method="POST">
					<input type="hidden" id="editLocationId" name="evac_loc_id">

					<div class="row">
						<!-- Left: Map -->
						<div class="col-md-6">
							<label class="form-label">Location Map</label>
							<div id="editLocationMap" class="rounded border mb-2" style="height: 300px;"></div>

							<!-- Hidden lat/lng fields -->
							<input type="hidden" id="editLatitude" name="latitude">
							<input type="hidden" id="editLongitude" name="longitude">

							<!-- Coordinates display -->
							<div id="editCoordinatesDisplay" class="text-muted fst-italic">
								Latitude: <span id="editLatDisplay">10.3157</span>,
								Longitude: <span id="editLngDisplay">123.8854</span>
							</div>
						</div>

						<!-- Right: Form Fields -->
						<div class="col-md-6">
							<div class="mb-3">
								<label for="editLocationName" class="form-label">Location Name</label>
								<input type="text" class="form-control" id="editLocationName" name="location_name" required>
							</div>

							<div class="mb-3">
								<label for="editLocationCity" class="form-label">City</label>
								<input type="text" class="form-control" id="editLocationCity" name="location_city" required>
							</div>
							<div class="mb-3">
								<label for="editLocationCity" class="form-label">Barangay</label>
								<input type="text" class="form-control" id="displayBarangay" name="" readonly>
							</div>
							<div class="mb-3">
								<label class="form-label fw-medium">📌 Barangay</label>
								<select name="barangay" id="barangay_id" class="form-select rounded-3 shadow-sm">
									<option value="">Select Barangay</option>
									<?php
									include '../../../../database/session.php';
									$query = "SELECT barangay_id, barangay_name, latitude, longitude FROM barangay_manegement_table ORDER BY barangay_name ASC";
									$result = mysqli_query($conn, $query);

									if ($result && mysqli_num_rows($result) > 0) {
										while ($row = mysqli_fetch_assoc($result)) {
											echo '<option value="' . htmlspecialchars($row['barangay_name']) . '" data-lat="' . $row['latitude'] . '" data-lng="' . $row['longitude'] . '">'
												. htmlspecialchars($row['barangay_name']) . '</option>';
										}
									}
									?>
								</select>
							</div>

							<div class="mb-3">
								<label for="editLocationPurok" class="form-label">Purok</label>
								<input type="text" class="form-control" id="editLocationPurok" name="location_purok" required>
							</div>

							<div class="mb-3">
								<label for="editTotalCapacity" class="form-label">Total Capacity</label>
								<input type="number" class="form-control" id="editTotalCapacity" name="total_capacity" required>
							</div>
						</div>
					</div>

					<!-- Footer -->
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
						<button type="submit" class="btn btn-primary">Update Location</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

<!-- For edit populated -->
<script src="../scripts/evac_location_script/edit_location.js"></script>
<!-- For add map -->
<script src="../scripts/evac_location_script/modal_location.js"></script>

<div class="modal fade" id="deleteLocationModal" tabindex="-1" aria-labelledby="deleteLocationModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="deleteLocationModalLabel">Confirm Delete</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<p>Are you sure you want to delete this location?</p>
			</div>
			<div class="modal-footer">
				<form action="../action/delete_location.php" method="POST">
					<input type="hidden" id="deleteLocationId" name="evac_loc_id">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
					<button type="submit" class="btn btn-danger">Delete</button>
				</form>
			</div>
		</div>
	</div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>