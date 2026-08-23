<?php
$rank = $rank ?? [];
$base = defined('BASE_URL') ? BASE_URL : '';
$id   = (int)($rank['id'] ?? 0);
?>
<div class="container-fluid py-4" class="style-28388">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="m-0"><i class="fas fa-edit me-2"></i>Edit Rank: <?= htmlspecialchars($rank['rank_name'] ?? '') ?></h4>
        <a href="<?= $base ?>/admin/network/ranks" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="<?= $base ?>/admin/network/ranks/<?= $id ?>/update">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                <div class="alert alert-warning py-2">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Slug <code><?= htmlspecialchars($rank['rank_slug'] ?? '') ?></code> cannot be changed (used as key in commission engine).
                    Only values below can be edited.
                </div>

                <!-- Basic Rank Info -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="rank_name">Rank Name</label>
                        <input type="text" id="rank_name" name="rank_name" class="form-control"
                               value="<?= htmlspecialchars($rank['rank_name'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold" for="sort_order">Sort Order</label>
                        <input type="number" id="sort_order" name="sort_order" class="form-control"
                               value="<?= (int)($rank['sort_order'] ?? 0) ?>" min="0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold" for="is_active">Status</label>
                        <select id="is_active" name="is_active" class="form-select">
                            <option value="1" <?= ($rank['is_active'] ?? 1) ? 'selected' : '' ?>>Active</option>
                            <option value="0" <?= !($rank['is_active'] ?? 1) ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                </div>

                <!-- GBV Thresholds -->
                <hr>
                <h6 class="text-muted mb-3">Business Volume Thresholds (₹)</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label" for="min_gbv">Minimum GBV to Qualify (₹)</label>
                        <input type="number" id="min_gbv" name="min_gbv" class="form-control"
                               value="<?= (float)($rank['min_gbv'] ?? 0) ?>" min="0" step="1" required>
                        <small class="text-muted">1 Lakh = 100000, 1 Crore = 10000000</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="max_gbv">Maximum GBV (₹) — 0 means No Limit</label>
                        <input type="number" id="max_gbv" name="max_gbv" class="form-control"
                               value="<?= (float)($rank['max_gbv'] ?? 0) ?>" min="0" step="1">
                        <small class="text-muted">Set 0 for the highest rank (no upper limit)</small>
                    </div>
                </div>

                <!-- Commission Rate -->
                <hr>
                <h6 class="text-muted mb-3">Commission & Earnings</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold" for="commission_rate">Commission Rate (%) — Track A</label>
                        <input type="number" id="commission_rate" name="commission_rate" class="form-control"
                               value="<?= (float)($rank['commission_rate'] ?? 0) ?>" min="0" max="20" step="0.01" required>
                        <small class="text-muted">Max 20% (hard cap). This is the SLAB rate.</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="leadership_bonus_pct">Leadership Bonus (%)</label>
                        <input type="number" id="leadership_bonus_pct" name="leadership_bonus_pct" class="form-control"
                               value="<?= (float)($rank['leadership_bonus_pct'] ?? 0) ?>" min="0" max="5" step="0.01">
                        <small class="text-muted">Additional override bonus % for leadership ranks</small>
                    </div>
                </div>

                <!-- Royalty Settings -->
                <hr>
                <h6 class="text-muted mb-3">Royalty Pool Settings (for Royalty Director+ ranks)</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label" for="royalty_eligible">Royalty Pool Eligible</label>
                        <select id="royalty_eligible" name="royalty_eligible" class="form-select">
                            <option value="0" <?= !($rank['royalty_eligible'] ?? 0) ? 'selected' : '' ?>>No</option>
                            <option value="1" <?= ($rank['royalty_eligible'] ?? 0) ? 'selected' : '' ?>>Yes</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="royalty_pool_share_pct">Pool Share % (of monthly pool)</label>
                        <input type="number" id="royalty_pool_share_pct" name="royalty_pool_share_pct" class="form-control"
                               value="<?= (float)($rank['royalty_pool_share_pct'] ?? 0) ?>" min="0" max="100" step="0.0001">
                        <small class="text-muted">e.g. 2.00 = this rank class gets 2% of total royalty pool</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="profit_share_eligible">Profit Share / Shareholder Level</label>
                        <select id="profit_share_eligible" name="profit_share_eligible" class="form-select">
                            <option value="0" <?= !($rank['profit_share_eligible'] ?? 0) ? 'selected' : '' ?>>No</option>
                            <option value="1" <?= ($rank['profit_share_eligible'] ?? 0) ? 'selected' : '' ?>>Yes (Shareholder)</option>
                        </select>
                    </div>
                </div>

                <!-- Reward Settings -->
                <hr>
                <h6 class="text-muted mb-3">Target Achievement Reward</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-5">
                        <label class="form-label" for="reward_name">Reward Name</label>
                        <input type="text" id="reward_name" name="reward_name" class="form-control"
                               value="<?= htmlspecialchars($rank['reward_name'] ?? '') ?>"
                               placeholder="e.g. Motorcycle Bonus">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="reward_value">Reward Value (₹)</label>
                        <input type="number" id="reward_value" name="reward_value" class="form-control"
                               value="<?= (float)($rank['reward_value'] ?? 0) ?>" min="0" step="0.01">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="reward_description">Reward Description</label>
                        <input type="text" id="reward_description" name="reward_description" class="form-control"
                               value="<?= htmlspecialchars($rank['reward_description'] ?? '') ?>"
                               placeholder="e.g. Motorcycle or equivalent cash">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100">
                    <i class="fas fa-save me-2"></i>Save Rank Changes
                </button>
            </form>
        </div>
    </div>
</div>
