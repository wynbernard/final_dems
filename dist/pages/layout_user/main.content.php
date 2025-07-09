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
      <div class="col-lg-3 col-md-6 col-12 mb-4">
        <!-- Reservation Box -->
        <div class="small-box text-bg-success shadow-lg rounded">
          <div class="inner" style="padding: 20px; word-wrap: break-word;">
            <?php
            $user_reservation_query = "
                SELECT 
                    r.room_id,
                    r.room_name,
                    l.name AS location_name,
                    e.status
                FROM room_reservation_table e
                JOIN room_table r ON e.room_id = r.room_id
                JOIN evac_loc_table l ON r.evac_loc_id = l.evac_loc_id
                WHERE e.pre_reg_id = ?
                LIMIT 1
            ";
            $user_stmt = $conn->prepare($user_reservation_query);
            $user_stmt->bind_param("i", $_SESSION['pre_reg_id']);
            $user_stmt->execute();
            $user_result = $user_stmt->get_result();
            $user_reservation = $user_result->fetch_assoc();

            if ($user_reservation) { // Check if a reservation exists
            ?>
              <h4 class="font-weight-bold" style="color: #fff;">Reservation: <?= htmlspecialchars($user_reservation['room_name']) ?></h4>
              <p class="text-light" style="font-size: 16px;">Location: <?= htmlspecialchars($user_reservation['location_name']) ?></p>
              <p class="text-light" style="font-size: 16px;">Status: <?= htmlspecialchars($user_reservation['status']) ?></p>
            <?php
            } else {
            ?>
              <h4 class="font-weight-bold" style="color: #fff;">No Reservation</h4>
              <p class="text-light" style="font-size: 16px;">You have not made a reservation yet.</p>
            <?php
            }
            ?>
          </div>
          <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24" style="opacity: 0.7; width: 30px; height: 30px;">
            <path
              d="M3 5a2 2 0 012-2h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5zm2 0v14h14V5H5zm3 4h8v2H8V9zm0 4h5v2H8v-2z">
            </path>
          </svg>
          <a href="./room_reservation.php" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover" style="background-color: #28a745; color: #fff; border-radius: 0 0 8px 8px; padding: 10px 20px; text-align: center; display: block;">
            View Details <i class="bi bi-arrow-right"></i>
          </a>
        </div>
      </div>

      <!--end::Col-->

      <!--begin::Col-->
      <div class="col-lg-5 col-md-6 col-12 mb-4">
        <div class="card text-white bg-danger bg-gradient border-danger mb-4 shadow-lg rounded">
          <div class="card-header border-0">
            <h3 class="card-title" id="report-title" style="font-size: 1.25rem; font-weight: bold; color: #fff;">Phenomenal Report</h3>
            <div class="card-tools">
              <button type="button" class="btn btn-light btn-sm" data-lte-toggle="card-collapse" style="border-radius: 5px;">
                <i data-lte-icon="expand" class="bi bi-plus-lg"></i>
                <i data-lte-icon="collapse" class="bi bi-dash-lg"></i>
              </button>
            </div>
          </div>
          <div class="card-body" id="typhoon-info" style="padding: 20px; font-size: 16px; line-height: 1.5; color: #f0f0f0;">
            <p>Loading Phenomenal Report...</p>
          </div>
          <div class="card-footer border-0 text-center" style="background-color: #c62828; padding: 12px; border-radius: 0 0 8px 8px;">
            <small id="typhoon-updated" style="color: #fff;">Loading...</small>
          </div>
        </div>
      </div>
    </div>
    <!-- /.row (main row) -->
  </div>
  <!--end::Container-->
</div>
<!--end::App Content-->

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