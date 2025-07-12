<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Landing Page</title>
	<!-- Bootstrap CSS -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<style>
		/* Full-screen background image */
		.hero-section {
			background: url('src/images/new.png') no-repeat center center/cover;
			height: 100vh;
			position: relative;
			color: white;
		}

		/* Transparent navbar */
		.navbar-transparent {
			background-color: transparent;
			/* Fully transparent */
			position: absolute;
			width: 100%;
			top: 0;
			left: 0;
			z-index: 1000;
		}


		h1,
		p {
			text-shadow: 0 2px 6px rgba(0, 0, 0, 0.7);
		}

		.btn-primary {
			background-color: #007bff;
			border: none;
		}
	</style>
	qwejoqekoqndikoaniheq
</head>

<body>

	<!-- Transparent Navbar -->
	<nav class="navbar navbar-expand-lg navbar-dark navbar-transparent">
		<div class="container-fluid">
			<a class="navbar-brand fs-6 d-flex align-items-center gap-2" href="#">
				<img src="src/images/logo2.png" alt="Logo" style="height: 60px;">
				<h6 class="mb-0 text-dark">Disaster Evacuation <br> Management System</h6>
			</a>
			<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
				aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>
			<div class="collapse navbar-collapse" id="navbarNav">
				<ul class="navbar-nav ms-auto">
					<li class="nav-item">
						<a class="nav-link active" href="#">Home</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="./dist/pages/auth/log_in.php">Log In</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="./dist/pages/auth/user_registration.php">Register</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="#"></a>
					</li>
				</ul>
			</div>
		</div>
	</nav>


	<!-- Hero Section -->
	<div class="hero-section" style="min-height: 100vh; position: relative; display: flex; align-items: center; justify-content: space-between;">
		<div class="hero-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0;"></div>
		<!-- Left Side Content -->
		<div class="container hero-content z-1" style="padding-top: 70px; flex: 1; margin-left: 100px; border: none; box-shadow: none;">
			<div class="row">
				<div class="col-md-12">
					<h1 class="display-4 fw-bold" style="color:rgb(14, 109, 146); text-shadow: 1px 1px 4px rgba(0,0,0,0.4);">
						Disaster Evacuation <br> Management System
					</h1>
					<p class="lead" style="color:rgb(19, 140, 188);">
						Your success starts here. Build your vision with us.
					</p>
					<!-- <a href="#" class="btn btn-primary btn-lg mt-3">Get Started</a> -->
				</div>
			</div>
		</div>
		<!-- Right Side Image -->
		<div class="hero-image" style="flex: 1; display: flex; justify-content: center; align-items: center;">
			<img src="src/images/side-bg.png" alt="Hero Image" style="max-width: 100%; height: auto; border-radius: 10px;">
		</div>
	</div>

	<!-- Responsive Styles -->
	<style>
		/* Remove left margin for small screens (mobile view) */
		@media (max-width: 768px) {
			.hero-content {
				margin-left: 0 !important;
				margin-top: 50px;
				/* Removes the margin on mobile */
			}

			.navbar-transparent {
				background-color: rgba(0, 0, 0, 0.8);
				/* Change this to the color you want for mobile */
			}
		}
	</style>

	<!-- Responsive Styles -->
	<style>
		/* Responsive for smaller screens */
		@media (max-width: 991px) {
			.hero-section {
				flex-direction: column;
				margin-left: 0;
			}

			.hero-content {
				padding-top: 50px;
				text-align: center;
			}

			.hero-image {
				width: 100%;
				margin-top: 0px;
			}
		}

		/* Extra small devices (mobile) */
		@media (max-width: 576px) {
			.hero-section {
				margin-left: 0;
			}

			.hero-content {
				padding-top: 100px;
				margin-left: 0;
			}

			.hero-image img {
				width: 80%;
			}

		}
	</style>
</body>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>