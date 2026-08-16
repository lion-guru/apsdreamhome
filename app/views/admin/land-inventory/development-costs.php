<?php
$colony = $colony ?? [];
$costs = $costs ?? [];
$summary = $summary ?? ['total_acquisition'=>0, 'total_development'=>0, 'total_land'=>0, 'grand_total'=>0, 'count'=>0];
$colonyId = (int)($colony['id'] ?? 0);
?>
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-hammer text-primary me-2"></i>Development Costs — <?= htmlspecialchars($colony['name'] ?? 'Colony #'.$colonyId) ?></h4>
            <small class="text-muted">Plot Inventory Development Ledger</small>
        </div>
        <a href="<?= BASE_URL ?>/admin/colonies/show/<?= $colonyId ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back to Colony
        </a>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="aps-cp-card text-center">
                <div class="aps-cp-card-body">
                    <h5 class="text-primary mb-0">₹<?= number_format((float)$summary['total_acquisition']) ?></h5>
                    <small class="text-muted">Land Acquisition</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card text-center">
                <div class="aps-cp-card-body">
                    <h5 class="text-info mb-0">₹<?= number_format((float)$summary['total_development']) ?></h5>
                    <small class="text-muted">Development Costs</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card text-center">
                <div class="aps-cp-card-body">
                    <h5 class="text-success mb-0">₹<?= number_format((float)$summary['total_land']) ?></h5>
                    <small class="text-muted">Land Cost Total</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card text-center">
                <div class="aps-cp-card-body">
                    <h5 class="text-warning mb-0">₹<?= number_format((float)$summary['grand_total']) ?></h5>
                    <small class="text-muted">Grand Total (<?= (int)$summary['count'] ?> entries)</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-plus-circle me-2"></i>Add Cost Entry</div>
                <div class="aps-cp-card-body">
                    <form method="post" action="<?= BASE_URL ?>/admin/land-inventory/colonies/<?= $colonyId ?>/costs/store">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <div class="mb-2">
                            <label class="form-label small">Cost Type <span class="text-danger">*</span></label>
                            <select name="cost_type" class="form-select form-select-sm" required>
                                <option value="acquisition">Acquisition</option>
                                <option value="development">Development</option>
                                <option value="infrastructure">Infrastructure</option>
                                <option value="legal">Legal</option>
                                <option value="marketing">Marketing</option>
                                <option value="approval">Approvals / NOC</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Category</label>
                            <input type="text" name="category" class="form-control form-control-sm" placeholder="e.g. Road work, Drainage">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Description <span class="text-danger">*</span></label>
                            <input type="text" name="description" class="form-control form-control-sm" required>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small">Amount (₹) <span class="text-danger">*</span></label>
                                <input type="number" name="amount" step="0.01" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small">Date <span class="text-danger">*</span></label>
                                <input type="date" name="expense_date" class="form-control form-control-sm" required value="<?= date('Y-m-d') ?>">
                            </div>
                        </div>
                        <div class="mb-2 mt-2">
                            <label class="form-label small">Vendor / Payee</label>
                            <input type="text" name="vendor_name" class="form-control form-control-sm">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Invoice #</label>
                            <input type="text" name="invoice_number" class="form-control form-control-sm">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Per Sqft Cost (auto-calc if blank)</label>
                            <input type="number" name="cost_per_sqft" step="0.01" class="form-control form-control-sm">
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="is_capitalized" value="1" id="cap" checked>
                            <label class="form-check-label" for="cap">Capitalize to plot cost</label>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Notes</label>
                            <textarea name="notes" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-save me-1"></i>Add Cost
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-list me-2"></i>Cost Ledger (<?= count($costs) ?>)</div>
                <div class="aps-cp-card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr><th>Date</th><th>Type</th><th>Description</th><th>Vendor</th><th>Amount</th><th>Per Sqft</th><th>Cap</th></tr>
                            </thead>
                            <tbody>
                            <?php foreach ($costs as $c): ?>
                                <tr>
                                    <td><small><?= htmlspecialchars($c['expense_date'] ?? '—') ?></small></td>
                                    <td>
                                        <span class="badge bg-<?= ($c['cost_type'] ?? '') === 'acquisition' ? 'primary' : (($c['cost_type'] ?? '') === 'development' ? 'info' : 'secondary') ?>">
                                            <?= htmlspecialchars(ucfirst($c['cost_type'] ?? '')) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($c['description'] ?? '—') ?></strong>
                                        <?php if (!empty($c['category'])): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($c['category'] ?? '') ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><small><?= htmlspecialchars($c['vendor_name'] ?? '—') ?></small></td>
                                    <td>₹<?= number_format((float)($c['amount'] ?? 0)) ?></td>
                                    <td>₹<?= number_format((float)($c['cost_per_sqft'] ?? 0), 2) ?></td>
                                    <td><?= !empty($c['is_capitalized']) ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-minus text-muted"></i>' ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($costs)): ?>
                                <tr><td colspan="7" class="text-center text-muted py-4">No costs recorded yet.</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
