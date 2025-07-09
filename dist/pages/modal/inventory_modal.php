<!-- Add this just before the closing </body> tag or after your main content -->
<div class="modal fade" id="addInventoryModal" tabindex="-1" aria-labelledby="addInventoryModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<form action="../action/inventory/add_inventory.php" method="POST" id="addInventoryForm">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="addInventoryModalLabel"><i class="bi bi-plus-circle"></i> Add New Resource</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="mb-3">
						<label for="resource_name" class="form-label">Resource Name</label>
						<input type="text" class="form-control" id="resource_name" name="resource_name" required>
					</div>
					<div class="mb-3">
						<label for="quantity" class="form-label">Quantity</label>
						<input type="number" min="0" class="form-control" id="quantity" name="quantity" required>
					</div>
					<!-- Optional: Add expiration date if needed
					<div class="mb-3">
						<label for="expiration_date" class="form-label">Expiration Date</label>
						<input type="date" class="form-control" id="expiration_date" name="expiration_date">
					</div>
					-->
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
					<button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Resource</button>
				</div>
			</div>
		</form>
	</div>
</div>

<!-- Edit inventory -->

<div class="modal fade" id="editInventoryModal" tabindex="-1" aria-labelledby="editInventoryModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<form id="editInventoryForm" method="POST" action="../action/inventory/edit_inventory.php">
				<div class="modal-header bg-primary text-white">
					<h5 class="modal-title" id="editInventoryModalLabel">Edit Inventory</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<input type="hidden" name="original_resource_name" id="editOriginalResource">
					<div class="mb-3">
						<label for="editResource" class="form-label">Resource Name</label>
						<input type="text" class="form-control" id="editResource" name="resource_name" required>
					</div>
					<div class="mb-3">
						<label for="editQuantity" class="form-label">Quantity</label>
						<input type="number" class="form-control" id="editQuantity" name="quantity" required>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary">Save Changes</button>
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- DELETE INVENTORY MODAL -->
<div class="modal fade" id="deleteInventoryModal" tabindex="-1" aria-labelledby="deleteInventoryModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="deleteInventoryModalLabel">Confirm Deletion</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<p>Are you sure you want to delete this resource: <strong id="deleteResourceName"></strong>?</p>
				<form id="deleteInventoryForm" action="../action/inventory/delete_inventory.php" method="POST">
					<input type="hidden" name="resource_name" id="deleteResourceInput">
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
				<button type="submit" class="btn btn-danger" onclick="document.getElementById('deleteInventoryForm').submit();">
					<i class="fas fa-trash"></i> Delete
				</button>
			</div>
		</div>
	</div>
</div>

<script>
	document.addEventListener("DOMContentLoaded", () => {
		const editButtons = document.querySelectorAll(".edit-btn");
		const deleteButtons = document.querySelectorAll(".delete-btn");

		editButtons.forEach(button => {
			button.addEventListener("click", () => {
				const resource = button.getAttribute("data-resource");
				const quantity = button.getAttribute("data-quantity");

				document.getElementById("editResource").value = resource;
				document.getElementById("editOriginalResource").value = resource; // for tracking old name
				document.getElementById("editQuantity").value = quantity;
			});
		});

		deleteButtons.forEach(button => {
			button.addEventListener("click", () => {
				const resource = button.getAttribute("data-resource");

				document.getElementById("deleteResourceName").textContent = resource;
				document.getElementById("deleteResourceInput").value = resource;
			});
		});
	});
</script>


<script>
	document.addEventListener("DOMContentLoaded", () => {
		const deleteButtons = document.querySelectorAll(".delete-btn");

		deleteButtons.forEach(button => {
			button.addEventListener("click", () => {
				const resource = button.getAttribute("data-resource");
				document.getElementById("deleteResourceName").textContent = resource;
				document.getElementById("deleteResourceInput").value = resource;
			});
		});
	});
</script>