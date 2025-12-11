# XSS Vulnerabilities - Complete Audit List

**Date:** January 2025  
**Total Instances Found:** 610+ output statements requiring XSS protection  
**Status:** ❌ 0% Complete - All instances need review and fixing

---

## 📋 Summary

- **Total Files with Output:** 107 files
- **Total Echo/Print Statements:** 791+ instances
- **Already Protected (htmlspecialchars):** ~324 instances (41%)
- **Vulnerable Output:** ~467+ instances (59%)
- **Priority:** 🔴 **CRITICAL** - Must fix before deployment

---

## 🎯 Priority Files (High Risk - User Input Displayed)

### 1. Admin Pages (High Priority)
These files display user-controlled data and are most vulnerable:

#### `dist/pages/admin_page/idps_user.php` (67 instances)
- **Risk:** HIGH - Displays user registration data, names, locations
- **Lines to check:** All echo statements with user data
- **Status:** ⚠️ Some protected, many vulnerable

#### `dist/pages/admin_page/reports.php` (42 instances)
- **Risk:** HIGH - Displays aggregated user data
- **Lines to check:** All echo statements
- **Status:** ⚠️ Partial protection

#### `dist/pages/admin_page/pre_reg.php` (51 instances)
- **Risk:** HIGH - Displays pre-registration user data
- **Lines to check:** All echo statements with user input
- **Status:** ⚠️ Needs review

#### `dist/pages/admin_page/brgy_record.php` (30 instances)
- **Risk:** HIGH - Displays barangay and disaster data
- **Lines to check:** Lines 537, 752-757, and all echo statements
- **Status:** ⚠️ Some protected with htmlspecialchars, needs complete audit

#### `dist/pages/admin_page/loc_management.php` (23 instances)
- **Risk:** MEDIUM - Displays location data
- **Lines to check:** Lines 86-143 (location IDs, status, names)
- **Status:** ⚠️ Needs protection

#### `dist/pages/admin_page/barangay_management.php` (24 instances)
- **Risk:** MEDIUM - Displays barangay data
- **Lines to check:** Lines 120-176
- **Status:** ⚠️ Needs protection

#### `dist/pages/admin_page/disaster.php` (17 instances)
- **Risk:** MEDIUM - Displays disaster data
- **Lines to check:** Lines 82-116
- **Status:** ⚠️ Needs protection

#### `dist/pages/admin_page/activity_log.php` (7 instances)
- **Risk:** MEDIUM - Displays activity log data
- **Lines to check:** Lines 85-106
- **Status:** ⚠️ Needs protection

#### `dist/pages/admin_page/predictive.php` (8 instances)
- **Risk:** MEDIUM - Displays forecast data
- **Lines to check:** Lines 84-108
- **Status:** ⚠️ Needs protection

#### `dist/pages/admin_page/rooms.php` (20 instances)
- **Risk:** MEDIUM - Displays room data
- **Status:** ⚠️ Needs protection

#### `dist/pages/admin_page/admin_user.php` (18 instances)
- **Risk:** MEDIUM - Displays admin user data
- **Status:** ⚠️ Needs protection

#### `dist/pages/admin_page/confidence_analysis.php` (24 instances)
- **Risk:** MEDIUM - Displays analysis data
- **Status:** ⚠️ Needs protection

#### `dist/pages/admin_page/idps_log.php` (7 instances)
- **Risk:** MEDIUM - Displays log data
- **Lines to check:** Lines 203-212
- **Status:** ⚠️ Needs protection

#### `dist/pages/admin_page/resource_inventory.php` (1 instance)
- **Risk:** LOW - Static output
- **Status:** ⚠️ Needs review

#### `dist/pages/admin_page/resource_distribution.php` (1 instance)
- **Risk:** LOW - Static output
- **Status:** ⚠️ Needs review

#### `dist/pages/admin_page/Dashboard.php` (1 instance)
- **Risk:** LOW
- **Status:** ⚠️ Needs review

#### `dist/pages/admin_page/profile_admin.php` (5 instances)
- **Risk:** MEDIUM - User profile data
- **Status:** ⚠️ Needs protection

#### `dist/pages/admin_page/area_affected.php` (4 instances)
- **Risk:** MEDIUM
- **Status:** ⚠️ Needs protection

#### `dist/pages/admin_page/update_location_status_bulk.php` (5 instances)
- **Risk:** MEDIUM
- **Status:** ⚠️ Needs protection

---

### 2. Modal Files (High Priority)
These files contain forms and display user data:

#### `dist/pages/modal/registered_idps.php` (19 instances)
- **Risk:** HIGH - Registration form with user input
- **Lines to check:** Lines 33-103
- **Status:** ⚠️ Some protected, needs complete audit

#### `dist/pages/modal/admin_user_modal.php` (5 instances)
- **Risk:** MEDIUM - Admin user forms
- **Lines to check:** Line 165 (location options)
- **Status:** ⚠️ Some protected with htmlspecialchars

#### `dist/pages/modal/modal_room.php` (7 instances)
- **Risk:** MEDIUM - Room management forms
- **Lines to check:** Lines 14, 17, 49, 102 (location_name, evac_loc_id)
- **Status:** ⚠️ **VULNERABLE** - Direct output without escaping

#### `dist/pages/modal/evac_location/barangay_management_modal.php` (6 instances)
- **Risk:** MEDIUM - Barangay management
- **Status:** ⚠️ Needs protection

#### `dist/pages/modal/evac_location/modal_location.php` (5 instances)
- **Risk:** MEDIUM - Location management
- **Lines to check:** Lines 38, 135 (barangay_name, coordinates)
- **Status:** ⚠️ Some protected, needs complete audit

#### `dist/pages/modal/inventory_modal.php` (3 instances)
- **Risk:** MEDIUM - Inventory forms
- **Status:** ⚠️ Needs protection

#### `dist/pages/modal/disaster.php` (3 instances)
- **Risk:** MEDIUM - Disaster forms
- **Status:** ⚠️ Needs protection

#### `dist/pages/modal/details_idps.php` (1 instance)
- **Risk:** MEDIUM - IDP details
- **Status:** ⚠️ Needs protection

#### `dist/pages/modal/admin_profile_modal.php` (4 instances)
- **Risk:** MEDIUM - Admin profile
- **Status:** ⚠️ Needs protection

#### `dist/pages/modal/resources_distribution.php` (1 instance)
- **Risk:** LOW
- **Status:** ⚠️ Needs review

---

### 3. Action Files (Medium Priority)
These files may output error messages or data:

#### `dist/pages/action/registration_backend.php` (14 instances)
- **Risk:** MEDIUM - Registration processing
- **Status:** ⚠️ Needs protection (JSON responses should be safe)

#### `dist/pages/action/dispatch_all.php` (5 instances)
- **Risk:** LOW - JSON responses
- **Status:** ⚠️ Needs review (JSON should be safe)

#### `dist/pages/action/update_family_member_status.php` (5 instances)
- **Risk:** MEDIUM - Status updates
- **Status:** ⚠️ Needs protection

#### `dist/pages/action/brgy_management_action/add_purok.php` (5 instances)
- **Risk:** MEDIUM - Purok management
- **Status:** ⚠️ Needs protection

#### `dist/pages/action/brgy_management_action/edit_purok.php` (4 instances)
- **Risk:** MEDIUM
- **Status:** ⚠️ Needs protection

#### `dist/pages/action/brgy_management_action/delete_purok.php` (4 instances)
- **Risk:** MEDIUM
- **Status:** ⚠️ Needs protection

#### `dist/pages/action/registered_idps.php` (2 instances)
- **Risk:** MEDIUM - Registration processing
- **Status:** ⚠️ Needs protection

#### `dist/pages/action/evac_location_action/edit_location.php` (1 instance)
- **Risk:** LOW - Error message
- **Line:** 78 - "Invalid request method."
- **Status:** ⚠️ Needs review

#### `dist/pages/action/action_pre_reg.php` (12 instances)
- **Risk:** MEDIUM - Pre-registration
- **Status:** ⚠️ Needs protection

#### `dist/pages/action/database_backup.php` (18 instances)
- **Risk:** MEDIUM - Database operations
- **Status:** ⚠️ Needs protection

#### `dist/pages/action/database_restore.php` (5 instances)
- **Risk:** MEDIUM
- **Status:** ⚠️ Needs protection

#### `dist/pages/action/auth_action/user_pre_reg.php` (8 instances)
- **Risk:** MEDIUM - User registration
- **Status:** ⚠️ Needs protection

#### `dist/pages/action/brgy_management_action/toggle_evacuation_db.php` (4 instances)
- **Risk:** MEDIUM
- **Status:** ⚠️ Needs protection

#### `dist/pages/action/brgy_management_action/set_evac_all.php` (3 instances)
- **Risk:** MEDIUM
- **Status:** ⚠️ Needs protection

#### `dist/pages/action/brgy_management_action/list_purok.php` (2 instances)
- **Risk:** MEDIUM
- **Status:** ⚠️ Needs protection

#### `dist/pages/action/brgy_management_action/list_barangay_map.php` (2 instances)
- **Risk:** MEDIUM
- **Status:** ⚠️ Needs protection

#### `dist/pages/action/log_family.php` (2 instances)
- **Risk:** MEDIUM
- **Status:** ⚠️ Needs protection

#### `dist/pages/action/get_latest_status.php` (3 instances)
- **Risk:** MEDIUM
- **Status:** ⚠️ Needs protection

#### `dist/pages/action_user/save_coordinates.php` (12 instances)
- **Risk:** MEDIUM
- **Status:** ⚠️ Needs protection

#### `dist/pages/action_user/profile_user.php` (2 instances)
- **Risk:** MEDIUM
- **Status:** ⚠️ Needs protection

#### `dist/pages/action_user/log_recommended_arrival.php` (7 instances)
- **Risk:** MEDIUM
- **Status:** ⚠️ Needs protection

#### `dist/pages/action_user/edit_member.php` (8 instances)
- **Risk:** MEDIUM
- **Status:** ⚠️ Needs protection

---

### 4. Fetch Data Files (Medium Priority)
These files output data via AJAX/API:

#### `dist/pages/fetch_data/idps_staff.php` (6 instances)
- **Risk:** MEDIUM - IDP staff data
- **Lines to check:** Lines 55-86
- **Status:** ⚠️ **VULNERABLE** - Direct HTML output without escaping

#### `dist/pages/fetch_data/fetch_idps.php` (2 instances)
- **Risk:** MEDIUM
- **Status:** ⚠️ Needs protection

#### `dist/pages/fetch_data/get_resource_quantities.php` (2 instances)
- **Risk:** LOW - JSON output
- **Status:** ⚠️ Needs review

#### `dist/pages/fetch_data/get_age_data.php` (2 instances)
- **Risk:** LOW - JSON output
- **Status:** ⚠️ Needs review

#### `dist/pages/fetch_data/fetch_location.php` (3 instances)
- **Risk:** LOW - JSON output
- **Status:** ⚠️ Needs review

#### `dist/pages/fetch_data/fetch_disaster.php` (2 instances)
- **Risk:** LOW - JSON output
- **Status:** ⚠️ Needs review

#### `dist/pages/fetch_data/recieve_resources_ajax.php` (7 instances)
- **Risk:** MEDIUM
- **Status:** ⚠️ Needs protection

#### `dist/pages/fetch_data/get_staff_location.php` (4 instances)
- **Risk:** MEDIUM
- **Status:** ⚠️ Needs protection

#### `dist/pages/fetch_data/get_room_info.php` (3 instances)
- **Risk:** MEDIUM
- **Status:** ⚠️ Needs protection

#### `dist/pages/fetch_data/get_idps.php` (1 instance)
- **Risk:** LOW
- **Status:** ⚠️ Needs review

#### `dist/pages/fetch_data/get_family_data.php` (4 instances)
- **Risk:** MEDIUM
- **Status:** ⚠️ Needs protection

#### `dist/pages/fetch_data/fetch_room_staff.php` (3 instances)
- **Risk:** MEDIUM
- **Status:** ⚠️ Needs protection

#### `dist/pages/fetch_data/fetch_room.php` (2 instances)
- **Risk:** MEDIUM
- **Status:** ⚠️ Needs protection

#### `dist/pages/fetch_data/fetch_reg_idps.php` (2 instances)
- **Risk:** MEDIUM
- **Status:** ⚠️ Needs protection

#### `dist/pages/fetch_data/fetch_idps_staff.php` (1 instance)
- **Risk:** LOW
- **Status:** ⚠️ Needs review

#### `dist/pages/fetch_data/fetch_idps_reservation.php` (2 instances)
- **Risk:** MEDIUM
- **Status:** ⚠️ Needs protection

#### `dist/pages/fetch_data/check_evacuess_location.php` (6 instances)
- **Risk:** MEDIUM
- **Status:** ⚠️ Needs protection

---

### 5. User Pages (Medium Priority)

#### `dist/pages/user_page/profile_user.php` (11 instances)
- **Risk:** MEDIUM - User profile data
- **Status:** ⚠️ Needs protection

#### `dist/pages/user_page/family.php` (3 instances)
- **Risk:** MEDIUM
- **Status:** ⚠️ Needs protection

#### `dist/pages/user_page/history.php` (1 instance)
- **Risk:** LOW
- **Status:** ⚠️ Needs review

#### `dist/pages/user_page/Dashboard.php` (1 instance)
- **Risk:** LOW
- **Status:** ⚠️ Needs review

---

### 6. Layout Files (Medium Priority)

#### `dist/pages/layout/sidebar.php` (24 instances)
- **Risk:** MEDIUM - Navigation, user data
- **Status:** ⚠️ Needs protection

#### `dist/pages/layout/main.content.php` (7 instances)
- **Risk:** MEDIUM - Main content area
- **Status:** ⚠️ Needs protection

#### `dist/pages/layout/header.php` (2 instances)
- **Risk:** LOW - Header content
- **Status:** ⚠️ Needs review

#### `dist/pages/layout/head_links.php` (1 instance)
- **Risk:** LOW - CSRF token (already protected)
- **Status:** ✅ Protected

#### `dist/pages/layout/profile_content.php` (8 instances)
- **Risk:** MEDIUM - Profile data
- **Status:** ⚠️ Needs protection

#### `dist/pages/layout_user/main.content.php` (9 instances)
- **Risk:** MEDIUM - User main content
- **Lines to check:** Lines 285-286 (coordinates)
- **Status:** ⚠️ **VULNERABLE** - Direct output in JavaScript

#### `dist/pages/layout_user/sidebar.php` (3 instances)
- **Risk:** MEDIUM
- **Status:** ⚠️ Needs protection

#### `dist/pages/layout_user/header.php` (5 instances)
- **Risk:** MEDIUM
- **Status:** ⚠️ Needs protection

#### `dist/pages/layout_user/profile_content.php` (8 instances)
- **Risk:** MEDIUM
- **Status:** ⚠️ Needs protection

---

### 7. Modal User Files (Medium Priority)

#### `dist/pages/modal_profile_user/user_profile_modal.php` (24 instances)
- **Risk:** MEDIUM - User profile modal
- **Status:** ⚠️ Needs protection

#### `dist/pages/modal_user/family_details.php` (1 instance)
- **Risk:** MEDIUM
- **Status:** ⚠️ Needs protection

---

### 8. QR Code Scanner Files (Medium Priority)

#### `dist/pages/qr_code_scanner/register_family.php` (4 instances)
- **Risk:** MEDIUM - Family registration
- **Status:** ⚠️ Needs protection

#### `dist/pages/qr_code_scanner/verify_qr.php` (4 instances)
- **Risk:** MEDIUM
- **Status:** ⚠️ Needs protection

#### `dist/pages/qr_code_scanner/verify_family_by_pre_reg.php` (4 instances)
- **Risk:** MEDIUM
- **Status:** ⚠️ Needs protection

#### `dist/pages/qr_code_scanner/qr_scanner.php` (8 instances)
- **Risk:** MEDIUM
- **Status:** ⚠️ Needs protection

#### `dist/pages/qr_code_scanner/lookup_room.php` (2 instances)
- **Risk:** MEDIUM
- **Status:** ⚠️ Needs protection

#### `dist/pages/qr_code_scanner/get_rooms.php` (3 instances)
- **Risk:** MEDIUM
- **Status:** ⚠️ Needs protection

#### `dist/pages/qr_code_scanner/get_registration_details.php` (3 instances)
- **Risk:** MEDIUM
- **Status:** ⚠️ Needs protection

#### `dist/pages/qr_code_scanner/get_family_members.php` (6 instances)
- **Risk:** MEDIUM
- **Status:** ⚠️ Needs protection

#### `dist/pages/qr_code_scanner/family_get.php` (3 instances)
- **Risk:** MEDIUM
- **Status:** ⚠️ Needs protection

#### `dist/pages/qr_code_scanner/check_registration.php` (5 instances)
- **Risk:** MEDIUM
- **Status:** ⚠️ Needs protection

---

### 9. Auth Files (Medium Priority)

#### `dist/pages/auth/user_registration.php` (1 instance)
- **Risk:** MEDIUM - Registration form
- **Status:** ⚠️ Needs protection

#### `dist/pages/auth/log_in.php` (1 instance)
- **Risk:** MEDIUM - Login form
- **Status:** ⚠️ Needs protection

#### `dist/pages/auth/forgot_password.php` (4 instances)
- **Risk:** MEDIUM
- **Status:** ⚠️ Needs protection

#### `dist/pages/auth/digital_id.php` (6 instances)
- **Risk:** MEDIUM
- **Status:** ⚠️ Needs protection

---

### 10. Validation Files (Low Priority)

#### `dist/pages/check_validation/admin_username.php` (5 instances)
- **Risk:** LOW - Validation responses
- **Lines to check:** Lines 9-34
- **Status:** ⚠️ Needs review (simple text responses)

#### `dist/pages/check_validation/user_email.php` (3 instances)
- **Risk:** LOW
- **Status:** ⚠️ Needs review

#### `dist/pages/check_validation/name_validation.php` (3 instances)
- **Risk:** LOW
- **Status:** ⚠️ Needs review

#### `dist/pages/check_validation/barangay_name.php` (2 instances)
- **Risk:** LOW
- **Status:** ⚠️ Needs review

---

### 11. Other Files (Low Priority)

#### `dist/pages/barangay_view.php` (20 instances)
- **Risk:** MEDIUM - Barangay details
- **Lines to check:** Lines 252-483 (purok data, coordinates)
- **Status:** ⚠️ **VULNERABLE** - Direct output in JavaScript and HTML

#### `dist/pages/export_distribution_csv.php` (1 instance)
- **Risk:** LOW - CSV export
- **Status:** ⚠️ Needs review

#### `dist/pages/typhoon_json/typhoon_scraper.php` (1 instance)
- **Risk:** LOW
- **Status:** ⚠️ Needs review

#### `dist/pages/typhoon_json/disaster_scraper.php` (1 instance)
- **Risk:** LOW
- **Status:** ⚠️ Needs review

#### `dist/pages/alert/warning.php` (2 instances)
- **Risk:** LOW
- **Status:** ⚠️ Needs review

#### `dist/pages/alert/registration_warning.php` (2 instances)
- **Risk:** LOW
- **Status:** ⚠️ Needs review

---

## 🔴 Critical Vulnerabilities (Immediate Fix Required)

### 1. Direct Variable Output in HTML (High Risk)
**Files:**
- `dist/pages/modal/modal_room.php` - Lines 14, 17, 49, 102
- `dist/pages/admin_page/barangay_view.php` - Lines 287, 290, 482-483
- `dist/pages/layout_user/main.content.php` - Lines 285-286

**Example Vulnerable Code:**
```php
// VULNERABLE
<h5>Adding Room to: <strong><?php echo $location_name; ?></strong></h5>
<input type="hidden" name="evac_loc_id" value="<?php echo $evac_loc_id; ?>">
```

**Fix Required:**
```php
// FIXED
<h5>Adding Room to: <strong><?php echo htmlspecialchars($location_name, ENT_QUOTES, 'UTF-8'); ?></strong></h5>
<input type="hidden" name="evac_loc_id" value="<?php echo htmlspecialchars($evac_loc_id, ENT_QUOTES, 'UTF-8'); ?>">
```

### 2. Direct Variable Output in JavaScript (High Risk)
**Files:**
- `dist/pages/admin_page/barangay_view.php` - Lines 482-483
- `dist/pages/layout_user/main.content.php` - Lines 285-286

**Example Vulnerable Code:**
```php
// VULNERABLE
const lat = parseFloat('<?php echo $barangay['latitude']; ?>');
```

**Fix Required:**
```php
// FIXED
const lat = parseFloat(<?php echo json_encode($barangay['latitude'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>);
```

### 3. Direct HTML Output in Fetch Data (High Risk)
**Files:**
- `dist/pages/fetch_data/idps_staff.php` - Lines 55-86

**Example Vulnerable Code:**
```php
// VULNERABLE
echo '<div class="p-3">' . $familyMember['name'] . '</div>';
```

**Fix Required:**
```php
// FIXED
echo '<div class="p-3">' . htmlspecialchars($familyMember['name'], ENT_QUOTES, 'UTF-8') . '</div>';
```

---

## 📝 Implementation Guide

### Using htmlspecialchars()
For HTML content:
```php
<?php echo htmlspecialchars($variable, ENT_QUOTES, 'UTF-8'); ?>
```

For HTML attributes:
```php
<input value="<?php echo htmlspecialchars($variable, ENT_QUOTES, 'UTF-8'); ?>">
```

### Using XSS Helper Functions
The project has `database/xss_helper.php` with helper functions:

```php
require_once '../../../database/xss_helper.php';

// For HTML content
<?php echo e($variable); ?>

// For HTML attributes
<input value="<?php echo e_attr($variable); ?>">

// For JavaScript
<script>
const data = <?php echo e_js($variable); ?>;
</script>
```

### Using json_encode() for JavaScript
For JavaScript variables:
```php
<script>
const data = <?php echo json_encode($variable, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
</script>
```

---

## ✅ Testing Checklist

After fixing, test with these XSS payloads:
- `<script>alert('XSS')</script>`
- `<img src=x onerror=alert('XSS')>`
- `"><script>alert('XSS')</script>`
- `javascript:alert('XSS')`
- `<svg onload=alert('XSS')>`

---

## 📊 Progress Tracking

- **Total Files:** 107
- **Total Instances:** 610+
- **Fixed:** 0 (0%)
- **Remaining:** 610+ (100%)

---

**Last Updated:** January 2025  
**Priority:** 🔴 **CRITICAL** - Must fix before deployment

