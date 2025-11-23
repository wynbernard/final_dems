<?php
include '../../../database/session.php';
include '../layout/head_links.php';

// Load barangay boundaries for disaster-prone types
$boundaryFile = dirname(dirname(dirname(__DIR__))) . '/address_json/barangay_boundaries.json';
$barangayBoundaries = [];
if (file_exists($boundaryFile)) {
    $boundaryData = @file_get_contents($boundaryFile);
    if ($boundaryData !== false) {
        $decoded = json_decode($boundaryData, true);
        if (is_array($decoded)) {
            $barangayBoundaries = $decoded;
        }
    }
}

// Extract all unique disaster-prone types from barangay_boundaries.json
$allProneTypes = [];
foreach ($barangayBoundaries as $barangayName => $barangayData) {
    if (isset($barangayData['disaster_prone_types']) && is_array($barangayData['disaster_prone_types'])) {
        foreach ($barangayData['disaster_prone_types'] as $proneType) {
            if (!in_array($proneType, $allProneTypes)) {
                $allProneTypes[] = $proneType;
            }
        }
    }
}
sort($allProneTypes);

// Function to get affected barangays for a specific prone type
function getAffectedBarangaysByProneType($proneType, $barangayBoundaries) {
    $affectedBarangays = [];
    
    foreach ($barangayBoundaries as $barangayName => $barangayData) {
        if (isset($barangayData['disaster_prone_types']) && is_array($barangayData['disaster_prone_types'])) {
            if (in_array($proneType, $barangayData['disaster_prone_types'])) {
                $affectedBarangays[] = $barangayName;
            }
        }
    }
    
    return $affectedBarangays;
}

// Fetch barangay disaster records from disaster_occurrence_history
$query = "
 SELECT 
    history_id, 
    disaster_id, 
    barangay_id, 
    barangay_name, 
    disaster_type, 
    severity_scale, 
    total_affected, 
    record_date, 
    rainfall_mm, 
    wind_speed_kph, 
    temperature_c
 FROM disaster_occurrence_history
 WHERE 1=1
 ORDER BY barangay_name ASC, record_date ASC
";
$result = mysqli_query($conn, $query);

// Build data grouped by barangay (aggregate total affected per barangay)
$dataByBarangay = []; // barangay_name => total_affected
$barangayList = []; // List of all unique barangays
$disasterTypesByBarangay = []; // barangay_name => [disaster_type => total_affected]

while ($row = mysqli_fetch_assoc($result)) {
  $barangay = $row['barangay_name'];
  $disasterType = $row['disaster_type'];
  $totalAffected = (int)$row['total_affected'];

  // Add to barangay list
  if (!in_array($barangay, $barangayList)) {
    $barangayList[] = $barangay;
  }

  // Aggregate total affected per barangay
  if (!isset($dataByBarangay[$barangay])) {
    $dataByBarangay[$barangay] = 0;
  }
  $dataByBarangay[$barangay] += $totalAffected;

  // Group by disaster type per barangay
  if (!isset($disasterTypesByBarangay[$barangay])) {
    $disasterTypesByBarangay[$barangay] = [];
  }
  if (!isset($disasterTypesByBarangay[$barangay][$disasterType])) {
    $disasterTypesByBarangay[$barangay][$disasterType] = 0;
  }
  $disasterTypesByBarangay[$barangay][$disasterType] += $totalAffected;
}

// Get all barangays from JSON file (to include those without data yet)
$allBarangaysFromJSON = array_keys($barangayBoundaries);
sort($allBarangaysFromJSON);

// Merge: include all barangays from JSON, but prioritize those with data
$barangayList = array_unique(array_merge($barangayList, $allBarangaysFromJSON));
sort($barangayList);

// Prepare datasets for chart (barangays on x-axis, total affected on y-axis)
$barangayDatasets = [
  [
    'label' => 'Total Affected',
    'data' => array_map(function($barangay) use ($dataByBarangay) {
      return $dataByBarangay[$barangay] ?? 0;
    }, $barangayList)
  ]
];

// Also prepare datasets by disaster type
$allDisasterTypes = [];
foreach ($disasterTypesByBarangay as $barangay => $disasters) {
  foreach (array_keys($disasters) as $disasterType) {
    if (!in_array($disasterType, $allDisasterTypes)) {
      $allDisasterTypes[] = $disasterType;
    }
  }
}
sort($allDisasterTypes);

$disasterDatasets = [];
foreach ($allDisasterTypes as $disasterType) {
  $series = [];
  foreach ($barangayList as $barangay) {
    $series[] = $disasterTypesByBarangay[$barangay][$disasterType] ?? 0;
  }
  $disasterDatasets[] = [
    'label' => $disasterType,
    'data' => $series
  ];
}

// For barangay-based chart, we don't need forecast dates
// We'll use barangay names as labels instead

// Prepare prone type to affected barangays mapping for JavaScript
$proneTypeAffectedBarangays = [];
foreach ($allProneTypes as $proneType) {
    $affected = getAffectedBarangaysByProneType($proneType, $barangayBoundaries);
    $proneTypeAffectedBarangays[$proneType] = $affected;
}

// Fetch all disaster occurrence history data for the table
$historyQuery = "
 SELECT 
    history_id, 
    disaster_id, 
    barangay_id, 
    barangay_name, 
    disaster_type, 
    severity_scale, 
    total_affected, 
    record_date, 
    rainfall_mm, 
    wind_speed_kph, 
    temperature_c
 FROM disaster_occurrence_history
 WHERE 1=1
 ORDER BY record_date DESC, barangay_name ASC
";
$historyResult = mysqli_query($conn, $historyQuery);
$historyData = [];
while ($row = mysqli_fetch_assoc($historyResult)) {
    $historyData[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Barangay Record</title>
  <?php include '../layout/head_links.php'; ?>
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
                        <label for="disasterSelect" class="mb-0 fw-semibold text-dark">Filter by Disaster-Prone Type:</label>
                        <select id="disasterSelect" class="form-select w-auto">
                          <option value="__all__">🌍 All Prone Types</option>
                          <?php
                          // build a list of disaster-prone types from JSON
                          foreach ($allProneTypes as $proneType) {
                            $barangayCount = count($proneTypeAffectedBarangays[$proneType]);
                            echo '<option value="' . htmlspecialchars($proneType) . '">⚠️ ' . htmlspecialchars($proneType) . ' (' . $barangayCount . ' barangay' . ($barangayCount > 1 ? 's' : '') . ')</option>';
                          }
                          ?>
                        </select>
                      </div>
                    </div>
                    <div class="col-md-6 text-end">
                      <div class="d-flex align-items-center justify-content-end gap-2">
                        <span id="affectedBarangaysCount" class="badge bg-info" style="display: none;"></span>
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- Table for Barangays with Selected Prone Type -->
                <div class="table-responsive mt-3">
                  <table id="barangayTable" class="table table-striped table-hover table-bordered">
                    <thead class="table-success sticky-header">
                      <tr>
                        <th>No.</th>
                        <th><i class="bi bi-geo-alt-fill"></i> Barangay Name</th>
                        <th><i class="bi bi-exclamation-triangle-fill"></i> Disaster-Prone Types</th>
                        <th><i class="bi bi-exclamation-triangle-fill"></i> Disaster Type</th>
                        <th><i class="bi bi-exclamation-triangle-fill"></i> Severity Scale</th>
                        <th><i class="bi bi-people-fill"></i> Total Affected</th>
                        <th><i class="bi bi-exclamation-triangle-fill"></i> Record Date</th>
                        <th><i class="bi bi-exclamation-triangle-fill"></i> Rainfall (mm)</th>
                        <th><i class="bi bi-exclamation-triangle-fill"></i> Wind Speed (kph)</th>
                        <th><i class="bi bi-exclamation-triangle-fill"></i> Temperature (°C)</th>
                      </tr>
                    </thead>
                    <tbody id="barangayTableBody">
                      <tr>
                        <td colspan="10" class="text-center text-muted">
                          <i class="bi bi-info-circle me-2"></i>
                          Select a disaster-prone type to view affected barangays
                        </td>
                      </tr>
                    </tbody>
                  </table>
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
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {

    // Get all barangays from JSON file (this is the source of truth)
    const allBarangaysFromJSON = <?php echo json_encode($allBarangaysFromJSON); ?>;
    
    // Barangay boundaries from JSON (for prone type lookup)
    const barangayBoundariesJSON = <?php echo json_encode($barangayBoundaries); ?>;
    
    // Disaster-prone types and affected barangays from JSON
    const proneTypeAffectedBarangays = <?php echo json_encode($proneTypeAffectedBarangays); ?>;
    const allProneTypes = <?php echo json_encode($allProneTypes); ?>;
    
    // Disaster occurrence history data for table
    const historyData = <?php echo json_encode($historyData); ?>;
    
    // Function to update the table based on selected prone type - show barangays from JSON
    function updateBarangayTable(selectedProneType) {
      const tbody = document.getElementById('barangayTableBody');
      tbody.innerHTML = '';
      
      if (selectedProneType === '__all__') {
        // Show all barangays from JSON with their prone types
        let rowNum = 1;
        allBarangaysFromJSON.forEach(barangayName => {
          const barangayInfo = barangayBoundariesJSON[barangayName] || {};
          const proneTypes = barangayInfo.disaster_prone_types || [];
          const proneTypesDisplay = proneTypes.length > 0 
            ? proneTypes.map(type => `<span class="badge bg-info me-1">${escapeHtml(type)}</span>`).join('')
            : '<span class="text-muted">None</span>';
          
          // Get records for this barangay
          const barangayRecords = historyData.filter(r => r.barangay_name === barangayName);
          
          if (barangayRecords.length === 0) {
            // Show barangay even if no records
            const row = document.createElement('tr');
            row.innerHTML = `
              <td>${rowNum++}</td>
              <td><strong>${escapeHtml(barangayName)}</strong></td>
              <td>${proneTypesDisplay}</td>
              <td class="text-muted">No records</td>
              <td>-</td>
              <td>-</td>
              <td>-</td>
              <td>-</td>
              <td>-</td>
              <td>-</td>
            `;
            tbody.appendChild(row);
          } else {
            // Show each record for this barangay
            barangayRecords.forEach(record => {
              const row = document.createElement('tr');
              row.innerHTML = `
                <td>${rowNum++}</td>
                <td><strong>${escapeHtml(record.barangay_name)}</strong></td>
                <td>${proneTypesDisplay}</td>
                <td>${escapeHtml(record.disaster_type || 'N/A')}</td>
                <td><span class="badge bg-${getSeverityBadgeColor(record.severity_scale)}">${escapeHtml(record.severity_scale || 'N/A')}</span></td>
                <td><strong>${parseInt(record.total_affected || 0).toLocaleString()}</strong></td>
                <td>${formatDate(record.record_date)}</td>
                <td>${record.rainfall_mm ? parseFloat(record.rainfall_mm).toFixed(2) : 'N/A'}</td>
                <td>${record.wind_speed_kph ? parseFloat(record.wind_speed_kph).toFixed(2) : 'N/A'}</td>
                <td>${record.temperature_c ? parseFloat(record.temperature_c).toFixed(1) : 'N/A'}</td>
              `;
              tbody.appendChild(row);
            });
          }
        });
        
        // Hide count badge when showing all
        const countBadge = document.getElementById('affectedBarangaysCount');
        if (countBadge) {
          countBadge.style.display = 'none';
        }
        
        if (allBarangaysFromJSON.length === 0) {
          tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted">No barangays found in JSON file</td></tr>';
        }
      } else {
        // Filter by prone type - show only barangays with selected prone type from JSON
        const affectedBarangays = proneTypeAffectedBarangays[selectedProneType] || [];
        
        if (affectedBarangays.length === 0) {
          tbody.innerHTML = `<tr><td colspan="10" class="text-center text-muted">No barangays found with ${selectedProneType}</td></tr>`;
        } else {
          let rowNum = 1;
          affectedBarangays.forEach(barangayName => {
            const barangayInfo = barangayBoundariesJSON[barangayName] || {};
            const proneTypes = barangayInfo.disaster_prone_types || [];
            const proneTypesDisplay = proneTypes.length > 0 
              ? proneTypes.map(type => `<span class="badge bg-info me-1">${escapeHtml(type)}</span>`).join('')
              : '<span class="text-muted">None</span>';
            
            // Get records for this barangay
            const barangayRecords = historyData.filter(r => r.barangay_name === barangayName);
            
            if (barangayRecords.length === 0) {
              // Show barangay even if no records
              const row = document.createElement('tr');
              row.innerHTML = `
                <td>${rowNum++}</td>
                <td><strong>${escapeHtml(barangayName)}</strong></td>
                <td>${proneTypesDisplay}</td>
                <td class="text-muted">No records</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
              `;
              tbody.appendChild(row);
              } else {
              // Show each record for this barangay
              barangayRecords.forEach(record => {
                const row = document.createElement('tr');
                row.innerHTML = `
                  <td>${rowNum++}</td>
                  <td><strong>${escapeHtml(record.barangay_name)}</strong></td>
                  <td>${proneTypesDisplay}</td>
                  <td>${escapeHtml(record.disaster_type || 'N/A')}</td>
                  <td><span class="badge bg-${getSeverityBadgeColor(record.severity_scale)}">${escapeHtml(record.severity_scale || 'N/A')}</span></td>
                  <td><strong>${parseInt(record.total_affected || 0).toLocaleString()}</strong></td>
                  <td>${formatDate(record.record_date)}</td>
                  <td>${record.rainfall_mm ? parseFloat(record.rainfall_mm).toFixed(2) : 'N/A'}</td>
                  <td>${record.wind_speed_kph ? parseFloat(record.wind_speed_kph).toFixed(2) : 'N/A'}</td>
                  <td>${record.temperature_c ? parseFloat(record.temperature_c).toFixed(1) : 'N/A'}</td>
                `;
                tbody.appendChild(row);
                });
              }
            });
          }
      }
    }
    
    // Helper functions
    function escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }
    
    function formatDate(dateString) {
      if (!dateString) return 'N/A';
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
    }
    
    function getSeverityBadgeColor(scale) {
      if (!scale) return 'secondary';
      const scaleLower = scale.toString().toLowerCase();
      
      // Handle text-based severity scales
      if (scaleLower.includes('low') || scaleLower.includes('minor')) {
        return 'success'; // Green for low severity
      }
      if (scaleLower.includes('moderate') || scaleLower.includes('medium')) {
        return 'warning'; // Yellow for moderate severity
      }
      if (scaleLower.includes('high') || scaleLower.includes('severe') || scaleLower.includes('extreme')) {
        return 'danger'; // Red for high/severe severity
      }
      
      // Handle numeric ranges (fallback for old data format)
      if (scale.includes('-')) {
        const scaleNum = parseInt(scale.split('-')[0]);
        if (scaleNum >= 1 && scaleNum <= 3) return 'success';
        if (scaleNum >= 4 && scaleNum <= 7) return 'warning';
        if (scaleNum >= 8 && scaleNum <= 10) return 'danger';
      }
      
      return 'secondary';
    }

    const disasterSelect = document.getElementById('disasterSelect');
    let selectedProneType = disasterSelect.value;
    
    // Initialize table on page load
    updateBarangayTable(selectedProneType);
    
    // Dropdown filter logic - filter by prone type
    disasterSelect.addEventListener('change', function() {
      const val = this.value;
      selectedProneType = val;
      
      // Update the table
      updateBarangayTable(val);
    });
    });
  </script>
</body>

</html>