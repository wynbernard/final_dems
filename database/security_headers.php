<?php
/**
 * Security Headers and HTTPS Enforcement
 * Include this file at the top of your PHP pages to enforce HTTPS and add security headers
 * 
 * Usage: require_once __DIR__ . '/security_headers.php';
 */

// Only enforce HTTPS in production (not on localhost)
function enforce_https() {
    // Check if we're not on localhost/127.0.0.1
    $is_localhost = in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1', '::1']) 
                    || strpos($_SERVER['HTTP_HOST'], 'localhost:') === 0
                    || strpos($_SERVER['HTTP_HOST'], '127.0.0.1:') === 0;
    
    // Check if HTTPS is not enabled
    $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
                || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
                || (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
    
    // Force HTTPS redirect in production (not localhost)
    if (!$is_localhost && !$is_https) {
        $https_url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        header("Location: $https_url", true, 301);
        exit();
    }
}

// Set security headers
function set_security_headers() {
    // HSTS (HTTP Strict Transport Security) - Only in production
    $is_localhost = in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1', '::1']) 
                    || strpos($_SERVER['HTTP_HOST'], 'localhost:') === 0;
    
    if (!$is_localhost) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
    }
    
    // X-Frame-Options: Prevent clickjacking
    header('X-Frame-Options: SAMEORIGIN');
    
    // X-Content-Type-Options: Prevent MIME type sniffing
    header('X-Content-Type-Options: nosniff');
    
    // X-XSS-Protection: Legacy XSS protection
    header('X-XSS-Protection: 1; mode=block');
    
    // Referrer Policy: Control referrer information
    // header('Referrer-Policy: strict-origin-when-cross-origin'); 
    
    // Permissions Policy (formerly Feature Policy)
    // NOTE: This line is commented out to allow geolocation, camera, and microphone access
    // If you need to block these features, uncomment the line below:
    // header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    
    // Remove server information
    header_remove('X-Powered-By');
    header_remove('Server');
    
    // Content Security Policy (CSP) - Basic policy, adjust as needed
    // Uncomment and adjust based on your application's needs
    // $csp = "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; img-src 'self' data: https:; font-src 'self' https://cdnjs.cloudflare.com; connect-src 'self';";
    // header("Content-Security-Policy: $csp");
}

// Auto-enforce HTTPS and set headers when file is included
// Uncomment the line below to auto-enforce HTTPS on all pages that include this file
// enforce_https();
set_security_headers();

