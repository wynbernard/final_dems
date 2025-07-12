<?php
include '../../../database/conn.php';

$user = $_SESSION['pre_reg_id'];

$query = "SELECT barangay_manegement_table.barangay_name AS barangay_name FROM pre_reg_table
LEFT JOIN solo_address_table ON pre_reg_table.solo_address_id = solo_address_table.solo_address_id
LEFT JOIN barangay_manegement_table ON solo_address_table.barangay_id = barangay_manegement_table.barangay_id
WHERE pre_reg_table.pre_reg_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$user_barangay = $row['barangay_name'] ?? 'Taloc'; // fallback if not set

$barangayQuery = "SELECT latitude, longitude FROM barangay_manegement_table WHERE barangay_name = ?";
$stmt = $conn->prepare($barangayQuery);
$stmt->bind_param("s", $user_barangay);
$stmt->execute();
$barangayResult = $stmt->get_result();

$barangayCoords = $barangayResult->fetch_assoc();

// Prepare query to fetch centers in the same barangay
$query = "SELECT evac_loc_table.barangay_id, name , evac_loc_table.latitude AS latitude, evac_loc_table.longitude AS longitude, barangay_manegement_table.barangay_name AS barangay_name FROM evac_loc_table
LEFT JOIN barangay_manegement_table ON evac_loc_table.barangay_id = barangay_manegement_table.barangay_id
 WHERE barangay_name = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_barangay);
$stmt->execute();
$result = $stmt->get_result();

$locations = [];
while ($row = $result->fetch_assoc()) {
	$locations[] = $row;
}
