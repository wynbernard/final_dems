# CSRF Protection - Quick Fix Script

## Automated Update Instructions

This document provides patterns to quickly add CSRF protection to all files.

---

## 🔧 Pattern 1: Forms (Add Token Field)

### Find:
```php
<form method="POST" action="
```

### Replace with:
```php
<form method="POST" action="
    <?php require_once '../../../../database/csrf.php'; echo csrf_token_field(); ?>
```

**Note:** Adjust path based on file location:
- `dist/pages/modal/` → `../../../../database/csrf.php`
- `dist/pages/auth/` → `../../../database/csrf.php`
- `dist/pages/admin_page/` → `../../../database/csrf.php`

---

## 🔧 Pattern 2: Action Files (Add Validation)

### Find (at top of file, after session include):
```php
include '../../../database/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
```

### Replace with:
```php
include '../../../database/session.php';
require_once '../../../database/csrf.php';

// Validate CSRF token
csrf_validate_or_die();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
```

### Alternative Pattern:
```php
include '../../../database/session.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
```

### Replace with:
```php
include '../../../database/session.php';
require_once '../../../database/csrf.php';

// Validate CSRF token
csrf_validate_or_die();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
```

---

## 📋 Files to Update

### Forms (17 remaining):
1. `dist/pages/auth/user_registration.php`
2. `dist/pages/auth/log_in.php`
3. `dist/pages/modal/evac_location/barangay_management_modal.php`
4. `dist/pages/modal/registered_idps.php`
5. `dist/pages/modal/inventory_modal.php`
6. `dist/pages/user_page/room_reservation.php`
7. `dist/pages/modal_user/family_details.php`
8. `dist/pages/modal_profile_user/user_profile_modal.php`
9. `dist/pages/modal/modal_room.php`
10. `dist/pages/modal/evac_location/modal_location.php`
11. `dist/pages/modal/auth_modal/user_pre_reg.php`
12. `dist/pages/modal/admin_user_modal.php`
13. `dist/pages/modal/admin_profile_modal.php`
14. `dist/pages/layout_user/profile_content.php`
15. `dist/pages/layout/profile_content.php`
16. `dist/pages/auth/reset_password.php`
17. `dist/pages/auth/digital_id.php`
18. `dist/pages/admin_page/pre_reg.php`
19. `dist/pages/admin_page/barangay_view.php`

### Action Files (30 remaining):
1. `dist/pages/action/registration_staff.php`
2. `dist/pages/action/auth_action/user_pre_reg.php`
3. `dist/pages/action/brgy_management_action/delete_purok.php`
4. `dist/pages/action/brgy_management_action/add_purok.php`
5. `dist/pages/action/brgy_management_action/edit_purok.php`
6. `dist/pages/action/brgy_management_action/edit_barangay.php`
7. `dist/pages/action/brgy_management_action/add_brgy.php`
8. `dist/pages/action/update_family_member_status.php`
9. `dist/pages/action/update_admin_profile.php`
10. `dist/pages/action/registration_backend.php`
11. `dist/pages/action/registered_idps.php`
12. `dist/pages/action/log_family.php`
13. `dist/pages/action/inventory/edit_inventory.php`
14. `dist/pages/action/inventory/add_inventory.php`
15. `dist/pages/action/inventory/delete_inventory.php`
16. `dist/pages/action/evac_location_action/edit_location.php`
17. `dist/pages/action/evac_location_action/add_location.php`
18. `dist/pages/action/edit_room.php`
19. `dist/pages/action/dispatch_all.php`
20. `dist/pages/action/delete_room.php`
21. `dist/pages/action/delete_location.php`
22. `dist/pages/action/brgy_management_action/toggle_evacuation_db.php`
23. `dist/pages/action/brgy_management_action/set_evac_all.php`
24. `dist/pages/action/brgy_management_action/delete_brgy.php`
25. `dist/pages/action/admin_user_action/edit_admin_user.php`
26. `dist/pages/action/admin_user_action/delete_admin_user.php`
27. `dist/pages/action/admin_user_action/assigned_location_admin.php`
28. `dist/pages/action/admin_user_action/add_admin_user.php`
29. `dist/pages/action/add_room.php`
30. `dist/pages/action/action_pre_reg.php`

---

## ✅ Verification

After updating each file:

1. **Form:** Check that hidden input appears in HTML source:
   ```html
   <input type="hidden" name="csrf_token" value="...">
   ```

2. **Action:** Check that validation is called before processing:
   ```php
   csrf_validate_or_die();
   ```

3. **Test:** Submit form - should work normally. Try without token - should fail.

---

**Use find-and-replace in your IDE to speed up the process!**
