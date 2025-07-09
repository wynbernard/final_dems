<!-- Register IDP Modal -->
<div class="modal fade" id="registerIDPModal" tabindex="-1" aria-labelledby="registerIDPModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="registerIDPModalLabel">Register New IDP</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form id="idpRegistrationForm" method="POST" action="../action/registration_staff.php">
					<div class="alert alert-info mb-3">
						<strong>Location:</strong>
						<?php
						// Safely get parameters with null coalescing operator
						$selectedLocationId = $_GET['location_id'] ?? $_SESSION['evac_loc_id'] ?? '';

						if ($selectedLocationId) {
							// Query location name using prepared statement
							$stmt = $conn->prepare("SELECT name FROM evac_loc_table WHERE evac_loc_id = ?");
							$stmt->bind_param("s", $selectedLocationId);
							$stmt->execute();
							$result = $stmt->get_result();

							if ($result->num_rows > 0) {
								$location = $result->fetch_assoc();
								echo htmlspecialchars($location['name']);
							} else {
								echo "Unknown Location (ID: " . htmlspecialchars($selectedLocationId) . ")";
							}
						} else {
							// Check if evac_loc_id exists but wasn't captured
							$fallbackEvacId = $_GET['evac_loc_id'] ?? '';
							if ($fallbackEvacId) {
								// Use prepared statement to prevent SQL injection
								$stmt = $conn->prepare("SELECT evac_loc_table.name FROM admin_table
								LEFT JOIN evac_loc_table ON admin_table.evac_loc_id = evac_loc_table.evac_loc_id
								 WHERE evac_loc_id = ?");
								$stmt->bind_param("s", $fallbackEvacId);

								if ($stmt->execute()) {
									$result = $stmt->get_result();

									if ($result->num_rows > 0) {
										$location = $result->fetch_assoc();
										echo htmlspecialchars($location['name']);
									} else {
										echo "Unknown Location (ID: " . htmlspecialchars($fallbackEvacId) . ")";
									}
								} else {
									// Handle query error
									echo "Location ID: " . htmlspecialchars($fallbackEvacId) . " (Error fetching details)";
								}

								$stmt->close();
							} else {
								echo "No location selected";
							}
						}
						?>
						<input type="hidden" name="location_id" value="<?php echo htmlspecialchars($selectedLocationId); ?>">
					</div>
					<div class="mb-3">
						<label for="idpName" class="form-label">Full Name</label>
						<input type="text" class="form-control" id="idpName" name="name" required autocomplete="off"
							placeholder="Start typing to search...">
						<input type="hidden" name="pre_reg_id" id="preRegId">
						<div id="nameSuggestions" class="list-group position-absolute" style="display: none; z-index: 1000; max-height: 200px; overflow-y: auto; width: calc(100% - 30px);"></div>
						<small id="noMatchWarning" class="text-danger mt-1 d-none">No IDPs found.</small>
					</div>

					<div class="mb-3">
						<label for="room" class="form-label">Room</label>
						<select class="form-select" id="room" name="room" required>
							<option value="" disabled selected>Select a room</option>
						</select>
					</div>
					<div class="mb-3">
						<label for="disasterDropdown" class="form-label">Disaster</label>
						<select class="form-select" id="disasterDropdown" name="disasterDropdown" required>
							<option value="" disabled selected>Select a Disaster</option>
							<?php
							// Include the database connection file
							include '../../../database/session.php'; // Adjust the path to your session/database connection file

							// Query to fetch disasters from the database
							$query = "SELECT disaster_id, disaster_name FROM disaster_table";
							$result = mysqli_query($conn, $query);

							// Check if the query was successful and returned results
							if ($result && mysqli_num_rows($result) > 0) {
								// Loop through the results and generate <option> elements
								while ($row = mysqli_fetch_assoc($result)) {
									echo '<option value="' . htmlspecialchars($row['disaster_id']) . '">' . htmlspecialchars($row['disaster_name']) . '</option>';
								}
							} else {
								// Fallback option if no disasters are found in the database
								echo '<option value="" disabled>No disasters available</option>';
							}

							// Close the database connection (optional, as it may be handled elsewhere)
							mysqli_close($conn);
							?>
						</select>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
				<button type="submit" form="idpRegistrationForm" class="btn btn-primary" id="registerBtn">Register</button>
			</div>
		</div>
	</div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
	document.addEventListener("DOMContentLoaded", function() {
		const inputField = document.getElementById("idpName");
		const suggestionsDiv = document.getElementById("nameSuggestions");
		const preRegIdField = document.getElementById("preRegId");

		// Function to fetch suggestions from database via AJAX
		function fetchSuggestions(query) {
			return new Promise((resolve, reject) => {
				if (query.length < 2) { // Don't search for very short queries
					resolve([]);
					return;
				}

				$.ajax({
					url: '../fetch_data/fetch_idps_staff.php', // Create this file
					method: 'GET',
					data: {
						query: query
					},
					dataType: 'json',
					success: function(data) {
						resolve(data);
					},
					error: function(xhr, status, error) {
						console.error("Error fetching suggestions:", error);
						resolve([]);
					}
				});
			});
		}

		// Function to display suggestions
		function displaySuggestions(suggestions) {
			suggestionsDiv.innerHTML = "";
			const registerBtn = document.getElementById("registerBtn");
			const warning = document.getElementById("noMatchWarning");

			if (suggestions.length === 0) {
				const noResultItem = document.createElement("button");
				noResultItem.className = "list-group-item list-group-item-action disabled";
				noResultItem.textContent = "No IDPs found";
				noResultItem.disabled = true;
				suggestionsDiv.appendChild(noResultItem);
				suggestionsDiv.style.display = "block";

				// Show warning
				warning.classList.remove("d-none");

				// Disable the Register button
				registerBtn.disabled = true;
				return;
			}

			// Hide warning if there are suggestions
			warning.classList.add("d-none");

			// Disable Register until one is selected
			registerBtn.disabled = true;

			suggestions.forEach(suggestion => {
				const suggestionItem = document.createElement("button");
				suggestionItem.className = "list-group-item list-group-item-action";
				suggestionItem.textContent = suggestion.name;

				suggestionItem.addEventListener("click", () => {
					inputField.value = suggestion.name;
					if (suggestion.id) {
						preRegIdField.value = suggestion.id;
					}
					suggestionsDiv.style.display = "none";

					// Enable Register
					registerBtn.disabled = false;
					warning.classList.add("d-none");
				});

				suggestionsDiv.appendChild(suggestionItem);
			});

			suggestionsDiv.style.display = "block";
		}


		// Debounce function to limit API calls
		function debounce(func, wait) {
			let timeout;
			return function(...args) {
				clearTimeout(timeout);
				timeout = setTimeout(() => func.apply(this, args), wait);
			};
		}

		// Debounced input handler
		const handleInput = debounce(async function() {
			const query = inputField.value.trim();
			if (query.length === 0) {
				suggestionsDiv.style.display = "none";
				return;
			}

			const suggestions = await fetchSuggestions(query);
			displaySuggestions(suggestions);
		}, 300);

		// Event listener for input changes
		inputField.addEventListener("input", handleInput);

		// Hide suggestions when clicking outside
		document.addEventListener("click", function(event) {
			if (!suggestionsDiv.contains(event.target) && event.target !== inputField) {
				suggestionsDiv.style.display = "none";
			}
		});
	});
</script>

<style>
	#nameSuggestions {
		background-color: white;
		border: 1px solid #ced4da;
		border-top: none;
	}

	#nameSuggestions button {
		cursor: pointer;
		text-align: left;
		border-radius: 0 !important;
		border-left: none;
		border-right: none;
		padding: 8px 12px;
	}

	#nameSuggestions button:hover {
		background-color: #f8f9fa;
	}

	#nameSuggestions button:active {
		background-color: #e2e6ea;
	}
</style>
<!-- for the room dropdown -->
<script>
	document.addEventListener("DOMContentLoaded", function() {
		const roomDropdown = document.getElementById("room");

		async function fetchRooms(locationId) {
			try {
				const response = await fetch(`../fetch_data/fetch_room_staff.php?location_id=${locationId}`);
				if (!response.ok) throw new Error("Failed to fetch rooms");
				const rooms = await response.json();

				roomDropdown.innerHTML = '<option value="" disabled selected>Select a room</option>';

				rooms.forEach(room => {
					const option = document.createElement("option");
					option.value = room.id;
					// Display format: "Room Name (Occupied/Capacity)"
					option.textContent = `${room.name} (${room.current_occupancy}/${room.capacity})`;

					// Disable option if room is full
					if (room.current_occupancy >= room.capacity) {
						option.disabled = true;
						option.textContent += " - FULL";
					}

					roomDropdown.appendChild(option);
				});
			} catch (error) {
				console.error("Error fetching rooms:", error);
				roomDropdown.innerHTML = '<option value="" disabled selected>Error loading rooms</option>';
			}
		}

		const locationId = document.querySelector("input[name='location_id']").value;
		if (locationId) {
			fetchRooms(locationId);
		} else {
			roomDropdown.innerHTML = '<option value="" disabled selected>No location selected</option>';
		}
	});
</script>
<div class="modal fade" id="registerChoiceModal" tabindex="-1" aria-labelledby="registerChoiceModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="registerChoiceModalLabel">Register IDP</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<!-- Button for Manual Registration -->
				<button type="button"
					class="btn btn-success w-100 mb-2"
					id="manualRegistrationBtn"
					data-bs-toggle="modal"
					data-bs-target="#registerIDPModal">
					<i class="fas fa-user-plus me-2"></i> Manual Registration
				</button>
				<!-- Button for Scanning QR Code -->
				<button type="button" class="btn btn-info w-100" id="scanQRBtn">
					<i class="fas fa-qrcode me-2"></i> Scan QR Code
				</button>
			</div>
		</div>
	</div>
</div>

<!-- QR Scanner Modal -->
<div class="modal fade" id="qrScannerModal" tabindex="-1" aria-labelledby="qrScannerModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header bg-primary text-white">
				<h5 class="modal-title" id="qrScannerModalLabel">Family Member Registration</h5>
				<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-6">
						<!-- QR Scanner Section -->
						<div class="card mb-4">
							<div class="card-header bg-info text-white">
								<h6 class="mb-0"><i class="fas fa-qrcode me-2"></i>Scan QR Code</h6>
							</div>
							<div class="card-body text-center">
								<div id="qrScanner" style="width: 100%; height: 300px; border: 2px dashed #ccc;"></div>
								<div class="mt-3">
									<button id="startScannerBtn" class="btn btn-primary me-2">
										<i class="fas fa-play me-1"></i> Start Scanner
									</button>
									<button id="stopScannerBtn" class="btn btn-danger" disabled>
										<i class="fas fa-stop me-1"></i> Stop
									</button>
								</div>
								<div id="scannerStatus" class="mt-2 small text-muted"></div>
							</div>
						</div>
					</div>

					<div class="col-md-6">
						<!-- Family Member Information -->
						<div class="card">
							<div class="card-header bg-success text-white">
								<h6 class="mb-0"><i class="fas fa-users me-2"></i>Family Members</h6>
							</div>
							<div class="card-body">
								<div id="familyInfo" class="text-center py-4">
									<i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
									<p class="text-muted">No family member scanned yet</p>
								</div>
								<div id="familyList" style="max-height: 300px; overflow-y: auto; display: none;">
									<!-- Dynamic content will appear here -->
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Selected Family Members -->
				<div class="card mt-3">
					<div class="card-header bg-warning">
						<h6 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Selected for Registration</h6>
					</div>
					<div class="card-body">
						<div id="selectedFamily" class="alert alert-info mb-0">
							No family members selected yet
						</div>
						<div class="mt-3">
							<label class="form-label">Assign to Room:</label>
							<select id="familyRoomAssignment" class="form-select" disabled>
								<option value="">Select room after scanning</option>
							</select>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
				<button type="button" id="registerFamilyBtn" class="btn btn-success" disabled>
					<i class="fas fa-user-plus me-1"></i> Register Selected Members
				</button>
			</div>
		</div>
	</div>
</div>

<!-- JavaScript for QR Scanner Functionality -->
<script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.4/dist/html5-qrcode.min.js"></script>

<?php include '../scripts/scanner.php'; ?>
<?php include '../css/scanner_ui.php'; ?>

<script>
	document.addEventListener("DOMContentLoaded", function() {
		// Utility to get location_id from hidden input
		function getLocationId() {
			const input = document.querySelector("input[name='location_id']");
			return input ? input.value : null;
		}

		// Utility to check if location name is shown
		function isLocationNameShown() {
			const locationAlert = document.querySelector('.alert.alert-info.mb-3');
			if (!locationAlert) return false;
			const text = locationAlert.textContent || "";
			// Check for fallback messages or empty
			if (
				text.includes("No location selected") ||
				text.includes("Unknown Location") ||
				text.trim() === ""
			) {
				return false;
			}
			return true;
		}

		// Prevent opening registerChoiceModal if location is empty or name not shown
		const registerChoiceModal = document.getElementById('registerChoiceModal');
		if (registerChoiceModal) {
			registerChoiceModal.addEventListener('show.bs.modal', function(e) {
				if (!getLocationId() || !isLocationNameShown()) {
					e.preventDefault();
					Swal.fire({
						icon: 'warning',
						title: 'No Location Selected',
						text: 'Please select a location first.',
						confirmButtonColor: '#3085d6'
					});
					return false;
				}
			});
		}
	});
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>