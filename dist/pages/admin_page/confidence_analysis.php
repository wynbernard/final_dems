<?php
include '../../../database/session.php';
include '../layout/head_links.php';

// Fetch forecast data with accuracy information
$query = "
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
$result = mysqli_query($conn, $query);

// Group data by barangay
$barangay_data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $barangay = $row['barangay_name'];
    if (!isset($barangay_data[$barangay])) {
        $barangay_data[$barangay] = [];
    }
    $barangay_data[$barangay][] = $row;
}

// Calculate overall accuracy statistics
$accuracy_stats = [
    'total_forecasts' => 0,
    'avg_accuracy' => 0,
    'high_accuracy' => 0,
    'medium_accuracy' => 0,
    'low_accuracy' => 0
];

$all_accuracies = [];
while ($row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT accuracy_percentage FROM brgy_forecasts WHERE accuracy_percentage IS NOT NULL"))) {
    $acc = floatval($row['accuracy_percentage']);
    $all_accuracies[] = $acc;
    $accuracy_stats['total_forecasts']++;
    
    if ($acc >= 90) $accuracy_stats['high_accuracy']++;
    elseif ($acc >= 80) $accuracy_stats['medium_accuracy']++;
    else $accuracy_stats['low_accuracy']++;
}

if (!empty($all_accuracies)) {
    $accuracy_stats['avg_accuracy'] = array_sum($all_accuracies) / count($all_accuracies);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Accuracy Analysis</title>
    <?php include '../layout/head_links.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <?php include '../layout/sidebar.php'; ?>
    
    <div class="wrapper">
        <?php include '../layout/topbar.php'; ?>
        
        <div class="content-wrapper">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Accuracy Analysis</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                                <li class="breadcrumb-item active">Accuracy Analysis</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <section class="content">
                <div class="container-fluid">
                    <!-- Accuracy Summary Cards -->
                    <div class="row">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3><?php echo $accuracy_stats['total_forecasts']; ?></h3>
                                    <p>Total Forecasts</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3><?php echo number_format($accuracy_stats['avg_accuracy'], 1); ?>%</h3>
                                    <p>Average Accuracy</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-percentage"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3><?php echo $accuracy_stats['medium_accuracy']; ?></h3>
                                    <p>Medium Accuracy</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3><?php echo $accuracy_stats['low_accuracy']; ?></h3>
                                    <p>Low Accuracy</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-exclamation-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Confidence Distribution Chart -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Confidence Distribution</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="confidenceChart" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Confidence Levels</h3>
                                </div>
                                <div class="card-body">
                                    <div class="progress-group">
                                        <span class="progress-text">High Accuracy (≥90%)</span>
                                        <span class="float-right"><b><?php echo $accuracy_stats['high_accuracy']; ?></b>/<?php echo $accuracy_stats['total_forecasts']; ?></span>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar bg-success" style="width: <?php echo $accuracy_stats['total_forecasts'] > 0 ? ($accuracy_stats['high_accuracy'] / $accuracy_stats['total_forecasts']) * 100 : 0; ?>%"></div>
                                        </div>
                                    </div>
                                    <div class="progress-group">
                                        <span class="progress-text">Medium Accuracy (80-89%)</span>
                                        <span class="float-right"><b><?php echo $accuracy_stats['medium_accuracy']; ?></b>/<?php echo $accuracy_stats['total_forecasts']; ?></span>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar bg-warning" style="width: <?php echo $accuracy_stats['total_forecasts'] > 0 ? ($accuracy_stats['medium_accuracy'] / $accuracy_stats['total_forecasts']) * 100 : 0; ?>%"></div>
                                        </div>
                                    </div>
                                    <div class="progress-group">
                                        <span class="progress-text">Low Accuracy (<80%)</span>
                                        <span class="float-right"><b><?php echo $accuracy_stats['low_accuracy']; ?></b>/<?php echo $accuracy_stats['total_forecasts']; ?></span>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar bg-danger" style="width: <?php echo $accuracy_stats['total_forecasts'] > 0 ? ($accuracy_stats['low_accuracy'] / $accuracy_stats['total_forecasts']) * 100 : 0; ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Forecast Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Forecast Confidence Details</h3>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped" id="confidenceTable">
                                            <thead>
                                                <tr>
                                                    <th>Barangay</th>
                                                    <th>Scale</th>
                                                    <th>Forecast</th>
                                                    <th>Lower Bound</th>
                                                    <th>Upper Bound</th>
                                                    <th>Accuracy</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($barangay_data as $barangay => $forecasts): ?>
                                                    <?php foreach ($forecasts as $forecast): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($forecast['barangay_name']); ?></td>
                                                        <td>
                                                            <span class="badge badge-<?php echo $forecast['scale_range'] == '1-3' ? 'success' : ($forecast['scale_range'] == '4-7' ? 'warning' : 'danger'); ?>">
                                                                <?php echo htmlspecialchars($forecast['scale_range']); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo number_format($forecast['forecast'], 2); ?></td>
                                                        <td><?php echo number_format($forecast['lower_bound'], 2); ?></td>
                                                        <td><?php echo number_format($forecast['upper_bound'], 2); ?></td>
                                                        <td>
                                                            <?php 
                                                            $acc = floatval($forecast['accuracy_percentage']);
                                                            $badge_class = $acc >= 90 ? 'success' : ($acc >= 80 ? 'warning' : 'danger');
                                                            ?>
                                                            <span class="badge badge-<?php echo $badge_class; ?>">
                                                                <?php echo number_format($acc, 1); ?>%
                                                            </span>
                                                        </td>
                                                        <td><?php echo date('M d, Y', strtotime($forecast['date'])); ?></td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        
        <?php include '../layout/footer.php'; ?>
    </div>

    <?php include '../layout/scripts.php'; ?>
    
    <script>
        // Confidence Distribution Chart
        const ctx = document.getElementById('confidenceChart').getContext('2d');
        const confidenceChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['High Accuracy (≥90%)', 'Medium Accuracy (80-89%)', 'Low Accuracy (<80%)'],
                datasets: [{
                    data: [
                        <?php echo $accuracy_stats['high_accuracy']; ?>,
                        <?php echo $accuracy_stats['medium_accuracy']; ?>,
                        <?php echo $accuracy_stats['low_accuracy']; ?>
                    ],
                    backgroundColor: [
                        '#28a745',
                        '#ffc107',
                        '#dc3545'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = <?php echo $accuracy_stats['total_forecasts']; ?>;
                                const value = context.parsed;
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return context.label + ': ' + value + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
        
        // Initialize DataTable
        $(document).ready(function() {
            $('#confidenceTable').DataTable({
                "responsive": true,
                "lengthChange": false,
                "autoWidth": false,
                "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"],
                "order": [[7, "desc"]] // Sort by confidence descending
            }).buttons().container().appendTo('#confidenceTable_wrapper .col-md-6:eq(0)');
        });
    </script>
</body>
</html>
