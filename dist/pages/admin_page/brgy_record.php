<?php
include '../../../database/session.php';
include '../layout/head_links.php';

// Fetch all disasters from disaster_table for dropdown (using prepared statements for consistency)
$allDisastersQuery = "SELECT MIN(disaster_id) AS disaster_id, kind_of_disaster
FROM disaster_table
GROUP BY kind_of_disaster
ORDER BY kind_of_disaster ASC";

$allDisastersResult = $conn->query($allDisastersQuery);
$allDisasterList = []; // disaster_id => disaster_name (kind_of_disaster)
$kindOfDisasterToIds = []; // kind_of_disaster => [disaster_ids]

// Also fetch all disaster_ids for each kind_of_disaster
$allDisastersFullQuery = "SELECT disaster_id, kind_of_disaster FROM disaster_table ORDER BY kind_of_disaster ASC";
$allDisastersFullResult = $conn->query($allDisastersFullQuery);
if ($allDisastersFullResult) {
  while ($row = $allDisastersFullResult->fetch_assoc()) {
    $disasterId = $row['disaster_id'];
    $kindOfDisaster = $row['kind_of_disaster'];
    if (!isset($kindOfDisasterToIds[$kindOfDisaster])) {
      $kindOfDisasterToIds[$kindOfDisaster] = [];
    }
    if (!in_array($disasterId, $kindOfDisasterToIds[$kindOfDisaster])) {
      $kindOfDisasterToIds[$kindOfDisaster][] = $disasterId;
    }
  }
}

if ($allDisastersResult) {
  while ($row = $allDisastersResult->fetch_assoc()) {
    $allDisasterList[$row['disaster_id']] = $row['kind_of_disaster'];
  }
}

// Fetch barangay disaster records (include disaster id so we can aggregate by disaster)
$query = "
 SELECT brgy_record_id, brgy_record_table.disaster_id as disaster_id, barangay_name, total_evacuess, total_population, kind_of_disaster, scale, brgy_record_table.date as date
 FROM brgy_record_table
 LEFT JOIN disaster_table ON brgy_record_table.disaster_id = disaster_table.disaster_id
 ORDER BY date ASC
";
$result = $conn->query($query);

/**
 * Calculate scale (1-10) based on evacuation percentage of population
 * @param int $totalEvacuees - Number of evacuees
 * @param int $totalPopulation - Total population
 * @return int - Scale value from 1 to 10
 */
function calculateScale($totalEvacuees, $totalPopulation) {
  // Handle edge cases
  if ($totalPopulation <= 0) {
    return 1; // Default to lowest scale if no population data
  }
  
  if ($totalEvacuees <= 0) {
    return 1; // No evacuees = lowest scale
  }
  
  // Calculate evacuation percentage
  $evacuationPercentage = ($totalEvacuees / $totalPopulation) * 100;
  
  // Map percentage to scale (1-10)
  // 0-10% = Scale 1-3 (Low)
  // 10-30% = Scale 4-7 (Medium)
  // 30%+ = Scale 8-10 (High)
  
  if ($evacuationPercentage <= 5) {
    // 0-5%: Scale 1-2
    return max(1, min(2, round(1 + ($evacuationPercentage / 5) * 1)));
  } elseif ($evacuationPercentage <= 10) {
    // 5-10%: Scale 2-3
    return max(2, min(3, round(2 + (($evacuationPercentage - 5) / 5) * 1)));
  } elseif ($evacuationPercentage <= 20) {
    // 10-20%: Scale 4-5
    return max(4, min(5, round(4 + (($evacuationPercentage - 10) / 10) * 1)));
  } elseif ($evacuationPercentage <= 30) {
    // 20-30%: Scale 6-7
    return max(6, min(7, round(6 + (($evacuationPercentage - 20) / 10) * 1)));
  } elseif ($evacuationPercentage <= 50) {
    // 30-50%: Scale 8
    return 8;
  } elseif ($evacuationPercentage <= 70) {
    // 50-70%: Scale 9
    return 9;
  } else {
    // 70%+: Scale 10
    return 10;
  }
}

// Build unified dataset
$allDates = [];
$dataByBarangay = [];
$dataByBarangayDisaster = []; // barangay => disaster_id => date => value
$dataByBarangayDisasterFull = []; // barangay => disaster_id => date => [full record with population and scale]
$barangayDisasterMap = []; // barangay => [disaster_ids]
$sumByDisasterDate = []; // disaster_id => date => sum
$disasterList = []; // disaster_id => disaster_name

while ($row = mysqli_fetch_assoc($result)) {
  $barangay = $row['barangay_name'];
  $date = $row['date'];
  $disasterId = $row['disaster_id'];
  $disasterName = $row['kind_of_disaster'];
  $totalEvacuees = (int)$row['total_evacuess'];
  $totalPopulation = isset($row['total_population']) ? (int)$row['total_population'] : 0;
  
  // Calculate scale based on population and evacuees
  $calculatedScale = calculateScale($totalEvacuees, $totalPopulation);

  $allDates[] = $date; // collect all dates
  $dataByBarangay[$barangay][$date] = $totalEvacuees;

  // Track barangay-disaster relationships
  if (!empty($disasterId)) {
    if (!isset($dataByBarangayDisaster[$barangay])) {
      $dataByBarangayDisaster[$barangay] = [];
    }
    if (!isset($dataByBarangayDisaster[$barangay][$disasterId])) {
      $dataByBarangayDisaster[$barangay][$disasterId] = [];
    }
    $dataByBarangayDisaster[$barangay][$disasterId][$date] = $totalEvacuees;
    
    // Store full record with population and calculated scale
    if (!isset($dataByBarangayDisasterFull[$barangay])) {
      $dataByBarangayDisasterFull[$barangay] = [];
    }
    if (!isset($dataByBarangayDisasterFull[$barangay][$disasterId])) {
      $dataByBarangayDisasterFull[$barangay][$disasterId] = [];
    }
    $dataByBarangayDisasterFull[$barangay][$disasterId][$date] = [
      'evacuees' => $totalEvacuees,
      'population' => $totalPopulation,
      'scale' => $calculatedScale,
      'date' => $date
    ];
    
    // Track which disasters each barangay has
    if (!isset($barangayDisasterMap[$barangay])) {
      $barangayDisasterMap[$barangay] = [];
    }
    if (!in_array($disasterId, $barangayDisasterMap[$barangay])) {
      $barangayDisasterMap[$barangay][] = $disasterId;
    }
    
    $disasterList[$disasterId] = $disasterName ?: ('Disaster ' . $disasterId);
    if (!isset($sumByDisasterDate[$disasterId])) $sumByDisasterDate[$disasterId] = [];
    if (!isset($sumByDisasterDate[$disasterId][$date])) $sumByDisasterDate[$disasterId][$date] = 0;
    $sumByDisasterDate[$disasterId][$date] += $totalEvacuees;
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
    'data' => $series,
    'disasterIds' => isset($barangayDisasterMap[$barangay]) ? $barangayDisasterMap[$barangay] : []
  ];
}

// Build disaster-specific barangay datasets (barangay data filtered by disaster)
$barangayDatasetsByDisaster = []; // disaster_id => [datasets]
$barangayDatasetsByKindOfDisaster = []; // kind_of_disaster => [datasets]
foreach ($dataByBarangayDisaster as $barangay => $disasterData) {
  foreach ($disasterData as $disasterId => $dateRecords) {
    // Get kind_of_disaster for this disaster_id
    $kindOfDisaster = $disasterList[$disasterId] ?? null;
    
    if (!isset($barangayDatasetsByDisaster[$disasterId])) {
      $barangayDatasetsByDisaster[$disasterId] = [];
    }
    $series = [];
    foreach ($allDates as $date) {
      $series[] = isset($dateRecords[$date]) ? $dateRecords[$date] : null;
    }
    $dataset = [
      'label' => $barangay,
      'data' => $series
    ];
    $barangayDatasetsByDisaster[$disasterId][] = $dataset;
    
    // Also group by kind_of_disaster
    if ($kindOfDisaster) {
      if (!isset($barangayDatasetsByKindOfDisaster[$kindOfDisaster])) {
        $barangayDatasetsByKindOfDisaster[$kindOfDisaster] = [];
      }
      // Check if this barangay already exists for this kind_of_disaster (merge data if needed)
      $existingIndex = -1;
      foreach ($barangayDatasetsByKindOfDisaster[$kindOfDisaster] as $idx => $existing) {
        if ($existing['label'] === $barangay) {
          $existingIndex = $idx;
          break;
        }
      }
      if ($existingIndex >= 0) {
        // Merge data: combine values where both have data, or use non-null value
        for ($i = 0; $i < count($series); $i++) {
          if ($series[$i] !== null) {
            $barangayDatasetsByKindOfDisaster[$kindOfDisaster][$existingIndex]['data'][$i] = 
              ($barangayDatasetsByKindOfDisaster[$kindOfDisaster][$existingIndex]['data'][$i] ?? 0) + $series[$i];
          }
        }
      } else {
        $barangayDatasetsByKindOfDisaster[$kindOfDisaster][] = $dataset;
      }
    }
  }
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

// Prepare table data: organize records by disaster and barangay
$tableData = []; // kind_of_disaster => [barangay => [records]]
foreach ($dataByBarangayDisasterFull as $barangay => $disasterData) {
  foreach ($disasterData as $disasterId => $dateRecords) {
    $kindOfDisaster = $disasterList[$disasterId] ?? null;
    if ($kindOfDisaster) {
      if (!isset($tableData[$kindOfDisaster])) {
        $tableData[$kindOfDisaster] = [];
      }
      if (!isset($tableData[$kindOfDisaster][$barangay])) {
        $tableData[$kindOfDisaster][$barangay] = [];
      }
      // Add all date records for this barangay and disaster with calculated scale
      foreach ($dateRecords as $date => $record) {
        $tableData[$kindOfDisaster][$barangay][] = [
          'date' => $date,
          'evacuees' => $record['evacuees'],
          'population' => $record['population'],
          'scale' => $record['scale'],
          'disaster_id' => $disasterId
        ];
      }
    }
  }
}

// Sort records by date for each barangay
foreach ($tableData as $kindOfDisaster => $barangays) {
  foreach ($barangays as $barangay => $records) {
    usort($tableData[$kindOfDisaster][$barangay], function($a, $b) {
      return strtotime($a['date']) - strtotime($b['date']);
    });
  }
}

// Fetch SARIMAX prediction results from brgy_forecasts table
$predictionsQuery = "
    SELECT 
        brgy_forecasts.brgy_forecast_id,
        brgy_forecasts.date,
        brgy_forecasts.barangay_name,
        brgy_forecasts.period,
        brgy_forecasts.scale_range,
        brgy_forecasts.forecast,
        brgy_forecasts.lower_bound,
        brgy_forecasts.upper_bound,
        brgy_forecasts.created_at,
        brgy_forecasts.accuracy_percentage,
        brgy_forecasts.disaster_id,
        disaster_table.kind_of_disaster
    FROM brgy_forecasts 
    LEFT JOIN disaster_table ON brgy_forecasts.disaster_id = disaster_table.disaster_id
    ORDER BY brgy_forecasts.barangay_name, brgy_forecasts.disaster_id, brgy_forecasts.date ASC, brgy_forecasts.scale_range
";
$predictionsResult = mysqli_query($conn, $predictionsQuery);

// Group predictions by barangay and disaster type (kind_of_disaster)
// Also create a lookup by barangay and disaster_id for graph access
$predictionsData = [];
$predictionsDataByDisasterId = []; // For graph lookup: barangay_disasterId => all forecasts array
while ($row = mysqli_fetch_assoc($predictionsResult)) {
    $barangay = trim($row['barangay_name']); // Trim whitespace
    $disasterId = $row['disaster_id'];
    $kindOfDisaster = $row['kind_of_disaster'] ?: 'Unknown';
    
    // Use kind_of_disaster as the key for table display
    $key = $barangay . '_' . $kindOfDisaster;
    
    // Also create key by disaster_id for graph lookup (normalize for consistency)
    $barangayNormalized = strtolower(trim($barangay));
    $disasterIdKey = ($disasterId !== null && $disasterId !== '') ? $disasterId : 'NULL';
    $keyByDisasterId = $barangayNormalized . '_' . $disasterIdKey;
    
    if (!isset($predictionsData[$key])) {
        $predictionsData[$key] = [
            'barangay_name' => $barangay,
            'disaster_id' => $disasterId,
            'kind_of_disaster' => $kindOfDisaster,
            'predictions' => []
        ];
    }
    
    // Store the latest prediction for each scale (for table display)
    $scale = $row['scale_range'];
    $predictionData = [
        'forecast' => (float)$row['forecast'],
        'lower_bound' => (float)$row['lower_bound'],
        'upper_bound' => (float)$row['upper_bound'],
        'accuracy' => (float)$row['accuracy_percentage'],
        'forecast_date' => $row['date'],
        'created_at' => $row['created_at'],
        'period' => $row['period']
    ];
    
    if (!isset($predictionsData[$key]['predictions'][$scale])) {
        $predictionsData[$key]['predictions'][$scale] = $predictionData;
    }
    
    // Store ALL forecasts for graph (organized by date and scale)
    if (!isset($predictionsDataByDisasterId[$keyByDisasterId])) {
        $predictionsDataByDisasterId[$keyByDisasterId] = [];
    }
    
    // Store all forecast records organized by date
    $forecastDate = $row['date'];
    if ($forecastDate) {
        if (!isset($predictionsDataByDisasterId[$keyByDisasterId][$forecastDate])) {
            $predictionsDataByDisasterId[$keyByDisasterId][$forecastDate] = [];
        }
        if (!isset($predictionsDataByDisasterId[$keyByDisasterId][$forecastDate][$scale])) {
            $predictionsDataByDisasterId[$keyByDisasterId][$forecastDate][$scale] = $predictionData;
        }
    }
}

// Process monthly forecasts with severity/risk levels
$monthlyForecastsByDisasterId = []; // barangay_disasterId => monthly forecasts array
$monthlyForecastsByBarangay = []; // Also create a lookup by barangay name only (case-insensitive)
mysqli_data_seek($predictionsResult, 0); // Reset result pointer
while ($row = mysqli_fetch_assoc($predictionsResult)) {
    $barangay = trim($row['barangay_name']); // Trim whitespace
    $disasterId = $row['disaster_id'];
    $period = $row['period'];
    
    // Only process records with period (monthly forecasts)
    if ($period && !empty($period) && !empty($barangay)) {
        // Create key - handle NULL disaster_id properly, normalize barangay name
        $barangayNormalized = strtolower(trim($barangay)); // Normalize for matching
        $disasterIdKey = ($disasterId !== null && $disasterId !== '') ? $disasterId : 'NULL';
        $keyByDisasterId = $barangayNormalized . '_' . $disasterIdKey;
        
        if (!isset($monthlyForecastsByDisasterId[$keyByDisasterId])) {
            $monthlyForecastsByDisasterId[$keyByDisasterId] = [];
        }
        
        // Also store by barangay name only (for fallback matching)
        if (!isset($monthlyForecastsByBarangay[$barangayNormalized])) {
            $monthlyForecastsByBarangay[$barangayNormalized] = [];
        }
        
        // Map scale_range to risk level
        $scale = $row['scale_range'];
        $riskLevel = 'Medium'; // Default
        if ($scale === '1-3') {
            $riskLevel = 'Low';
        } elseif ($scale === '4-7') {
            $riskLevel = 'Medium';
        } elseif ($scale === '8-10') {
            $riskLevel = 'High';
        }
        
        $monthlyForecastData = [
            'period' => $period,
            'date' => $row['date'],
            'risk_level' => $riskLevel,
            'forecast' => (float)$row['forecast'],
            'lower_bound' => (float)$row['lower_bound'],
            'upper_bound' => (float)$row['upper_bound'],
            'accuracy' => (float)$row['accuracy_percentage'],
            'disaster_id' => $disasterId  // Include disaster_id for separation
        ];
        
        // Store monthly forecast by period (only store one per period, prefer the latest)
        if (!isset($monthlyForecastsByDisasterId[$keyByDisasterId][$period])) {
            $monthlyForecastsByDisasterId[$keyByDisasterId][$period] = $monthlyForecastData;
        }
        
        // Also store in barangay-only lookup (for fallback, but include disaster_id)
        if (!isset($monthlyForecastsByBarangay[$barangayNormalized][$period])) {
            $monthlyForecastsByBarangay[$barangayNormalized][$period] = $monthlyForecastData;
        }
    }
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
  <style>
    .filter-controls {
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
    
    .table-container {
      overflow-x: auto;
    }
    
    .table thead th {
      background-color: #007bff;
      color: white;
      font-weight: 600;
      border: none;
      padding: 12px;
    }
    
    .table tbody tr:hover {
      background-color: #f8f9fa;
    }
    
    .table tbody td {
      padding: 12px;
      vertical-align: middle;
    }
    
    .badge {
      font-size: 0.85em;
      padding: 6px 10px;
    }
    
    .no-data {
      text-align: center;
      padding: 40px;
      color: #6c757d;
    }
    
    .view-graph-btn {
      transition: all 0.3s ease;
    }
    
    .view-graph-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
    }
    
    #graphContainer {
      min-height: 400px;
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
              <i class="bi bi-table fs-2 text-primary"></i>
              <h3 class="mb-0">Barangay Evacuation Records</h3>
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
                <div class="filter-controls">
                  <div class="row align-items-center">
                    <div class="col-md-6">
                      <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-funnel fs-5 text-primary"></i>
                        <label for="disasterSelect" class="mb-0 fw-semibold text-dark">Kind of Disaster:</label>
                        <select id="disasterSelect" class="form-select w-auto">
                          <option value="__all__">📊 All Disasters</option>
                          <?php
                          // build a list of all disasters for the dropdown
                          foreach ($allDisasterList as $disasterId => $disasterName) {
                            echo '<option value="' . htmlspecialchars($disasterId) . '">⚠️ ' . htmlspecialchars($disasterName) . '</option>';
                          }
                          ?>
                        </select>
                      </div>
                    </div>
                  </div>
                </div>
                
                <div class="table-container">
                  <table class="table table-striped table-hover" id="barangayTable">
                    <thead>
                      <tr>
                        <th>Barangay</th>
                        <th>Disaster Type</th>
                        <th>Total Evacuees</th>
                        <th>Number of Records</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody id="tableBody">
                      <?php
                      // Display unique barangays only
                      $uniqueBarangays = []; // Track unique barangay-disaster combinations
                      foreach ($tableData as $kindOfDisaster => $barangays) {
                        foreach ($barangays as $barangay => $records) {
                          // Create a unique key for barangay-disaster combination
                          $uniqueKey = $barangay . '_' . $kindOfDisaster;
                          if (!isset($uniqueBarangays[$uniqueKey])) {
                            // Calculate total evacuees for this barangay-disaster combination
                            $totalEvacuees = 0;
                            $disasterId = null;
                            foreach ($records as $record) {
                              $totalEvacuees += $record['evacuees'];
                              if ($disasterId === null) {
                                $disasterId = $record['disaster_id'];
                              }
                            }
                            
                            // Prepare data for the graph (dates and evacuees)
                            $graphData = [];
                            foreach ($records as $record) {
                              $graphData[] = [
                                'date' => $record['date'],
                                'evacuees' => $record['evacuees']
                              ];
                            }
                            // Sort by date
                            usort($graphData, function($a, $b) {
                              return strtotime($a['date']) - strtotime($b['date']);
                            });
                            
                            // Get ALL forecast data for this barangay-disaster combination (using disaster_id for lookup)
                            // Normalize for consistent matching
                            $barangayNormalizedForLookup = strtolower(trim($barangay));
                            $disasterIdForLookup = ($disasterId !== null && $disasterId !== '') ? $disasterId : 'NULL';
                            $forecastKey = $barangayNormalizedForLookup . '_' . $disasterIdForLookup;
                            $forecastDataForGraph = null;
                            if (isset($predictionsDataByDisasterId[$forecastKey])) {
                              $forecastDataForGraph = $predictionsDataByDisasterId[$forecastKey];
                            }
                            
                            // Get monthly forecasts for this specific barangay-disaster combination
                            // Direct search: iterate through ALL forecasts and match by barangay + disaster_id
                            $monthlyForecastsForGraph = [];
                            
                            // Normalize disaster_id for consistent matching (handle both int and string)
                            $disasterIdNormalized = ($disasterId !== null && $disasterId !== '') ? (int)$disasterId : null;
                            
                            // First, try exact key match (fastest path)
                            if (isset($monthlyForecastsByDisasterId[$forecastKey]) && !empty($monthlyForecastsByDisasterId[$forecastKey])) {
                              // Verify all forecasts in this key match the disaster_id
                              foreach ($monthlyForecastsByDisasterId[$forecastKey] as $period => $forecastData) {
                                if (empty($forecastData) || !is_array($forecastData)) {
                                  continue;
                                }
                                
                                $forecastDisasterId = isset($forecastData['disaster_id']) ? $forecastData['disaster_id'] : null;
                                $forecastDisasterIdNormalized = ($forecastDisasterId !== null && $forecastDisasterId !== '') 
                                  ? (int)$forecastDisasterId 
                                  : null;
                                
                                $disasterIdMatches = false;
                                if ($disasterIdNormalized === null) {
                                  $disasterIdMatches = ($forecastDisasterIdNormalized === null);
                                } else {
                                  $disasterIdMatches = ($forecastDisasterIdNormalized === $disasterIdNormalized || 
                                                       $forecastDisasterId == $disasterId);
                                }
                                
                                if ($disasterIdMatches && 
                                    isset($forecastData['forecast']) && 
                                    isset($forecastData['risk_level']) &&
                                    isset($forecastData['period']) &&
                                    $forecastData['forecast'] >= 0) {
                                  $monthlyForecastsForGraph[$period] = $forecastData;
                                }
                              }
                            }
                            
                            // If exact match didn't work, try comprehensive search through ALL monthly forecasts
                            if (empty($monthlyForecastsForGraph)) {
                            foreach ($monthlyForecastsByDisasterId as $key => $forecasts) {
                              if (empty($forecasts) || !is_array($forecasts)) {
                                continue;
                              }
                              
                              // Extract barangay and disaster_id from key (format: "barangay_disasterId")
                              $keyParts = explode('_', $key, 2);
                              $keyBarangayNormalized = count($keyParts) >= 1 ? strtolower(trim($keyParts[0])) : '';
                              $keyDisasterIdStr = count($keyParts) >= 2 ? $keyParts[1] : '';
                              $keyDisasterIdNormalized = ($keyDisasterIdStr !== null && $keyDisasterIdStr !== '' && $keyDisasterIdStr !== 'NULL') 
                                ? (int)$keyDisasterIdStr 
                                : null;
                              
                              // Check if barangay matches
                              $barangayMatches = ($keyBarangayNormalized === $barangayNormalizedForLookup);
                              
                              // Check if disaster_id from key matches
                              $keyDisasterIdMatches = false;
                              if ($disasterIdNormalized === null) {
                                $keyDisasterIdMatches = ($keyDisasterIdNormalized === null);
                              } else {
                                $keyDisasterIdMatches = ($keyDisasterIdNormalized === $disasterIdNormalized);
                              }
                              
                              // Only process if both barangay and disaster_id from key match
                              if ($barangayMatches && $keyDisasterIdMatches) {
                                // Check each forecast in this group
                                foreach ($forecasts as $period => $forecastData) {
                                  if (empty($forecastData) || !is_array($forecastData)) {
                                    continue;
                                  }
                                  
                                  // Get disaster_id from forecast data (double-check)
                                  $forecastDisasterId = isset($forecastData['disaster_id']) ? $forecastData['disaster_id'] : null;
                                  $forecastDisasterIdNormalized = ($forecastDisasterId !== null && $forecastDisasterId !== '') 
                                    ? (int)$forecastDisasterId 
                                    : null;
                                  
                                  // Verify disaster_id from data also matches (safety check)
                                  $dataDisasterIdMatches = false;
                                  if ($disasterIdNormalized === null) {
                                    $dataDisasterIdMatches = ($forecastDisasterIdNormalized === null);
                                  } else {
                                    $dataDisasterIdMatches = ($forecastDisasterIdNormalized === $disasterIdNormalized || 
                                                             $forecastDisasterId == $disasterId);
                                  }
                                  
                                  // If matches, add to results
                                  if ($dataDisasterIdMatches && 
                                      isset($forecastData['forecast']) && 
                                      isset($forecastData['risk_level']) &&
                                      isset($forecastData['period']) &&
                                      $forecastData['forecast'] >= 0) {
                                    $monthlyForecastsForGraph[$period] = $forecastData;
                                  }
                                }
                              }
                            }
                            } // End of comprehensive search if empty
                            
                            // If still empty, try barangay-only lookup as final fallback
                            if (empty($monthlyForecastsForGraph) && 
                                isset($monthlyForecastsByBarangay[$barangayNormalizedForLookup])) {
                              $barangayForecasts = $monthlyForecastsByBarangay[$barangayNormalizedForLookup];
                              if (!empty($barangayForecasts) && is_array($barangayForecasts)) {
                                // Filter by disaster_id
                                foreach ($barangayForecasts as $period => $forecastData) {
                                  $forecastDisasterId = isset($forecastData['disaster_id']) ? $forecastData['disaster_id'] : null;
                                  $forecastDisasterIdNormalized = ($forecastDisasterId !== null && $forecastDisasterId !== '') 
                                    ? (int)$forecastDisasterId 
                                    : null;
                                  
                                  // Match disaster_id
                                  $disasterIdMatches = false;
                                  if ($disasterIdNormalized === null) {
                                    $disasterIdMatches = ($forecastDisasterIdNormalized === null);
                                  } else {
                                    $disasterIdMatches = ($forecastDisasterIdNormalized === $disasterIdNormalized || 
                                                         $forecastDisasterId == $disasterId);
                                  }
                                  
                                  if ($disasterIdMatches && 
                                      isset($forecastData['forecast']) && 
                                      isset($forecastData['risk_level']) &&
                                      isset($forecastData['period']) &&
                                      $forecastData['forecast'] >= 0) {
                                    $monthlyForecastsForGraph[$period] = $forecastData;
                                  }
                                }
                              }
                            }
                            
                            // Final validation: ensure all forecasts are valid (already filtered by disaster_id above)
                            if (!empty($monthlyForecastsForGraph)) {
                              $validatedForecasts = [];
                              foreach ($monthlyForecastsForGraph as $period => $forecastData) {
                                // Final validation check
                                if (!empty($forecastData) && 
                                    isset($forecastData['forecast']) && 
                                    isset($forecastData['risk_level']) &&
                                    isset($forecastData['period']) &&
                                    $forecastData['forecast'] >= 0) {
                                  $validatedForecasts[$period] = $forecastData;
                                }
                              }
                              $monthlyForecastsForGraph = $validatedForecasts;
                            }
                            
                            // Ensure it's an array, not null
                            if (empty($monthlyForecastsForGraph)) {
                              $monthlyForecastsForGraph = [];
                            }
                            
                            echo '<tr data-disaster-id="' . htmlspecialchars($disasterId) . '" data-kind-of-disaster="' . htmlspecialchars($kindOfDisaster) . '" data-barangay="' . htmlspecialchars($barangay) . '">';
                            echo '<td><strong>' . htmlspecialchars($barangay) . '</strong></td>';
                            echo '<td>' . htmlspecialchars($kindOfDisaster) . '</td>';
                            echo '<td><span class="badge bg-primary">' . number_format($totalEvacuees) . '</span></td>';
                            echo '<td><span class="badge bg-info">' . count($records) . '</span></td>';
                            echo '<td>';
                            echo '<button class="btn btn-sm btn-primary view-graph-btn" data-barangay="' . htmlspecialchars($barangay) . '" data-disaster="' . htmlspecialchars($kindOfDisaster) . '" data-disaster-id="' . htmlspecialchars($disasterId) . '" data-graph-data=\'' . json_encode($graphData) . '\' data-forecast-data=\'' . json_encode($forecastDataForGraph) . '\' data-monthly-forecasts=\'' . json_encode($monthlyForecastsForGraph) . '\'>';
                            echo '<i class="bi bi-graph-up"></i> View Graph';
                            echo '</button>';
                            echo '</td>';
                            echo '</tr>';
                            
                            $uniqueBarangays[$uniqueKey] = true;
                          }
                        }
                      }
                      ?>
                    </tbody>
                  </table>
                  <div id="noDataMessage" class="no-data" style="display: none;">
                    <i class="bi bi-inbox fs-1 text-muted"></i>
                    <p class="mt-3">No records found for the selected disaster.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- SARIMAX Predictions Table -->
          <div class="row mt-4">
            <div class="col-md-12">
              <div class="card">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                  <h5 class="mb-0">
                    <i class="bi bi-calculator"></i>Predictive Results In Every Barangay
                  </h5>
                  <span class="badge bg-light text-dark"><?php 
                    $uniqueBarangays = [];
                    foreach ($predictionsData as $data) {
                      if (isset($data['barangay_name'])) {
                        $uniqueBarangays[$data['barangay_name']] = true;
                      }
                    }
                    echo count($uniqueBarangays); 
                  ?> Barangays</span>
                </div>
                <div class="card-body">
                  <div class="table-container">
                    <table class="table table-striped table-hover" id="predictionsTable">
                      <thead>
                        <tr>
                          <th>Barangay</th>
                          <th>Disaster Type</th>
                          <th>Scale Range</th>
                          <th>Forecast (Evacuees)</th>
                          <th>Accuracy</th>
                          <th>Generated At</th>
                        </tr>
                      </thead>
                      <tbody id="predictionsTableBody">
                        <?php
                        if (empty($predictionsData)) {
                          echo '<tr><td colspan="10" class="text-center text-muted">No predictions available. Run the SARIMAX predictor to generate forecasts.</td></tr>';
                        } else {
                          foreach ($predictionsData as $key => $data) {
                            $barangay = $data['barangay_name'];
                            $disasterId = $data['disaster_id'];
                            $kindOfDisaster = $data['kind_of_disaster'] ?: 'N/A';
                            
                            // Display predictions for each scale
                            $scaleLabels = ['1-3' => 'Low Risk', '4-7' => 'Medium Risk', '8-10' => 'High Risk'];
                            $scaleColors = ['1-3' => 'success', '4-7' => 'warning', '8-10' => 'danger'];
                            
                            foreach (['1-3', '4-7', '8-10'] as $scale) {
                              if (isset($data['predictions'][$scale])) {
                                $pred = $data['predictions'][$scale];
                                $ciRange = $pred['upper_bound'] - $pred['lower_bound'];
                                $color = $scaleColors[$scale];
                                
                                echo '<tr data-barangay="' . htmlspecialchars($barangay) . '" data-disaster-id="' . htmlspecialchars($disasterId ?: '') . '" data-kind-of-disaster="' . htmlspecialchars($kindOfDisaster) . '">';
                                echo '<td><strong>' . htmlspecialchars($barangay) . '</strong></td>';
                                echo '<td>' . htmlspecialchars($kindOfDisaster) . '</td>';
                                echo '<td><span class="badge bg-' . $color . '">' . $scaleLabels[$scale] . ' (' . $scale . ')</span></td>';
                                echo '<td><strong>' . number_format($pred['forecast'], 0) . '</strong></td>';
                                echo '<td>';
                                if ($pred['accuracy'] !== null) {
                                  $accColor = $pred['accuracy'] >= 90 ? 'success' : ($pred['accuracy'] >= 80 ? 'warning' : 'danger');
                                  echo '<span class="badge bg-' . $accColor . '">' . number_format($pred['accuracy'], 1) . '%</span>';
                                } else {
                                  echo '<span class="badge bg-secondary">N/A</span>';
                                }
                                echo '</td>';
                                echo '</td>';
                                echo '<td>';
                                if ($pred['created_at']) {
                                  echo date('M d, Y H:i', strtotime($pred['created_at']));
                                } else {
                                  echo 'N/A';
                                }
                                echo '</td>';
                                echo '</tr>';
                              }
                            }
                          }
                        }
                        ?>
                      </tbody>
                    </table>
                    <div id="noPredictionsMessage" class="no-data" style="display: none;">
                      <i class="bi bi-inbox fs-1 text-muted"></i>
                      <p class="mt-3">No predictions found for the selected disaster.</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
    <?php include '../layout/footer.php'; ?>
  </div>

  <!-- Graph Modal -->
  <div class="modal fade" id="graphModal" tabindex="-1" aria-labelledby="graphModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="graphModalLabel">
            <i class="bi bi-graph-up"></i> Evacuation Trend
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <!-- Combined Historical and Forecast Data Graph -->
          <div id="graphContainer" style="position: relative; height: 500px;">
              <canvas id="barangayChart"></canvas>
          </div>
          
          <!-- Monthly Severity Predictions -->
          <div id="monthlySeveritySection" class="mt-4" style="display: none;">
            <hr class="my-4">
            <h6 class="mb-3 text-info">
              <i class="bi bi-calendar-month"></i> Monthly Severity Predictions
            </h6>
            <div id="monthlySeverityContainer" class="row">
              <!-- Monthly severity cards will be inserted here -->
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const select = document.getElementById('disasterSelect');
      const tableBody = document.getElementById('tableBody');
      const table = document.getElementById('barangayTable');
      const noDataMessage = document.getElementById('noDataMessage');
      const allDisasterList = <?php echo json_encode($allDisasterList); ?>;
      
      // Store all rows for filtering
      const allRows = Array.from(tableBody.querySelectorAll('tr'));
      
      function filterTable() {
        const selectedValue = select.value;
        let visibleCount = 0;
        
        // Filter main table
        allRows.forEach(row => {
          if (selectedValue === '__all__') {
            // Show all rows
            row.style.display = '';
            visibleCount++;
          } else {
            // Get the kind_of_disaster name from the selected disaster ID
            const selectedDisasterName = allDisasterList[selectedValue];
            // Get the kind_of_disaster from the row's data attribute
            const rowKindOfDisaster = row.getAttribute('data-kind-of-disaster');
            
            // Show row if its kind_of_disaster matches the selected one
            if (selectedDisasterName && rowKindOfDisaster === selectedDisasterName) {
              row.style.display = '';
              visibleCount++;
            } else {
              row.style.display = 'none';
            }
          }
        });
        
        // Show/hide table and no data message
        if (visibleCount === 0) {
          table.style.display = 'none';
          noDataMessage.style.display = 'block';
        } else {
          table.style.display = '';
          noDataMessage.style.display = 'none';
        }
        
        // Also filter predictions table
        const predictionsTableBody = document.getElementById('predictionsTableBody');
        const predictionsTable = document.getElementById('predictionsTable');
        const noPredictionsMessage = document.getElementById('noPredictionsMessage');
        
        if (predictionsTableBody) {
          const allPredictionRows = Array.from(predictionsTableBody.querySelectorAll('tr'));
          let predictionVisibleCount = 0;
          
          allPredictionRows.forEach(row => {
            if (selectedValue === '__all__') {
              row.style.display = '';
              predictionVisibleCount++;
            } else {
              // Get the kind_of_disaster name from the selected disaster ID
              const selectedDisasterName = allDisasterList[selectedValue];
              // Get the kind_of_disaster from the row's data attribute
              const rowKindOfDisaster = row.getAttribute('data-kind-of-disaster');
              
              // Show row if its kind_of_disaster matches the selected one
              if (selectedDisasterName && rowKindOfDisaster === selectedDisasterName) {
                row.style.display = '';
                predictionVisibleCount++;
              } else {
                row.style.display = 'none';
              }
            }
          });
          
          // Show/hide predictions table and no data message
          if (predictionVisibleCount === 0) {
            if (predictionsTable) predictionsTable.style.display = 'none';
            if (noPredictionsMessage) noPredictionsMessage.style.display = 'block';
          } else {
            if (predictionsTable) predictionsTable.style.display = '';
            if (noPredictionsMessage) noPredictionsMessage.style.display = 'none';
          }
        }
      }
      
      // Add event listener to dropdown
      select.addEventListener('change', filterTable);
      
      // Initial filter (show all by default)
      filterTable();
      
      // Graph functionality
      let barangayChart = null;
      const graphModalElement = document.getElementById('graphModal');
      const graphModal = new bootstrap.Modal(graphModalElement);
      
      /**
       * Function to display the combined graph for a barangay (historical + forecast)
       * @param {string} barangay - Name of the barangay
       * @param {string} disaster - Type of disaster
       * @param {Array} graphData - Array of objects with date and evacuees properties
       * @param {Object} forecastData - Forecast data organized by date: { "date": { "scale": {forecast, lower_bound, upper_bound} } }
       * @param {Object} monthlyForecasts - Monthly forecasts with severity: { "period": {period, date, risk_level, forecast, ...} }
       */
      function displayBarangayGraph(barangay, disaster, graphData, forecastData, monthlyForecasts) {
        // Update modal title
        document.getElementById('graphModalLabel').innerHTML = 
          '<i class="bi bi-graph-up"></i> ' + barangay + ' - ' + disaster;
        
        // Display monthly severity predictions grouped by disaster_id
        displayMonthlySeverity(monthlyForecasts, disaster, allDisasterList);
        
        // Prepare historical data only
        const historicalLabels = graphData.map(item => {
          const date = new Date(item.date);
          return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        });
        const evacueesData = graphData.map(item => item.evacuees);
        
        const datasets = [];
        
        // Historical data dataset only
        if (historicalLabels.length > 0) {
        datasets.push({
          label: 'Historical Evacuees',
          data: evacueesData,
          borderColor: '#007bff',
          backgroundColor: 'rgba(0, 123, 255, 0.1)',
          borderWidth: 3,
          fill: true,
          tension: 0.4,
          pointRadius: 5,
          pointHoverRadius: 7,
          pointBackgroundColor: '#007bff',
          pointBorderColor: '#fff',
          pointBorderWidth: 2
          });
        }
        
        // Destroy existing chart if it exists
        if (barangayChart) {
          barangayChart.destroy();
          barangayChart = null;
        }
        
        // Create chart with historical data only
        const ctx = document.getElementById('barangayChart').getContext('2d');
        barangayChart = new Chart(ctx, {
          type: 'line',
          data: {
            labels: historicalLabels,
            datasets: datasets
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          title: {
            display: true,
                text: 'Historical Evacuation Data',
            font: {
                  size: 16,
                  weight: 'bold'
            },
            padding: {
                  top: 10,
                  bottom: 20
            }
          },
              legend: {
                display: true,
                position: 'top',
                labels: {
                  boxWidth: 12,
                  padding: 10,
                  font: {
                    size: 11
                  }
                }
              },
          tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            titleColor: '#fff',
            bodyColor: '#fff',
            borderColor: '#007bff',
            borderWidth: 1,
            cornerRadius: 8,
            callbacks: {
              label: function(context) {
                const dataset = context.dataset;
                const value = context.formattedValue;
                  return dataset.label + ': ' + value + ' evacuees';
                }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: 'Number of Evacuees',
              font: {
                    size: 12,
                    weight: 'bold'
                  }
            },
            ticks: {
                  callback: function(value) {
                    return value.toLocaleString();
                  }
                }
              },
              x: {
            title: {
              display: true,
              text: 'Date',
              font: {
                    size: 12,
                    weight: 'bold'
                  }
                },
                ticks: {
                  maxRotation: 45,
                  minRotation: 0
                }
              }
            },
            interaction: {
              intersect: false,
              mode: 'index'
            }
          }
        });
        
        // Show modal
        graphModal.show();
      }
      
      /**
       * Function to display monthly severity predictions grouped by disaster_id
       * @param {Object} monthlyForecasts - Monthly forecasts organized by period
       * @param {string} disaster - Disaster type/name
       * @param {Object} allDisasterList - Map of disaster_id to disaster name
       */
      function displayMonthlySeverity(monthlyForecasts, disaster, allDisasterList) {
        const monthlySeveritySection = document.getElementById('monthlySeveritySection');
        const monthlySeverityContainer = document.getElementById('monthlySeverityContainer');
        
        // Handle null, undefined, or empty objects
        if (!monthlyForecasts || 
            monthlyForecasts === null || 
            monthlyForecasts === 'null' ||
            monthlyForecasts === undefined ||
            (typeof monthlyForecasts === 'object' && Object.keys(monthlyForecasts).length === 0)) {
          monthlySeveritySection.style.display = 'none';
          return;
        }
        
        // Additional check: ensure it's a valid object with data
        try {
          const keys = Object.keys(monthlyForecasts);
          if (keys.length === 0) {
            monthlySeveritySection.style.display = 'none';
            return;
          }
        } catch (e) {
          monthlySeveritySection.style.display = 'none';
          return;
        }
        
        // Show the section
        monthlySeveritySection.style.display = 'block';
        monthlySeverityContainer.innerHTML = '';
        
        // Group monthly forecasts by disaster_id
        // Handle both object format (period as key) and array format (with unique keys)
        const forecastsByDisaster = {};
        
        // Convert to array format if needed (in case we have unique keys like "Month 1_disaster_1")
        const forecastsArray = [];
        Object.keys(monthlyForecasts).forEach(key => {
          const monthData = monthlyForecasts[key];
          // Extract period from key if it's in format "Month X_disaster_Y", otherwise use key as period
          let period = key;
          if (key.includes('_disaster_')) {
            period = key.split('_disaster_')[0];
          }
          
          forecastsArray.push({
            period: period,
            ...monthData
          });
        });
        
        // Group by disaster_id
        forecastsArray.forEach(forecastItem => {
          const disasterId = forecastItem.disaster_id !== null && forecastItem.disaster_id !== undefined 
            ? forecastItem.disaster_id 
            : 'null';
          
          if (!forecastsByDisaster[disasterId]) {
            forecastsByDisaster[disasterId] = [];
          }
          
          forecastsByDisaster[disasterId].push(forecastItem);
        });
        
        // Check if there are any forecasts - if not, hide the section
        const disasterIds = Object.keys(forecastsByDisaster);
        if (disasterIds.length === 0) {
          monthlySeveritySection.style.display = 'none';
          return;
        }
        
        // Filter out disasters that have no forecasts (empty arrays)
        const disastersWithForecasts = {};
        Object.keys(forecastsByDisaster).forEach(disasterId => {
          if (forecastsByDisaster[disasterId] && forecastsByDisaster[disasterId].length > 0) {
            disastersWithForecasts[disasterId] = forecastsByDisaster[disasterId];
          }
        });
        
        // If no disasters have forecasts, hide the section
        if (Object.keys(disastersWithForecasts).length === 0) {
          monthlySeveritySection.style.display = 'none';
          return;
        }
        
        // Risk level colors and icons
        const riskConfig = {
          'Low': {
            color: 'success',
            bgColor: '#d4edda',
            borderColor: '#28a745',
            icon: 'bi-check-circle',
            label: 'Low Risk'
          },
          'Medium': {
            color: 'warning',
            bgColor: '#fff3cd',
            borderColor: '#ffc107',
            icon: 'bi-exclamation-triangle',
            label: 'Medium Risk'
          },
          'High': {
            color: 'danger',
            bgColor: '#f8d7da',
            borderColor: '#dc3545',
            icon: 'bi-exclamation-circle',
            label: 'High Risk'
          }
        };
        
        // Check again if we have any disasters with forecasts after filtering
        const finalDisasterIds = Object.keys(disastersWithForecasts).filter(disasterId => {
          const forecasts = disastersWithForecasts[disasterId];
          return forecasts && forecasts.length > 0 && forecasts.some(f => f.forecast !== null && f.forecast !== undefined);
        });
        
        if (finalDisasterIds.length === 0) {
          monthlySeveritySection.style.display = 'none';
          return;
        }
        
        // Display forecasts grouped by disaster_id (only for disasters with forecasts)
        finalDisasterIds.sort().forEach(disasterId => {
          const disasterForecasts = disastersWithForecasts[disasterId];
          
          // Double-check that this disaster has valid forecasts
          const validForecasts = disasterForecasts.filter(f => 
            f && 
            f.forecast !== null && 
            f.forecast !== undefined && 
            f.risk_level &&
            f.period
          );
          
          if (validForecasts.length === 0) {
            return; // Skip this disaster if it has no valid forecasts
          }
          
          // Get disaster name
          const disasterName = (disasterId !== 'null' && allDisasterList && allDisasterList[disasterId]) 
            ? allDisasterList[disasterId] 
            : (disaster || 'Unknown Disaster');
          
          // Create disaster section header
          const disasterSection = document.createElement('div');
          disasterSection.className = 'mb-4';
          disasterSection.innerHTML = `
            <div class="card mb-3">
              <div class="card-header bg-secondary text-white">
                <h6 class="mb-0">
                  <i class="bi bi-shield-exclamation"></i> ${disasterName}
                  ${disasterId !== 'null' ? `<span class="badge bg-light text-dark ms-2">ID: ${disasterId}</span>` : ''}
                </h6>
              </div>
              <div class="card-body">
                <div class="row" id="disaster-${disasterId}-forecasts">
                  <!-- Monthly cards will be inserted here -->
                </div>
              </div>
            </div>
          `;
          monthlySeverityContainer.appendChild(disasterSection);
          
          // Get container for this disaster
          const disasterContainer = document.getElementById(`disaster-${disasterId}-forecasts`);
          
          // Sort months by period
          validForecasts.sort((a, b) => {
            const monthA = parseInt(a.period.replace('Month ', ''));
            const monthB = parseInt(b.period.replace('Month ', ''));
            return monthA - monthB;
          });
          
          // Create cards for each month in this disaster
          validForecasts.forEach(monthData => {
            const riskLevel = monthData.risk_level || 'Medium';
            const config = riskConfig[riskLevel] || riskConfig['Medium'];
            
            // Extract just the month name from period (e.g., "January 2024" -> "January")
            let monthName = monthData.period || '';
            if (monthName.includes(' ')) {
              // Split by space and take the first part (month name)
              monthName = monthName.split(' ')[0];
            }
            
            // If period is not in expected format, try to get month from date
            if (!monthName || monthName === '') {
              const forecastDate = new Date(monthData.date);
              monthName = forecastDate.toLocaleDateString('en-US', { month: 'long' });
            }
            
            // Create card
            const card = document.createElement('div');
            card.className = 'col-md-4 mb-3';
            card.innerHTML = `
              <div class="card h-100 border-${config.color}" style="border-width: 2px !important;">
                <div class="card-body" style="background-color: ${config.bgColor};">
                  <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                      <i class="bi ${config.icon} text-${config.color}"></i> ${monthName}
                    </h5>
                    <span class="badge bg-${config.color} fs-6">${config.label}</span>
                  </div>
                </div>
              </div>
            `;
            
            disasterContainer.appendChild(card);
          });
        });
      }
      
      /**
       * Function to destroy the chart when modal is closed
       */
      function destroyChart() {
        if (barangayChart) {
          barangayChart.destroy();
          barangayChart = null;
        }
      }
      
      // Use event delegation for dynamically added buttons
      tableBody.addEventListener('click', function(e) {
        if (e.target.closest('.view-graph-btn')) {
          const button = e.target.closest('.view-graph-btn');
          const barangay = button.getAttribute('data-barangay');
          const disaster = button.getAttribute('data-disaster');
          const graphDataStr = button.getAttribute('data-graph-data');
          const forecastDataStr = button.getAttribute('data-forecast-data');
          const monthlyForecastsStr = button.getAttribute('data-monthly-forecasts');
          
          // Parse graph data
          const graphData = JSON.parse(graphDataStr);
          const forecastData = forecastDataStr ? JSON.parse(forecastDataStr) : null;
          const monthlyForecasts = monthlyForecastsStr ? JSON.parse(monthlyForecastsStr) : null;
          
          // Call the function to display the graph
          displayBarangayGraph(barangay, disaster, graphData, forecastData, monthlyForecasts);
        }
      });
      
      // Destroy chart when modal is closed
      graphModalElement.addEventListener('hidden.bs.modal', destroyChart);
    });
  </script>
</body>

</html>
</html>