$(document).ready(function() {
		// Search functionality
		$("#searchBox").on("keyup", function() {
			var searchTerm = $(this).val().toLowerCase().trim(); // Get the search term

			// Loop through each row in the table body
			$("#adminTable tbody tr").each(function() {
				var rowText = $(this).text().toLowerCase(); // Get the text content of the row

				// Show or hide the row based on whether it matches the search term
				if (rowText.includes(searchTerm)) {
					$(this).fadeIn();
				} else {
					$(this).fadeOut();
				}
			});
		});
	});

document.addEventListener('DOMContentLoaded', function() {
		// Location select change event
		document.getElementById('locationSelect').addEventListener('change', function() {
			document.getElementById('locationForm').submit();
		});

		// Search functionality
		const searchBox = document.getElementById('searchBox');

		searchBox.addEventListener('keyup', function() {
			const searchTerm = this.value.toLowerCase().trim();
			const rows = document.querySelectorAll('#usertable tbody tr');

			rows.forEach(function(row) {
				const rowText = row.textContent.toLowerCase();

				if (searchTerm === '' || rowText.includes(searchTerm)) {
					row.style.display = '';
				} else {
					row.style.display = 'none';
				}
			});
		});
	});