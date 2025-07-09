<!-- Edit Email Modal -->
<div class="modal fade" id="editEmailModal" tabindex="-1" aria-labelledby="editEmailModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editEmailModalLabel">Edit Email Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="../action_user/profile_user.php" method="POST">
                    <input type="hidden" name="pre_reg_id" value="<?php echo $user['pre_reg_id']; ?>">
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-envelope"></i> New Email Address</label>
                        <input type="email" class="form-control" name="email_address" value="<?php echo htmlspecialchars($user['email_address']); ?>" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Update Email
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Edit Password Modal -->
<div class="modal fade" id="editPasswordModal" tabindex="-1" aria-labelledby="editPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPasswordModalLabel">Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="../action_user/profile_user.php" method="POST">
                    <input type="hidden" name="pre_reg_id" value="<?php echo $user['pre_reg_id']; ?>">

                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-lock"></i> Current Password</label>
                        <input type="password" class="form-control" name="current_password" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-key"></i> New Password</label>
                        <input type="password" class="form-control" name="new_password" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-check"></i> Confirm New Password</label>
                        <input type="password" class="form-control" name="confirm_password" required>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit First Name Modal -->
<div class="modal fade" id="editFirstNameModal" tabindex="-1" aria-labelledby="editFirstNameModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editFirstNameModalLabel">Edit First Name</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="../action_user/profile_user.php" method="POST">
                    <input type="hidden" name="pre_reg_id" value="<?php echo $user['pre_reg_id']; ?>">

                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-user"></i> First Name</label>
                        <input type="text" class="form-control" name="f_name" value="<?php echo htmlspecialchars($user['f_name']); ?>" required>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Update First Name
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Last Name Modal -->
<div class="modal fade" id="editLastNameModal" tabindex="-1" aria-labelledby="editLastNameModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editLastNameModalLabel">Edit Last Name</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="../action_user/profile_user.php" method="POST">
                    <input type="hidden" name="pre_reg_id" value="<?php echo $user['pre_reg_id']; ?>">

                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-user"></i> Last Name</label>
                        <input type="text" class="form-control" name="l_name" value="<?php echo htmlspecialchars($user['l_name']); ?>" required>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Update Last Name
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Edit Contact Number Modal -->
<div class="modal fade" id="editContactModal" tabindex="-1" aria-labelledby="editContactModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editContactModalLabel">Edit Contact Number</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="../action_user/profile_user.php" method="POST">
                    <input type="hidden" name="pre_reg_id" value="<?php echo $user['pre_reg_id']; ?>">

                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-phone"></i> Contact Number</label>
                        <input type="text" class="form-control" name="contact_no" value="0<?php echo htmlspecialchars($user['contact_no']); ?>" required pattern="[0-9]{10,11}" title="Enter a valid contact number">
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Update Contact Number
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Edit Gender Modal -->
<div class="modal fade" id="editGenderModal" tabindex="-1" aria-labelledby="editGenderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editGenderModalLabel">Edit Gender</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="../action_user/profile_user.php" method="POST">
                    <input type="hidden" name="pre_reg_id" value="<?php echo $user['pre_reg_id']; ?>">

                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-venus-mars"></i> Select Gender</label>
                        <select class="form-select" name="gender" required>
                            <option value="Male" <?php echo ($user['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo ($user['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo ($user['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Update Gender
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Date of Birth Modal -->
<div class="modal fade" id="editDobModal" tabindex="-1" aria-labelledby="editDobModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editDobModalLabel">Edit Date of Birth</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="../action_user/profile_user.php" method="POST">
                    <input type="hidden" name="pre_reg_id" value="<?php echo $user['pre_reg_id']; ?>">

                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-calendar-alt"></i> Select Date of Birth</label>
                        <input type="date" class="form-control" name="date_of_birth"
                            value="<?php echo htmlspecialchars($user['date_of_birth']); ?>" required>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Update Date of Birth
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>