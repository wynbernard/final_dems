<?php
// Include database connection
include '../../../database/session.php';

// Retrieve evac_reg_id from query string
if (isset($_GET['id'])) {
    $evac_reg_id = intval($_GET['id']); // Sanitize input

    // Fetch IDP details from the database
    $query = "
            SELECT 
                er.evac_reg_id,
                pr.f_name,
                pr.l_name,
                pr.gender,
                r.room_name,
                pr.family_id,
                pr.date_of_birth,
                l.name AS location_name
            FROM evac_reg_table er
            INNER JOIN pre_reg_table pr ON er.pre_reg_id = pr.pre_reg_id
            INNER JOIN room_table r ON er.room_id = r.room_id
            INNER JOIN evac_loc_table l ON r.evac_loc_id = l.evac_loc_id
            WHERE er.evac_reg_id = '$evac_reg_id'
        ";
    $result = mysqli_query($conn, $query);


    if (mysqli_num_rows($result) > 0) {
        $idp = mysqli_fetch_assoc($result);
        $familyId = isset($idp['family_id']) ? intval($idp['family_id']) : 0;

        // If this evacuee belongs to a family, list all members of that family
        if ($familyId > 0) {
            $membersSql = "SELECT f_name, m_name, l_name, gender, date_of_birth, relation_to_family FROM pre_reg_table WHERE family_id = ? ORDER BY relation_to_family, f_name";
            if ($stmt = mysqli_prepare($conn, $membersSql)) {
                mysqli_stmt_bind_param($stmt, 'i', $familyId);
                mysqli_stmt_execute($stmt);
                $membersResult = mysqli_stmt_get_result($stmt);

                if ($membersResult && mysqli_num_rows($membersResult) > 0) {
                    $rowsHtml = '';
                    while ($m = mysqli_fetch_assoc($membersResult)) {
                        $mdob = !empty($m['date_of_birth']) ? new DateTime($m['date_of_birth']) : null;
                        $mage = $mdob ? (new DateTime('today'))->diff($mdob)->y : '';
                        $fullName = trim($m['f_name'] . ' ' . (!empty($m['m_name']) ? $m['m_name'] . ' ' : '') . $m['l_name']);
                        $rowsHtml .= '<tr>'
                            . '<td>' . htmlspecialchars($fullName) . '</td>'
                            . '<td>' . htmlspecialchars($m['relation_to_family'] ?? '') . '</td>'
                            . '<td>' . htmlspecialchars($m['gender'] ?? '') . '</td>'
                            . '<td>' . htmlspecialchars($mage) . '</td>'
                            . '</tr>';
                    }

                    echo '
                        <div class="container bg-white p-3 rounded-4 shadow-sm border">
                            <h6 class="fw-bold mb-2"><i class="fas fa-users me-2 text-primary"></i>Family Members</h6>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Name</th>
                                            <th>Relation</th>
                                            <th>Gender</th>
                                            <th>Age</th>
                                        </tr>
                                    </thead>
                                    <tbody>' . $rowsHtml . '</tbody>
                                </table>
                            </div>
                        </div>';
                } else {
                    echo '<div class="p-3">No family members found.</div>';
                }
                mysqli_stmt_close($stmt);
            } else {
                echo '<div class="p-3 text-danger">Unable to load family members.</div>';
            }
        } else {
            echo '<div class="p-3">This evacuee is not linked to a family.</div>';
        }
    } else {
        echo '<p class="text-danger">IDP not found.</p>';
    }
} else {
    echo '<p class="text-danger">Invalid request.</p>';
}
