<?php
include '../../../database/session.php';
include '../layout/head_links.php';
 
// Role-based log filtering
$role = isset($_SESSION['role']) ? (trim($_SESSION['role'])) : '';
$assigned_loc = isset($_SESSION['evac_loc_id']) ? $_SESSION['evac_loc_id'] : '';

// Use prepared statements to prevent SQL injection
if ($role === 'Staff') {
	// Staff: show only logs for assigned location
	$query = "SELECT 
				evac_reg_table.*,
				pre_reg_table.f_name,
				pre_reg_table.l_name,
				logs_table.date_time,
				room_table.room_name,
				evac_loc_table.name,
				logs_table.status
			FROM logs_table
			INNER JOIN evac_reg_table 
			ON logs_table.evac_reg_id = evac_reg_table.evac_reg_id
			INNER JOIN pre_reg_table 
			ON evac_reg_table.pre_reg_id = pre_reg_table.pre_reg_id
			LEFT JOIN room_table 
			ON evac_reg_table.room_id = room_table.room_id
			LEFT JOIN evac_loc_table
			ON evac_reg_table.evac_loc_id = evac_loc_table.evac_loc_id
			WHERE evac_reg_table.evac_loc_id = ?
			ORDER BY logs_table.date_time DESC";
	
	$stmt = $conn->prepare($query);
	if ($stmt) {
		$stmt->bind_param("i", $assigned_loc);
		$stmt->execute();
		$result = $stmt->get_result();
	} else {
		error_log("Failed to prepare query: " . $conn->error);
		$result = false;
	}
} else {
	// Admin: show all logs
	$query = "SELECT 
				evac_reg_table.*,
				pre_reg_table.f_name,
				pre_reg_table.l_name,
				logs_table.date_time,
				room_table.room_name,
				evac_loc_table.name,
				logs_table.status
			FROM logs_table
			INNER JOIN evac_reg_table 
			ON logs_table.evac_reg_id = evac_reg_table.evac_reg_id
			INNER JOIN pre_reg_table 
			ON evac_reg_table.pre_reg_id = pre_reg_table.pre_reg_id
			LEFT JOIN room_table 
			ON evac_reg_table.room_id = room_table.room_id
			LEFT JOIN evac_loc_table
			ON evac_reg_table.evac_loc_id = evac_loc_table.evac_loc_id
			ORDER BY logs_table.date_time DESC";
	
	$result = $conn->query($query);
}

if (!$result) {
	error_log("Query failed: " . $conn->error);
	die("An error occurred. Please contact administrator.");
}

// Debug: Log the query and role for troubleshooting
error_log("Logs Query for role '$role': " . $query);
if ($role === 'Staff') {
	error_log("Staff assigned location ID: " . $assigned_loc);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<title>Log Management</title>
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
	<!-- QR Code Scanner Modal -->
	<!-- QR Scanner Modal -->
	<div class="modal fade" id="scanQRModal" tabindex="-1" aria-labelledby="scanQRModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header bg-primary text-white">
					<h5 class="modal-title" id="scanQRModalLabel"><i class="bi bi-qr-code-scan"></i> QR Code Scanner</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="stopScanner()"></button>
				</div>

				<div class="modal-body text-center">
					<select id="cameraSelect" class="form-select mb-2" style="max-width: 300px; margin: auto;"></select>
					<div id="qr-reader" style="width: 100%; max-width: 500px; margin: auto;"></div>

					<div class="mt-3">
						<button id="startScannerBtn" class="btn btn-success">Start Scanner</button>
						<button id="stopScannerBtn" class="btn btn-secondary" disabled>Stop Scanner</button>
					</div>

					<div id="family-info" class="alert alert-info d-none mt-3">
						<strong>Family Name:</strong> <span id="family-name"></span>
					</div>
				</div>
			</div>
		</div>
	</div>

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
						<i class="bi bi-journal-text fs-2 text-primary"></i>
						<div>
							<h3 class="mb-0">Logs Record</h3>
							<?php if ($role === 'Staff' && !empty($assigned_loc)): 
								// Get location name for display
								$locQuery = "SELECT name FROM evac_loc_table WHERE evac_loc_id = ?";
								$locStmt = $conn->prepare($locQuery);
								$locStmt->bind_param("i", $assigned_loc);
								$locStmt->execute();
								$locResult = $locStmt->get_result();
								$locationName = $locResult->fetch_assoc()['name'] ?? 'Unknown Location';
							?>
								<small class="text-muted">📍 Viewing logs for: <strong><?= htmlspecialchars($locationName) ?></strong></small>
							<?php endif; ?>
						</div>
					</div>

						<div class="col-sm-6">
							<ol class="breadcrumb float-sm-end">
								<li class="breadcrumb-item"><a href="#">Home</a></li>
								<li class="breadcrumb-item active" aria-current="page">Log Records</li>
							</ol>
						</div>
					</div>
				</div>
			</div>

			<!-- Search Box -->
			<div class="container mt-0"></div>

			<div class="content">
				<div class="row">
					<div class="col-md-12">
						<div class="card">
							<!-- Buttons + Mode Field -->
							<div class="card-header d-flex align-items-center">
								<input type="text" id="searchBox" class="form-control me-2" placeholder="Search..." style="max-width: 300px;">

								<!-- New Date Picker -->
								<input type="date" id="dateFilter" class="form-control me-2" style="max-width: 200px;">

								
								<div class="d-flex align-items-center">
									<label for="rowsPerPageSelect" class="form-label me-2 mb-0">Show:</label>
									<select id="rowsPerPageSelect" class="form-select" style="max-width: 100px;">
										<option value="5">5</option>
										<option value="10" selected>10</option>
										<option value="25">25</option>
										<option value="50">50</option>
										<option value="100">100</option>
									</select>
									<span class="ms-2 text-muted">rows</span>
								</div>
								<button type="button" class="btn btn-primary btn-sm ms-2" id="logBtn" data-bs-toggle="modal" data-bs-target="#scanQRModal">
									<i class="bi bi-qr-code-scan"></i> Scan QR
								</button>
							</div>
							<!-- <input type="hidden" id="scanMode" value="IN"> Default mode -->

							<div class="card-body">
								<div class="table-responsive log-table-scroll" style="max-height: 400px; overflow-y: auto;">
									<table id="logTable" class="table table-sm">
										<thead class="table-success">
											<tr>
												<th>No.</th>
												<th><i class="bi bi-person-fill"></i> Name</th>
												<th><i class="bi bi-geo-alt-fill"></i> Location</th>
												<th><i class="bi bi-door-closed-fill"></i> Room Name</th>
												<th><i class="bi bi-calendar-check-fill"></i> Date & Time</th>
												<th><i class="bi bi-info-circle-fill"></i> Status</th>
											</tr>
										</thead>
										<tbody>
											<?php
											$counter = 1;
											if ($result && $result->num_rows > 0) {
												while ($row = $result->fetch_assoc()):
													$formattedDate = date('F j, Y, g:i A', strtotime($row['date_time']));
											?>
													<tr>
														<td class="cell-number"><?php echo $counter++; ?></td>
														<td class="cell-name"><?php echo htmlspecialchars($row['f_name'] . " " . $row['l_name']); ?></td>
														<td class="cell-location"><?php echo htmlspecialchars($row['name'] ?? 'N/A'); ?></td>
														<td class="cell-room"><?php echo htmlspecialchars($row['room_name'] ?? 'N/A'); ?></td>
														<td class="cell-date"><?php echo htmlspecialchars($formattedDate ?? 'N/A'); ?></td>
														<td class="cell-status"><?php echo htmlspecialchars($row['status'] ?? 'N/A'); ?></td>
													</tr>
											<?php endwhile;
											} else {
												echo "<tr><td colspan='6' class='text-center'>No records found.</td></tr>";
											}
											?>
										</tbody>
									</table>
								</div>

								<!-- Pagination -->
								<nav aria-label="Page navigation example" class="mt-3">
									<ul class="pagination justify-content-center" id="pagination">
										<!-- JS will fill this -->
									</ul>
								</nav>

							</div>
						</div>
					</div>
				</div>
			</div>
		</main>

		<?php include '../layout/footer.php';
		// include '../modal/modal_log.php'; Assuming modal file is updated for log management
		?>
	</div>
	<script src="../scripts/admin_script/idps_log.js"></script>
	<!-- SweetAlert2 -->
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<!-- QR Code Scanner -->
	<script src="https://unpkg.com/html5-qrcode"></script>
	<script>
		let qrScanner;
let availableCameras = [];
let lastScannedId = null;
let debounceTimeout = null;

async function initCameraList() {
	const select = document.getElementById("cameraSelect");
	select.innerHTML = "";
	availableCameras = await Html5Qrcode.getCameras();
	availableCameras.forEach(cam => {
		const option = document.createElement("option");
		option.value = cam.id;
		option.text = cam.label;
		select.appendChild(option);
	});
}

async function startScanner() {
	const cameraId = document.getElementById("cameraSelect").value;
	qrScanner = new Html5Qrcode("qr-reader");

	try {
		await qrScanner.start(
			cameraId,
			{ fps: 10, qrbox: 300 },
			async (decodedText) => {
				const match = decodedText.trim().match(/pre_reg(?:_id)?:\s*(\d+)/i);
				if (!match) return;

				const preRegId = match[1];
				if (preRegId === lastScannedId) return;

				lastScannedId = preRegId;
				clearTimeout(debounceTimeout);
				debounceTimeout = setTimeout(() => lastScannedId = null, 3000);

				try {
					// Step 1: Get latest status from backend
					let statusRes = await fetch(`../action/get_latest_status.php?pre_reg_id=${preRegId}`);
					let statusData = await statusRes.json();

					let nextAction;
					if (statusData.latest_status === "IN") {
						nextAction = "OUT";
					} else {
						nextAction = "IN";
					}

					// Step 2: Log the next action
					const formData = new FormData();
					formData.append("pre_reg_id", preRegId);
					formData.append("logType", nextAction);

					let logRes = await fetch(`../action/log_family.php`, {
						method: "POST",
						body: formData
					});

					let logData = await logRes.json();
					let logTime = new Date().toLocaleTimeString();

					if (logData.success) {
						Swal.fire({
							icon: 'success',
							title: `${nextAction} logged!`,
							text: `${logData.name} (${logData.type}) has been logged at ${logTime}.`,
							timer: 1500,
							timerProgressBar: true
						});
					} else {
						Swal.fire({
							icon: 'warning',
							title: 'Scan Failed',
							text: logData.message || 'Unregistered or already scanned.',
							timer: 1500,
							timerProgressBar: true
						});
					}
				} catch (err) {
					console.error("Log error:", err);
					Swal.fire({
						icon: 'error',
						title: 'Log Failed',
						text: err.message,
					});
				}
			},
			(err) => console.warn("QR error:", err)
		);

		document.getElementById("startScannerBtn").disabled = true;
		document.getElementById("stopScannerBtn").disabled = false;
	} catch (err) {
		console.error("Scanner failed to start:", err);
		alert("Unable to access the camera.");
	}
}

async function stopScanner() {
	if (qrScanner) {
		await qrScanner.stop();
		await qrScanner.clear();
		qrScanner = null;
	}
	document.getElementById("startScannerBtn").disabled = false;
	document.getElementById("stopScannerBtn").disabled = true;
}

document.getElementById("startScannerBtn").addEventListener("click", startScanner);
document.getElementById("stopScannerBtn").addEventListener("click", stopScanner);

document.getElementById("scanQRModal")?.addEventListener("shown.bs.modal", async () => {
	await initCameraList();
});
document.getElementById("scanQRModal")?.addEventListener("hidden.bs.modal", stopScanner);
	</script>
  <script>
        document.addEventListener("DOMContentLoaded", function() {
            let rowsPerPage = 10;
            let currentPage = 1;

            const table = document.getElementById("logTable").getElementsByTagName("tbody")[0];
            const rows = Array.from(table.getElementsByTagName("tr"));
            const searchBox = document.getElementById("searchBox");
            const dateFilter = document.getElementById("dateFilter");
            const pagination = document.getElementById("pagination");
            const rowsPerPageSelect = document.getElementById("rowsPerPageSelect");

            function filterRows() {
                const searchText = searchBox.value.toLowerCase();
                const dateValue = dateFilter.value;

                rows.forEach(row => {
                    const cells = row.getElementsByTagName("td");
                    const name = cells[1]?.textContent.toLowerCase() || "";
                    const location = cells[2]?.textContent.toLowerCase() || "";
                    const dateText = cells[4]?.textContent || "";
                    const dateMatch = dateValue ? dateText.includes(new Date(dateValue).toLocaleDateString('en-US', {
                        month: 'long',
                        day: 'numeric',
                        year: 'numeric'
                    })) : true;

                    if ((name.includes(searchText) || location.includes(searchText)) && dateMatch) {
                        row.style.display = "";
                    } else {
                        row.style.display = "none";
                    }
                });

                currentPage = 1;
                updatePagination();
            }

            function updatePagination() {
                const visibleRows = getVisibleRows();
                const totalPages = Math.ceil(visibleRows.length / rowsPerPage);

                // Ensure current page doesn't exceed total pages
                if (currentPage > totalPages && totalPages > 0) {
                    currentPage = totalPages;
                }

                // Clear pagination
                pagination.innerHTML = "";

                if (totalPages <= 1) {
                    showPage();
                    return;
                }

                // Previous button
                const prevLi = document.createElement("li");
                prevLi.className = "page-item" + (currentPage === 1 ? " disabled" : "");
                const prevA = document.createElement("a");
                prevA.className = "page-link";
                prevA.href = "#";
                prevA.innerHTML = "&laquo;";
                prevA.addEventListener("click", function(e) {
                    e.preventDefault();
                    if (currentPage > 1) {
                        currentPage--;
                        showPage();
                        updatePaginationButtons();
                    }
                });
                prevLi.appendChild(prevA);
                pagination.appendChild(prevLi);

                // Generate pagination buttons
                for (let i = 1; i <= totalPages; i++) {
                    const li = document.createElement("li");
                    li.className = "page-item" + (i === currentPage ? " active" : "");
                    const a = document.createElement("a");
                    a.className = "page-link";
                    a.href = "#";
                    a.textContent = i;
                    a.addEventListener("click", function(e) {
                        e.preventDefault();
                        currentPage = i;
                        showPage();
                        updatePaginationButtons();
                    });
                    li.appendChild(a);
                    pagination.appendChild(li);
                }

                // Next button
                const nextLi = document.createElement("li");
                nextLi.className = "page-item" + (currentPage === totalPages ? " disabled" : "");
                const nextA = document.createElement("a");
                nextA.className = "page-link";
                nextA.href = "#";
                nextA.innerHTML = "&raquo;";
                nextA.addEventListener("click", function(e) {
                    e.preventDefault();
                    if (currentPage < totalPages) {
                        currentPage++;
                        showPage();
                        updatePaginationButtons();
                    }
                });
                nextLi.appendChild(nextA);
                pagination.appendChild(nextLi);

                showPage();
            }

            function updatePaginationButtons() {
                const paginationItems = pagination.querySelectorAll('.page-item');
                paginationItems.forEach((item, index) => {
                    if (index === 0) {
                        // Previous button
                        item.className = "page-item" + (currentPage === 1 ? " disabled" : "");
                    } else if (index === paginationItems.length - 1) {
                        // Next button
                        const visibleRows = getVisibleRows();
                        const totalPages = Math.ceil(visibleRows.length / rowsPerPage);
                        item.className = "page-item" + (currentPage === totalPages ? " disabled" : "");
                    } else {
                        // Page number buttons
                        const pageNum = parseInt(item.querySelector('.page-link').textContent);
                        item.className = "page-item" + (pageNum === currentPage ? " active" : "");
                    }
                });
            }

            function getVisibleRows() {
                return rows.filter(row => {
                    const cells = row.getElementsByTagName("td");
                    const searchText = searchBox.value.toLowerCase();
                    const dateValue = dateFilter.value;
                    const name = cells[1]?.textContent.toLowerCase() || "";
                    const location = cells[2]?.textContent.toLowerCase() || "";
                    const dateText = cells[4]?.textContent || "";
                    const dateMatch = dateValue ? dateText.includes(new Date(dateValue).toLocaleDateString('en-US', {
                        month: 'long',
                        day: 'numeric',
                        year: 'numeric'
                    })) : true;
                    
                    return (name.includes(searchText) || location.includes(searchText)) && dateMatch;
                });
            }

            function showPage() {
                const visibleRows = getVisibleRows();
                const start = (currentPage - 1) * rowsPerPage;
                const end = start + rowsPerPage;

                // First, reset all rows to hidden
                rows.forEach(row => {
                    row.style.display = "none";
                });

                // Then show only the rows for current page
                visibleRows.forEach((row, index) => {
                    if (index >= start && index < end) {
                        row.style.display = "";
                    }
                });
            }

            // Event listeners
            searchBox.addEventListener("input", filterRows);
            dateFilter.addEventListener("change", filterRows);
            
            rowsPerPageSelect.addEventListener("change", function() {
                rowsPerPage = parseInt(this.value);
                currentPage = 1;
                updatePagination();
            });

            // Initial run
            filterRows();
        });
    </script>
</body>

</html>