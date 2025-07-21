<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0"> Admin Dashboard</h3>
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
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!--end::App Content Header-->
<!--begin::App Content-->

<div class="app-content position-relative">
  <!--begin::Container-->
  <div id="evacMapContainer">
    <div id="evacMap"></div>
  </div>
  <div class="container-fluid position-relative" style="z-index: 2;">
    <!--begin::Row-->
    <div class="row">
      <!--begin::Col-->
      <div class="col-lg-3 col-6">
        <!--begin::Small Box Widget 1-->
        <div class="small-box text-bg-primary">
          <div class="inner">
            <?php
            $query = "SELECT COUNT(*) AS total_admin FROM admin_table";
            $result = mysqli_query($conn, $query);
            $row = mysqli_fetch_assoc($result);
            $total_admin = $row['total_admin'];
            ?>
            <h3><?php echo htmlspecialchars($total_admin) ?></h3>
            <p>Total Staff</p>
          </div>
          <svg
            class="small-box-icon"
            fill="currentColor"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
            aria-hidden="true">
            <path
              d="M4.619,15.479c0.888,3.39,3.752,6.513,7.382,6.513c3.684,0,6.594-3.109,7.504-6.49c0.346-0.039,0.632-0.303,0.663-0.663
				l0.115-1.336c0.029-0.348-0.189-0.646-0.506-0.756c-0.006-0.08-0.008-0.161-0.017-0.24c-0.068-3.062-0.6-5.534-3.01-6.556
				c-2.544-1.078-4.786-1.093-6.432-0.453C10.21,5.541,9.931,5.912,9.822,5.979C9.713,6.046,9.136,5.856,8.917,5.907
				c-3.61,0.516-4.801,3.917-4.538,6.569C4.371,12.55,4.366,12.625,4.36,12.7c-0.349,0.087-0.599,0.404-0.567,0.774l0.114,1.336
				C3.94,15.188,4.25,15.462,4.619,15.479z M5.388,12.833c1.581-0.579,4.622-1.79,4.952-2.426c1.383,1.437,6.267,2.244,8.411,2.513
				c0.009,0.139,0.021,0.274,0.021,0.414c0,3.525-2.958,7.623-6.771,7.623c-3.799,0-6.638-4.024-6.638-7.623
				C5.362,13.165,5.375,13,5.388,12.833z"></path>
            <path d="M17.818,20.777c-0.19-0.029-0.376,0.014-0.498,0.063l-3.041,4.113l-2.307-1.84l-0.014,0.012v0.013l-0.003-0.003
				l-2.307,1.84l-3.041-4.113c-0.121-0.05-0.308-0.093-0.498-0.064C0.364,21.608,0,34.584,0,34.584l11.969,0.008v-0.021
				l11.958-0.008C23.928,34.563,23.562,21.587,17.818,20.777z" />
          </svg>
          <a
            href="#"
            class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
            More info <i class="bi bi-link-45deg"></i>
          </a>
        </div>
        <!--end::Small Box Widget 1-->
      </div>
      <!--end::Col-->
      <div class="col-lg-3 col-6">
        <!--begin::Small Box Widget 2-->
        <div class="small-box text-bg-success">
          <div class="inner">
            <?php
            $query = "SELECT COUNT(*) AS pre_reg FROM pre_reg_table";
            $result = mysqli_query($conn, $query);
            $row = mysqli_fetch_assoc($result);
            $total_pre_reg = $row['pre_reg'];
            ?>
            <h3><?php echo htmlspecialchars($total_pre_reg) ?><sup class="fs-5"></sup></h3>
            <p>Pre-Registration</p>
          </div>
          <svg
            class="small-box-icon"
            fill="currentColor"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
            aria-hidden="true">
            <path
              d="M18.375 2.25c-1.035 0-1.875.84-1.875 1.875v15.75c0 1.035.84 1.875 1.875 1.875h.75c1.035 0 1.875-.84 1.875-1.875V4.125c0-1.036-.84-1.875-1.875-1.875h-.75zM9.75 8.625c0-1.036.84-1.875 1.875-1.875h.75c1.036 0 1.875.84 1.875 1.875v11.25c0 1.035-.84 1.875-1.875 1.875h-.75a1.875 1.875 0 01-1.875-1.875V8.625zM3 13.125c0-1.036.84-1.875 1.875-1.875h.75c1.036 0 1.875.84 1.875 1.875v6.75c0 1.035-.84 1.875-1.875 1.875h-.75A1.875 1.875 0 013 19.875v-6.75z"></path>
          </svg>
          <a
            href="#"
            class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
            More info <i class="bi bi-link-45deg"></i>
          </a>
        </div>
        <!--end::Small Box Widget 2-->
      </div>
      <!--end::Col-->
      <div class="col-lg-3 col-6">
        <!--begin::Small Box Widget 3-->
        <div class="small-box text-bg-warning">
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
        </div>
        <!--end::Small Box Widget 3-->
      </div>
      <!--end::Col-->
      <div class="col-lg-3 col-6">
        <!--begin::Small Box Widget 4-->
        <div class="small-box text-bg-danger">
          <div class="inner">
            <h3>65</h3>
            <p>Unique Visitors</p>
          </div>
          <svg
            class="small-box-icon"
            fill="currentColor"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
            aria-hidden="true">
            <path
              clip-rule="evenodd"
              fill-rule="evenodd"
              d="M2.25 13.5a8.25 8.25 0 018.25-8.25.75.75 0 01.75.75v6.75H18a.75.75 0 01.75.75 8.25 8.25 0 01-16.5 0z"></path>
            <path
              clip-rule="evenodd"
              fill-rule="evenodd"
              d="M12.75 3a.75.75 0 01.75-.75 8.25 8.25 0 018.25 8.25.75.75 0 01-.75.75h-7.5a.75.75 0 01-.75-.75V3z"></path>
          </svg>
          <a
            href="#"
            class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
            More info <i class="bi bi-link-45deg"></i>
          </a>
        </div>
        <!--end::Small Box Widget 4-->
      </div>
      <div class="col-lg-3">
        <div class="card shadow-sm border-0 bg-white rounded-3">
          <div class="card-header bg-primary text-white py-2 px-3">
            <strong>Evacuation Locations</strong>
          </div>
          <div class="card-body p-3" style="max-height: 500px; overflow-y: auto;">
            <!-- Search box -->
            <div class="input-group mb-3">
              <input type="text" class="form-control" id="evacSearch" placeholder="Search location...">
              <span class="input-group-text"><i class="bi bi-search"></i></span>
            </div>

            <!-- Evacuation list -->
            <ul class="list-group list-group-flush" id="evacuationList">
              <?php
              $query = "
                    SELECT 
                      elt.name, 
                      elt.latitude, 
                      elt.longitude, 
                      COUNT(rt.evac_loc_id) AS room_count
                    FROM room_table AS rt
                    LEFT JOIN evac_loc_table AS elt ON rt.evac_loc_id = elt.evac_loc_id
                    GROUP BY elt.evac_loc_id, elt.name, elt.latitude, elt.longitude
                  ";
              $result = mysqli_query($conn, $query);
              while ($row = mysqli_fetch_assoc($result)):
                $name = htmlspecialchars($row['name']);
                $roomCount = htmlspecialchars($row['room_count']);
                $lat = htmlspecialchars($row['latitude']);
                $lng = htmlspecialchars($row['longitude']);
              ?>
                <li class="list-group-item d-flex justify-content-between align-items-start evacuation-item"
                  data-lat="<?= $lat ?>"
                  data-lng="<?= $lng ?>">
                  <div>
                    <div class="fw-semibold"><?= $name ?></div>
                    <small><?= $roomCount ?> room(s)</small>
                  </div>
                  <button class="btn btn-sm btn-outline-primary show-on-map">Show</button>
                </li>
              <?php endwhile; ?>
            </ul>
          </div>
        </div>
      </div>
    </div>
    <!--end::Container-->
  </div>

  <style>
    .room-box-container {
      display: flex;
      flex-wrap: wrap;
      gap: 1rem;
    }

    .room-box {
      background-color: #f1f5f9;
      border-radius: 10px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
      padding: 1rem;
      width: 220px;
      text-align: center;
      transition: 0.3s ease;
    }

    .room-box.available {
      background-color: #dcfce7;
    }

    .room-box.full {
      background-color: #fee2e2;
    }

    .room-name {
      font-weight: bold;
      font-size: 1.1rem;
      margin-bottom: 0.5rem;
    }

    .room-capacity {
      font-size: 0.95rem;
      margin-bottom: 0.5rem;
    }

    .room-status {
      font-size: 0.9rem;
      font-weight: 600;
    }
  </style>
  <!-- Leaflet CSS -->


  <style>
    .app-content {
      position: relative;
      padding: 20px;
    }

    #evacMapContainer {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 500px;
      margin: 0 auto;
      z-index: 1;
      border: 3px solid #ccc;
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    #evacMap {
      width: 100%;
      height: 100%;
    }

    .small-box {
      background-color: #fff;
      border-radius: 10px;
      padding: 15px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      position: relative;
      z-index: 2;
    }

    /* small boxes */
    .small-box {
      padding: 10px 12px;
      min-height: auto;
      border-radius: 8px;
      font-size: 13px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
      background-color: #ffffff !important;
      /* white background */
      color: #333;
      position: relative;
    }

    .small-box .inner h3 {
      font-size: 20px;
      margin: 0 0 4px 0;
      font-weight: 600;
    }

    .small-box .inner p {
      font-size: 13px;
      margin: 0;
      color: #666;
    }

    .small-box-icon {
      position: absolute;
      top: 10px;
      right: 12px;
      width: 22px;
      height: 22px;
      opacity: 0.15;
    }

    .small-box-footer {
      font-size: 12px;
      margin-top: 6px;
      display: inline-block;
    }
  </style>


  <!-- SEARCH THE LOCATION -->
  <script>
    $(document).ready(function() {
      $('#evacSearch').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase().trim();

        $('#evacuationList .evacuation-item').each(function() {
          const text = $(this).text().toLowerCase();

          if (text.includes(searchTerm)) {
            $(this).stop(true, true).fadeIn(150);
          } else {
            $(this).stop(true, true).fadeOut(150);
          }
        });
      });

      // Show on map button
      $('#evacuationList').on('click', '.show-on-map', function() {
        const item = $(this).closest('.evacuation-item');
        const lat = parseFloat(item.data('lat'));
        const lng = parseFloat(item.data('lng'));
        const label = item.find('.fw-semibold').text();

        if (window.evacMap && lat && lng) {
          evacMap.setView([lat, lng], 17);
          L.popup()
            .setLatLng([lat, lng])
            .setContent(label)
            .openOn(evacMap);
        }
      });
    });
  </script>



  <!-- Leaflet JS -->


  <script>
    const map = L.map('evacMap').setView([10.485, 122.83], 13); // Change coords to your area

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Example: Static evacuation locations (replace with dynamic PHP data if needed)
    const evacLocations = [{
        name: "Evac Center 1",
        lat: 10.486,
        lng: 122.831
      },
      {
        name: "Evac Center 2",
        lat: 10.489,
        lng: 122.829
      },
      {
        name: "Evac Center 3",
        lat: 10.483,
        lng: 122.827
      }
    ];

    evacLocations.forEach(loc => {
      L.marker([loc.lat, loc.lng]).addTo(map).bindPopup(loc.name);
    });
  </script>