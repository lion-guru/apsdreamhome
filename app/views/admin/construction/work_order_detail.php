<?php
/**
 * Admin Work Order Detail & Milestone RA Bills Management View
 */
$page_title = $page_title ?? "Work Order #{$order['order_number']}";
$order = $order ?? [];
$bills = $order['bills'] ?? [];
$contractAmt = (float)($order['contract_amount'] ?? 0);
$billedAmt = (float)($order['total_gross_billed'] ?? 0);
$paidAmt = (float)($order['total_paid'] ?? 0);
$retentionHeld = (float)($order['total_retention_held'] ?? 0);
$tdsDeducted = (float)($order['total_tds_deducted'] ?? 0);
$pct = $order['completion_pct'] ?? 0;
?>

<div class="container-fluid py-4">
    <!-- Breadcrumb & Back -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="/admin/work-orders" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back to Work Orders
        </a>
        <div class="d-flex gap-2">
            <a href="/admin/work-orders/<?= $order['id'] ?>/print" target="_blank" class="btn btn-outline-dark btn-sm">
                <i class="fas fa-print me-1"></i>Print Official Work Order
            </a>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#newBillModal">
                <i class="fas fa-plus-circle me-1"></i>Submit Running Account (RA) Bill
            </button>
        </div>
    </div>

    <!-- Order Header Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-primary text-uppercase font-monospace fs-6"><?= htmlspecialchars($order['order_number']) ?></span>
                        <span class="badge bg-secondary"><?= str_replace('_', ' ', strtoupper(htmlspecialchars($order['work_category']))) ?></span>
                        <span class="badge <?= $order['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>"><?= ucfirst(htmlspecialchars($order['status'])) ?></span>
                    </div>
                    <h4 class="fw-bold text-dark mb-1"><?= htmlspecialchars($order['work_title']) ?></h4>
                    <p class="text-muted mb-0">
                        <i class="fas fa-map-marker-alt text-danger me-1"></i><?= htmlspecialchars($order['colony_name']) ?>
                        <?php if (!empty($order['colony_location'])): ?>
                            <span class="text-muted">(<?= htmlspecialchars($order['colony_location']) ?>)</span>
                        <?php endif; ?>
                    </p>
                </div>
                <div class="text-md-end">
                    <span class="text-muted small d-block">Contract Sum</span>
                    <h3 class="fw-bold text-dark mb-0">₹<?= number_format($contractAmt, 2) ?></h3>
                </div>
            </div>

            <!-- Scope -->
            <?php if (!empty($order['scope_description'])): ?>
                <div class="p-3 bg-light rounded-3 small text-secondary mb-3">
                    <strong>Scope & Specifications:</strong> <?= nl2br(htmlspecialchars($order['scope_description'])) ?>
                </div>
            <?php endif; ?>

            <!-- Contractor Info Row -->
            <div class="row g-3 pt-2 border-top small text-muted">
                <div class="col-md-3 col-6">
                    <span class="d-block fw-semibold text-dark">Contractor</span>
                    <?= htmlspecialchars($order['contractor_name']) ?>
                </div>
                <div class="col-md-3 col-6">
                    <span class="d-block fw-semibold text-dark">Phone</span>
                    <a href="tel:<?= htmlspecialchars($order['contractor_phone']) ?>" class="text-decoration-none">
                        <i class="fas fa-phone-alt me-1 text-success"></i><?= htmlspecialchars($order['contractor_phone']) ?>
                    </a>
                </div>
                <div class="col-md-3 col-6">
                    <span class="d-block fw-semibold text-dark">PAN & GSTIN</span>
                    <?= htmlspecialchars($order['contractor_pan'] ?: 'PAN: N/A') ?> | <?= htmlspecialchars($order['contractor_gstin'] ?: 'GST: N/A') ?>
                </div>
                <div class="col-md-3 col-6">
                    <span class="d-block fw-semibold text-dark">Target Deadline</span>
                    <i class="far fa-calendar-alt me-1"></i><?= !empty($order['completion_deadline']) ? date('d M Y', strtotime($order['completion_deadline'])) : 'Not specified' ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Breakdown Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm p-3 bg-white">
                <span class="text-muted small d-block">Total Gross Billed</span>
                <h4 class="fw-bold text-dark mb-1">₹<?= number_format($billedAmt, 2) ?></h4>
                <div class="progress" style="height: 5px;">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $pct ?>%"></div>
                </div>
                <small class="text-muted mt-1 d-block"><?= $pct ?>% of contract sum</small>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm p-3 bg-white">
                <span class="text-muted small d-block">Net Disbursed</span>
                <h4 class="fw-bold text-success mb-1">₹<?= number_format($paidAmt, 2) ?></h4>
                <small class="text-muted">Cleared via bank UTR</small>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm p-3 bg-white">
                <span class="text-muted small d-block">Retention Held (<?= $order['retention_pct'] ?>%)</span>
                <h4 class="fw-bold text-warning mb-1">₹<?= number_format($retentionHeld, 2) ?></h4>
                <small class="text-muted">Defects Liability Holdback</small>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm p-3 bg-white">
                <span class="text-muted small d-block">TDS Deducted (<?= $order['tds_pct'] ?>%)</span>
                <h4 class="fw-bold text-info mb-1">₹<?= number_format($tdsDeducted, 2) ?></h4>
                <small class="text-muted">IT Act Sec 194C</small>
            </div>
        </div>
    </div>

    <!-- RA Milestone Bills Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-list-ol text-primary me-2"></i>Milestone Running Account (RA) Bills</h5>
            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#newBillModal">
                <i class="fas fa-plus me-1"></i>Add RA Bill
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Bill #</th>
                        <th>Bill Date</th>
                        <th>Measurement (MB Ref)</th>
                        <th>Gross Bill</th>
                        <th>Retention (<?= $order['retention_pct'] ?>%)</th>
                        <th>TDS (<?= $order['tds_pct'] ?>%)</th>
                        <th>Net Payable</th>
                        <th>Status</th>
                        <th>Disbursed</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bills)): ?>
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="fas fa-file-invoice-dollar fa-2x mb-2 d-block text-secondary"></i>
                                No running account bills recorded for this contract yet. Click "Submit Running Account (RA) Bill" above to record completed milestone measurements.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($bills as $b): ?>
                            <tr>
                                <td>
                                    <span class="fw-bold font-monospace text-primary"><?= htmlspecialchars($b['bill_number']) ?></span>
                                </td>
                                <td class="small text-muted font-monospace">
                                    <?= date('d M Y', strtotime($b['bill_date'])) ?>
                                </td>
                                <td>
                                    <?php if (!empty($b['measurement_ref'])): ?>
                                        <div class="fw-semibold small font-monospace"><?= htmlspecialchars($b['measurement_ref']) ?></div>
                                    <?php endif; ?>
                                    <div class="small text-muted text-truncate" style="max-width: 200px;">
                                        <?= htmlspecialchars($b['measurement_notes'] ?: 'No notes') ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold">₹<?= number_format((float)$b['gross_amount'], 2) ?></span>
                                </td>
                                <td class="text-warning font-monospace small">
                                    -₹<?= number_format((float)$b['retention_amount'], 2) ?>
                                </td>
                                <td class="text-info font-monospace small">
                                    -₹<?= number_format((float)$b['tds_amount'], 2) ?>
                                </td>
                                <td>
                                    <span class="fw-bold text-success font-monospace">₹<?= number_format((float)$b['net_payable_amount'], 2) ?></span>
                                </td>
                                <td>
                                    <?php if ($b['status'] === 'paid'): ?>
                                        <span class="badge bg-success">Paid</span>
                                    <?php elseif ($b['status'] === 'approved'): ?>
                                        <span class="badge bg-primary">Approved</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Submitted</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ((float)$b['paid_amount'] > 0): ?>
                                        <div class="fw-semibold text-dark small">₹<?= number_format((float)$b['paid_amount'], 2) ?></div>
                                        <div class="text-muted small font-monospace"><?= htmlspecialchars($b['payment_ref'] ?? '') ?></div>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <?php if ($b['status'] === 'submitted'): ?>
                                            <form method="POST" action="/admin/work-orders/<?= $order['id'] ?>/bills/<?= $b['id'] ?>/approve" class="d-inline" onsubmit="return confirm('Approve this RA bill for payout?');">
                                                <?= \App\Core\Security::csrfField() ?>
                                                <button type="submit" class="btn btn-outline-success btn-sm">
                                                    <i class="fas fa-check me-1"></i>Approve
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if ($b['status'] !== 'paid'): ?>
                                            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#payModal<?= $b['id'] ?>">
                                                <i class="fas fa-money-check-alt me-1"></i>Disburse
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>

                            <!-- Disburse Payment Modal -->
                            <div class="modal fade" id="payModal<?= $b['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <form method="POST" action="/admin/work-orders/<?= $order['id'] ?>/bills/<?= $b['id'] ?>/disburse" class="modal-content">
                                        <?= \App\Core\Security::csrfField() ?>
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold">Disburse Payout: Bill #<?= htmlspecialchars($b['bill_number']) ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="p-3 bg-light rounded-3 mb-3">
                                                <div class="d-flex justify-content-between mb-1">
                                                    <span>Gross Bill:</span>
                                                    <span class="fw-bold">₹<?= number_format((float)$b['gross_amount'], 2) ?></span>
                                                </div>
                                                <div class="d-flex justify-content-between mb-1 text-warning">
                                                    <span>Retention Holdback (<?= $order['retention_pct'] ?>%):</span>
                                                    <span>-₹<?= number_format((float)$b['retention_amount'], 2) ?></span>
                                                </div>
                                                <div class="d-flex justify-content-between mb-1 text-info">
                                                    <span>TDS Sec 194C (<?= $order['tds_pct'] ?>%):</span>
                                                    <span>-₹<?= number_format((float)$b['tds_amount'], 2) ?></span>
                                                </div>
                                                <div class="d-flex justify-content-between border-top pt-2 fw-bold text-success fs-6">
                                                    <span>Net Payable:</span>
                                                    <span>₹<?= number_format((float)$b['net_payable_amount'], 2) ?></span>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">Disbursement Amount (₹)</label>
                                                <input type="number" step="0.01" name="paid_amount" class="form-control" value="<?= (float)$b['net_payable_amount'] ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">Bank UTR / Cheque Reference <span class="text-danger">*</span></label>
                                                <input type="text" name="payment_ref" class="form-control" placeholder="e.g. UTR-HDFC-2026-987654" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-success"><i class="fas fa-check-circle me-1"></i>Confirm Disbursement</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: New RA Bill -->
<div class="modal fade" id="newBillModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="/admin/work-orders/<?= $order['id'] ?>/bills/store" class="modal-content">
            <?= \App\Core\Security::csrfField() ?>
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-file-invoice text-primary me-2"></i>Record Running Account (RA) Bill</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Bill Number</label>
                    <input type="text" name="bill_number" class="form-control" placeholder="e.g. RA-01" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Bill Date</label>
                    <input type="date" name="bill_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label small fw-bold">Gross Milestone Value (₹) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="gross_amount" id="billGross" class="form-control form-control-lg fw-bold" placeholder="500000" required>
                    <small class="text-muted">Retention (<?= $order['retention_pct'] ?>%) and TDS (<?= $order['tds_pct'] ?>%) will be calculated automatically.</small>
                </div>
                <div class="col-12">
                    <label class="form-label small fw-bold">Measurement Sheet / Book (MB) Ref</label>
                    <input type="text" name="measurement_ref" class="form-control" placeholder="e.g. MB-04 / Page 12-14 (Rft 500 boundary wall completed)">
                </div>
                <div class="col-12">
                    <label class="form-label small fw-bold">Site Engineer Verification Notes</label>
                    <textarea name="measurement_notes" rows="2" class="form-control" placeholder="Work verified on site by Er. Ajay Sharma. Quality check passed..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Record Bill</button>
            </div>
        </form>
    </div>
</div>
