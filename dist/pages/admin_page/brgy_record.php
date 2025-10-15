<?php
include '../../../database/session.php';
include '../layout/head_links.php';

// Fetch barangay disaster records
$query = "
 SELECT brgy_record_id, barangay_name, total_evacuees, disaster_table.disaster_name as disaster_name, scale, brgy_record_table.date as date
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
  $dataByBarangay[$barangay][$date] = (int)$row['total_evacuees'];
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

// Fetch forecast data from brgy_forecasts table
$forecastQuery = "
    SELECT 
        barangay_name,
        scale_range,
        forecast,
        lower_bound,
        upper_bound,
        accuracy_percentage,
        date,
        created_at
    FROM brgy_forecasts 
    ORDER BY barangay_name, scale_range, created_at DESC
";
$forecastResult = mysqli_query($conn, $forecastQuery);

// Group forecast data by barangay
$forecastData = [];
while ($row = mysqli_fetch_assoc($forecastResult)) {
    $barangay = $row['barangay_name'];
    $scale = $row['scale_range'];
    
    if (!isset($forecastData[$barangay])) {
        $forecastData[$barangay] = [];
    }
    
    $forecastData[$barangay][$scale] = [
        'forecast' => (float)$row['forecast'],
        'lower_bound' => (float)$row['lower_bound'],
        'upper_bound' => (float)$row['upper_bound'],
        'accuracy' => (float)$row['accuracy_percentage']
    ];
}

// Add forecast dates to extend the timeline
$forecastDates = [];
$lastHistoricalDate = end($allDates);
$lastDate = new DateTime($lastHistoricalDate);
for ($i = 1; $i <= 7; $i++) {
    $lastDate->add(new DateInterval('P1D'));
    $forecastDates[] = $lastDate->format('Y-m-d');
}

// Combine historical and forecast dates
$combinedDates = array_merge($allDates, $forecastDates);
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
    const historicalLabels = <?php echo json_encode($allDates); ?>;
    const combinedLabels = <?php echo json_encode($combinedDates); ?>;
    const forecastData = <?php echo json_encode($forecastData); ?>;

    // Barangay datasets
    const rawDatasets = <?php echo json_encode($barangayDatasets); ?>;

    // Find the index where historical data ends
    const historicalEndIndex = historicalLabels.length - 1;

    function makeChartDatasets(list, selectedBarangay = '__all__') {
      const datasets = [];
      
      list.forEach(ds => {
        if (selectedBarangay !== '__all__' && ds.label !== selectedBarangay) return;
        
        // Create extended data array with nulls for forecast period
        const extendedData = [...ds.data];
        for (let i = ds.data.length; i < combinedLabels.length; i++) {
          extendedData.push(null);
        }
        
        // Historical data line (blue)
        datasets.push({
          label: ds.label,
          data: extendedData,
          borderColor: '#007bff',
          backgroundColor: '#007bff',
          borderWidth: 2,
          fill: false,
          tension: 0.3,
          spanGaps: false,
          pointRadius: 3,
          pointHoverRadius: 5
        });
        
        // Add predictive line if forecast data exists
        if (forecastData[ds.label] && forecastData[ds.label]['4-7']) {
          const forecast = forecastData[ds.label]['4-7'];
          const lastHistoricalValue = ds.data[ds.data.length - 1];
          
          if (lastHistoricalValue !== null && lastHistoricalValue !== undefined) {
            // Create predictive data array
            const predictiveData = [...ds.data];
            
            // Add forecast values for the next 7 days
            for (let i = 0; i < 7; i++) {
              const forecastValue = forecast.forecast + (Math.random() - 0.5) * 5; // Add some variation
              predictiveData.push(Math.max(0, forecastValue));
            }
            
            // Predictive line (orange/red color)
            datasets.push({
              label: ds.label + ' (Predicted)',
              data: predictiveData,
              borderColor: '#ff6b35', // Orange-red color
              backgroundColor: '#ff6b35',
              borderWidth: 2,
              borderDash: [5, 5], // Dashed line
              fill: false,
              tension: 0.3,
              spanGaps: false,
              pointRadius: 3,
              pointHoverRadius: 5
            });
          }
        }
      });
      
      return datasets;
    }

    // keep a copy of the original labels (full union of dates)
    const originalLabels = combinedLabels.slice();

    let chart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: combinedLabels,
        datasets: makeChartDatasets(rawDatasets)
      },
      options: {
        responsive: true,
        plugins: {
          title: {
            display: true,
            text: 'Total Evacuees per Barangay with Predictions'
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                const dataset = context.dataset;
                const value = context.formattedValue;
                const isForecast = context.dataIndex > historicalEndIndex;
                
                if (dataset.label.includes('Predicted')) {
                  const accuracy = forecastData[dataset.label.split(' (')[0]]?.['4-7']?.accuracy;
                  return `${dataset.label}: ${value} evacuees (Accuracy: ${accuracy?.toFixed(1)}%)`;
                }
                return `${dataset.label}: ${value} evacuees`;
              }
            }
          },
          legend: {
            display: true,
            position: 'top',
            labels: {
              usePointStyle: true,
              padding: 20
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: 'Number of Evacuees'
            }
          },
          x: {
            title: {
              display: true,
              text: 'Date'
            }
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
        const ds = filtered.length ? filtered[0] : null;
        if (ds) {
          const newLabels = [];
          const newData = [];
          for (let i = 0; i < ds.data.length; i++) {
            if (ds.data[i] !== null && ds.data[i] !== undefined) {
              newLabels.push(historicalLabels[i]);
              newData.push(ds.data[i]);
            }
          }
          chart.data.labels = newLabels;
          chart.data.datasets = makeChartDatasets([{label: ds.label, data: newData}], val);
        }
      }
      chart.update();
    });
  </script>
</body>

</html>