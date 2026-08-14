<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-warehouse me-2"></i>Plot Inventory Overview</h4>
        <div>
            <span class="badge bg-success me-1 p-2"><?php echo $totalAvailable ?? 0; ?> Available</span>
            <span class="badge bg-warning text-dark me-1 p-2"><?php echo $totalBooked ?? 0; ?> Booked</span>
            <span class="badge bg-danger me-1 p-2"><?php echo $totalSold ?? 0; ?> Sold</span>
            <span class="badge bg-secondary p-2"><?php echo $totalPlots ?? 0; ?> Total</span>
        </div>
    </div>

    <?php if (empty($inventory)): ?>
        <div class="alert alert-info">No colonies found. Create a colony first.</div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($inventory as $col): 
                $availPct = $col['total_plots_actual'] > 0 ? round(($col['available'] / $col['total_plots_actual']) * 100) : 0;
                $bookedPct = $col['total_plots_actual'] > 0 ? round(($col['booked'] / $col['total_plots_actual']) * 100) : 0;
                $soldPct = $col['total_plots_actual'] > 0 ? round(($col['sold'] / $col['total_plots_actual']) * 100) : 0;
            ?>
            <div class="col-md-6 col-xl-4 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($col['colony_name'] ?? 'Unknown'); ?></h6>
                        <span class="badge bg-info"><?php echo $col['total_plots_actual']; ?> plots</span>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <p class="text-muted small mb-3">
                            <i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($col['location'] ?? 'N/A'); ?>
                            &middot; <i class="fas fa-layer-group ms-2 me-1"></i>Site capacity: <?php echo $col['total_plots'] ?? 'N/A'; ?>
                        </p>

                        <!-- Progress bars -->
                        <div class="mb-2">
                            <div class="d-flex justify-content-between small">
                                <span>Available</span>
                                <span class="fw-bold text-success"><?php echo $col['available']; ?> (<?php echo $availPct; ?>%)</span>
                            </div>
                            <div class="progress" class="style-31164">
                                <div class="progress-bar bg-success" class="style-57761"></div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between small">
                                <span>Booked</span>
                                <span class="fw-bold text-warning"><?php echo $col['booked']; ?> (<?php echo $bookedPct; ?>%)</span>
                            </div>
                            <div class="progress" class="style-31164">
                                <div class="progress-bar bg-warning" class="style-31368"></div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between small">
                                <span>Sold</span>
                                <span class="fw-bold text-danger"><?php echo $col['sold']; ?> (<?php echo $soldPct; ?>%)</span>
                            </div>
                            <div class="progress" class="style-31164">
                                <div class="progress-bar bg-danger" class="style-93120"></div>
                            </div>
                        </div>

                        <?php if ($col['other'] > 0): ?>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between small">
                                <span>Hold/Reserved</span>
                                <span class="fw-bold text-secondary"><?php echo $col['other']; ?></span>
                            </div>
                            <div class="progress" class="style-31164">
                                <div class="progress-bar bg-secondary" class="style-43704"></div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="mt-3 text-end">
                            <a href="<?php echo BASE_URL; ?>/admin/plots?colony_id=<?php echo $col['colony_id']; ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-list me-1"></i>View Plots
                            </a>
                            <a href="<?php echo BASE_URL; ?>/admin/bookings?colony_id=<?php echo $col['colony_id']; ?>" class="btn btn-sm btn-outline-info">
                                <i class="fas fa-file-invoice me-1"></i>Bookings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
