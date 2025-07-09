<!doctype html>
<html lang="en">

<head>
	<?php include '../../../database/user_session.php'; ?>
	<?php include '../layout_user/head_links.php'; ?>
	<?php include '../css/profile_user.php'; ?>
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
	<div class="app-wrapper">
		<?php include '../layout_user/header.php'; ?>
		<?php include '../layout_user/sidebar.php'; ?>
		<?php include '../alert/warning.php'; ?>
		<?php include '../action_user/fetch_user.php'; ?>
		<main class="app-main">
			<div class="content container-fluid">
				<div class="row justify-content-center g-4">
					<!-- Profile Image Card -->
					<div class="col-lg-4 col-md-6 col-12">
						<div class="profile-image-card">
							<div class="cover-image"></div>
							<div class="avatar-container text-center">
								<img class="avatar rounded-circle"
									src="<?php echo !empty($user['profile_pic']) ? htmlspecialchars($user['profile_pic']) : '../../../dist/assets/img/user2-160x160.jpg'; ?>"
									width="90" height="90" alt="Profile Image">
							</div>
							<div class="card-body text-center pt-2 pb-3">
								<h5 class="user-name mb-1"><?php echo htmlspecialchars($user['f_name'] . ' ' . $user['l_name']); ?></h5>
								<p class="user-email mb-3"><?php echo htmlspecialchars($user['email_address']); ?></p>

								<div class="qr-code-container">
									<img src="<?php echo htmlspecialchars("../../../" . $qrCodePath); ?>" alt="QR Code" class="img-fluid" width="140">
								</div>

								<div class="mt-3">
									<span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
										<i class="fas fa-user-circle me-2"></i>Member
									</span>
								</div>
							</div>
						</div>
					</div>

					<!-- Profile Details Card -->
					<div class="col-lg-7 col-md-8 col-12">
						<div class="profile-card">
							<div class="profile-header d-flex justify-content-between align-items-center">
								<h5 class="title">Profile Information</h5>
							</div>
							<div class="profile-body">


								<form>
									<div class="row">
										<!-- Email Address -->
										<div class="col-md-6">
											<div class="form-group">
												<div class="d-flex justify-content-between align-items-center mb-1">
													<label><i class="fas fa-envelope me-1"></i>Email Address</label>
													<a href="#" data-bs-toggle="modal" data-bs-target="#editEmailModal">
														<i class="fas fa-edit edit-icon"></i>
													</a>
												</div>
												<p class="form-control-plaintext"><?php echo htmlspecialchars($user['email_address']); ?></p>
											</div>
										</div>

										<!-- Password -->
										<div class="col-md-6">
											<div class="form-group">
												<div class="d-flex justify-content-between align-items-center mb-1">
													<label><i class="fas fa-lock me-1"></i>Password</label>
													<a href="#" data-bs-toggle="modal" data-bs-target="#editPasswordModal">
														<i class="fas fa-edit edit-icon"></i>
													</a>
												</div>
												<p class="form-control-plaintext">••••••••••</p>
											</div>
										</div>

										<!-- First Name -->
										<div class="col-md-6">
											<div class="form-group">
												<div class="d-flex justify-content-between align-items-center mb-1">
													<label><i class="fas fa-user me-1"></i>First Name</label>
													<a href="#" data-bs-toggle="modal" data-bs-target="#editFirstNameModal">
														<i class="fas fa-edit edit-icon"></i>
													</a>
												</div>
												<p class="form-control-plaintext"><?php echo htmlspecialchars($user['f_name']); ?></p>
											</div>
										</div>

										<!-- Last Name -->
										<div class="col-md-6">
											<div class="form-group">
												<div class="d-flex justify-content-between align-items-center mb-1">
													<label><i class="fas fa-user me-1"></i>Last Name</label>
													<a href="#" data-bs-toggle="modal" data-bs-target="#editLastNameModal">
														<i class="fas fa-edit edit-icon"></i>
													</a>
												</div>
												<p class="form-control-plaintext"><?php echo htmlspecialchars($user['l_name']); ?></p>
											</div>
										</div>

										<!-- Contact Number -->
										<div class="col-md-6">
											<div class="form-group">
												<div class="d-flex justify-content-between align-items-center mb-1">
													<label><i class="fas fa-phone me-1"></i>Contact Number</label>
													<a href="#" data-bs-toggle="modal" data-bs-target="#editContactModal">
														<i class="fas fa-edit edit-icon"></i>
													</a>
												</div>
												<p class="form-control-plaintext">0<?php echo htmlspecialchars($user['contact_no']); ?></p>
											</div>
										</div>

										<!-- Gender -->
										<div class="col-md-6">
											<div class="form-group">
												<div class="d-flex justify-content-between align-items-center mb-1">
													<label><i class="fas fa-venus-mars me-1"></i>Gender</label>
													<a href="#" data-bs-toggle="modal" data-bs-target="#editGenderModal">
														<i class="fas fa-edit edit-icon"></i>
													</a>
												</div>
												<p class="form-control-plaintext"><?php echo htmlspecialchars($user['gender']); ?></p>
											</div>
										</div>

										<!-- Date of Birth -->
										<div class="col-md-6">
											<div class="form-group">
												<div class="d-flex justify-content-between align-items-center mb-1">
													<label><i class="fas fa-calendar-alt me-1"></i>Date of Birth</label>
													<a href="#" data-bs-toggle="modal" data-bs-target="#editDobModal">
														<i class="fas fa-edit edit-icon"></i>
													</a>
												</div>
												<p class="form-control-plaintext"><?php echo date("F j, Y", strtotime($user['date_of_birth'])); ?></p>
											</div>
										</div>
										<?php if ($user['registered_as'] == 'Solo') { ?>
											<div class="col-md-6">
												<div class="form-group">
													<div class="d-flex justify-content-between align-items-center mb-1">
														<label><i class="fas fa-venus-mars me-1"></i>Address</label>
														<a href="#" data-bs-toggle="modal" data-bs-target="#editAddressModal">
															<i class="fas fa-edit edit-icon"></i>
														</a>
													</div>
													<p class="form-control-plaintext"><?php echo htmlspecialchars($user['address']); ?></p>
												</div>
											</div>
										<?php } ?>
									</div>
								</form>
							</div>
						</div>
					</div>

					<?php include '../modal_profile_user/user_profile_modal.php'; ?>
				</div>
			</div>
		</main>

		<?php include '../layout_user/footer.php'; ?>
	</div>

	<!-- <?php include '../scripts/scripts.php'; ?> -->

	<script>
		// Add some interactive effects
		document.addEventListener('DOMContentLoaded', function() {
			// Add hover effect to edit icons
			const editIcons = document.querySelectorAll('.edit-icon');
			editIcons.forEach(icon => {
				icon.addEventListener('mouseenter', () => {
					icon.classList.add('fa-spin');
				});
				icon.addEventListener('mouseleave', () => {
					icon.classList.remove('fa-spin');
				});
			});

			// Add animation to profile cards when they come into view
			const observer = new IntersectionObserver((entries) => {
				entries.forEach(entry => {
					if (entry.isIntersecting) {
						entry.target.style.opacity = 1;
						entry.target.style.transform = 'translateY(0)';
					}
				});
			}, {
				threshold: 0.1
			});

			const cards = document.querySelectorAll('.profile-card, .profile-image-card');
			cards.forEach(card => {
				card.style.opacity = 0;
				card.style.transform = 'translateY(20px)';
				card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
				observer.observe(card);
			});
		});
	</script>
</body>

</html>