<?php
// Suppress warnings and errors to prevent them from corrupting SQL output
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering to capture any unwanted output from includes
ob_start();

// Get the correct path to database files (3 levels up from dist/pages/action/)
require_once '../../../database/session.php';

// session.php calls ob_end_flush(), which flushes its own buffer
// Discard any content in our outer buffer (in case of any warnings/errors)
ob_clean();

// Set headers for file download (must be before any output)
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="database_backup_' . date('Y-m-d_H-i-s') . '.sql"');
header('Pragma: no-cache');
header('Expires: 0');

// Use the existing connection
$backup_conn = $conn;

if (!$backup_conn || $backup_conn->connect_error) {
    // Output error as SQL comment if connection fails
    echo "-- ERROR: Database connection failed\n";
    echo "-- " . ($backup_conn ? $backup_conn->connect_error : "Connection object not found") . "\n";
    exit();
}

// Get database name from connection
$dbname = $backup_conn->query("SELECT DATABASE()")->fetch_array()[0];

// Set charset
$backup_conn->set_charset("utf8");

// Start output
echo "-- Database Backup\n";
echo "-- Generated on: " . date('Y-m-d H:i:s') . "\n";
echo "-- Database: " . $dbname . "\n\n";
echo "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
echo "SET AUTOCOMMIT = 0;\n";
echo "START TRANSACTION;\n";
echo "SET time_zone = \"+00:00\";\n\n";

// Get all tables
$tables_result = $backup_conn->query("SHOW TABLES");
$tables = [];

while ($row = $tables_result->fetch_array()) {
    $tables[] = $row[0];
}

// Backup each table
foreach ($tables as $table) {
    echo "\n-- --------------------------------------------------------\n";
    echo "-- Table structure for table `$table`\n";
    echo "-- --------------------------------------------------------\n\n";
    
    // Get table structure
    $create_table = $backup_conn->query("SHOW CREATE TABLE `$table`");
    $create_row = $create_table->fetch_array();
    echo "DROP TABLE IF EXISTS `$table`;\n";
    echo $create_row[1] . ";\n\n";
    
    // Get table data
    $data_result = $backup_conn->query("SELECT * FROM `$table`");
    
    if ($data_result->num_rows > 0) {
        echo "-- Dumping data for table `$table`\n\n";
        
        // Get column names
        $columns = [];
        $columns_result = $backup_conn->query("SHOW COLUMNS FROM `$table`");
        while ($col = $columns_result->fetch_assoc()) {
            $columns[] = $col['Field'];
        }
        
        // Insert data
        while ($row = $data_result->fetch_assoc()) {
            $values = [];
            foreach ($columns as $col) {
                $value = $row[$col];
                if ($value === NULL) {
                    $values[] = 'NULL';
                } else {
                    $value = $backup_conn->real_escape_string($value);
                    $values[] = "'$value'";
                }
            }
            
            echo "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");\n";
        }
        echo "\n";
    }
}

echo "\nCOMMIT;\n";

$backup_conn->close();
exit();
?>

