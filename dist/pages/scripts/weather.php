<script>
	document.addEventListener("DOMContentLoaded", function() {
		const apiKey = "55df71abc970af13354d6b500e292735"; // Replace with your OpenWeatherMap API key
		const city = "Bago City"; // Change to desired city
		const apiUrl = `https://api.openweathermap.org/data/2.5/weather?q=${city}&units=metric&appid=${apiKey}`;

		fetch(apiUrl)
			.then(response => response.json())
			.then(data => {
				if (data.cod !== 200) {
					throw new Error(data.message);
				}
				// Update weather card with data
				document.getElementById("weather-location").innerText = `${data.name}, ${data.sys.country}`;
				document.getElementById("weather-description").innerText = data.weather[0].description;
				document.getElementById("weather-temp-value").innerText = Math.round(data.main.temp);
				document.getElementById("weather-feels").innerText = Math.round(data.main.feels_like) + "°C";
				document.getElementById("weather-wind").innerText = data.wind.speed + " m/s";
				document.getElementById("weather-humidity").innerText = data.main.humidity + "%";

				// Update weather icon dynamically
				const weatherCode = data.weather[0].main;
				const icons = {
					Clear: "☀️",
					Clouds: "☁️",
					Rain: "🌧️",
					Drizzle: "🌦️",
					Thunderstorm: "⛈️",
					Snow: "❄️",
					Mist: "🌫️"
				};
				document.getElementById("weather-icon").innerText = icons[weatherCode] || "☁️";
			})
			.catch(error => {
				console.error("Error fetching weather data:", error);
				document.getElementById("weather-location").innerText = "Unable to load weather data";
			});
	});
</script>
<style>
	.weather-card {
		background: linear-gradient(to right, #4facfe, #00f2fe);
		/* fallback for old browsers */
		/* background: -webkit-linear-gradient(to right, #4facfe, #00f2fe);
		background: -moz-linear-gradient(to right, #4facfe, #00f2fe); */
		color: #fff;
		border: none;
		border-radius: 1rem;
		box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
		overflow: hidden;
	}

	.clouds {
		position: absolute;
		top: 0;
		left: 0;
		width: 300%;
		height: 100%;
		background: url('https://www.transparenttextures.com/patterns/clouds.png');
		background-size: cover;
		opacity: 0.3;
		animation: moveClouds 30s linear infinite;
	}

	@keyframes moveClouds {
		from {
			transform: translateX(0);
		}

		to {
			transform: translateX(-100%);
		}
	}

	.weather-card .card-header {
		background: rgba(0, 0, 0, 0.2);
		border-bottom: none;
	}

	.weather-card .card-body {
		background: rgba(255, 255, 255, 0.1);
		backdrop-filter: blur(10px);
		padding: 2rem;
	}

	/* Professional text sizes and spacing */
	.weather-card h3 {
		margin-bottom: 0;
	}

	.weather-card h4 {
		margin-top: 1rem;
		font-weight: 600;
	}

	.weather-card p {
		margin: 0.5rem 0;
	}

	.weather-temp {
		font-size: 2.5rem;
		font-weight: bold;
	}

	.weather-info {
		font-size: 1.2rem;
		font-weight: 600;
	}
</style>