<?php
/**
 * Centralized Input Validation Library
 * 
 * Provides consistent validation functions across the application.
 * All validation functions return true on success, false on failure.
 * 
 * Usage:
 * require_once 'database/validation.php';
 * 
 * if (!validate_required($value)) {
 *     // Handle error
 * }
 */

/**
 * Validate that a value is not empty
 * 
 * @param mixed $value The value to validate
 * @return bool True if value is not empty, false otherwise
 */
function validate_required($value) {
    if (is_string($value)) {
        $value = trim($value);
    }
    return !empty($value);
}

/**
 * Validate string length
 * 
 * @param string $value The string to validate
 * @param int $min Minimum length (default: 1)
 * @param int $max Maximum length (default: 255)
 * @return bool True if length is within range, false otherwise
 */
function validate_length($value, $min = 1, $max = 255) {
    if (!is_string($value)) {
        return false;
    }
    $length = mb_strlen(trim($value), 'UTF-8');
    return $length >= $min && $length <= $max;
}

/**
 * Validate integer value
 * 
 * @param mixed $value The value to validate
 * @param int|null $min Minimum value (null for no minimum)
 * @param int|null $max Maximum value (null for no maximum)
 * @return bool True if valid integer within range, false otherwise
 */
function validate_integer($value, $min = null, $max = null) {
    $int_value = filter_var($value, FILTER_VALIDATE_INT);
    if ($int_value === false) {
        return false;
    }
    if ($min !== null && $int_value < $min) {
        return false;
    }
    if ($max !== null && $int_value > $max) {
        return false;
    }
    return true;
}

/**
 * Validate email address
 * 
 * @param string $email The email to validate
 * @return bool True if valid email, false otherwise
 */
function validate_email($email) {
    if (!is_string($email)) {
        return false;
    }
    return filter_var(trim($email), FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (basic validation)
 * 
 * @param string $phone The phone number to validate
 * @return bool True if valid phone format, false otherwise
 */
function validate_phone($phone) {
    if (!is_string($phone)) {
        return false;
    }
    // Remove common phone number characters
    $cleaned = preg_replace('/[\s\-\(\)\+]/', '', trim($phone));
    // Check if it's all digits and reasonable length (7-15 digits)
    return preg_match('/^\d{7,15}$/', $cleaned) === 1;
}

/**
 * Validate date format (YYYY-MM-DD)
 * 
 * @param string $date The date to validate
 * @return bool True if valid date format, false otherwise
 */
function validate_date($date) {
    if (!is_string($date)) {
        return false;
    }
    $date = trim($date);
    // Check format YYYY-MM-DD
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) !== 1) {
        return false;
    }
    // Check if it's a valid date
    $date_parts = explode('-', $date);
    return checkdate((int)$date_parts[1], (int)$date_parts[2], (int)$date_parts[0]);
}

/**
 * Validate that value is in a whitelist
 * 
 * @param mixed $value The value to validate
 * @param array $allowed_values Array of allowed values
 * @return bool True if value is in whitelist, false otherwise
 */
function validate_whitelist($value, array $allowed_values) {
    return in_array($value, $allowed_values, true);
}

/**
 * Validate username format
 * 
 * @param string $username The username to validate
 * @param int $min Minimum length (default: 3)
 * @param int $max Maximum length (default: 50)
 * @return bool True if valid username, false otherwise
 */
function validate_username($username, $min = 3, $max = 50) {
    if (!is_string($username)) {
        return false;
    }
    $username = trim($username);
    // Check length
    if (!validate_length($username, $min, $max)) {
        return false;
    }
    // Username can contain letters, numbers, underscores, and hyphens
    return preg_match('/^[a-zA-Z0-9_-]+$/', $username) === 1;
}

/**
 * Validate name (first name, last name, etc.)
 * 
 * @param string $name The name to validate
 * @param int $min Minimum length (default: 1)
 * @param int $max Maximum length (default: 100)
 * @return bool True if valid name, false otherwise
 */
function validate_name($name, $min = 1, $max = 100) {
    if (!is_string($name)) {
        return false;
    }
    $name = trim($name);
    // Check length
    if (!validate_length($name, $min, $max)) {
        return false;
    }
    // Name can contain letters, spaces, hyphens, and apostrophes
    return preg_match("/^[a-zA-Z\s'-]+$/u", $name) === 1;
}

/**
 * Validate password strength
 * 
 * @param string $password The password to validate
 * @param int $min_length Minimum length (default: 8)
 * @return bool True if password meets requirements, false otherwise
 */
function validate_password($password, $min_length = 8) {
    if (!is_string($password)) {
        return false;
    }
    $password = trim($password);
    // Check minimum length
    if (mb_strlen($password, 'UTF-8') < $min_length) {
        return false;
    }
    // Password should contain at least one letter and one number
    return preg_match('/[a-zA-Z]/', $password) === 1 && preg_match('/[0-9]/', $password) === 1;
}

/**
 * Sanitize string input (trim and basic cleaning)
 * 
 * @param mixed $value The value to sanitize
 * @return string Sanitized string
 */
function sanitize_string($value) {
    if (!is_string($value)) {
        return '';
    }
    return trim($value);
}

/**
 * Sanitize integer input
 * 
 * @param mixed $value The value to sanitize
 * @param int $default Default value if invalid (default: 0)
 * @return int Sanitized integer
 */
function sanitize_integer($value, $default = 0) {
    $int_value = filter_var($value, FILTER_VALIDATE_INT);
    return $int_value !== false ? $int_value : $default;
}

/**
 * Sanitize email input
 * 
 * @param mixed $value The value to sanitize
 * @return string Sanitized email or empty string if invalid
 */
function sanitize_email($value) {
    if (!is_string($value)) {
        return '';
    }
    $email = filter_var(trim($value), FILTER_SANITIZE_EMAIL);
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false ? $email : '';
}

/**
 * Get validation error message
 * 
 * @param string $field_name The name of the field
 * @param string $validation_type The type of validation that failed
 * @return string Error message
 */
function get_validation_error($field_name, $validation_type) {
    $messages = [
        'required' => ucfirst($field_name) . ' is required.',
        'length' => ucfirst($field_name) . ' length is invalid.',
        'integer' => ucfirst($field_name) . ' must be a valid number.',
        'email' => ucfirst($field_name) . ' must be a valid email address.',
        'phone' => ucfirst($field_name) . ' must be a valid phone number.',
        'date' => ucfirst($field_name) . ' must be a valid date.',
        'whitelist' => ucfirst($field_name) . ' has an invalid value.',
        'username' => ucfirst($field_name) . ' must be 3-50 characters and contain only letters, numbers, underscores, and hyphens.',
        'name' => ucfirst($field_name) . ' must contain only letters, spaces, hyphens, and apostrophes.',
        'password' => ucfirst($field_name) . ' must be at least 8 characters and contain both letters and numbers.',
    ];
    
    return $messages[$validation_type] ?? ucfirst($field_name) . ' is invalid.';
}

/**
 * Validate multiple fields at once
 * 
 * @param array $fields Array of field names and their validation rules
 *                    Format: ['field_name' => ['type' => 'required|length', 'min' => 3, 'max' => 50], ...]
 * @param array $data Array of data to validate (usually $_POST or $_GET)
 * @return array Array with 'valid' (bool) and 'errors' (array of error messages)
 */
function validate_fields(array $fields, array $data) {
    $errors = [];
    $valid = true;
    
    foreach ($fields as $field_name => $rules) {
        $value = $data[$field_name] ?? null;
        $field_valid = true;
        
        // Check required
        if (isset($rules['required']) && $rules['required']) {
            if (!validate_required($value)) {
                $errors[$field_name] = get_validation_error($field_name, 'required');
                $valid = false;
                $field_valid = false;
                continue; // Skip other validations if required fails
            }
        }
        
        // Skip other validations if field is empty and not required
        if (empty($value) && (!isset($rules['required']) || !$rules['required'])) {
            continue;
        }
        
        // Validate based on type
        if (isset($rules['type'])) {
            $type = $rules['type'];
            
            if ($type === 'string' || $type === 'length') {
                $min = $rules['min'] ?? 1;
                $max = $rules['max'] ?? 255;
                if (!validate_length($value, $min, $max)) {
                    $errors[$field_name] = get_validation_error($field_name, 'length');
                    $valid = false;
                }
            } elseif ($type === 'integer' || $type === 'int') {
                $min = $rules['min'] ?? null;
                $max = $rules['max'] ?? null;
                if (!validate_integer($value, $min, $max)) {
                    $errors[$field_name] = get_validation_error($field_name, 'integer');
                    $valid = false;
                }
            } elseif ($type === 'email') {
                if (!validate_email($value)) {
                    $errors[$field_name] = get_validation_error($field_name, 'email');
                    $valid = false;
                }
            } elseif ($type === 'phone') {
                if (!validate_phone($value)) {
                    $errors[$field_name] = get_validation_error($field_name, 'phone');
                    $valid = false;
                }
            } elseif ($type === 'date') {
                if (!validate_date($value)) {
                    $errors[$field_name] = get_validation_error($field_name, 'date');
                    $valid = false;
                }
            } elseif ($type === 'username') {
                $min = $rules['min'] ?? 3;
                $max = $rules['max'] ?? 50;
                if (!validate_username($value, $min, $max)) {
                    $errors[$field_name] = get_validation_error($field_name, 'username');
                    $valid = false;
                }
            } elseif ($type === 'name') {
                $min = $rules['min'] ?? 1;
                $max = $rules['max'] ?? 100;
                if (!validate_name($value, $min, $max)) {
                    $errors[$field_name] = get_validation_error($field_name, 'name');
                    $valid = false;
                }
            } elseif ($type === 'password') {
                $min_length = $rules['min_length'] ?? 8;
                if (!validate_password($value, $min_length)) {
                    $errors[$field_name] = get_validation_error($field_name, 'password');
                    $valid = false;
                }
            } elseif ($type === 'whitelist' && isset($rules['allowed'])) {
                if (!validate_whitelist($value, $rules['allowed'])) {
                    $errors[$field_name] = get_validation_error($field_name, 'whitelist');
                    $valid = false;
                }
            }
        }
    }
    
    return [
        'valid' => $valid,
        'errors' => $errors
    ];
}
