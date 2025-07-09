
document.addEventListener('DOMContentLoaded', function() {
    if (typeof latitude !== "undefined" && typeof longitude !== "undefined" && latitude && longitude) {
        var map = L.map('locationMap').setView([latitude, longitude], 17);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        L.marker([latitude, longitude]).addTo(map)
            .bindPopup(locationName)
            .openPopup();
    } else {
        document.getElementById('locationMap').innerHTML = '<div class="text-danger p-3">Location coordinates not set.</div>';
    }
});


// SEARCH FUNCTIONALITY
$(document).ready(function() {
		$("#searchBox").on("keyup", function() {
			var searchTerm = $(this).val().toLowerCase().trim();

			$("#locationTable tbody tr").each(function() {
				var rowText = $(this).text().toLowerCase();

				if (rowText.includes(searchTerm)) {
					$(this).fadeIn();
				} else {
					$(this).fadeOut();
				}
			});
		});
	});

// VIEW IDPS INSIDE

document.addEventListener("DOMContentLoaded", function() {
		// Handle "View IDPs" button click
		document.querySelectorAll(".view-idp-btn").forEach(button => {
			button.addEventListener("click", function() {
				const roomId = this.dataset.id;
				const location = this.dataset.location;

				// Update modal title
				document.getElementById("modalLocation").textContent = location;
				document.getElementById("modalRoom").textContent = this.closest("tr").querySelector("td:nth-child(2)").textContent;

				// Fetch IDP data via AJAX
				fetch(`../fetch_data/get_idps.php?room_id=${roomId}`)
					.then(response => response.json())
					.then(data => {
						const idpList = document.getElementById("idpList");
						idpList.innerHTML = ""; // Clear previous content

						if (data.length > 0) {
							const ul = document.createElement("ul");
							data.forEach(idp => {
								const li = document.createElement("li");
								li.textContent = `${idp.f_name} ${idp.l_name}`;
								ul.appendChild(li);
							});
							idpList.appendChild(ul);
						} else {
							idpList.innerHTML = "<p>No IDPs found in this room.</p>";
						}
					})
					.catch(error => console.error("Error fetching IDPs:", error));
			});
		});
	});