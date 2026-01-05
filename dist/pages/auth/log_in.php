<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Login</title>
	<!-- Bootstrap 5 CSS -->
	<link rel="stylesheet" href="../../../dist/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
	<link rel="stylesheet" href="../css/auth/lol_in.css">
	<script src="../scripts/auth_script/log_in.js"></script>
</head>
<style>
	.login-page {
		background: url('../../../src/images/new.jpg') no-repeat center center fixed;
		background-size: cover;
	}
</style>

<body class="login-page d-flex justify-content-center align-items-center vh-100 bg-light">
	<div class="container">
		<div class="row login-container bg-white">
			<!-- Left Side - Background Image -->
			<div class="col-md-6 d-none d-md-flex bg-image align-items-center justify-content-center">
				<img src="../../../src/images/bagonhon.png" alt="Logo" 	 width="250" height="250">
			</div>

			<!-- Right Side - Login Form -->
			<div class="col-md-6 p-5">
				<div class="text-center mb-4">
					<img src="../../../src/images/bagonhon.png" alt="Logo" width="80">
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
						<a href="#" onclick="forgotPassword()" class="text-decoration-none">Forgot Password?</a>
					</div>
					<button type="submit" class="btn btn-primary w-100">Login</button>
				</form>

				<div class="text-center mt-3">
					<p>User Pre-Registration <a href="user_registration.php" class="text-primary" id="registerLink">Sign up</a></p>
				</div>
			</div>
		</div>
	</div>
	<script src="../../../dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
<?php
include '../../../database/conn.php';
require_once '../../../database/rate_limit.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$username = trim($_POST['username']);
	$password = trim($_POST['password']);
	
	// Rate limiting: Check if user has exceeded login attempts
	$rate_limit = rate_limit_check('login', $username, 5, 300); // 5 attempts per 5 minutes
	
	if (!$rate_limit['allowed']) {
		$_SESSION['notification'] = "<span style='color:red'><i class='bi bi-exclamation-circle-fill'></i></span> " . $rate_limit['message'];
		header("Location: log_in.php");
		exit();
	}

	// Check admin table first - use password_verify() for secure password checking
	$stmt = $conn->prepare("SELECT * FROM admin_table WHERE username = ?");
	$stmt->bind_param("s", $username);
	$stmt->execute();
	$result = $stmt->get_result();
	$admin = $result->fetch_assoc();

	// Verify password using password_verify() (handles both hashed and plaintext during migration)
	if ($admin) {
		$password_valid = false;
		
		// Check if password is already hashed (starts with $2y$, $2a$, or $2b$ for bcrypt)
		if (strpos($admin['password'], '$2y$') === 0 || strpos($admin['password'], '$2a$') === 0 || strpos($admin['password'], '$2b$') === 0) {
			// Password is hashed, use password_verify()
			$password_valid = password_verify($password, $admin['password']);
		} else {
			// Password is plaintext (legacy), compare directly for migration period
			$password_valid = ($admin['password'] === $password);
			
			// Auto-migrate: hash the plaintext password if login succeeds
			if ($password_valid) {
				$hashed_password = password_hash($password, PASSWORD_DEFAULT);
				$updateStmt = $conn->prepare("UPDATE admin_table SET password = ? WHERE admin_id = ?");
				$updateStmt->bind_param("si", $hashed_password, $admin['admin_id']);
				$updateStmt->execute();
				$updateStmt->close();
			}
		}
		
		if ($password_valid) {
			// Clear rate limit on successful login
			rate_limit_clear('login', $username);
			
			session_regenerate_id(true);
			$session_token = bin2hex(random_bytes(32));

			$_SESSION['admin_id'] = $admin['admin_id'];
			$_SESSION['username'] = $admin['username'];
			$_SESSION['session_token'] = $session_token;

			$updateToken = $conn->prepare("UPDATE admin_table SET session_token = ? WHERE admin_id = ?");
			$updateToken->bind_param("si", $session_token, $admin['admin_id']);
			$updateToken->execute();
			$updateToken->close();

			header("Location: ../admin_page/Dashboard.php");
			exit();
		} else {
			// Record failed login attempt for rate limiting
			rate_limit_record_attempt('login', $username);
		}
	}

	// Check pre_reg_table if not found in admin_table
	$stmt = $conn->prepare("SELECT * FROM pre_reg_table WHERE email_address = ?");
	$stmt->bind_param("s", $username);
	$stmt->execute();
	$result = $stmt->get_result();
	$preRegUser = $result->fetch_assoc();

	if ($preRegUser && password_verify($password, $preRegUser['password'])) {
		// Clear rate limit on successful login
		rate_limit_clear('login', $username);
		
		session_regenerate_id(true);
		$session_token = bin2hex(random_bytes(32));

		$_SESSION['pre_reg_id'] = $preRegUser['pre_reg_id'];
		$_SESSION['email_address'] = $preRegUser['email_address'];
		$_SESSION['user_session_token'] = $session_token;

		// Prompt the user to save their current device coordinates after login
		$_SESSION['prompt_save_location'] = true;

		$updateToken = $conn->prepare("UPDATE pre_reg_table SET user_session_token = ? WHERE pre_reg_id = ?");
		$updateToken->bind_param("si", $session_token, $preRegUser['pre_reg_id']);
		$updateToken->execute();
		header("Location: ../user_page/Dashboard.php");
		exit();
	}
	
	// If login fails for both, record failed attempt
	rate_limit_record_attempt('login', $username);
	
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
			1
			icon.classList.remove("fa-eye-slash");
			icon.classList.add("fa-eye");
		} else {
			input.type = "password";
			icon.classList.remove("fa-eye");
			icon.classList.add("fa-eye-slash");
		}
	}
</script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
	function forgotPassword() {
		Swal.fire({
			title: 'Forgot Password?',
			input: 'email',
			inputLabel: 'Enter your email address',
			inputPlaceholder: 'you@example.com',
			inputAttributes: {
				required: true
			},
			showCancelButton: true,
			confirmButtonText: 'Send Reset Link',
			preConfirm: async (email) => {
				if (!email) {
					Swal.showValidationMessage('⚠️ Email is required');
					return;
				}

				try {
					const response = await fetch('forgot_password.php', {
						method: 'POST',
						headers: {
							'Content-Type': 'application/json'
						},
						body: JSON.stringify({
							email
						})
					});

					const text = await response.text(); // Get raw response first

					try {
						const data = JSON.parse(text); // Try to parse JSON
						if (!data.success) throw new Error(data.message);
						return data;
					} catch (jsonError) {
						console.error("❌ JSON parse error:", jsonError);
						console.error("⚠️ Raw response:", text);
						throw new Error("Server error or invalid response:\n" + text);
					}

				} catch (error) {
					Swal.showValidationMessage(`❌ ${error.message}`);
				}
			}
		}).then((result) => {
			if (result.isConfirmed && result.value?.success) {
				Swal.fire('✅ Sent!', result.value.message, 'success');
			}
		});
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
<style>
	.login-container {
		max-width: 900px;
		margin: auto;
		border-radius: 10px;
		overflow: hidden;
		box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
	}

	.form-group {
		position: relative;
		margin-bottom: 1.5rem;
	}

	.form-group .form-label {
		position: absolute;
		top: 10px;
		left: 12px;
		font-size: 14px;
		color: #6c757d;
		background: white;
		padding: 0 5px;
		transition: all 0.2s ease-out;
		pointer-events: none;
		opacity: 0;
		transform: translateY(0);
		z-index: 1;
	}

	.form-group input:focus+.form-label,
	.form-group input:not(:placeholder-shown)+.form-label {
		top: 0;
		left: 10px;
		font-size: 12px;
		opacity: 1;
		transform: translateY(-50%);
	}

	/* Make sure empty inputs show placeholder */
	.form-control::placeholder {
		opacity: 1;
		transition: opacity 0.2s ease;
		color: #adb5bd;
	}

	/* Hide placeholder when focused or when has value */
	.form-control:focus::placeholder,
	.form-control:not(:placeholder-shown)::placeholder {
		opacity: 0;
	}

	.swal2-icon.swal2-info {
		color: red !important;
	}
</style>