<div class="mb-3">
  <label for="citySelect" class="form-label">Select City</label>
  <select id="citySelect" class="form-control">
    <option value="">-- Select City --</option>
  </select>
</div>

<div id="selectedCityId" class="mt-2 fw-bold text-success"></div>


<script>
  let cityData = [];

  // Load the cities from JSON
  fetch('address_json/city.json') // adjust path if needed
    .then(response => response.json())
    .then(data => {
      cityData = data;

      const citySelect = document.getElementById('citySelect');
      data.forEach(city => {
        const option = document.createElement('option');
        option.value = city.city_code;
        option.textContent = city.city_name;
        citySelect.appendChild(option);
      });
    });

  // Display selected city_code
  document.getElementById('citySelect').addEventListener('change', function () {
    const selectedCode = this.value;
    const display = document.getElementById('selectedCityId');

    if (selectedCode) {
      display.textContent = `Selected City ID: ${selectedCode}`;
    } else {
      display.textContent = '';
    }
  });
</script>
