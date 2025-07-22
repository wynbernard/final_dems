<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">Dashboard</h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="#">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
        </ol>
      </div>
    </div>
    <!--end::Row-->
  </div>
  <!--end::Container-->
</div>
<!--end::App Content Header-->

<!--begin::App Content-->
<div class="app-content">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row">
      <!--begin::Col-->
      <div class="col-lg-3 col-6">
        <!--begin::Small Box Widget 3-->
        <!-- <div class="small-box text-bg-warning">
          <div class="inner">
            <?php
            $query = "SELECT COUNT(*) AS evac_reg FROM evac_reg_table";
            $result = mysqli_query($conn, $query);
            $row = mysqli_fetch_assoc($result);
            $total_evac_reg = $row['evac_reg'];
            ?>
            <h3><?php echo htmlspecialchars($total_evac_reg) ?></h3>
            <p>Evacuation Registration</p>
          </div>
          <svg
            class="small-box-icon"
            fill="currentColor"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
            aria-hidden="true">
            <path
              d="M6.25 6.375a4.125 4.125 0 118.25 0 4.125 4.125 0 01-8.25 0zM3.25 19.125a7.125 7.125 0 0114.25 0v.003l-.001.119a.75.75 0 01-.363.63 13.067 13.067 0 01-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 01-.364-.63l-.001-.122zM19.75 7.5a.75.75 0 00-1.5 0v2.25H16a.75.75 0 000 1.5h2.25v2.25a.75.75 0 001.5 0v-2.25H22a.75.75 0 000-1.5h-2.25V7.5z"></path>
          </svg>
          <a
            href="#"
            class="small-box-footer link-dark link-underline-opacity-0 link-underline-opacity-50-hover">
            More info <i class="bi bi-link-45deg"></i>
          </a>
        </div> -->
        <!--end::Small Box Widget 3-->
      </div>

      <!--end::Col-->
    </div>
    <!-- /.row (main row) -->
  </div>


  <div class="row">
    <!-- Map Column -->
    <div class="col-lg-8 col-md-7 col-sm-12 mb-4">
      <div class="card shadow-lg rounded border-success h-100">
        <div class="card-header bg-success text-white">
          <h3 class="card-title mb-0" style="font-size: 1.25rem;">Disaster Map</h3>
        </div>
        <div class="card-body p-0" style="min-height: 300px;">
          <div id="map" style="height: 100%; width: 100%; min-height: 300px;"></div>
          <div id="route-directions" class="leaflet-routing-container"></div>
        </div>
        <div class="card-footer text-muted text-center small">
          Powered by OpenStreetMap & Leaflet
        </div>
      </div>
    </div>

    <!-- Evacuation Centers List Column -->
    <div class="col-lg-4 col-md-5 col-sm-12 mb-4">
      <div class="card shadow-sm h-100">
        <div class="card-header bg-light">
          <h5 class="mb-0">Evacuation Centers</h5>
        </div>
        <ul id="evacuation-list" class="list-group list-group-flush"></ul>
      </div>
    </div>
  </div>

  <!--end::Container-->
</div>
<!--end::App Content-->
<?php include '../fetch_data/location_evacuation.php'; ?>
<!-- Leaflet -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<!-- Leaflet Routing Machine -->
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.css" />
<script src="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.min.js"></script>

<script>
  const barangayLat = <?php echo $barangayCoords['latitude']; ?>;
  const barangayLng = <?php echo $barangayCoords['longitude']; ?>;
  const evacuationCenters = <?php echo json_encode($locations, JSON_NUMERIC_CHECK); ?>;

  let userLat = null;
  let userLng = null;
  let userMarker = null;
  let routingControl = null;

  const map = L.map("map").setView([barangayLat, barangayLng], 13);

  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);

  const userIcon = L.icon({
    iconUrl: 'https://cdn-icons-png.flaticon.com/512/684/684908.png',
    iconSize: [30, 30],
    iconAnchor: [15, 30],
    popupAnchor: [0, -30]
  });

  const evacList = document.getElementById("evacuation-list");
  const bounds = [];

  function getDistance(lat1, lng1, lat2, lng2) {
    const R = 6371;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLng = (lng2 - lng1) * Math.PI / 180;
    const a = Math.sin(dLat / 2) ** 2 +
      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
      Math.sin(dLng / 2) ** 2;
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
  }

  function renderEvacuationCenters() {
    evacList.innerHTML = '';

    evacuationCenters.forEach(center => {
      const lat = parseFloat(center.latitude);
      const lng = parseFloat(center.longitude);

      if (!isNaN(lat) && !isNaN(lng)) {
        // Add marker
        const popupContent = `
          <strong>${center.name}</strong><br>
          <small>Barangay: ${center.barangay_name}</small><br>
          <button onclick="createRoute(${lat}, ${lng}, this)">Get Route</button>
        `;
        L.marker([lat, lng]).addTo(map).bindPopup(popupContent);
        bounds.push([lat, lng]);

        // Distance calculation
        const distance = (userLat && userLng) ? getDistance(userLat, userLng, lat, lng) : null;
        const distanceText = distance ? `<small class="text-muted">${distance.toFixed(2)} km away</small><br>` : '';

        // Build list item
        const listItem = document.createElement("li");
        listItem.className = "list-group-item d-flex justify-content-between align-items-start flex-column";
        listItem.innerHTML = `
          <div class="w-100">
            <strong>${center.name}</strong><br>
            <small>Barangay: ${center.barangay_name}</small><br>
            ${distanceText}
            <div class="route-steps mt-2 text-muted small"></div>
          </div>
          <button class="btn btn-sm btn-outline-primary align-self-end mt-2" onclick="createRoute(${lat}, ${lng}, this)">Get Route</button>
        `;
        evacList.appendChild(listItem);
      }
    });

    if (bounds.length) {
      map.fitBounds(bounds, {
        padding: [20, 20]
      });
    }
  }

  function createRoute(destLat, destLng, btn) {
    if (userLat === null || userLng === null) {
      alert("Getting your current location... please try again.");
      return;
    }

    if (routingControl) {
      map.removeControl(routingControl);
    }

    const stepDiv = btn.closest("li").querySelector(".route-steps");
    stepDiv.innerHTML = "<em>Loading directions...</em>";

    routingControl = L.Routing.control({
      waypoints: [
        L.latLng(userLat, userLng),
        L.latLng(destLat, destLng)
      ],
      router: new L.Routing.OSRMv1({
        serviceUrl: 'https://router.project-osrm.org/route/v1'
      }),
      routeWhileDragging: false,
      draggableWaypoints: false,
      addWaypoints: false,
      fitSelectedRoutes: true,
      showAlternatives: false,
      createMarker: () => null,
      lineOptions: {
        styles: [{
          color: 'blue',
          weight: 5
        }]
      }
    }).addTo(map);

    routingControl.on('routesfound', e => {
      const steps = e.routes[0].instructions.map((step, i) => `${i + 1}. ${step.text}`);
      stepDiv.innerHTML = `<strong>Directions:</strong><br>${steps.join("<br>")}`;
    });

    routingControl.on('routingerror', err => {
      console.error("Routing error:", err);
      stepDiv.innerHTML = "<span class='text-danger'>Failed to get route.</span>";
    });
  }

  // Geolocation & start tracking
  if (navigator.geolocation) {
    navigator.geolocation.watchPosition(pos => {
      userLat = pos.coords.latitude;
      userLng = pos.coords.longitude;

      if (!userMarker) {
        userMarker = L.marker([userLat, userLng], {
            icon: userIcon
          })
          .addTo(map)
          .bindPopup("<strong>You are here</strong>")
          .openPopup();
        map.setView([userLat, userLng], 15);
      } else {
        userMarker.setLatLng([userLat, userLng]);
      }

      renderEvacuationCenters();
    }, err => {
      console.warn("Location error:", err.message);
      renderEvacuationCenters();
    }, {
      enableHighAccuracy: true,
      maximumAge: 0,
      timeout: 10000
    });
  } else {
    alert("Geolocation is not supported.");
    renderEvacuationCenters();
  }
</script>