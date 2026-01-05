<?php
/**
 * Rate Limiting Helper
 * Provides functions to implement rate limiting for login attempts and API endpoints
 * 
 * Usage:
 *   - In login: require_once '../../../database/rate_limit.php'; rate_limit_check('login', $username);
 *   - In API: require_once '../../../database/rate_limit.php'; rate_limit_check('api', $ip_address);
 */

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if rate limit has been exceeded
 * 
 * @param string $type Type of rate limit ('login', 'api', 'password_reset', etc.)
 * @param string $identifier Unique identifier (username, IP address, etc.)
 * @param int $max_attempts Maximum attempts allowed
 * @param int $time_window Time window in seconds
 * @return array ['allowed' => bool, 'remaining' => int, 'reset_time' => int]
 */
function rate_limit_check($type, $identifier, $max_attempts = 5, $time_window = 300) {
    // Sanitize identifier
    $identifier = md5($type . '_' . $identifier);
    $key = "rate_limit_{$type}_{$identifier}";
    
    // Get current attempts from session
    $attempts = $_SESSION[$key] ?? [];
    
    // Clean old attempts outside the time window
    $current_time = time();
    $attempts = array_filter($attempts, function($timestamp) use ($current_time, $time_window) {
        return ($current_time - $timestamp) < $time_window;
    });
    
    // Count current attempts
    $attempt_count = count($attempts);
    
    // Check if limit exceeded
    if ($attempt_count >= $max_attempts) {
        // Calculate reset time (oldest attempt + time window)
        $oldest_attempt = min($attempts);
        $reset_time = $oldest_attempt + $time_window;
        
        return [
            'allowed' => false,
            'remaining' => 0,
            'reset_time' => $reset_time,
            'message' => "Too many attempts. Please try again in " . ceil(($reset_time - $current_time) / 60) . " minutes."
        ];
    }
    
    // Record this attempt
    $attempts[] = $current_time;
    $_SESSION[$key] = $attempts;
    
    return [
        'allowed' => true,
        'remaining' => $max_attempts - count($attempts),
        'reset_time' => $current_time + $time_window
    ];
}

/**
 * Record a failed attempt (for rate limiting)
 * 
 * @param string $type Type of rate limit
 * @param string $identifier Unique identifier
 * @return void
 */
function rate_limit_record_attempt($type, $identifier) {
    rate_limit_check($type, $identifier);
}

/**
 * Clear rate limit for a specific identifier (e.g., on successful login)
 * 
 * @param string $type Type of rate limit
 * @param string $identifier Unique identifier
 * @return void
 */
function rate_limit_clear($type, $identifier) {
    $identifier = md5($type . '_' . $identifier);
    $key = "rate_limit_{$type}_{$identifier}";
    unset($_SESSION[$key]);
}

/**
 * Get rate limit status without recording an attempt
 * 
 * @param string $type Type of rate limit
 * @param string $identifier Unique identifier
 * @param int $max_attempts Maximum attempts allowed
 * @param int $time_window Time window in seconds
 * @return array ['allowed' => bool, 'remaining' => int, 'reset_time' => int]
 */
function rate_limit_status($type, $identifier, $max_attempts = 5, $time_window = 300) {
    $identifier = md5($type . '_' . $identifier);
    $key = "rate_limit_{$type}_{$identifier}";
    
    $attempts = $_SESSION[$key] ?? [];
    $current_time = time();
    
    // Clean old attempts
    $attempts = array_filter($attempts, function($timestamp) use ($current_time, $time_window) {
        return ($current_time - $timestamp) < $time_window;
    });
    
    $attempt_count = count($attempts);
    
    if ($attempt_count >= $max_attempts) {
        $oldest_attempt = min($attempts);
        $reset_time = $oldest_attempt + $time_window;
        
        return [
            'allowed' => false,
            'remaining' => 0,
            'reset_time' => $reset_time
        ];
    }
    
    return [
        'allowed' => true,
        'remaining' => $max_attempts - $attempt_count,
        'reset_time' => $current_time + $time_window
    ];
}

/**
 * Get client IP address (handles proxies and load balancers)
 * 
 * @return string IP address
 */
function get_client_ip() {
    $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 
                'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
    
    foreach ($ip_keys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

