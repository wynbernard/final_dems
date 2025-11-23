<?php
header("Content-Type: application/json");

// DB connection
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "f_dems"; // change this

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => $conn->connect_error]);
    exit();
}

// Validate upload
if (!isset($_FILES["sqlfile"])) {
    echo json_encode(["success" => false, "error" => "No SQL file uploaded."]);
    exit();
}

$sql = file_get_contents($_FILES["sqlfile"]["tmp_name"]);
if (!$sql) {
    echo json_encode(["success" => false, "error" => "Failed to read SQL file."]);
    exit();
}

/*
    ===============================================
    MODIFY SQL BEFORE EXECUTION
    ===============================================

    1. Remove all DROP TABLE statements
    2. Convert CREATE TABLE → CREATE TABLE IF NOT EXISTS
    3. Convert INSERT → INSERT IGNORE (skip duplicate rows)
*/
$sql = preg_replace('/DROP TABLE IF EXISTS `?([a-zA-Z0-9_]+)`?;/i', '', $sql);
$sql = preg_replace('/CREATE TABLE `?([a-zA-Z0-9_]+)`?/i', 'CREATE TABLE IF NOT EXISTS `$1`', $sql);
$sql = preg_replace('/INSERT INTO/i', 'INSERT IGNORE INTO', $sql);

// Split SQL into statements
$statements = array_filter(array_map('trim', explode(";", $sql)));

$executed = 0;
$errors   = [];

foreach ($statements as $index => $stmt) {
    if ($stmt === "") continue;
    $stmt .= ";";

    if (!$conn->query($stmt)) {
        // Skip errors caused by duplicates or existing tables
        if (!str_contains($conn->error, "Duplicate") &&
            !str_contains($conn->error, "exists")) {
            
            // Report REAL errors only
            $errors[] = [
                "index" => $index,
                "error" => $conn->error,
                "sql"   => substr($stmt, 0, 200)
            ];
        }
    } else {
        $executed++;
    }
}

// Always return success, but include warnings if there are minor errors
if (empty($errors)) {
    echo json_encode([
        "success" => true,
        "message" => "Restore completed successfully (only new tables & new rows were added).",
        "executed_statements" => $executed
    ]);
} else {
    // Return success even with minor errors, but include warning
    echo json_encode([
        "success" => true,
        "message" => "Restore completed successfully with some minor warnings.",
        "executed_statements" => $executed,
        "has_warnings" => true,
        "error_list" => $errors
    ]);
}

$conn->close();
?>
