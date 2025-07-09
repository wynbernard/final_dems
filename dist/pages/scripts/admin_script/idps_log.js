$(document).ready(function() {
		$("#searchBox").on("keyup", function() {
			var searchTerm = $(this).val().toLowerCase().trim();

			// Loop through each row in the table
			$("#logTable tbody tr").each(function() {
				var rowText = $(this).text().toLowerCase();

				// Check if the row contains the search term
				if (rowText.indexOf(searchTerm) !== -1) {
					$(this).show(); // Show the row if match
				} else {
					$(this).hide(); // Hide the row if no match
				}
			});
		});
	});