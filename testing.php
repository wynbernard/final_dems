<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>Static Location Map</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<style>
		#map {
			height: 500px;
			width: 100%;
		}
	</style>

	<!-- Leaflet CSS + JS -->
	<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
	<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
</head>

<body background="G:/Intership/sfedit.jpeg">

	<h2 style="text-align:center;">Barangay Hall Location</h2>
	<div id="map"></div>

	<script>
		// Set static coordinates for the location
		const lat = 10.500000; // You can adjust this if needed
		const lon = 122.833333;

		// Initialize map
		const map = L.map('map').setView([lat, lon], 16);

		L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
			attribution: '&copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors'
		}).addTo(map);

		// Static popup content
		const staticAddress = `
      <strong>Amenity:</strong> Barangay Hall<br>
      <strong>Street:</strong> Camingawan Proper, Taloc<br>
      <strong>City:</strong> Bago City<br>
      <strong>County:</strong> Negros Occidental<br>
      <strong>State:</strong> Western Visayas<br>
      <strong>Country:</strong> Philippines<br>
      <strong>Postal Code:</strong> 6101
    `;

		// Add marker with popup
		L.marker([lat, lon])
			.addTo(map)
			.bindPopup(staticAddress)
			.openPopup();
	</script>
</body>

</html>