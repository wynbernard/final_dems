<?php
include '../../../database/session.php';
include '../layout/head_links.php';

$currentUser = $_SESSION['admin_id'];
$userRole = $_SESSION['role'];
$evacRegData = [];

// Handle search for admin
$searchLocation = isset($_GET['search_location']) ? trim($_GET['search_location']) : '';

if (trim(strtolower($userRole)) === 'staff') {
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
            LEFT JOIN age_class_table AS ac ON pr.age_class_id = ac.age_class_id
            WHERE evc.name = ?
        ");
        $stmt->bind_param("s", $assignedEvacLocName);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = false;
    }
} else {
    // Admin can see all locations, with optional search
    if ($searchLocation !== '') {
        $query = "
            SELECT * FROM evac_reg_table
            LEFT JOIN evac_loc_table AS evc ON evac_reg_table.evac_loc_id = evc.evac_loc_id
            LEFT JOIN pre_reg_table AS pr ON evac_reg_table.pre_reg_id = pr.pre_reg_id
            LEFT JOIN age_class_table AS ac ON pr.age_class_id = ac.age_class_id
            WHERE evc.name LIKE ?
        ";
        $stmt = $conn->prepare($query);
        $likeSearch = "%$searchLocation%";
        $stmt->bind_param("s", $likeSearch);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $query = "
            SELECT * FROM evac_reg_table
            LEFT JOIN evac_loc_table AS evc ON evac_reg_table.evac_loc_id = evc.evac_loc_id
            LEFT JOIN pre_reg_table AS pr ON evac_reg_table.pre_reg_id = pr.pre_reg_id
            LEFT JOIN age_class_table AS ac ON pr.age_class_id = ac.age_class_id
        ";
        $result = mysqli_query($conn, $query);
    }
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
    $relation = $row['relation_to_family'] ?? '';

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
    elseif ($regType === 'Family' && $relation === 'Head of Family') {
        $familySize = (isset($row['family_count']) && is_numeric($row['family_count'])) ? (int)$row['family_count'] : 1;
        $locationCounts[$locName]['Family'] += $familySize;
    }

    // Track unique pre_reg_id for total evacuees per location
    if (!empty($preRegId)) {
        $preRegIdsByLoc[$locName][$preRegId] = true;
    }
}
// Calculate total unique evacuees
$uniquePreRegIds = [];
foreach ($evacRegData as $row) {
    if (!empty($row['pre_reg_id'])) {
        $uniquePreRegIds[$row['pre_reg_id']] = true;
    }
}

// Compute gender counts for unique pre_reg_ids
$maleCount = 0;
$femaleCount = 0;
if (!empty($uniquePreRegIds)) {
    $ids = array_keys($uniquePreRegIds);
    // prepare IN clause safely
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));
    $sql = "SELECT gender, COUNT(*) as cnt FROM pre_reg_table WHERE pre_reg_id IN ($placeholders) GROUP BY gender";
    $stmtG = $conn->prepare($sql);
    if ($stmtG) {
        // bind params dynamically
        $refs = [];
        foreach ($ids as $k => $v) $refs[] = &$ids[$k];
        array_unshift($refs, $types);
        call_user_func_array([$stmtG, 'bind_param'], $refs);
        $stmtG->execute();
        $gres = $stmtG->get_result();
        while ($grow = $gres->fetch_assoc()) {
            $g = strtolower(trim($grow['gender']));
            if ($g === 'male') $maleCount = intval($grow['cnt']);
            elseif ($g === 'female') $femaleCount = intval($grow['cnt']);
        }
        $stmtG->close();
    }
}


// Prepare chart data
if (trim(strtolower($userRole)) === 'admin') {
    if ($searchLocation !== '' && count($locationCounts) === 1) {
        // If searching and only one location matches, show that location as a single bar
        $chartLabels = array_keys($locationCounts);
        $soloData = [reset($locationCounts)["Solo"]];
        $familyData = [reset($locationCounts)["Family"]];
        $totalEvacueesPerLoc = [count(reset($preRegIdsByLoc))];
    } else {
        // Merge all locations for admin: show only one bar for Solo and one for Family
        $chartLabels = ['All Locations'];
        $soloData = [array_sum(array_column($locationCounts, 'Solo'))];
        $familyData = [array_sum(array_column($locationCounts, 'Family'))];
        $totalEvacueesPerLoc = [count($uniquePreRegIds)];
    }
} else {
    $chartLabels = array_keys($locationCounts);
    $soloData = [];
    $familyData = [];
    $totalEvacueesPerLoc = [];
    foreach ($locationCounts as $locName => $counts) {
        $soloData[] = $counts['Solo'];
        $familyData[] = $counts['Family'];
        $totalEvacueesPerLoc[] = count($preRegIdsByLoc[$locName] ?? []);
    }
}


$totalEvacuees = count($uniquePreRegIds);


// Age classification aggregation based on DOB
$allAgeClasses = [
    'Infant',
    'Toddler',
    'Pre-School',
    'School-Age',
    'Teenage',
    'Adult',
    'Senior'
];
$ageGroups = array_fill_keys($allAgeClasses, 0);

foreach ($evacRegData as $row) {
    if (!empty($row['date_of_birth'])) {
        $dob = new DateTime($row['date_of_birth']);
        $today = new DateTime();
        $age = $dob->diff($today)->y; // age in years

        // Classification based on age
        if ($age <= 1) {
            $class = 'Infant';
        } elseif ($age >= 2 && $age <= 3) {
            $class = 'Toddler';
        } elseif ($age >= 4 && $age <= 6) {
            $class = 'Pre-School';
        } elseif ($age >= 7 && $age <= 12) {
            $class = 'School-Age';
        } elseif ($age >= 13 && $age <= 19) {
            $class = 'Teenage';
        } elseif ($age >= 20 && $age <= 59) {
            $class = 'Adult';
        } else {
            $class = 'Senior';
        }
    } else {
        $class = 'Unknown'; // fallback if dob is missing
    }

    if (isset($ageGroups[$class])) {
        $ageGroups[$class]++;
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <title>Evacuation Registration Records</title>
    <style>
        .card .shadow-sm:hover {
            transform: translateY(-2px);
            transition: 0.2s ease-in-out;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
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
                <?php if (trim(strtolower($userRole)) === 'admin'): ?>
                    <?php
                    // Get all unique locations for datalist
                    $allLocations = array_unique(array_map(function ($row) {
                        return $row['name'] ?? '';
                    }, $evacRegData));
                    $allLocations = array_filter($allLocations, fn($v) => $v !== '');
                    ?>
                    <form id="searchForm" method="get" class="mb-3" autocomplete="off">
                        <div class="row justify-content-end">
                            <div class="col-md-4 col-lg-3 position-relative">
                                <input type="text" name="search_location" id="search_location" class="form-control" placeholder="Search location..." value="<?php echo htmlspecialchars($searchLocation); ?>" autocomplete="off">
                                <div id="locationSuggestions" class="list-group position-absolute w-100" style="z-index: 1000; display: none; max-height: 200px; overflow-y: auto;"></div>
                            </div>
                        </div>
                    </form>
                    <script>
                        // Live suggestions and real-time search for admin search box
                        const allLocations = <?php echo json_encode(array_values($allLocations)); ?>;
                        const input = document.getElementById('search_location');
                        const suggestions = document.getElementById('locationSuggestions');
                        const form = document.getElementById('searchForm');
                        let debounceTimer;

                        input.addEventListener('input', function() {
                            const value = this.value.trim().toLowerCase();
                            suggestions.innerHTML = '';
                            if (value.length === 0) {
                                suggestions.style.display = 'none';
                                clearTimeout(debounceTimer);
                                return;
                            }
                            const matches = allLocations.filter(loc => loc.toLowerCase().includes(value));
                            if (matches.length === 0) {
                                suggestions.style.display = 'none';
                            } else {
                                matches.forEach(loc => {
                                    const item = document.createElement('button');
                                    item.type = 'button';
                                    item.className = 'list-group-item list-group-item-action';
                                    item.textContent = loc;
                                    item.onclick = function() {
                                        input.value = loc;
                                        suggestions.style.display = 'none';
                                        form.submit(); // Submit immediately on suggestion click
                                    };
                                    suggestions.appendChild(item);
                                });
                                suggestions.style.display = 'block';
                            }
                            // Debounced real-time submit (2 seconds)
                            clearTimeout(debounceTimer);
                            debounceTimer = setTimeout(() => {
                                form.submit();
                            }, 2000);
                        });
                        // Hide suggestions when clicking outside
                        document.addEventListener('click', function(e) {
                            if (!input.contains(e.target) && !suggestions.contains(e.target)) {
                                suggestions.style.display = 'none';
                            }
                        });
                    </script>
                <?php endif; ?>
                <div class="row justify-content-center">
                    <div class="col-lg-10 col-xl-12">
                        <!-- Top Navigation Bar -->
                        <nav class="navbar navbar-expand-lg navbar-light bg-white rounded-top-4 shadow-sm mb-2 px-4 py-2">
                            <ul class="navbar-nav flex-row gap-3">
                                <li class="nav-item">
                                    <a class="nav-link fw-semibold text-success" href="#" data-target="totalsSection">
                                        <i class="bi bi-people-fill"></i> Total Evacuees
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link fw-semibold text-primary" href="#" data-target="distributionSection">
                                        <i class="bi bi-bar-chart-fill"></i> Distribution
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link fw-semibold text-warning" href="#" data-target="logsSection">
                                        <i class="bi bi-journal-text"></i> Logs
                                    </a>
                                </li>
                            </ul>
                            <span class="badge bg-primary bg-opacity-25 text-primary fw-semibold px-3 py-2 ms-auto">Live Data</span>
                        </nav>

                        <!-- Total Evacuees Section -->
                        <section id="totalsSection" class="mb-4">
                            <div class="card shadow border-0 rounded-4 overflow-hidden">
                                <div class="card-header bg-white border-0 rounded-top-4 px-4 py-3 d-flex align-items-center gap-2">
                                    <i class="bi bi-people-fill fs-3 text-success"></i>
                                    <span class="fw-semibold fs-5 text-dark">Total Evacuees & Age Classification</span>
                                    <span class="badge bg-success bg-opacity-25 text-success fw-semibold px-3 py-2 ms-auto">Summary</span>
                                    <button type="button" class="btn btn-outline-primary btn-sm ms-3" id="generateReportBtn">
                                        <i class="bi bi-file-earmark-arrow-down"></i> Generate Report
                                    </button>
                                </div>
                                <div class="card-body px-4 py-3">
                                    <div class="row text-center g-3 mb-4">
                                        <div class="col-md-3">
                                            <div class="p-3 rounded-3 bg-white shadow-sm border" style="border-left: 6px solid #4e73df;">
                                                <small class="text-muted">Total Solo Evacuees</small>
                                                <h4 class="fw-bold text-primary mb-0"><?php echo array_sum($soloData); ?></h4>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="p-3 rounded-3 bg-white shadow-sm border" style="border-left: 6px solid #1cc88a;">
                                                <small class="text-muted">Total Family Evacuees</small>
                                                <h4 class="fw-bold text-success mb-0"><?php echo array_sum($familyData); ?></h4>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="p-3 rounded-3 bg-white shadow-sm border" style="border-left: 6px solid #f6c23e;">
                                                <small class="text-muted">Total Unique Evacuees</small>
                                                <h4 class="fw-bold text-warning mb-0"><?php echo $totalEvacuees; ?></h4>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="p-3 rounded-3 bg-white shadow-sm border" style="border-left: 6px solid #e83e8c;">
                                                <small class="text-muted">Male</small>
                                                <h4 class="fw-bold text-danger mb-0"><?php echo isset($maleCount) ? $maleCount : 0; ?></h4>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="p-3 rounded-3 bg-white shadow-sm border" style="border-left: 6px solid #6f42c1;">
                                                <small class="text-muted">Female</small>
                                                <h4 class="fw-bold text-muted mb-0"><?php echo isset($femaleCount) ? $femaleCount : 0; ?></h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <div class="card border-0 shadow-sm h-100">
                                                <div class="card-header bg-white border-0 d-flex align-items-center gap-2">
                                                    <i class="bi bi-bar-chart-fill fs-5 text-primary"></i>
                                                    <span class="fw-semibold fs-5 text-dark">Total Evacuees Chart</span>
                                                </div>
                                                <div class="card-body">
                                                    <div style="height: 260px;">
                                                        <canvas id="evacuationChart" style="height: 100%; width: 100%;"></canvas>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card border-0 shadow-sm h-100">
                                                <div class="card-header bg-white border-0 d-flex align-items-center gap-2">
                                                    <i class="bi bi-people-fill fs-5 text-primary"></i>
                                                    <span class="fw-semibold fs-5 text-dark">Evacuee Age Classification</span>
                                                </div>
                                                <div class="card-body">
                                                    <p class="text-muted mb-3">This chart shows the distribution of evacuees by age group.</p>
                                                    <div style="height: 260px;">
                                                        <canvas id="ageClassificationChart" aria-label="Age Classification Chart" role="img" style="height: 100%; width: 100%;"></canvas>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <?php

                        // Sample example: get role and location from session
                        $role = $_SESSION['role'] ?? 'Staff';  // default to staff if not set
                        $assignedLocation = $_SESSION['evac_loc_id'] ?? '';

                        // Base query
                        $sql = "SELECT d.evac_reg_id, r.resource_name, pr.f_name AS evacuee_name, 
               d.date_time AS date_received, d.quantity AS quantity, r.measurement_unit AS unit, 
               evt.name AS evacuation_center,evt.evac_loc_id
        FROM resource_distribution_table d
        LEFT JOIN evac_reg_table e ON d.evac_reg_id = e.evac_reg_id
        LEFT JOIN evac_loc_table evt ON e.evac_loc_id = evt.evac_loc_id
        LEFT JOIN pre_reg_table pr ON e.pre_reg_id = pr.pre_reg_id
        LEFT JOIN resource_allocation_table r ON d.resource_id = r.resource_id";

                        // Add condition if role is staff to filter by assigned location
                        if ($role === 'Staff' && !empty($assignedLocation)) {
                            $sql .= " WHERE evt.evac_loc_id = ?";
                        }

                        $sql .= " GROUP BY d.evac_reg_id, r.resource_name
          ORDER BY r.resource_name";

                        // Prepare and execute
                        if ($stmt = $conn->prepare($sql)) {
                            if ($role === 'Staff' && !empty($assignedLocation)) {
                                $stmt->bind_param("i", $assignedLocation);
                            }
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $distributionRows = [];
                            while ($row = $result->fetch_assoc()) {
                                $distributionRows[] = $row;
                            }
                            $stmt->close();
                        } else {
                            // Handle prepare error
                            echo "SQL prepare error: " . $conn->error;
                        }
                        ?>

                        <!-- Distribution Section -->
                        <section id="distributionSection" class="mb-4">
                            <div class="card shadow border-0 rounded-4 overflow-hidden">
                                <div class="card-header bg-white border-0 rounded-top-4 px-4 py-3 d-flex align-items-center gap-2">
                                    <i class="bi bi-bar-chart-fill fs-3 text-primary"></i>
                                    <span class="fw-semibold fs-5 text-dark">Resource Distribution</span>
                                </div>
                                <div class="card-body px-4 py-3">

                                    <!-- Generate Report Buttons -->
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <button id="printReportBtns" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-printer"></i> Print Report
                                        </button>
                                        <button id="downloadPdfBtns" class="btn btn-outline-success btn-sm">
                                            <i class="bi bi-file-earmark-pdf"></i> Download PDF
                                        </button>
                                    </div>

                                    <div class="table-responsive">
                                        <table id="distributionTable" class="table table-bordered table-sm align-middle">
                                            <thead class="table-primary">
                                                <tr>
                                                    <th>Evacuee Name</th>
                                                    <th>Resource Name</th>
                                                    <th>Quantity</th>
                                                    <th>Date Received</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($distributionRows)): ?>
                                                    <?php foreach ($distributionRows as $dist): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($dist['evacuee_name'] ?? 'Unknown'); ?></td>
                                                            <td>
                                                                <?php
                                                                echo htmlspecialchars(
                                                                    ($dist['resource_name'] ?? 'Unknown') .
                                                                        (isset($dist['unit']) && $dist['unit'] !== '' ? ' ' . $dist['unit'] : '')
                                                                );
                                                                ?>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($dist['quantity'] ?? 'Unknown'); ?></td>
                                                            <td>
                                                                <?php
                                                                if (!empty($dist['date_received']) && $dist['date_received'] != '0000-00-00 00:00:00') {
                                                                    $date = new DateTime($dist['date_received']);
                                                                    echo htmlspecialchars($date->format('F j, Y, g:i A'));
                                                                } else {
                                                                    echo 'Unknown';
                                                                }
                                                                ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="3" class="text-center">No distribution data found.</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- Include jsPDF & jsPDF-AutoTable for PDF export -->
                        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
                        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>

                        <script>
                            // Print report button
                            document.getElementById('printReportBtns').addEventListener('click', () => {
                                window.print();
                            });

                            // Download PDF button
                            document.getElementById('downloadPdfBtns').addEventListener('click', () => {
                                const {
                                    jsPDF
                                } = window.jspdf;
                                const doc = new jsPDF();

                                doc.setFontSize(14);
                                doc.text("Resource Distribution Report", 14, 15);

                                doc.autoTable({
                                    startY: 20,
                                    html: '#distributionTable',
                                    theme: 'striped',
                                    headStyles: {
                                        fillColor: [54, 162, 235]
                                    },
                                    styles: {
                                        fontSize: 10
                                    },
                                    margin: {
                                        left: 14,
                                        right: 14
                                    }
                                });

                                doc.save('resource_distribution_report.pdf');
                            });
                        </script>
                        <!-- Logs Section -->
                        <?php


                        $logsReport = [];

                        // Query to get the latest status per evacuee in each evacuation center
                        $sql = "SELECT e.name AS evacuation_center,
                                        prt.l_name AS last_name,
                                        prt.f_name AS first_name,
                                        lt.status,
                                        MAX(lt.date_time) AS last_log_time
                                    FROM logs_table lt
                                    JOIN evac_reg_table er ON lt.evac_reg_id = er.evac_reg_id
                                    LEFT JOIN pre_reg_table prt ON er.pre_reg_id = prt.pre_reg_id
                                    LEFT JOIN evac_loc_table e ON er.evac_loc_id = e.evac_loc_id
                                    ";

                        // Add filtering if role is Staff
                        if ($role === 'Staff' && !empty($assignedLocation)) {
                            $sql .= " WHERE e.evac_loc_id = ?";
                        }

                        $sql .= " GROUP BY e.name, er.evac_reg_id
                                    ORDER BY e.name, prt.l_name, prt.f_name";

                        if ($stmt = $conn->prepare($sql)) {
                            if ($role === 'Staff' && !empty($assignedLocation)) {
                                $stmt->bind_param("i", $assignedLocation);
                            }
                            $stmt->execute();
                            $result = $stmt->get_result();

                            while ($row = $result->fetch_assoc()) {
                                // Group by evacuation center; each center holds an array of evacuees
                                $center = $row['evacuation_center'];
                                if (!isset($logsReport[$center])) {
                                    $logsReport[$center] = [];
                                }
                                $logsReport[$center][] = [
                                    'last_name' => $row['last_name'],
                                    'first_name' => $row['first_name'],
                                    'status' => $row['status'],
                                    'last_log_time' => $row['last_log_time']
                                ];
                            }
                            $stmt->close();
                        } else {
                            echo "Database prepare failed: " . $conn->error;
                        }
                        ?>
                        <section id="logsSection" class="mb-4">
                            <div class="card shadow border-0 rounded-4 overflow-hidden">
                                <div class="card-header bg-white border-0 rounded-top-4 px-4 py-3 d-flex align-items-center gap-2">
                                    <i class="bi bi-journal-text fs-3 text-warning"></i>
                                    <span class="fw-semibold fs-5 text-dark">Evacuee IN/OUT Logs Report</span>
                                    <span class="badge bg-warning bg-opacity-25 text-warning fw-semibold px-3 py-2 ms-auto">Logs</span>
                                </div>
                                <div class="card-body px-4 py-3">
                                    <p class="text-muted mb-3">This table shows evacuees and their latest IN/OUT status per evacuation center.</p>
                                    <div class="table-responsive">
                                        <?php if (!empty($logsReport)): ?>
                                            <?php foreach ($logsReport as $center => $evacuees): ?>
                                                <h6 class="fw-semibold mt-3 mb-2"><?php echo htmlspecialchars($center); ?></h6>
                                                <table class="table table-bordered table-sm mb-4">
                                                    <thead class="table-success">
                                                        <tr>
                                                            <th>Last Name</th>
                                                            <th>First Name</th>
                                                            <th>Status</th>
                                                            <th>Last Log Time</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($evacuees as $evacuee): ?>
                                                            <tr>
                                                                <td><?php echo htmlspecialchars($evacuee['last_name']); ?></td>
                                                                <td><?php echo htmlspecialchars($evacuee['first_name']); ?></td>
                                                                <td><?php echo htmlspecialchars($evacuee['status']); ?></td>
                                                                <td><?php echo date('F j, Y, g:i A', strtotime($evacuee['last_log_time'])); ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p class="text-center text-muted">No logs data found.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </section>

                    </div>
                </div>
                <!-- Report Preview Modal -->
                <div class="modal fade" id="reportPreviewModal" tabindex="-1" aria-labelledby="reportPreviewModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title" id="reportPreviewModalLabel">
                                    <i class="bi bi-file-earmark-text"></i> Evacuation Report Preview
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body" id="reportPreviewContent">
                                <!-- Dynamic table content will be injected here -->
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                    <i class="bi bi-x-circle"></i> Close
                                </button>
                                <button type="button" class="btn btn-success" id="downloadPdfBtn">
                                    <i class="bi bi-file-earmark-arrow-down"></i> Download PDF
                                </button>
                                <button type="button" class="btn btn-primary" id="printReportBtn">
                                    <i class="bi bi-printer"></i> Print PDF
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const sections = document.querySelectorAll('section');
                const navLinks = document.querySelectorAll('.navbar-nav a[data-target]');

                function showSection(id) {
                    sections.forEach(sec => {
                        sec.classList.toggle('active', sec.id === id);
                    });
                    navLinks.forEach(link => {
                        link.classList.toggle('active', link.getAttribute('data-target') === id);
                    });
                }

                // Show totalsSection by default on page load
                showSection('totalsSection');

                navLinks.forEach(link => {
                    link.addEventListener('click', e => {
                        e.preventDefault();
                        showSection(link.getAttribute('data-target'));
                    });
                });
            });
        </script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // Main Evacuation Chart
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
                    datasets: [{
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

            // Age Classification Chart
            const ageLabels = <?php echo json_encode(array_keys($ageGroups)); ?>;
            const ageData = <?php echo json_encode(array_values($ageGroups)); ?>;
            const ageMax = Math.max(...ageData, 10);
            const ageStep = ageMax <= 10 ? 1 : (ageMax <= 50 ? 5 : 10);
            const ageLimit = ageMax <= 10 ? 10 : (ageMax <= 50 ? Math.ceil((ageMax + 5) / 5) * 5 : Math.ceil((ageMax + 10) / 10) * 10);

            const ageCtx = document.getElementById('ageClassificationChart').getContext('2d');
            new Chart(ageCtx, {
                type: 'bar',
                data: {
                    labels: ageLabels,
                    datasets: [{
                        label: 'Evacuees',
                        data: ageData,
                        backgroundColor: ['#36b9cc', '#4e73df', '#1cc88a', '#f6c23e']
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            mode: 'nearest',
                            intersect: true
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Age Group'
                            }
                        },
                        y: {
                            beginAtZero: true,
                            max: ageLimit,
                            ticks: {
                                stepSize: ageStep
                            },
                            title: {
                                display: true,
                                text: 'Evacuee Count'
                            }
                        }
                    }
                }
            });
        </script>


        <?php include '../layout/footer.php'; ?>
    </div>
    <script src="../scripts/scripts.js"></script>
    <!-- html2pdf.js CDN for PDF export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <script>
        document.getElementById('generateReportBtn').addEventListener('click', function() {
            const chartLabels = <?php echo json_encode($chartLabels); ?>;
            const soloData = <?php echo json_encode($soloData); ?>;
            const familyData = <?php echo json_encode($familyData); ?>;
            const totalEvacueesPerLoc = <?php echo json_encode($totalEvacueesPerLoc); ?>;
            const ageGroups = <?php echo json_encode($ageGroups); ?>;

            const totalSolo = soloData.reduce((a, b) => a + b, 0);
            const totalFamily = familyData.reduce((a, b) => a + b, 0);
            const totalEvacuees = totalEvacueesPerLoc.reduce((a, b) => a + b, 0);

            let html = `
        <h5 class="mb-3">Evacuation Summary</h5>
        <table class="table table-bordered text-center align-middle">
            <thead class="table-primary">
                <tr>
                    <th>Category</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>Total Solo</td><td>${totalSolo}</td></tr>
                <tr><td>Total Family</td><td>${totalFamily}</td></tr>
                <tr class="fw-bold table-light"><td>Total Evacuees</td><td>${totalEvacuees}</td></tr>
            </tbody>
        </table>

        <h5 class="mt-4">Evacuation Center Breakdown</h5>
        <table class="table table-bordered text-center align-middle">
            <thead class="table-primary">
                <tr>
                    <th>Evacuation Center</th>
                    <th>Solo Evacuees</th>
                    <th>Family Evacuees</th>
                    <th>Total Evacuees</th>
                </tr>
            </thead>
            <tbody>
    `;
            chartLabels.forEach((label, i) => {
                html += `<tr>
            <td>${label}</td>
            <td>${soloData[i]}</td>
            <td>${familyData[i]}</td>
            <td>${totalEvacueesPerLoc[i]}</td>
        </tr>`;
            });
            html += `</tbody></table>`;

            html += `
        <h5 class="mt-4">Age Classification Statistics</h5>
        <table class="table table-bordered text-center align-middle">
            <thead class="table-primary">
                <tr>
                    <th>Age Group</th>
                    <th>Number of Evacuees</th>
                </tr>
            </thead>
            <tbody>
    `;
            for (let group in ageGroups) {
                html += `<tr><td>${group}</td><td>${ageGroups[group]}</td></tr>`;
            }
            html += `
            </tbody>
            <tfoot class="fw-bold table-light">
                <tr>
                    <td>Total</td>
                    <td>${Object.values(ageGroups).reduce((a, b) => a + b, 0)}</td>
                </tr>
            </tfoot>
        </table>
    `;

            document.getElementById('reportPreviewContent').innerHTML = html;
            new bootstrap.Modal(document.getElementById('reportPreviewModal')).show();
        });

        // Download PDF directly
        document.getElementById('downloadPdfBtn').addEventListener('click', function() {
            const content = document.getElementById('reportPreviewContent');
            html2pdf().set({
                margin: 0.5,
                filename: 'Evacuation_Report.pdf',
                image: {
                    type: 'jpeg',
                    quality: 0.98
                },
                html2canvas: {
                    scale: 2
                },
                jsPDF: {
                    unit: 'in',
                    format: 'letter',
                    orientation: 'portrait'
                }
            }).from(content).save();
        });

        // Print PDF via browser dialog
        document.getElementById('printReportBtn').addEventListener('click', function() {
            const content = document.getElementById('reportPreviewContent').innerHTML;
            const printWindow = window.open('', '', 'width=900,height=650');
            printWindow.document.write(`
        <html>
            <head>
                <title>Evacuation Report</title>
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
            </head>
            <body>
                ${content}
                <script>
                    window.onload = function() {
                        window.print();
                        window.close();
                    }
                <\/script>
            </body>
        </html>
    `);
            printWindow.document.close();
        });
    </script>


    <script>
        // Search filter
        document.getElementById('searchBox').addEventListener('keyup', function() {
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
<style>
    /* Hide all sections by default */
    section {
        display: none;
    }

    /* Show only the active section */
    section.active {
        display: block;
    }

    /* Active nav link: underline only, no blue highlight */
    .navbar-nav a.active {
        color: inherit !important;
        /* Preserve original text color */
        text-decoration: underline;
        font-weight: 700;
        background-color: transparent !important;
        box-shadow: none !important;
    }
</style>