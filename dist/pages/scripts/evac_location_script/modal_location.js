// This script dynamically loads cities and barangays for a modal location selection
document.addEventListener('DOMContentLoaded', function () {
	const citySelect = document.getElementById('city');
	const barangaySelect = document.getElementById('barangay');

	let barangayList = [];
	let cityList = [];

	// Load Cities
	fetch('../../../address_json/city.json')
		.then(response => response.json())
		.then(cities => {
			cityList = cities;

			// Filter and show only cities with province_code 0645 (Iloilo)
			const filteredCities = cityList.filter(c => c.province_code === "0645");
			filteredCities.forEach(city => {
				const option = document.createElement('option');
				option.value = city.city_name;
				option.textContent = city.city_name;
				option.setAttribute('data-city-code', city.city_code);
				citySelect.appendChild(option);
			});
		})
		.catch(error => console.error('Error loading city data:', error));

	// Load Barangays
	fetch('../../../address_json/barangays.json')
		.then(response => response.json())
		.then(data => {
			barangayList = data;
		})
		.catch(error => console.error('Error loading barangay data:', error));

	// When City changes → filter Barangays
	citySelect.addEventListener('change', function () {
		const selectedCityCode = this.options[this.selectedIndex].getAttribute('data-city-code');
		barangaySelect.innerHTML = '<option value="">Select Barangay</option>';

		const filteredBarangays = barangayList.filter(b => b.city_code === selectedCityCode);
		filteredBarangays.forEach(barangay => {
			const option = document.createElement('option');
			option.value = barangay.brgy_name;
			option.textContent = barangay.brgy_name;
			barangaySelect.appendChild(option);
		});
	});
});

// MAP
		document.addEventListener("DOMContentLoaded", function() {
		let map = L.map('locationMap').setView([10.5381, 122.8352], 12); // Default center
		let marker;
		const modalElement = document.getElementById('addLocationModal'); // Modal

		// Add OSM tiles
		L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
			attribution: '&copy; OpenStreetMap contributors'
		}).addTo(map);
		modalElement.addEventListener('shown.bs.modal', function() {
			setTimeout(() => {
				map.invalidateSize();
			}, 100);
		});

		// Function to update everything
		function updateMap(lat, lng) {
			// Update marker
			if (marker) {
				marker.setLatLng([lat, lng]);
			} else {
				marker = L.marker([lat, lng]).addTo(map);
			}

			// Move map view
			map.setView([lat, lng], 20);

			// Update display and hidden fields
			document.getElementById('coordinatesDisplay').textContent =
				`Latitude: ${lat}, Longitude: ${lng}`;
			document.getElementById('latitude').value = lat;
			document.getElementById('longitude').value = lng;
		}

		// Barangay select change handler
		document.getElementById('barangay_id').addEventListener('change', function() {
			let selected = this.options[this.selectedIndex];
			let lat = selected.getAttribute('data-lat');
			let lng = selected.getAttribute('data-lng');

			if (lat && lng) {
				updateMap(parseFloat(lat), parseFloat(lng));
			}
		});

			// Manual click on map
			map.on('click', function(e) {
				let lat = e.latlng.lat.toFixed(6);
				let lng = e.latlng.lng.toFixed(6);
				updateMap(lat, lng);
			});
	});


// EDIT
