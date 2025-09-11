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
    <?php
    // If the logged-in user (pre_reg) is registered to an evacuation center, show it here
    $user_pre_reg = $_SESSION['pre_reg_id'] ?? null;
    $registeredLocation = null;
  if ($user_pre_reg) {
    // Include evac_loc id and coordinates so we can show the registered location on the map
    $regStmt = $conn->prepare(
      "SELECT er.date_reg, er.status, el.evac_loc_id, el.latitude AS evac_lat, el.longitude AS evac_lng, el.name AS evac_loc_name, r.room_name
       FROM evac_reg_table er
       LEFT JOIN evac_loc_table el ON er.evac_loc_id = el.evac_loc_id
       LEFT JOIN room_table r ON er.room_id = r.room_id
       WHERE er.pre_reg_id = ?
       ORDER BY er.date_reg DESC LIMIT 1"
    );
        $regStmt->bind_param('i', $user_pre_reg);
        $regStmt->execute();
        $regRes = $regStmt->get_result();
        if ($regRes && $regRes->num_rows > 0) {
            $registeredLocation = $regRes->fetch_assoc();
        }
    }
    ?>

  <?php if (!empty($registeredLocation) && strtolower($registeredLocation['status'] ?? '') !== 'dispatched'): ?>
      <div class="row mb-3">
        <div class="col-12">
          <div class="alert alert-info mb-0">
      <strong>Registered Location:</strong>
            <?= htmlspecialchars($registeredLocation['evac_loc_name'] ?? 'N/A') ?>
            <?php if (!empty($registeredLocation['room_name'])): ?>
              &mdash; <small>Room: <?= htmlspecialchars($registeredLocation['room_name']) ?></small>
            <?php endif; ?>
            &nbsp; <span class="text-muted">(Registered on: <?= htmlspecialchars(date('M d, Y H:i', strtotime($registeredLocation['date_reg'] ?? ''))) ?>)</span>
            <?php if (!empty($registeredLocation['status'])): ?>
              &nbsp; <span class="badge bg-secondary ms-2"><?= htmlspecialchars($registeredLocation['status']) ?></span>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endif; ?>
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

  // If the PHP detected a registered evacuation for this logged-in user, expose it here.
  const registeredLocation = <?php echo !empty($registeredLocation) ? json_encode([
      'evac_loc_id' => isset($registeredLocation['evac_loc_id']) ? intval($registeredLocation['evac_loc_id']) : 0,
      'name' => isset($registeredLocation['evac_loc_name']) ? $registeredLocation['evac_loc_name'] : '',
      'latitude' => isset($registeredLocation['evac_lat']) ? floatval($registeredLocation['evac_lat']) : 0.0,
      'longitude' => isset($registeredLocation['evac_lng']) ? floatval($registeredLocation['evac_lng']) : 0.0,
      'status' => isset($registeredLocation['status']) ? $registeredLocation['status'] : ''
  ], JSON_NUMERIC_CHECK) : 'null'; ?>;

  // Helper to replace contents of the evacuationCenters array (keeps the same reference)
  function replaceEvacCenters(newCenters) {
    evacuationCenters.splice(0, evacuationCenters.length, ...newCenters);
  }

  // Behavior:
  // - If user is registered and status === 'dispatched', show all centers EXCEPT the one they registered to.
  // - Otherwise if user is registered, show only the registered center.
  if (registeredLocation) {
    const regId = parseInt(registeredLocation.evac_loc_id) || 0;
    const regStatus = (registeredLocation.status || '').toString().toLowerCase();

    if (regStatus === 'dispatched') {
      // Filter out the registered center from the displayed centers
      const filtered = evacuationCenters.filter(c => parseInt(c.evac_loc_id) !== regId);
      if (filtered.length > 0) {
        replaceEvacCenters(filtered);
      } else {
        // If filtering removed everything, keep original centers but notify in console
        console.warn('All evacuation centers were filtered out after excluding registered location. Showing all centers.');
      }
    } else {
      // Show only the registered center
      replaceEvacCenters([{
        evac_loc_id: regId,
        name: registeredLocation.name,
        latitude: registeredLocation.latitude,
        longitude: registeredLocation.longitude,
        barangay_name: '',
        total_capacity: 0,
        total_recommended: 0
      }]);

      // Center the map on the registered location if coordinates present
      try {
        const lat = parseFloat(registeredLocation.latitude);
        const lng = parseFloat(registeredLocation.longitude);
        if (!isNaN(lat) && !isNaN(lng) && lat !== 0 && lng !== 0) {
          map.setView([lat, lng], 14);
        }
      } catch (e) {
        console.warn('Unable to center map on registered location', e);
      }
    }
  }

  let userLat = null;
  let userLng = null;
  let userMarker = null;
  let routingControl = null;
  // Router provider: 'osrm' (default), 'openrouteservice', 'mapbox'
  const ROUTER_PROVIDER = '<?php echo isset($router_provider) ? $router_provider : 'osrm'; ?>';
  // If using a provider that requires an API key (openrouteservice, mapbox), set it in server-side variable $router_api_key
  const ROUTER_API_KEY = '<?php echo isset($router_api_key) ? $router_api_key : ''; ?>';
  let routeLayer = null; // for non-LRM rendered routes

  const map = L.map("map").setView([barangayLat, barangayLng], 13);

  // If the user is registered to a specific evacuation location, center the map there
  if (registeredLocation) {
    try {
      const lat = parseFloat(registeredLocation.latitude);
      const lng = parseFloat(registeredLocation.longitude);
      if (!isNaN(lat) && !isNaN(lng) && lat !== 0 && lng !== 0) {
        map.setView([lat, lng], 14);
      }
    } catch (e) {
      console.warn('Unable to center map on registered location', e);
    }
  }

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

  // Haversine distance (fallback if OSRM fails)
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

  // Get OSRM route distance (returns Promise)
  function getRouteDistance(lat1, lng1, lat2, lng2) {
    const url = `https://router.project-osrm.org/route/v1/driving/${lng1},${lat1};${lng2},${lat2}?overview=false`;
    return fetch(url)
      .then(res => res.json())
      .then(data => {
        if (data.routes && data.routes.length > 0) {
          return data.routes[0].distance / 1000; // km
        } else {
          return getDistance(lat1, lng1, lat2, lng2);
        }
      })
      .catch(() => getDistance(lat1, lng1, lat2, lng2));
  }

  // Render nearest 3 evacuation centers
  async function renderEvacuationCenters() {
    evacList.innerHTML = '';

    // Compute distances
    const centersWithDistance = await Promise.all(evacuationCenters.map(async center => {
      const lat = parseFloat(center.latitude);
      const lng = parseFloat(center.longitude);
      if (isNaN(lat) || isNaN(lng)) return null;
      const distance = (userLat && userLng) ?
        await getRouteDistance(userLat, userLng, lat, lng) :
        null;
      return {
        ...center,
        lat,
        lng,
        distance
      };
    }));


    // Filter + sort by distance
    const nearest = centersWithDistance
      .filter(c => c && c.distance !== null)
      .sort((a, b) => a.distance - b.distance)
      .slice(0, 3);

    // Find the first location with available capacity
    let recommendedIdx = 0;
    for (let i = 0; i < nearest.length; i++) {
      const cap = parseInt(nearest[i].total_capacity) || 0;
      const rec = parseInt(nearest[i].total_recommended) || 0;
      if (cap === 0 || rec < cap) {
        recommendedIdx = i;
        break;
      }
      // If all are full, will default to the last
      recommendedIdx = i;
    }

    // Fit map bounds
    const bounds = [];

    function addEvacuationMarker(center, isRecommended = false, isFull = false) {
      const fullBadge = isFull ? '<span class="badge bg-danger mb-1">Full</span><br>' : '';
      // Determine if this center is the registered location for the current user
      const isRegistered = (typeof registeredLocation !== 'undefined' && registeredLocation && parseInt(center.evac_loc_id) === parseInt(registeredLocation.evac_loc_id));
      const statusBadge = isRegistered ? '<span class="badge bg-primary mb-1">Registered Location</span><br>' : (isRecommended ? '<span class="badge bg-success mb-1">Recommended</span><br>' : '');
      const popupContent = `
          <strong>${center.name}</strong><br>
          <small>Barangay: ${center.barangay_name}</small><br>
          ${statusBadge}
          ${fullBadge}
          <small class='text-muted'>${center.total_recommended || 0} / ${center.total_capacity || 0} Arrivals</small><br>
          <button onclick="createRoute(${center.lat}, ${center.lng}, this)">Get Route</button>
        `;
      const greenIcon = L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-shadow.png',
        shadowSize: [41, 41]
      });
      const defaultIcon = L.icon({
        iconUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-icon.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-shadow.png',
        shadowSize: [41, 41]
      });
  if (isRecommended && !isRegistered) {
        const recommendedDivIcon = L.divIcon({
          html: `<div style=\"display:flex;align-items:center;\"><img src='https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png' style='width:25px;height:41px;'><span style=\"background:#28a745;color:#fff;padding:2px 8px;border-radius:8px;font-size:0.9em;margin-left:4px;\">Recommended</span></div>`,
          iconSize: [100, 41],
          iconAnchor: [12, 41],
          className: ''
        });
        L.marker([center.lat, center.lng], {
            icon: recommendedDivIcon
          })
          .addTo(map)
          .bindPopup(popupContent);
      } else {
        L.marker([center.lat, center.lng], {
            icon: defaultIcon
          })
          .addTo(map)
          .bindPopup(popupContent);
      }
      bounds.push([center.lat, center.lng]);
    }

    nearest.forEach((center, idx) => {
      const isRecommended = idx === recommendedIdx;
      const cap = parseInt(center.total_capacity) || 0;
      const rec = parseInt(center.total_recommended) || 0;
      const isFull = cap > 0 && rec >= cap;
      addEvacuationMarker(center, isRecommended, isFull);

      // Build list item
      const listItem = document.createElement("li");
      // show a primary border if this is the user's registered location, otherwise highlight recommended
      const regBorder = (typeof registeredLocation !== 'undefined' && registeredLocation && parseInt(center.evac_loc_id) === parseInt(registeredLocation.evac_loc_id)) ? ' border-primary border-2' : (isRecommended ? ' border-success border-2' : '');
      listItem.className = "list-group-item d-flex justify-content-between align-items-start flex-column" + regBorder;
      const registeredBadgeHtml = (typeof registeredLocation !== 'undefined' && registeredLocation && parseInt(center.evac_loc_id) === parseInt(registeredLocation.evac_loc_id)) ? '<span class="badge bg-primary">Registered Location</span><br>' : '';
      listItem.innerHTML = `
          <div class="w-100">
            <strong>${center.name}</strong><br>
            <small>Barangay: ${center.barangay_name}</small><br>
            ${registeredBadgeHtml}${isRecommended && !(typeof registeredLocation !== 'undefined' && registeredLocation && parseInt(center.evac_loc_id) === parseInt(registeredLocation.evac_loc_id)) ? '<span class="badge bg-success">Recommended</span><br>' : ''}
            ${isFull ? '<span class="badge bg-danger">Full</span><br>' : ''}
            <small class="text-muted">${center.distance.toFixed(2)} km by route</small><br>
            <small class="text-muted">${center.total_recommended || 0} / ${center.total_capacity || 0} recommended people</small><br>
            <div class="route-steps mt-2 text-muted small"></div>
            <div class="d-flex flex-row gap-2 mt-2 align-items-center">
              <button class="btn btn-sm btn-outline-primary" onclick="createRoute(${center.lat}, ${center.lng}, this)">Get Route</button>
            </div>
          </div>
        `;
      evacList.appendChild(listItem);

      // Auto-log recommended location arrival (only once, for the recommended)
      if (isRecommended && window._recommendedLogged !== true) {
        window._recommendedLogged = true;
        // Get pre_reg_id from PHP session and send with evac_loc_id
        const preRegId = <?php echo isset($_SESSION['pre_reg_id']) ? intval($_SESSION['pre_reg_id']) : 'null'; ?>;
        if (preRegId) {
          fetch('../action_user/log_recommended_arrival.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
              },
              body: `evac_loc_id=${center.evac_loc_id}&pre_reg_id=${preRegId}`
            })
            .then(res => res.text())
            .then(result => {
              if (result !== 'success') {
                // alert('Recommendation update error: ' + result);
              }
            })
            .catch(err => {
              // alert('Network or server error: ' + err);
            });
        }
      }
    });

    if (bounds.length) {
      bounds.push([userLat, userLng]);
      map.fitBounds(bounds, {
        padding: [20, 20]
      });
    }
  }

  // Draw route on map
  function createRoute(destLat, destLng, btn) {
    if (userLat === null || userLng === null) {
      alert("Getting your current location... please try again.");
      return;
    }

    if (routingControl) {
      try {
        map.removeControl(routingControl);
      } catch (e) {}
      routingControl = null;
    }
    if (routeLayer) {
      try {
        map.removeLayer(routeLayer);
      } catch (e) {}
      routeLayer = null;
    }

    // Find the .route-steps container: in list (li) or in marker popup
    let stepDiv = null;
    // If button is inside a list item, use that
    if (btn.closest("li")) {
      stepDiv = btn.closest("li").querySelector(".route-steps");
    } else {
      // Otherwise, try to find a .route-steps in the popup (if you add one)
      const popup = document.querySelector('.leaflet-popup-content');
      if (popup) {
        stepDiv = popup.querySelector('.route-steps');
        // If not present, create and append it
        if (!stepDiv) {
          stepDiv = document.createElement('div');
          stepDiv.className = 'route-steps mt-2 text-muted small';
          popup.appendChild(stepDiv);
        }
      }
    }
    if (stepDiv) {
      stepDiv.innerHTML = "<em>Loading directions...</em>";
    }

    // Choose routing provider
    if (ROUTER_PROVIDER === 'osrm' || !ROUTER_PROVIDER) {
      // Use existing Leaflet Routing Machine + OSRM
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
        },
        show: false
      }).addTo(map);

      routingControl.on('routesfound', e => {
        const route = e.routes[0];
        const summary = route.summary;
        stepDiv.innerHTML = `<strong>Route found:</strong><br>${ (summary.totalDistance / 1000).toFixed(2) } km, ${ Math.round(summary.totalTime / 60) } min`;
      });

      routingControl.on('routingerror', err => {
        console.error('Routing error:', err);
        stepDiv.innerHTML = "<span class='text-danger'>Failed to get route.</span>";
      });

      return;
    }

    // For providers other than OSRM we'll call their HTTP APIs and draw a polyline on the map
    stepDiv.innerHTML = '<em>Loading directions from ' + ROUTER_PROVIDER + '...</em>';

    const start = [userLng, userLat];
    const end = [destLng, destLat];

    // Helper to render geojson coordinates (array of [lng,lat])
    function renderRouteFromCoords(coords, distanceMeters, durationSec) {
      const latlngs = coords.map(c => [c[1], c[0]]);
      routeLayer = L.polyline(latlngs, {
        color: 'blue',
        weight: 5
      }).addTo(map);
      const bounds = L.latLngBounds(latlngs);
      bounds.extend([userLat, userLng]);
      map.fitBounds(bounds, {
        padding: [20, 20]
      });
      stepDiv.innerHTML = `<strong>Route found:</strong><br>${ (distanceMeters/1000).toFixed(2) } km, ${ Math.round(durationSec/60) } min`;
    }

    if (ROUTER_PROVIDER === 'openrouteservice') {
      if (!ROUTER_API_KEY) {
        stepDiv.innerHTML = '<span class="text-danger">OpenRouteService API key not configured.</span>';
        return;
      }
      // POST to ORS directions (geojson response)
      fetch('https://api.openrouteservice.org/v2/directions/driving-car/geojson', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': ROUTER_API_KEY
        },
        body: JSON.stringify({
          coordinates: [start, end]
        })
      }).then(r => r.json()).then(data => {
        if (data && data.features && data.features.length) {
          const feat = data.features[0];
          const coords = feat.geometry.coordinates; // [[lng,lat],...]
          const props = feat.properties || {};
          const summary = (props.summary) ? props.summary : {};
          const distance = summary.distance || (props.segments && props.segments[0] && props.segments[0].distance) || 0;
          const duration = summary.duration || (props.segments && props.segments[0] && props.segments[0].duration) || 0;
          renderRouteFromCoords(coords, distance, duration);
        } else {
          stepDiv.innerHTML = '<span class="text-danger">No route returned from OpenRouteService.</span>';
        }
      }).catch(err => {
        console.error('ORS error', err);
        stepDiv.innerHTML = '<span class="text-danger">Failed to fetch route from OpenRouteService.</span>';
      });
      return;
    }

    if (ROUTER_PROVIDER === 'mapbox') {
      if (!ROUTER_API_KEY) {
        stepDiv.innerHTML = '<span class="text-danger">Mapbox access token not configured.</span>';
        return;
      }
      const mbUrl = `https://api.mapbox.com/directions/v5/mapbox/driving/${userLng},${userLat};${destLng},${destLat}?geometries=geojson&overview=full&access_token=${encodeURIComponent(ROUTER_API_KEY)}`;
      fetch(mbUrl).then(r => r.json()).then(data => {
        if (data && data.routes && data.routes.length) {
          const route = data.routes[0];
          const coords = route.geometry.coordinates; // [[lng,lat],...]
          const distance = route.distance || 0;
          const duration = route.duration || 0;
          renderRouteFromCoords(coords, distance, duration);
        } else {
          stepDiv.innerHTML = '<span class="text-danger">No route returned from Mapbox.</span>';
        }
      }).catch(err => {
        console.error('Mapbox error', err);
        stepDiv.innerHTML = '<span class="text-danger">Failed to fetch route from Mapbox.</span>';
      });
      return;
    }

    // Unknown provider
    stepDiv.innerHTML = '<span class="text-danger">Unknown routing provider: ' + ROUTER_PROVIDER + '</span>';
  }

  // Geolocation
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(pos => {
      userLat = pos.coords.latitude;
      userLng = pos.coords.longitude;

      if (!userMarker) {
        userMarker = L.marker([userLat, userLng], {
            icon: userIcon
          })
          .addTo(map)
          .bindPopup("<strong>You are here</strong>")
          .openPopup();
      }

      // Save user coordinates to server (solo_address_table or family_table)
      (function saveCoords(lat, lng) {
        fetch('../action_user/save_coordinates.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `latitude=${encodeURIComponent(lat)}&longitude=${encodeURIComponent(lng)}`
          })
          .then(r => r.json())
          .then(data => {
            if (data.success) {
              console.log('Coordinates saved:', data);
            } else {
              console.warn('Failed to save coordinates:', data.error || data);
            }
          }).catch(err => {
            console.error('Network error while saving coordinates', err);
          });
      })(userLat, userLng);

      renderEvacuationCenters();
    }, err => {
      console.warn("Location error:", err.message);
      alert("Failed to get location.");
    }, {
      enableHighAccuracy: true,
      maximumAge: 0,
      timeout: 10000
    });
  } else {
    alert("Geolocation is not supported.");
  }
</script>