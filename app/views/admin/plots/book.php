<?php
$plot = $plot ?? [];
$customers = $customers ?? [];
$paymentPlans = $paymentPlans ?? ['Full Payment', 'Installment (6 months)', 'Installment (12 months)', 'Installment (24 months)', 'Construction Linked'];
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-book me-2"></i>Book Plot</h2>
                <div>
                    <a href="/admin/plots/<?= $plot['id'] ?? 0 ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Plot
                    </a>
                    <a href="/admin/plots" class="btn btn-outline-secondary">
                        <i class="fas fa-th"></i> All Plots
                    </a>
                </div>
            </div>


            <div class="row">
                <!-- Plot Details Card -->
                <div class="col-md-5">
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white"><h5 class="mb-0"><i class="fas fa-th"></i> Plot Details</h5></div>
                        <div class="card-body aps-cp-card-body">
                            <div class="table-responsive"><table class="table table-bordered">
                                <tr><th style="width:140px">Plot #</th><td><strong><?= htmlspecialchars($plot['plot_number'] ?? 'N/A') ?></strong></td></tr>
                                <tr><th>Colony</th><td><?= htmlspecialchars($plot['colony_name'] ?? 'N/A') ?></td></tr>
                                <tr><th>Block / Sector</th><td><?= htmlspecialchars($plot['block'] ?? '') ?> <?= !empty($plot['sector']) ? '/ Sector ' . htmlspecialchars($plot['sector']) : '' ?></td></tr>
                                <tr><th>Type</th><td><?= ucfirst(htmlspecialchars($plot['plot_type'] ?? 'residential')) ?></td></tr>
                                <tr><th>Dimensions</th><td><?= !empty($plot['dimension_label']) ? htmlspecialchars($plot['dimension_label']) : number_format($plot['width_ft'] ?? 0) . 'x' . number_format($plot['length_ft'] ?? 0) . ' ft' ?></td></tr>
                                <tr><th>Area</th><td><?= number_format($plot['area_sqft'] ?? 0) ?> sqft</td></tr>
                                <tr><th>Price / sqft</th><td>₹<?= number_format(floatval($plot['price_per_sqft'] ?? 0), 2) ?></td></tr>
                                <tr><th>Total Price</th><td><strong class="text-primary">₹<?= number_format(intval($plot['total_price'] ?? 0)) ?></strong></td></tr>
                                <tr><th>Status</th><td><span class="badge bg-<?= $plot['status'] === 'available' ? 'success' : 'warning' ?>"><?= ucfirst(htmlspecialchars($plot['status'] ?? 'available')) ?></span></td></tr>
                                <tr><th>Facing</th><td><?= htmlspecialchars(ucfirst($plot['facing'] ?? 'N/A')) ?></td></tr>
                            </table></div>
                            <?php if (!empty($plot['features'])): ?>
                                <h6>Features</h6>
                                <p class="text-muted"><?= nl2br(htmlspecialchars($plot['features'])) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Booking Form -->
                <div class="col-md-7">
                    <div class="card mb-4">
                        <div class="card-header bg-success text-white"><h5 class="mb-0"><i class="fas fa-file-contract"></i> New Booking</h5></div>
                        <div class="card-body aps-cp-card-body">
                            <form method="POST" action="/admin/plots/<?= $plot['id'] ?? 0 ?>/book">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label fw-bold">Customer <span class="text-danger">*</span></label>
                                        <select name="customer_id" class="form-select" required>
                                            <option value="">-- Select Customer --</option>
                                            <?php foreach ($customers as $c): ?>
                                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name'] . ' (' . $c['email'] . ' - ' . $c['phone'] . ')') ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="form-text">Search by name, email or phone. <a href="/admin/users/create" target="_blank">+ Add New Customer</a></div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Payment Plan <span class="text-danger">*</span></label>
                                        <select name="payment_plan" class="form-select" required>
                                            <?php foreach ($paymentPlans as $pp): ?>
                                                <option value="<?= htmlspecialchars($pp) ?>"><?= htmlspecialchars($pp) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Token Amount (₹) <span class="text-danger">*</span></label>
                                        <input type="number" name="token_amount" class="form-control" min="0" step="0.01" value="<?= min(10000, intval($plot['total_price'] ?? 0) * 0.1) ?>" required>
                                        <div class="form-text">Typically 10% of total price</div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Booking Date</label>
                                        <input type="date" name="booking_date" class="form-control" value="<?= date('Y-m-d') ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Expected Possession</label>
                                        <input type="date" name="possession_date" class="form-control">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Negotiated / Deal Price (₹)</label>
                                    <input type="number" name="negotiated_price" class="form-control" min="0" step="0.01" value="<?= floatval($plot['negotiated_price'] ?? $plot['total_price'] ?? 0) ?>">
                                    <div class="form-text">Leave same as total price if no negotiation</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Notes</label>
                                    <textarea name="notes" class="form-control" rows="3" placeholder="Any special instructions or remarks..."></textarea>
                                </div>

                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> Booking will be created with <strong>pending</strong> status. Admin can confirm from the booking management page.
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="/admin/plots/<?= $plot['id'] ?? 0 ?>" class="btn btn-secondary me-md-2">Cancel</a>
                                    <button type="submit" class="btn btn-success px-4">
                                        <i class="fas fa-check-circle"></i> Create Booking
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
