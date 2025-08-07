<?php
include '../../../database/session.php';
include '../layout/head_links.php';

$currentUser = $_SESSION['admin_id'];
$userRole = $_SESSION['role'];
$evacRegData = [];

if (trim(strtolower($userRole)) === 'staff') {
    // Fetch assigned evacuation location name for the current staff user
    $locQuery = $conn->prepare("SELECT name FROM admin_table 
        LEFT JOIN evac_loc_table ON admin_table.evac_loc_id = evac_loc_table.evac_loc_id
        WHERE admin_id = ?");
    $locQuery->bind_param("s", $currentUser);
    $locQuery->execute();
    $locResult = $locQuery->get_result();

    if ($locRow = $locResult->fetch_assoc()) {
        $assignedEvacLocName = $locRow['name'];
        $_SESSION['name'] = $assignedEvacLocName;

        $stmt = $conn->prepare("
            SELECT * FROM evac_reg_table
            LEFT JOIN evac_loc_table AS evc ON evac_reg_table.evac_loc_id = evc.evac_loc_id
            LEFT JOIN pre_reg_table AS pr ON evac_reg_table.pre_reg_id = pr.pre_reg_id
            WHERE evc.name = ?
        ");
        $stmt->bind_param("s", $assignedEvacLocName);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = false;
    }
} else {
    // Admin can see all locations
    $query = "
            SELECT * FROM evac_reg_table
            LEFT JOIN evac_loc_table AS evc ON evac_reg_table.evac_loc_id = evc.evac_loc_id
            LEFT JOIN pre_reg_table AS pr ON evac_reg_table.pre_reg_id = pr.pre_reg_id
    ";
    $result = mysqli_query($conn, $query);
}

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $evacRegData[] = $row;
    }
}

// Count Solo, Family (with size), and Unique PreReg per location
$locationCounts = [];
$preRegIdsByLoc = [];

foreach ($evacRegData as $row) {
    $locName = $row['name'] ?? 'Unknown';
    $regType = $row['registered_as'];
    $preRegId = $row['pre_reg_id'];

    // Initialize location
    if (!isset($locationCounts[$locName])) {
        $locationCounts[$locName] = ['Solo' => 0, 'Family' => 0];
        $preRegIdsByLoc[$locName] = [];
    }

    // Count Solo
    if ($regType === 'Solo') {
        $locationCounts[$locName]['Solo']++;
    }
    // Count Family (add by size if available)
    elseif ($regType === 'Family') {
        $familySize = (isset($row['family_count']) && is_numeric($row['family_count'])) ? (int)$row['family_count'] : 1;
        $locationCounts[$locName]['Family'] += $familySize;
    }

    // Track unique pre_reg_id for total evacuees per location
    if (!empty($preRegId)) {
        $preRegIdsByLoc[$locName][$preRegId] = true;
    }
}

// Prepare chart data
$chartLabels = array_keys($locationCounts);
$soloData = [];
$familyData = [];
$totalEvacueesPerLoc = [];

foreach ($locationCounts as $locName => $counts) {
    $soloData[] = $counts['Solo'];
    $familyData[] = $counts['Family'];
    $totalEvacueesPerLoc[] = count($preRegIdsByLoc[$locName] ?? []);
}

// Calculate total unique evacuees
$uniquePreRegIds = [];
foreach ($evacRegData as $row) {
    if (!empty($row['pre_reg_id'])) {
        $uniquePreRegIds[$row['pre_reg_id']] = true;
    }
}
$totalEvacuees = count($uniquePreRegIds);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Evacuation Registration Records</title>
    <style>
        .table-responsive {
            max-height: 400px;
            overflow-y: auto;
        }
        #evacRegTable thead th {
            position: sticky;
            top: 0;
            z-index: 10;
            background: #d1e7dd;
        }
    </style>
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
<div class="app-wrapper">
    <?php include '../layout/header.php'; ?>
    <?php include '../layout/sidebar.php'; ?>

    <main class="app-main">
        <div class="app-content-header">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6 d-flex align-items-center gap-2">
                        <i class="bi bi-clipboard-data fs-2 text-info"></i>
                        <h3 class="mb-0">Evacuation Registration Records</h3>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="../dashboard/">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Evacuation Records</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="content">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-12">
            <div class="card shadow border-0 rounded-4 overflow-hidden" style="background: linear-gradient(135deg, #f0f4f8 60%, #e3f2fd 100%);">
                <!-- Header -->
                <div class="card-header bg-white border-0 rounded-top-4 px-4 py-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-bar-chart-fill fs-3 text-primary"></i>
                        <span class="fw-semibold fs-5 text-dark">Evacuee Statistics</span>
                    </div>
                    <span class="badge bg-primary bg-opacity-25 text-primary fw-semibold px-3 py-2">Live Data</span>
                </div>

                <!-- Total Evacuees -->
                <!-- <div class="px-4 pt-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="fs-6 text-muted">Total Unique Evacuees:</span>
                        <span class="fw-bold fs-4 text-success"><?php echo $totalEvacuees; ?></span>
                    </div>
                </div> -->

                <!-- Chart -->
                <div class="card-body px-4 py-3">
                    <div class="chart-container" style="position: relative; height: 350px;">
                        <canvas id="evacuationChart" style="height: 100%; width: 100%;"></canvas>
                    </div>
                    <!-- Totals below the bar chart, color-matched to bar colors -->
                    <div class="d-flex justify-content-center align-items-center gap-3 mt-3">
                        <span class="badge" style="background-color: #4e73df; color: #fff; font-size: 1rem; min-width: 120px;">Solo: <span class="fw-bold ms-1"><?php echo array_sum($soloData); ?></span></span>
                        <span class="badge" style="background-color: #1cc88a; color: #fff; font-size: 1rem; min-width: 120px;">Family: <span class="fw-bold ms-1"><?php echo array_sum($familyData); ?></span></span>
                        <span class="badge" style="background-color: #f6c23e; color: #fff; font-size: 1rem; min-width: 120px;">Total Evacuess: <span class="fw-bold ms-1"><?php echo $totalEvacuees; ?></span></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
  
    </main>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const chartLabels = <?php echo json_encode($chartLabels); ?>;
    const soloData = <?php echo json_encode($soloData); ?>;
    const familyData = <?php echo json_encode($familyData); ?>;
    const totalEvacueesPerLoc = <?php echo json_encode($totalEvacueesPerLoc); ?>;

    const maxY = Math.max(...soloData, ...familyData, ...totalEvacueesPerLoc);

    let stepSize;
    let maxLimit;
    if (maxY <= 10) {
        stepSize = 1;
        maxLimit = 10;
    } else if (maxY <= 50) {
        stepSize = 5;
        maxLimit = Math.ceil((maxY + 5) / 5) * 5;
    } else {
        stepSize = 10;
        maxLimit = Math.ceil((maxY + 10) / 10) * 10;
    }

    const ctx = document.getElementById('evacuationChart').getContext('2d');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartLabels,
            datasets: [
                {
                    label: 'Solo Evacuees',
                    data: soloData,
                    backgroundColor: '#4e73df'
                },
                {
                    label: 'Family Evacuees',
                    data: familyData,
                    backgroundColor: '#1cc88a'
                },
                {
                    label: 'Total Unique Evacuees',
                    data: totalEvacueesPerLoc,
                    backgroundColor: '#f6c23e'
                }
            ]
        },
        options: {
            responsive: true,
            layout: {
                padding: {
                    top: 10
                }
            },
            interaction: {
                mode: 'nearest',
                intersect: true
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Evacuation Center'
                    }
                },
                y: {
                    beginAtZero: true,
                    max: maxLimit,
                    ticks: {
                        stepSize: stepSize
                    },
                    title: {
                        display: true,
                        text: 'Evacuee Count'
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top'
                },
                tooltip: {
                    mode: 'nearest',
                    intersect: true
                }
            }
        }
    });
</script>


    <?php include '../layout/footer.php'; ?>
</div>

<script src="../scripts/scripts.js"></script>
<script>
    // Search filter
    document.getElementById('searchBox').addEventListener('keyup', function () {
        const term = this.value.toLowerCase();
        document.querySelectorAll('#evacRegTable tbody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
        });
    });
</script>
</body>
    <style>
        .table-responsive {
            max-height: 400px;
            overflow-y: auto;
        }
        #evacRegTable thead th {
            position: sticky;
            top: 0;
            z-index: 10;
            background: #d1e7dd;
        }
        .card {
            box-shadow: 0 6px 32px 0 rgba(33, 150, 243, 0.10), 0 1.5px 6px 0 rgba(33, 150, 243, 0.08);
            border-radius: 1.5rem;
            border: none;
        }
        .card-header {
            background: #fff;
            border-bottom: 1px solid #e3e3e3;
            border-radius: 1.5rem 1.5rem 0 0;
        }
        .card-body {
            background: linear-gradient(135deg, #f8fafc 60%, #e3f2fd 100%);
            border-radius: 0 0 1.5rem 1.5rem;
        }
        .badge.bg-gradient-primary {
            background: linear-gradient(90deg, #43e97b 0%, #38f9d7 100%) !important;
            color: #1976d2 !important;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
    </style>
