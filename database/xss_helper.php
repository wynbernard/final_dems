<?php
/**
 * XSS Protection Helper Functions
 * Provides functions to safely escape output for HTML
 * 
 * Usage:
 *   - In HTML: <?php echo e($variable); ?>
 *   - In attributes: <?php echo e_attr($variable); ?>
 *   - In JavaScript: <?php echo e_js($variable); ?>
 */

/**
 * Escape output for HTML content
 * Use this for text content inside HTML tags
 * 
 * @param mixed $value The value to escape
 * @return string Escaped string safe for HTML output
 */
function e($value) {
    if ($value === null) {
        return '';
    }
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/**
 * Escape output for HTML attributes
 * Use this for values in HTML attributes (e.g., value="...", data-*="...")
 * 
 * @param mixed $value The value to escape
 * @return string Escaped string safe for HTML attributes
 */
function e_attr($value) {
    if ($value === null) {
        return '';
    }
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Escape output for JavaScript
 * Use this when outputting values in JavaScript code
 * 
 * @param mixed $value The value to escape
 * @return string Escaped string safe for JavaScript
 */
function e_js($value) {
    if ($value === null) {
        return '';
    }
    return json_encode((string)$value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
}

/**
 * Escape and format a date for display
 * 
 * @param string $date Date string
 * @param string $format Date format (default: 'F j, Y g:i A')
 * @return string Formatted and escaped date
 */
function e_date($date, $format = 'F j, Y g:i A') {
    if (empty($date)) {
        return 'N/A';
    }
    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return 'N/A';
    }
    return e(date($format, $timestamp));
}

/**
 * Escape output for URL parameters
 * Use this when building URLs with user input
 * 
 * @param mixed $value The value to escape
 * @return string URL-encoded string
 */
function e_url($value) {
    if ($value === null) {
        return '';
    }
    return urlencode((string)$value);
}

/**
 * Check if a value is already escaped (basic check)
 * This is a helper to avoid double-escaping
 * 
 * @param string $value The value to check
 * @return bool True if appears to be escaped
 */
function is_escaped($value) {
    // Basic check - if it contains HTML entities, it might be escaped
    // This is not foolproof, but helps avoid double-escaping
    return $value !== htmlspecialchars_decode($value, ENT_QUOTES);
}
