<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Disaster Evacuation Management System</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
	<style>
		body {
			font-family: 'Segoe UI', sans-serif;
			background-color: #f8f9fa;
			margin: 0;
			padding: 0;
			scroll-behavior: smooth;
		}

		.hero {
			background: linear-gradient(to right, #003366, #006699);
			color: white;
			min-height: 100vh;
			padding-top: 56px;
			/* space for fixed navbar */
		}

		.hero .container {
			min-height: calc(100vh - 56px);
		}


		/* features */
		.feature-icon {
			font-size: 48px;
			color: #0d6efd;
		}

		.feature-box {
			opacity: 0;
			transform: translateY(40px);
			transition: all 0.6s ease-out;
			margin: 50px;
		}

		.feature-box.visible {
			opacity: 1;
			transform: translateY(0);
		}



		/* Footer */
		footer {
			background-color: #343a40;
			color: white;
			padding: 30px 0;
		}

		footer a {
			color: #adb5bd;
			text-decoration: none;
		}

		footer a:hover {
			text-decoration: underline;
		}

		/* Background for hero */
		.hero {
			background: url('src/images/new.jpg') no-repeat center center/cover;
			background-attachment: fixed;
			color: white;
			min-height: 100vh;
			position: relative;
		}

		.hero::before {
			content: '';
			position: absolute;
			inset: 0;
			background-color: rgba(0, 0, 0, 0.5);
			/* dark overlay for contrast */
			z-index: 1;
		}

		.hero .container {
			position: relative;
			z-index: 2;
		}

		/* background for features */
		.features-section {
			background: white;
			background-attachment: fixed;
			background-size: cover;
			background-position: center;
			position: relative;
			color: white;
		}

		.features-section::before {
			content: '';
			position: absolute;
			inset: 0;
			background: none;
			/* dark overlay for readability */
			/* z-index: 1; */
		}

		.features-section .container {
			position: relative;
			z-index: 2;
		}

		/* background for stats */
		.navbar {
			background-color: rgba(0, 0, 0, 0.4);
			/* dark glass */
			backdrop-filter: blur(6px);
		}

		.navbar.scrolled {
			background-color: rgba(3, 31, 57, 0.4) !important;
			transition: background-color 0.3s ease-in-out;
		}
	</style>
</head>

<body>


	<!-- Navbar -->
	<nav class="navbar navbar-expand-lg navbar-dark fixed-top shadow-sm" style="background-color: transparent;">
		<div class="container">
			<a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="#">
				<img src="src/images/logo/logo.png" alt="DEMS Logo" height="30">
				<span>DEMS</span>
			</a>

			<!-- Toggler button for mobile -->
			<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
				aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>

			<!-- Collapsible content -->
			<div class="collapse navbar-collapse" id="navbarNav">
				<ul class="navbar-nav ms-auto">
					<li class="nav-item">
						<a class="nav-link" href="#home">Home</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="#features">About</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="./dist/pages/auth/log_in.php">Log In</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="./dist/pages/auth/user_registration.php">Register</a>
					</li>
				</ul>
			</div>
		</div>
	</nav>
	<script>
		document.addEventListener("DOMContentLoaded", function() {
			const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
			const sections = document.querySelectorAll('section[id]');

			const observer = new IntersectionObserver(
				entries => {
					entries.forEach(entry => {
						if (entry.isIntersecting) {
							const id = entry.target.getAttribute('id');
							navLinks.forEach(link => {
								link.classList.remove('active');
								if (link.getAttribute('href') === `#${id}`) {
									link.classList.add('active');
								}
							});
						}
					});
				}, {
					root: null,
					rootMargin: '0px',
					threshold: 0.6 // Adjust as needed
				}
			);

			sections.forEach(section => observer.observe(section));
		});
	</script>




	<!-- Hero Section -->
	<section id="home" class="hero d-flex align-items-center">
		<div class="container text-center d-flex flex-column justify-content-center">
			<h1 class="display-4 fw-bold">Disaster Evacuation Management System</h1>
			<p class="lead mt-3">Real-time alerts. Smart tracking. Efficient evacuation.</p>
			<a href="#features" class="btn btn-light btn-sm mt-4 d-block mx-auto" style="width: 100px;">Learn More</a>
		</div>
	</section>

	<!-- Features Section -->
	<section id="features" class="features-section py-5 min-vh-100 bg-light d-flex align-items-center position-relative overflow-hidden">

		<!-- Flying Plane -->
		<div class="plane-wrapper">
			<img src="src/images/logo/plane.png" alt="Flying Plane" class="flying-plane">
			<span class="cloud-puff"></span>
			<span class="cloud-puff delay-1"></span>
			<span class="cloud-puff delay-2"></span>
			<span class="cloud-puff delay-3"></span>
		</div>


		<div class="container text-center">
			<div class="row justify-content-center text-black g-4">
				<div class="col-md-3 mb-3 feature-box border border-light rounded p-3">
					<div class="feature-icon mb-3">📍</div>
					<h5>Location Tracking</h5>
					<p>Monitor evacuees and shelters with live GPS updates.</p>
				</div>
				<div class="col-md-3 mb-3 feature-box border border-light rounded p-3">
					<div class="feature-icon mb-3">📢</div>
					<h5>Emergency Alerts</h5>
					<p>Instant SMS and email notifications for evacuation orders.</p>
				</div>
				<div class="col-md-3 mb-3 feature-box border border-light rounded p-3">
					<div class="feature-icon mb-3">📊</div>
					<h5>Data Dashboard</h5>
					<p>Track statistics and resource distribution in real-time.</p>
				</div>
			</div>
		</div>
	</section>
	<style>
		.plane-wrapper {
			position: absolute;
			top: 20%;
			left: -150px;
			animation: flyAcross 10s linear infinite;
			display: inline-block;
			z-index: 1;
		}

		.flying-plane {
			width: 100px;
			z-index: 2;
			position: relative;
			rotate: -5deg;
		}

		/* Cloud trail puffs */
		.cloud-puff {
			position: absolute;
			bottom: 10px;
			left: -40px;
			width: 25px;
			height: 25px;
			background: rgba(0, 0, 0, 0.6);
			border-radius: 50%;
			filter: blur(4px);
			animation: puffTrail 3s ease-in-out infinite;
			z-index: 0;
		}

		/* Optional variations */
		.cloud-puff.delay-1 {
			animation-delay: 0.5s;
			left: -60px;
			width: 20px;
			height: 20px;
		}

		.cloud-puff.delay-2 {
			animation-delay: 1s;
			left: -80px;
			width: 30px;
			height: 30px;
		}

		.cloud-puff.delay-3 {
			animation-delay: 1.5s;
			left: -100px;
			width: 22px;
			height: 22px;
		}

		@keyframes puffTrail {
			0% {
				transform: scale(0.8) translateY(0);
				opacity: 0.7;
			}

			50% {
				transform: scale(1.2) translateY(-10px);
				opacity: 0.5;
			}

			100% {
				transform: scale(1.6) translateY(-20px);
				opacity: 0;
			}
		}

		@keyframes flyAcross {
			0% {
				transform: translateX(0) rotate(0deg);
				opacity: 0.8;
			}

			50% {
				opacity: 1;
			}

			100% {
				transform: translateX(120vw) rotate(10deg);
				opacity: 0;
			}
		}
	</style>


	<style>
		.feature-icon {
			font-size: 2rem;
			animation: floatIcon 2s ease-in-out infinite;
			display: inline-block;
		}

		@keyframes floatIcon {
			0% {
				transform: translateY(0);
			}

			50% {
				transform: translateY(-10px);
			}

			100% {
				transform: translateY(0);
			}
		}

		.feature-box:hover .feature-icon {
			animation: bounceIcon 0.6s;
		}

		@keyframes bounceIcon {
			0% {
				transform: scale(1);
			}

			30% {
				transform: scale(1.3);
			}

			60% {
				transform: scale(0.9);
			}

			100% {
				transform: scale(1);
			}
		}
	</style>

	<script>
		document.addEventListener("DOMContentLoaded", () => {
			const boxes = document.querySelectorAll('.feature-box');

			const observer = new IntersectionObserver(entries => {
				entries.forEach(entry => {
					if (entry.isIntersecting) {
						entry.target.classList.add('visible');
					} else {
						entry.target.classList.remove('visible');
					}
				});
			}, {
				threshold: 0.2
			});

			boxes.forEach(box => observer.observe(box));
		});
	</script>


	<!-- Contact Section -->
	<section id="features" class="py-5 min-vh-100 d-flex align-items-center bg-light position-relative overflow-hidden">
		<div class="phone-animation-container position-absolute w-100 h-100 top-0 start-0"></div>
		<!-- Foreground content -->
		<div class="container position-relative" style="z-index: 1;">
			<div class="row g-5 align-items-center">
				<!-- Emergency Contact Info -->
				<div class="col-md-6 text-center text-md-start">
					<h3 class="fw-bold mb-3">📞 Emergency Contact</h3>
					<p class="mb-1">If you are in danger or need assistance, call:</p>
					<h4 class="text-danger fw-bold">911 / 0987‑654‑3210</h4>
				</div>

				<!-- Inquiry Form -->
				<div class="col-md-6">
					<div class="card shadow-lg border-0 rounded-4">
						<div class="card-body p-4">
							<h4 class="fw-semibold mb-3 text-center">Quick Inquiry</h4>
							<form id="contact-form">
								<div class="mb-3">
									<label for="name">Name</label>
									<input type="text" id="name" name="name" class="form-control rounded-3" placeholder="Your Name">
								</div>
								<div class="mb-3">
									<label for="email">Email Address</label>
									<input type="email" id="email" name="email" class="form-control rounded-3" placeholder="Your Email">
								</div>
								<div class="mb-3">
									<label for="message">Message</label>
									<textarea class="form-control rounded-3" id="message" name="message" rows="4" placeholder="Your Message"></textarea>
								</div>
								<div class="d-grid">
									<button type="submit" class="btn btn-primary btn-lg rounded-3">Send Message</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- Email Backgroung Style -->

	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

	<style>
		.phone-animation-container {
			z-index: 1;
			pointer-events: none;
		}

		.phone-animation-container i {
			position: absolute;
			color: #007bff;
			font-size: 2rem;
			opacity: 0.3;
			animation: phone-float 10s linear infinite;
		}

		@keyframes phone-float {
			0% {
				transform: translateY(-10vh) rotate(0deg);
				opacity: 0;
			}

			10% {
				opacity: 0.3;
			}

			50% {
				opacity: 0.5;
			}

			100% {
				transform: translateY(110vh) rotate(360deg);
				opacity: 0;
			}
		}
	</style>

	<!-- Email background animation -->
	<script>
		document.addEventListener('DOMContentLoaded', () => {
			const container = document.querySelector('.phone-animation-container');
			const phoneCount = 10;

			for (let i = 0; i < phoneCount; i++) {
				const icon = document.createElement('i');
				icon.classList.add('fas', 'fa-phone');
				icon.style.left = Math.random() * 100 + '%';
				icon.style.top = '-10%';
				icon.style.animationDelay = `${Math.random() * 6}s`;
				icon.style.animationDuration = `${5 + Math.random() * 5}s`;
				container.appendChild(icon);
			}
		});
	</script>

	<!-- FOR EMAIL SECTION -->

	<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/@emailjs/browser@4/dist/email.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script>
		(function() {
			emailjs.init({
				publicKey: "glaLEYSsKmM6c8uHi",
			});
		})();
		window.onload = function() {
			document.getElementById('contact-form').addEventListener('submit', function(event) {
				event.preventDefault();

				// Show loading SweetAlert
				Swal.fire({
					title: 'Sending...',
					text: 'Please wait while we send your message.',
					allowOutsideClick: false,
					didOpen: () => {
						Swal.showLoading();
					}
				});

				// Send the form
				emailjs.sendForm('service_wynbernard', 'template_wbbp0vr', this)
					.then(() => {
						Swal.fire({
							icon: 'success',
							title: 'Message Sent!',
							text: 'Your inquiry has been successfully sent.',
							confirmButtonColor: '#3085d6',
							confirmButtonText: 'OK'
						});
						this.reset(); // Clear the form
					}, (error) => {
						console.error('FAILED...', error);
						Swal.fire({
							icon: 'error',
							title: 'Oops...',
							text: 'Failed to send your message. Please try again.',
							confirmButtonColor: '#d33'
						});
					});
			});
		}
	</script>
	<script>
		document.addEventListener("DOMContentLoaded", function() {
			const navbar = document.querySelector('.navbar');
			const lightSections = document.querySelectorAll('section.bg-light');

			const observer = new IntersectionObserver(
				(entries) => {
					let overlapping = false;

					entries.forEach(entry => {
						if (entry.isIntersecting) {
							overlapping = true;
						}
					});

					if (overlapping) {
						navbar.classList.add('scrolled');
					} else {
						navbar.classList.remove('scrolled');
					}
				}, {
					root: null,
					threshold: 0.5
				}
			);

			lightSections.forEach(section => observer.observe(section));
		});
	</script>



	<!-- Footer -->
	<footer class="text-center">
		<div class="container">
			<p class="mb-1">&copy; 2025 Disaster Evacuation Management System</p>
			<a href="#">Home</a> | <a href="#">Privacy Policy</a> | <a href="#">Terms</a>
		</div>
	</footer>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>