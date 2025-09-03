<?php
header('Content-Type: application/json');
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php-error.log');
error_reporting(E_ALL);

include '../../../database/session.php'; // Include DB connection

try {
    if (!isset($_GET['pre_reg_id'])) {
        throw new Exception("pre_reg_id is required");
    }

    $pre_reg_id = intval($_GET['pre_reg_id']);

    // Query to get registration details
    $query = "SELECT 
               CONCAT(pr.f_name,' ',pr.m_name,' ',pr.l_name) AS name,
               qt.code AS qr_code,
                sat.solo_address_id AS address_id,
                CONCAT(sat.street, ', ', bmt.barangay_name, ', ', sat.city_municipality) AS solo_address,
               CONCAT(ft.street, ', ', bmt2.barangay_name, ', ', ft.city_municipality) AS family_address,
               bmt.barangay_name AS solo_barangay,
               bmt2.barangay_name AS family_barangay,
                pr.contact_no AS contact_number,
                -- Count of members with same solo_address_id
    (SELECT COUNT(*) FROM pre_reg_table pr2 WHERE pr2.solo_address_id = pr.solo_address_id) AS solo_member_count,
    -- Count of members in the same family
    (SELECT COUNT(*) FROM pre_reg_table pr3 WHERE pr3.family_id = pr.family_id) AS family_member_count
              FROM pre_reg_table pr
              LEFT JOIN solo_address_table sat ON pr.solo_address_id = sat.solo_address_id
              LEFT JOIN qr_table qt ON pr.qr_id = qt.qr_id
              LEFT JOIN barangay_manegement_table bmt ON sat.barangay_id = bmt.barangay_id
              LEFT JOIN family_table ft ON pr.family_id = ft.family_id
              LEFT JOIN barangay_manegement_table bmt2 ON ft.barangay_id = bmt2.barangay_id
              WHERE pr.pre_reg_id = ?
              LIMIT 1";

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $pre_reg_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        echo json_encode([
            "success" => true,
            "data" => [
                "name" => $row['name'],
                "qr_code" => $row['qr_code'],
                "contact_number" => $row['contact_number'],
                "solo_address" => $row['solo_address'],
                "family_address" => $row['family_address'],
                "solo_barangay" => $row['solo_barangay'],
                "family_barangay" => $row['family_barangay'],
                "solo_member_count" => $row['solo_member_count'],
                "family_member_count" => $row['family_member_count']
            ]
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "error" => "No registration found for pre_reg_id $pre_reg_id"
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
