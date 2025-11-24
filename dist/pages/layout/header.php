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
			<!--begin::Database Restore Button-->
			<li class="nav-item">
				<a class="nav-link" href="#" id="dbRestoreBtn" title="Restore Database (Overrides existing tables, creates new ones)">
					<i class="bi bi-arrow-repeat text-danger"></i>
				</a>
			</li>
			<!--end::Database Restore Button-->
			<!--begin::Database Backup Button-->
			<li class="nav-item">
				<a class="nav-link" href="../action/database_backup.php" id="dbBackupBtn" title="Backup Database">
					<i class="bi bi-database-check"></i>
				</a>
			</li>
			<!--end::Database Backup Button-->
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
						src="../../../dist/assets/img/user2-160x160.jpg"
						class="user-image rounded-circle shadow"
						alt="User Image" />
					<span class="d-none d-md-inline"><?php echo htmlspecialchars($admin['f_name'] . ' ' . $admin['l_name']); ?></span>
				</a>
				<ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
					<!--begin::User Image-->
					<li class="user-header text-bg-primary">
						<img
							src="../../../dist/assets/img/user2-160x160.jpg"
							class="rounded-circle shadow"
							alt="User Image" />
						<p>
							<?php echo htmlspecialchars($admin['f_name'] . ' ' . $admin['l_name']); ?>
							<small>Member since Nov. 2023</small>
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
						<a href="../admin_page/profile_admin.php" class="btn btn-default btn-flat">Profile</a>
						<a href="../auth/log_out.php" class="btn btn-default btn-flat float-end" id="logoutBtn">Sign out</a>
					</li>
					<!--end::Menu Footer-->
					<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
					<script>
						document.addEventListener('DOMContentLoaded', function() {
							// Logout Button Handler
							const logoutBtn = document.getElementById("logoutBtn");
							if (logoutBtn) {
								logoutBtn.addEventListener("click", function(e) {
									e.preventDefault();
									const logoutUrl = this.href;

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
											window.location.href = logoutUrl;
										}
									});
								});
							}

							// Database Backup Button Handler
							const dbBackupBtn = document.getElementById("dbBackupBtn");
							if (dbBackupBtn) {
								dbBackupBtn.addEventListener("click", function(e) {
									e.preventDefault();
									const backupUrl = this.href;

									Swal.fire({
										title: 'Backup Database?',
										text: "This will download a complete backup of the database.",
										icon: 'info',
										showCancelButton: true,
										confirmButtonColor: '#3085d6',
										cancelButtonColor: '#6c757d',
										confirmButtonText: 'Yes, download backup!',
										cancelButtonText: 'Cancel'
									}).then((result) => {
										if (result.isConfirmed) {
											// Show brief success message and trigger download
											Swal.fire({
												title: 'Download Started!',
												text: 'Your database backup is being prepared. The download will start shortly.',
												icon: 'success',
												timer: 2000,
												showConfirmButton: false,
												timerProgressBar: true
											});
											// Trigger download after a brief delay
											setTimeout(() => {
												window.location.href = backupUrl;
											}, 500);
										}
									});
								});
							}

							// Database Restore Button Handler
							const dbRestoreBtn = document.getElementById("dbRestoreBtn");
							if (dbRestoreBtn) {
								dbRestoreBtn.addEventListener("click", function(e) {
									e.preventDefault();
									const restoreModalEl = document.getElementById('restoreModal');
									if (restoreModalEl) {
										const modal = new bootstrap.Modal(restoreModalEl);
										modal.show();
									}
								});
							}

							// Start Restore Button Handler
							const startRestoreBtn = document.getElementById("startRestoreBtn");
							if (startRestoreBtn) {
								startRestoreBtn.addEventListener("click", function() {
									const fileInput = document.getElementById("sqlfile");
									const restoreModalEl = document.getElementById('restoreModal');

									if (!fileInput || fileInput.files.length === 0) {
										Swal.fire({
											title: 'No File Selected',
											text: 'Please select a .sql file to restore.',
											icon: 'warning',
											confirmButtonColor: '#3085d6'
										});
										return;
									}

									// Close the Bootstrap modal first
									if (restoreModalEl) {
										const restoreModal = bootstrap.Modal.getInstance(restoreModalEl);
										if (restoreModal) {
											restoreModal.hide();
										}
									}

									// Show loading alert
									Swal.fire({
										title: 'Restoring Database',
										text: 'Please wait while the database is being restored...',
										icon: 'info',
										allowOutsideClick: false,
										allowEscapeKey: false,
										showConfirmButton: false,
										didOpen: () => {
											Swal.showLoading();
										}
									});

									const formData = new FormData();
									formData.append("sqlfile", fileInput.files[0]);

									fetch("../action/database_restore.php", {
										method: "POST",
										body: formData
									})
									.then(res => res.json())
									.then(data => {
										console.log(data);
										if (data.success) {
											// Show success with warning if there are minor errors
											if (data.has_warnings) {
												Swal.fire({
													title: 'Restore Completed!',
													html: data.message + '<br><br><small class="text-muted">Some minor warnings occurred but the restore was successful.</small>',
													icon: 'success',
													confirmButtonColor: '#28a745',
													confirmButtonText: 'OK'
												}).then(() => {
													// Reset the form
													const restoreForm = document.getElementById("restoreForm");
													if (restoreForm) {
														restoreForm.reset();
													}
													const restoreStatus = document.getElementById("restoreStatus");
													if (restoreStatus) {
														restoreStatus.innerHTML = "";
													}
												});
											} else {
												Swal.fire({
													title: 'Success!',
													text: data.message || 'Database restored successfully!',
													icon: 'success',
													confirmButtonColor: '#28a745',
													confirmButtonText: 'OK'
												}).then(() => {
													// Reset the form
													const restoreForm = document.getElementById("restoreForm");
													if (restoreForm) {
														restoreForm.reset();
													}
													const restoreStatus = document.getElementById("restoreStatus");
													if (restoreStatus) {
														restoreStatus.innerHTML = "";
													}
												});
											}
										} else {
											Swal.fire({
												title: 'Restore Failed',
												text: data.message || 'An error occurred while restoring the database.',
												icon: 'error',
												confirmButtonColor: '#dc3545',
												confirmButtonText: 'OK'
											});
										}
									})
									.catch(err => {
										Swal.fire({
											title: 'Error',
											text: 'An unexpected error occurred: ' + err,
											icon: 'error',
											confirmButtonColor: '#dc3545',
											confirmButtonText: 'OK'
										});
									});
								});
							}
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

<!-- Restore Database Modal (Outside navbar to fix z-index) -->
<div class="modal fade" id="restoreModal" tabindex="-1" aria-hidden="true" style="z-index: 9999;">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Restore Database</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
			</div>
			<div class="modal-body">
				<p class="text-danger">
					Restoring a database will overwrite existing data.  
					Make sure you have created a backup first.
				</p>
				<form id="restoreForm" enctype="multipart/form-data">
					<label>Select SQL File (.sql only)</label>
					<input type="file" name="sqlfile" id="sqlfile" class="form-control" accept=".sql" required>
				</form>
				<div id="restoreStatus" class="mt-3 small text-muted"></div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
				<button class="btn btn-danger" id="startRestoreBtn">Start Restore</button>
			</div>
		</div>
	</div>
</div>

<!-- <style>
	/* Ensure modal backdrop and modal have proper z-index */
	.modal-backdrop {
		z-index: 9998 !important;
	}
	#restoreModal {
		z-index: 9999 !important;
	}
	#restoreModal .modal-dialog {
		z-index: 10000 !important;
	}
</style> -->