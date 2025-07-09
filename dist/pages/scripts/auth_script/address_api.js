document.addEventListener('DOMContentLoaded', function () {
	const regionSelect = document.getElementById('region');
	const provinceSelect = document.getElementById('province');
	const citySelect = document.getElementById('city');
	const barangaySelect = document.getElementById('barangay');

	let regionList = [];
	let provinceList = [];
	let cityList = [];
	let barangayList = [];

	// Load Regions
	fetch('../../../address_json/region.json')
		.then(response => response.json())
		.then(regions => {
			regionList = regions;
			regions.forEach(region => {
				const option = document.createElement('option');
				option.value = region.region_name;
				option.textContent = region.region_name;
				regionSelect.appendChild(option);
			});
		})
		.catch(error => console.error('Error loading region data:', error));

	// Load Provinces
	fetch('../../../address_json/province.json')
		.then(response => response.json())
		.then(provinces => {
			provinceList = provinces;
		})
		.catch(error => console.error('Error loading province data:', error));

	// Load Cities
	fetch('../../../address_json/city.json')
		.then(response => response.json())
		.then(cities => {
			cityList = cities;
		})
		.catch(error => console.error('Error loading city data:', error));

	// Load Barangays
	fetch('../../../address_json/barangays.json')
		.then(response => response.json())
		.then(barangays => {
			barangayList = barangays;
		})
		.catch(error => console.error('Error loading barangay data:', error));

	// When Region changes → filter Provinces
	regionSelect.addEventListener('change', function () {
		const selectedRegionName = this.value;

		const selectedRegion = regionList.find(r => r.region_name === selectedRegionName);
		const regionCode = selectedRegion?.region_code || '';

		provinceSelect.innerHTML = '<option value="">Select Province</option>';
		citySelect.innerHTML = '<option value="">Select City</option>';
		barangaySelect.innerHTML = '<option value="">Select Barangay</option>';

		const filteredProvinces = provinceList.filter(p => p.region_code === regionCode);
		filteredProvinces.forEach(province => {
			const option = document.createElement('option');
			option.value = province.province_name;
			option.textContent = province.province_name;
			provinceSelect.appendChild(option);
		});
	});

	// When Province changes → filter Cities
	provinceSelect.addEventListener('change', function () {
		const selectedProvinceName = this.value;

		const selectedProvince = provinceList.find(p => p.province_name === selectedProvinceName);
		const provinceCode = selectedProvince?.province_code || '';

		citySelect.innerHTML = '<option value="">Select City</option>';
		barangaySelect.innerHTML = '<option value="">Select Barangay</option>';

		const filteredCities = cityList.filter(c => c.province_code === provinceCode);
		filteredCities.forEach(city => {
			const option = document.createElement('option');
			option.value = city.city_name;
			option.textContent = city.city_name;
			citySelect.appendChild(option);
		});
	});

	// When City changes → filter Barangays
	citySelect.addEventListener('change', function () {
		const selectedCityName = this.value;

		const selectedCity = cityList.find(c => c.city_name === selectedCityName);
		const cityCode = selectedCity?.city_code || '';

		barangaySelect.innerHTML = '<option value="">Select Barangay</option>';

		const filteredBarangays = barangayList.filter(b => b.city_code === cityCode);
		filteredBarangays.forEach(barangay => {
			const option = document.createElement('option');
			option.value = barangay.brgy_name;
			option.textContent = barangay.brgy_name;
			barangaySelect.appendChild(option);
		});
	});
});

