<!-- Add this to your HTML head -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
	document.addEventListener("DOMContentLoaded", function() {
		// Elements
		const scanQRBtn = document.getElementById("scanQRBtn");
		const qrScannerModal = new bootstrap.Modal(document.getElementById('qrScannerModal'));
		const startScannerBtn = document.getElementById("startScannerBtn");
		const stopScannerBtn = document.getElementById("stopScannerBtn");
		const registerFamilyBtn = document.getElementById("registerFamilyBtn");
		const familyRoomAssignment = document.getElementById("familyRoomAssignment");
		const familySearchInput = document.getElementById("familySearchInput");
		const cameraSelection = document.getElementById("cameraSelection");
		const cameraSelect = document.getElementById("cameraSelect");
		const scannerStatus = document.getElementById("scannerStatus");

		// State
		let html5QrCode;
		let selectedMembers = new Set();
		let selectedIDPs = new Set();
		let scannedMembers = [];
		let currentLocationId = document.querySelector("input[name='location_id']")?.value;
		let availableCameras = [];

		// Event Listeners
		scanQRBtn?.addEventListener("click", showScannerModal);
		familySearchInput?.addEventListener("input", searchFamilyMembers);

		// Modal Show Handler
		function showScannerModal() {
			if (!currentLocationId) {
				showAlert("Please select a location first", "warning");
				return;
			}

			qrScannerModal.show();
			loadAvailableRooms();
			fetchExistingFamilyMembers();
		}

		// Initialize Scanner
		async function initScanner() {
			if (!Html5Qrcode) {
				showAlert("QR Scanner library not loaded", "danger");
				return;
			}

			try {
				// Create new scanner instance
				html5QrCode = new Html5Qrcode("qrScanner", /* verbose= */ false);

				// Get available cameras
				availableCameras = await Html5Qrcode.getCameras();

				if (availableCameras.length === 0) {
					showAlert("No cameras found on this device", "warning");
					startScannerBtn.disabled = true;
					return;
				}

				// Setup camera selection if multiple cameras available
				if (availableCameras.length > 1) {
					cameraSelection.style.display = "block";
					cameraSelect.innerHTML = "";

					availableCameras.forEach((camera, index) => {
						const option = document.createElement("option");
						option.value = camera.id;
						option.text = camera.label || `Camera ${index + 1}`;
						cameraSelect.appendChild(option);
					});
				}

				// Set initial status
				updateScannerStatus(`${availableCameras.length} camera(s) detected`);

				startScannerBtn.addEventListener("click", startScanner);
				stopScannerBtn.addEventListener("click", stopScanner);

			} catch (err) {
				console.error("Scanner initialization failed:", err);
				showAlert("Failed to access camera: " + err.message, "danger");
				startScannerBtn.disabled = true;
			}
		}

		// Start Scanner
		async function startScanner() {
			try {
				let cameraId;

				// Use selected camera if available, otherwise first camera
				if (availableCameras.length > 1 && cameraSelect) {
					cameraId = cameraSelect.value;
				} else if (availableCameras.length > 0) {
					cameraId = availableCameras[0].id;
				} else {
					throw new Error("No cameras available");
				}

				const config = {
					fps: 10,
					qrbox: {
						width: 250,
						height: 250
					},
					supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA],
					rememberLastUsedCamera: true
				};

				updateScannerStatus("Starting camera...");

				await html5QrCode.start(
					cameraId,
					config,
					handleSuccessfulScan,
					handleScanError
				);

				toggleScannerButtons(true);
				updateScannerStatus("Scanning... Point camera at QR code");

			} catch (err) {
				console.error("Scanner start failed:", err);
				showAlert("Failed to start camera: " + err.message, "danger");
				updateScannerStatus("Error starting camera");
			}
		}

		// Stop Scanner
		async function stopScanner() {
			try {
				updateScannerStatus("Stopping camera...");
				await html5QrCode.stop();
				toggleScannerButtons(false);
				updateScannerStatus("Camera stopped");
			} catch (err) {
				console.error("Scanner stop failed:", err);
				showAlert("Failed to stop camera properly", "warning");
			}
		}

		// Process Scanned QR Code
		let isProcessing = false; // Track scan cooldown state
		const SCAN_INTERVAL = 5000; // 5 seconds
		let lastScannedId = null;
		let lastScanTime = 0;

		async function handleSuccessfulScan(decodedText) {
			try {
				// Prevent multiple concurrent scans
				if (isProcessing) {
					// showAlert("");
					return;
				}

				// Get pre_reg_id from QR code
				const match = decodedText.trim().match(/pre_reg_id[_id]?:\s*(\d+)/i);
				if (!match) {
					showAlert("Invalid QR code format", "danger");
					updateScannerStatus("Invalid format - expected 'pre_reg:<ID>'");
					return;
				}
				const preRegId = match[1];

				// Alert once if the same QR code is steady in the scanner
				const now = Date.now();
				if (lastScannedId === preRegId && now - lastScanTime < SCAN_INTERVAL) {
					// Do not alert again, just ignore
					return;
				}
				lastScannedId = preRegId;
				lastScanTime = now;

				isProcessing = true;
				updateScannerStatus("Processing QR code...");

				// Check for duplicate scans
				if (scannedMembers.some(m => m.pre_reg_id === preRegId)) {
					showAlert("This member has already been scanned", "warning");
					return;
				}

				// Check if already registered BEFORE loading family data
				const alreadyRegistered = await checkFamilyRegistration(preRegId);
				if (alreadyRegistered) {
					showAlert(`<i class="bi bi-exclamation-circle-fill"></i> Family is already registered at this location.`, "warning");
					updateScannerStatus("Family already registered");
					return; // Do NOT load or display family members
				}

				updateScannerStatus("Fetching family data...");
				const familyData = await getFamilyByPreRegId(preRegId);

				if (!familyData?.length) {
					showAlert("No family found for this QR code", "danger");
					updateScannerStatus("No family found");
					return;
				}

				// Check registration for each family member
				const unregisteredMembers = [];
				for (const member of familyData) {
					// Check if this member is already registered
					const isRegistered = await checkFamilyRegistration(member.pre_reg_id);
					if (!isRegistered) {
						unregisteredMembers.push(member);
					}
				}

				// If all members are registered, alert and do not load
				if (unregisteredMembers.length === 0) {
					showAlert("All family members are already registered at this location.", "warning");
					updateScannerStatus("All family members registered");
					return;
				}

				// Add only unregistered members
				const timestamp = Date.now();
				scannedMembers.push(...unregisteredMembers.map(member => ({
					...member,
					pre_reg_id: member.pre_reg_id,
					isPresent: true,
					scannedAt: timestamp
				})));

				updateFamilyDisplay();
				showAlert(`Loaded ${unregisteredMembers.length} unregistered family member(s)`, "success");

			} catch (error) {
				console.error("Error handling scan:", error);
				showAlert("Error processing QR code", "danger");
				updateScannerStatus("Scan error");
			} finally {
				// Enforce cooldown
				setTimeout(() => {
					isProcessing = false;
					updateScannerStatus("Ready to scan");
				}, SCAN_INTERVAL);
			}
		}

		// Updated reset function
		function resetScanner() {
			// Stop scanner if active
			if (html5QrCode?.isScanning) {
				stopScanner();
			}

			// Reset internal state
			isProcessing = false;
			scannedMembers = [];
			selectedMembers = [];

			// Reset UI elements
			updateFamilyDisplay();
			updateSelectionUI();

			// Reset scanner status
			updateScannerStatus("Ready to scan");
		}

		// Handle scan errors
		function handleScanError(error) {
			console.log("Scan error:", error);
			// Don't show every error to user as they happen frequently during normal operation
		}

		// Update scanner status text
		function updateScannerStatus(message) {
			scannerStatus.textContent = message;
		}

		// Toggle scanner buttons
		function toggleScannerButtons(isScanning) {
			startScannerBtn.disabled = isScanning;
			stopScannerBtn.disabled = !isScanning;
			if (cameraSelect) cameraSelect.disabled = isScanning;
		}

		// Backend Integration Functions
		async function verifyQRCode(qrData) {
			try {
				const response = await fetch('../qr_code_scanner/verify_qr.php', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json'
					},
					body: JSON.stringify({
						qr_data: qrData
					})
				});
				if (!response.ok) throw new Error("Verification failed");
				return await response.json();
			} catch (error) {
				console.error("QR verification error:", error);
				return null;
			}
		}
		// family member
		async function getFamilyByPreRegId(preRegId) {
			try {
				const response = await fetch(`../qr_code_scanner/verify_family_by_pre_reg.php?pre_reg_id=${encodeURIComponent(preRegId)}`);

				if (!response.ok) {
					throw new Error("Network response was not ok.");
				}

				const data = await response.json();

				// Ensure data is valid and has family_members array
				if (data && Array.isArray(data.family_members)) {
					return data.family_members;
				} else {
					console.warn("No family members found", data);
					return [];
				}
			} catch (error) {
				console.error("Fetch family error:", error);
				showAlert("Could not load family members", "danger");
				return [];
			}
		}

		async function checkFamilyRegistration(preRegId) {
			try {
				const response = await fetch(`../qr_code_scanner/check_registration.php?pre_reg_id=${preRegId}&location_id=${currentLocationId}`);
				const data = await response.json();
				return data.success && data.isRegistered;
			} catch (error) {
				console.error("Family registration check error:", error);
				return false;
			}
		}

		// DUPLICATION OF REGISTRATION
		async function handleScannedPreRegId(preRegId) {
			if (!preRegId) {
				showAlert("Invalid Pre-Registration ID", "warning");
				return;
			}

			try {
				const existingRegId = await checkFamilyRegistration(preRegId);

				if (existingRegId) {
					showAlert(`<i class="bi bi-exclamation-circle-fill"></i> Family is already registered at this location.`, "warning");
					// Do NOT display family members if already registered
					return;
				}

				const familyMembers = await getFamilyByPreRegId(preRegId);

				if (familyMembers.length === 0) {
					showAlert("No family members found for this Pre-Registration ID", "info");
					return;
				}

				// Only display if not registered
				displayFamilyMembers(familyMembers);

			} catch (error) {
				console.error("Error handling preRegId:", error);
				showAlert("Unexpected error occurred while processing the Pre-Registration ID", "danger");
			}
		}
		async function loadAvailableRooms() {
			try {
				const response = await fetch(`../qr_code_scanner/get_rooms.php?location_id=${currentLocationId}`);

				// First check if response is OK
				if (!response.ok) {
					throw new Error(`HTTP error! Status: ${response.status}`);
				}

				// Parse the JSON response
				const data = await response.json();

				// Check if data contains rooms array
				if (!data?.success || !Array.isArray(data.rooms)) {
					throw new Error('Invalid room data format');
				}

				// Clear existing options
				familyRoomAssignment.innerHTML = '<option value="">Select room</option>';

				// Process rooms
				data.rooms.forEach(room => {
					const option = document.createElement("option");
					option.value = room.id;
					option.textContent = `${room.name} (${room.current_occupancy}/${room.capacity})`;

					if (room.current_occupancy >= room.capacity) {
						option.disabled = true;
						option.textContent += " - FULL";
					} else if (room.is_reserved) {
						option.textContent += " - RESERVED";
					}

					familyRoomAssignment.appendChild(option);
				});

			} catch (error) {
				console.error("Room loading error:", error);
				familyRoomAssignment.innerHTML = '<option value="">Error loading rooms</option>';
				// Optional: Show error to user
				showAlert(`Failed to load rooms: ${error.message}`, 'danger');
			}
		}

		async function fetchExistingFamilyMembers() {
			try {
				// const response = await fetch(`../qr_code_scanner/get_family_members.php?location_id=${currentLocationId}`);
				const response = await fetch(`../qr_code_scanner/get_family_members.php`);

				// Handle HTTP errors (4xx/5xx responses)
				if (!response.ok) {
					const text = await response.text();
					console.error("HTTP Error Response:", text);
					throw new Error(`Server error: ${response.status} ${response.statusText}`);
				}

				// Attempt JSON parsing
				const result = await response.json().catch(jsonError => {
					console.error("JSON Parse Error:", jsonError);
					throw new Error("Invalid server response format");
				});

				// Handle API business logic errors
				if (!result.success) {
					throw new Error(result.error || "Unknown server-side error occurred");
				}

				// Clear existing members
				scannedMembers = [];

				// Process families and members
				result.data.forEach(family => {
					family.members.forEach(member => {
						scannedMembers.push({
							...member,
							family_id: family.family_id,
							pre_reg_id: family.pre_reg_id,
							isRegistered: true,
							isPresent: true
						});
					});
				});

				updateFamilyDisplay();

			} catch (error) {
				console.error("Failed to load family members:", error);

				// Robust error message handling
				let message;
				if (error instanceof Error) {
					message = error.message === "undefined" ? "Unknown error occurred" : error.message;
				} else if (typeof error === "string") {
					message = error;
				} else {
					message = "Unknown error occurred";
				}

				alert(`Error: ${message}`);
			}
		}

		// UI Update Functions
		function updateFamilyDisplay() {
			const familyList = document.getElementById("familyList");
			const familyInfo = document.getElementById("familyInfo");

			// Filter out registered members
			const unregisteredMembers = scannedMembers.filter(m => !m.isRegistered);
			const registeredMembers = scannedMembers.filter(m => m.isRegistered);

			// ✅ Alert specific pre_reg_ids if already registered
			if (registeredMembers.length > 0) {
				const registeredIds = registeredMembers.map(m => m.id).join(', ');
				alert(`The following pre_reg_id(s) are already registered and will be skipped:\n${registeredIds}`);
			}

			if (unregisteredMembers.length === 0) {
				familyInfo.style.display = "block";
				familyList.style.display = "none";
				return;
			}

			// Proceed with unregistered members
			familyInfo.style.display = "none";
			familyList.style.display = "block";
			familyList.innerHTML = "";

			const families = groupByFamily(unregisteredMembers);
			let anyFamilyRendered = false;

			Object.entries(families).forEach(([familyId, members]) => {
				const familySection = document.createElement("div");
				familySection.className = "family-group mb-3";
				familySection.innerHTML = `
			<h6 class="family-header">Family ${familyId}</h6>
			<div class="family-members"></div>
		`;

				const membersContainer = familySection.querySelector(".family-members");
				const table = document.createElement("table");
				table.className = "table table-hover";
				const tbody = document.createElement("tbody");

				members.forEach(member => {
					const row = document.createElement("tr");
					row.className = `align-middle ${member.isPresent ? 'table-success' : 'table-danger'}`;

					row.innerHTML = `
				<td class="align-middle text-center px-2">
					<div class="form-check form-check-sm">
						<input class="form-check-input member-checkbox" type="checkbox" 
							id="member-${member.id}" value="${member.id}"
							${member.isPresent ? 'checked' : 'disabled'}>
					</div>
				</td>
				<td class="align-middle px-2">
					<div class="d-flex align-items-center gap-2">
						<div class="avatar-xs bg-light rounded-circle">
							${member.gender === 'Male' ? '👨' : '👩'}
						</div>
						<div>
							<div class="fw-medium small">${member.name}</div>
							<small class="text-muted">ID: ${member.id}</small>
						</div>
					</div>
				</td>
				<td class="align-middle px-2">
					<span class="badge ${member.gender === 'Male' ? 'bg-primary' : 'bg-purple'} text-uppercase fs-11">
						${member.gender === 'Male' ? 'M' : 'F'}
					</span>
				</td>
				<td class="align-middle px-2">
					<div class="small">
						<span class="fw-medium">${member.age}</span>
						<small class="text-muted ms-1">(${member.date_of_birth})</small>
					</div>
				</td>
				<td class="align-middle text-end px-2">
					<button class="btn btn-xs btn-outline-primary px-2 py-0" data-pre-reg-id="${member.pre_reg_id}">
						<i class="fas fa-edit fa-sm"></i>
					</button>
				</td>
			`;

					tbody.appendChild(row);
				});

				table.appendChild(tbody);
				membersContainer.appendChild(table);
				familySection.appendChild(membersContainer);
				familyList.appendChild(familySection);
				anyFamilyRendered = true;
			});

			// Attach checkbox listeners
			setupCheckboxListeners();

			// Enable register button if applicable
			const canRegister = unregisteredMembers.some(m => m.isPresent);
			familyRoomAssignment.disabled = !canRegister;

			setTimeout(() => {
				updateSelectedMembers();
				updateIDPDisplay();
			}, 0);
		}


		function setupCheckboxListeners() {
			document.querySelectorAll(".member-checkbox").forEach(checkbox => {
				checkbox.removeEventListener("change", handleCheckboxChange);
				checkbox.addEventListener("change", handleCheckboxChange);
			});
		}

		// Updated checkbox handler
		function handleCheckboxChange(e) {
			const checkbox = e.target;
			const memberId = checkbox.value;
			const member = scannedMembers.find(m => m.id === memberId);

			if (checkbox.checked) {
				selectedMembers.add(memberId);
				if (member?.idps) {
					member.idps.forEach(idp => selectedIDPs.add(idp));
				}
			} else {
				selectedMembers.delete(memberId);
				if (member?.idps) {
					member.idps.forEach(idp => selectedIDPs.delete(idp));
				}
			}

			updateSelectedMembers();
			updateIDPDisplay();
		}

		function updateSelectedMembers() {
			const selectedCheckboxes = Array.from(document.querySelectorAll(".member-checkbox:checked"));

			// Clear previous selections
			selectedMembers.clear();
			selectedIDPs.clear();

			const selectedMemberIds = selectedCheckboxes.map(cb => cb.value);

			console.log("Selected Member IDs: ", selectedMemberIds); // Debugging line

			selectedMemberIds.forEach(id => {
				selectedMembers.add(id);
				const member = scannedMembers.find(m => m.id == id);

				console.log("Selected Member: ", member); // Debugging line

				// Check if IDPs exist for the selected member
				if (member?.idps && Array.isArray(member.idps)) {
					member.idps.forEach(idp => {
						selectedIDPs.add(idp);
					});
				} else {
					console.log(`No IDPs for member ${id}`); // Debugging line
				}
			});

			const selectedFamilyDiv = document.getElementById("selectedFamily");
			const uniquePreRegIds = [...new Set(selectedMemberIds.map(id => {
				const member = scannedMembers.find(m => m.id == id);
				return member?.pre_reg_id;
			}).filter(Boolean))];

			if (selectedMemberIds.length > 0) {
				selectedFamilyDiv.innerHTML = `
        <p>Selected ${selectedMemberIds.length} member(s):</p>
        <ul class="mb-0">
            ${selectedMemberIds.map(id => {
                const member = scannedMembers.find(m => m.id == id);
                return `<li>
                    ${member.name}
                    (Family ID: ${member.id})<br>
                    ${member.idps?.length > 0 
                        ? `IDPs: ${member.idps.join(', ')}` 
                        : ' '}
                </li>`;
            }).join("")}
        </ul>
        ${uniquePreRegIds.length > 0 ? `
            <p class="mt-2">Unique Pre-Registration IDs: ${uniquePreRegIds.join(', ')}</p>
        ` : ''}
        `;
				document.getElementById("registerFamilyBtn").disabled = false;
			} else {
				selectedFamilyDiv.innerHTML = "No members selected yet";
				document.getElementById("registerFamilyBtn").disabled = true;
			}

			// Call to update IDP display
			updateIDPDisplay();
		}


		function updateIDPDisplay() {
			const container = document.getElementById("selectedIDPs") || createIDPDisplayContainer();
			container.innerHTML = ''; // Clear previous content

			console.log("Selected IDPs: ", selectedIDPs); // Debugging line

			if (selectedIDPs.size === 0) {
				container.innerHTML = `<p class="text-muted mb-0">No IDPs found in selected members</p>`;
				return;
			}

			const header = document.createElement("p");
			header.className = "mb-2 fw-medium";
			header.textContent = `Selected IDPs (${selectedIDPs.size}):`;

			const badgeContainer = document.createElement("div");
			badgeContainer.className = "d-flex flex-wrap gap-2";

			selectedIDPs.forEach(idp => {
				const badge = document.createElement("span");
				badge.className = "badge bg-primary fs-12 py-2 px-3";
				badge.textContent = idp;
				badgeContainer.appendChild(badge);
			});

			container.append(header, badgeContainer);
		}


		function createIDPDisplayContainer() {
			const container = document.createElement("div");
			container.id = "selectedIDPs";
			document.querySelector("#selectedFamily").appendChild(container);
			return container;
		}
		// Registration Handler
		async function registerSelectedMembers() {
			const roomId = familyRoomAssignment.value;
			const memberIds = Array.from(selectedMembers); // Convert selectedMembers (Set) to array
			const locationId = currentLocationId; // Assumes currentLocationId is set

			alert("Selected Members: ", memberIds); // Debugging line to check the value of memberIds

			// Validate required fields
			if (!roomId || memberIds.length === 0 || !locationId) {
				showAlert("Please select a room, at least one member, and a location", "warning");
				return;
			}

			const data = {
				room_id: roomId,
				member_ids: memberIds,
				location_id: locationId
			};

			try {
				const response = await fetch('../qr_code_scanner/register_family.php', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json'
					},
					body: JSON.stringify(data)
				});

				const result = await response.json();

				if (result.success) {
					showAlert(`${result.success_count} members registered successfully!`, "success");
					// Clear selected members and refresh data
					scannedMembers = [];
					selectedMembers.clear();
					await fetchExistingFamilyMembers();
					await loadAvailableRooms();
				} else {
					showAlert(result.error || "Registration failed.", "danger");
				}

			} catch (error) {
				console.error("Registration error:", error);
				showAlert("Failed to complete registration", "danger");
			}
		}

		// Helper Functions
		function groupByFamily(members) {
			return members.reduce((groups, member) => {
				const familyId = member.family_id || 'individual';
				groups[familyId] = groups[familyId] || [];
				groups[familyId].push(member);
				return groups;
			}, {});
		}

		function showAlert(message, type) {
			const alertDiv = document.createElement("div");
			alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
			alertDiv.innerHTML = `
      ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

			const alertContainer = document.getElementById("alertContainer") || createAlertContainer();
			alertContainer.prepend(alertDiv);

			setTimeout(() => alertDiv.remove(), 5000);
		}

		function createAlertContainer() {
			const container = document.createElement("div");
			container.id = "alertContainer";
			container.style.position = "fixed";
			container.style.top = "20px";
			container.style.right = "20px";
			container.style.zIndex = "2000";
			container.style.width = "300px";
			document.body.appendChild(container);
			return container;
		}

		function searchFamilyMembers() {
			const searchTerm = familySearchInput.value.toLowerCase();
			document.querySelectorAll(".family-group").forEach(group => {
				let hasMatches = false;
				group.querySelectorAll(".family-members .card").forEach(card => {
					const text = card.textContent.toLowerCase();
					const isMatch = text.includes(searchTerm);
					card.style.display = isMatch ? "" : "none";
					if (isMatch) hasMatches = true;
				});
				group.style.display = hasMatches ? "" : "none";
			});
		}

		function resetScanner() {
			if (html5QrCode?.isScanning) {
				stopScanner();
			}
			scannedMembers = [];
			selectedMembers = [];
			updateFamilyDisplay();
			familyRoomAssignment.disabled = true;
			registerFamilyBtn.disabled = true;
			document.getElementById("selectedFamily").innerHTML = "No members selected yet";
			updateScannerStatus("Ready to scan");
		}

		function showAlert(message, type = "info") {
			let icon = "info";
			if (type === "success") icon = "success";
			else if (type === "danger" || type === "error") icon = "error";
			else if (type === "warning") icon = "warning";

			Swal.fire({
				html: message,
				icon: icon,
				toast: true,
				position: 'top-end',
				showConfirmButton: false,
				timer: 3500,
				timerProgressBar: true,
				customClass: {
					popup: 'swal2-sm'
				}
			});
		}

		// Event Delegation
		document.addEventListener("click", function(e) {
			if (e.target.matches("#registerFamilyBtn")) {
				registerSelectedMembers();
			}
		});

		// Modal Events
		document.getElementById('qrScannerModal')?.addEventListener('shown.bs.modal', initScanner);
		document.getElementById('qrScannerModal')?.addEventListener('hidden.bs.modal', resetScanner);
	});
</script>
<style>
	.avatar-xs {
		width: 28px;
		height: 28px;
		font-size: 0.9rem;
	}

	.fs-11 {
		font-size: 0.7rem;
		padding: 3px 6px;
	}

	.btn-xs {
		--bs-btn-padding-y: 0.15rem;
		--bs-btn-padding-x: 0.4rem;
		--bs-btn-font-size: 0.75rem;
	}

	.small {
		font-size: 0.825rem;
	}
</style>