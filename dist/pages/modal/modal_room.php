<!-- Add Room Modal -->
<div class="modal fade" id="addRoomModal" tabindex="-1" aria-labelledby="addRoomModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="addRoomModalLabel">Add New Room</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form action="../action/add_room.php" method="POST">
					<!-- Display Selected Location -->
					<div class="mb-3">
						<h5>Adding Room to: <strong class="text-primary"><?php echo $location_name; ?></strong></h5>
					</div>
					<!-- Hidden Input to Pass Location ID -->
					<input type="hidden" name="evac_loc_id" value="<?php echo $evac_loc_id; ?>">

					<div class="mb-3">
						<label for="roomName" class="form-label">Room Name</label>
						<input type="text" class="form-control" id="roomName" name="room_name" required>
					</div>
					<div class="mb-3">
						<label for="roomCapacity" class="form-label">Room Capacity</label>
						<input type="number" class="form-control" id="roomCapacity" name="room_capacity" required>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
						<button type="submit" class="btn btn-primary">Add Room</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="editRoomModal" tabindex="-1" aria-labelledby="editRoomModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="editRoomModalLabel">Edit Room</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form action="../action/edit_room.php" method="POST">
					<!-- Hidden Field for room_id -->
					<input type="hidden" id="editRoomId" name="room_id">
					<input type="hidden" name="evac_loc_id" value="<?php echo $evac_loc_id; ?>">
					<div class="mb-3">
						<label for="editRoomName" class="form-label">Room Name</label>
						<input type="text" class="form-control" id="editRoomName" name="room_name" required>
					</div>
					<div class="mb-3">
						<label for="editRoomCapacity" class="form-label">Room Capacity</label>
						<input type="number" class="form-control" id="editRoomCapacity" name="room_capacity" required>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
						<button type="submit" class="btn btn-primary">Update Room</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<script>
	document.addEventListener("DOMContentLoaded", function() {
		// Select all edit buttons
		const editButtons = document.querySelectorAll(".edit-btn");

		editButtons.forEach(button => {
			button.addEventListener("click", function() {
				// Get data attributes from the clicked button
				const roomId = this.getAttribute("data-id");
				const roomName = this.getAttribute("data-name");
				const roomCapacity = this.getAttribute("data-capacity");

				// Populate the modal fields with the current room data
				document.getElementById("editRoomId").value = roomId;
				document.getElementById("editRoomName").value = roomName;
				document.getElementById("editRoomCapacity").value = roomCapacity;
			});
		});
	});
</script>

<!-- Delete Room Modal -->
<div class="modal fade" id="deleteRoomModal" tabindex="-1" aria-labelledby="deleteRoomModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="deleteRoomModalLabel">Confirm Deletion</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<p>Are you sure you want to delete this room?</p>
			</div>
			<div class="modal-footer">
				<form action="../action/delete_room.php" method="POST">
					<input type="hidden" name="evac_loc_id" value="<?php echo $evac_loc_id; ?>">
					<input type="hidden" name="room_id" id="deleteRoomId">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
					<button type="submit" class="btn btn-danger">Delete</button>
				</form>
			</div>
		</div>
	</div>
</div>
<script>
	document.addEventListener("DOMContentLoaded", function() {
		let deleteButtons = document.querySelectorAll(".delete-btn");

		deleteButtons.forEach(button => {
			button.addEventListener("click", function() {
				let roomId = this.getAttribute("data-id");
				document.getElementById("deleteRoomId").value = roomId;
			});
		});
	});
</script>

<!-- View IDP Modal -->
<div class="modal fade" id="viewIDPModal" tabindex="-1" aria-labelledby="viewIDPModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-lg">
		<div class="modal-content shadow-lg rounded-4 overflow-hidden border-0">

			<!-- Modal Header -->
			<div class="modal-header bg-gradient-success text-white py-3 px-4">
				<h5 class="modal-title fw-semibold mb-0">
					<i class="fas fa-users me-2"></i>
					<span id="modalLocation">...</span> Residents
				</h5>
				<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>

			<!-- Modal Body -->
			<div class="modal-body p-0">
				<!-- Search and Filter -->
				<div class="sticky-top bg-white z-1 p-3 border-bottom">
					<div class="d-flex align-items-center gap-2">
						<div class="input-group input-group-sm rounded-pill flex-grow-1" style="max-width: 300px;">
							<span class="input-group-text bg-white border-end-0 ps-3">
								<i class="fas fa-search text-muted"></i>
							</span>
							<input type="text" class="form-control border-start-0 pe-3 py-1" placeholder="Search residents..." id="idpSearch">
						</div>
						<div class="dropdown">
							<button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
								<i class="fas fa-filter me-1"></i> Filter
							</button>
							<ul class="dropdown-menu dropdown-menu-end">
								<li>
									<h6 class="dropdown-header">Filter by</h6>
								</li>
								<li><a class="dropdown-item" href="#">All Residents</a></li>
								<li>
									<hr class="dropdown-divider">
								</li>
								<li><a class="dropdown-item" href="#"><i class="fas fa-male me-2"></i>Male</a></li>
								<li><a class="dropdown-item" href="#"><i class="fas fa-female me-2"></i>Female</a></li>
								<li>
									<hr class="dropdown-divider">
								</li>
								<li><a class="dropdown-item" href="#"><i class="fas fa-baby me-2"></i>Children (0-12)</a></li>
								<li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Adults (13-59)</a></li>
								<li><a class="dropdown-item" href="#"><i class="fas fa-user-tie me-2"></i>Seniors (60+)</a></li>
							</ul>
						</div>
					</div>
				</div>
				<!-- Residents Table -->
				<div class="table-responsive px-3 py-2">
					<table class="table table-hover align-middle">
						<thead class="table-success text-center">
							<tr>
								<th>Full Name</th>
								<th>Gender</th>
								<th>Age</th>
								<th>Arrival Date</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody id="idpTableBody">
							<!-- Rows will be injected here -->
						</tbody>
					</table>
				</div>
			</div>

			<!-- Modal Footer -->
			<div class="modal-footer bg-light py-2 px-3 border-top">
				<div class="d-flex justify-content-between w-100 align-items-center">
					<div class="text-muted small">
						Showing <span class="fw-semibold" id="showingCount">0</span> of <span class="fw-semibold" id="totalCount">0</span> residents
					</div>
					<button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">
						<i class="fas fa-times me-1"></i> Close
					</button>
				</div>
			</div>

		</div>
	</div>
</div>

<script>
	document.addEventListener('DOMContentLoaded', function() {
		const viewButtons = document.querySelectorAll('.view-idp-btn');

		viewButtons.forEach(button => {
			button.addEventListener('click', function() {
				const roomId = this.getAttribute('data-id');
				const locationName = this.getAttribute('data-location');

				// Update the modal title
				document.getElementById('modalLocation').textContent = locationName;

				// Show loading spinner
				const tableBody = document.getElementById('idpTableBody');
				tableBody.innerHTML = `
                <tr class="text-center text-muted">
                    <td colspan="7">
                        <div class="spinner-border text-success" role="status"></div>
                        <p class="mt-2 mb-0">Loading residents data...</p>
                    </td>
                </tr>
            `;

				// Fetch residents from backend
				fetch('../fetch_data/fetch_reg_idps.php?room_id=' + roomId)
					.then(response => response.json())
					.then(data => {
						tableBody.innerHTML = '';

						if (data.length > 0) {
							data.forEach(resident => {
								const row = `
                                <tr class="text-center">
                                    <td>${resident.full_name}</td>
                                    <td>${resident.gender}</td>
                                    <td>${resident.age}</td>
                                    <td>${resident.arrival_date}</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                    </td>
                                </tr>
                            `;
								tableBody.innerHTML += row;
							});
						} else {
							tableBody.innerHTML = `
                            <tr class="text-center text-muted">
                                <td colspan="7">
                                    <i class="fas fa-user-slash fs-3"></i>
                                    <h6 class="mt-2">No residents found</h6>
                                    <p class="small">This room currently has no residents assigned.</p>
                                    <button class="btn btn-sm btn-success mt-2">
                                        <i class="fas fa-plus me-1"></i> Assign Resident
                                    </button>
                                </td>
                            </tr>
                        `;
						}

						// Update footer counts
						document.getElementById('showingCount').textContent = data.length;
						document.getElementById('totalCount').textContent = data.length;
					})
					.catch(error => {
						console.error('Error fetching residents:', error);
						tableBody.innerHTML = `
                        <tr class="text-center text-danger">
                            <td colspan="7">Failed to load residents.</td>
                        </tr>
                    `;
					});
			});
		});
	});
</script>