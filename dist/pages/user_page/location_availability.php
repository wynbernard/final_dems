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
		<?php include '../alert/warning.php';
		$user1 = $_SESSION['pre_reg_id']; // Assuming user ID is stored in session
		?>

		<main class="app-main bg-light">
			<!-- Main Content -->
			<div class="content py-4">
				<div class="container-fluid">
					<div class="row">
						<div class="col-12">
							<!-- Card with Search and Actions -->
							<div class="card border-0 shadow-sm rounded-lg overflow-hidden">
								<div class="card-header bg-white border-0 py-3">
									<div class="d-flex flex-column flex-md-row align-items-center justify-content-between">
										<div class="search-container mb-3 mb-md-0 w-100">
											<div class="input-group">
												<span class="input-group-text bg-white border-end-0">
													<i class="bi bi-search text-muted"></i>
												</span>
												<input type="text" id="searchBox" class="form-control border-start-0" placeholder="Search location or room...">
											</div>
										</div>
										<!-- <a href="location_availability.php" class="btn btn-primary btn-lg rounded-pill px-4 ms-md-3">
											<i class="bi bi-calendar-check me-2"></i> Check Availability
										</a> -->
									</div>
								</div>

								<div class="card-body p-0">
									<!-- Accordion for Locations -->
									<div class="accordion accordion-flush" id="evacuationAccordion">
										<?php
										$query = "
                                    SELECT 
                                        l.evac_loc_id,
                                        l.name AS location_name,
                                        r.room_id,
                                        r.room_name,
                                        r.room_capacity,
                                        COUNT(DISTINCT CASE WHEN ac.classification != 'Infant' THEN e.pre_reg_id END) AS occupied_slots,
                                        (r.room_capacity - COUNT(DISTINCT CASE WHEN ac.classification  != 'Infant' THEN e.pre_reg_id END)) AS available_slots
                                    FROM evac_loc_table l
                                    LEFT JOIN room_table r ON l.evac_loc_id = r.evac_loc_id
                                    LEFT JOIN room_reservation_table e ON r.room_id = e.room_id
                                    LEFT JOIN pre_reg_table pr ON e.pre_reg_id = pr.pre_reg_id
                                    LEFT JOIN age_class_table ac ON pr.age_class_id = ac.age_class_id
                                    GROUP BY 
                                        l.evac_loc_id, l.name, r.room_id, r.room_name, r.room_capacity
                                    ORDER BY l.name, r.room_name
                                    ";

										$result = $conn->query($query);
										$locations = [];
										while ($row = $result->fetch_assoc()) {
											$locations[$row['location_name']][] = $row;
										}

										$index = 0;
										foreach ($locations as $locationName => $rooms):
											$collapseId = 'collapse' . $index;
										?>
											<div class="accordion-item border-0 mb-3">
												<h2 class="accordion-header" id="heading<?= $index; ?>">
													<button class="accordion-button bg-white rounded-3 shadow-sm py-3 px-4 <?= $index !== 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $collapseId ?>" aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>" aria-controls="<?= $collapseId ?>">
														<div class="d-flex align-items-center w-100">
															<div class="me-3">
																<i class="bi bi-building text-primary fs-4"></i>
															</div>
															<div class="flex-grow-1 text-start">
																<h5 class="mb-0 fw-bold"><?= htmlspecialchars($locationName); ?></h5>
																<small class="text-muted"><?= count($rooms); ?> room<?= count($rooms) !== 1 ? 's' : '' ?> available</small>
															</div>
															<i class="bi bi-chevron-down accordion-arrow ms-auto"></i>
														</div>
													</button>
												</h2>
												<div id="<?= $collapseId ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" aria-labelledby="heading<?= $index; ?>" data-bs-parent="#evacuationAccordion">
													<div class="accordion-body px-0 pt-3">
														<?php if (count($rooms) > 0 && $rooms[0]['room_id']): ?>
															<div class="row g-3">
																<?php foreach ($rooms as $room): ?>
																	<div class="col-md-6 col-lg-4">
																		<div class="card h-100 border-0 shadow-sm rounded-lg overflow-hidden">
																			<div class="card-header bg-light py-3">
																				<h6 class="mb-0 fw-bold text-dark"><?= htmlspecialchars($room['room_name']); ?></h6>
																			</div>
																			<div class="card-body">
																				<div class="d-flex justify-content-between mb-3">
																					<div>
																						<small class="text-muted">Capacity</small>
																						<h5 class="mb-0 fw-bold"><?= $room['room_capacity']; ?></h5>
																					</div>
																					<div>
																						<small class="text-muted">Occupied</small>
																						<h5 class="mb-0 fw-bold"><?= $room['occupied_slots']; ?></h5>
																					</div>
																					<div>
																						<small class="text-muted">Available</small>
																						<h5 class="mb-0 fw-bold <?= $room['available_slots'] > 0 ? 'text-success' : 'text-danger' ?>">
																							<?= $room['available_slots']; ?>
																						</h5>
																					</div>
																				</div>

																				<?php if ($room['available_slots'] > 0): ?>
																					<button class="btn btn-primary w-100 rounded-pill py-2" onclick="confirmReservation(<?= $room['room_id']; ?>)">
																						<i class="bi bi-bookmark-check me-2"></i> Reserve Now
																					</button>
																				<?php else: ?>
																					<button class="btn btn-outline-danger w-100 rounded-pill py-2" disabled>
																						<i class="bi bi-exclamation-circle me-2"></i> Fully Booked
																					</button>
																				<?php endif; ?>
																			</div>
																		</div>
																	</div>
																<?php endforeach; ?>
															</div>
														<?php else: ?>
															<div class="text-center py-5 bg-light rounded-3">
																<i class="bi bi-door-closed text-muted fs-1 mb-3"></i>
																<p class="text-muted mb-0">No rooms available at this location</p>
															</div>
														<?php endif; ?>
													</div>
												</div>
											</div>
										<?php
											$index++;
										endforeach;
										?>
									</div> <!-- End Accordion -->
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</main>

		<?php include '../layout_user/footer.php';
		include '../scripts/location_availabilty.php';
		?>
	</div>

	<?php include '../scripts/scripts.php'; ?>
</body>

</html>