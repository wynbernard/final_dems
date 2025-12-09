<?php
/**
 * CSRF Protection Helper
 * Provides functions to generate and validate CSRF tokens
 * 
 * Usage:
 *   - In forms: <?php echo csrf_token_field(); ?>
 *   - In action files: require_once '../../../database/csrf.php'; csrf_validate();
 */

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generate a CSRF token and store it in session
 * @return string The CSRF token
 */
function csrf_generate_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Get the current CSRF token (generates if doesn't exist)
 * @return string The CSRF token
 */
function csrf_get_token() {
    return csrf_generate_token();
}

/**
 * Validate CSRF token from POST request
 * @param string|null $token The token to validate (defaults to $_POST['csrf_token'])
 * @return bool True if valid, false otherwise
 */
function csrf_validate($token = null) {
    if ($token === null) {
        $token = $_POST['csrf_token'] ?? $_REQUEST['csrf_token'] ?? null;
    }
    
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    
    // Use hash_equals for timing-safe comparison
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Validate CSRF token and die with error if invalid
 * Use this in action files to protect against CSRF attacks
 * 
 * @param string|null $token The token to validate
 * @return void Dies with error message if invalid
 */
function csrf_validate_or_die($token = null) {
    if (!csrf_validate($token)) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'CSRF token validation failed. Please refresh the page and try again.'
        ]);
        exit();
    }
}

/**
 * Generate HTML hidden input field for CSRF token
 * Use this in forms: <?php echo csrf_token_field(); ?>
 * 
 * @return string HTML input field
 */
function csrf_token_field() {
    $token = csrf_get_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Get CSRF token as JSON (for AJAX requests)
 * @return string JSON encoded token
 */
function csrf_token_json() {
    return json_encode(['csrf_token' => csrf_get_token()]);
}

/**
 * Validate CSRF token from AJAX request
 * Checks for token in POST, GET, or X-CSRF-Token header
 * 
 * @return bool True if valid, false otherwise
 */
function csrf_validate_ajax() {
    // Check POST/GET first
    $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? null;
    
    // Check header (for AJAX requests)
    if (empty($token)) {
        $headers = getallheaders();
        $token = $headers['X-CSRF-Token'] ?? $headers['x-csrf-token'] ?? null;
    }
    
    return csrf_validate($token);
}

/**
 * Regenerate CSRF token (use after sensitive operations)
 * @return string New CSRF token
 */
function csrf_regenerate_token() {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
