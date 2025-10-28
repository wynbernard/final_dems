<?php
/**
 * Simple encryption/decryption helper for URL parameters
 * Uses AES-256-CBC encryption with a secret key
 */

// Secret key - should be stored in environment variables in production
define('ENCRYPTION_KEY', 'your-secret-key-here-change-this-to-random-value');
define('ENCRYPTION_CIPHER', 'AES-256-CBC');

/**
 * Encrypt a value for URL parameter
 * @param string $value The value to encrypt
 * @return string Base64 encoded encrypted string
 */
function encrypt_url_param($value) {
    if (empty($value)) {
        return '';
    }
    
    // Generate a random IV (Initialization Vector)
    $iv = openssl_random_pseudo_bytes(16);
    
    // Encrypt the value
    $encrypted = openssl_encrypt(
        (string)$value,
        ENCRYPTION_CIPHER,
        hash('sha256', ENCRYPTION_KEY, true),
        0,
        $iv
    );
    
    // Prepend IV to encrypted data and encode
    return base64_encode($iv . $encrypted);
}

/**
 * Decrypt a URL parameter
 * @param string $encrypted The encrypted value
 * @return string Decrypted value or empty string on failure
 */
function decrypt_url_param($encrypted) {
    if (empty($encrypted)) {
        return '';
    }
    
    try {
        // Decode from base64
        $data = base64_decode($encrypted, true);
        if ($data === false) {
            return '';
        }
        
        // Extract IV (first 16 bytes)
        $iv = substr($data, 0, 16);
        $encrypted_data = substr($data, 16);
        
        // Decrypt
        $decrypted = openssl_decrypt(
            $encrypted_data,
            ENCRYPTION_CIPHER,
            hash('sha256', ENCRYPTION_KEY, true),
            0,
            $iv
        );
        
        return $decrypted !== false ? $decrypted : '';
    } catch (Exception $e) {
        return '';
    }
}

/**
 * Encrypt ID for display in HTML
 * @param mixed $id The ID to encrypt
 * @return string Encrypted ID string
 */
function encrypt_for_display($id) {
    if (empty($id) || $id === 0 || $id === '0') {
        return '';
    }
    return encrypt_url_param((string)$id);
}

/**
 * Decrypt ID from URL parameter
 * @param string $encrypted The encrypted ID from URL
 * @return mixed Decrypted ID or empty string
 */
function decrypt_from_url($encrypted) {
    if (empty($encrypted)) {
        return '';
    }
    return decrypt_url_param($encrypted);
}
?>
