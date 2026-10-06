<?php
$page_title = $page_title ?? 'Referral Tiers';
$base = defined('BASE_URL') ? BASE_URL : '';
$tiers = $tiers ?? [];
$tier_counts = $tier_counts ?? [];
$csrf_token = $csrf_token ?? ($_SESSION['csrf_token'] ?? '');
?>
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="m-0"><i class="fas fa-layer-group me-2 text-info"></i>Referral Tiers</h4>
        <a href="<?= $base ?>/admin/referrals" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>

    <div class="row g-4">
        <?php foreach ($tiers as $tier): ?>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div >
                        <i class="<?= $tier['icon'] ?>"></i>
                    </div>
                    <h5 class="mt-2 mb-1"><?= $tier['label'] ?></h5>
                    <div class="text-muted small mb-3"><?= $tier['min_referrals'] ?>+ referrals needed</div>
                    
                    <div class="mb-3">
                        <div class="fw-bold">₹<?= number_format($tier['bonus_per_referral']) ?></div>
                        <div class="text-muted small">per signup</div>
                    </div>
                    <div class="mb-3">
                        <div class="fw-bold">₹<?= number_format($tier['bonus_on_booking']) ?></div>
                        <div class="text-muted small">on booking</div>
                    </div>

                    <div class="text-start">
                        <div class="small fw-bold text-muted mb-2">Perks:</div>
                        <?php foreach ($tier['perks'] as $perk): ?>
                        <div class="small"><i class="fas fa-check text-success me-1"></i><?= $perk ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="card-footer bg-white text-center">
                    <div class="small text-muted mb-2">
                        <strong><?= $tier_counts[$tier['tier']] ?? 0 ?></strong> users at this tier
                    </div>
                    <form method="POST" action="<?= $base ?>/admin/referrals/tiers/update" onsubmit="return confirm('Update <?= htmlspecialchars($tier['label']) ?> tier? Applies to future bonuses only.');">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <input type="hidden" name="tier_key" value="<?= htmlspecialchars($tier['tier']) ?>">
                        <div class="row g-1 text-start">
                            <div class="col-4"><label class="small text-muted">Min refs</label><input type="number" name="min_referrals" class="form-control form-control-sm" value="<?= (int)$tier['min_referrals'] ?>" min="0"></div>
                            <div class="col-4"><label class="small text-muted">₹/signup</label><input type="number" name="bonus_per_referral" class="form-control form-control-sm" value="<?= htmlspecialchars($tier['bonus_per_referral']) ?>" min="0" step="1"></div>
                            <div class="col-4"><label class="small text-muted">₹/booking</label><input type="number" name="bonus_on_booking" class="form-control form-control-sm" value="<?= htmlspecialchars($tier['bonus_on_booking']) ?>" min="0" step="1"></div>
                        </div>
                        <button type="submit" class="btn btn-sm btn-outline-primary w-100 mt-2">Save Tier</button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
