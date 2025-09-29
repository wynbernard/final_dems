<?php
include '../../../database/session.php';
include '../layout/head_links.php';

// Fetch barangay disaster records
$query = "
 SELECT brgy_record_id, barangay_name, total_evacuess, disaster_table.disaster_name as disaster_name, scale, brgy_record_table.date as date
 FROM brgy_record_table
 LEFT JOIN disaster_table ON brgy_record_table.disaster_id = disaster_table.disaster_id
 ORDER BY date ASC
";
$result = mysqli_query($conn, $query);

// Build unified dataset
$allDates = [];
$dataByBarangay = [];

while ($row = mysqli_fetch_assoc($result)) {
  $barangay = $row['barangay_name'];
  $date = $row['date'];

  $allDates[] = $date; // collect all dates
  $dataByBarangay[$barangay][$date] = (int)$row['total_evacuess'];
}

// make dates unique & sorted
$allDates = array_values(array_unique($allDates));
sort($allDates);

// prepare data arrays (fill missing dates with null/0)
$barangayDatasets = [];
foreach ($dataByBarangay as $barangay => $records) {
  $series = [];
  foreach ($allDates as $date) {
    $series[] = isset($records[$date]) ? $records[$date] : null; // null to break the line
  }
  $barangayDatasets[] = [
    'label' => $barangay,
    'data' => $series
  ];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Barangay Record</title>
  <?php include '../layout/head_links.php'; ?>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
  <div class="app-wrapper">
    <?php
    include '../layout/header.php';
    include '../layout/sidebar.php';
    include '../alert/warning.php';
    ?>
    <main class="app-main">
      <div class="app-content-header">
        <div class="container-fluid">
          <div class="row">
            <div class="col-sm-6 d-flex align-items-center gap-2">
              <i class="bi bi-graph-up fs-2 text-primary"></i>
              <h3 class="mb-0">Barangay Evacuation Trends</h3>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Barangay Record</li>
              </ol>
            </div>
          </div>
        </div>
      </div>

      <div class="content">
        <div class="row">
          <div class="col-md-12">
            <div class="card">
              <div class="card-body">
                <div class="mb-3 d-flex align-items-center gap-2">
                  <label for="barangaySelect" class="mb-0 fw-semibold">Barangay:</label>
                  <select id="barangaySelect" class="form-select w-auto">
                    <option value="__all__">All Barangays</option>
                    <?php
                    // build a list of barangays for the dropdown
                    $barangayList = array_keys($dataByBarangay);
                    foreach ($barangayList as $bname) {
                      echo '<option value="' . htmlspecialchars($bname) . '">' . htmlspecialchars($bname) . '</option>';
                    }
                    ?>
                  </select>
                </div>
                <canvas id="evacuationChart" style="height: 400px;"></canvas>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
    <?php include '../layout/footer.php'; ?>
  </div>

  <script>
    const ctx = document.getElementById('evacuationChart').getContext('2d');

    // Dates from PHP
    const labels = <?php echo json_encode($allDates); ?>;

    // Barangay datasets
    const rawDatasets = <?php echo json_encode($barangayDatasets); ?>;

    function makeChartDatasets(list) {
      return list.map(ds => ({
        label: ds.label,
        data: ds.data,
        borderColor: `hsl(${Math.floor(Math.random() * 360)}, 70%, 50%)`,
        backgroundColor: `hsl(${Math.floor(Math.random() * 360)}, 70%, 70%)`,
        borderWidth: 2,
        fill: false,
        tension: 0.3,
        spanGaps: true,
        pointRadius: 3
      }));
    }

    // keep a copy of the original labels (full union of dates)
    const originalLabels = labels.slice();

    let chart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: makeChartDatasets(rawDatasets)
      },
      options: {
        responsive: true,
        plugins: {
          title: {
            display: true,
            text: 'Total Evacuees per Barangay (Timeline)'
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                return context.dataset.label + ": " + context.formattedValue + " evacuees";
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });

    // Dropdown filter logic
    const select = document.getElementById('barangaySelect');
    select.addEventListener('change', function() {
      const val = this.value;
      if (val === '__all__') {
        chart.data.labels = originalLabels.slice();
        chart.data.datasets = makeChartDatasets(rawDatasets);
      } else {
        const filtered = rawDatasets.filter(d => d.label === val);
        // compute labels where this dataset has non-null values so the latest point appears at the end
        const ds = filtered.length ? filtered[0] : null;
        if (ds) {
          const newLabels = [];
          const newData = [];
          for (let i = 0; i < ds.data.length; i++) {
            if (ds.data[i] !== null && ds.data[i] !== undefined) {
              newLabels.push(originalLabels[i]);
              newData.push(ds.data[i]);
            }
          }
          chart.data.labels = newLabels;
          // create a single dataset with same styling but only the trimmed data
          chart.data.datasets = [{
            label: ds.label,
            data: newData,
            borderColor: `hsl(${Math.floor(Math.random() * 360)}, 70%, 50%)`,
            backgroundColor: `hsl(${Math.floor(Math.random() * 360)}, 70%, 70%)`,
            borderWidth: 2,
            fill: false,
            tension: 0.3,
            spanGaps: true,
            pointRadius: 3
          }];
        }
      }
      chart.update();
    });
  </script>
</body>

</html>