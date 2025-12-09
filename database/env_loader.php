<?php
/**
 * Simple environment variable loader
 * Loads .env file if it exists, otherwise uses system environment variables
 */

function loadEnv($filePath = null) {
    if ($filePath === null) {
        // Default to .env in project root
        $filePath = __DIR__ . '/../.env';
    }
    
    if (!file_exists($filePath)) {
        // .env file doesn't exist, use system environment variables
        return;
    }
    
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes if present
            if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                $value = substr($value, 1, -1);
            }
            
            // Set environment variable if not already set
            if (!getenv($key)) {
                putenv("$key=$value");
                $_ENV[$key] = $value;
            }
        }
    }
}

// Auto-load .env file when this file is included
loadEnv();
