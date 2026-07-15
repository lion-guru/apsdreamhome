<?php
$incentives = $incentives ?? [];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-gift me-2 text-success"></i>Early Payment Incentives</h2>
        <div>
            <a href="<?= BASE_URL ?>/admin/company-loans" class="btn btn-outline-secondary btn-sm me-1"><i class="fas fa-arrow-left me-1"></i>Back</a>
            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#incentiveModal"><i class="fas fa-plus me-1"></i>New Incentive</button>
        </div>
    </div>

    <div class="row g-4">
        <?php if (empty($incentives)): ?>
            <div class="col-12">
                <div class="text-center text-muted py-5">
                    <i class="fas fa-gift fa-3x mb-3"></i>
                    <p>No early payment incentives defined.</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($incentives as $inc): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="aps-cp-card h-100 border-<?= $inc['is_active'] ? 'success' : 'secondary' ?>">
                        <div class="aps-cp-card-header d-flex justify-content-between">
                            <span><i class="fas fa-<?= match($inc['incentive_type'] ?? '') {
                                'interest_discount' => 'percentage', 'cashback' => 'cashback',
                                'penalty_waiver' => 'hand-holding', default => 'gift'
                            } ?> me-1 text-success"></i><?= htmlspecialchars($inc['name'] ?? '') ?></span>
                            <span class="badge bg-<?= $inc['is_active'] ? 'success' : 'secondary' ?>"><?= $inc['is_active'] ? 'Active' : 'Inactive' ?></span>
                        </div>
                        <div class="aps-cp-card-body">
                            <p class="small text-muted"><?= htmlspecialchars($inc['description'] ?? '') ?></p>
                            <table class="table table-sm mb-0">
                                <tr><td>Type</td><td><strong><?= str_replace('_', ' ', ucfirst($inc['incentive_type'] ?? '')) ?></strong></td></tr>
                                <?php if ($inc['calculation_method'] === 'percentage'): ?>
                                    <tr><td>Discount</td><td><strong class="text-success"><?= (float)($inc['discount_percent'] ?? 0) ?>% off</strong></td></tr>
                                <?php elseif ($inc['calculation_method'] === 'fixed'): ?>
                                    <tr><td>Fixed Amount</td><td><strong class="text-success">₹<?= number_format($inc['fixed_amount'] ?? 0) ?></strong></td></tr>
                                <?php endif; ?>
                                <tr><td>Min Remaining</td><td><?= $inc['min_remaining_months'] ?? 0 ?> months</td></tr>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Create Incentive Modal -->
<div class="modal fade" id="incentiveModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Create Early Payment Incentive</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form method="POST" action="<?= BASE_URL ?>/admin/company-loans/early-incentives/create">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="2"></textarea>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Incentive Type</label>
                        <select name="incentive_type" class="form-select">
                            <option value="interest_discount">Interest Discount</option>
                            <option value="cashback">Cashback</option>
                            <option value="penalty_waiver">Penalty Waiver</option>
                            <option value="partial_waiver">Partial Waiver</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Calculation Method</label>
                        <select name="calculation_method" class="form-select">
                            <option value="percentage">Percentage</option>
                            <option value="fixed">Fixed Amount</option>
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Discount %</label>
                        <input type="number" name="discount_percent" class="form-control" value="0" min="0" max="100" step="0.5">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fixed Amount (₹)</label>
                        <input type="number" name="fixed_amount" class="form-control" value="0" min="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Min Remaining Months</label>
                        <input type="number" name="min_remaining_months" class="form-control" value="0" min="0">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i>Create Incentive</button>
            </div>
        </form>
    </div></div>
</div>
