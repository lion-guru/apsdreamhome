<?php
$plot = $plot ?? [];
$customers = $customers ?? [];
$transferReasons = $transferReasons ?? ['Sale by Owner', 'Gift / Family Transfer', 'Resale', 'Company Transfer', 'Nominee Transfer', 'Legal Heir', 'Other'];
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-exchange-alt me-2"></i>Plot Transfer</h2>
                <div>
                    <a href="/admin/plots/<?= $plot['id'] ?? 0 ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Plot
                    </a>
                    <a href="/admin/plots" class="btn btn-outline-secondary">
                        <i class="fas fa-th"></i> All Plots
                    </a>
                </div>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show"><?= $_SESSION['success']; unset($_SESSION['success']); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show"><?= $_SESSION['error']; unset($_SESSION['error']); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>

            <div class="row">
                <!-- Plot & Current Owner Info -->
                <div class="col-md-5">
                    <div class="card mb-4">
                        <div class="card-header bg-info text-white"><h5 class="mb-0"><i class="fas fa-th"></i> Plot Information</h5></div>
                        <div class="card-body aps-cp-card-body">
                            <table class="table table-bordered">
                                <tr><th style="width:140px">Plot #</th><td><strong><?= htmlspecialchars($plot['plot_number'] ?? 'N/A') ?></strong></td></tr>
                                <tr><th>Colony</th><td><?= htmlspecialchars($plot['colony_name'] ?? 'N/A') ?></td></tr>
                                <tr><th>Block / Sector</th><td><?= htmlspecialchars($plot['block'] ?? '') ?> <?= !empty($plot['sector']) ? '/ Sector ' . htmlspecialchars($plot['sector']) : '' ?></td></tr>
                                <tr><th>Dimensions</th><td><?= !empty($plot['dimension_label']) ? htmlspecialchars($plot['dimension_label']) : number_format($plot['width_ft'] ?? 0) . 'x' . number_format($plot['length_ft'] ?? 0) . ' ft' ?></td></tr>
                                <tr><th>Area</th><td><?= number_format($plot['area_sqft'] ?? 0) ?> sqft</td></tr>
                                <tr><th>Total Price</th><td><strong class="text-primary">₹<?= number_format(intval($plot['total_price'] ?? 0)) ?></strong></td></tr>
                                <tr><th>Current Status</th><td><span class="badge bg-<?= $plot['status'] === 'available' ? 'success' : ($plot['status'] === 'booked' ? 'warning' : ($plot['status'] === 'sold' ? 'danger' : 'secondary')) ?>"><?= ucfirst(htmlspecialchars($plot['status'] ?? 'available')) ?></span></td></tr>
                            </table>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-secondary text-white"><h5 class="mb-0"><i class="fas fa-user"></i> Current Owner</h5></div>
                        <div class="card-body aps-cp-card-body">
                            <?php if (!empty($plot['customer_id']) && !empty($plot['current_owner_name'])): ?>
                                <table class="table table-bordered">
                                    <tr><th style="width:140px">Name</th><td><?= htmlspecialchars($plot['current_owner_name'] ?? '') ?></td></tr>
                                    <tr><th>Email</th><td><?= htmlspecialchars($plot['current_owner_email'] ?? '') ?></td></tr>
                                    <tr><th>Phone</th><td><?= htmlspecialchars($plot['current_owner_phone'] ?? '') ?></td></tr>
                                    <tr><th>Booking Date</th><td><?= htmlspecialchars($plot['booking_date'] ?? 'N/A') ?></td></tr>
                                </table>
                            <?php else: ?>
                                <p class="text-muted mb-0"><i class="fas fa-info-circle"></i> No current owner / Not yet booked.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-warning"><h5 class="mb-0"><i class="fas fa-calculator"></i> Transfer Fee</h5></div>
                        <div class="card-body aps-cp-card-body">
                            <?php
                            $transferFeeRate = 0.02; // 2% of total price
                            $transferFee = floatval($plot['total_price'] ?? 0) * $transferFeeRate;
                            ?>
                            <table class="table table-bordered">
                                <tr><th style="width:140px">Transfer Fee</th><td><strong class="text-danger">₹<?= number_format($transferFee, 2) ?></strong></td></tr>
                                <tr><th>Rate</th><td><?= ($transferFeeRate * 100) ?>% of total price</td></tr>
                                <tr><th>Total Price</th><td>₹<?= number_format(intval($plot['total_price'] ?? 0)) ?></td></tr>
                            </table>
                            <div class="form-text">Transfer fee subject to change as per company policy.</div>
                        </div>
                    </div>
                </div>

                <!-- Transfer Form -->
                <div class="col-md-7">
                    <div class="card mb-4">
                        <div class="card-header bg-warning"><h5 class="mb-0"><i class="fas fa-file-signature"></i> Transfer Details</h5></div>
                        <div class="card-body aps-cp-card-body">
                            <form method="POST" action="/admin/plots/<?= $plot['id'] ?? 0 ?>/transfer">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label fw-bold">New Owner <span class="text-danger">*</span></label>
                                        <select name="new_owner_id" class="form-select" required>
                                            <option value="">-- Select Customer --</option>
                                            <?php foreach ($customers as $c): ?>
                                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name'] . ' (' . $c['email'] . ' - ' . $c['phone'] . ')') ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="form-text">Select the person this plot is being transferred to.</div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Transfer Reason <span class="text-danger">*</span></label>
                                        <select name="transfer_reason" class="form-select" required>
                                            <?php foreach ($transferReasons as $tr): ?>
                                                <option value="<?= htmlspecialchars($tr) ?>"><?= htmlspecialchars($tr) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Transfer Date</label>
                                        <input type="date" name="transfer_date" class="form-control" value="<?= date('Y-m-d') ?>">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Transfer Amount (₹)</label>
                                        <input type="number" name="transfer_amount" class="form-control" min="0" step="0.01" value="<?= floatval($plot['total_price'] ?? 0) ?>">
                                        <div class="form-text">Amount to be paid by new owner</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Transfer Fee (₹) <span class="text-danger">*</span></label>
                                        <input type="number" name="transfer_fee" class="form-control" min="0" step="0.01" value="<?= $transferFee ?>" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">New Plot Status After Transfer</label>
                                        <select name="new_status" class="form-select">
                                            <option value="sold">Sold (Completed)</option>
                                            <option value="booked">Booked (Pending)</option>
                                            <option value="hold">Hold</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Document Reference</label>
                                        <input type="text" name="document_ref" class="form-control" placeholder="Agreement / Registry #">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Remarks / Notes</label>
                                    <textarea name="remarks" class="form-control" rows="3" placeholder="Additional details about this transfer..."></textarea>
                                </div>

                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i> <strong>Important:</strong> Plot ownership will be permanently transferred to the new owner. Ensure all dues are cleared before processing.
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="/admin/plots/<?= $plot['id'] ?? 0 ?>" class="btn btn-secondary me-md-2">Cancel</a>
                                    <button type="submit" class="btn btn-warning px-4">
                                        <i class="fas fa-exchange-alt"></i> Process Transfer
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
