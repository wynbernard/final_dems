<?php
// In your PHP file that handles the table data
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10; // Items per page

// Base query
$sql = "SELECT * FROM admin_table";
$countSql = "SELECT COUNT(*) FROM admin_table";

// Add search conditions
if (!empty($searchTerm)) {
	$searchParam = "%$searchTerm%";
	$sql .= " WHERE name LIKE :search OR email LIKE :search OR description LIKE :search";
	$countSql .= " WHERE l_name LIKE :search OR email LIKE :search OR description LIKE :search";
}

// Add pagination
$sql .= " LIMIT :offset, :perPage";
$offset = ($page - 1) * $perPage;

// Execute query
$stmt = $pdo->prepare($sql);
if (!empty($searchTerm)) {
	$stmt->bindParam(':search', $searchParam);
}
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':perPage', $perPage, PDO::PARAM_INT);
$stmt->execute();

// Get total count for pagination
$countStmt = $pdo->prepare($countSql);
if (!empty($searchTerm)) {
	$countStmt->bindParam(':search', $searchParam);
}
$countStmt->execute();
$totalItems = $countStmt->fetchColumn();
$totalPages = ceil($totalItems / $perPage);
