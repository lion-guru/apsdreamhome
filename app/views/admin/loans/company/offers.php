<?php
$offers = $offers ?? [];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-tags me-2 text-primary"></i>Loan Offers</h2>
        <div>
            <a href="<?= BASE_URL ?>/admin/company-loans" class="btn btn-outline-secondary btn-sm me-1"><i class="fas fa-arrow-left me-1"></i>Back</a>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#offerModal"><i class="fas fa-plus me-1"></i>New Offer</button>
        </div>
    </div>

    <div class="row g-4">
        <?php if (empty($offers)): ?>
            <div class="col-12">
                <div class="text-center text-muted py-5">
                    <i class="fas fa-tag fa-3x mb-3"></i>
                    <p>No loan offers defined yet. Create your first promotional offer.</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($offers as $o): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="aps-cp-card h-100">
                        <div class="aps-cp-card-header d-flex justify-content-between">
                            <span><i class="fas fa-<?= $o['offer_type'] === 'interest_free' ? 'star' : 'percentage' ?> me-1 text-warning"></i><?= htmlspecialchars($o['name'] ?? '') ?></span>
                            <span class="badge bg-<?= $o['is_active'] ? 'success' : 'secondary' ?>"><?= $o['is_active'] ? 'Active' : 'Inactive' ?></span>
                        </div>
                        <div class="aps-cp-card-body">
                            <p class="small text-muted"><?= htmlspecialchars($o['description'] ?? '') ?></p>
                            <table class="table table-sm mb-0">
                                <tr><td>Type</td><td><strong><?= str_replace('_', ' ', ucfirst($o['offer_type'] ?? '')) ?></strong></td></tr>
                                <tr><td>Interest-Free Months</td><td><strong><?= (int)($o['interest_free_months'] ?? 0) ?></strong></td></tr>
                                <tr><td>Max Tenure</td><td><?= (int)($o['max_tenure_months'] ?? 0) > 0 ? ($o['max_tenure_months'] . ' months') : 'Unlimited' ?></td></tr>
                                <tr><td>Max Amount</td><td><?= (float)($o['max_amount'] ?? 0) > 0 ? '₹' . number_format($o['max_amount'] / 100000, 1) . 'L' : 'Unlimited' ?></td></tr>
                                <tr><td>Valid</td><td><small><?= $o['valid_from'] ? date('d/m/Y', strtotime($o['valid_from'])) : 'Open' ?> - <?= $o['valid_until'] ? date('d/m/Y', strtotime($o['valid_until'])) : 'Open' ?></small></td></tr>
                            </table>
                            <form method="POST" action="<?= BASE_URL ?>/admin/company-loans/offers/<?= $o['id'] ?>/update" class="mt-2">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                <input type="hidden" name="is_active" value="<?= $o['is_active'] ? 0 : 1 ?>">
                                <button type="submit" class="btn btn-sm btn-outline-<?= $o['is_active'] ? 'danger' : 'success' ?> w-100">
                                    <i class="fas fa-<?= $o['is_active'] ? 'ban' : 'check' ?> me-1"></i><?= $o['is_active'] ? 'Deactivate' : 'Activate' ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Create Offer Modal -->
<div class="modal fade" id="offerModal" tabindex="-1">
    <div class="modal-dialog modal-lg"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Create Loan Offer</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form method="POST" action="<?= BASE_URL ?>/admin/company-loans/offers/create">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-8">
                        <label class="form-label">Offer Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Type</label>
                        <select name="offer_type" class="form-select">
                            <option value="interest_free">Interest-Free</option>
                            <option value="reduced_rate">Reduced Rate</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="2"></textarea>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Interest-Free Months</label>
                        <input type="number" name="interest_free_months" class="form-control" value="0" min="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Max Tenure (months)</label>
                        <input type="number" name="max_tenure_months" class="form-control" value="0" min="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Max Amount (₹)</label>
                        <input type="number" name="max_amount" class="form-control" value="0" min="0">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Discount %</label>
                        <input type="number" name="discount_percent" class="form-control" value="0" min="0" max="100" step="0.5">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Valid From</label>
                        <input type="date" name="valid_from" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Valid Until</label>
                        <input type="date" name="valid_until" class="form-control">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Terms & Conditions</label>
                    <textarea name="terms_conditions" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Create Offer</button>
            </div>
        </form>
    </div></div>
</div>
