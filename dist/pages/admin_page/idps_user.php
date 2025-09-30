<?php
include '../../../database/session.php'; // Include your database connection file
include '../layout/head_links.php'; // Include any necessary CSS or JS files


if ($_SESSION['role'] == 'admin') {
    // SQL query to fetch all data from evac_reg_table
    $query = "SELECT * FROM evac_reg_table
LEFT JOIN evac_loc_table ON evac_reg_table.evac_loc_id = evac_loc_table.evac_loc_id
LEFT JOIN disaster_table ON evac_reg_table.disaster_id = disaster_table.disaster_id
LEFT JOIN room_table ON evac_reg_table.room_id = room_table.room_id
LEFT JOIN pre_reg_table ON evac_reg_table.pre_reg_id = pre_reg_table.pre_reg_id
LEFT JOIN age_class_table ON pre_reg_table.age_class_id = age_class_table.age_class_id
WHERE evac_reg_table.status = 'Evacuated'
";

    // Execute the query
    $result = mysqli_query($conn, $query);

    if (!$result) {
        die("Query failed: " . mysqli_error($conn));
    }

    // Check if there are any rows in the result
    $hasRecords = mysqli_num_rows($result) > 0;

    // Fetch column names dynamically
    $columns = [];
    if ($hasRecords) {
        $columns = array_keys(mysqli_fetch_assoc($result)); // Get column names
        mysqli_data_seek($result, 0); // Reset the pointer to the beginning of the result set
    }
} else {
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Evacuation Registration Data</title>
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">
        <?php include '../layout/header.php';
        include '../layout/sidebar.php';
        include '../alert/warning.php'; ?>

        <main class="app-main">
            <!-- Page Header -->
            <div class="app-content-header ">
                <div class="row">
                    <div class="col-sm-6 d-flex align-items-center gap-2">
                        <i class="fas fa-people-roof fs-2 text-primary"></i>
                        <h3 class="mb-0">Registration</h3>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <ol class="breadcrumb justify-content-md-end">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Evacuation Registration Data</li>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- Selection and Results Card -->
            <div class="content">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card shadow-sm">
                            <div class="card-header d-flex align-items-center flex-wrap">
                                <!-- Search and Location Filter -->
                                <form method="GET" id="locationForm" class="d-flex flex-wrap align-items-center w-100 mb-2 ms-auto">
                                    <!-- Location Dropdown -->
                                    <select name="location_id" id="locationSelect" class="form-select me-2 mb-2" style="max-width: 250px;" required>
                                        <?php
                                        // For staff users - show ONLY their assigned location
                                        if ($_SESSION['role'] == 'Staff') {
                                            $staff_location_id = $_SESSION['evac_loc_id'];
                                            $query = mysqli_query($conn, "SELECT evac_loc_table.evac_loc_id, evac_loc_table.name 
											FROM admin_table
											JOIN evac_loc_table ON admin_table.evac_loc_id = evac_loc_table.evac_loc_id
											WHERE admin_table.evac_loc_id = '$staff_location_id'");

                                            if ($query && mysqli_num_rows($query) > 0) {
                                                $loc = mysqli_fetch_assoc($query);
                                                echo '<option value="' . htmlspecialchars($loc['evac_loc_id']) . '" selected>' . htmlspecialchars($loc['name']) . '</option>';
                                            } else {
                                                echo '<option value="" selected>Location Not Found</option>';
                                            }
                                        }
                                        // For admin users - show all locations
                                        else {
                                            echo '<option value="">All Locations</option>';
                                            $query = mysqli_query($conn, "SELECT evac_loc_id, name FROM evac_loc_table ORDER BY name ASC");
                                            if ($query) {
                                                while ($loc = mysqli_fetch_assoc($query)) {
                                                    $selected = (isset($_GET['location_id']) && $_GET['location_id'] == $loc['evac_loc_id']) ? 'selected' : '';
                                                    echo '<option value="' . htmlspecialchars($loc['evac_loc_id']) . '" ' . $selected . '>' . htmlspecialchars($loc['name']) . '</option>';
                                                }
                                            }
                                        }
                                        ?>
                                    </select>
                                    <!-- Disaster Selection Dropdown -->
                                    <select name="disasterId" id="disasterSelect" class="form-select me-2 mb-2" style="max-width: 250px;" required>
                                        <option value="" disabled selected>Select Disaster Event</option>
                                        <?php
                                        $disasterQuery = mysqli_query($conn, "SELECT disaster_id, disaster_name FROM disaster_table WHERE status = 'Ongoing' ORDER BY disaster_id DESC");
                                        if ($disasterQuery && mysqli_num_rows($disasterQuery) > 0) {
                                            while ($disaster = mysqli_fetch_assoc($disasterQuery)) {
                                                $selected = (isset($_GET['disasterId']) && $_GET['disasterId'] == $disaster['disaster_id']) ? 'selected' : '';
                                                echo '<option value="' . htmlspecialchars($disaster['disaster_id']) . '" ' . $selected . '>' . htmlspecialchars($disaster['disaster_name']) . '</option>';
                                            }
                                        } else {
                                            echo '<option value="">No disaster events found</option>';
                                        }
                                        ?>
                                    </select>

                                    <!-- Register Button -->
                                    <button type="button" id="registerBtn" class="btn btn-primary mb-2">
                                        <i class="fas fa-user-plus me-1"></i> Redgister IDP
                                    </button>

                                    <button type="button" class="btn btn-danger mb-2 ms-2" id="dispatchAllBtn"><i class="fas fa-truck-moving me-1"></i> Dispatch All</button>

                                    <?php
                                    $locationId = $_GET['location_id'] ?? '';
                                    $selectedDisasterId = $_GET['disaster_id'] ?? '';

                                    // Age classification query (filter only if location selected)
                                    $ageQuery = "
												SELECT 
													SUM(CASE WHEN a.classification = 'Child' THEN 1 ELSE 0 END) AS Child,
													SUM(CASE WHEN a.classification = 'Teen' THEN 1 ELSE 0 END) AS Teen,
													SUM(CASE WHEN a.classification = 'Adult' THEN 1 ELSE 0 END) AS Adult,
													SUM(CASE WHEN a.classification = 'Senior' THEN 1 ELSE 0 END) AS Senior,
													COUNT(*) AS total,
                                                    er.status
												FROM evac_reg_table er
												INNER JOIN pre_reg_table pr ON er.pre_reg_id = pr.pre_reg_id
												LEFT JOIN age_class_table a ON pr.age_class_id = a.age_class_id
												INNER JOIN room_table r ON er.room_id = r.room_id
											";

                                    if (!empty($locationId)) {
                                        $ageQuery .= " WHERE r.evac_loc_id = '$locationId' AND er.status = 'Evacuated'";
                                    }else{
                                        $ageQuery .= " WHERE er.status = 'Evacuated'";
                                    }

                                    $ageResult = mysqli_query($conn, $ageQuery);
                                    $ageData = mysqli_fetch_assoc($ageResult);
                                    ?>
                                    <!-- Age Classification Counts -->
                                    <div class="age-classification ms-2 mb-2 d-flex align-items-center">
                                        <!-- <div class="badge bg-info me-1" title="Children (0-12)">
											<i class="fas fa-child me-1"></i>Children: <?= $ageData['Child'] ?? 0 ?>
										</div>
										<div class="badge bg-primary me-1" title="Teens (13-17)">
											<i class="fas fa-user me-1"></i>Teens: <?= $ageData['Teen'] ?? 0 ?>
										</div>
										<div class="badge bg-success me-1" title="Adults (18-59)">
											<i class="fas fa-user me-1"></i>Adults: <?= $ageData['Adult'] ?? 0 ?>
										</div>
										<div class="badge bg-warning me-1" title="Seniors (60+)">
											<i class="fas fa-user-tie me-1"></i>Senior: <?= $ageData['Senior'] ?? 0 ?>
										</div> -->
                                        <div class="badge bg-dark" title="Total">
                                            <i class="fas fa-users me-1"></i>Total: <?= $ageData['total'] ?? 0 ?>
                                        </div>
                                    </div>

                                    <!-- Search Box -->
                                    <input type="text" id="searchBox" class="form-control me-2 ms-auto" placeholder="Search IDPs..." style="max-width: 240px;">
                                    <!-- Print/Download Button -->
                                    <button type="button" class="btn btn-info mb-2" data-bs-toggle="modal" data-bs-target="#idCardModal">
                                        <i class="fas fa-id-card me-1"></i> View ID Card
                                    </button>
                                </form>

                            </div>

                            <?php
                            // Get the selected location ID
                            $locationId = $_GET['location_id'] ?? '';

                            // Query to fetch IDPs (with location filter if specified)
                            $query = "
									SELECT 
										l.evac_loc_id AS location_id,
										l.name AS location_name,
										r.room_name,
										er.evac_reg_id,
                                        er.status,
										er.date_reg,
										pr.f_name,
										pr.l_name
									FROM evac_loc_table l
									INNER JOIN room_table r ON l.evac_loc_id = r.evac_loc_id
									INNER JOIN evac_reg_table er ON r.room_id = er.room_id
									INNER JOIN pre_reg_table pr ON er.pre_reg_id = pr.pre_reg_id
                                    WHERE er.status = 'Evacuated'
								";


                            // Initialize base query
                            $query = "SELECT r.evac_reg_id, p.f_name, p.l_name, p.m_name, l.name AS location_name, rm.room_name , r.date_reg, r.status
									FROM evac_reg_table r
									LEFT JOIN pre_reg_table p ON r.pre_reg_id = p.pre_reg_id
									JOIN evac_loc_table l ON r.evac_loc_id = l.evac_loc_id
									LEFT JOIN room_table rm ON r.room_id = rm.room_id";

                            // For staff users - always filter by their assigned location
                            if ($_SESSION['role'] == 'Staff') {
                                $staff_location_id = $_SESSION['evac_loc_id'];
                                $query .= " WHERE r.evac_loc_id = '$staff_location_id' AND r.status = 'Evacuated' ";
                            }elseif($_SESSION['role'] == 'Admin'){
                                $query .= " WHERE r.status = 'Evacuated' ";
                            }
                            // For admin users - filter by selected location if specified
                            elseif (!empty($locationId)) {
                                $query .= " WHERE r.evac_loc_id = '$locationId' AND r.status = 'Evacuated'";
                            }

                            $query .= " ORDER BY l.name, rm.room_name, p.l_name";
                            $result = mysqli_query($conn, $query);

                            if (!$result) {
                                die('Query failed: ' . mysqli_error($conn));
                            }
                            ?>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <!-- Scrollable Table Wrapper -->
                                    <div style="max-height: 400px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: .375rem;">
                                        <table class="table table-sm table-hover mb-0" id="usertable" style="min-width: 600px;">
                                            <thead class="table-success text-nowrap sticky-top" style="top: 0; z-index: 1;">
                                                <tr>
                                                    <th>No.</th>
                                                    <th><i class="bi bi-person-fill"></i> Full Name</th>
                                                    <th><i class="bi bi-geo-alt-fill"></i> Location</th>
                                                    <th><i class="bi bi-door-closed-fill"></i> Assigned Room</th>
                                                    <th><i class="bi bi-calendar-event-fill"></i> Date</th>
                                                    <th><i class="bi bi-gear-fill"></i> Action</th>

                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (mysqli_num_rows($result) > 0):
                                                    $i = 1;
                                                    while ($row = mysqli_fetch_assoc($result)): ?>
                                                        <tr>
                                                            <td><?= $i++ ?>.</td>
                                                            <td><?= htmlspecialchars($row['f_name'] . " " . $row['m_name'] . " " . $row['l_name']) ?></td>
                                                            <td><?= htmlspecialchars($row['location_name']) ?></td>
                                                            <td><?= htmlspecialchars($row['room_name']) ?></td>
                                                            <td><?= date("F j, Y g:i A", strtotime($row['date_reg'])) ?></td>
                                                            <td>
                                                                <button
                                                                    class="btn btn-sm btn-info view-idp-btn"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#idpDetailsModal"
                                                                    data-id="<?= $row['evac_reg_id'] ?>">
                                                                    <i class="fas fa-eye me-1"></i> View Details
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    <?php endwhile;
                                                else: ?>
                                                    <tr>
                                                        <td colspan="5" class="text-center">
                                                            <?= empty($locationId) ? 'No IDPs found in the system.' : 'No IDPs found for this location.' ?>
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php include '../modal/details_idps.php'; ?>
        </main>
        <?php include '../modal/registered_idps.php';
        include '../layout/footer.php'; ?>
    </div>
    <script src="../scripts/scripts.js"></script>
    <script src="../scripts/admin_script/idps_user.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btn = document.getElementById('dispatchAllBtn');
            if (btn) {
                btn.addEventListener('click', function() {
                    const loc = document.getElementById('locationSelect').value;
                    if (!loc) {
                        if (window.Swal) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'No location selected',
                                text: 'Please select a location before dispatching.'
                            });
                        } else {
                            alert('Please select a location');
                        }
                        return;
                    }

                    // Check if the table currently has evacuees to dispatch
                    const userTable = document.getElementById('usertable');
                    if (userTable) {
                        const tbody = userTable.querySelector('tbody');
                        if (tbody) {
                            const rows = Array.from(tbody.querySelectorAll('tr'));
                            // Determine if any row represents an evacuee by looking for the action button
                            const hasEvacuees = rows.some(r => !!r.querySelector('.view-idp-btn'));
                            if (!hasEvacuees) {
                                if (window.Swal) {
                                    Swal.fire({
                                        icon: 'info',
                                        title: 'No evacuees',
                                        text: 'There are no evacuees at the selected location to dispatch.'
                                    });
                                } else {
                                    alert('There are no evacuees at the selected location to dispatch.');
                                }
                                return;
                            }
                        }
                    }

                    const confirmAndDispatch = () => {
                        // Show loading modal if Swal available
                        if (window.Swal) {
                            Swal.fire({
                                title: 'Dispatching...',
                                html: 'Please wait while evacuees are being dispatched.',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                        }

                        fetch('../action/dispatch_all.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: 'location_id=' + encodeURIComponent(loc)
                        }).then(r => r.json()).then(data => {
                            if (window.Swal) Swal.close();
                            if (data.success) {
                                if (window.Swal) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Dispatched',
                                        text: data.count ? data.count + ' evacuees dispatched.' : data.message
                                    }).then(() => window.location.reload());
                                } else {
                                    alert((data.count ? data.count + ' evacuees dispatched.' : data.message));
                                    window.location.reload();
                                }
                            } else {
                                if (window.Swal) {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: data.message || 'Failed to dispatch evacuees.'
                                    });
                                } else {
                                    alert('Error: ' + data.message);
                                }
                            }
                        }).catch(err => {
                            console.error(err);
                            if (window.Swal) Swal.close();
                            if (window.Swal) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Server error',
                                    text: 'Could not complete the dispatch. Try again.'
                                });
                            } else {
                                alert('Server error');
                            }
                        });
                    };

                    if (window.Swal) {
                        Swal.fire({
                            title: 'Confirm dispatch',
                            text: 'Are you sure you want to mark all evacuees at this location as Dispatched?',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, dispatch',
                            cancelButtonText: 'Cancel'
                        }).then((res) => {
                            if (res.isConfirmed) confirmAndDispatch();
                        });
                    } else {
                        if (confirm('Are you sure you want to mark all evacuees at this location as Dispatched?')) confirmAndDispatch();
                    }
                });
            }
            // Register button: validate disaster selection before opening modal
            const registerBtn = document.getElementById('registerBtn');
            if (registerBtn) {
                registerBtn.addEventListener('click', function(e) {
                    const disasterSelect = document.getElementById('disasterSelect');
                    const disasterVal = disasterSelect ? disasterSelect.value : '';
                    // If no disaster selected, show SweetAlert2 warning (if available) or fallback to alert
                    if (!disasterVal) {
                        if (window.Swal) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'No disaster selected',
                                text: 'Please select a disaster event before registering an IDP.',
                                confirmButtonText: 'OK'
                            });
                        } else {
                            alert('Please select a disaster event before registering an IDP.');
                        }
                        return;
                    }

                    // Open the register modal programmatically (Bootstrap 5)
                    const modalEl = document.getElementById('registerChoiceModal');
                    if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        const modal = new bootstrap.Modal(modalEl);
                        modal.show();
                    } else if (modalEl) {
                        // Fallback: toggle attribute to trigger modal
                        modalEl.classList.add('show');
                        modalEl.style.display = 'block';
                    }
                });
            }
        });
    </script>
    <div class="modal fade" id="idCardModal" tabindex="-1" aria-labelledby="idCardModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title" id="idCardModalLabel">ID Card Layout Preview (4 Copies)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <?php
                include '../../../database/conn.php';

                // Fetch full details for last 4 registrations
                $sql = "SELECT 
    prt.registered_as AS type,
    prt.family_id,
   ert.status,
	qr.code AS qr_code,
    prt.solo_address_id,
    prt.relation_to_family,
	CONCAT(ft.purok, ', ', bmt2.barangay_name, ', ', ft.city_municipality) AS family_address,
	bmt2.barangay_name AS family_barangay,
	CONCAT(sat.purok, ', ', bmt.barangay_name, ', ', sat.city_municipality) AS solo_address,
	bmt.barangay_name AS solo_barangay,
    -- Count members for each family
    (
        SELECT COUNT(*) 
        FROM pre_reg_table prt2
        WHERE prt2.family_id = prt.family_id
    ) AS member_count,
   CONCAT(prt.f_name,' ',prt.m_name,' ', prt.l_name) AS full_name,
    prt.contact_no AS contact_number,
    evc.name AS evacuation_center,
    DATE(MAX(ert.date_reg)) AS reg_date
FROM evac_reg_table ert
LEFT JOIN pre_reg_table prt ON ert.pre_reg_id = prt.pre_reg_id
LEFT JOIN qr_table qr ON prt.qr_id = qr.qr_id
LEFT JOIN evac_loc_table evc ON ert.evac_loc_id = evc.evac_loc_id
LEFT JOIN pre_reg_table prt2 ON ert.pre_reg_id = prt2.pre_reg_id
LEFT JOIN solo_address_table sat ON prt.solo_address_id = sat.solo_address_id
LEFT JOIN family_table ft ON prt2.family_id = ft.family_id
LEFT JOIN barangay_manegement_table bmt ON sat.barangay_id = bmt.barangay_id
LEFT JOIN barangay_manegement_table bmt2 ON ft.barangay_id = bmt2.barangay_id
WHERE prt.relation_to_family = 'Head of Family' AND ert.status = 'Evacuated'
GROUP BY prt.family_id
ORDER BY MAX(ert.date_reg) DESC";

                $result = $conn->query($sql);
                $registrations = $result->fetch_all(MYSQLI_ASSOC);

                // Close connection
                $conn->close();
                ?>

                <!-- Modal Body -->
                <div class="modal-body" id="idCardContent">
                    <div class="container-fluid">
                        <div class="row g-3">
                            <?php foreach ($registrations as $index => $reg):
                                $isFamily = $reg['type'] == 'Family';
                                // $stayInCenter = $reg['stay_in_center'] == 'yes';
                            ?>
                                <!-- Start new row after every 2 cards -->
                                <?php if ($index % 2 == 0): ?>
                                    <div class="w-100"></div>
                                <?php endif; ?>

                                <div class="col-md-6">
                                    <div class="id-card <?= $isFamily ? 'family-card' : 'solo-card' ?>">
                                        <!-- Header -->
                                        <div class="card-header">
                                            <div class="card-title">KANLAON EVACUATION PLAN</div>
                                            <div class="card-subtitle">BAKWIT CARD</div>
                                            <div class="registration-type">
                                                <?= $isFamily ? 'FAMILY' : 'INDIVIDUAL' ?>
                                            </div>
                                        </div>

                                        <!-- Main Information Section -->
                                        <div class="form-section">
                                            <table class="form-table">
                                                <tr>
                                                    <td>
                                                        <?= $isFamily ? 'HOUSEHOLD HEAD:' : 'HOUSEHOLD HEAD:' ?>
                                                        <span class="form-label-local">(<?= $isFamily ? '(PANGULO SANG PANIMALAY)' : '(PANGULO SANG PANIMALAY)' ?>)</span>
                                                    </td>
                                                    <td><?= htmlspecialchars(string: $reg['full_name']) ?></td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <?= $isFamily ? 'NO. OF HOUSEHOLD MEMBER:' : 'NO. OF HOUSEHOLD MEMBER:' ?>
                                                        <span class="form-label-local">(<?= $isFamily ? '(KADAMUON/KADAGHANON SA PANIMALAY)' : '(KADAMUON/KADAGHANON SA PANIMALAY)' ?>)</span>
                                                    </td>
                                                    <td>
                                                        <?= $isFamily ? $reg['member_count'] : '1' ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        ADDRESS:
                                                        <span class="form-label-local">(PULOY-AN/PUY-ANAN)</span>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        if (!empty($reg['solo_address'])) {
                                                            echo htmlspecialchars($reg['solo_address']);
                                                        } elseif (!empty($reg['family_address'])) {
                                                            echo htmlspecialchars($reg['family_address']);
                                                        } else {
                                                            echo 'N/A';
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        COLLECTION POINT/PICKUP POINT:
                                                        <span class="form-label-local">(TILIPUNAN PARA SA BAKWIT)</span>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        if (!empty($reg['solo_barangay'])) {
                                                            echo htmlspecialchars($reg['solo_barangay']);
                                                        } elseif (!empty($reg['family_barangay'])) {
                                                            echo htmlspecialchars($reg['family_barangay']);
                                                        } else {
                                                            echo 'N/A';
                                                        }
                                                        ?>
                                                    </td>
                                                <tr>
                                                    <td>
                                                        ASSIGNED EVACUATION CENTER:
                                                        <span class="form-label-local">(GINTALANA NGA EVACUATION CENTER)</span>
                                                    </td>
                                                    <td><?= htmlspecialchars($reg['evacuation_center']) ?></td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        PHONE NUMBER OF FAMILY LEADER:
                                                        <span class="form-label-local">(NUMERO SA SELPON SANG PANGULO SANG PANIMALAY)</span>
                                                    </td>
                                                    <td><?= htmlspecialchars($reg['contact_number']) ?></td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        PERSONS WITH SPECIAL NEEDS:
                                                        <span class="form-label-local">(MIYEMBRO NGA MAY ESPESYAL NGA PANGINAHANGLANON)</span>
                                                    </td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        STAYING INSIDE EVACUATION CENTER?:
                                                        <span class="form-label-local">(MUSULOD BA MO SA EVACUATION CENTER?)</span>
                                                    </td>
                                                    <td colspan="2">
                                                        <div class="checkbox-group" style="display: flex; gap: 20px; align-items: center;">
                                                            <!-- YES -->
                                                            <div class="checkbox-item" style="display: flex; align-items: center; gap: 5px;">
                                                                <div class="checkbox-box <?= !$stayInCenter ? 'checked' : '' ?>"></div>
                                                                <span>YES (Oo)</span>
                                                            </div>

                                                            <!-- NO -->
                                                            <div class="checkbox-item" style="display: flex; align-items: center; gap: 5px;">
                                                                <div class="checkbox-box <?= $stayInCenter ? 'checked' : '' ?>"></div>
                                                                <span>NO (Indi)</span>
                                                            </div>

                                                            <!-- Divider Line -->
                                                            <div style="border-left: 2px solid #000; height: 40px; margin: 0 10px;"></div>

                                                            <!-- QR Code Label & Image -->
                                                            <span>QR CODE:</span>
                                                            <div class="checkbox-item" style="display: flex; align-items: center; gap: 5px;">
                                                                <img src="<?= htmlspecialchars('../../../' . ltrim($reg['qr_code'])) ?>" alt="QR Code" style="width: 100px; height: 100px;">
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>

                                            <!-- Control Number Section -->
                                            <!-- <div class="control-number-section">
                                        <table class="control-number-table">
                                            <tr>
                                                <td>CONTROL NUMBER:</td>
                                                <td>
                                                    <div class="control-number-box">
                                                        <?= htmlspecialchars($reg['control_number']) ?> 
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </div> -->
                                        </div>
                                        <!-- Authority Section -->
                                        <div class="authority-section">
                                            <div class="logo-placeholder">
                                                Place LGU logo here
                                            </div>

                                            <div class="authority-list">
                                                <div class="authority-item">
                                                    <div class="authority-name">LDRRMO</div>
                                                    <div class="authority-line"></div>
                                                </div>
                                                <div class="authority-item">
                                                    <div class="authority-name">PUNONG BARANGAY</div>
                                                    <div class="authority-line"></div>
                                                </div>
                                                <div class="authority-item">
                                                    <div class="authority-name">PUROK LEADER</div>
                                                    <div class="authority-line"></div>
                                                </div>
                                                <div class="authority-item">
                                                    <div class="authority-name">LOCAL POLICE STATION</div>
                                                    <div class="authority-line"></div>
                                                </div>
                                                <div class="authority-item">
                                                    <div class="authority-name">OFFICE OF CIVIL DEFENSE NIR</div>
                                                    <div class="authority-line">
                                                        <span class="authority-phone">09956112342 / 09177040134</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Footer -->
                                        <div class="footer">
                                            <div class="footer-content">
                                                <div class="footer-text">REGIONAL TASK FORCE KANLAON</div>
                                                <div class="volcano-logo"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <style>
                            .container {
                                max-width: 900px;
                                margin: 0 auto;
                                background: white;
                                padding: 15px;
                                border-radius: 8px;
                                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                            }

                            .preview-header {
                                text-align: center;
                                margin-bottom: 15px;
                                padding: 10px;
                                background: #f8f9fa;
                                border-radius: 8px;
                            }

                            .preview-header h1 {
                                color: #333;
                                margin-bottom: 5px;
                                font-size: 1.2rem;
                            }

                            .preview-header p {
                                color: #666;
                                margin: 3px 0;
                                font-size: 0.9rem;
                            }

                            .id-card {
                                background: white;
                                color: black;
                                padding: 1rem;
                                border: 2px solid #000;
                                border-radius: 0;
                                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
                                position: relative;
                                margin-bottom: 20px;
                                font-family: Arial, sans-serif;
                                font-size: 12px;
                                line-height: 1.3;
                            }

                            .card-header {
                                text-align: center;
                                margin-bottom: 1rem;
                                border-bottom: 2px solid #000;
                                padding-bottom: 0.5rem;
                            }

                            .card-title {
                                font-size: 1.1rem;
                                font-weight: bold;
                                margin: 0;
                                color: #000;
                                text-transform: uppercase;
                            }

                            .card-subtitle {
                                font-size: 1rem;
                                font-weight: bold;
                                margin: 0.3rem 0 0 0;
                                color: #dc3545;
                                text-transform: uppercase;
                            }

                            .form-section {
                                margin-bottom: 1rem;
                                height: 400px;
                            }

                            .form-table {
                                width: 100%;
                                border-collapse: collapse;
                                border: 1px solid #000;
                            }

                            .form-table td {
                                padding: 0rem 0;
                                vertical-align: top;
                            }


                            .form-table td:first-child {
                                width: 25%;
                                font-size: 7px;
                                font-weight: bold;
                                text-transform: uppercase;
                                padding: 2px;
                                text-align: center;
                                vertical-align: middle;
                                border-right: 1px solid #000;
                                /* Added vertical line between columns */
                                border-bottom: 1px solid #000;
                                /* Added horizontal line */
                            }

                            .form-table td:last-child {
                                width: 75%;
                                /* Adjusted to total 100% with first-child */
                                font-size: 9px;
                                padding: 2px 2px 2px 5px;
                                /* Added padding */
                                border-bottom: 1px solid #000;
                                /* Added horizontal line */
                                vertical-align: middle;
                                /* Ensure consistent vertical alignment */
                            }

                            .form-label-local {
                                font-size: 0.4rem;
                                color: #666;
                                font-style: italic;
                                text-transform: none;
                                font-weight: normal;
                                display: block;
                                margin-top: 0.1rem;
                            }

                            .checkbox-group {
                                display: flex;
                                align-items: center;
                                gap: 0.5rem;
                            }

                            .checkbox-item {
                                display: flex;
                                align-items: center;
                                gap: 0.3rem;
                            }

                            .checkbox-box {
                                width: 16px;
                                height: 16px;
                                border: 1px solid #000;
                                display: inline-block;
                                position: relative;
                            }

                            .checkbox-box.checked {
                                background: #000;
                            }

                            .checkbox-box.checked::after {
                                content: '✓';
                                position: absolute;
                                top: -2px;
                                left: 1px;
                                font-weight: bold;
                                color: white;
                                font-size: 0.8rem;
                            }

                            .control-number-section {
                                margin-top: 0.5rem;
                                padding-top: 0.5rem;
                                border-top: 1px solid #ccc;
                            }

                            .control-number-table {
                                width: 100%;
                                border-collapse: collapse;
                            }

                            .control-number-table td {
                                padding: 0.3rem 0;
                                vertical-align: middle;
                            }

                            .control-number-table td:first-child {
                                width: 30%;
                                font-weight: bold;
                                text-transform: uppercase;
                                padding-right: 0.5rem;
                            }

                            .control-number-table td:last-child {
                                width: 70%;
                            }

                            .control-number-box {
                                border: 1px solid #000;
                                padding: 0.3rem 0.5rem;
                                text-align: center;
                                font-weight: bold;
                                background: #f8f9fa;
                                display: inline-block;
                                min-width: 150px;
                                font-size: 0.9rem;
                            }

                            .authority-section {
                                display: flex;
                                justify-content: space-between;
                                margin-top: -100px;
                                padding: 0.8rem;
                                background: #f4a460;
                                border: 1px solid #000;
                            }

                            .logo-placeholder {
                                width: 80px;
                                height: 80px;
                                border: 1px dashed #8b4513;
                                border-radius: 50%;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                background: #deb887;
                                font-size: 0.6rem;
                                color: #8b4513;
                                text-align: center;
                                flex-shrink: 0;
                            }

                            .authority-list {
                                flex-grow: 1;
                                margin-left: 1rem;
                            }

                            .authority-item {
                                display: flex;
                                justify-content: space-between;
                                align-items: center;
                                margin-bottom: 0.3rem;
                            }

                            .authority-name {
                                font-weight: bold;
                                text-transform: uppercase;
                                font-size: 0.7rem;
                            }

                            .authority-line {
                                border-bottom: 1px solid #000;
                                flex-grow: 1;
                                margin-left: 0.5rem;
                                min-width: 100px;
                            }

                            .authority-phone {
                                font-size: 0.6rem;
                                color: #666;
                            }

                            .footer {
                                margin-top: 0.5rem;
                                border-radius: 20px;
                                border-right: 60px solid white;
                                border-top: 20px solid white;
                                border-bottom: 20px solid white;
                                border-left: 30px solid white;
                                background-color: lightblue;
                                padding: 0;
                                /* Remove all internal padding */
                                box-sizing: border-box;
                                /* Include border in width calculation */
                            }

                            .footer-content {
                                display: flex;
                                justify-content: space-between;
                                align-items: center;
                                gap: 0;
                                /* Ensures no gap between items */
                            }

                            .footer-text {
                                font-weight: bold;
                                text-transform: uppercase;
                                font-size: 0.8rem;
                                text-align: center;
                                flex-grow: 1;
                                padding-right: 0;
                                /* Removed padding */
                                /* margin-right: -10px; Pulls logo closer by negative margin */
                            }

                            .volcano-logo {
                                width: 80px;
                                height: 80px;
                                border: 1px solid #000;
                                border-radius: 50%;
                                background: #ff8c00;
                                position: relative;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                margin-left: 0;
                                /* Removed margin */
                                transform: translateX(4px);
                                /* Fine-tune positioning */
                                /* margin-right:100px; */
                            }

                            /* Keep volcano logo details the same */
                            .volcano-logo::before {
                                content: '🌋';
                                position: absolute;
                                font-size: 3rem;
                            }

                            .volcano-logo::after {
                                content: 'TASK FORCE\A KANLAON';
                                position: absolute;
                                bottom: 2px;
                                left: 50%;
                                transform: translateX(-50%);
                                font-size: 0.3rem;
                                text-align: center;
                                line-height: 1;
                                white-space: pre-line;

                            }

                            .print-info {
                                background: #e9ecef;
                                padding: 10px;
                                border-radius: 8px;
                                margin-bottom: 15px;
                                font-size: 12px;
                            }

                            .print-info h3 {
                                margin-top: 0;
                                color: #333;
                                font-size: 1rem;
                            }

                            .print-info ul {
                                margin: 8px 0;
                                padding-left: 15px;
                            }

                            .print-info li {
                                margin: 3px 0;
                                font-size: 0.8rem;
                            }

                            @media print {
                                .footer {
                                    background-color: turquoise;
                                }
                            }
                        </style>
                    </div>
                </div>
                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button class="btn btn-success" id="printCardBtn"><i class="fas fa-print me-1"></i> Print</button>
                    <button class="btn btn-danger" id="downloadCardBtn"><i class="fas fa-file-pdf me-1"></i> Download PDF</button>
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
            <!-- </div> -->

            <!-- <div class="print-info">
                <h3>Print Information:</h3>
                <ul>
                    <li><strong>Page Size:</strong> A4 (210mm x 297mm)</li>
                    <li><strong>Cards Per Page:</strong> 1 card per page for optimal readability</li>
                    <li><strong>Print Orientation:</strong> Portrait</li>
                    <li><strong>Margins:</strong> 10mm on all sides</li>
                    <li><strong>Font:</strong> Arial, 9pt for print version</li>
                    <li><strong>Colors:</strong> Black text on white background, red subtitle, orange authority section</li>
                    <li><strong>Layout:</strong> Table format with labels in left column, data in right column</li>
                </ul>
            </div> -->
            <!-- </div>
        </div> -->
        </div>
    </div>
    </div>




    </div>

    </div>
    </div>
    <!-- JS for Print & Download -->

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <script>
        document.getElementById("printCardBtn").addEventListener("click", function() {
            // Collect each id-card element and wrap into a printable item
            const cardNodes = Array.from(document.querySelectorAll('#idCardContent .id-card'));
            if (cardNodes.length === 0) return alert('No ID cards found to print.');

            let itemsHtml = cardNodes.map(card => {
                // remove interactive attributes that shouldn't be printed
                const clone = card.cloneNode(true);
                // remove modal-specific classes on clone
                clone.querySelectorAll('[data-bs-toggle]').forEach(n => n.removeAttribute('data-bs-toggle'));
                // Inject small SVG logo at the start of header if missing
                let header = clone.querySelector('.card-header');
                // if (header) {
                //     const svgLogo = `<svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><circle cx="12" cy="12" r="10" fill="#0d6efd"/><text x="12" y="16" font-size="10" text-anchor="middle" fill="#fff" font-family="Arial, sans-serif">LGU</text></svg>`;
                //     header.innerHTML = svgLogo + header.innerHTML;
                // }
                return `<div class="id-card-print">${clone.innerHTML}</div>`;
            }).join('');

            let printWindow = window.open("", "", "width=900,height=650");

            // Build print HTML that forces two cards per row on A4
            let printContent = `
                <html>
                <head>
                    <title>Print ID Cards</title>
                    <style>
                                /* Page setup */
                                @page { size: A4 portrait; margin: 8mm; }
                                body { font-family: Arial, sans-serif; margin: 0; padding: 6mm; background: white; }
                                .print-container { display: grid; grid-template-columns: 1fr 1fr; gap: 6mm; width: calc(100% - 12mm); }

                                /* Compact printed card (preserve recent size choices) */
                                .id-card-print {
                                    background: white;
                                    border: 2px solid #000; /* match design border */
                                    padding: 2mm;            /* keep compact */
                                    font-size: 6px;         /* preserve compact font */
                                    line-height: 1.05;
                                    box-sizing: border-box;
                                }

                                /* Header / Title styling from provided design */
                                .id-card-print .card-header {
                                    text-align: center;
                                    margin-bottom: 4px;
                                    border-bottom: 2px solid #000;
                                    padding-bottom: 2px;
                                }
                                .id-card-print .card-title {
                                    font-size: 9px; /* slightly larger but still compact */
                                    font-weight: bold;
                                    margin: 0;
                                    color: #000;
                                    text-transform: uppercase;
                                }
                                .id-card-print .card-subtitle {
                                    font-size: 8px;
                                    font-weight: bold;
                                    margin: 2px 0 0 0;
                                    color: #dc3545;
                                    text-transform: uppercase;
                                }

                                /* Table styling from provided design but keep compact paddings */
                                .id-card-print .form-section { margin-bottom: 4px; }
                                .id-card-print .form-table { width: 100%; border-collapse: collapse; border: 1px solid #000; }
                                .id-card-print .form-table td { padding: 0 0; vertical-align: top; }
                                .id-card-print .form-table td:first-child {
                                    width: 28%;
                                    font-size: 5px;
                                    font-weight: bold;
                                    text-transform: uppercase;
                                    padding: 2px 3px;
                                    text-align: center;
                                    vertical-align: middle;
                                    border-right: 1px solid #000;
                                    border-bottom: 1px solid #000;
                                    background: #f2f6ff; /* subtle accent */
                                }
                                .id-card-print .form-table td:last-child {
                                    width: 72%;
                                    font-size: 6px;
                                    padding: 2px 4px;
                                    border-bottom: 1px solid #000;
                                    vertical-align: middle;
                                    background: #ffffff;
                                }
                                .id-card-print .form-label-local { font-size: 4px; color: #666; font-style: italic; }

                                /* Checkbox visuals kept small */
                                // .id-card-print .checkbox-box { width: 10px; height: 5px; border: 1px solid #000; }
                                // .id-card-print .checkbox-box.checked { background: #000; }

                                /* Force QR prominence (preserve recent larger size) */
                                // .id-card-print img[alt="QR Code"] { width: 50mm !important; height: 50mm !important; object-fit: contain !important; display:block; }
                                // .id-card-print img { max-width: 100% !important; height: auto !important; }

                                /* Authority / footer design from provided CSS */
                                .id-card-print .authority-section {
                                    display: flex;
                                    justify-content: space-between;
                                    margin-top: 6px;
                                    padding: 6px;
                                    background: #f4a460;
                                    border: 1px solid #000;
                                }
                                .id-card-print .logo-placeholder {
                                    width: 50px; height: 50px; border: 1px dashed #8b4513; border-radius: 50%; display:flex; align-items:center; justify-content:center; background:#deb887; color:#8b4513; font-size:10px;
                                }
                                .id-card-print .authority-name { font-weight: bold; text-transform: uppercase; font-size: 7px; }

                                .id-card-print .footer { margin-top: 4px; background-color: lightblue; padding: 0; box-sizing: border-box; }
                                .id-card-print .footer-text { font-weight: bold; text-transform: uppercase; font-size: 8px; text-align: center; }

                                /* Subtitle emphasis */
                                .id-card-print .card-subtitle { color: #dc3545; font-weight: 700; }

                                @media print {
                                    .print-container { page-break-inside: avoid; }
                                    .id-card-print { page-break-inside: avoid; }
                                }
                            </style>
                </head>
                <body>
                    <div class="print-container">
                        ${itemsHtml}
                    </div>
                </body>
                </html>
            `;

            printWindow.document.write(printContent);
            printWindow.document.close();

            printWindow.onload = function() {
                printWindow.focus();
                printWindow.print();
            };
        });

        document.getElementById("downloadCardBtn").addEventListener("click", function() {
            const {
                jsPDF
            } = window.jspdf; // Get jsPDF from UMD
            html2canvas(document.getElementById("idCardContent")).then(canvas => {
                const imgData = canvas.toDataURL("image/png");
                const pdf = new jsPDF({
                    orientation: "portrait",
                    unit: "mm",
                    format: "a4"
                });

                const imgProps = pdf.getImageProperties(imgData);
                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

                pdf.addImage(imgData, "PNG", 0, 0, pdfWidth, pdfHeight);
                pdf.save("ID_Card.pdf");
            });
        });
    </script>


</body>

</html>