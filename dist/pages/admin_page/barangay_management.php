<?php
include '../../../database/session.php';
include '../layout/head_links.php';

// Load boundary data (keep fencing intact)
$barangayBoundaries = [];
$boundaryFile = dirname(__DIR__, 3) . '/address_json/barangay_boundaries.json';
if (file_exists($boundaryFile)) {
	$boundaryContent = @file_get_contents($boundaryFile);
	if ($boundaryContent !== false) {
		$decoded = json_decode($boundaryContent, true);
		if (is_array($decoded)) {
			$barangayBoundaries = $decoded;
		}
	}
}

$query = "SELECT b.*, IFNULL(b.evacuation_needed, 0) AS evacuation_needed,
	(
		SELECT COUNT(*)
		FROM pre_reg_table pr
		LEFT JOIN solo_address_table sat ON pr.solo_address_id = sat.solo_address_id
		LEFT JOIN family_table ft ON pr.family_id = ft.family_id
		WHERE (sat.barangay_id = b.barangay_id) OR (ft.barangay_id = b.barangay_id)
	) AS pre_reg_count
	FROM barangay_manegement_table b";

$result = mysqli_query($conn, $query);

if (!$result) {
	die("Query failed: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<title>Location Management</title>
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
	<div class="app-wrapper">
		<?php include '../layout/header.php';
		include '../layout/sidebar.php';
		include '../alert/warning.php';
		?>

		<main class="app-main">
			<div class="app-content-header">
				<div class="container-fluid">
					<div class="row">
						<div class="col-sm-6 d-flex align-items-center gap-2">
							<i class="fas fa-city fs-2 text-primary"></i>
							<h3 class="mb-0">Barangay Management</h3>
						</div>
						<div class="col-sm-6">
							<ol class="breadcrumb float-sm-end">
								<li class="breadcrumb-item"><a href="#">Home</a></li>
								<li class="breadcrumb-item active" aria-current="page">Location Records</li>
							</ol>
						</div>
					</div>
				</div>
			</div>

			<div class="content">
				<div class="row">
					<div class="col-md-12">
						<div class="card">
							<div class="card-header d-flex align-items-center gap-2">
								<input type="text" id="searchBox" class="form-control me-2" placeholder="Search..." style="max-width: 300px;">
								<button type="button" class="btn btn-sm btn-outline-primary" id="showProneBelow">Show Prone Areas Below</button>
								<button type="button" class="btn btn-sm btn-outline-success ms-auto" id="markAllEvac">Mark all evacuation</button>
								<button type="button" class="btn btn-sm btn-outline-secondary" id="clearAllEvac">Clear all</button>
								<button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addLocationModal">
									<i class="fas fa-plus-circle"></i> Add Location
								</button>
							</div>

							<div class="card-body">
								<div class="table-responsive">
									<table id="locationTable" class="searchable-table table table-sm table-hover w-100">
										<thead class="table-success sticky-header">
											<tr>
												<th> No.</th>
												<th><i class="bi bi-geo-alt-fill"></i> Location</th>
												<th><i class="bi bi-person-badge-fill"></i> Barangay Captain</th>
												<th><i class="bi bi-people-fill"></i> Total Population</th>
												<th><i class="bi bi-list-check"></i> Pre-registered</th>
												<th><i class="bi bi-exclamation-triangle"></i> Evacuation Needed</th>
												<th class="text-center" style="text-align: center; vertical-align: middle;">
													<i class="bi bi-gear-fill"></i> Actions
												</th>
											</tr>
										</thead>
										<tbody>
											<?php
											$counter = 1;
											if (mysqli_num_rows($result) > 0) {
												while ($barangay = mysqli_fetch_assoc($result)): ?>
													<tr>
														<td class="cell-number"><?php echo $counter++; ?>.</td>
														<td class="cell-location "><?php echo htmlspecialchars($barangay['barangay_name']); ?></td>
														<td class="cell-address justify-content-center text-centerz"><?php echo htmlspecialchars($barangay['barangay_captain_name']); ?></td>
														<td class="cell-population justify-content-center text-centerz"><?php echo number_format((int)$barangay['total_population']); ?></td>
														<td class="cell-prereg text-center"><?php echo isset($barangay['pre_reg_count']) ? number_format((int)$barangay['pre_reg_count'], 0) : 0; ?></td>
														<td>
															<div class="form-check form-switch">
																<input class="form-check-input evac-toggle" type="checkbox" role="switch" data-id="<?php echo (int)$barangay['barangay_id']; ?>" <?php echo ((int)$barangay['evacuation_needed'] === 1) ? 'checked' : ''; ?>>
															</div>
														</td>
														<td class="justify-content-center text-center">
															<a href="#" class="btn btn-outline-success btn-sm edit-btn shadow"
																data-id="<?php echo (int)$barangay['barangay_id']; ?>"
																data-name="<?php echo htmlspecialchars($barangay['barangay_name']); ?>"
																data-captain="<?php echo htmlspecialchars($barangay['barangay_captain_name']); ?>"
																data-signature="<?php echo htmlspecialchars($barangay['signature_brgy_captain']); ?>"
																data-population="<?php echo (int)$barangay['total_population']; ?>"
																data-latitude="<?php echo htmlspecialchars($barangay['latitude']); ?>"
																data-longitude="<?php echo htmlspecialchars($barangay['longitude']); ?>"
																data-bs-toggle="modal" data-bs-target="#editLocationModal">
																<i class="fas fa-edit"></i> Edit
															</a>

															<a href="#" class="btn btn-outline-danger btn-sm delete-btn shadow"
																data-id="<?php echo (int)$barangay['barangay_id']; ?>"
																data-bs-toggle="modal" data-bs-target="#deleteLocationModal">
																<i class="fas fa-trash"></i> Delete
															</a>

															<a href="#"
																class="btn btn-outline-primary btn-sm shadow view-barangay-btn"
																data-bs-toggle="modal"
																data-bs-target="#viewBarangayModal"
																data-name1="<?php echo htmlspecialchars($barangay['barangay_name']); ?>"
																data-captain="<?php echo htmlspecialchars($barangay['barangay_captain_name']); ?>"
																data-signature="<?php echo htmlspecialchars($barangay['signature_brgy_captain']); ?>"
																data-population="<?php echo (int)$barangay['total_population']; ?>"
																data-latitude="<?php echo htmlspecialchars($barangay['latitude']); ?>"
																data-longitude="<?php echo htmlspecialchars($barangay['longitude']); ?>">
																<i class="fas fa-eye"></i> View
															</a>
														</td>
													</tr>
											<?php endwhile;
											} else {
												echo "<tr><td colspan='7' class='text-center'>No location records found.</td></tr>";
											}
											?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</main>

		<?php include '../layout/footer.php'; ?>
		
		<script>
		// Pass boundary data to JavaScript
		window.barangayBoundaries = <?php echo json_encode($barangayBoundaries, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
		console.log('Loaded boundary data:', window.barangayBoundaries);
		</script>
	</div>
	
	<?php include '../modal/evac_location/barangay_management_modal.php'; ?>

	<script>
		$(document).ready(function() {
			$("#searchBox").on("keyup", function() {
				var searchTerm = $(this).val().toLowerCase().trim();
				$("#locationTable tbody tr").each(function() {
					var rowText = $(this).text().toLowerCase();
					if (rowText.includes(searchTerm)) { $(this).fadeIn(); } else { $(this).fadeOut(); }
				});
			});
		});
	</script>
	<script>
	// Single toggle
	document.addEventListener('DOMContentLoaded', function(){
		document.querySelectorAll('.evac-toggle').forEach(function(cb){
			cb.addEventListener('change', async function(){
				const id = this.getAttribute('data-id');
				const prev = !this.checked; // remember previous state to revert if needed
				const needed = this.checked ? 1 : 0;
				this.disabled = true;
				try{
					const resp = await fetch('../action/brgy_management_action/toggle_evacuation_db.php', {
						method: 'POST',
						headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
						body: 'barangay_id=' + encodeURIComponent(id) + '&needed=' + needed
					});
					const data = await resp.json().catch(()=>({ ok:false, error:'invalid_json' }));
					if (!resp.ok || !data.ok) {
						console.warn('Save failed', data);
						this.checked = prev; // revert
						alert('Failed to save evacuation flag.');
					}
				} catch(e){
					console.warn('Request error', e);
					this.checked = prev; // revert
					alert('Network error while saving.');
				} finally {
					this.disabled = false;
				}
			});
		});
		// Bulk buttons
		document.getElementById('markAllEvac').addEventListener('click', async function(){
			try{
				const resp = await fetch('../action/brgy_management_action/set_evac_all.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }, body: 'needed=1' });
				const data = await resp.json().catch(()=>({ ok:false }));
				if (resp.ok && data.ok) {
					document.querySelectorAll('.evac-toggle').forEach(cb=>{ cb.checked = true; });
				} else {
					alert('Failed to mark all.');
				}
			}catch(e){ console.warn(e); alert('Network error while marking all.'); }
		});
		document.getElementById('clearAllEvac').addEventListener('click', async function(){
			try{
				const resp = await fetch('../action/brgy_management_action/set_evac_all.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }, body: 'needed=0' });
				const data = await resp.json().catch(()=>({ ok:false }));
				if (resp.ok && data.ok) {
					document.querySelectorAll('.evac-toggle').forEach(cb=>{ cb.checked = false; });
				} else {
					alert('Failed to clear all.');
				}
			}catch(e){ console.warn(e); alert('Network error while clearing all.'); }
		});

		// Prone Areas Filter
		document.getElementById('showProneBelow').addEventListener('click', async function(){
			const btn = this;
			const originalText = btn.textContent;
			
			if (btn.classList.contains('active')) {
				// Reset filter - show all barangays
				btn.classList.remove('active');
				btn.textContent = 'Show Prone Areas Below';
				$("#locationTable tbody tr").show();
				return;
			}
			
			btn.textContent = 'Loading...';
			
			try {
				// Fetch flood prone boundaries
				const response = await fetch('../../../address_json/barangay_boundaries.json', { cache: 'no-cache' });
				const proneData = await response.json();
				const proneBarangays = Object.keys(proneData || {});
				
				if (proneBarangays.length === 0) {
					alert('No barangays with flood-prone boundaries found.');
					btn.textContent = originalText;
					return;
				}
				
				// Filter table rows
				$("#locationTable tbody tr").each(function() {
					const barangayName = $(this).find('.cell-location').text().trim();
					if (proneBarangays.includes(barangayName)) {
						$(this).show();
					} else {
						$(this).hide();
					}
				});
				
				btn.classList.add('active');
				btn.textContent = 'Show All Barangays';
				
			} catch (error) {
				console.error('Error loading prone areas:', error);
				alert('Error loading flood-prone areas data.');
				btn.textContent = originalText;
			}
		});
	});
	</script>
	<style>
		.table-responsive { max-height: 400px; overflow-y: auto; }
		#locationTable thead th { position: sticky; top: 0; z-index: 10; background: #d1e7dd; }
	</style>

</body>

</html>