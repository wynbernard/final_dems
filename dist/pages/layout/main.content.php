<!--begin::App Content Header-->
<div class="app-content-header">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Row-->
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">
          <i class="bi bi-speedometer2 me-2"></i> Admin Dashboard
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="#">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
        </ol>
      </div>
    </div>
    <!-- Pre-Registration Analytics -->
    <?php
    // Evacuation statistics from evacuation_record_table
    $analyticsTotal = 0;
    $analyticsToday = 0;
    $analyticsTrend = [];
    $analyticsLabels = [];
    // Total evacuations (sum of total_evacuation)
    $analyticsQuery = "SELECT SUM(total_evacuation) AS total FROM evacuation_record_table";
    $analyticsResult = mysqli_query($conn, $analyticsQuery);
    if ($analyticsResult) {
      $analyticsTotal = (int)mysqli_fetch_assoc($analyticsResult)['total'];
    }
    // New today (sum of total_evacuation for today)
    $today = date('Y-m-d');
    $analyticsTodayQuery = "SELECT SUM(total_evacuation) AS today FROM evacuation_record_table WHERE DATE(start_date) = '$today'";
    $analyticsTodayResult = mysqli_query($conn, $analyticsTodayQuery);
    if ($analyticsTodayResult) {
      $analyticsToday = (int)mysqli_fetch_assoc($analyticsTodayResult)['today'];
    }
    // Trend: group by start_date and end_date, sum total_evacuation for each unique pair (last 7 unique pairs)
    $trendQuery = "SELECT start_date, end_date, SUM(total_evacuation) as total_evacuation FROM evacuation_record_table GROUP BY start_date, end_date ORDER BY start_date DESC LIMIT 7";
    $trendResult = mysqli_query($conn, $trendQuery);
    $analyticsLabels = [];
    $analyticsTrend = [];
    if ($trendResult) {
      $trendRows = [];
      while ($row = mysqli_fetch_assoc($trendResult)) {
        $trendRows[] = $row;
      }
      // Reverse to show oldest to newest
      $trendRows = array_reverse($trendRows);
      foreach ($trendRows as $row) {
        $label = date('M d', strtotime($row['start_date'])) . ' - ' . date('M d', strtotime($row['end_date']));
        $analyticsLabels[] = $label;
        $analyticsTrend[] = (int)$row['total_evacuation'];
      }
    }

    // --- Predictive Trend (Simple Linear Regression) ---
    // Predict next 3 records (not dates, just for continuity)
    $n = count($analyticsTrend);
    $x = range(1, $n);
    $y = $analyticsTrend;
    $sumX = array_sum($x);
    $sumY = array_sum($y);
    $sumXY = 0;
    $sumX2 = 0;
    for ($i = 0; $i < $n; $i++) {
      $sumXY += $x[$i] * $y[$i];
      $sumX2 += $x[$i] * $x[$i];
    }
    $denominator = ($n * $sumX2 - $sumX * $sumX);
    if ($denominator == 0 || $n == 0) {
      $slope = 0;
      $intercept = 0;
    } else {
      $slope = ($n * $sumXY - $sumX * $sumY) / $denominator;
      $intercept = ($sumY - $slope * $sumX) / $n;
    }
    $predictLabels = [];
    $predictData = [];
    for ($i = 1; $i <= 3; $i++) {
      $predictLabels[] = 'Next #' . $i;
      $predictData[] = round($slope * ($n + $i) + $intercept, 2);
    }
    ?>
    <div class="row mb-4">
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
            <h3 style="color:#333"><?php echo htmlspecialchars($total_admin) ?></h3>
            <p>Total Staff</p>
          </div>
          <svg
            class="small-box-icon"
            style="color: #00000080; width: 50px; height: 50px; position: absolute; top: 15px; right: 15px;"
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
            class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover bg-primary">
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
            <h3 class="text-dark mb-1"><?php echo htmlspecialchars($total_pre_reg); ?><sup class="fs-5"></sup></h3>
            <p>Pre-Registration</p>
          </div>

          <!-- Pre-Registration (User Check) Icon -->
          <svg
            class="small-box-icon"
            style="color: #00000080; width: 50px; height: 50px; position: absolute; top: 15px; right: 15px;"
            fill="currentColor"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
            aria-hidden="true">
            <path d="M16.5 8.25a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM3.375 20.25a7.125 7.125 0 0114.25 0v.375a.375.375 0 01-.375.375H3.75a.375.375 0 01-.375-.375v-.375zM18.53 15.47a.75.75 0 00-1.06 1.06l1.47 1.47-1.47 1.47a.75.75 0 101.06 1.06L20.25 19l1.47 1.47a.75.75 0 101.06-1.06L21.31 18l1.47-1.47a.75.75 0 00-1.06-1.06L20.25 16.94l-1.47-1.47z" />
          </svg>

          <a
            href="../admin_page/pre_reg.php"
            class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover bg-primary">
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
            <h3 style="color:#333"><?php echo htmlspecialchars($total_evac_reg) ?></h3>
            <p>Registered Evacuees</p>
          </div>
          <svg
            class="small-box-icon"
            style="color: #00000080; width: 50px; height: 50px; position: absolute; top: 15px; right: 15px;"
            fill="currentColor"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
            aria-hidden="true">
            <path
              d="M6.25 6.375a4.125 4.125 0 118.25 0 4.125 4.125 0 01-8.25 0zM3.25 19.125a7.125 7.125 0 0114.25 0v.003l-.001.119a.75.75 0 01-.363.63 13.067 13.067 0 01-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 01-.364-.63l-.001-.122zM19.75 7.5a.75.75 0 00-1.5 0v2.25H16a.75.75 0 000 1.5h2.25v2.25a.75.75 0 001.5 0v-2.25H22a.75.75 0 000-1.5h-2.25V7.5z"></path>
          </svg>
          <a
            href="../admin_page/idps_user.php"
            class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover bg-primary">
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
            <?php
            $query = "SELECT COUNT(*) AS total_locations FROM evac_loc_table";
            $result = mysqli_query($conn, $query);
            $row = mysqli_fetch_assoc($result);
            $total_locations = $row['total_locations'];
            ?>
            <h3 class="text-dark mb-1"><?php echo htmlspecialchars($total_locations); ?></h3>
            <p>Evacuation Locations</p>
          </div>
          <!-- Location Pin Icon -->
          <svg
            class="small-box-icon"
            style="color: #00000080; width: 50px; height: 50px; position: absolute; top: 15px; right: 15px;"
            fill="currentColor"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
            aria-hidden="true">
            <path
              fill-rule="evenodd"
              clip-rule="evenodd"
              d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5A2.5 2.5 0 1112 6a2.5 2.5 0 010 5.5z">
            </path>
          </svg>

          <a
            href="../admin_page/loc_management.php"
            class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover bg-primary">
            More info <i class="bi bi-link-45deg"></i>
          </a>
        </div>
        <!--end::Small Box Widget 4-->
      </div>
    </div>
    <!--end::Container-->
    <div class="col-lg-3 col-6">
    </div>

    <?php
    // --- Gender Breakdown ---
    $maleCount = 0;
    $femaleCount = 0;

    $genderQuery = "SELECT classification, COUNT(*) as count FROM evac_reg_table
          LEFT JOIN pre_reg_table ON evac_reg_table.pre_reg_id = pre_reg_table.pre_reg_id
          LEFT JOIN age_class_table ON pre_reg_table.pre_reg_id = age_class_table.age_class_id
          WHERE age_class_table.classification IS NOT NULL
          GROUP BY classification";
    $genderResult = mysqli_query($conn, $genderQuery);
    if ($genderResult) {
      while ($row = mysqli_fetch_assoc($genderResult)) {
        if (strtolower($row['gender']) == 'male') {
          $maleCount = $row['count'];
        } elseif (strtolower($row['gender']) == 'female') {
          $femaleCount = $row['count'];
        }
      }
    }
    $selectedSource = $_GET['source'] ?? 'pre';

    // Prepare static age classification labels
    $ageGroups = [];
    $staticAgeLabels = ['Infant', 'Child', 'Teen', 'Adult', 'Senior'];
    foreach ($staticAgeLabels as $label) {
      $ageGroups[$label] = 0;
    }


    // --- Age Classification Breakdown ---
    // Query based on selected source
    if ($selectedSource === 'evac') {
      $ageQuery = "
    SELECT act.classification, COUNT(*) as total
    FROM evac_reg_table ert
    LEFT JOIN pre_reg_table prt ON ert.pre_reg_id = prt.pre_reg_id
    LEFT JOIN age_class_table act ON prt.age_class_id = act.age_class_id
    GROUP BY act.classification
  ";
    } else {
      $ageQuery = "
    SELECT act.classification, COUNT(*) as total
    FROM pre_reg_table prt
    LEFT JOIN evac_reg_table ert ON prt.pre_reg_id = ert.pre_reg_id
    LEFT JOIN age_class_table act ON prt.age_class_id = act.age_class_id
    GROUP BY act.classification
  ";
    }

    $ageResult = mysqli_query($conn, $ageQuery);
    if ($ageResult) {
      while ($row = mysqli_fetch_assoc($ageResult)) {
        $classification = $row['classification'];
        $total = (int)$row['total'];
        $ageGroups[$classification] = $total;
      }
    }
    // Count solo evacuees with solo_address_id > 0
    $soloQuery = "SELECT COUNT(*) AS solo_count FROM pre_reg_table WHERE registered_as = 'Solo'";
    $soloResult = mysqli_query($conn, $soloQuery);
    $soloCount = ($soloResult) ? (int)mysqli_fetch_assoc($soloResult)['solo_count'] : 0;

    // Count family evacuees with family_id > 0
    $familyQuery = "SELECT COUNT(*) AS family_count FROM pre_reg_table WHERE registered_as = 'Family'";
    $familyResult = mysqli_query($conn, $familyQuery);
    $familyCount = ($familyResult) ? (int)mysqli_fetch_assoc($familyResult)['family_count'] : 0;
    ?>



<!-- EVACUATION LOCATION -->
<!-- MAP SECTION -->

<div class="card-body position-relative d-flex" style="height: 500px;">
  <div id="evacMap" style="flex: 1 1 auto; height: 100%; position: relative;"></div>
  <!-- Overlay for details and chart at the right side of the map area -->
  <div id="evacDetails" class="bg-white shadow border rounded p-3" 
       style="position: absolute; top: 50%; right: 0; transform: translateY(-50%); min-width: 400px; max-width: 90vw; display: none; z-index: 2000; box-shadow: 0 4px 24px rgba(0,0,0,0.15);">
    <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 24px; width: 100%;">
      <div id="evacInfoContent" style="width: 100%; display: flex; align-items: center; gap: 12px; flex-wrap: wrap;"></div>
      <div id="evacStatsGraph" style="width: 100%; min-width: 320px; height: 350px;"></div>
    </div>
    <button class="btn btn-sm btn-outline-danger mt-2" style="margin-left: auto; display: block;" onclick="closeEvacDetails()">Close</button>
  </div>
</div>



<?php
$query = "SELECT 
            evacuation_location AS evacuation_center, 
            start_date, 
            end_date, 
            total_evacuation AS total_evacuees,
            CASE 
              WHEN end_date IS NULL OR end_date = '0000-00-00 00:00:00' THEN 'Ongoing'
              ELSE 'Completed'
            END AS event_status
          FROM evacuation_record_table 
          ORDER BY start_date ASC 
          LIMIT 100";

$result = mysqli_query($conn, $query);

$analyticsByCenter = [];

while ($row = mysqli_fetch_assoc($result)) {
  $center = $row['evacuation_center'];
  $startDate = date('M d, Y', strtotime($row['start_date']));
  $count = (int) $row['total_evacuees'];
  $status = $row['event_status']; // 'Ongoing' or 'Completed'

  if (!isset($analyticsByCenter[$center])) {
    $analyticsByCenter[$center] = [
      'labels' => [],
      'completed' => [],
      'ongoing' => [],
    ];
  }

  $analyticsByCenter[$center]['labels'][] = $startDate;

  if ($status === 'Ongoing') {
    $analyticsByCenter[$center]['ongoing'][] = $count;
    $analyticsByCenter[$center]['completed'][] = null;
  } else {
    $analyticsByCenter[$center]['completed'][] = $count;
    $analyticsByCenter[$center]['ongoing'][] = null;
  }
}
?>

<script>
  const ongoingEvents = <?= json_encode($ongoingDebug) ?>;

  if (ongoingEvents.length > 0) {
    let alertMessage = "Ongoing Evacuation Events:\n\n";
    ongoingEvents.forEach(event => {
      alertMessage += `📍 ${event.center}\n🗓️ Started: ${event.start_date}\n👥 Evacuees: ${event.evacuees}\n\n`;
    });
    alert(alertMessage);
  }
</script>


<div class="row mt-4">
  <div class="col-12">
    <div class="card shadow-sm border-0">
      <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <span>Evacuation Statistics</span>
        <select id="evacuationCenterSelect" class="form-select w-auto">
          <?php foreach ($analyticsByCenter as $centerName => $data): ?>
            <option value="<?= htmlspecialchars($centerName) ?>">
              <?= htmlspecialchars($centerName) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="card-body">
        <canvas id="evacStatChart" height="100"></canvas>
        <p id="evacuationLocationText" class="text-muted text-center mt-3">
          Showing data for all evacuation centers.
        </p>
      </div>
    </div>
  </div>
</div>



    <!-- Age Group and Evacuee Type Charts -->
    <div class="row mt-4">
      <div class="col-lg-6">
        <!-- Navbar-style tabs for source selection -->
        <div class="d-flex justify-content-between align-items-center mb-2">
          <div>
            <h6 class="mb-1 fw-bold">Age Classification Source:</h6>
            <ul class="nav nav-pills custom-nav-pills">
              <li class="nav-item">
                <a class="nav-link <?= $selectedSource === 'pre' ? 'active' : '' ?>"
                  href="?source=pre">
                  <small>Pre-Registration</small>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link <?= $selectedSource === 'evac' ? 'active' : '' ?>"
                  href="?source=evac">
                  <small>Evacuation Registration</small>
                </a>
              </li>
            </ul>

          </div>
          <div>
            <span class="badge bg-primary">
              <?= $selectedSource === 'pre' ? 'Showing: Pre-Registration' : 'Showing: Evacuation Registration' ?>
            </span>
          </div>
        </div>
        <!-- Chart Card -->
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <canvas id="ageChart"></canvas>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="card shadow-sm border-0">
          <div class="card-header bg-warning text-dark">Evacuee Type</div>
          <div class="card-body text-center">
            <div style="max-width: 300px; margin: auto;">
              <canvas id="evacTypeChart"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>

<?php
include '../../../database/conn.php';

$evacMapLocations = [];
$evacStats = [];

// Get evacuation locations
$locQuery = "
  SELECT evac_loc_id, name, city, barangay_manegement_table.barangay_name AS barangay, purok, total_capacity, evc.longitude as longitude, evc.latitude as latitude
  FROM evac_loc_table as evc
  LEFT JOIN barangay_manegement_table ON evc.barangay_id = barangay_manegement_table.barangay_id
  WHERE evc.latitude IS NOT NULL AND evc.longitude IS NOT NULL
";
$locResult = mysqli_query($conn, $locQuery);

while ($row = mysqli_fetch_assoc($locResult)) {
  $evacMapLocations[] = [
    'id' => $row['evac_loc_id'],
    'name' => $row['name'],
    'city' => $row['city'],
    'barangay' => $row['barangay'],
    'purok' => $row['purok'],
    'capacity' => $row['total_capacity'],
    'lat' => (float)$row['latitude'],
    'lng' => (float)$row['longitude'],
  ];
}

// Get evacuation records
$statsQuery = "
  SELECT evacuation_location, start_date, end_date, total_evacuation
  FROM evacuation_record_table
  WHERE start_date IS NOT NULL
  ORDER BY start_date ASC
";
$statsResult = mysqli_query($conn, $statsQuery);

while ($row = mysqli_fetch_assoc($statsResult)) {
  $locationName = $row['evacuation_location'];
  $evacStats[$locationName][] = [
    'date' => date('Y-m-d', strtotime($row['start_date'])),
    'evacuation' => (int)$row['total_evacuation'],
    'end' => ($row['end_date'] === '0000-00-00 00:00:00' || !$row['end_date']) ? 'Ongoing' : date('Y-m-d', strtotime($row['end_date']))
  ];
}
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const evacuationData = <?= json_encode($analyticsByCenter) ?>;
const ctx = document.getElementById('evacStatChart').getContext('2d');
const select = document.getElementById('evacuationCenterSelect');
const labelDisplay = document.getElementById('evacuationLocationText');

let evacChart;

function renderChart(centerName) {
  const centerData = evacuationData[centerName];
  const labels = centerData.labels;
  const completed = centerData.completed;
  const ongoing = centerData.ongoing;

  // Merge data into one array: pick non-null from completed or ongoing
  const mergedData = completed.map((val, i) => val !== null ? val : ongoing[i]);

  labelDisplay.textContent = `Showing data for: ${centerName}`;

  if (evacChart) evacChart.destroy();

  evacChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: labels,
      datasets: [
        {
          label: 'Evacuation Events',
          data: mergedData,
          borderColor: '#2980b9',
          backgroundColor: 'rgba(46, 204, 113, 0.1)',  // greenish fill below
          borderWidth: 3,
          tension: 0.4,
          fill: true,
          spanGaps: true,
          pointRadius: 6,
          pointHoverRadius: 8,
          pointBackgroundColor: (ctx) => {
            const index = ctx.dataIndex;
            return ongoing[index] !== null ? '#2ecc71' : '#3498db';
          },
          pointBorderColor: (ctx) => {
            const index = ctx.dataIndex;
            return ongoing[index] !== null ? '#27ae60' : '#2980b9';
          },
          pointStyle: (ctx) => {
            const index = ctx.dataIndex;
            return ongoing[index] !== null ? 'circle' : 'circle';
          }
        }
      ]
    },
    options: {
  responsive: true,
  plugins: {
    legend: {
      display: true,
      labels: {
        generateLabels: function(chart) {
          return [
            {
              text: 'Completed Event',
              fillStyle: '#3498db',
              strokeStyle: '#2980b9',
              pointStyle: 'circle',
              lineWidth: 2
            },
            {
              text: 'Ongoing Event',
              fillStyle: '#2ecc71',
              strokeStyle: '#27ae60',
              pointStyle: 'circle',
              lineWidth: 2
            }
          ];
        },
        usePointStyle: true,
        boxWidth: 12,
        font: { size: 13 }
      }
    },
    title: {
      display: true,
      text: 'Evacuation Statistics',
      color: '#2c3e50',
      font: { size: 20 }
    },
    tooltip: {
      callbacks: {
        label: ctx => `${ctx.dataset.label}: ${ctx.parsed.y} evacuees`
      }
    }
  },
  scales: {
    x: {
      ticks: {
        color: '#34495e',
        font: { size: 12 }
      },
      grid: { display: false }
    },
    y: {
      beginAtZero: true,
      ticks: {
        color: '#34495e',
        font: { size: 12 }
      },
      grid: {
        color: '#ecf0f1'
      }
    }
  }
}
  });
}

renderChart(select.value);
select.addEventListener('change', () => renderChart(select.value));
</script>



    <script>
      const ageLabels = <?= json_encode(array_keys($ageGroups)) ?>;
      const ageData = <?= json_encode(array_values($ageGroups)) ?>;

      const ageCtx = document.getElementById('ageChart').getContext('2d');
      new Chart(ageCtx, {
        type: 'bar',
        data: {
          labels: ageLabels,
          datasets: [{
            label: 'No. of Individuals',
            data: ageData,
            backgroundColor: ['#4dc9f6', '#f67019', '#f53794', '#537bc4', '#acc236'],
            borderRadius: 5
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              display: false
            },
            title: {
              display: true,
              text: 'Age Classification Breakdown'
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                stepSize: 1000,
                callback: function(value) {
                  return value.toLocaleString();
                }
              }
            }
          }
        }
      });
      // Solo vs Family Chart
      const evacCtx = document.getElementById('evacTypeChart').getContext('2d');
      new Chart(evacCtx, {
        type: 'pie',
        data: {
          labels: ['Solo', 'Family'],
          datasets: [{
            label: 'Evacuee Type',
            data: [<?php echo $soloCount; ?>, <?php echo $familyCount; ?>],
            backgroundColor: ['#9b59b6', '#2ecc71']
          }]
        }
      });
    </script>

<script>

  const evacLocations = <?= json_encode($evacMapLocations); ?>;
  const evacStats = <?= json_encode($evacStats); ?>;

  const map = L.map('evacMap');
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  maxZoom: 19,
  attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
}).addTo(map);

  // Collect all marker positions
  const markerPositions = [];
  evacLocations.forEach(loc => {
    const marker = L.marker([loc.lat, loc.lng]).addTo(map);
    markerPositions.push([loc.lat, loc.lng]);

    // Show popup with name on hover, but keep open if opened by click
    let popupOpenedByClick = false;
    marker.on('mouseover', function(e) {
      if (!popupOpenedByClick) {
        marker.bindPopup(`<b>${loc.name}</b>`, {closeButton: false, offset: L.point(0, -30)}).openPopup();
      }
    });
    marker.on('mouseout', function(e) {
      if (!popupOpenedByClick) {
        marker.closePopup();
      }
    });

    marker.on('click', () => {
      // Move the marker to the right side of the map (not centered), and zoom in
      const mapWidth = map.getSize().x;
      const overlayWidth = 400; // match min-width of #evacDetails
      const targetZoom = 15;
      const markerPoint = map.project([loc.lat, loc.lng], targetZoom);
      // Offset so marker is at the right side (visible area = mapWidth - overlayWidth)
      const offsetX = ((mapWidth - overlayWidth) / 2) - 40; // 40px padding from right
      const newPoint = L.point(markerPoint.x + offsetX, markerPoint.y);
      const newLatLng = map.unproject(newPoint, targetZoom);
      map.setView(newLatLng, targetZoom); // Pan and zoom

      // Show popup/tag with the location name above the marker and keep it open
      popupOpenedByClick = true;
      marker.bindPopup(`<b>${loc.name}</b>`, {closeButton: false, offset: L.point(0, -30)}).openPopup();

      // Details as a single line at the top
      let html = `
        <h6 class="text-primary mb-0" style="font-size: 1.2rem;">${loc.name}</h6>
        <span><strong>City:</strong> ${loc.city}</span>
        <span><strong>Barangay:</strong> ${loc.barangay}</span>
        <span><strong>Purok:</strong> ${loc.purok}</span>
        <span><strong>Capacity:</strong> ${loc.capacity}</span>
      `;
      document.getElementById('evacInfoContent').innerHTML = html;
      document.getElementById('evacDetails').style.display = 'block';

      // Build chart data
      const stats = evacStats[loc.name] || [];
      const labels = stats.map(s => s.date);
      const data = stats.map(s => s.evacuation);

      // Move the graph below the details
      const graphDiv = document.getElementById('evacStatsGraph');
      graphDiv.parentNode.appendChild(graphDiv); // ensures it's after details
      renderEvacChart(labels, data, loc.name);
    });

    // Close popup if map is clicked elsewhere
    map.on('click', function(e) {
      popupOpenedByClick = false;
      marker.closePopup();
    });
  });

  // Center and fit map to all markers
  if (markerPositions.length > 0) {
    const bounds = L.latLngBounds(markerPositions);
    map.fitBounds(bounds, { padding: [30, 30] });
  } else {
    map.setView([10.3157, 123.8854], 10);
  }

  function renderEvacChart(labels, data, locationName) {
    const ctxId = 'evacStatsChart';
    document.getElementById('evacStatsGraph').innerHTML = `<canvas id="${ctxId}" style="height: 350px; width: 100%"></canvas>`;

    // Find ongoing event indices (where evacStats[locationName][i].end === 'Ongoing')
    const statsArr = evacStats[locationName] || [];
    const ongoingIndices = statsArr.map((s, i) => s.end === 'Ongoing' ? i : -1).filter(i => i !== -1);

    new Chart(document.getElementById(ctxId), {
      type: 'line',
      data: {
        labels,
        datasets: [{
          label: 'Total Evacuated',
          data,
          fill: true,
          borderColor: '#2980b9',
          backgroundColor: 'rgba(46, 204, 113, 0.15)', // light green fill
          tension: 0.3,
          pointBackgroundColor: labels.map((_, i) => ongoingIndices.includes(i) ? '#e67e22' : '#2980b9'),
          pointBorderColor: labels.map((_, i) => ongoingIndices.includes(i) ? '#d35400' : '#2980b9'),
          pointRadius: labels.map((_, i) => ongoingIndices.includes(i) ? 9 : 6),
          pointStyle: labels.map((_, i) => ongoingIndices.includes(i) ? 'rectRot' : 'circle'),
        }]
      },
      options: {
        responsive: true,
        plugins: {
          title: {
            display: true,
            text: `Evacuation Trend - ${locationName}`
          },
          legend: {
            display: true,
            labels: {
              generateLabels: function(chart) {
                return [
                  {
                    text: 'Ongoing Event',
                    fillStyle: '#e67e22',
                    strokeStyle: '#d35400',
                    pointStyle: 'rectRot',
                    lineWidth: 2
                  },
                  {
                    text: 'Completed Event',
                    fillStyle: '#2980b9',
                    strokeStyle: '#2980b9',
                    pointStyle: 'circle',
                    lineWidth: 2
                  }
                ];
              },
              usePointStyle: true,
              boxWidth: 12,
              font: { size: 13 }
            }
          },
          tooltip: {
            callbacks: {
              label: function(ctx) {
                let label = `${ctx.dataset.label}: ${ctx.parsed.y} evacuees`;
                if (ongoingIndices.includes(ctx.dataIndex)) label += ' (Ongoing)';
                return label;
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: { color: '#ecf0f1' },
            ticks: { color: '#34495e', font: { size: 12 } }
          },
          x: {
            grid: { display: false },
            ticks: { color: '#34495e', font: { size: 12 } }
          }
        }
      }
    });
  }

  function closeEvacDetails() {
    document.getElementById('evacDetails').style.display = 'none';
    // Re-center and fit map to all markers
    if (markerPositions && markerPositions.length > 0) {
      const bounds = L.latLngBounds(markerPositions);
      map.fitBounds(bounds, { padding: [30, 30] });
    } else {
      map.setView([10.3157, 123.8854], 10);
    }
  }
</script>



    <style>
      .room-box-container {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
      }

      .room-box {
        border-radius: 10px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        padding: 1rem;
        width: 220px;
        text-align: center;
        transition: 0.3s ease;
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

    <style>
      .small-box {
        background: linear-gradient(135deg, #ffffff, #f8f9fa);
        border: 1px solid #e5e7eb;
        padding: 20px 24px;
        color: #2f2f2f;
        position: relative;
        font-size: 14px;
        transition: all 0.3s ease;
        overflow: hidden;
        border-radius: 12px;
        /* ✅ Rounded corners */
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        /* ✅ black shadow */
      }

      .small-box:hover {
        box-shadow: 0 14px 28px rgba(0, 0, 0, 0.2);
        transform: translateY(-2px);
      }

      .small-box .inner h3 {
        font-size: 32px;
        font-weight: 800;
        color: #1f1f1f;
        margin: 0 0 6px 0;
        line-height: 1.2;
      }

      .small-box .inner p {
        font-size: 15px;
        color: #6b7280;
        margin: 0;
        font-weight: 500;
      }

      .small-box-icon {
        position: absolute;
        top: 16px;
        right: 16px;
        width: 56px;
        height: 56px;
        color: rgba(0, 0, 0, 0.08);
        opacity: 1;
      }

      .small-box-footer {
        display: inline-block;
        margin-top: 16px;
        font-size: 13px;
        font-weight: 600;
        color: #0d6efd;
        text-decoration: none;
        position: relative;
        padding-right: 18px;
      }

      .small-box-footer::after {
        content: '→';
        position: absolute;
        right: 0;
        top: 0;
        transition: transform 0.2s;
      } 

      .small-box-footer:hover::after {
        transform: translateX(4px);
      }
    </style>

    <style>
      .custom-nav-pills .nav-link {
        color: #495057;
        background-color: transparent;
        border: 1px solid transparent;
        border-radius: 0.25rem;
        padding: 4px 10px;
        margin-right: 5px;
        transition: all 0.2s ease-in-out;
      }

      .custom-nav-pills .nav-link.active {
        color: #0d6efd;
        background-color: #e7f1ff;
        /* light blue background */
        border: 1px solid #b6d4fe;
        /* subtle blue border */
        font-weight: 500;
      }

      .custom-nav-pills .nav-link:hover {
        background-color: #f0f8ff;
        color: #0a58ca;
      }
    </style>
  </div>
  <!--end::Container-->
</div>
<!--end::App Content-->
 