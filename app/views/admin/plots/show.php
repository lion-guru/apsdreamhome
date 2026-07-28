<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-th"></i> Plot Details: <?= htmlspecialchars($plot['plot_number'] ?? '') ?></h2>
                <div>
                    <a href="<?= BASE_URL ?>/admin/plots" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Plots
                    </a>
                    <a href="<?= BASE_URL ?>/admin/plots/<?= $plot['id'] ?? 0 ?>/edit" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit Plot
                    </a>
                    <a href="<?= BASE_URL ?>/admin/mlm-realestate/bookings?plot_id=<?= $plot['id'] ?? 0 ?>" class="btn btn-info text-white">
                        <i class="fas fa-book"></i> Bookings
                    </a>
                </div>
            </div>


            <div class="row">
                <!-- Basic Info -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header aps-cp-card-header"><h5 class="mb-0">Basic Information</h5></div>
                        <div class="card-body aps-cp-card-body">
                            <div class="table-responsive"><table class="table table-bordered">
                                <tr><th style="width:160px">Plot Number</th><td><?= htmlspecialchars($plot['plot_number'] ?? '') ?></td></tr>
                                <tr><th>Block / Sector</th><td><?= htmlspecialchars($plot['block'] ?? '') ?> <?= !empty($plot['sector']) ? '/ Sector ' . htmlspecialchars($plot['sector']) : '' ?></td></tr>
                                <tr><th>Type</th><td><?= ucfirst(htmlspecialchars($plot['plot_type'] ?? 'residential')) ?></td></tr>
                                <tr><th>Status</th><td>
                                    <span class="badge bg-<?= ($plot['status'] ?? '') === 'available' ? 'success' : (($plot['status'] ?? '') === 'booked' ? 'warning' : (($plot['status'] ?? '') === 'sold' ? 'danger' : 'secondary')) ?> fs-6">
                                        <?= ucfirst(htmlspecialchars($plot['status'] ?? 'available')) ?>
                                    </span>
                                </td></tr>
                                <tr><th>Colony</th><td><?= htmlspecialchars($plot['colony_name'] ?? '') ?></td></tr>
                                <tr><th>Location</th><td><?= htmlspecialchars(($plot['state_name'] ?? '') . (!empty($plot['district_name']) ? ', ' . $plot['district_name'] : '')) ?></td></tr>
                            </table></div>
                        </div>
                    </div>
                </div>

                <!-- Dimensions & Area -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header aps-cp-card-header"><h5 class="mb-0">Dimensions & Area</h5></div>
                        <div class="card-body aps-cp-card-body">
                            <div class="table-responsive"><table class="table table-bordered">
                                <tr><th style="width:160px">Dimensions</th>
                                    <td>
                                        <?php if (!empty($plot['dimension_label'])): ?>
                                            <span class="badge bg-primary fs-6 px-3 py-2"><?= htmlspecialchars($plot['dimension_label']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">Not set</span>
                                        <?php endif; ?>
                                        <?php if (!empty($plot['width_ft']) && !empty($plot['length_ft'])): ?>
                                            <span class="text-muted ms-2">(<?= floatval($plot['width_ft']) ?>ft × <?= floatval($plot['length_ft']) ?>ft)</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr><th>Area (sqft)</th><td><strong><?= number_format($plot['area_sqft'] ?? 0) ?></strong> sqft</td></tr>
                                <tr><th>Area (sqm)</th><td><?= number_format($plot['area_sqm'] ?? 0, 2) ?> sqm</td></tr>
                                <tr><th>Frontage / Depth</th><td><?= ($plot['frontage_ft'] ?? 0) > 0 ? floatval($plot['frontage_ft']) . ' ft' : 'N/A' ?> / <?= ($plot['depth_ft'] ?? 0) > 0 ? floatval($plot['depth_ft']) . ' ft' : 'N/A' ?></td></tr>
                                <tr><th>Road Width</th><td><?= ($plot['road_width_ft'] ?? 0) > 0 ? floatval($plot['road_width_ft']) . ' ft' : 'N/A' ?></td></tr>
                                <tr><th>Facing</th><td><?= htmlspecialchars(ucfirst($plot['facing'] ?? 'N/A')) ?></td></tr>
                            </table></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pricing Card -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-rupee-sign"></i> Pricing</h5>
                    <?php if (!empty($plot['negotiated_price']) && $plot['negotiated_price'] != $plot['total_price']): ?>
                        <span class="badge bg-warning text-dark fs-6">Negotiated Price Active</span>
                    <?php endif; ?>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <div class="text-muted small">Base Price/sqft</div>
                            <div class="fs-5 fw-bold">₹<?= number_format(floatval($plot['base_price_per_sqft'] ?? $plot['price_per_sqft'] ?? 0), 2) ?></div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="text-muted small">Current Price/sqft</div>
                            <div class="fs-5 fw-bold">₹<?= number_format(floatval($plot['price_per_sqft'] ?? 0), 2) ?></div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="text-muted small">Total Price</div>
                            <div class="fs-4 fw-bold text-primary">₹<?= number_format(intval($plot['total_price'] ?? 0)) ?></div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="text-muted small">Negotiated / Deal Price</div>
                            <div class="fs-5 fw-bold <?= !empty($plot['negotiated_price']) && $plot['negotiated_price'] != $plot['total_price'] ? 'text-success' : 'text-muted' ?>">
                                <?= !empty($plot['negotiated_price']) ? '₹' . number_format(intval($plot['negotiated_price'])) : '—' ?>
                            </div>
                            <?php if (!empty($plot['price_override_reason'])): ?>
                                <div class="text-muted small mt-1"><em><?= htmlspecialchars($plot['price_override_reason']) ?></em></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Price History -->
            <?php $priceHistory = $priceHistory ?? []; if (!empty($priceHistory)): ?>
            <div class="card mb-4">
                <div class="card-header aps-cp-card-header"><h5 class="mb-0"><i class="fas fa-history"></i> Price Change History</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-striped">
                        <thead><tr><th>Date</th><th>Old Price</th><th>New Price</th><th>Change Type</th><th>Reason</th><th>Changed By</th></tr></thead>
                        <tbody>
                            <?php foreach ($priceHistory as $ph): ?>
                            <tr>
                                <td><?= date('d M Y H:i', strtotime($ph['created_at'] ?? 'now')) ?></td>
                                <td>₹<?= number_format(intval($ph['old_price'] ?? 0)) ?></td>
                                <td>₹<?= number_format(intval($ph['new_price'] ?? 0)) ?></td>
                                <td><span class="badge bg-info"><?= htmlspecialchars($ph['change_type'] ?? 'override') ?></span></td>
                                <td><?= htmlspecialchars($ph['reason'] ?? '') ?></td>
                                <td><?= htmlspecialchars($ph['changed_by_name'] ?? 'Admin') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table></div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Features -->
            <?php if (!empty($plot['description']) || ($plot['corner_plot'] ?? false) || ($plot['park_facing'] ?? false)): ?>
            <div class="card mb-4">
                <div class="card-header aps-cp-card-header"><h5 class="mb-0">Features & Description</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="mb-3">
                        <?php if ($plot['corner_plot'] ?? false): ?>
                            <span class="badge bg-primary me-2 fs-6">Corner Plot</span>
                        <?php endif; ?>
                        <?php if ($plot['park_facing'] ?? false): ?>
                            <span class="badge bg-success me-2 fs-6">Park Facing</span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($plot['description'])): ?>
                        <p><?= nl2br(htmlspecialchars($plot['description'])) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Related Bookings -->
            <?php $bookings = $bookings ?? []; if (!empty($bookings)): ?>
            <div class="card mb-4">
                <div class="card-header aps-cp-card-header"><h5 class="mb-0"><i class="fas fa-book"></i> Related Bookings</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-striped">
                        <thead><tr><th>Booking #</th><th>Customer</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
                        <tbody>
                            <?php foreach ($bookings as $bk): ?>
                            <tr>
                                <td><a href="<?= BASE_URL ?>/admin/mlm-realestate/bookings/<?= $bk['id'] ?>">#<?= $bk['id'] ?></a></td>
                                <td><?= htmlspecialchars($bk['customer_name'] ?? 'N/A') ?></td>
                                <td>₹<?= number_format(intval($bk['amount'] ?? $bk['total_amount'] ?? 0)) ?></td>
                                <td><span class="badge bg-<?= $bk['status'] === 'confirmed' ? 'success' : ($bk['status'] === 'pending' ? 'warning' : 'secondary') ?>"><?= htmlspecialchars($bk['status'] ?? '') ?></span></td>
                                <td><?= htmlspecialchars($bk['booking_date'] ?? $bk['created_at'] ?? '') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table></div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Status History -->
            <?php $history = $history ?? []; if (!empty($history)): ?>
            <div class="card mb-4">
                <div class="card-header aps-cp-card-header"><h5 class="mb-0">Status History</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-striped">
                        <thead><tr><th>Date</th><th>Old Status</th><th>New Status</th><th>Changed By</th><th>Reason</th></tr></thead>
                        <tbody>
                            <?php foreach ($history as $h): ?>
                            <tr>
                                <td><?= date('d M Y H:i', strtotime($h['created_at'])) ?></td>
                                <td><?= htmlspecialchars($h['old_status']) ?></td>
                                <td><?= htmlspecialchars($h['new_status']) ?></td>
                                <td><?= htmlspecialchars($h['changed_by_name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($h['change_reason'] ?? '') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table></div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
