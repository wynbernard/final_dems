<!-- Add Location Modal -->
<div class="modal fade" id="addLocationModal" tabindex="-1" aria-labelledby="addLocationModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl">
		<form id="addLocationForm" method="POST" action="../action/brgy_management_action/add_brgy.php" enctype="multipart/form-data">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="addLocationModalLabel">Add New Barangay</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="row">
						<!-- Map Column -->
						<div class="col-md-6 mb-3">
							<label class="form-label">Select Location on Map</label>
							<div id="locationMap" style="height: 400px; border: 1px solid #ced4da; border-radius: 0.25rem;"></div>
							<small class="form-text text-muted">Click on the map to set the barangay center location.</small>
							<input type="hidden" id="latitude" name="latitude">
							<input type="hidden" id="longitude" name="longitude">
							<div id="coordinatesDisplay" class="mt-2 text-secondary"></div>
						</div>
						<!-- Form Column -->
						<div class="col-md-6">
							<div class="mb-3">
								<label for="add_barangay_name" class="form-label">Barangay Name</label>
								<input type="text" class="form-control" id="add_barangay_name" name="barangay_name" required>
							</div>

							<div class="mb-3">
								<label for="add_barangay_captain" class="form-label">Captain Name</label>
								<input type="text" class="form-control" id="add_barangay_captain" name="barangay_captain_name" required>
							</div>

							<!-- Signature Input Section -->
							<div class="mb-3">
								<label class="form-label">Signature</label>

								<div class="form-check">
									<input class="form-check-input" type="radio" name="signature_option" id="option_draw" value="draw" checked onchange="toggleSignatureInput()">
									<label class="form-check-label" for="option_draw">Draw Signature</label>
								</div>
								<div class="form-check">
									<input class="form-check-input" type="radio" name="signature_option" id="option_upload" value="upload" onchange="toggleSignatureInput()">
									<label class="form-check-label" for="option_upload">Upload Signature</label>
								</div>
								<!-- Draw Signature Canvas -->
								<div id="signature-draw" class="mt-2">
									<canvas id="signature-pad" width="400" height="150" style="border: 1px solid #ccc; border-radius: 4px; width: 100%; touch-action: none;"></canvas>
									<input type="hidden" name="signature_data" id="signature_data">
									<button type="button" class="btn btn-sm btn-secondary mt-2" onclick="clearSignature()">Clear</button>
								</div>
								<!-- Upload Signature File -->
								<div id="signature-upload" class="mt-2" style="display: none;">
									<input type="file" name="signature_file" id="signature_file" class="form-control" accept="image/*">
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-success">Add Barangay</button>
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
				</div>
			</div>
		</form>
	</div>
</div>

<!-- Edit Location Modal -->
<div class="modal fade" id="editLocationModal" tabindex="-1" aria-labelledby="editLocationModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl">
		<form id="editLocationForm" method="POST" action="../action/brgy_management_action/edit_barangay.php" enctype="multipart/form-data">
			<div class="modal-content">
				<div class="modal-header bg-primary text-white">
					<h5 class="modal-title" id="editLocationModalLabel">Edit Barangay</h5>
					<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>

				<div class="modal-body">
					<input type="hidden" name="barangay_id" id="edit_barangay_id">
					<input type="hidden" name="current_signature" id="edit_current_signature">

					<div class="row">
						<!-- Map -->
						<div class="col-md-6 mb-3">
							<label class="form-label fw-bold">Barangay Location</label>
							<div id="editLocationMap" style="height: 400px; border: 1px solid #ced4da; border-radius: 0.25rem;"></div>
							<small class="form-text text-muted">Click or drag marker to update the location.</small>
							<input type="hidden" name="latitude" id="edit_latitude">
							<input type="hidden" name="longitude" id="edit_longitude">
							<div id="editCoordinatesDisplay" class="mt-2 text-secondary"></div>
						</div>

						<!-- Form Inputs -->
						<div class="col-md-6">
							<div class="mb-3">
								<label for="edit_barangay_name" class="form-label fw-bold">Barangay Name</label>
								<input type="text" class="form-control" id="edit_barangay_name" name="barangay_name" required>
							</div>

							<div class="mb-3">
								<label for="edit_barangay_captain" class="form-label fw-bold">Captain Name</label>
								<input type="text" class="form-control" id="edit_barangay_captain" name="barangay_captain_name" required>
							</div>

							<div class="mb-3">
								<label class="form-label fw-bold">Current Signature</label><br>
								<img id="edit_signature_preview" src="" alt="Signature Preview" style="max-height: 80px; border: 1px solid #ddd; padding: 4px; border-radius: 4px; background-color: white;">
							</div>
							<div class="mb-3">
								<label for="edit_signature_file" class="form-label fw-bold">Upload New Signature (optional)</label>
								<input type="file" class="form-control" name="signature_file" id="edit_signature_file" accept="image/*">
								<small class="form-text text-muted">Leave blank to keep the current signature.</small>
							</div>
						</div>
					</div>
				</div>

				<div class="modal-footer">
					<button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Changes</button>
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
				</div>
			</div>
		</form>
	</div>
</div>

<!-- Delete Location Modal -->
<div class="modal fade" id="deleteLocationModal" tabindex="-1" aria-labelledby="deleteLocationModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<form id="deleteLocationForm" method="POST" action="../action/brgy_management_action/delete_brgy.php">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="deleteLocationModalLabel">Confirm Deletion</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					Are you sure you want to delete this barangay?
					<input type="hidden" name="barangay_id" id="delete_barangay_id">
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-danger">Delete</button>
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
				</div>
			</div>
		</form>
	</div>
</div>

<!-- View Barangay Modal -->
<div class="modal fade" id="viewBarangayModal" tabindex="-1" aria-labelledby="viewBarangayModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header bg-primary text-white">
				<h5 class="modal-title" id="viewBarangayModalLabel">Barangay Details</h5>
				<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="row">
					<!-- Map Column -->
					<div class="col-md-6 mb-3">
						<label class="form-label fw-bold">Barangay Location</label>
						<div id="modalMap" style="height: 400px; border: 1px solid #ced4da; border-radius: 0.25rem;"></div>
						<small class="form-text text-muted">Map is centered on the barangay location.</small>
					</div>
					<!-- Details Column -->
					<div class="col-md-6">
						<div class="mb-3">
							<label class="form-label fw-bold">Barangay Name</label>
							<p id="modalBarangayName" class="form-control-plaintext"></p>
						</div>
						<div class="mb-3">
							<label class="form-label fw-bold">Barangay Captain</label>
							<p id="modalCaptainName" class="form-control-plaintext"></p>
						</div>
						<div class="mb-3">
							<label class="form-label fw-bold">Signature</label><br>
							<img id="modalSignature" src="" alt="Signature" style="max-height: 80px;" class="img-fluid border bg-white p-1 rounded">
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>


<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.6/dist/signature_pad.umd.min.js"></script>

<script src="../scripts/evac_location_script/barangay_management.js?v=<?php echo time(); ?>"></script>
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<!-- Leaflet.Pip -->
<script src="https://unpkg.com/leaflet-pip@latest/leaflet-pip.min.js"></script>