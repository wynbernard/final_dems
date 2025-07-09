document.addEventListener("DOMContentLoaded", function () {
	const editButtons = document.querySelectorAll(".edit-btn");

	editButtons.forEach(button => {
		button.addEventListener("click", function () {
			const id = this.getAttribute("data-id");
			const name = this.getAttribute("data-name");
			const city = this.getAttribute("data-city");
			const barangay = this.getAttribute("data-barangay");
			const purok = this.getAttribute("data-purok");
			const capacity = this.getAttribute("data-capacity");
			const longitude = this.getAttribute("data-longitude");
			const latitude = this.getAttribute("data-latitude");

			// Target modal
			const modal = document.getElementById("editLocationModal");

			// Find fields within the modal only
			const barangaySelect = modal.querySelector("#barangay_id");
			const displayBarangay = modal.querySelector("#displayBarangay");

			document.getElementById("editLocationId").value = id;
			document.getElementById("editLocationName").value = name;
			document.getElementById("editLocationCity").value = city;
			document.getElementById("editLocationPurok").value = purok;
			document.getElementById("editTotalCapacity").value = capacity;
			document.getElementById("editLatitude").value = latitude;
			document.getElementById("editLongitude").value = longitude;

			// Set dropdown selection
			for (let i = 0; i < barangaySelect.options.length; i++) {
				if (barangaySelect.options[i].value === barangay) {
					barangaySelect.selectedIndex = i;
					break;
				}
			}

			// Set display value
			displayBarangay.value = barangaySelect.options[barangaySelect.selectedIndex].textContent;

			// Remove any previous listeners to avoid duplicates
			barangaySelect.removeEventListener("change", updateDisplay);
			barangaySelect.addEventListener("change", updateDisplay);

			function updateDisplay() {
				const selectedOption = barangaySelect.options[barangaySelect.selectedIndex];
				displayBarangay.value = selectedOption.textContent || "";
			}
		});
	});
});
	// Initialize Leaflet map for editing location
	let editMap, editMarker;

	function updateCoordinateDisplay(lat, lng) {
		document.getElementById('editLatDisplay').textContent = lat;
		document.getElementById('editLngDisplay').textContent = lng;
	}

	function initEditMap() {
		const lat = parseFloat(document.getElementById('editLatitude').value) || 10.3157;
		const lng = parseFloat(document.getElementById('editLongitude').value) || 123.8854;
		editMap = L.map('editLocationMap').setView([lat, lng], 13);
		L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
			attribution: '&copy; OpenStreetMap contributors'
		}).addTo(editMap);

		editMarker = L.marker([lat, lng], {
			draggable: true
		}).addTo(editMap);

		editMarker.on('dragend', function() {
			const latlng = editMarker.getLatLng();
			document.getElementById('editLatitude').value = latlng.lat.toFixed(6);
			document.getElementById('editLongitude').value = latlng.lng.toFixed(6);
			updateCoordinateDisplay(lat, lng);
		});
		editMap.on('click', function(e) {
			const lat = e.latlng.lat.toFixed(6);
			const lng = e.latlng.lng.toFixed(6);
			updateEditMap(lat, lng);
			updateCoordinateDisplay(lat, lng);
		});
	}

	function updateEditMap(lat, lng) {
		const newLatLng = new L.LatLng(lat, lng);
		editMap.setView(newLatLng, 15);
		editMarker.setLatLng(newLatLng);
		document.getElementById('editLatitude').value = lat;
		document.getElementById('editLongitude').value = lng;
		updateCoordinateDisplay(lat, lng);
	}

	// Modal shown event
	document.getElementById('editLocationModal').addEventListener('shown.bs.modal', function() {
		if (!editMap) {
			initEditMap();
		} else {
			editMap.invalidateSize();
		}
	});

	// When barangay is selected, update map with its coordinates
	document.getElementById('barangay_id').addEventListener('change', function() {
		const selected = this.options[this.selectedIndex];
		const lat = selected.getAttribute('data-latitude');
		const lng = selected.getAttribute('data-longitude');
		if (lat && lng) {
			updateEditMap(parseFloat(lat), parseFloat(lng));
		}
	});
	document.getElementById('editLocationModal').addEventListener('hidden.bs.modal', function() {
		if (editMap) {
			editMap.remove(); // Fully remove map instance
			editMap = null; // Clear reference to force re-init
		}
	});

	// Delete button functionality

		document.addEventListener("DOMContentLoaded", function() {
		// Select all delete buttons
		const deleteButtons = document.querySelectorAll(".delete-btn");

		deleteButtons.forEach(button => {
			button.addEventListener("click", function() {
				// Get the evac_loc_id from the data attribute
				const id = this.getAttribute("data-id");

				// Set the evac_loc_id in the modal form
				document.getElementById("deleteLocationId").value = id;
			});
		});
	});