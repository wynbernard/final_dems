<select id="provinceSelect" class="form-control">
	<option value="">Select Province</option>
</select>

<p id="provinceCodeDisplay"></p> <!-- To show selected province code -->

<script>
	let provinceList = [];

	// Fetch province data from JSON
	fetch('./address_json/province.json')
		.then(response => response.json())
		.then(provinces => {
			provinceList = provinces;

			const provinceSelect = document.getElementById('provinceSelect');

			// Populate the province dropdown
			provinces.forEach(province => {
				const option = document.createElement('option');
				option.value = province.province_code; // Use province_code as the value
				option.textContent = province.province_name;
				provinceSelect.appendChild(option);
			});

			// Add change event to display selected province code
			provinceSelect.addEventListener('change', function() {
				const selectedProvinceCode = this.value;
				const selectedProvince = provinceList.find(p => p.province_code === selectedProvinceCode);
				document.getElementById('provinceCodeDisplay').textContent =
					selectedProvince ? `Selected Province Code: ${selectedProvince.province_code}` : '';
			});
		})
		.catch(error => console.error('Error loading province data:', error));
</script>