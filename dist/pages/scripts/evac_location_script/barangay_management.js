let map, marker;

	document.addEventListener("DOMContentLoaded", function() {
		const modalElement = document.getElementById('addLocationModal');
		const mapContainer = document.getElementById('locationMap');
		

		// Initialize map
		map = L.map(mapContainer).setView([10.5351, 122.8357], 13); // Default center
		L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
			attribution: '&copy; OpenStreetMap contributors'
		}).addTo(map);

		// Resize map when modal opens
		modalElement.addEventListener('shown.bs.modal', function() {
			setTimeout(() => map.invalidateSize(), 100);
		});

		// Handle map click
		map.on('click', function(e) {
			const lat = e.latlng.lat.toFixed(6);
			const lng = e.latlng.lng.toFixed(6);
			let barangayName = document.getElementById('add_barangay_name').value;
			// Remove existing marker
			if (marker) {
				map.removeLayer(marker);
			}

			// Add new marker
			marker = L.marker([lat, lng]).addTo(map)
				.bindPopup(`📍Brgy.  ${barangayName}`)
				.openPopup();

			// Update hidden inputs
			document.getElementById('latitude').value = lat;
			document.getElementById('longitude').value = lng;
			document.getElementById('coordinatesDisplay').textContent = `Latitude: ${lat}, Longitude: ${lng}`;
		});
	});

	let canvas, ctx, drawing = false;

	function initializeSignaturePad() {
		canvas = document.getElementById("signature-pad");
		ctx = canvas.getContext("2d");

		// Ensure canvas has correct size when modal opens
		canvas.width = canvas.offsetWidth;
		canvas.height = 150;

		// Mouse support
		canvas.addEventListener("mousedown", () => drawing = true);
		canvas.addEventListener("mouseup", () => {
			drawing = false;
			ctx.beginPath();
			saveSignature();
		});
		canvas.addEventListener("mousemove", (e) => {
			if (!drawing) return;
			draw(e.clientX, e.clientY);
		});

		// Touch support
		canvas.addEventListener("touchstart", (e) => {
			e.preventDefault();
			drawing = true;
		});
		canvas.addEventListener("touchend", (e) => {
			e.preventDefault();
			drawing = false;
			ctx.beginPath();
			saveSignature();
		});
		canvas.addEventListener("touchmove", (e) => {
			e.preventDefault();
			if (!drawing) return;
			const touch = e.touches[0];
			draw(touch.clientX, touch.clientY);
		});
	}

	function draw(x, y) {
		const rect = canvas.getBoundingClientRect();
		ctx.lineWidth = 2;
		ctx.lineCap = "round";
		ctx.strokeStyle = "#000";
		ctx.lineTo(x - rect.left, y - rect.top);
		ctx.stroke();
		ctx.beginPath();
		ctx.moveTo(x - rect.left, y - rect.top);
	}

	function clearSignature() {
		ctx.clearRect(0, 0, canvas.width, canvas.height);
		document.getElementById("signature_data").value = "";
	}

	function saveSignature() {
		document.getElementById("signature_data").value = canvas.toDataURL("image/png");
	}

	function toggleSignatureInput() {
		const option = document.querySelector('input[name="signature_option"]:checked').value;
		document.getElementById("signature-draw").style.display = (option === "draw") ? "block" : "none";
		document.getElementById("signature-upload").style.display = (option === "upload") ? "block" : "none";
	}

	document.getElementById("addLocationForm").addEventListener("submit", function(e) {
		const drawSelected = document.getElementById("option_draw").checked;
		if (drawSelected) saveSignature();
	});

	// Initialize on modal show
	const modal = document.getElementById('addLocationForm').closest('.modal');
	if (modal) {
		modal.addEventListener('shown.bs.modal', () => {
			initializeSignaturePad();
		});
	}



// EDIT MAP 
let editMap, editMarker;

	function initEditMap(lat = 10.3157, lng = 123.8854) {
		const container = L.DomUtil.get('editLocationMap');
		if (container._leaflet_id) {
			container._leaflet_id = null;
		}

		editMap = L.map('editLocationMap').setView([lat, lng], 15);

		L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
			maxZoom: 19,
		}).addTo(editMap);

		editMarker = L.marker([lat, lng], { draggable: true }).addTo(editMap);

		editMarker.on('dragend', function () {
			const pos = editMarker.getLatLng();
			updateEditCoordinates(pos.lat, pos.lng);
		});

		editMap.on('click', function (e) {
			const { lat, lng } = e.latlng;
			editMarker.setLatLng([lat, lng]);
			updateEditCoordinates(lat, lng);
		});
	}

	function updateEditCoordinates(lat, lng) {
		document.getElementById('edit_latitude').value = lat;
		document.getElementById('edit_longitude').value = lng;
		document.getElementById('editCoordinatesDisplay').innerText =
			`Latitude: ${lat.toFixed(6)}, Longitude: ${lng.toFixed(6)}`;
	}

	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('.edit-btn').forEach(button => {
			button.addEventListener('click', () => {
				const id = button.getAttribute('data-id');
				const name = button.getAttribute('data-name');
				const captain = button.getAttribute('data-captain');
				const signature = button.getAttribute('data-signature');
				const latitude = parseFloat(button.getAttribute('data-latitude')) || 10.5382;
				const longitude = parseFloat(button.getAttribute('data-longitude')) || 122.8351;

				document.getElementById('edit_barangay_id').value = id;
				document.getElementById('edit_barangay_name').value = name;
				document.getElementById('edit_barangay_captain').value = captain;
				document.getElementById('edit_current_signature').value = signature;
				document.getElementById('edit_signature_preview').src = "../../../uploads/" + signature;
				document.getElementById('edit_latitude').value = latitude;
				document.getElementById('edit_longitude').value = longitude;
				document.getElementById('editCoordinatesDisplay').innerText =
					`Latitude: ${latitude}, Longitude: ${longitude}`;

				const modalElement = document.getElementById('editLocationModal');
				const modal = new bootstrap.Modal(modalElement);
				modal.show();

				modalElement.addEventListener('shown.bs.modal', function () {
					initEditMap(latitude, longitude);
				}, { once: true });
			});
		});

		// Cleanup on modal hide to prevent overlay bugs
		const modalElement = document.getElementById('editLocationModal');
		modalElement.addEventListener('hidden.bs.modal', function () {
			// Remove map to prevent duplicates
			if (editMap) {
				editMap.remove();
				editMap = null;
			}

			// Remove any stuck backdrops
			document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
			document.body.classList.remove('modal-open');
			document.body.style.overflow = '';
			document.body.style.paddingRight = '';
		});
	});
	
	
	// DELETE
	document.addEventListener('DOMContentLoaded', function() {
		// Delete modal
		document.querySelectorAll('.delete-btn').forEach(button => {
			button.addEventListener('click', () => {
				document.getElementById('delete_barangay_id').value = button.getAttribute('data-id');
			});
		});
	});

// VIEW BUTTON

  let map1; // globally store map instance
  let marker1;

  document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('viewBarangayModal');

    modal.addEventListener('show.bs.modal', event => {
      const button = event.relatedTarget;

      // Get data attributes
      const name = button.getAttribute('data-name1');
      const captain = button.getAttribute('data-captain');
      const signature = button.getAttribute('data-signature');
      const lat = parseFloat(button.getAttribute('data-latitude'));
      const lng = parseFloat(button.getAttribute('data-longitude'));

      // Populate modal fields
      document.getElementById('modalBarangayName').textContent = name;
      document.getElementById('modalCaptainName').textContent = captain;
      document.getElementById('modalSignature').src = "../../../uploads/" +signature;

      // Initialize or update map
      setTimeout(() => {
        if (!map1) {
          map1 = L.map('modalMap').setView([lat, lng], 15);
          L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
          }).addTo(map1);
          marker1 = L.marker([lat, lng]).addTo(map1);
        } else {
          map1.setView([lat, lng], 15);
          marker1.setLatLng([lat, lng]);
        }
      }, 200); // slight delay ensures modal is shown before loading map
    });
  });

