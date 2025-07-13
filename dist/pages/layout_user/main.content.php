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


  <!--begin::Col Map Box-->
  <div class="col-12 col-sm-12 col-md-12 mb-4">
    <div class="card shadow-lg rounded border-success h-100">
      <div class="card-header bg-success text-white">
        <h3 class="card-title mb-0" style="font-size: 1.25rem;">Disaster Map</h3>
      </div>
      <div class="card-body p-0" style="min-height: 300px;">
        <div id="map" style="height: 100%; width: 100%; min-height: 300px;"></div>
      </div>
      <div class="card-footer text-muted text-center small">
        Powered by OpenStreetMap & Leaflet
      </div>
    </div>
  </div>

  <!--end::Container-->
</div>
<!--end::App Content-->
<?php include '../fetch_data/location_evacuation.php'; ?>

<script>
  async function loadTyphoonData() {
    try {
      const res = await fetch("../typhoon_json/typhoon_scraper.php");
      const data = await res.json();

      const infoDiv = document.getElementById("typhoon-info");
      const updated = document.getElementById("typhoon-updated");
      const title = document.getElementById("report-title");

      if (data.status === "success") {
        title.innerText = data.type || "Phenomenal Report";

        if (data.latest.length === 0) {
          infoDiv.innerHTML = "<p>No phenomenal weather at the moment.</p>";
        } else {
          infoDiv.innerHTML = data.latest.map((entry) => `<p>${entry}</p>`).join("");
        }

        updated.innerText = "Source: PAGASA • Updated: " + new Date().toLocaleString();
      } else {
        throw new Error("Invalid data");
      }
    } catch (err) {
      document.getElementById("typhoon-info").innerHTML = "<p>Failed to load Phenomenal Report.</p>";
    }
  }
  loadTyphoonData();
  setInterval(loadTyphoonData, 300000); // refresh every 5 minutes
</script>
<script>
  const barangayLat = <?php echo $barangayCoords['latitude']; ?>;
  const barangayLng = <?php echo $barangayCoords['longitude']; ?>;
  // Initialize Leaflet map
  const map = L.map("map").setView([barangayLat, barangayLng], 12);

  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);

  // Data from PHP (name, latitude, longitude, barangay)
  const evacuationCenters = <?php echo json_encode($locations, JSON_NUMERIC_CHECK); ?>;

  if (evacuationCenters.length === 0) {
    console.warn("No evacuation center data available.");
  }

  evacuationCenters.forEach(center => {
    const lat = parseFloat(center.latitude);
    const lng = parseFloat(center.longitude);

    if (!isNaN(lat) && !isNaN(lng)) {
      const popupContent = `
        <strong>${center.name}</strong><br>
        <small>Barangay: ${center.barangay_name}</small>
      `;

      L.marker([lat, lng])
        .addTo(map)
        .bindPopup(popupContent);
    } else {
      console.warn("Invalid coordinates for center:", center);
    }

    console.log("Evacuation Center:", center.name, "Barangay:", center.barangay, "at", lat, lng);
  });
</script>