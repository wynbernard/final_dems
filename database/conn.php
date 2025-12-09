<?php
/**
 * Database Connection Configuration
 * Uses environment variables from .env file or system environment
 */

// Load environment variables
require_once __DIR__ . '/env_loader.php';

// Get database credentials from environment variables with fallback defaults
$servername = getenv('DB_HOST') ?: 'localhost';
$username   = getenv('DB_USER') ?: 'u520834156_userDEMS';
$password   = getenv('DB_PASS') ?: '5YnY61~U~Hz';
$dbname     = getenv('DB_NAME') ?: 'u520834156_DBDems';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
	// Log error instead of exposing details
	error_log("Database connection failed: " . $conn->connect_error);
	die("Database connection failed. Please contact administrator.");
}
