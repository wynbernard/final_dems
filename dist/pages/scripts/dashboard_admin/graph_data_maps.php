<script>
  const evacLocations = <?= json_encode($evacMapLocations); ?>;
  const map = L.map('evacMap');
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
  }).addTo(map);

  const markerPositions = [];
  evacLocations.forEach(loc => {
    const marker = L.marker([loc.lat, loc.lng]).addTo(map);
    markerPositions.push([loc.lat, loc.lng]);

    let popupOpenedByClick = false;
    marker.on('mouseover', function() {
      if (!popupOpenedByClick) {
        marker.bindPopup(`<b>${loc.name}</b>`, {
          closeButton: false,
          offset: L.point(0, -30)
        }).openPopup();
      }
    });
    marker.on('mouseout', function() {
      if (!popupOpenedByClick) {
        marker.closePopup();
      }
    });

    marker.on('click', () => {
      const mapWidth = map.getSize().x;
      const overlayWidth = 400;
      const targetZoom = 15;
      const markerPoint = map.project([loc.lat, loc.lng], targetZoom);
      const offsetX = ((mapWidth - overlayWidth) / 2) - 40;
      const newPoint = L.point(markerPoint.x + offsetX, markerPoint.y);
      const newLatLng = map.unproject(newPoint, targetZoom);
      map.setView(newLatLng, targetZoom);

      popupOpenedByClick = true;
      marker.bindPopup(`<b>${loc.name}</b>`, {
        closeButton: false,
        offset: L.point(0, -30)
      }).openPopup();

      const totalEvacuees = (loc.total_evacuees || 0);

      // Show details with total evacuees
      const html = `
    
<h6 class="text-primary fw-bold mb-2" style="font-size: 1.25rem;">${loc.name}</h6>
<hr style="margin: 0.4rem 0; border: 0; border-top: 2px solid #020000ff;">
<p class="mb-2" style="font-size: 0.95rem; color: #555;">
  <strong>Location:</strong> Prk. ${loc.purok}, Bgry. ${loc.barangay}, ${loc.city}<br>
  <strong>Occupied:</strong> ${totalEvacuees} people<br>
  <strong>Available:</strong> ${loc.available_space - totalEvacuees} people<br>
  <strong>Room:</strong> ${loc.room_count} rooms
</p>
<hr style="margin: 10px 0; border-color: #ddd;" />

  `;
      document.getElementById('evacInfoContent').innerHTML = html;

      // Show the details panel
      document.getElementById('evacDetails').style.display = 'block';

      const ctxId = 'evacStatsChart';

      // Clear previous graph and insert canvas
      document.getElementById('evacStatsGraph').innerHTML = `<canvas id="${ctxId}" style="height: 350px; width: 100%;"></canvas>`;

      new Chart(document.getElementById(ctxId), {
        type: 'bar',
        data: {
          labels: ['Solo Evacuees', 'Family Evacuees', 'Total Evacuees'],
          datasets: [{
            label: 'Number of Evacuees',
            data: [loc.total_solo || 0, loc.total_family || 0, totalEvacuees],
            backgroundColor: ['#3498db', '#e67e22', '#2ecc71'] // blue, orange, green
          }]
        },
        options: {
          responsive: true,
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                stepSize: 1
              }
            }
          },
          plugins: {
            legend: {
              display: false
            },
            title: {
              display: true,
              text: `Evacuee Breakdown for ${loc.name}`,
              font: {
                size: 16
              }
            },
            tooltip: {
              callbacks: {
                label: ctx => `${ctx.parsed.y} evacuees`
              }
            }
          }
        }
      });
    });


    map.on('click', function() {
      popupOpenedByClick = false;
      marker.closePopup();
    });
  });

  if (markerPositions.length > 0) {
    const bounds = L.latLngBounds(markerPositions);
    map.fitBounds(bounds, {
      padding: [30, 30]
    });
  } else {
    map.setView([10.3157, 123.8854], 10);
  }

  function closeEvacDetails() {
    document.getElementById('evacDetails').style.display = 'none';
    if (markerPositions && markerPositions.length > 0) {
      const bounds = L.latLngBounds(markerPositions);
      map.fitBounds(bounds, {
        padding: [30, 30]
      });
    } else {
      map.setView([10.3157, 123.8854], 10);
    }
  }

  function renderEvacGraph(loc) {
    const graphDiv = document.getElementById('evacStatsGraph');
    graphDiv.innerHTML = `<canvas id="evacChart" style="height: 350px; width: 100%;"></canvas>`;

    const ctx = document.getElementById('evacChart').getContext('2d');

    // Data for graph: total solo and family evacuees from loc object
    const data = {
      labels: ['Solo Evacuees', 'Family Evacuees'],
      datasets: [{
        label: 'Number of Evacuees',
        data: [loc.total_solo || 0, loc.total_family || 0],
        backgroundColor: ['#3498db', '#e67e22']
      }]
    };

    const options = {
      responsive: true,
      plugins: {
        legend: {
          display: false
        },
        title: {
          display: true,
          text: `Evacuees Breakdown - ${loc.name}`
        },
        tooltip: {
          callbacks: {
            label: context => `${context.parsed.y} evacuees`
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            stepSize: 1
          }
        }
      }
    };

    // Destroy existing chart instance if exists to avoid duplicates
    if (window.currentEvacChart) {
      window.currentEvacChart.destroy();
    }
    window.currentEvacChart = new Chart(ctx, {
      type: 'bar',
      data,
      options
    });
  }
</script>