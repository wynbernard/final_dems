let map, marker;
let addFenceMode = false;
let addFencePoints = [];
let addFencePolyline = null;

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
			// Reset disaster-prone type checkboxes
			document.querySelectorAll('#addLocationModal input[name="disaster_prone_type[]"]').forEach(cb => {
				cb.checked = false;
			});
			const selectAllCheckbox = document.getElementById('add_select_all_prone');
			if (selectAllCheckbox) {
				selectAllCheckbox.checked = false;
			}
		});

		// Handle map click
		map.on('click', function(e) {
			if (addFenceMode) {
				// Add fence point
				const lat = parseFloat(e.latlng.lat.toFixed(6));
				const lng = parseFloat(e.latlng.lng.toFixed(6));
				addFencePoints.push([lat, lng]);
				updateAddFenceLine();
				updateAddFenceJSON();
			} else {
				// Set marker location
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
			}
		});
		
		// Setup fence mode buttons for add modal
		const toggleAddFenceBtn = document.getElementById('toggleAddFenceMode');
		const clearAddFenceBtn = document.getElementById('clearAddFence');
		
		if (toggleAddFenceBtn) {
			toggleAddFenceBtn.addEventListener('click', toggleAddFenceMode);
		}
		if (clearAddFenceBtn) {
			clearAddFenceBtn.addEventListener('click', clearAddFence);
		}
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
let editFenceMode = false;
let editFencePoints = [];
let editFencePolyline = null;
let currentBoundaryBarangay = '';

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
		if (editFenceMode) {
			// Add fence point
			const { lat, lng } = e.latlng;
			editFencePoints.push([lat, lng]);
			updateFenceLine();
			updateFenceJSON();
		} else {
			// Move marker
			const { lat, lng } = e.latlng;
			editMarker.setLatLng([lat, lng]);
			updateEditCoordinates(lat, lng);
		}
	});
	}

	function updateEditCoordinates(lat, lng) {
		document.getElementById('edit_latitude').value = lat;
		document.getElementById('edit_longitude').value = lng;
		document.getElementById('editCoordinatesDisplay').innerText =
			`Latitude: ${lat.toFixed(6)}, Longitude: ${lng.toFixed(6)}`;
	}

	function toggleEditFenceMode() {
		editFenceMode = !editFenceMode;
		const toggleBtn = document.getElementById('toggleFenceMode');
		const clearBtn = document.getElementById('clearFence');
		
		if (editFenceMode) {
			toggleBtn.innerHTML = '<i class="bi bi-geo-alt"></i> Move Marker';
			toggleBtn.className = 'btn btn-sm btn-secondary';
			clearBtn.style.display = 'inline-block';
			editMarker.dragging.disable();
		} else {
			toggleBtn.innerHTML = '<i class="bi bi-vector-pen"></i> Draw Fence';
			toggleBtn.className = 'btn btn-sm btn-outline-primary';
			clearBtn.style.display = 'none';
			editMarker.dragging.enable();
		}
	}

	function updateFenceLine() {
		if (editFencePolyline) {
			editMap.removeLayer(editFencePolyline);
		}
		if (editFencePoints.length > 1) {
			editFencePolyline = L.polyline(editFencePoints, {
				color: '#f97316',
				weight: 3,
				opacity: 0.8
			}).addTo(editMap);
		}
	}

	function updateFenceJSON() {
		const coords = editFencePoints.map(point => [point[0], point[1]]);
		const jsonValue = JSON.stringify(coords);
		document.getElementById('edit_boundary_json').value = jsonValue;
		console.log('Updated boundary coordinates:', jsonValue);
	}

	function clearEditFence() {
		editFencePoints = [];
		if (editFencePolyline) {
			editMap.removeLayer(editFencePolyline);
			editFencePolyline = null;
		}
		document.getElementById('edit_boundary_json').value = '';
		
		// Also remove from local boundary data
		if (currentBoundaryBarangay && window.barangayBoundaries) {
			delete window.barangayBoundaries[currentBoundaryBarangay];
			console.log('Cleared boundary data for:', currentBoundaryBarangay);
		}
	}

	function loadExistingFence(barangayName) {
		if (window.barangayBoundaries && window.barangayBoundaries[barangayName]) {
			const boundary = window.barangayBoundaries[barangayName];
			if (boundary.coordinates) {
				editFencePoints = boundary.coordinates.map(c => [c.lat, c.lng]);
				updateFenceLine();
				updateFenceJSON();
			}
		}
	}

	// Add modal fence functions
	function toggleAddFenceMode() {
		addFenceMode = !addFenceMode;
		const toggleBtn = document.getElementById('toggleAddFenceMode');
		const clearBtn = document.getElementById('clearAddFence');
		
		if (addFenceMode) {
			toggleBtn.innerHTML = '<i class="bi bi-geo-alt"></i> Set Location';
			toggleBtn.className = 'btn btn-sm btn-secondary';
			clearBtn.style.display = 'inline-block';
		} else {
			toggleBtn.innerHTML = '<i class="bi bi-vector-pen"></i> Draw Fence';
			toggleBtn.className = 'btn btn-sm btn-outline-primary';
			clearBtn.style.display = 'none';
		}
	}

	function updateAddFenceLine() {
		if (addFencePolyline) {
			map.removeLayer(addFencePolyline);
		}
		if (addFencePoints.length > 1) {
			addFencePolyline = L.polyline(addFencePoints, {
				color: '#f97316',
				weight: 3,
				opacity: 0.8
			}).addTo(map);
		}
	}

	function updateAddFenceJSON() {
		const coords = addFencePoints.map(point => [point[0], point[1]]);
		const jsonValue = JSON.stringify(coords);
		document.getElementById('add_boundary_json').value = jsonValue;
		console.log('Add modal boundary coordinates:', jsonValue);
	}

	function clearAddFence() {
		addFencePoints = [];
		if (addFencePolyline) {
			map.removeLayer(addFencePolyline);
			addFencePolyline = null;
		}
		document.getElementById('add_boundary_json').value = '';
		console.log('Cleared add fence data');
	}

	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('.edit-btn').forEach(button => {
			button.addEventListener('click', () => {
				const id = button.getAttribute('data-id');
				const name = button.getAttribute('data-name');
				const captain = button.getAttribute('data-captain');
				const population = button.getAttribute('data-population');
				const signature = button.getAttribute('data-signature');
				const latitude = parseFloat(button.getAttribute('data-latitude')) || 10.5382;
				const longitude = parseFloat(button.getAttribute('data-longitude')) || 122.8351;

				document.getElementById('edit_barangay_id').value = id;
				document.getElementById('edit_barangay_name').value = name;
				document.getElementById('edit_barangay_captain').value = captain;
				document.getElementById('edit_current_signature').value = signature;
				document.getElementById('edit_total_population').value = button.getAttribute('data-population');
				
				// Load disaster-prone types from JSON file only
				const disasterProneTypes = [];
				if (window.barangayBoundaries && window.barangayBoundaries[name] && window.barangayBoundaries[name].disaster_prone_types) {
					disasterProneTypes.push(...window.barangayBoundaries[name].disaster_prone_types);
				}
				
				// Clear all checkboxes first (only edit modal checkboxes)
				document.querySelectorAll('#editLocationModal input[name="disaster_prone_type[]"]').forEach(cb => {
					cb.checked = false;
				});
				
				// Check the appropriate checkboxes
				disasterProneTypes.forEach(type => {
					const checkbox = document.querySelector(`#editLocationModal input[name="disaster_prone_type[]"][value="${type}"]`);
					if (checkbox) {
						checkbox.checked = true;
					}
				});
				
				// Update "Select All" checkbox state
				if (typeof updateSelectAll === 'function') {
					updateSelectAll('edit');
				}
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
			clearEditFence(); // Clear any previous fence
			loadExistingFence(name);
			
			// Setup fence mode buttons
			const toggleBtn = document.getElementById('toggleFenceMode');
			const clearBtn = document.getElementById('clearFence');
			
			if (toggleBtn) {
				toggleBtn.addEventListener('click', toggleEditFenceMode);
			}
			if (clearBtn) {
				clearBtn.addEventListener('click', clearEditFence);
			}
			
			// Setup form submission handler
			const form = document.getElementById('editLocationForm');
			if (form) {
				form.addEventListener('submit', function(e) {
					const boundaryData = document.getElementById('edit_boundary_json').value;
					console.log('Form submitting with boundary data:', boundaryData);
				});
			}
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

      // Load disaster-prone types from JSON file only
      let disasterProneTypes = [];
      if (window.barangayBoundaries && window.barangayBoundaries[name] && window.barangayBoundaries[name].disaster_prone_types) {
        disasterProneTypes = window.barangayBoundaries[name].disaster_prone_types;
      }
      const disasterProneTypeDisplay = disasterProneTypes.length > 0 ? disasterProneTypes.join(', ') : 'Not specified';

      // Populate modal fields
      document.getElementById('modalBarangayName').textContent = name;
      document.getElementById('modalCaptainName').textContent = captain;
      document.getElementById('modalDisasterProneType').textContent = disasterProneTypeDisplay;
      document.getElementById('modalSignature').src = "../../../uploads/" +signature;
    });

    // Initialize map when modal is fully shown
    modal.addEventListener('shown.bs.modal', event => {
      const button = event.relatedTarget;

      // Get data attributes
      const name = button.getAttribute('data-name1');
      const lat = parseFloat(button.getAttribute('data-latitude'));
      const lng = parseFloat(button.getAttribute('data-longitude'));

      if (isNaN(lat) || isNaN(lng)) {
        console.error('Invalid coordinates:', lat, lng);
        return;
      }
	  
      // Initialize or update map
      const mapContainer = document.getElementById('modalMap');
      if (!mapContainer) {
        console.error('Map container not found');
        return;
      }

      // Check if map already exists and remove it
      if (map1) {
        map1.remove();
        map1 = null;
        marker1 = null;
      }

      // Small delay to ensure modal is fully rendered
      setTimeout(() => {
        // Initialize new map
        map1 = L.map('modalMap').setView([lat, lng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution: '© OpenStreetMap'
        }).addTo(map1);
        
        // Invalidate size to ensure proper rendering
        setTimeout(() => {
          map1.invalidateSize();
        }, 50);
        
        marker1 = L.marker([lat, lng]).addTo(map1);
        marker1.bindPopup(`<strong>${name}</strong><br>Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}`).openPopup();
        
        // Display boundary fence if it exists
        if (window.barangayBoundaries && window.barangayBoundaries[name]) {
          const boundary = window.barangayBoundaries[name];
          
          if (boundary.coordinates && Array.isArray(boundary.coordinates) && boundary.coordinates.length > 0) {
            const coords = boundary.coordinates.map(c => [c.lat, c.lng]);
            
            let boundaryLayer;
            if (boundary.type === 'polygon' && coords.length >= 3) {
              boundaryLayer = L.polygon(coords, {
                color: '#f97316',
                fillColor: '#fb923c',
                fillOpacity: 0.25,
                weight: 2
              });
            } else {
              boundaryLayer = L.polyline(coords, {
                color: '#22c55e',
                weight: 3,
                opacity: 0.8
              });
            }
            
            boundaryLayer.addTo(map1);
            boundaryLayer.bindPopup(`<strong>${name}</strong><br>Boundary: ${boundary.type}<br>Points: ${coords.length}`);
            
            // Fit map to show both marker and boundary
            const group = L.featureGroup([marker1, boundaryLayer]);
            map1.fitBounds(group.getBounds(), { padding: [20, 20] });
          } else {
            // No boundary, just center on marker
            map1.setView([lat, lng], 15);
          }
        } else {
          // No boundary, just center on marker
          map1.setView([lat, lng], 15);
        }
      }, 100);
    });

    // Cleanup map when modal is hidden
    modal.addEventListener('hidden.bs.modal', function() {
      if (map1) {
        map1.remove();
        map1 = null;
        marker1 = null;
      }
    });
  });

	// Purok management JS
	document.addEventListener('DOMContentLoaded', function() {
		const viewModal = document.getElementById('viewBarangayModal');
		const purokTableBody = document.querySelector('#purokTable tbody');
		const btnAddPurok = document.getElementById('btnAddPurok');

		function loadPuroks(barangayId) {
			if (!purokTableBody) return;
			purokTableBody.innerHTML = '<tr><td colspan="5">Loading...</td></tr>';
			fetch(`../action/brgy_management_action/list_purok.php?barangay_id=${barangayId}`)
				.then(r => r.json())
				.then(data => {
					if (!data.success) {
						purokTableBody.innerHTML = `<tr><td colspan="5">${data.message || 'Failed to load'}</td></tr>`;
						return;
					}
					const rows = data.data.map((p, idx) => {
						return `
							<tr data-id="${p.purok_id}">
								<td>${idx+1}</td>
								<td class="purok-name">${escapeHtml(p.purok_name)}</td>
								<td class="purok-leader">${escapeHtml(p.purok_leader || '')}</td>
								<td class="purok-pick-up-point">${escapeHtml(p.pickup_point_name || '')}</td>
								<td>
									<button class="btn btn-sm btn-primary edit-purok-btn">Edit</button>
									<button class="btn btn-sm btn-danger delete-purok-btn">Delete</button>
								</td>
							</tr>`;
					}).join('');
					purokTableBody.innerHTML = rows || '<tr><td colspan="5">No puroks found</td></tr>';
					attachPurokHandlers(barangayId);
				}).catch(err => {
					purokTableBody.innerHTML = `<tr><td colspan="5">Error loading puroks</td></tr>`;
					console.error(err);
				});
		}

		function attachPurokHandlers(barangayId) {
			document.querySelectorAll('.edit-purok-btn').forEach(btn => {
				btn.addEventListener('click', (e) => {
					const tr = e.target.closest('tr');
					const id = tr.getAttribute('data-id');
					const name = tr.querySelector('.purok-name').textContent.trim();
					const leader = tr.querySelector('.purok-leader').textContent.trim();
					const pickupPoint = tr.querySelector('.purok-pick-up-point').textContent.trim();
					// populate edit modal and show
					document.getElementById('editPurokId').value = id;
					document.getElementById('editPurokName').value = name;
					document.getElementById('editPurokLeader').value = leader;
					document.getElementById('editPurokPickUpPoint').value = pickupPoint;
					const editModalEl = document.getElementById('editPurokModal');
					const editModal = new bootstrap.Modal(editModalEl);
					editModal.show();
				});
			});

			document.querySelectorAll('.delete-purok-btn').forEach(btn => {
				btn.addEventListener('click', (e) => {
					const tr = e.target.closest('tr');
					const id = tr.getAttribute('data-id');
					// populate delete modal and show
					document.getElementById('deletePurokId').value = id;
					const name = tr.querySelector('.purok-name').textContent.trim();
					document.getElementById('deletePurokName').textContent = name;
					const delModalEl = document.getElementById('deletePurokModal');
					const delModal = new bootstrap.Modal(delModalEl);
					delModal.show();
				});
			});
		}

		// show Add Purok modal instead
		function openAddPurokModal(barangayId) {
			document.getElementById('addPurokBarangayId').value = barangayId;
			document.getElementById('addPurokName').value = '';
			document.getElementById('addPurokLeader').value = '';
			document.getElementById('addPurokPickUpPoint').value = '';
			const addModalEl = document.getElementById('addPurokModal');
			const addModal = new bootstrap.Modal(addModalEl);
			addModal.show();
		}

		// handled via edit modal submit handler

		// When the view modal is shown, load puroks for the barangay
		viewModal.addEventListener('show.bs.modal', function(event) {
			const button = event.relatedTarget;
			const barangayId = button.getAttribute('data-id');
			// expose for other functions
			viewModal.dataset.barangayId = barangayId;
			loadPuroks(barangayId);
		});

		// Add purok button
		if (btnAddPurok) btnAddPurok.addEventListener('click', function() {
			const barangayId = viewModal.dataset.barangayId;
			if (!barangayId) return alert('Barangay ID not found');
			openAddPurokModal(barangayId);
		});

		// AJAX submit to keep modal open and show SweetAlert toast
		const addPurokForm = document.getElementById('addPurokForm');
		if (addPurokForm) {
			addPurokForm.addEventListener('submit', function(e) {
				e.preventDefault();
				const formData = new URLSearchParams(new FormData(addPurokForm));
				fetch('../action/brgy_management_action/add_purok.php', {
					method: 'POST',
					headers: { 'X-Requested-With': 'XMLHttpRequest' },
					body: formData
				}).then(r => {
					if (!r.ok) {
						throw new Error('Network response was not ok');
					}
					return r.json();
				}).then(res => {
					if (res && res.success) {
						if (window.Swal) {
							Swal.fire({
								toast: true,
								position: 'top',
								showConfirmButton: false,
								timer: 3000,
								icon: 'success',
								title: 'Purok added successfully'
							});
						}
						// Close add modal and reload puroks
						const addModal = bootstrap.Modal.getInstance(document.getElementById('addPurokModal'));
						if (addModal) addModal.hide();
						// Clear form
						document.getElementById('addPurokName').value = '';
						document.getElementById('addPurokLeader').value = '';
						document.getElementById('addPurokPickUpPoint').value = '';
						// Reload puroks list
						const barangayId = viewModal.dataset.barangayId;
						if (barangayId) loadPuroks(barangayId);
					} else {
						if (window.Swal) {
							Swal.fire({icon:'error', title:'Error', text: (res && res.message) || 'Add failed'});
						} else alert('Add failed: ' + (res && res.message || 'Unknown error'));
					}
				}).catch(err => {
					console.error('Add purok error:', err);
					if (window.Swal) {
						Swal.fire({icon:'error', title:'Error', text:'Failed to add purok. Please try again.'});
					} else alert('Add failed');
				});
			});
		}

		const editPurokForm = document.getElementById('editPurokForm');
		if (editPurokForm) {
			editPurokForm.addEventListener('submit', function(e) {
				e.preventDefault();
				const formData = new URLSearchParams(new FormData(editPurokForm));
				fetch('../action/brgy_management_action/edit_purok.php', {
					method: 'POST',
					headers: { 'X-Requested-With': 'XMLHttpRequest' },
					body: formData
				}).then(r => {
					if (!r.ok) {
						throw new Error('Network response was not ok');
					}
					return r.json();
				}).then(res => {
					if (res && res.success) {
						if (window.Swal) {
							Swal.fire({
								toast: true,
								position: 'top',
								showConfirmButton: false,
								timer: 3000,
								icon: 'success',
								title: 'Purok updated successfully'
							});
						}
						// Close edit modal and reload puroks
						const editModal = bootstrap.Modal.getInstance(document.getElementById('editPurokModal'));
						if (editModal) editModal.hide();
						// Reload puroks list
						const barangayId = viewModal.dataset.barangayId;
						if (barangayId) loadPuroks(barangayId);
					} else {
						if (window.Swal) {
							Swal.fire({icon:'error', title:'Error', text: (res && res.message) || 'Update failed'});
						} else alert('Update failed: ' + (res && res.message || 'Unknown error'));
					}
				}).catch(err => {
					console.error('Edit purok error:', err);
					if (window.Swal) {
						Swal.fire({icon:'error', title:'Error', text:'Failed to update purok. Please try again.'});
					} else alert('Update failed');
				});
			});
		}

		const deletePurokForm = document.getElementById('deletePurokForm');
		if (deletePurokForm) {
			deletePurokForm.addEventListener('submit', function(e) {
				e.preventDefault();
				const formData = new URLSearchParams(new FormData(deletePurokForm));
				fetch('../action/brgy_management_action/delete_purok.php', {
					method: 'POST',
					headers: { 'X-Requested-With': 'XMLHttpRequest' },
					body: formData
				}).then(r => {
					if (!r.ok) {
						throw new Error('Network response was not ok');
					}
					return r.json();
				}).then(res => {
					if (res && res.success) {
						const delModalEl = document.getElementById('deletePurokModal');
						const delModal = bootstrap.Modal.getInstance(delModalEl);
						if (delModal) delModal.hide();
						if (window.Swal) {
							Swal.fire({
								toast: true,
								position: 'top',
								showConfirmButton: false,
								timer: 3000,
								icon: 'success',
								title: 'Purok deleted successfully'
							});
						}
						// Reload puroks list
						const barangayId = viewModal.dataset.barangayId;
						if (barangayId) loadPuroks(barangayId);
					} else {
						if (window.Swal) {
							Swal.fire({icon:'error', title:'Error', text: (res && res.message) || 'Delete failed'});
						} else alert('Delete failed: ' + (res && res.message || 'Unknown error'));
					}
				}).catch(err => {
					console.error('Delete purok error:', err);
					if (window.Swal) {
						Swal.fire({icon:'error', title:'Error', text:'Failed to delete purok. Please try again.'});
					} else alert('Delete failed');
				});
			});
		}

		function escapeHtml(str) {
			return String(str)
				.replace(/&/g, '&amp;')
				.replace(/</g, '&lt;')
				.replace(/>/g, '&gt;')
				.replace(/"/g, '&quot;')
				.replace(/'/g, '&#39;');
		}
	});

