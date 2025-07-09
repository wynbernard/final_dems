<!doctype html>
<html lang="en">
<!--begin::Head-->

<?php
include '../../../database/session.php';
include '../layout/head_links.php';
?>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
	<!--begin::App Wrapper-->
	<div class="app-wrapper">
		<?php
		include '../layout/header.php';
		include '../layout/sidebar.php';
		?>
		<main class="app-main">
			<div class="content">
				<div class="row justify-content-center"> <!-- Center content -->
					<!-- Edit Profile Card -->
					<div class="col-12 col-md-8 col-lg-7 mt-4 px-3"> <!-- Adjust width for mobile -->
						<div class="card shadow-lg">
							<div class="card-header text-center">
								<h5 class="title">Edit Profile</h5>
							</div>
							<div class="card-body">
								<?php
								include '../action/fetch_admin.php';
								include '../alert/warning.php';
								?>
								<form>
									<div class="row">
										<div class="col-md-6">
											<div class="form-group">
												<label>Username</label>
												<input type="text" class="form-control" name="username"
													value="<?php echo htmlspecialchars($admin['username']); ?>" readonly>
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label>Password</label>
												<input type="password" class="form-control" name="password"
													value="<?php echo htmlspecialchars($admin['password']); ?>" readonly>
											</div>
										</div>
									</div>
									<div class="row mt-2">
										<div class="col-md-6">
											<div class="form-group">
												<label>First Name</label>
												<input type="text" class="form-control" name="f_name"
													value="<?php echo htmlspecialchars($admin['f_name']); ?>" readonly>
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label>Last Name</label>
												<input type="text" class="form-control" name="l_name"
													value="<?php echo htmlspecialchars($admin['l_name']); ?>" readonly>
											</div>
										</div>
									</div>

									<!-- Edit Profile Button -->
									<div class="text-center mt-3">
										<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">
											<i class="fas fa-edit me-1"></i> Edit Profile
										</button>
									</div>
								</form>
							</div>
						</div>
					</div>

					<?php include '../modal/admin_profile_modal.php'; ?>

					<!-- Profile Card -->
					<div class="col-12 col-md-8 col-lg-4 mt-4 px-3">
						<div class="card shadow-lg rounded-3 overflow-hidden position-relative">
							<!-- Cover Image -->
							<div class="image">
								<img src="../../../dist/assets/img/photo1.png" class="card-img-top" alt="Cover Image"
									style="height: 120px; object-fit: cover;">
							</div>

							<!-- Profile Image Overlapping -->
							<div class="d-flex justify-content-center position-relative" style="margin-top: -35px;">
								<img class="avatar border border-3 border-white rounded-circle"
									src="../../../dist/assets/img/user2-160x160.jpg"
									width="75" height="75">
							</div>

							<div class="card-body text-center pt-3 pb-2">
								<h6 class="title fw-bold mb-0"><?php echo htmlspecialchars($admin['f_name'] . ' ' . $admin['l_name']); ?></h6>
								<p class="text-muted mb-1">@michael24</p>

								<p class="description text-center fst-italic text-secondary mb-2" style="font-size: 12px;">
									"Lamborghini Mercy <br>
									Your chick she so thirsty <br>
									I'm in that two-seat Lambo"
								</p>
							</div>

							<hr class="my-2">

							<!-- Social Buttons -->
							<div class="button-container text-center pb-2">
								<a href="#" class="btn btn-primary btn-sm me-1">
									<i class="fab fa-facebook-f"></i>
								</a>
								<a href="#" class="btn btn-info btn-sm me-1">
									<i class="fab fa-twitter"></i>
								</a>
								<a href="#" class="btn btn-danger btn-sm">
									<i class="fab fa-google-plus-g"></i>
								</a>
							</div>
						</div>
					</div> <!-- End Profile Card -->
				</div>
			</div>
		</main>
		<?php include '../layout/footer.php'; ?>
	</div>
	<?php include '../scripts/scripts.php'; ?>
</body>
<!--end::Body-->

</html>