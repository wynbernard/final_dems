
<script>
  const evacLocations = <?= json_encode($evacMapLocations); ?>;
  const map = L.map('evacMap');

  // Tile Layer
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
  }).addTo(map);

  const markerPositions = [];

  // Custom Icons
  const icons = {
    green: L.icon({
      iconUrl: 'https://maps.google.com/mapfiles/ms/icons/green-dot.png',
      iconSize: [32, 32],
      iconAnchor: [16, 32],
      popupAnchor: [0, -32]
    }),
    red: L.icon({
      iconUrl: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png',
      iconSize: [32, 32],
      iconAnchor: [16, 32],
      popupAnchor: [0, -32]
    })
  };

  // Add Legend as a Leaflet Control
  const legend = L.control({ position: 'topright' });
  legend.onAdd = function () {
    const div = L.DomUtil.create('div', 'info legend');
    div.style.background = 'white';
    div.style.padding = '8px 10px';
    div.style.borderRadius = '8px';
    div.style.boxShadow = '0 2px 6px rgba(0,0,0,0.2)';
    div.style.fontSize = '0.9rem';
    div.innerHTML = `
      <div style="display:flex;align-items:center;margin-bottom:4px;">
        <img src="https://maps.google.com/mapfiles/ms/icons/green-dot.png" style="width:18px;height:18px;margin-right:5px;">
        Ongoing Event
      </div>
      <div style="display:flex;align-items:center;">
        <img src="https://maps.google.com/mapfiles/ms/icons/red-dot.png" style="width:18px;height:18px;margin-right:5px;">
        Empty Center
      </div>
    `;
    return div;
  };
  legend.addTo(map);

  evacLocations.forEach(loc => {
    const hasEvacuees = (loc.total_evacuees || 0) > 0;

    // Choose icon based on evacuees count
    const marker = L.marker([loc.lat, loc.lng], {
      icon: hasEvacuees ? icons.green : icons.red
    }).addTo(map);

    markerPositions.push([loc.lat, loc.lng]);

    let popupOpenedByClick = false;

    // Hover popup
    marker.on('mouseover', function () {
      if (!popupOpenedByClick) {
        marker.bindPopup(`<b>${loc.name}</b>`, {
          closeButton: false,
          offset: L.point(0, -30)
        }).openPopup();
      }
    });

    marker.on('mouseout', function () {
      if (!popupOpenedByClick) {
        marker.closePopup();
      }
    });

    // Click event for details
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

      const totalEvacuees = loc.total_evacuees || 0;
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
      document.getElementById('evacDetails').style.display = 'block';

      // Render Chart
      const ctxId = 'evacStatsChart';
      document.getElementById('evacStatsGraph').innerHTML =
        `<canvas id="${ctxId}" style="height: 350px; width: 100%;"></canvas>`;

      new Chart(document.getElementById(ctxId), {
        type: 'bar',
        data: {
          labels: ['Solo Evacuees', 'Family Evacuees', 'Total Evacuees'],
          datasets: [{
            label: 'Number of Evacuees',
            data: [loc.total_solo || 0, loc.total_family || 0, totalEvacuees],
            backgroundColor: ['#3498db', '#e67e22', '#2ecc71']
          }]
        },
        options: {
          responsive: true,
          scales: {
            y: {
              beginAtZero: true,
              ticks: { stepSize: 1 }
            }
          },
          plugins: {
            legend: { display: false },
            title: {
              display: true,
              text: `Evacuee Breakdown for ${loc.name}`,
              font: { size: 16 }
            },
            tooltip: {
              callbacks: { label: ctx => `${ctx.parsed.y} evacuees` }
            }
          }
        }
      });
    });

    // Map click closes popup
    map.on('click', function () {
      popupOpenedByClick = false;
      marker.closePopup();
    });
  });

  // Fit map to markers
  if (markerPositions.length > 0) {
    const bounds = L.latLngBounds(markerPositions);
    map.fitBounds(bounds, { padding: [30, 30] });
  } else {
    map.setView([10.3157, 123.8854], 10);
  }

  // Close details panel
  function closeEvacDetails() {
    document.getElementById('evacDetails').style.display = 'none';
    if (markerPositions.length > 0) {
      const bounds = L.latLngBounds(markerPositions);
      map.fitBounds(bounds, { padding: [30, 30] });
    } else {
      map.setView([10.3157, 123.8854], 10);
    }
  }
</script>