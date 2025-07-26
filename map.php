<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Terrain Map - OpenTopoMap + Leaflet</title>

  <!-- Leaflet CSS -->
  <link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
  />
  <style>
    /* Full page layout */
    body, html {
      margin: 0;
      padding: 0;
      height: 100%;
    }

    #terrainMap {
      width: 100%;
      height: 100vh; /* Full screen height */
    }
  </style>
</head>
<body>

  <!-- Map container -->
  <div id="terrainMap"></div>

  <!-- Leaflet JS -->
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

  <script>
    // Initialize map centered at Cebu City
    const terrainMap = L.map('terrainMap').setView([10.3157, 123.8854], 10);

    // Add OpenTopoMap tile layer (terrain view)
    L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
      maxZoom: 17,
      attribution:
        'Map data © <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, SRTM | Map style: © <a href="https://opentopomap.org">OpenTopoMap</a>'
    }).addTo(terrainMap);

    // Optional: Add a marker at default location
    L.marker([10.3157, 123.8854])
      .addTo(terrainMap)
      .bindPopup("Cebu City (Default Location)")
      .openPopup();
  </script>

</body>
</html>

