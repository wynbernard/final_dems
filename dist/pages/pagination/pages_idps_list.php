<?php

// Set the number of records per page
$limit = 5; 

// Get the current page number
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1); // Ensure the page is at least 1

// Calculate the offset for SQL query
$offset = ($page - 1) * $limit;

// Get total number of records
$totalQuery = "SELECT COUNT(*) AS total FROM pre_reg_table";
$totalResult = mysqli_query($conn, $totalQuery);
$totalRow = mysqli_fetch_assoc($totalResult);
$totalRecords = $totalRow['total'];

// Calculate total pages
$totalPages = ceil($totalRecords / $limit);

// Fetch data for the current page
$query = "SELECT * FROM pre_reg_table LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);
?>