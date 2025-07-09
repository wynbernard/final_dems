<div class="content-wrapper">
				<div class="content-header">
					<div class="container-fluid">
						<div class="row mb-2">
							<div class="col-sm-6">
								<h1 class="m-0">Admin Profile</h1>
							</div>
						</div>
					</div>
				</div>

				<!-- Main content -->
				<section class="content">
					<div class="container-fluid">
						<div class="row">
							<div class="col-md-3">
								<!-- Profile Image -->
								<div class="card card-primary card-outline">
									<div class="card-body box-profile">
										<div class="text-center">
											<img class="profile-user-img img-fluid img-circle"
												src="../../../dist/assets/img/user2-160x160.jpg"
												alt="User profile picture">
										</div>
										<h3 class="profile-username text-center"><?php echo htmlspecialchars($admin['f_name'] . ' ' . $admin['l_name']); ?></h3>
										<p class="text-muted text-center">Administrator</p>
									</div>
								</div>
							</div>

							<!-- Profile Details -->
							<div class="col-md-9">
								<div class="card">
									<div class="card-header p-2">
										<ul class="nav nav-pills">
											<li class="nav-item"><a class="nav-link active" href="#details" data-toggle="tab">Details</a></li>
											<li class="nav-item"><a class="nav-link" href="#settings" data-toggle="tab">Settings</a></li>
										</ul>
									</div>
									<div class="card-body">
										<div class="tab-content">
											<!-- Profile Details -->
											<div class="active tab-pane" id="details">
												<strong><i class="fas fa-user mr-1"></i> Full Name</strong>
												<p class="text-muted"><?php echo htmlspecialchars($admin['f_name'] . ' ' . $admin['l_name']); ?></p>
												<hr>

												<strong><i class="fas fa-envelope mr-1"></i> Email</strong>
												<p class="text-muted"><?php echo htmlspecialchars($admin['email']); ?></p>
												<hr>

												<strong><i class="fas fa-phone mr-1"></i> Contact</strong>
												<p class="text-muted"><?php echo htmlspecialchars($admin['contact']); ?></p>
												<hr>

												<strong><i class="fas fa-map-marker-alt mr-1"></i> Address</strong>
												<p class="text-muted"><?php echo htmlspecialchars($admin['address']); ?></p>
											</div>

											<!-- Profile Settings -->
											<div class="tab-pane" id="settings">
												<form action="update_profile.php" method="POST">
													<div class="form-group">
														<label for="email">Email</label>
														<input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($admin['email']); ?>">
													</div>
													<div class="form-group">
														<label for="contact">Contact</label>
														<input type="text" name="contact" class="form-control" value="<?php echo htmlspecialchars($admin['contact']); ?>">
													</div>
													<div class="form-group">
														<label for="address">Address</label>
														<input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($admin['address']); ?>">
													</div>
													<button type="submit" class="btn btn-primary">Update Profile</button>
												</form>
											</div>
										</div>
									</div>
								</div>
							</div>

						</div>
					</div>
				</section>
			</div>
