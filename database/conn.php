<?php
/**
 * Database Connection Configuration
 * Uses environment variables from .env file or system environment
 * 
 * SECURITY: No hardcoded credentials. All credentials must be set via environment variables.
 * For local development, create a .env file with your database credentials.
 */

// Load environment variables
require_once __DIR__ . '/env_loader.php';

// Get database credentials from environment variables
// Only use safe local development defaults if environment variables are not set
$servername = getenv('DB_HOST');
$username   = getenv('DB_USER');
$password   = getenv('DB_PASS');
$dbname     = getenv('DB_NAME');

// Validate that required environment variables are set
// In production, these should always be set via .env file or system environment
if (empty($servername) || empty($username) || empty($dbname)) {
	error_log("Database configuration error: Required environment variables (DB_HOST, DB_USER, DB_NAME) are not set.");
	die("Database configuration error. Please contact administrator.");
}

// Password can be empty for local development (e.g., root user with no password)
// But log a warning if it's empty in what appears to be a production environment
if (empty($password) && $servername !== 'localhost' && $servername !== '127.0.0.1') {
	error_log("Database configuration warning: DB_PASS is empty for non-localhost connection.");
}

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
	// Log error instead of exposing details
	error_log("Database connection failed: " . $conn->connect_error);
	die("Database connection failed. Please contact administrator.");
}
