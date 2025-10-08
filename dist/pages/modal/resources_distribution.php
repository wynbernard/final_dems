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
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label for="cameraSelect" class="form-label">Select Camera</label>
                <div class="d-flex align-items-center gap-2">
                    <span id="scannerStatus" class="scanner-indicator scanner-indicator--idle">Idle</span>
                    <select id="cameraSelect" class="form-select"></select>
                </div>
            </div>

            <div id="qr-reader" class="qr-preview mb-3 position-relative">
                <div class="qr-frame" aria-hidden="true"></div>
            </div>

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
					<div class="mb-2">
						<label for="distributionType" class="form-label small mb-1">Distribution Type</label>
						<select id="distributionType" name="distribution_type" class="form-select form-select-sm w-100">
							<option value="family" selected>Family Distribution</option>
							<option value="solo">Individual Distribution</option>
						</select>
					</div>
					<div class="dropdown">
						<button class="btn btn-outline-secondary dropdown-toggle w-100" type="button" id="resourceDropdownBtn" data-bs-toggle="dropdown" aria-expanded="false">
							<span id="resourceDropdownLabel">Select resources</span>
						</button>
						<div class="dropdown-menu p-3" aria-labelledby="resourceDropdownBtn" style="max-height:300px; overflow:auto; min-width:100%;">
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
									<input class="form-check-input me-2 resource-checkbox" type="checkbox" name="resources[]" value="<?= $resourceId ?>" id="res-<?= $resourceId ?>">
									<label class="form-check-label me-3" for="res-<?= $resourceId ?>"><?= $resourceLabel ?></label>
									<input type="number" name="quantity[<?= $resourceId ?>]" class="form-control form-control-sm w-auto resource-qty" value="1" min="1" style="width: 80px;" disabled>
									<span class="ms-2 text-muted"><?= $unit ?></span>
								</div>
							<?php
								endwhile;
							else:
								echo "<p class='text-muted'>No resources available to select.</p>";
							endif;
							?>
						</div>
					</div>
				</form>
				<small class="text-muted d-block mt-2" id="resource-help">Choose one or more resources and adjust quantities inside the dropdown.</small>
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
				// Flash indicator on successful scan
				const indicator = document.getElementById('scannerStatus');
				if (indicator) {
					indicator.textContent = 'Scanned';
					indicator.classList.remove('scanner-indicator--idle');
					indicator.classList.add('scanner-indicator--active');
					setTimeout(() => {
						indicator.textContent = 'Ready';
					}, 800);
				}
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
                        const isNoActiveLocation = (
                            locationData.no_active_location === true ||
                            locationData.status === 'no_active_location' ||
                            locationData.code === 'NO_ACTIVE_LOCATION' ||
                            (!locationData.evacuee_location_id && !locationData.evacuee_location_name)
                        );
                        if (isNoActiveLocation) {
                            // Permit distribution when evacuee has no active location
                            locationData.success = true;
                            locationData.evacuee_location_name = locationData.evacuee_location_name || 'No active location';
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Location Mismatch',
                                text: locationData.message || 'This evacuee is registered in another evacuation location.',
                                timer: 3000,
                                showConfirmButton: true
                            });
                            return;
                        }
                    }

					// If location check passes, perform validation depending on distribution type
					const distributionType = document.getElementById('distributionType')?.value || 'family';

					// helper to send distribution form
                    async function sendDistribution(targetPreRegId, overrideDistributionType = null) {
                        const form = document.getElementById("resource-selection-form");
						const formData = new FormData(form);
						formData.append("pre_reg_id", targetPreRegId);
						if (overrideDistributionType) formData.set('distribution_type', overrideDistributionType);
                        if (typeof staffLocationId !== 'undefined' && staffLocationId) {
                            formData.append('staff_evac_loc_id', staffLocationId);
                        }

						const resp = await fetch("../fetch_data/recieve_resources_ajax.php", {
							method: "POST",
							body: formData
						});

						if (!resp.ok) throw new Error(`Resource distribution failed: HTTP ${resp.status} - ${resp.statusText}`);
						let respJson;
						try { respJson = await resp.json(); } catch (je) { throw new Error('Invalid JSON from distribution service'); }
						return respJson;
					}

					// Fetch family info for the scanned pre_reg_id
					let familyInfo;
					try {
						const vf = await fetch('../qr_code_scanner/verify_family_by_pre_reg.php', {
							method: 'POST',
							headers: { 'Content-Type': 'application/json' },
							body: JSON.stringify({ pre_reg_id: preRegId })
						});
						if (!vf.ok) throw new Error('Failed to verify family info');
						familyInfo = await vf.json();
					} catch (err) {
						console.warn('Family verify failed, proceeding with individual distribution', err);
						// fallback: perform individual distribution
						try {
							const result = await sendDistribution(preRegId);
							if (result.success) {
								document.getElementById("family-name").textContent = result.name || "N/A";
								document.getElementById("evacuee-location").textContent = locationData.evacuee_location_name || "N/A";
								document.getElementById("family-info").classList.remove("d-none");
								Swal.fire({ icon: 'success', title: 'Distribution Successful!', text: result.message || 'Resources distributed.', timer: 3000, showConfirmButton: false });
							} else {
								Swal.fire({ icon: 'warning', title: 'Distribution Failed', text: result.message || 'Please try again.' });
							}
						} catch (e) {
							Swal.fire({ icon: 'error', title: 'Distribution Error', text: e.message || 'Failed to distribute.' });
						}
						return;
					}

					// familyInfo expected shape: { family_id, family_members: [...] }
					const members = Array.isArray(familyInfo.family_members) ? familyInfo.family_members : [];

					if (distributionType === 'solo') {
						// Solo distribution: always send to the scanned pre_reg_id (even if belongs to a family)
						try {
							const result = await sendDistribution(preRegId);
							if (result.success) {
								document.getElementById("family-name").textContent = result.name || "N/A";
								document.getElementById("evacuee-location").textContent = locationData.evacuee_location_name || "N/A";
								document.getElementById("family-info").classList.remove("d-none");
								Swal.fire({ icon: 'success', title: 'Distribution Successful!', text: result.message || 'Resources distributed to individual.', timer: 3000, showConfirmButton: false });
							} else {
								Swal.fire({ icon: 'warning', title: 'Distribution Failed', text: result.message || 'Please try again.' });
							}
						} catch (e) {
							Swal.fire({ icon: 'error', title: 'Distribution Error', text: e.message || 'Failed to distribute.' });
						}
						return;
					}

					// distributionType === 'family'
					if (members.length > 1) {
						// Give user choice: distribute to whole family (mark family as received) or distribute to this individual only
						const { value: action } = await Swal.fire({
							title: 'Family detected',
							html: `<p>This QR belongs to a family with ${members.length} members. How would you like to proceed?</p>`,
							showDenyButton: true,
							showCancelButton: true,
							confirmButtonText: 'Family (mark family received)',
							denyButtonText: 'Individual only',
						});

						if (action === undefined) {
							// user cancelled
							updateScannerStatus && updateScannerStatus('Distribution cancelled');
							return;
						}

						if (action === true) {
							// Family distribution: send with distribution_type=family
							try {
								const result = await sendDistribution(preRegId, 'family');
								if (result.success) {
									document.getElementById("family-name").textContent = result.name || "N/A";
									document.getElementById("evacuee-location").textContent = locationData.evacuee_location_name || "N/A";
									document.getElementById("family-info").classList.remove("d-none");
									Swal.fire({ icon: 'success', title: 'Family Distribution Successful', text: result.message || 'Family marked as received.', timer: 3000, showConfirmButton: false });
								} else {
									Swal.fire({ icon: 'warning', title: 'Distribution Failed', text: result.message || 'Please try again.' });
								}
							} catch (e) {
								Swal.fire({ icon: 'error', title: 'Distribution Error', text: e.message || 'Failed to distribute.' });
							}
						} else {
							// Individual only: send to scanned pre_reg_id but keep distribution_type as solo
							try {
								const result = await sendDistribution(preRegId, 'solo');
								if (result.success) {
									document.getElementById("family-name").textContent = result.name || "N/A";
									document.getElementById("evacuee-location").textContent = locationData.evacuee_location_name || "N/A";
									document.getElementById("family-info").classList.remove("d-none");
									Swal.fire({ icon: 'success', title: 'Distribution Successful', text: result.message || 'Resources distributed to individual.', timer: 3000, showConfirmButton: false });
								} else {
									Swal.fire({ icon: 'warning', title: 'Distribution Failed', text: result.message || 'Please try again.' });
								}
							} catch (e) {
								Swal.fire({ icon: 'error', title: 'Distribution Error', text: e.message || 'Failed to distribute.' });
							}
						}
					} else {
						// family has 0 or 1 member -> treat as individual
						try {
							const result = await sendDistribution(preRegId, 'family');
							if (result.success) {
								document.getElementById("family-name").textContent = result.name || "N/A";
								document.getElementById("evacuee-location").textContent = locationData.evacuee_location_name || "N/A";
								document.getElementById("family-info").classList.remove("d-none");
								Swal.fire({ icon: 'success', title: 'Distribution Successful', text: result.message || 'Resources distributed.', timer: 3000, showConfirmButton: false });
							} else {
								Swal.fire({ icon: 'warning', title: 'Distribution Failed', text: result.message || 'Please try again.' });
							}
						} catch (e) {
							Swal.fire({ icon: 'error', title: 'Distribution Error', text: e.message || 'Failed to distribute.' });
						}
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
		const indicator = document.getElementById('scannerStatus');
		if (indicator) {
			indicator.textContent = 'Scanning…';
			indicator.classList.remove('scanner-indicator--idle');
			indicator.classList.add('scanner-indicator--active');
		}
		
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
		const indicator = document.getElementById('scannerStatus');
		if (indicator) {
			indicator.textContent = 'Idle';
			indicator.classList.add('scanner-indicator--idle');
			indicator.classList.remove('scanner-indicator--active');
		}
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
	
	// Resource dropdown behavior
	document.addEventListener('DOMContentLoaded', function() {
	    function updateDropdownLabel() {
	        const checked = Array.from(document.querySelectorAll('.resource-checkbox:checked'));
	        const label = document.getElementById('resourceDropdownLabel');
	        if (checked.length === 0) {
	            label.textContent = 'Select resources';
	        } else if (checked.length === 1) {
	            const first = checked[0].nextElementSibling.textContent.trim();
	            label.textContent = first;
	        } else {
	            label.textContent = `${checked.length} selected`;
	        }
	    }
	
	    document.querySelectorAll('.resource-checkbox').forEach(cb => {
	        cb.addEventListener('change', function() {
	            const id = this.value;
	            const qty = document.querySelector(`.resource-qty[name="quantity[${id}]"]`);
	            if (this.checked) {
	                qty.disabled = false;
	            } else {
	                qty.disabled = true;
	            }
	            updateDropdownLabel();
	        });
	    });
	
	    // Initialize label based on any pre-checked inputs
	    updateDropdownLabel();
	});
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

	/* Scanner indicator */
	.scanner-indicator {
		font-size: 12px;
		padding: 2px 8px;
		border-radius: 999px;
		border: 1px solid rgba(0,0,0,0.1);
	}
	.scanner-indicator--idle {
		background: #f8f9fa;
		color: #6c757d;
	}
	.scanner-indicator--active {
		background: #e7f7ee;
		color: #198754;
		box-shadow: 0 0 0 2px rgba(25,135,84,0.15);
	}

	/* QR frame overlay */
	.qr-frame {
		position: absolute;
		top: 50%;
		left: 50%;
		transform: translate(-50%, -50%);
		width: 240px;
		height: 240px;
		border: 3px solid rgba(25,135,84,0.85);
		border-radius: 12px;
		box-shadow: 0 0 0 9999px rgba(0,0,0,0.08) inset;
		pointer-events: none;
	}
</style>