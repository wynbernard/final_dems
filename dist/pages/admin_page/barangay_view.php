<?php
include '../../../database/session.php';
include '../layout/head_links.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    $_SESSION['error'] = 'Invalid barangay id';
    header('Location: barangay_management.php');
    exit;
}

$stmt = $conn->prepare("SELECT * FROM barangay_manegement_table WHERE barangay_id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$barangay = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$barangay) {
    $_SESSION['error'] = 'Barangay not found';
    header('Location: barangay_management.php');
    exit;
}

// Load puroks for this barangay
$puroks = [];
$stmt = $conn->prepare("SELECT purok_id, purok_name, purok_leader, pickup_point_name FROM purok_table WHERE barangay_id = ? ORDER BY purok_name ASC");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $puroks[] = $row;
}
$stmt->close();

// Load boundaries for map
$barangayBoundaries = [];
$boundaryFile = dirname(__DIR__, 3) . '/address_json/barangay_boundaries.json';
if (file_exists($boundaryFile)) {
    $data = @file_get_contents($boundaryFile);
    if ($data !== false) {
        $decoded = json_decode($data, true);
        if (is_array($decoded)) $barangayBoundaries = $decoded;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Barangay View</title>
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
<div class="app-wrapper">
    <?php include '../layout/header.php'; include '../layout/sidebar.php'; include '../alert/warning.php'; ?>

    <main class="app-main">
        <div class="app-content-header">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6 d-flex align-items-center gap-2">
                        <a href="barangay_management.php" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-eye fs-2 text-primary"></i>
                            <h3 class="mb-0">View Barangay <?php echo htmlspecialchars($barangay['barangay_name']); ?></h3>
                        </div>
                    </div>
					<div class="col-sm-6 d-flex justify-content-sm-end align-items-center gap-2">
                        <ol class="breadcrumb float-sm-center">
                            <li class="breadcrumb-item"><a href="barangay_management.php">Purok Management</a></li>
                            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($barangay['barangay_name']); ?></li>
                        </ol>
						
                    </div>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-gradient-primary text-white">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-building me-2" style="color: #000;"></i>
                                <h5 class="mb-0" style="color: #000;">Barangay Details</h5>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-2">
                                <div class="col-12">
                                    <div class="d-flex align-items-center p-2 bg-light rounded">
                                        <div class="flex-shrink-0">
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                                <i class="fas fa-map-marker-alt fa-sm"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-2">
                                            <small class="text-muted">Barangay Name</small>
                                            <div class="fw-bold text-primary"><?php echo htmlspecialchars($barangay['barangay_name']); ?></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <div class="d-flex align-items-center p-2 bg-light rounded">
                                        <div class="flex-shrink-0">
                                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                                <i class="fas fa-user-tie fa-sm"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-2">
                                            <small class="text-muted">Captain</small>
                                            <div class="fw-bold"><?php echo htmlspecialchars($barangay['barangay_captain_name']); ?></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <div class="d-flex align-items-center p-2 bg-light rounded">
                                        <div class="flex-shrink-0">
                                            <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                                <i class="fas fa-users fa-sm"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-2">
                                            <small class="text-muted">Population</small>
                                            <div class="fw-bold text-info"><?php echo number_format((int)$barangay['total_population']); ?></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="p-2 bg-light rounded">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0">
                                                <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                                    <i class="fas fa-signature fa-sm"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-2">
                                                <small class="text-muted">Signature</small>
                                            </div>
                                        </div>
                                        <div class="text-center">
                                            <?php if (!empty($barangay['signature_brgy_captain'])): ?>
                                                <div class="border rounded p-2 bg-white">
                                                    <img src="../../../uploads/<?php echo htmlspecialchars($barangay['signature_brgy_captain']); ?>" 
                                                         alt="Signature" 
                                                         class="img-fluid" 
                                                         style="max-height: 60px; max-width: 120px;">
                                                </div>
                                            <?php else: ?>
                                                <div class="border rounded p-2 bg-white text-muted">
                                                    <i class="fas fa-image fa-lg mb-1"></i>
                                                    <div class="small">No signature</div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-gradient-info text-white">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-map-marked-alt me-2" style="color: #000;"></i>
                                    <h5 class="mb-0" style="color: #000;">Barangay Location</h5>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-light text-dark me-2">
                                        <i class="fas fa-crosshairs me-1"></i>
                                        Interactive Map
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0 position-relative">
                            <div id="viewMap" style="height: 400px; border-radius: 0 0 0.375rem 0.375rem;"></div>
                            <div class="position-absolute top-0 end-0 m-3">
                                <div class="bg-white rounded shadow-sm p-2">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle text-info me-1"></i>
                                        Click and drag to explore
                                    </small>
                                </div>
                            </div>
                            <div class="position-absolute bottom-0 start-0 m-3">
                                <div class="bg-white rounded shadow-sm p-2">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary rounded-circle me-2" style="width: 12px; height: 12px;"></div>
                                        <small class="text-muted">Barangay Center</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row g-3 mt-3">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-gradient-success text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-map-marked-alt me-2" style="color: #000;"></i>
                                    <h5 class="mb-0" style="color: #000;">Puroks in <?php echo htmlspecialchars($barangay['barangay_name']); ?></h5>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-light text-dark me-2">
                                        <i class="fas fa-list me-1"></i>
                                        <?php echo count($puroks); ?> Puroks
                                    </span>
                                    <button class="btn btn-success btn-sm" onclick="openAddPurokModal()">
                                        <i class="fas fa-plus me-1"></i> Add Purok
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <?php if (!empty($puroks)): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="border-0 py-3 px-4">
                                                    <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                                    Purok Name
                                                </th>
                                                <th class="border-0 py-3 px-4">
                                                    <i class="fas fa-user-tie text-success me-2"></i>
                                                    Leader
                                                </th>
                                                <th class="border-0 py-3 px-4">
                                                    <i class="fas fa-truck text-info me-2"></i>
                                                    Pickup Point
                                                </th>
                                                <th class="border-0 py-3 px-4 text-center">
                                                    <i class="fas fa-cogs text-secondary me-2"></i>
                                                    Actions
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($puroks as $index => $purok): ?>
                                                <tr class="border-bottom">
                                                    <td class="py-3 px-4">
                                                        <div class="d-flex align-items-center">
                                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 35px; height: 35px;">
                                                                <span class="fw-bold small"><?php echo $index + 1; ?></span>
                                                            </div>
                                                            <div>
                                                                <h6 class="mb-0 text-primary"><?php echo htmlspecialchars($purok['purok_name']); ?></h6>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="py-3 px-4">
                                                        <?php if (!empty($purok['purok_leader'])): ?>
                                                            <div class="d-flex align-items-center">
                                                                <i class="fas fa-user-circle text-success me-2"></i>
                                                                <span class="fw-medium"><?php echo htmlspecialchars($purok['purok_leader']); ?></span>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="d-flex align-items-center">
                                                                <i class="fas fa-user-slash text-muted me-2"></i>
                                                                <em class="text-muted">Not specified</em>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="py-3 px-4">
                                                        <?php if (!empty($purok['pickup_point_name'])): ?>
                                                            <div class="d-flex align-items-center">
                                                                <i class="fas fa-map-pin text-info me-2"></i>
                                                                <span class="fw-medium"><?php echo htmlspecialchars($purok['pickup_point_name']); ?></span>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="d-flex align-items-center">
                                                                <i class="fas fa-map-marker-alt text-muted me-2"></i>
                                                                <em class="text-muted">Not specified</em>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="py-3 px-4 text-center">
                                                        <div class="d-flex gap-2 justify-content-center">
                                                            <button class="btn btn-outline-success btn-sm" onclick="openEditPurokModal(<?php echo $purok['purok_id']; ?>, '<?php echo addslashes($purok['purok_name']); ?>', '<?php echo addslashes($purok['purok_leader'] ?? ''); ?>', '<?php echo addslashes($purok['pickup_point_name'] ?? ''); ?>')" title="Edit Purok">
                                                                <i class="fas fa-edit"></i> Edit
                                                            </button>
                                                            <button class="btn btn-outline-danger btn-sm" onclick="openDeletePurokModal(<?php echo $purok['purok_id']; ?>, '<?php echo addslashes($purok['purok_name']); ?>')" title="Delete Purok">
                                                                <i class="fas fa-trash"></i> Delete
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <div class="mb-3">
                                        <i class="fas fa-map-marked-alt fa-3x text-muted"></i>
                                    </div>
                                    <h5 class="text-muted mb-2">No Puroks Found</h5>
                                    <p class="text-muted mb-4">This barangay doesn't have any puroks yet.</p>
                                    <button class="btn btn-success" onclick="openAddPurokModal()">
                                        <i class="fas fa-plus me-2"></i>Add First Purok
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../layout/footer.php'; ?>
</div>

<!-- Purok Add Modal -->
<div class="modal fade" id="addPurokModal" tabindex="-1" aria-labelledby="addPurokModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="addPurokForm" method="POST" action="../action/brgy_management_action/add_purok.php">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPurokModalLabel">Add Purok</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php require_once '../../../database/csrf.php'; echo csrf_token_field(); ?>
                    <input type="hidden" id="addPurokBarangayId" name="barangay_id" value="<?php echo $id; ?>">
                    <div class="mb-3">
                        <label for="addPurokName" class="form-label">Purok Name</label>
                        <input type="text" id="addPurokName" name="purok_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="addPurokLeader" class="form-label">Purok Leader</label>
                        <input type="text" id="addPurokLeader" name="purok_leader" class="form-control">
                    </div>
                    <div>
                        <label for="addPurokPickUpPoint" class="form-label">Pick Up Point</label>
                        <input type="text" id="addPurokPickUpPoint" name="pickup_point_name" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Add</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Purok Edit Modal -->
<div class="modal fade" id="editPurokModal" tabindex="-1" aria-labelledby="editPurokModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="editPurokForm" method="POST" action="../action/brgy_management_action/edit_purok.php">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPurokModalLabel">Edit Purok</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="editPurokId" name="purok_id">
                    <div class="mb-3">
                        <label for="editPurokName" class="form-label">Purok Name</label>
                        <input type="text" id="editPurokName" name="purok_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="editPurokLeader" class="form-label">Purok Leader</label>
                        <input type="text" id="editPurokLeader" name="purok_leader" class="form-control">
                    </div>
                    <div>
                        <label for="editPurokPickUpPoint" class="form-label">Pick Up Point</label>
                        <input type="text" id="editPurokPickUpPoint" name="pickup_point_name" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Purok Delete Confirmation Modal -->
<div class="modal fade" id="deletePurokModal" tabindex="-1" aria-labelledby="deletePurokModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="deletePurokForm" method="POST" action="../action/brgy_management_action/delete_purok.php">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deletePurokModalLabel">Delete Purok</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="deletePurokId" name="purok_id">
                    <p>Are you sure you want to delete this purok?</p>
                    <p class="fw-bold" id="deletePurokName"></p>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">Delete</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<style>
    /* Custom map styles */
    #viewMap {
        border-radius: 0 0 0.375rem 0.375rem !important;
        box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
    }
    
    /* Custom marker styles */
    .custom-marker {
        background: transparent !important;
        border: none !important;
    }
    
    /* Custom popup styles */
    .custom-popup .leaflet-popup-content-wrapper {
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        border: 1px solid #e9ecef;
    }
    
    .custom-popup .leaflet-popup-content {
        margin: 12px 16px;
        line-height: 1.4;
    }
    
    .custom-popup .leaflet-popup-tip {
        background: white;
        border: 1px solid #e9ecef;
    }
    
    /* Map controls styling */
    .leaflet-control-zoom a {
        background: white;
        border: 1px solid #dee2e6;
        color: #495057;
        font-weight: bold;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .leaflet-control-zoom a:hover {
        background: #f8f9fa;
        color: #007bff;
    }
    
    /* Attribution styling */
    .leaflet-control-attribution {
        background: rgba(255,255,255,0.9) !important;
        border-radius: 4px;
        font-size: 11px;
    }
    
    /* Map loading animation */
    .leaflet-container {
        background: #f8f9fa;
    }
    
    /* Custom info overlays */
    .map-info-overlay {
        background: rgba(255,255,255,0.95);
        backdrop-filter: blur(5px);
        border: 1px solid rgba(0,0,0,0.1);
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const lat = parseFloat('<?php echo $barangay['latitude'] !== null ? $barangay['latitude'] : '10.5351'; ?>') || 10.5351;
        const lng = parseFloat('<?php echo $barangay['longitude'] !== null ? $barangay['longitude'] : '122.8357'; ?>') || 122.8357;
        const name = '<?php echo addslashes($barangay['barangay_name']); ?>';
        const boundaries = <?php echo json_encode($barangayBoundaries, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

        const map = L.map('viewMap', {
            zoomControl: true,
            scrollWheelZoom: true,
            doubleClickZoom: true,
            boxZoom: true,
            dragging: true,
            keyboard: true,
            touchZoom: true
        }).setView([lat, lng], 15);

        // Enhanced tile layer with better styling
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19,
            subdomains: ['a', 'b', 'c']
        }).addTo(map);

        // Custom marker with better styling
        const customIcon = L.divIcon({
            className: 'custom-marker',
            html: `
                <div style="
                    background: linear-gradient(135deg, #007bff, #0056b3);
                    width: 40px;
                    height: 40px;
                    border-radius: 50% 50% 50% 0;
                    transform: rotate(-45deg);
                    border: 3px solid white;
                    box-shadow: 0 4px 8px rgba(0,0,0,0.3);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                ">
                    <i class="fas fa-map-marker-alt" style="
                        color: white;
                        font-size: 16px;
                        transform: rotate(45deg);
                    "></i>
                </div>
            `,
            iconSize: [40, 40],
            iconAnchor: [20, 40],
            popupAnchor: [0, -40]
        });

        const marker = L.marker([lat, lng], { icon: customIcon }).addTo(map);
        
        // Enhanced popup
        marker.bindPopup(`
            <div class="text-center">
                <h6 class="mb-2 text-primary">
                    <i class="fas fa-building me-1"></i>
                    ${name}
                </h6>
                <p class="mb-1 small text-muted">
                    <i class="fas fa-map-pin me-1"></i>
                    Coordinates: ${lat.toFixed(6)}, ${lng.toFixed(6)}
                </p>
                <p class="mb-0 small text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Barangay Center Location
                </p>
            </div>
        `, {
            className: 'custom-popup',
            maxWidth: 250
        });

        if (boundaries && boundaries[name] && Array.isArray(boundaries[name].coordinates)) {
            const coords = boundaries[name].coordinates.map(c => [c.lat, c.lng]);
            let layer;
            if (boundaries[name].type === 'polygon' && coords.length >= 3) {
                layer = L.polygon(coords, {
                    color: '#007bff',
                    fillColor: '#007bff',
                    fillOpacity: 0.15,
                    weight: 3,
                    opacity: 0.8,
                    dashArray: '5, 5'
                });
            } else {
                layer = L.polyline(coords, {
                    color: '#28a745',
                    weight: 4,
                    opacity: 0.9,
                    dashArray: '10, 5'
                });
            }
            layer.addTo(map);
            
            // Add boundary popup
            layer.bindPopup(`
                <div class="text-center">
                    <h6 class="mb-2 text-success">
                        <i class="fas fa-map me-1"></i>
                        ${name} Boundary
                    </h6>
                    <p class="mb-0 small text-muted">
                        <i class="fas fa-expand-arrows-alt me-1"></i>
                        Administrative Area
                    </p>
                </div>
            `);
            
            const group = L.featureGroup([marker, layer]);
            map.fitBounds(group.getBounds(), { padding: [30, 30] });
        }

// Load puroks for this barangay - now using server-side rendering
// loadPuroks(); // Removed for faster loading
    });

    // loadPuroks function removed - now using server-side rendering for faster loading

    // Modal functions
    function openAddPurokModal() {
        const modal = new bootstrap.Modal(document.getElementById('addPurokModal'));
        modal.show();
    }

    function openEditPurokModal(purokId, purokName, purokLeader, pickupPoint) {
        document.getElementById('editPurokId').value = purokId;
        document.getElementById('editPurokName').value = purokName;
        document.getElementById('editPurokLeader').value = purokLeader;
        document.getElementById('editPurokPickUpPoint').value = pickupPoint;
        
        const modal = new bootstrap.Modal(document.getElementById('editPurokModal'));
        modal.show();
    }

    function openDeletePurokModal(purokId, purokName) {
        document.getElementById('deletePurokId').value = purokId;
        document.getElementById('deletePurokName').textContent = purokName;
        
        const modal = new bootstrap.Modal(document.getElementById('deletePurokModal'));
        modal.show();
    }


</script>
</body>
</html>

