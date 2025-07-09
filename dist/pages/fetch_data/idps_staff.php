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
        $date = new DateTime($idp['date_of_birth']);
        $today = new DateTime('today');
        $age = $today->diff($date)->y; // Calculate age in years

        // Output the IDP details in HTML format
        echo '
<div class="container bg-white p-4 rounded-4 shadow-sm border">
    <div class="row g-4">

        <!-- Left Column -->
        <div class="col-md-6">
            <p class="mb-2">
                <i class="fas fa-user me-2 text-primary"></i>
                <strong class="fw-semibold">Full Name:</strong>
                <span class="text-muted">' . htmlspecialchars($idp['f_name'] . ' ' . $idp['l_name']) . '</span>
            </p>

            <p class="mb-2">
                <i class="fas fa-venus-mars me-2 text-primary"></i>
                <strong class="fw-semibold">Gender:</strong>
                <span class="text-muted">' . htmlspecialchars($idp['gender']) . '</span>
            </p>

            <p class="mb-2">
                <i class="fas fa-birthday-cake me-2 text-primary"></i>
                <strong class="fw-semibold">Age:</strong>
                <span class="text-muted">' . htmlspecialchars($age) . '</span>
            </p>
        </div>

        <!-- Right Column -->
        <div class="col-md-6">
            <p class="mb-2">
                <i class="fas fa-door-open me-2 text-primary"></i>
                <strong class="fw-semibold">Assigned Room:</strong>
                <span class="text-muted">' . htmlspecialchars($idp['room_name']) . '</span>
            </p>

            <p class="mb-2">
                <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                <strong class="fw-semibold">Location:</strong>
                <span class="text-muted">' . htmlspecialchars($idp['location_name']) . '</span>
            </p>
        </div>

    </div>
</div>';
    } else {
        echo '<p class="text-danger">IDP not found.</p>';
    }
} else {
    echo '<p class="text-danger">Invalid request.</p>';
}
