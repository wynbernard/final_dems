<nav class="app-header navbar navbar-expand bg-body">
	<!--begin::Container-->
	<div class="container-fluid">
		<!--begin::Start Navbar Links-->
		<ul class="navbar-nav">
			<li class="nav-item">
				<a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
					<i class="bi bi-list"></i>
				</a>
			</li>
			<li class="nav-item d-none d-md-block"><a href="#" class="nav-link">Home</a></li>
			<li class="nav-item d-none d-md-block"><a href="#" class="nav-link">Contact</a></li>
		</ul>
		<!--end::Start Navbar Links-->
		<!--begin::End Navbar Links-->
		<ul class="navbar-nav ms-auto">
			<li class="nav-item dropdown">
				<a class="nav-link" data-bs-toggle="dropdown" href="#">
					<i class="bi bi-chat-text"></i>
					<span class="navbar-badge badge text-bg-danger">3</span>
				</a>
				<div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
					<a href="#" class="dropdown-item">
						<!--begin::Message-->
						<div class="d-flex">
							<div class="flex-shrink-0">
								<img
									src="../../dist/assets/img/user1-128x128.jpg"
									alt="User Avatar"
									class="img-size-50 rounded-circle me-3" />
							</div>
							<div class="flex-grow-1">
								<h3 class="dropdown-item-title">
									Brad Diesel
									<span class="float-end fs-7 text-danger"><i class="bi bi-star-fill"></i></span>
								</h3>
								<p class="fs-7">Call me whenever you can...</p>
								<p class="fs-7 text-secondary">
									<i class="bi bi-clock-fill me-1"></i> 4 Hours Ago
								</p>
							</div>
						</div>
						<!--end::Message-->
					</a>
					<div class="dropdown-divider"></div>
					<a href="#" class="dropdown-item">
						<!--begin::Message-->
						<div class="d-flex">
							<div class="flex-shrink-0">
								<img
									src="../../dist/assets/img/user8-128x128.jpg"
									alt="User Avatar"
									class="img-size-50 rounded-circle me-3" />
							</div>
							<div class="flex-grow-1">
								<h3 class="dropdown-item-title">
									John Pierce
									<span class="float-end fs-7 text-secondary">
										<i class="bi bi-star-fill"></i>
									</span>
								</h3>
								<p class="fs-7">I got your message bro</p>
								<p class="fs-7 text-secondary">
									<i class="bi bi-clock-fill me-1"></i> 4 Hours Ago
								</p>
							</div>
						</div>
						<!--end::Message-->
					</a>
					<div class="dropdown-divider"></div>
					<a href="#" class="dropdown-item">
						<!--begin::Message-->
						<div class="d-flex">
							<div class="flex-shrink-0">
								<img
									src="../../dist/assets/img/user3-128x128.jpg"
									alt="User Avatar"
									class="img-size-50 rounded-circle me-3" />
							</div>
							<div class="flex-grow-1">
								<h3 class="dropdown-item-title">
									Nora Silvester
									<span class="float-end fs-7 text-warning">
										<i class="bi bi-star-fill"></i>
									</span>
								</h3>
								<p class="fs-7">The subject goes here</p>
								<p class="fs-7 text-secondary">
									<i class="bi bi-clock-fill me-1"></i> 4 Hours Ago
								</p>
							</div>
						</div>
						<!--end::Message-->
					</a>
					<div class="dropdown-divider"></div>
					<a href="#" class="dropdown-item dropdown-footer">See All Messages</a>
				</div>
			</li>
			<!--end::Messages Dropdown Menu-->
			<!--begin::Notifications Dropdown Menu-->
			<li class="nav-item dropdown">
				<a class="nav-link" data-bs-toggle="dropdown" href="#">
					<i class="bi bi-bell-fill"></i>
					<span class="navbar-badge badge text-bg-warning">15</span>
				</a>
				<div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
					<span class="dropdown-item dropdown-header">15 Notifications</span>
					<div class="dropdown-divider"></div>
					<a href="#" class="dropdown-item">
						<i class="bi bi-envelope me-2"></i> 4 new messages
						<span class="float-end text-secondary fs-7">3 mins</span>
					</a>
					<div class="dropdown-divider"></div>
					<a href="#" class="dropdown-item">
						<i class="bi bi-people-fill me-2"></i> 8 friend requests
						<span class="float-end text-secondary fs-7">12 hours</span>
					</a>
					<div class="dropdown-divider"></div>
					<a href="#" class="dropdown-item">
						<i class="bi bi-file-earmark-fill me-2"></i> 3 new reports
						<span class="float-end text-secondary fs-7">2 days</span>
					</a>
					<div class="dropdown-divider"></div>
					<a href="#" class="dropdown-item dropdown-footer"> See All Notifications </a>
				</div>
			</li>
			<!--end::Notifications Dropdown Menu-->
			<!--begin::Fullscreen Toggle-->
			<li class="nav-item">
				<a class="nav-link" href="#" data-lte-toggle="fullscreen">
					<i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
					<i data-lte-icon="minimize" class="bi bi-fullscreen-exit" style="display: none"></i>
				</a>
			</li>
			<!--end::Fullscreen Toggle-->
			<!--begin::User Menu Dropdown-->
			<li class="nav-item dropdown user-menu">
				<a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
					<img
						src="<?php echo !empty($user['profile_pic']) ? htmlspecialchars($user['profile_pic']) : '../../../dist/assets/img/user2-160x160.jpg'; ?>"
						class="user-image rounded-circle shadow"
						style="object-fit: cover;"
						alt="User Image" />
					<span class="d-none d-md-inline"><?php echo htmlspecialchars($user['f_name'] . ' ' . $user['l_name']); ?></span>
				</a>
				<ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
					<!--begin::User Image-->
					<li class="user-header text-bg-primary">
						<img
							src="<?php echo !empty($user['profile_pic']) ? htmlspecialchars($user['profile_pic']) : '../../../dist/assets/img/user2-160x160.jpg'; ?>"
							class="rounded-circle shadow"
							style="object-fit: cover;"
							alt="User Image" />
						<p>
							<?php echo htmlspecialchars($user['f_name'] . ' ' . $user['l_name']); ?>
							<small>
								Member since
								<?php
								$date = !empty($user['registered_date']) ? $user['registered_date'] : '';
								echo $date ? date('F j, Y', strtotime($date)) : '';
								?>
							</small>
						</p>
					</li>
					<!--end::User Image-->
					<!--begin::Menu Body-->
					<li class="user-body">
						<!--begin::Row-->
						<div class="row">
							<div class="col-4 text-center"><a href="#">Followers</a></div>
							<div class="col-4 text-center"><a href="#">Sales</a></div>
							<div class="col-4 text-center"><a href="#">Friends</a></div>
						</div>
						<!--end::Row-->
					</li>
					<!--end::Menu Body-->
					<!--begin::Menu Footer-->
					<li class="user-footer">
						<a href="../user_page/profile_user.php" class="btn btn-default btn-flat">Profile</a>
						<a href="../auth/user_logout.php" id="logoutBtn" class="btn btn-default btn-flat float-end">Sign out</a>
					</li>
					<!--end::Menu Footer-->
					<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
					<!-- SweetAlert2 script for logout confirmation -->
					<script>
						document.getElementById("logoutBtn").addEventListener("click", function(e) {
							e.preventDefault(); // Stop the default link behavior

							Swal.fire({
								title: 'Are you sure?',
								text: "You will be logged out.",
								icon: 'warning',
								showCancelButton: true,
								confirmButtonColor: '#3085d6',
								cancelButtonColor: '#d33',
								confirmButtonText: 'Yes, log me out!'
							}).then((result) => {
								if (result.isConfirmed) {
									window.location.href = this.href;
								}
							});
						});
					</script>

				</ul>
			</li>
			<!--end::User Menu Dropdown-->
		</ul>
		<!--end::End Navbar Links-->
	</div>
	<!--end::Container-->
</nav>