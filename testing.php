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
						if (!match) {
							console.warn("Invalid QR format");
							return;
						}
						const preRegId = match[1];

						// Debounce to avoid processing same QR repeatedly
						if (preRegId === lastScannedId) return;
						lastScannedId = preRegId;

						clearTimeout(debounceTimeout);
						debounceTimeout = setTimeout(() => {
							lastScannedId = null;
						}, 3000); // allow next scan after 3 seconds

						console.log("Scanned ID:", preRegId);

						try {
							const response = await fetch(`../fetch_data/get_family_data.php?pre_reg_id=${encodeURIComponent(preRegId)}`);
							if (!response.ok) throw new Error(`Server error ${response.status}`);

							const data = await response.json();

							if (data.success && Array.isArray(data.family_members) && data.family_members.length > 0) {
								const member = data.family_members[0];

								// Show results
								document.getElementById("family-name").textContent = member.name || "N/A";
								document.getElementById("input-idp-id").value = data.family_id || "";
								document.getElementById("family-info").classList.remove("d-none");

								// Optional: Sound or visual indicator
								console.log("Scan successful.");
							} else {
								throw new Error("No matching family found.");
							}
						} catch (e) {
							console.error("Fetch error:", e.message);
						}
					},
					(errorMessage) => {
						console.warn("QR Scan error:", errorMessage);
					}
			);

			document.getElementById("startScannerBtn").disabled = true;
			document.getElementById("stopScannerBtn").disabled = false;
		} catch (err) {
			console.error("Scanner start failed:", err);
			alert("Unable to start camera.");
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

	// Event Listeners
	document.getElementById("startScannerBtn").addEventListener("click", startScanner);
	document.getElementById("stopScannerBtn").addEventListener("click", stopScanner);

	// Modal-related reset
	document.getElementById("scanQRModal")?.addEventListener("shown.bs.modal", async () => {
		await initCameraList();
		document.getElementById("family-info").classList.add("d-none");
		document.getElementById("family-name").textContent = "";
		document.getElementById("input-idp-id").value = "";
	});
	document.getElementById("scanQRModal")?.addEventListener("hidden.bs.modal", stopScanner);
</script>


list to deploy the changes.

1. admin_page/resource_distribution.php
2. fetch_data/get_family_data.php
3. fetch_data/recieve_resources_ajax.php
4. modal/resources_distribution.php