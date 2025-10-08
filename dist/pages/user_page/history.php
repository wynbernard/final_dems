<!doctype html>
<html lang="en">

<head>
	<?php include '../../../database/user_session.php'; ?>
	<?php include '../layout_user/head_links.php'; ?>
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
	<div class="app-wrapper">
		<?php include '../layout_user/header.php'; ?>
		<?php include '../layout_user/sidebar.php'; ?>
		<?php include '../alert/warning.php'; ?>
		<main class="app-main">
				<div class="content container-fluid">
					<div class="row g-4">
						<div class="col-12 mt-5">
							<div class="card rounded-3 overflow-hidden">
								<div class="card-header">
									<h5 class="card-title text-white mb-0"><i class="fas fa-history me-2"></i> My Event History</h5>
								</div>
								<div class="card-body">
									<?php
                                    include '../../../database/conn.php';
									// Show all evacuation registrations (events) that this pre_reg attended
									$userPreRegId = $_SESSION['pre_reg_id'] ?? null;
									if (!$userPreRegId) {
										echo '<div class="alert alert-warning">No user logged in.</div>';
									} else {
										$historySql = "
										SELECT er.evac_reg_id, er.pre_reg_id, er.room_id, er.evac_loc_id, er.date_reg, er.status,
										       pr.f_name, pr.l_name, pr.contact_no, r.room_name, el.name as evac_loc_name
										FROM evac_reg_table er
										LEFT JOIN pre_reg_table pr ON er.pre_reg_id = pr.pre_reg_id
										LEFT JOIN room_table r ON er.room_id = r.room_id
										LEFT JOIN evac_loc_table el ON er.evac_loc_id = el.evac_loc_id
										WHERE er.pre_reg_id = ?
										ORDER BY er.date_reg DESC
										";

										$hst = $conn->prepare($historySql);
										$hst->bind_param('i', $userPreRegId);
										$hst->execute();
										$hres = $hst->get_result();

										if ($hres && $hres->num_rows > 0) {
											?>
											<div class="table-responsive">
												<table class="table table-hover align-middle">
													<thead class="table-light">
														<tr>
															<th>Date</th>
															<th>Location</th>
															<th>Room</th>
															<th>Status</th>
															<th>Distributed Items</th>
														</tr>
													</thead>
													<tbody>
											<?php
											while ($row = $hres->fetch_assoc()) {
												// get distributed items aggregated for this user (table uses pre_reg_id)
												$distQ = $conn->prepare("SELECT d.resource_id, ra.resource_name, SUM(d.quantity) AS total_qty, MIN(ra.measurement_unit) AS unit FROM resource_distribution_table d LEFT JOIN resource_allocation_table ra ON d.resource_id = ra.resource_id WHERE d.pre_reg_id = ? GROUP BY d.resource_id");
												$distQ->bind_param('i', $row['pre_reg_id']);
												$distQ->execute();
												$distR = $distQ->get_result();
												$items = [];
												while ($di = $distR->fetch_assoc()) {
													$label = htmlspecialchars($di['resource_name']);
													$qty = intval($di['total_qty']);
													$unit = htmlspecialchars($di['unit'] ?? '');
													$items[] = $label . ' x ' . $qty . ($unit ? (' ' . $unit) : '');
												}
											?>
												<tr>
													<td><?= htmlspecialchars($row['date_reg']) ?></td>
													<td><?= htmlspecialchars($row['evac_loc_name'] ?? 'N/A') ?></td>
													<td><?= htmlspecialchars($row['room_name'] ?? 'N/A') ?></td>
													<td><?= htmlspecialchars($row['status'] ?? '') ?></td>
													<td>
														<?php if (!empty($items)): ?>
															<?= implode('<br>', $items) ?>
														<?php else: ?>
															<small class="text-muted">No items distributed</small>
														<?php endif; ?>
													</td>
												</tr>
											<?php
											}
												?>
													</tbody>
													</table>
												</div>
												<?php
										} else {
											?>
											<div class="empty-state text-center py-4">
												<i class="fas fa-history fa-2x"></i>
												<h5 class="mt-3">No event history found</h5>
												<p class="text-muted">You have no recorded evacuation events.</p>
											</div>
											<?php
										}
											}
											?>
										</div>
										</div>
									</div>
								</div>
								<div class="col-12">
									<div class="card rounded-3 overflow-hidden mt-3">
										<div class="card-header">
											<h5 class="card-title text-white mb-0"><i class="fas fa-boxes me-2"></i> Resource Distribution History</h5>
										</div>
										<div class="card-body">
											<?php
											if ($userPreRegId) {
												$distHist = $conn->prepare("SELECT 
													d.date_time, 
													d.resource_id, 
													d.quantity, 
													d.unit, 
													d.distribution_type, 
													ra.resource_name,
													(
														SELECT er.status 
														FROM evac_reg_table er 
														WHERE er.pre_reg_id = d.pre_reg_id 
														  AND er.date_reg <= d.date_time 
														ORDER BY er.date_reg DESC 
														LIMIT 1
													) AS reg_status_at_time
												FROM resource_distribution_table d 
												LEFT JOIN resource_allocation_table ra ON d.resource_id = ra.resource_id 
												WHERE d.pre_reg_id = ? 
												ORDER BY d.date_time DESC");
												$distHist->bind_param('i', $userPreRegId);
												$distHist->execute();
												$distRes = $distHist->get_result();
												if ($distRes && $distRes->num_rows > 0) {
													?>
													<div class="table-responsive">
														<table class="table table-hover align-middle">
															<thead class="table-light">
																<tr>
																	<th>Date</th>
																	<th>Resource</th>
																	<th>Quantity</th>
																	<th>Type</th>
																	<th>Status</th>
																</tr>
															</thead>
															<tbody>
																<?php while ($drow = $distRes->fetch_assoc()): ?>
																<tr>
																	<td><?= htmlspecialchars($drow['date_time']) ?></td>
																	<td><?= htmlspecialchars($drow['resource_name'] ?? ('#' . $drow['resource_id'])) ?></td>
																	<td><?= intval($drow['quantity']) . ' ' . htmlspecialchars($drow['unit'] ?? '') ?></td>
																	<td><?= htmlspecialchars(ucfirst($drow['distribution_type'] ?? '')) ?></td>
																	<td>
																		<?php 
																			$onsite = (isset($drow['reg_status_at_time']) && $drow['reg_status_at_time'] === 'Evacuated');
																			$badgeClass = $onsite ? 'badge bg-success' : 'badge bg-secondary';
																			$label = $onsite ? 'On-site' : 'Off-site';
																		?>
																		<span class="<?= $badgeClass ?>"><?= $label ?></span>
																	</td>
																</tr>
																<?php endwhile; ?>
															</tbody>
														</table>
													</div>
													<?php
												} else {
													?>
													<div class="empty-state text-center py-4">
														<i class="fas fa-box-open fa-2x"></i>
														<h6 class="mt-3">No distribution history</h6>
														<p class="text-muted">No resources have been recorded for your account.</p>
													</div>
													<?php
												}
											}
											?>
										</div>
									</div>
								</div>
					</div>
				</div>
			</main>

		<?php include '../layout_user/footer.php'; ?>
	</div>

	<!-- <?php include '../scripts/scripts.php'; ?> -->

	<script>
		// Add some interactive effects
		document.addEventListener('DOMContentLoaded', function() {
			// Add hover effect to edit icons
			const editIcons = document.querySelectorAll('.edit-icon');
			editIcons.forEach(icon => {
				icon.addEventListener('mouseenter', () => {
					icon.classList.add('fa-spin');
				});
				icon.addEventListener('mouseleave', () => {
					icon.classList.remove('fa-spin');
				});
			});

			// Add animation to profile cards when they come into view
			const observer = new IntersectionObserver((entries) => {
				entries.forEach(entry => {
					if (entry.isIntersecting) {
						entry.target.style.opacity = 1;
						entry.target.style.transform = 'translateY(0)';
					}
				});
			}, {
				threshold: 0.1
			});

			const cards = document.querySelectorAll('.profile-card, .profile-image-card');
			cards.forEach(card => {
				card.style.opacity = 0;
				card.style.transform = 'translateY(20px)';
				card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
				observer.observe(card);
			});
		});
	</script>
</body>

</html>