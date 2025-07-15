<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Login</title>
	<!-- Bootstrap 5 CSS -->
	<link rel="stylesheet" href="../../../dist/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
	<link rel="stylesheet" href="../css/auth/log_in.css">
	<script src="../scripts/auth_script/log_in.js"></script>
</head>

<body class="d-flex justify-content-center align-items-center vh-100 bg-light">
	<div class="container">
		<div class="row login-container bg-white">
			<!-- Left Side - Background Image -->
			<div class="col-md-6 d-none d-md-block bg-image"></div>

			<!-- Right Side - Login Form -->
			<div class="col-md-6 p-5">
				<div class="text-center mb-4">
					<img src="../../../src/images/logo/images.png" alt="Logo" class="rounded-circle" width="80">
				</div>
				<h4 class="text-center mb-4">Sign In</h4>
				<?php
				session_start();
				include '../alert/warning.php';

				if (isset($_SESSION['notification'])):
					$notification = addslashes($_SESSION['notification']);
					unset($_SESSION['notification']);
				?>
					<script>
						document.addEventListener("DOMContentLoaded", function() {
							const Toast = Swal.mixin({
								toast: true,
								position: 'top-end',
								showConfirmButton: false,
								timer: 3000,
								timerProgressBar: true,
								didOpen: (toast) => {
									toast.style.fontSize = '0.9rem'; // Smaller font
								}
							});

							Toast.fire({
								icon: 'info',
								title: '<?php echo $notification; ?>'
							});
						});
					</script>
				<?php endif; ?>


				<form method="POST" action="log_in.php">
					<div class="mb-3 form-group">
						<label class="form-label">Username or Email</label>
						<input type="text" name="username" class="form-control" placeholder="Username or Email" required>
					</div>
					<div class="mb-3 form-group position-relative">
						<label class="form-label">Password</label>
						<div class="input-group">
							<input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
							<span class="input-group-text bg-white border-start-0" style="cursor: pointer;" onclick="togglePasswordVisibility()">
								<i id="toggleIcon" class="fa fa-eye-slash"></i>
							</span>
						</div>
					</div>
					<div class="d-flex justify-content-between mb-3">
						<div class="form-check">
							<input type="checkbox" class="form-check-input" id="rememberMe">
							<label class="form-check-label" for="rememberMe">Remember Me</label>
						</div>
						<a href="#" class="text-decoration-none">Forgot Password?</a>
					</div>
					<button type="submit" class="btn btn-primary w-100">Login</button>
				</form>

				<div class="text-center mt-3">
					<p>User Pre-Registration <a href="user_registration.php" class="text-primary">Sign up</a></p>
				</div>
			</div>
		</div>
	</div>
	<script src="../../../dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
<?php
include '../../../database/conn.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$username = trim($_POST['username']);
	$password = trim($_POST['password']);

	// Check admin table first
	$stmt = $conn->prepare("SELECT * FROM admin_table WHERE username = ? AND password = ?");
	$stmt->bind_param("ss", $username, $password);
	$stmt->execute();
	$result = $stmt->get_result();
	$admin = $result->fetch_assoc();

	if ($admin) {
		session_regenerate_id(true);
		$session_token = bin2hex(random_bytes(32));

		$_SESSION['admin_id'] = $admin['admin_id'];
		$_SESSION['username'] = $admin['username'];
		$_SESSION['session_token'] = $session_token;

		$updateToken = $conn->prepare("UPDATE admin_table SET session_token = ? WHERE admin_id = ?");
		$updateToken->bind_param("si", $session_token, $admin['admin_id']);
		$updateToken->execute();

		header("Location: ../admin_page/dashboard.php");
		exit();
	}

	// Check pre_reg_table if not found in admin_table
	$stmt = $conn->prepare("SELECT * FROM pre_reg_table WHERE email_address = ?");
	$stmt->bind_param("s", $username);
	$stmt->execute();
	$result = $stmt->get_result();
	$preRegUser = $result->fetch_assoc();

	if ($preRegUser && password_verify($password, $preRegUser['password'])) {
		session_regenerate_id(true);
		$session_token = bin2hex(random_bytes(32));

		$_SESSION['pre_reg_id'] = $preRegUser['pre_reg_id'];
		$_SESSION['email_address'] = $preRegUser['email_address'];
		$_SESSION['user_session_token'] = $session_token;

		$updateToken = $conn->prepare("UPDATE pre_reg_table SET user_session_token = ? WHERE pre_reg_id = ?");
		$updateToken->bind_param("si", $session_token, $preRegUser['pre_reg_id']);
		$updateToken->execute();
		header("Location: ../user_page/dashboard.php");
		exit();
	}
	// If login fails for both
	$_SESSION['notification'] = "<span style='color:red'><i class='bi bi-exclamation-circle-fill'></i></span> Incorrect username or password!";
	header("Location: log_in.php");
	exit();
}
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<script>
	function togglePasswordVisibility() {
		const input = document.getElementById('password');
		const icon = document.getElementById('toggleIcon');
		if (input.type === "password") {
			input.type = "text";
			icon.classList.remove("fa-eye-slash");
			icon.classList.add("fa-eye");
		} else {
			input.type = "password";
			icon.classList.remove("fa-eye");
			icon.classList.add("fa-eye-slash");
		}
	}
</script>

<style>
	.input-group {
		position: relative;
	}

	.input-group input {
		padding-right: 2.5rem;
		/* space for the icon */
	}

	.input-group .input-group-text {
		position: absolute;
		top: 50%;
		right: 1px;
		transform: translateY(-50%);
		cursor: pointer;
		color: #6c757d;
		background: none;
		border: none;
		z-index: 10;
		/* ensure it's above the input */
	}
</style>