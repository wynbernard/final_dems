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
					<!-- Left: Scanner -->
					<div class="col-md-6">
						<div class="mb-2">
							<label for="cameraSelect" class="form-label">Select Camera</label>
							<select id="cameraSelect" class="form-select"></select>
						</div>
						<div id="qr-reader" style="width: 100%; height: 300px; border: 1px solid #ccc; border-radius: 10px;"></div>

						<div class="mt-2">
							<button class="btn btn-sm btn-success" id="startScannerBtn">Start Scanner</button>
							<button class="btn btn-sm btn-danger" id="stopScannerBtn" disabled>Stop Scanner</button>
						</div>
					</div>

					<!-- Right: Family Info + Resources -->
					<div class="col-md-6">
						<div id="family-info" class="d-none">
							<h5>Family Information</h5>
							<p><strong>Name:</strong> <span id="family-name"></span></p>
							<p><strong>Address:</strong> <span id="family-address"></span></p>

							<form action="receive_resources.php" method="POST">
								<input type="hidden" name="idp_id" id="input-idp-id">

								<h6>Select Received Resources:</h6>
								<?php
								include '../../database/conn.php';
								$resourceQuery = "SELECT resource_name FROM resource_allocation_table ORDER BY resource_name ASC";
								$result = $conn->query($resourceQuery);

								if ($result && $result->num_rows > 0):
									while ($row = $result->fetch_assoc()):
										$resource = htmlspecialchars($row['resource_name']);
								?>
										<div class="form-check">
											<input class="form-check-input" type="checkbox" name="resources[]" value="<?= $resource ?>" id="res-<?= $resource ?>">
											<label class="form-check-label" for="res-<?= $resource ?>"><?= $resource ?></label>
										</div>
								<?php
									endwhile;
								else:
									echo "<p class='text-muted'>No resources available to select.</p>";
								endif;
								?>

								<div class="mt-3">
									<button type="submit" class="btn btn-primary">Submit Record</button>
								</div>
							</form>
						</div>
					</div>
				</div> <!-- end row -->
			</div>
		</div>
	</div>
</div>
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
	let qrScanner;
	let availableCameras = [];

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
				async (qrCodeMessage) => {
						// Stop scanning once QR is detected
						await qrScanner.stop();
						await qrScanner.clear();

						// Fetch family data using scanned QR code
						const response = await fetch(`../fetch_data/get_family_data.php?qr=${encodeURIComponent(qrCodeMessage)}`);
						const data = await response.json();

						if (data.success) {
							document.getElementById("family-name").textContent = data.f_name + " " + data.l_name;
							document.getElementById("family-address").textContent = data.address;
							document.getElementById("input-idp-id").value = data.idp_id;
							document.getElementById("family-info").classList.remove("d-none");
						} else {
							alert("Family not found for scanned QR code.");
						}
					},
					(errorMessage) => {
						console.warn("QR Scanner Error:", errorMessage);
					}
			);

			// Disable scanner button to prevent multiple starts
			document.getElementById("startScannerBtn").disabled = true;

		} catch (err) {
			console.error("Failed to start scanner:", err);
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

	document.getElementById("scanQRModal")?.addEventListener("shown.bs.modal", async () => {
		await initCameraList();
		document.getElementById("family-info").classList.add("d-none");
		document.getElementById("family-name").textContent = "";
		document.getElementById("family-address").textContent = "";
		document.getElementById("input-idp-id").value = "";
	});

	document.getElementById("scanQRModal")?.addEventListener("hidden.bs.modal", stopScanner);

	document.getElementById("startScannerBtn").addEventListener("click", startScanner);
	document.getElementById("stopScannerBtn").addEventListener("click", stopScanner);
</script>