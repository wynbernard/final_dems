<!-- QR Scanner Modal -->
<div class="modal fade" id="scanQRModal" tabindex="-1" aria-labelledby="scanQRModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<div class="modal-header bg-success text-white">
				<h5 class="modal-title" id="scanQRModalLabel">Scan QR to Receive Resources</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
			</div>
			<div class="modal-body">
    <div class="row">
        <!-- Left: Scanner + Camera -->
        <div class="col-md-6">
            <div class="mb-2">
                <label for="cameraSelect" class="form-label">Select Camera</label>
                <select id="cameraSelect" class="form-select"></select>
            </div>

            <div id="qr-reader" class="qr-preview mb-3"></div>

            <div class="mt-2">
                <button class="btn btn-sm btn-success" id="startScannerBtn">Start Scanner</button>
                <button class="btn btn-sm btn-danger" id="stopScannerBtn" disabled>Stop Scanner</button>
            </div>
        </div>

        <!-- Right: Resources + Family Info -->
        <div class="col-md-6">
            <!-- Resources Selection -->
            <div class="mb-4">
                <h6>Select Resources to Distribute:</h6>
                <form id="resource-selection-form">
                    <?php
                    include '../../../database/conn.php';
                    $resourceQuery = "SELECT resource_id, resource_name, measurement_unit FROM resource_allocation_table ORDER BY resource_name ASC";
                    $result = $conn->query($resourceQuery);

                    if ($result && $result->num_rows > 0):
                        while ($row = $result->fetch_assoc()):
                            $resourceId = (int)$row['resource_id'];
                            $resourceLabel = htmlspecialchars($row['resource_name']);
                            $unit = htmlspecialchars($row['measurement_unit']);
                    ?>
                        <div class="form-check d-flex align-items-center mb-2">
                            <input class="form-check-input me-2" type="checkbox" name="resources[]" value="<?= $resourceId ?>" id="res-<?= $resourceId ?>">
                            <label class="form-check-label me-3" for="res-<?= $resourceId ?>"><?= $resourceLabel ?></label>
                            <input type="number" name="quantity[<?= $resourceId ?>]" class="form-control form-control-sm w-auto" value="1" min="1" style="width: 80px;">
                            <span class="ms-2"><?= $unit ?></span>
                        </div>
                    <?php
                        endwhile;
                    else:
                        echo "<p class='text-muted'>No resources available to select.</p>";
                    endif;
                    ?>
                </form>
            </div>

            <!-- Family Info -->
            <div id="family-info" class="d-none">
                <div class="alert alert-info">
                    <strong>Family Name:</strong> <span id="family-name"></span><br>
                    <strong>Registered Location:</strong> <span id="evacuee-location"></span>
                </div>
            </div>
        </div>
    </div>
</div>

		</div>
	</div>
</div>
<script src="https://unpkg.com/html5-qrcode"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
	let qrScanner;
	let availableCameras = [];
	let lastScannedId = null;
	let debounceTimeout = null;
	let staffLocationId = null; // This should be set when page loads

	// Initialize staff location (you should set this based on logged-in staff data)
	async function initStaffLocation() {
		try {
			const response = await fetch("../fetch_data/get_staff_location.php");
			const data = await response.json();
			if (data.success) {
				staffLocationId = data.evac_loc_id;
				console.log("Staff assigned location ID:", staffLocationId);
			}
		} catch (e) {
			console.error("Failed to get staff location:", e);
		}
	}

	async function initCameraList() {
		const select = document.getElementById("cameraSelect");
		select.innerHTML = "";
		availableCameras = await Html5Qrcode.getCameras();
		availableCameras.forEach(cam => {
			const option = document.createElement("option");
			option.value = cam.id;
			option.text = cam.label;
			select.appendChild(option);
		});
	}

	async function startScanner() {
	const cameraId = document.getElementById("cameraSelect").value;
	qrScanner = new Html5Qrcode("qr-reader");

	try {
		await qrScanner.start(
			cameraId, {
				fps: 10,
				qrbox: 300
			},
			async (decodedText) => {
				const match = decodedText.trim().match(/pre_reg(?:_id)?:\s*(\d+)/i);
				if (!match) return;

				const preRegId = match[1];
				if (preRegId === lastScannedId) return;

				lastScannedId = preRegId;
				clearTimeout(debounceTimeout);
				debounceTimeout = setTimeout(() => lastScannedId = null, 3000);

				// First, validate evacuee location
				try {
					const locationCheckResponse = await fetch("../fetch_data/check_evacuess_location.php", {
						method: "POST",
						headers: {
							"Content-Type": "application/json",
						},
						body: JSON.stringify({
							pre_reg_id: preRegId,
							staff_evac_loc_id: staffLocationId
						})
					});

					// Check if response is ok
					if (!locationCheckResponse.ok) {
						throw new Error(`Location check failed: HTTP ${locationCheckResponse.status} - ${locationCheckResponse.statusText}`);
					}

					let locationData;
					try {
						locationData = await locationCheckResponse.json();
					} catch (jsonError) {
						console.error("Location check JSON parse error:", jsonError);
						Swal.fire({
							icon: 'error',
							title: 'Location Check Error',
							text: 'Invalid response format from location check service.',
							confirmButtonText: 'OK'
						});
						return;
					}

					if (!locationData.success) {
						Swal.fire({
							icon: 'error',
							title: 'Location Mismatch',
							text: locationData.message || 'This evacuee is not registered for this location.',
							timer: 3000,
							showConfirmButton: true
						});
						return;
					}

					// If location check passes, proceed with resource distribution
					const form = document.getElementById("resource-selection-form");
					const formData = new FormData(form);
					formData.append("pre_reg_id", preRegId);

					const response = await fetch("../fetch_data/recieve_resources_ajax.php", {
						method: "POST",
						body: formData
					});

					// Check if response is ok
					if (!response.ok) {
						throw new Error(`Resource distribution failed: HTTP ${response.status} - ${response.statusText}`);
					}

					let data;
					try {
						data = await response.json();
					} catch (jsonError) {
						console.error("Distribution response JSON parse error:", jsonError);
						Swal.fire({
							icon: 'error',
							title: 'Response Format Error',
							text: 'Invalid response format from distribution service. Please contact system administrator.',
							confirmButtonText: 'OK'
						});
						return;
					}

					if (data.success) {
						// Display evacuee information
						document.getElementById("family-name").textContent = data.name || "N/A";
						document.getElementById("evacuee-location").textContent = locationData.evacuee_location_name || "N/A";
						document.getElementById("family-info").classList.remove("d-none");

						// Enhanced success message with more details
						let successHtml = `
							<p><strong>Resources distributed to:</strong> ${data.name || 'recipient'}</p>
							<p><strong>Evacuee Location:</strong> ${locationData.evacuee_location_name}</p>
							<p><strong>Staff Location:</strong> ${locationData.staff_location_name}</p>
						`;

						// Add cost information if available
						if (data.total_cost) {
							successHtml += `<p><strong>Total Cost:</strong> ₱${data.total_cost}</p>`;
						}

						// Add items count if available
						if (data.items_count) {
							successHtml += `<p><strong>Items Distributed:</strong> ${data.items_count} type(s)</p>`;
						}

						Swal.fire({
							icon: 'success',
							title: 'Distribution Successful!',
							html: successHtml,
							timer: 4000,
							showConfirmButton: false
						});

					} else {
						// Handle specific error types from the PHP response
						let errorTitle = 'Distribution Failed';
						let errorIcon = 'warning';

						// Check for specific error patterns
						if (data.message) {
							if (data.message.includes('Database error') || data.message.includes('database')) {
								errorTitle = 'Database Error';
								errorIcon = 'error';
							} else if (data.message.includes('already received')) {
								errorTitle = 'Already Received Aid';
								errorIcon = 'info';
							} else if (data.message.includes('not found') || data.message.includes('not registered')) {
								errorTitle = 'Invalid Registration';
								errorIcon = 'error';
							} else if (data.message.includes('Insufficient stock')) {
								errorTitle = 'Stock Unavailable';
								errorIcon = 'warning';
							} else if (data.message.includes('Unauthorized')) {
								errorTitle = 'Access Denied';
								errorIcon = 'error';
							}
						}

						Swal.fire({
							icon: errorIcon,
							title: errorTitle,
							text: data.message || "Distribution failed. Please try again.",
							timer: 3000,
							showConfirmButton: true
						});
					}

				} catch (networkError) {
					console.error("Network or system error:", networkError);
					
					let errorMessage = 'Network connection failed. Please check your connection and try again.';
					let errorTitle = 'Connection Error';
					
					// Handle specific network errors
					if (networkError.message.includes('HTTP 500')) {
						errorMessage = 'Server error occurred. Please contact system administrator.';
						errorTitle = 'Server Error';
					} else if (networkError.message.includes('HTTP 404')) {
						errorMessage = 'Service not found. Please contact system administrator.';
						errorTitle = 'Service Error';
					} else if (networkError.message.includes('HTTP 403')) {
						errorMessage = 'Access forbidden. Please check your permissions.';
						errorTitle = 'Access Denied';
	} else if (networkError.message.includes('Failed to fetch')) {
						errorMessage = 'Unable to connect to server. Please check network connection.';
						errorTitle = 'Network Error';
					}

					Swal.fire({
						icon: 'error',
						title: errorTitle,
						text: errorMessage,
						confirmButtonText: 'OK',
						footer: '<small>Error details have been logged for troubleshooting.</small>'
					});
				}
			},
			(err) => console.warn("QR scanning error:", err)
		);

		document.getElementById("startScannerBtn").disabled = true;
		document.getElementById("stopScannerBtn").disabled = false;
		
	} catch (cameraError) {
		console.error("Camera initialization failed:", cameraError);
		
		let cameraErrorMessage = "Unable to access the camera.";
		
		// Handle specific camera errors
		if (cameraError.message.includes('Permission denied')) {
			cameraErrorMessage = "Camera access denied. Please allow camera permissions and try again.";
		} else if (cameraError.message.includes('not found')) {
			cameraErrorMessage = "No camera found. Please connect a camera and try again.";
		} else if (cameraError.message.includes('already in use')) {
			cameraErrorMessage = "Camera is already in use by another application.";
		}
		
		Swal.fire({
			icon: 'error',
			title: 'Camera Error',
			text: cameraErrorMessage,
			confirmButtonText: 'OK'
		});
	}
}
	async function stopScanner() {
		if (qrScanner) {
			await qrScanner.stop();
			await qrScanner.clear();
			qrScanner = null;
		}
		document.getElementById("startScannerBtn").disabled = false;
		document.getElementById("stopScannerBtn").disabled = true;
	}

	// Event listeners
	document.getElementById("startScannerBtn").addEventListener("click", startScanner);
	document.getElementById("stopScannerBtn").addEventListener("click", stopScanner);

	document.getElementById("scanQRModal")?.addEventListener("shown.bs.modal", async () => {
		await initCameraList();
		document.getElementById("family-info").classList.add("d-none");
		document.getElementById("family-name").textContent = "";
		
		// Initialize staff location if not already done
		if (!staffLocationId) {
			await initStaffLocation();
		}
	});
	
	document.getElementById("scanQRModal")?.addEventListener("hidden.bs.modal", stopScanner);

	// Initialize staff location when page loads
	document.addEventListener("DOMContentLoaded", initStaffLocation);
</script>



<style>
	.qr-preview {
		width: 100%;
		height: 300px;
		border: 1px solid #ccc;
		border-radius: 10px;
		overflow: hidden;
	}

	.qr-preview>* {
		width: 100% !important;
		height: 100% !important;
		object-fit: cover;
		border-radius: 10px;
	}
</style>