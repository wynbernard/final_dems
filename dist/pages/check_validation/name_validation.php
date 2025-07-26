<?php
include '../../../database/conn.php';

// Sanitize input
$f_name = isset($_POST['f_name']) ? trim($_POST['f_name']) : '';
$m_name = isset($_POST['m_name']) ? trim($_POST['m_name']) : '';
$l_name = isset($_POST['l_name']) ? trim($_POST['l_name']) : '';
$name_ext = isset($_POST['name_ext']) ? trim($_POST['name_ext']) : null; // extension is optional

if ($f_name === '' || $m_name === '' || $l_name === '') {
    echo "invalid";
    exit;
}

// Prepare query including extension (allow NULL and empty string)
if ($name_ext !== null && $name_ext !== '') {
    $stmt = $conn->prepare("SELECT * FROM pre_reg_table WHERE f_name = ? AND m_name = ? AND l_name = ? AND name_ext = ? LIMIT 1");
    $stmt->bind_param("ssss", $f_name, $m_name, $l_name, $name_ext);
} else {
    $stmt = $conn->prepare("SELECT * FROM pre_reg_table WHERE f_name = ? AND m_name = ? AND l_name = ? AND (name_ext IS NULL OR name_ext = '') LIMIT 1");
    $stmt->bind_param("sss", $f_name, $m_name, $l_name);
}

$stmt->execute();
$stmt->store_result();

// Check if name exists
if ($stmt->num_rows > 0) {
    echo "taken";
} else {
    echo "available";
}

$stmt->close();
$conn->close();
?>
