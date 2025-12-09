# CSRF Protection Implementation Guide

## ✅ CSRF Protection System Created

A comprehensive CSRF protection system has been implemented. This guide explains how to use it and how to apply it to all forms and action files.

---

## 📁 Files Created

### `database/csrf.php`
Contains all CSRF helper functions:
- `csrf_generate_token()` - Generate a new CSRF token
- `csrf_get_token()` - Get current token (generates if needed)
- `csrf_validate()` - Validate a token
- `csrf_validate_or_die()` - Validate and die with error if invalid
- `csrf_token_field()` - Generate HTML hidden input field
- `csrf_token_json()` - Get token as JSON (for AJAX)
- `csrf_validate_ajax()` - Validate token from AJAX request
- `csrf_regenerate_token()` - Regenerate token after sensitive operations

---

## 🔧 How to Use

### Step 1: Add CSRF Token to Forms

In any form that submits via POST, add the CSRF token field:

```php
<form method="POST" action="action_file.php">
    <?php 
    require_once '../../../database/csrf.php'; 
    echo csrf_token_field(); 
    ?>
    <!-- rest of form fields -->
</form>
```

**Example:**
```php
<!-- Before -->
<form method="POST" action="../action/disaster/add_disaster.php">
    <input type="text" name="disaster_name">
    <button type="submit">Submit</button>
</form>

<!-- After -->
<form method="POST" action="../action/disaster/add_disaster.php">
    <?php require_once '../../../../database/csrf.php'; echo csrf_token_field(); ?>
    <input type="text" name="disaster_name">
    <button type="submit">Submit</button>
</form>
```

### Step 2: Validate CSRF Token in Action Files

In any PHP file that processes POST requests, validate the CSRF token:

```php
<?php
include '../../../database/session.php';
require_once '../../../database/csrf.php';

// Validate CSRF token - dies with error if invalid
csrf_validate_or_die();

// Your code here...
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process form data
}
```

**Example:**
```php
<!-- Before -->
<?php
include '../../../../database/session.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // process form
}
?>

<!-- After -->
<?php
include '../../../../database/session.php';
require_once '../../../../database/csrf.php';

// Validate CSRF token
csrf_validate_or_die();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // process form
}
?>
```

---

## 📋 Files That Need CSRF Protection

### Forms (20 files found)
Add `<?php require_once '../../../database/csrf.php'; echo csrf_token_field(); ?>` to each form:

1. `dist/pages/modal/disaster.php` ✅ **DONE** (3 forms)
2. `dist/pages/auth/user_registration.php`
3. `dist/pages/auth/log_in.php`
4. `dist/pages/modal/evac_location/barangay_management_modal.php`
5. `dist/pages/modal/registered_idps.php`
6. `dist/pages/modal/inventory_modal.php`
7. `dist/pages/user_page/room_reservation.php`
8. `dist/pages/modal_user/family_details.php`
9. `dist/pages/modal_profile_user/user_profile_modal.php`
10. `dist/pages/modal/modal_room.php`
11. `dist/pages/modal/evac_location/modal_location.php`
12. `dist/pages/modal/auth_modal/user_pre_reg.php`
13. `dist/pages/modal/admin_user_modal.php`
14. `dist/pages/modal/admin_profile_modal.php`
15. `dist/pages/layout_user/profile_content.php`
16. `dist/pages/layout/profile_content.php`
17. `dist/pages/auth/reset_password.php`
18. `dist/pages/auth/digital_id.php`
19. `dist/pages/admin_page/pre_reg.php`
20. `dist/pages/admin_page/barangay_view.php`

### Action Files (33 files found)
Add CSRF validation to each action file:

1. `dist/pages/action/disaster/add_disaster.php` ✅ **DONE**
2. `dist/pages/action/disaster/edit_disaster.php` ✅ **DONE**
3. `dist/pages/action/disaster/delete_disaster.php` ✅ **DONE**
4. `dist/pages/action/registration_staff.php`
5. `dist/pages/action/auth_action/user_pre_reg.php`
6. `dist/pages/action/brgy_management_action/delete_purok.php`
7. `dist/pages/action/brgy_management_action/add_purok.php`
8. `dist/pages/action/brgy_management_action/edit_purok.php`
9. `dist/pages/action/brgy_management_action/edit_barangay.php`
10. `dist/pages/action/brgy_management_action/add_brgy.php`
11. `dist/pages/action/update_family_member_status.php`
12. `dist/pages/action/update_admin_profile.php`
13. `dist/pages/action/registration_backend.php`
14. `dist/pages/action/registered_idps.php`
15. `dist/pages/action/log_family.php`
16. `dist/pages/action/inventory/edit_inventory.php`
17. `dist/pages/action/inventory/add_inventory.php`
18. `dist/pages/action/inventory/delete_inventory.php`
19. `dist/pages/action/evac_location_action/edit_location.php`
20. `dist/pages/action/evac_location_action/add_location.php`
21. `dist/pages/action/edit_room.php`
22. `dist/pages/action/dispatch_all.php`
23. `dist/pages/action/delete_room.php`
24. `dist/pages/action/delete_location.php`
25. `dist/pages/action/brgy_management_action/toggle_evacuation_db.php`
26. `dist/pages/action/brgy_management_action/set_evac_all.php`
27. `dist/pages/action/brgy_management_action/delete_brgy.php`
28. `dist/pages/action/admin_user_action/edit_admin_user.php`
29. `dist/pages/action/admin_user_action/delete_admin_user.php`
30. `dist/pages/action/admin_user_action/assigned_location_admin.php`
31. `dist/pages/action/admin_user_action/add_admin_user.php`
32. `dist/pages/action/add_room.php`
33. `dist/pages/action/action_pre_reg.php`

---

## 🔄 AJAX Requests

For AJAX requests, include the CSRF token in the request:

### JavaScript (Fetch API)
```javascript
// Get token from a hidden field or meta tag
const csrfToken = document.querySelector('input[name="csrf_token"]').value;

fetch('action_file.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': csrfToken
    },
    body: JSON.stringify({ data: 'value' })
});
```

### JavaScript (jQuery)
```javascript
$.ajax({
    url: 'action_file.php',
    method: 'POST',
    headers: {
        'X-CSRF-Token': $('input[name="csrf_token"]').val()
    },
    data: { data: 'value' }
});
```

### PHP (Validate AJAX)
```php
require_once '../../../database/csrf.php';

if (!csrf_validate_ajax()) {
    http_response_code(403);
    echo json_encode(['error' => 'CSRF token validation failed']);
    exit();
}
```

---

## 📝 Quick Reference

### In Forms:
```php
<?php require_once '../../../database/csrf.php'; echo csrf_token_field(); ?>
```

### In Action Files:
```php
<?php
require_once '../../../database/csrf.php';
csrf_validate_or_die();
?>
```

### Path Calculation:
- From `dist/pages/modal/` → `../../../../database/csrf.php`
- From `dist/pages/action/` → `../../../../database/csrf.php`
- From `dist/pages/admin_page/` → `../../../database/csrf.php`
- From `dist/pages/auth/` → `../../../database/csrf.php`

---

## ✅ Example Implementation

### Complete Example: Disaster Management

**Form (modal/disaster.php):**
```php
<form method="POST" action="../action/disaster/add_disaster.php">
    <?php require_once '../../../../database/csrf.php'; echo csrf_token_field(); ?>
    <input type="text" name="disaster_name" required>
    <button type="submit">Add</button>
</form>
```

**Action (action/disaster/add_disaster.php):**
```php
<?php
include '../../../../database/session.php';
require_once '../../../../database/csrf.php';

// Validate CSRF token
csrf_validate_or_die();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process form data safely
    $name = trim($_POST['disaster_name'] ?? '');
    // ... rest of code
}
```

---

## 🚀 Implementation Status

- ✅ CSRF helper functions created
- ✅ Example implementation (Disaster management - 3 forms, 3 actions)
- ⚠️ **REMAINING:** 17 forms need CSRF tokens
- ⚠️ **REMAINING:** 30 action files need CSRF validation

---

## 📊 Progress Tracking

| Category | Total | Completed | Remaining | Progress |
|----------|-------|-----------|-----------|----------|
| Forms | 20 | 3 | 17 | 15% |
| Action Files | 33 | 3 | 30 | 9% |
| **Total** | **53** | **6** | **47** | **11%** |

---

## 🔍 Testing

After implementing CSRF protection:

1. **Test Normal Form Submission:**
   - Submit forms normally - should work

2. **Test CSRF Attack:**
   - Try submitting form without token - should fail with 403 error
   - Try submitting with invalid token - should fail

3. **Test AJAX Requests:**
   - Include token in header - should work
   - Omit token - should fail

---

## ⚠️ Important Notes

1. **Session Required:** CSRF protection requires sessions. Ensure `session_start()` is called before using CSRF functions.

2. **Token Regeneration:** Consider regenerating tokens after sensitive operations (login, password change):
   ```php
   csrf_regenerate_token();
   ```

3. **Error Handling:** `csrf_validate_or_die()` will automatically:
   - Return 403 status code
   - Send JSON error response
   - Exit script

4. **Path Adjustments:** Adjust the `require_once` path based on file location relative to `database/csrf.php`.

---

## 🎯 Next Steps

1. Apply CSRF tokens to remaining 17 forms
2. Add CSRF validation to remaining 30 action files
3. Test all forms and actions
4. Update code review report

---

**Status:** ✅ CSRF System Created | ⚠️ Implementation In Progress (11% Complete)
