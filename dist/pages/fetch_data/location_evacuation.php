<?php
include '../../../database/conn.php';

$user = $_SESSION['pre_reg_id'];

$query = "SELECT 
    brg.barangay_name AS barangay_name,
	brg_fam.barangay_name AS family_barangay_name
FROM 
    pre_reg_table
LEFT JOIN solo_address_table ON pre_reg_table.solo_address_id = solo_address_table.solo_address_id
LEFT JOIN family_table ON pre_reg_table.family_id = family_table.family_id
LEFT JOIN barangay_manegement_table AS brg_fam ON family_table.barangay_id = brg_fam.barangay_id
LEFT JOIN barangay_manegement_table AS brg ON solo_address_table.barangay_id = brg.barangay_id
WHERE 
    pre_reg_table.pre_reg_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$user_barangay = $row['barangay_name'] ?? $row['family_barangay_name']; // fallback if not set

$barangayQuery = "SELECT latitude, longitude FROM barangay_manegement_table WHERE barangay_name = ?";
$stmt = $conn->prepare($barangayQuery);
$stmt->bind_param("s", $user_barangay);
$stmt->execute();
$barangayResult = $stmt->get_result();

$barangayCoords = $barangayResult->fetch_assoc();

// Prepare query to fetch centers in the same barangay
$query = "SELECT 
    evac_loc_table.barangay_id, 
    name,
    evac_loc_table.total_capacity AS total_capacity,
    evac_loc_table.status AS status,
    evac_loc_table.evac_loc_id,
    evac_loc_table.latitude AS latitude, 
    evac_loc_table.longitude AS longitude,
    barangay_manegement_table.barangay_name AS barangay_name,
    (
        SELECT COUNT(pre_reg_id)
        FROM pre_reg_table
        WHERE recommended_location = evac_loc_table.evac_loc_id
    ) AS total_recommended
    , (
        SELECT COUNT(er.evac_reg_id)
        FROM evac_reg_table er
        WHERE er.evac_loc_id = evac_loc_table.evac_loc_id AND er.status = 'Evacuated'
    ) AS total_registered
FROM evac_loc_table
LEFT JOIN barangay_manegement_table 
    ON evac_loc_table.barangay_id = barangay_manegement_table.barangay_id
LEFT JOIN room_table r ON evac_loc_table.evac_loc_id = r.evac_loc_id
LEFT JOIN evac_reg_table e ON r.room_id = e.room_id
GROUP BY evac_loc_table.barangay_id, name, evac_loc_table.latitude, evac_loc_table.longitude, barangay_manegement_table.barangay_name
HAVING status = 'Active'
";

$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();


$locations = [];
while ($row = $result->fetch_assoc()) {
    $locations[] = $row;
}
