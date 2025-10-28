<?php
include '../../../database/session.php';
include '../layout/head_links.php';

// Fetch barangay disaster records (include disaster id so we can aggregate by disaster)
$query = "
 SELECT brgy_record_id, brgy_record_table.disaster_id as disaster_id, barangay_name, total_evacuess, disaster_table.disaster_name as disaster_name, scale, brgy_record_table.date as date
 FROM brgy_record_table
 LEFT JOIN disaster_table ON brgy_record_table.disaster_id = disaster_table.disaster_id
 ORDER BY date ASC
";
$result = mysqli_query($conn, $query);

// Build unified dataset
$allDates = [];
$dataByBarangay = [];
$sumByDisasterDate = []; // disaster_id => date => sum
$disasterList = []; // disaster_id => disaster_name

while ($row = mysqli_fetch_assoc($result)) {
  $barangay = $row['barangay_name'];
  $date = $row['date'];
  $disasterId = $row['disaster_id'];
  $disasterName = $row['disaster_name'];

  $allDates[] = $date; // collect all dates
  $dataByBarangay[$barangay][$date] = (int)$row['total_evacuess'];

  // aggregate sums by disaster and date
  if (!empty($disasterId)) {
    $disasterList[$disasterId] = $disasterName ?: ('Disaster ' . $disasterId);
    if (!isset($sumByDisasterDate[$disasterId])) $sumByDisasterDate[$disasterId] = [];
    if (!isset($sumByDisasterDate[$disasterId][$date])) $sumByDisasterDate[$disasterId][$date] = 0;
    $sumByDisasterDate[$disasterId][$date] += (int)$row['total_evacuess'];
  }
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

// Build aggregated datasets per disaster (sums across all barangays for each date)
$disasterDatasets = [];
foreach ($sumByDisasterDate as $dId => $dateSums) {
  $series = [];
  foreach ($allDates as $date) {
    $series[] = isset($dateSums[$date]) ? (int)$dateSums[$date] : null;
  }
  $disasterDatasets[] = [
    'label' => ($disasterList[$dId] ?? $dId),
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
    'lower_bound' => isset($row['lower_bound']) ? (float)$row['lower_bound'] : null,
    'upper_bound' => isset($row['upper_bound']) ? (float)$row['upper_bound'] : null,
    'accuracy' => isset($row['accuracy_percentage']) ? (float)$row['accuracy_percentage'] : null,
    'date' => isset($row['date']) ? $row['date'] : null,
    'period' => isset($row['period']) ? $row['period'] : null
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
  <style>
    .chart-container {
      background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
      border-radius: 12px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      padding: 20px;
      margin: 20px 0;
    }
    
    .chart-loading {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 500px;
      background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
      border-radius: 12px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    .loading-spinner {
      width: 40px;
      height: 40px;
      border: 4px solid #f3f3f3;
      border-top: 4px solid #007bff;
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    
    .chart-controls {
      background: #f8f9fa;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 20px;
      border: 1px solid #e9ecef;
    }
    
    .form-select {
      border-radius: 6px;
      border: 1px solid #ced4da;
      transition: all 0.3s ease;
    }
    
    .form-select:focus {
      border-color: #007bff;
      box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    
    .card {
      border-radius: 12px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      border: none;
    }
    
    .card-body {
      padding: 25px;
    }
    
    @media (max-width: 768px) {
      .chart-container {
        height: 400px !important;
        padding: 15px;
      }
      
      .chart-loading {
        height: 400px;
      }
    }
  </style>
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
                <div class="chart-controls">
                  <div class="row align-items-center">
                    <div class="col-md-6">
                      <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-funnel fs-5 text-primary"></i>
                        <label for="barangaySelect" class="mb-0 fw-semibold text-dark">Filter by Barangay:</label>
                        <select id="barangaySelect" class="form-select w-auto">
                          <option value="__all__">📊 All Barangays</option>
                          <?php
                          // build a list of barangays for the dropdown
                          $barangayList = array_keys($dataByBarangay);
                          foreach ($barangayList as $bname) {
                            echo '<option value="' . htmlspecialchars($bname) . '">🏘️ ' . htmlspecialchars($bname) . '</option>';
                          }
                          ?>
                        </select>
                      </div>
                    </div>
                    <div class="col-md-6 text-end">
                      <div class="d-flex align-items-center justify-content-end gap-2">
                        <span class="badge bg-primary">📈 Historical</span>
                        <span class="badge bg-warning">🔮 Predicted</span>
                      </div>
                    </div>
                  </div>
                </div>
                
                <div id="chartLoading" class="chart-loading">
                  <div class="text-center">
                    <div class="loading-spinner"></div>
                    <p class="mt-3 text-muted">Loading chart data...</p>
                  </div>
                </div>
                
                <div class="chart-container" id="chartContainer" style="position: relative; height: 500px; width: 100%; display: none;">
                  <canvas id="evacuationChart"></canvas>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
    <?php include '../layout/footer.php'; ?>
  </div>

  <script>
    // Show loading state initially
    document.addEventListener('DOMContentLoaded', function() {
      const loadingDiv = document.getElementById('chartLoading');
      const chartContainer = document.getElementById('chartContainer');
      
      // Simulate loading time for better UX
      setTimeout(() => {
        loadingDiv.style.display = 'none';
        chartContainer.style.display = 'block';
        initializeChart();
      }, 1000);
    });
    
    function initializeChart() {
      const ctx = document.getElementById('evacuationChart').getContext('2d');

    // Dates from PHP
    const historicalLabels = <?php echo json_encode($allDates); ?>;
    const combinedLabels = <?php echo json_encode($combinedDates); ?>;
    const forecastData = <?php echo json_encode($forecastData); ?>;

    // Build a tolerant lookup for forecastData: normalize keys (lowercase, remove punctuation, collapse spaces)
    const forecastLookup = {};
    function normalizeKey(s) {
      if (!s && s !== 0) return '';
      return String(s)
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9\s]/g, '')
        .replace(/\s+/g, ' ');
    }
    Object.keys(forecastData || {}).forEach(k => {
      const v = forecastData[k];
      forecastLookup[k] = v;
      const nk = normalizeKey(k);
      forecastLookup[nk] = v;
    });
    // Debug: expose forecast lookup keys to console for troubleshooting
    console.debug('forecastLookup keys:', Object.keys(forecastLookup));

    // Barangay datasets
    const rawDatasets = <?php echo json_encode($barangayDatasets); ?>;
  // Aggregated disaster datasets (sums across all barangays grouped by disaster)
  const aggregatedDisasterDatasets = <?php echo json_encode($disasterDatasets); ?>;

    // Find the index where historical data ends
    const historicalEndIndex = historicalLabels.length - 1;

    function makeChartDatasets(list, selectedBarangay = '__all__') {
      const datasets = [];
      
      // Colors for different disasters/barangays
      const colors = ['#007bff', '#28a745', '#dc3545', '#ffc107', '#17a2b8', '#6610f2', '#fd7e14', '#20c997'];
      let colorIndex = 0;
      
      list.forEach(ds => {
        if (selectedBarangay !== '__all__' && ds.label !== selectedBarangay) return;
        
        const currentColor = colors[colorIndex % colors.length];
        colorIndex++;
        
        // Historical data only (pad to full combined length so Chart.js aligns indexes)
        const extendedData = ds.data.slice(0, historicalEndIndex + 1);
        for (let i = extendedData.length; i < combinedLabels.length; i++) {
          extendedData.push(null);
        }
        
        // Historical data line with gradient
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(0, 123, 255, 0.1)');
        gradient.addColorStop(1, 'rgba(0, 123, 255, 0.05)');
        
        datasets.push({
          label: ds.label,
          data: extendedData,
          borderColor: '#007bff',
          backgroundColor: gradient,
          borderWidth: 3,
          fill: true,
          tension: 0.4,
          spanGaps: true,
          pointRadius: 4,
          pointHoverRadius: 6,
          pointBackgroundColor: '#007bff',
          pointBorderColor: '#fff',
          pointBorderWidth: 2,
          pointHoverBackgroundColor: '#0056b3',
          pointHoverBorderColor: '#fff',
          pointHoverBorderWidth: 3
        });
        
  // Add predictive lines if forecast data exists for this barangay (use normalized lookup)
  const normalizedLabel = (typeof ds.label === 'string') ? ds.label.split(' (')[0] : ds.label;
  const fd = forecastLookup[normalizedLabel] || forecastLookup[normalizedLabel.trim().toLowerCase()] || forecastLookup[normalizeKey(normalizedLabel)];
  // Debug: log which forecast match was found (if any)
  console.debug('Forecast match for:', normalizedLabel, '->', fd ? Object.keys(fd) : null);
        if (fd) {
          const lastHistoricalValue = ds.data[ds.data.length - 1];
          const scales = Object.keys(fd);

          if (lastHistoricalValue !== null && lastHistoricalValue !== undefined && scales.length > 0) {
            scales.forEach((scale) => {
              const forecast = fd[scale];
              if (!forecast || !forecast.date) return;

              // Build predictive data array (nulls except forecast point and connector)
              const predictiveData = new Array(combinedLabels.length).fill(null);

              // place last historical value at the last historical index to connect
              predictiveData[historicalEndIndex] = lastHistoricalValue;

              // find forecast date index in combinedLabels
              const forecastIndex = combinedLabels.indexOf(forecast.date);
              if (forecastIndex === -1) {
                // If forecast date not in combinedLabels, place forecast at last index
                predictiveData[combinedLabels.length - 1] = forecast.forecast;
              } else {
                predictiveData[forecastIndex] = forecast.forecast;
              }

              // color by scale when available
              const scaleColors = {
                '1-3': '#28a745',
                '4-7': '#ffc107',
                '8-10': '#dc3545'
              };
              const predColor = scaleColors[scale] || currentColor;

              // push predictive dataset for this scale
              datasets.push({
                label: `${ds.label} (Predicted ${scale})`,
                data: predictiveData,
                borderColor: predColor,
                backgroundColor: 'transparent',
                borderWidth: 2,
                borderDash: [8,4],
                fill: false,
                tension: 0.4,
                spanGaps: true,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: predColor,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointHoverBackgroundColor: predColor,
                pointHoverBorderColor: '#fff',
                pointHoverBorderWidth: 3,
                pointStyle: 'triangle'
              });

              // add bounds if provided
              if (forecast.lower_bound !== undefined && forecast.upper_bound !== undefined) {
                const boundsData = new Array(combinedLabels.length).fill(null);
                const bIndex = forecastIndex === -1 ? combinedLabels.length - 1 : forecastIndex;
                boundsData[bIndex] = [forecast.lower_bound, forecast.upper_bound];
                datasets.push({
                  label: `${ds.label} (${scale} Bounds)`,
                  data: boundsData,
                  borderColor: 'transparent',
                  backgroundColor: predColor + '22',
                  fill: true,
                  tension: 0.4,
                  spanGaps: true,
                  pointRadius: 0
                });
              }
            });
          }
        } else {
          // For aggregated disaster datasets or datasets without specific forecast data
          // Generate predictions based on trend
          const lastHistoricalValue = ds.data[ds.data.length - 1];
          
          if (lastHistoricalValue !== null && lastHistoricalValue !== undefined && ds.data.length > 2) {
            // Calculate trend from last few values
            const lastValues = ds.data.filter(v => v !== null).slice(-3);
            if (lastValues.length >= 2) {
              const trend = (lastValues[lastValues.length - 1] - lastValues[0]) / (lastValues.length - 1);
              const predictiveData = [...ds.data];
              
              // Add predicted values based on trend
              for (let i = 0; i < 7; i++) {
                const predictedValue = Math.max(0, lastHistoricalValue + trend * (i + 1));
                predictiveData.push(predictedValue);
              }
              
              // Predictive line with enhanced styling
              const predictionGradient = ctx.createLinearGradient(0, 0, 0, 400);
              predictionGradient.addColorStop(0, 'rgba(255, 107, 53, 0.15)');
              predictionGradient.addColorStop(1, 'rgba(255, 107, 53, 0.05)');
              
              datasets.push({
                label: ds.label + ' (Predicted)',
                data: predictiveData,
                borderColor: '#ff6b35',
                backgroundColor: predictionGradient,
                borderWidth: 3,
                borderDash: [8, 4],
                fill: true,
                tension: 0.4,
                spanGaps: true,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: '#ff6b35',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointHoverBackgroundColor: '#e55a2b',
                pointHoverBorderColor: '#fff',
                pointHoverBorderWidth: 3,
                pointStyle: 'triangle'
              });
            }
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
        maintainAspectRatio: false,
        interaction: {
          intersect: false,
          mode: 'index'
        },
        plugins: {
          title: {
            display: true,
            text: 'Total Evacuees per Barangay with Predictions',
            font: {
              size: 18,
              weight: 'bold',
              family: "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif"
            },
            color: '#2c3e50',
            padding: {
              top: 20,
              bottom: 30
            }
          },
          tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            titleColor: '#fff',
            bodyColor: '#fff',
            borderColor: '#007bff',
            borderWidth: 1,
            cornerRadius: 8,
            displayColors: true,
            callbacks: {
              title: function(context) {
                const date = new Date(context[0].label);
                return date.toLocaleDateString('en-US', { 
                  weekday: 'long', 
                  year: 'numeric', 
                  month: 'long', 
                  day: 'numeric' 
                });
              },
              label: function(context) {
                const dataset = context.dataset;
                const value = context.formattedValue;
                const isForecast = context.dataIndex > historicalEndIndex;
                
                if (dataset.label.includes('Predicted')) {
                  // parse base barangay and scale from label like "Barangay Name (Predicted 4-7)"
                  const base = dataset.label.split(' (')[0];
                  const scaleMatch = dataset.label.match(/Predicted\s*(.*)\)/);
                  const scale = scaleMatch ? scaleMatch[1] : null;
                  const f = forecastLookup[base] || forecastLookup[base.trim().toLowerCase()];
                  const accuracy = f && scale ? (f[scale]?.accuracy ?? null) : null;
                  if (accuracy !== null && accuracy !== undefined) {
                    return `${dataset.label}: ${value} evacuees (Accuracy: ${accuracy.toFixed(1)}%)`;
                  }
                  return `${dataset.label}: ${value} evacuees`;
                }
                return `${dataset.label}: ${value} evacuees`;
              },
              afterLabel: function(context) {
                const isForecast = context.dataIndex > historicalEndIndex;
                if (isForecast && !context.dataset.label.includes('Predicted')) {
                  return '📊 Forecast Period';
                }
                return '';
              }
            }
          },
          legend: {
            display: true,
            position: 'top',
            align: 'start',
            labels: {
              usePointStyle: true,
              pointStyle: 'circle',
              padding: 20,
              font: {
                size: 12,
                family: "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif"
              },
              color: '#2c3e50',
              generateLabels: function(chart) {
                const original = Chart.defaults.plugins.legend.labels.generateLabels;
                const labels = original.call(this, chart);
                labels.forEach(label => {
                  if (label.text.includes('Predicted')) {
                    label.text = '🔮 ' + label.text;
                  } else {
                    label.text = '📈 ' + label.text;
                  }
                });
                return labels;
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              color: 'rgba(0, 0, 0, 0.1)',
              drawBorder: false
            },
            ticks: {
              color: '#666',
              font: {
                size: 11,
                family: "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif"
              },
              callback: function(value) {
                return value.toLocaleString();
              }
            },
            title: {
              display: true,
              text: 'Number of Evacuees',
              color: '#2c3e50',
              font: {
                size: 14,
                weight: 'bold',
                family: "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif"
              },
              padding: {
                top: 10,
                bottom: 20
              }
            }
          },
          x: {
            grid: {
              color: 'rgba(0, 0, 0, 0.1)',
              drawBorder: false
            },
            ticks: {
              color: '#666',
              font: {
                size: 11,
                family: "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif"
              },
              maxRotation: 45,
              minRotation: 0,
              callback: function(value, index) {
                const date = new Date(this.getLabelForValue(value));
                return date.toLocaleDateString('en-US', { 
                  month: 'short', 
                  day: 'numeric' 
                });
              }
            },
            title: {
              display: true,
              text: 'Date',
              color: '#2c3e50',
              font: {
                size: 14,
                weight: 'bold',
                family: "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif"
              },
              padding: {
                top: 20,
                bottom: 10
              }
            }
          }
        },
        elements: {
          point: {
            hoverRadius: 8,
            hoverBorderWidth: 3
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
        // When All Barangays is selected, show each barangay with its prediction (display predictions for all barangays)
        chart.data.datasets = makeChartDatasets(rawDatasets, '__all__');
      } else {
        const filtered = rawDatasets.filter(d => d.label === val);
        const ds = filtered.length ? filtered[0] : null;
        if (ds) {
          // Keep all dates but just update the chart with the selected barangay's data
          chart.data.labels = combinedLabels;
          // Use the original dataset to preserve forecast handling
          chart.data.datasets = makeChartDatasets([ds], val);
        }
      }
      chart.update();
    });
    }
  </script>
</body>

</html>