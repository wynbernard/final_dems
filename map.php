<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Evacuation Statistics Dashboard</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light p-4">

  <div class="container">
    <h2 class="mb-4">Evacuation Statistics Dashboard</h2>

    <!-- Summary Cards -->
    <div class="row text-white">
      <div class="col-md-3 mb-3">
        <div class="bg-primary p-3 rounded text-center">
          <h4>Total Evacuees</h4>
          <h2>1,247</h2>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="bg-success p-3 rounded text-center">
          <h4>Families Evacuated</h4>
          <h2>184</h2>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="bg-warning p-3 rounded text-center">
          <h4>Solos Evacuated</h4>
          <h2>275</h2>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="bg-danger p-3 rounded text-center">
          <h4>Ongoing Events</h4>
          <h2>2</h2>
        </div>
      </div>
    </div>

    <!-- Evacuee Trend Chart -->
    <div class="card mb-4">
      <div class="card-header">Evacuee Trend (Past 7 Days)</div>
      <div class="card-body">
        <canvas id="evacueeTrendChart"></canvas>
      </div>
    </div>

    <!-- Center Utilization Donut -->
    <div class="card mb-4">
      <div class="card-header">Evacuation Center Utilization</div>
      <div class="card-body">
        <canvas id="centerUtilChart"></canvas>
      </div>
    </div>

    <!-- Event Summary Table -->
    <div class="card mb-4">
      <div class="card-header">Event Summary</div>
      <div class="card-body">
        <table class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>Event Name</th>
              <th>Start Date</th>
              <th>End Date</th>
              <th>Status</th>
              <th>Total Evacuees</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Typhoon Egay</td>
              <td>2025-07-01</td>
              <td>2025-07-04</td>
              <td><span class="badge bg-success">Completed</span></td>
              <td>562</td>
            </tr>
            <tr>
              <td>Flash Flood A</td>
              <td>2025-07-25</td>
              <td>–</td>
              <td><span class="badge bg-danger">Ongoing</span></td>
              <td>315</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Evacuation by Location Bar Chart -->
    <div class="card mb-4">
      <div class="card-header">Evacuation by Location</div>
      <div class="card-body">
        <canvas id="locationEvacChart"></canvas>
      </div>
    </div>

    <!-- Location Summary Table -->
    <div class="card mb-5">
      <div class="card-header">Location Summary</div>
      <div class="card-body">
        <table class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>Location</th>
              <th>Capacity</th>
              <th>Current Evacuees</th>
              <th>Occupancy %</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Barangay Hall 1</td>
              <td>100</td>
              <td>80</td>
              <td>80%</td>
            </tr>
            <tr>
              <td>School Gym</td>
              <td>200</td>
              <td>45</td>
              <td>22.5%</td>
            </tr>
            <tr>
              <td>Community Center</td>
              <td>150</td>
              <td>120</td>
              <td>80%</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Chart.js Scripts -->
  <script>
    // Evacuee Trend Chart
    new Chart(document.getElementById('evacueeTrendChart'), {
      type: 'line',
      data: {
        labels: ['Jul 25', 'Jul 26', 'Jul 27', 'Jul 28', 'Jul 29', 'Jul 30', 'Jul 31'],
        datasets: [{
          label: 'Registered Evacuees',
          data: [45, 72, 63, 89, 95, 112, 98],
          borderColor: 'blue',
          backgroundColor: 'rgba(0, 0, 255, 0.1)',
          tension: 0.3,
          fill: true
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: 'Number of Evacuees'
            }
          }
        }
      }
    });

    // Center Utilization Donut Chart
    new Chart(document.getElementById('centerUtilChart'), {
      type: 'doughnut',
      data: {
        labels: ['Barangay Hall 1 (80%)', 'School Gym (22%)', 'Community Center (80%)'],
        datasets: [{
          data: [80, 22, 80],
          backgroundColor: ['#0d6efd', '#ffc107', '#198754']
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'bottom'
          }
        }
      }
    });

    // Evacuation by Location - Bar Chart
    new Chart(document.getElementById('locationEvacChart'), {
      type: 'bar',
      data: {
        labels: ['Barangay Hall 1', 'School Gym', 'Community Center'],
        datasets: [{
            label: 'Evacuees',
            data: [80, 45, 120],
            backgroundColor: '#0d6efd'
          },
          {
            label: 'Capacity',
            data: [100, 200, 150],
            backgroundColor: '#dee2e6'
          }
        ]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'bottom'
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: 'Number of People'
            }
          }
        }
      }
    });
  </script>

</body>

</html>