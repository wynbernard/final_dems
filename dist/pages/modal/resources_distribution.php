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

						<div id="qr-reader" class="qr-preview"></div>

						<div class="mt-2">
							<button class="btn btn-sm btn-success" id="startScannerBtn">Start Scanner</button>
							<button class="btn btn-sm btn-danger" id="stopScannerBtn" disabled>Stop Scanner</button>
						</div>

						<!-- Resources Selection BEFORE Scanning -->
						<div class="mt-4">
							<h6>Select Resources to Distribute:</h6>
							<form id="resource-selection-form">
								<?php
								include '../../../database/conn.php';
								$resourceQuery = "SELECT resource_id, resource_name ,measurement_unit FROM resource_allocation_table ORDER BY resource_name ASC";
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
					</div>

					<!-- Right: Info Display Only -->
					<div class="col-md-6">
						<div id="family-info" class="d-none">
							<h5>Family Information</h5>
							<p><strong>Name:</strong> <span id="family-name"></span></p>
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

						const form = document.getElementById("resource-selection-form");
						const formData = new FormData(form);
						formData.append("pre_reg_id", preRegId);

						try {
							const response = await fetch("../fetch_data/recieve_resources_ajax.php", {
								method: "POST",
								body: formData
							});

							const data = await response.json();

							if (data.success) {
								document.getElementById("family-name").textContent = data.name || "N/A";
								document.getElementById("family-info").classList.remove("d-none");

								Swal.fire({
									icon: 'success',
									title: 'Distributed!',
									text: `Resources distributed to ${data.name || 'recipient'}.`,
									timer: 2000,
									showConfirmButton: false
								});
							} else {
								Swal.fire({
									icon: 'warning',
									title: 'Already Distributed or Not Registered',
									text: data.message || "This ID is either not registered or already received aid.",
									timer: 2500,
									showConfirmButton: false
								});
							}
						} catch (e) {
							console.error("Auto submit error:", e);
							Swal.fire({
								icon: 'error',
								title: 'Submission Failed',
								text: e.message,
								confirmButtonText: 'OK'
							});
						}
					},
					(err) => console.warn("QR error:", err)
			);

			document.getElementById("startScannerBtn").disabled = true;
			document.getElementById("stopScannerBtn").disabled = false;
		} catch (err) {
			console.error("Scanner failed to start:", err);
			alert("Unable to access the camera.");
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

	document.getElementById("startScannerBtn").addEventListener("click", startScanner);
	document.getElementById("stopScannerBtn").addEventListener("click", stopScanner);

	document.getElementById("scanQRModal")?.addEventListener("shown.bs.modal", async () => {
		await initCameraList();
		document.getElementById("family-info").classList.add("d-none");
		document.getElementById("family-name").textContent = "";
	});
	document.getElementById("scanQRModal")?.addEventListener("hidden.bs.modal", stopScanner);
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