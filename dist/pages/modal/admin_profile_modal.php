<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form action="../action/update_admin_profile.php" method="POST">
					<div class="mb-3">
						<label class="form-label">Username</label>
						<input type="text" class="form-control" name="username"
							value="<?php echo htmlspecialchars($admin['username']); ?>" required>
					</div>
					<div class="mb-3">
						<label class="form-label">New Password</label>
						<input type="password" class="form-control" name="password" value="<?php echo htmlspecialchars($admin['password']); ?>" required>
					</div>
					<div class="mb-3">
						<label class="form-label">First Name</label>
						<input type="text" class="form-control" name="f_name"
							value="<?php echo htmlspecialchars($admin['f_name']); ?>" required>
					</div>
					<div class="mb-3">
						<label class="form-label">Last Name</label>
						<input type="text" class="form-control" name="l_name"
							value="<?php echo htmlspecialchars($admin['l_name']); ?>" required>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
						<button type="submit" class="btn btn-primary">
							<i class="fas fa-save me-1"></i> Update Profile
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>