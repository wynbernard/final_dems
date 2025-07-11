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
        <div class="small-box text-bg-warning">
          <div class="inner">
            <?php
             $query = "SELECT COUNT(*) AS evac_reg FROM evac_reg_table";
            $result = mysqli_query($conn, $query);
            $row = mysqli_fetch_assoc($result);
            $total_evac_reg = $row['evac_reg'];
            ?>
            <h3><?php echo htmlspecialchars($total_evac_reg)?></h3>
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