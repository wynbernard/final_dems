# Input Validation Implementation Guide

## ✅ Centralized Validation Library Created

A comprehensive input validation library has been created to standardize validation across the application. This guide explains how to use it.

---

## 📁 File Created

### `database/validation.php`
Contains all validation helper functions:
- `validate_required()` - Check if value is not empty
- `validate_length()` - Validate string length
- `validate_integer()` - Validate integer with optional min/max
- `validate_email()` - Validate email address
- `validate_phone()` - Validate phone number
- `validate_date()` - Validate date format (YYYY-MM-DD)
- `validate_whitelist()` - Validate value is in allowed list
- `validate_username()` - Validate username format
- `validate_name()` - Validate name format
- `validate_password()` - Validate password strength
- `sanitize_string()` - Sanitize string input
- `sanitize_integer()` - Sanitize integer input
- `sanitize_email()` - Sanitize email input
- `get_validation_error()` - Get error message for validation type
- `validate_fields()` - Validate multiple fields at once

---

## 🔧 How to Use

### Step 1: Include the Validation Library

In any file that needs validation, include the library:

```php
<?php
require_once '../../../database/validation.php';
```

### Step 2: Use Individual Validation Functions

#### Example 1: Basic Validation

```php
<?php
require_once '../../../database/validation.php';

$username = sanitize_string($_POST['username'] ?? '');

if (!validate_required($username)) {
    $_SESSION['error'] = "Username is required.";
    header("Location: form.php");
    exit();
}

if (!validate_username($username)) {
    $_SESSION['error'] = "Username must be 3-50 characters and contain only letters, numbers, underscores, and hyphens.";
    header("Location: form.php");
    exit();
}
```

#### Example 2: Integer Validation

```php
<?php
require_once '../../../database/validation.php';

$room_capacity = sanitize_integer($_POST['room_capacity'] ?? 0);

if (!validate_integer($room_capacity, 1, 1000)) {
    $_SESSION['error'] = "Room capacity must be between 1 and 1000.";
    header("Location: form.php");
    exit();
}
```

#### Example 3: Email Validation

```php
<?php
require_once '../../../database/validation.php';

$email = sanitize_email($_POST['email'] ?? '');

if (!validate_email($email)) {
    $_SESSION['error'] = "Please enter a valid email address.";
    header("Location: form.php");
    exit();
}
```

### Step 3: Use Batch Validation

For forms with multiple fields, use `validate_fields()`:

```php
<?php
require_once '../../../database/validation.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Define validation rules
    $rules = [
        'username' => [
            'required' => true,
            'type' => 'username',
            'min' => 3,
            'max' => 50
        ],
        'email' => [
            'required' => true,
            'type' => 'email'
        ],
        'room_capacity' => [
            'required' => true,
            'type' => 'integer',
            'min' => 1,
            'max' => 1000
        ],
        'status' => [
            'required' => true,
            'type' => 'whitelist',
            'allowed' => ['Ongoing', 'Resolved']
        ]
    ];
    
    // Validate all fields
    $validation = validate_fields($rules, $_POST);
    
    if (!$validation['valid']) {
        // Handle errors
        $_SESSION['error'] = implode(' ', $validation['errors']);
        header("Location: form.php");
        exit();
    }
    
    // All validation passed, proceed with processing
    $username = sanitize_string($_POST['username']);
    $email = sanitize_email($_POST['email']);
    $room_capacity = sanitize_integer($_POST['room_capacity']);
    $status = sanitize_string($_POST['status']);
    
    // ... continue with database operations
}
```

---

## 📝 Migration Examples

### Before (Inconsistent Validation)

```php
// File 1: add_room.php
$room_name = trim($_POST['room_name'] ?? '');
if (empty($room_name)) {
    throw new Exception("Room name is required.");
}
if (strlen($room_name) > 100) {
    throw new Exception("Room name must be 100 characters or less.");
}

// File 2: add_disaster.php
$disaster_name = trim($_POST['disaster_name'] ?? '');
if ($disaster_name !== '') {
    // Process...
}

// File 3: update_profile.php
$username = trim($_POST['username'] ?? '');
if (empty($username)) {
    $_SESSION['error'] = "All fields are required!";
}
if (strlen($username) < 3 || strlen($username) > 50) {
    $_SESSION['error'] = "Username must be between 3 and 50 characters.";
}
```

### After (Consistent Validation)

```php
// File 1: add_room.php
require_once '../../../database/validation.php';

$room_name = sanitize_string($_POST['room_name'] ?? '');

if (!validate_required($room_name)) {
    throw new Exception("Room name is required.");
}
if (!validate_length($room_name, 1, 100)) {
    throw new Exception("Room name must be 100 characters or less.");
}

// File 2: add_disaster.php
require_once '../../../database/validation.php';

$disaster_name = sanitize_string($_POST['disaster_name'] ?? '');

if (!validate_required($disaster_name)) {
    $_SESSION['error'] = "Disaster name is required.";
    header("Location: disaster.php");
    exit();
}

// File 3: update_profile.php
require_once '../../../database/validation.php';

$username = sanitize_string($_POST['username'] ?? '');

if (!validate_required($username)) {
    $_SESSION['error'] = "Username is required.";
    header("Location: profile.php");
    exit();
}
if (!validate_username($username)) {
    $_SESSION['error'] = "Username must be 3-50 characters and contain only letters, numbers, underscores, and hyphens.";
    header("Location: profile.php");
    exit();
}
```

---

## 🎯 Validation Types

### String Validation
- `validate_required($value)` - Check if not empty
- `validate_length($value, $min, $max)` - Check string length
- `validate_name($name, $min, $max)` - Validate name format
- `validate_username($username, $min, $max)` - Validate username format
- `sanitize_string($value)` - Trim and clean string

### Number Validation
- `validate_integer($value, $min, $max)` - Validate integer with range
- `sanitize_integer($value, $default)` - Convert to integer

### Format Validation
- `validate_email($email)` - Validate email format
- `validate_phone($phone)` - Validate phone number
- `validate_date($date)` - Validate date (YYYY-MM-DD)
- `sanitize_email($email)` - Sanitize and validate email

### Security Validation
- `validate_password($password, $min_length)` - Validate password strength
- `validate_whitelist($value, $allowed_values)` - Check value is in allowed list

---

## ✅ Benefits

1. **Consistency** - All files use the same validation logic
2. **Maintainability** - Update validation rules in one place
3. **Security** - Standardized validation reduces vulnerabilities
4. **Readability** - Clear, descriptive function names
5. **Error Messages** - Consistent error messages across the application

---

## 📋 Next Steps

1. **Migrate Existing Files** - Update action files to use the validation library
2. **Add Validation to Files Without It** - Identify files lacking validation and add it
3. **Test Thoroughly** - Test all forms with valid and invalid input
4. **Update Documentation** - Document validation rules for each form

---

## 🔍 Files to Update

### High Priority (Files with User Input)
- `dist/pages/action/add_room.php` ✅ (Already has some validation, can be improved)
- `dist/pages/action/edit_room.php` ✅ (Already has some validation, can be improved)
- `dist/pages/action/update_admin_profile.php` ✅ (Already has some validation, can be improved)
- `dist/pages/action/disaster/add_disaster.php` ⚠️ (Needs improvement)
- `dist/pages/action/disaster/edit_disaster.php` ⚠️ (Needs improvement)
- `dist/pages/action/brgy_management_action/add_brgy.php` ⚠️ (Needs review)
- `dist/pages/action/brgy_management_action/edit_barangay.php` ⚠️ (Needs review)
- `dist/pages/action/action_pre_reg.php` ⚠️ (Needs review)
- `dist/pages/action_user/new_member.php` ⚠️ (Needs review)

### Medium Priority (Validation Check Files)
- `dist/pages/check_validation/admin_username.php` ✅ (Already good, can use library)
- `dist/pages/check_validation/name_validation.php` ⚠️ (Can use library)
- `dist/pages/check_validation/user_email.php` ⚠️ (Can use library)
- `dist/pages/check_validation/barangay_name.php` ⚠️ (Can use library)

---

## 📚 Additional Resources

- See `CODE_REVIEW_REPORT.md` for current validation status
- See `DEPLOYMENT_CHECKLIST.md` for validation requirements
- Review existing validation in fixed files for examples
