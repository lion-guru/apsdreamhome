<?php
/**
 * Colony Society Maintenance Dues View
 */
$dues = $dues ?? [];
$colonies = $colonies ?? [];
$stats = $stats ?? [];
$filters = $filters ?? [];
$base = defined('BASE_URL') ? BASE_URL : '';
$e = function ($v) { return htmlspecialchars((string)($v ?? '')); };
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1"><i class="fas fa-tools text-primary me-2"></i>Colony Society Maintenance Dues</h4>
        <p class="text-muted mb-0">Track monthly security, park, street light &amp; infrastructure maintenance billing</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#batchGenerateModal">
            <i class="fas fa-file-invoice me-1"></i> Batch Generate Bills
        </button>
    </div>
</div>

<!-- Quick Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
            <span class="text-muted small">Total Bills</span>
            <h4 class="mb-0 fw-bold"><?= number_format($stats['total_bills'] ?? 0) ?></h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
            <span class="text-muted small">Total Billed</span>
            <h4 class="mb-0 fw-bold text-dark">₹<?= number_format($stats['total_billed'] ?? 0, 2) ?></h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
            <span class="text-muted small">Collected</span>
            <h4 class="mb-0 fw-bold text-success">₹<?= number_format($stats['total_collected'] ?? 0, 2) ?></h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
            <span class="text-muted small">Pending Receivables</span>
            <h4 class="mb-0 fw-bold text-danger">₹<?= number_format($stats['total_pending'] ?? 0, 2) ?></h4>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body">
        <form method="GET" action="<?= $base ?>/admin/maintenance-dues" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">Colony / Township</label>
                <select name="colony_id" class="form-select">
                    <option value="">All Colonies</option>
                    <?php foreach ($colonies as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($filters['colony_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                            <?= $e($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Billing Month</label>
                <input type="month" name="bill_month" class="form-control" value="<?= $e($filters['bill_month'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="paid" <?= ($filters['status'] ?? '') === 'paid' ? 'selected' : '' ?>>Paid</option>
                    <option value="partially_paid" <?= ($filters['status'] ?? '') === 'partially_paid' ? 'selected' : '' ?>>Partially Paid</option>
                    <option value="waived" <?= ($filters['status'] ?? '') === 'waived' ? 'selected' : '' ?>>Waived</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Search Plot / Customer</label>
                <input type="text" name="search" class="form-control" placeholder="Plot number, name, phone" value="<?= $e($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i>Filter</button>
                <a href="<?= $base ?>/admin/maintenance-dues" class="btn btn-outline-secondary"><i class="fas fa-undo"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Dues Table -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small">
                    <tr>
                        <th>Bill #</th>
                        <th>Colony &amp; Plot</th>
                        <th>Customer</th>
                        <th>Month</th>
                        <th>Sqft &amp; Rate</th>
                        <th class="text-end">Amount Due</th>
                        <th class="text-end">Paid</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($dues)): ?>
                    <tr>
                        <td colspan="9" class="text-center text-muted py-5">
                            <i class="fas fa-file-invoice fa-2x mb-2 d-block opacity-25"></i>
                            No maintenance dues found. Click "Batch Generate Bills" above to issue colony maintenance.
                        </td>
                    </tr>
                <?php else: foreach ($dues as $d): ?>
                    <tr>
                        <td class="font-monospace small">#<?= (int)$d['id'] ?></td>
                        <td>
                            <div class="fw-semibold"><?= $e($d['colony_name'] ?: 'Colony #' . $d['colony_id']) ?></div>
                            <span class="badge bg-light text-dark border">Plot <?= $e($d['plot_number'] ?: ('#' . $d['plot_id'])) ?></span>
                        </td>
                        <td>
                            <div class="small fw-medium"><?= $e($d['customer_name'] ?: 'Customer #' . $d['customer_id']) ?></div>
                            <?php if (!empty($d['customer_phone'])): ?>
                                <div class="text-muted small"><?= $e($d['customer_phone']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="small fw-semibold"><?= date('M Y', strtotime($d['bill_month'] . '-01')) ?></td>
                        <td class="small">
                            <?= number_format((float)$d['area_sqft']) ?> sqft @ ₹<?= number_format((float)$d['rate_per_sqft'], 2) ?>
                        </td>
                        <td class="text-end fw-bold">₹<?= number_format((float)$d['amount_due'], 2) ?></td>
                        <td class="text-end text-success fw-semibold">₹<?= number_format((float)$d['paid_amount'], 2) ?></td>
                        <td>
                            <?php
                            $badgeClass = match ($d['status']) {
                                'paid' => 'bg-success',
                                'partially_paid' => 'bg-warning text-dark',
                                'waived' => 'bg-secondary',
                                default => 'bg-danger'
                            };
                            ?>
                            <span class="badge <?= $badgeClass ?>"><?= ucfirst(str_replace('_', ' ', $d['status'])) ?></span>
                        </td>
                        <td class="text-center">
                            <?php if ($d['status'] !== 'paid'): ?>
                                <button type="button" class="btn btn-sm btn-outline-success collect-btn"
                                        data-bs-toggle="modal" data-bs-target="#collectModal"
                                        data-id="<?= $d['id'] ?>"
                                        data-plot="<?= $e($d['plot_number'] ?: ('#' . $d['plot_id'])) ?>"
                                        data-due="<?= max(0.00, (float)$d['amount_due'] - (float)$d['paid_amount']) ?>">
                                    <i class="fas fa-check-circle me-1"></i>Collect
                                </button>
                            <?php else: ?>
                                <a href="<?= $base ?>/admin/maintenance-dues/receipt/<?= $d['id'] ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                    <i class="fas fa-receipt me-1"></i>Receipt
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Batch Generate -->
<div class="modal fade" id="batchGenerateModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?= $base ?>/admin/maintenance-dues/generate" class="modal-content">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-invoice me-2"></i>Batch Generate Maintenance Bills</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Colony / Township *</label>
                    <select name="colony_id" class="form-select" required>
                        <option value="">Select Colony</option>
                        <?php foreach ($colonies as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= $e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Billing Month *</label>
                        <input type="month" name="bill_month" class="form-control" value="<?= date('Y-m') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Rate Per Sqft (₹/sqft) *</label>
                        <input type="number" step="0.10" min="0.10" name="rate_per_sqft" class="form-control" value="1.00" required>
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small">Water Supply (₹)</label>
                        <input type="number" step="1" min="0" name="water_charges" class="form-control" value="0.00">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Security Guard (₹)</label>
                        <input type="number" step="1" min="0" name="security_charges" class="form-control" value="0.00">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small">Due Date</label>
                    <input type="date" name="due_date" class="form-control" value="<?= date('Y-m-15', strtotime('+1 month')) ?>">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-bolt me-1"></i>Generate All</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Collect Payment -->
<div class="modal fade" id="collectModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?= $base ?>/admin/maintenance-dues/collect" class="modal-content">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <input type="hidden" name="due_id" id="modalDueId" value="">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-cash-register me-2"></i>Record Maintenance Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2 small mb-3">
                    Plot: <strong id="modalPlotNum">-</strong> | Balance: <strong id="modalDueAmount">-</strong>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Amount Paid (₹) *</label>
                    <input type="number" step="0.01" min="1" name="amount" id="modalAmountInput" class="form-control" required>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Payment Mode</label>
                        <select name="payment_mode" class="form-select">
                            <option value="upi">UPI / QR</option>
                            <option value="cash">Cash Counter</option>
                            <option value="bank_transfer">Bank NEFT/RTGS</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Transaction / UTR Ref</label>
                        <input type="text" name="transaction_ref" class="form-control" placeholder="UPI Ref / UTR / Cheque #">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small">Notes</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Optional remark"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-check me-1"></i>Confirm &amp; Issue Receipt</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var collectModal = document.getElementById('collectModal');
    if (collectModal) {
        collectModal.addEventListener('show.bs.modal', function(event) {
            var btn = event.relatedTarget;
            document.getElementById('modalDueId').value = btn.getAttribute('data-id');
            document.getElementById('modalPlotNum').textContent = btn.getAttribute('data-plot');
            var dueAmt = parseFloat(btn.getAttribute('data-due') || 0);
            document.getElementById('modalDueAmount').textContent = '₹' + dueAmt.toFixed(2);
            document.getElementById('modalAmountInput').value = dueAmt.toFixed(2);
        });
    }
});
</script>
