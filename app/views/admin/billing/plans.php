<!-- Billing Plans — Subscription Plan Management -->
<?php
$plans   = $plans ?? [];
$by_plan = $by_plan ?? [];
$base    = BASE_URL ?? '';
?>
<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.plans-header { background: linear-gradient(135deg, #1a1a2e, #16213e, #0f3460); color: #fff; border-radius: 12px; padding: 24px; margin-bottom: 20px; }
.plan-card { border: none; border-radius: 12px; overflow: hidden; transition: transform 0.2s; height: 100%; }
.plan-card:hover { transform: translateY(-3px); box-shadow: 0 6px 24px rgba(0,0,0,0.12); }
.plan-card .card-header { border-bottom: none; padding-bottom: 0; }
.plan-price { font-size: 2rem; font-weight: 700; }
.plan-feature { font-size: 0.85rem; color: #555; }
.plan-feature i { width: 18px; text-align: center; }
.plan-tenants { font-size: 0.75rem; }
</style>

<div class="plans-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0"><i class="fas fa-tags me-2"></i>Subscription Plans</h4>
            <p class="mb-0 mt-1 style-91394">Manage pricing, limits, and features per plan</p>
        </div>
        <a href="<?= $base ?>/admin/billing" class="btn btn-outline-light btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back to Billing
        </a>
    </div>
</div>

<!-- Plan Summary Stats -->
<div class="row mb-4">
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <div class="text-primary mb-2"><i class="fas fa-layer-group fa-2x"></i></div>
                <h3 class="mb-1"><?= count($plans) ?></h3>
                <small class="text-muted">Total Plans</small>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <div class="text-success mb-2"><i class="fas fa-check-double fa-2x"></i></div>
                <h3 class="mb-1"><?= count(array_filter($plans, fn($p) => ($p['is_active'] ?? 1) == 1)) ?></h3>
                <small class="text-muted">Active Plans</small>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <div class="text-info mb-2"><i class="fas fa-rupee-sign fa-2x"></i></div>
                <h3 class="mb-1">₹<?= number_format(max(array_column($plans, 'price_monthly'))) ?></h3>
                <small class="text-muted">Highest Plan</small>
            </div>
        </div>
    </div>
</div>

<!-- Plan Cards -->
<div class="row">
    <?php foreach ($plans as $plan): ?>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card plan-card shadow-sm">
                <div class="card-header <?= ($plan['slug'] ?? '') === 'free' ? 'bg-secondary' : 'bg-primary' ?> text-white text-center">
                    <h6 class="mb-0"><?= htmlspecialchars($plan['name'] ?? '') ?></h6>
                </div>
                <div class="card-body text-center">
                    <div class="plan-price <?= ($plan['slug'] ?? '') === 'free' ? 'text-secondary' : 'text-primary' ?>">
                        ₹<?= number_format($plan['price_monthly']) ?>
                        <small class="text-muted fs-6 fw-normal">/mo</small>
                    </div>
                    <?php if (($plan['price_yearly'] ?? 0) > 0): ?>
                        <small class="text-muted">₹<?= number_format($plan['price_yearly']) ?>/yr (save <?= round((1 - $plan['price_yearly'] / ($plan['price_monthly'] * 12)) * 100) ?>%)</small>
                    <?php endif; ?>
                    <hr>
                    <div class="text-start">
                        <div class="plan-feature mb-2"><i class="fas fa-users text-primary me-2"></i><strong><?= $plan['max_users'] ?></strong> Users</div>
                        <div class="plan-feature mb-2"><i class="fas fa-bullseye text-primary me-2"></i><strong><?= $plan['max_leads'] ?></strong> Leads</div>
                        <div class="plan-feature mb-2"><i class="fas fa-home text-primary me-2"></i><strong><?= $plan['max_properties'] ?></strong> Properties</div>
                        <div class="plan-feature mb-2"><i class="fas fa-database text-primary me-2"></i><strong><?= $plan['storage_limit_mb'] ?></strong> MB Storage</div>
                        <?php if ($plan['max_associates'] ?? 0): ?>
                            <div class="plan-feature mb-2"><i class="fas fa-handshake text-primary me-2"></i><strong><?= $plan['max_associates'] ?></strong> Associates</div>
                        <?php endif; ?>
                        <hr>
                        <div class="plan-feature mb-1"><?= ($plan['api_access'] ?? 0) ? '<i class="fas fa-check text-success me-2"></i>API Access' : '<i class="fas fa-times text-muted me-2"></i>No API' ?></div>
                        <div class="plan-feature mb-1"><?= ($plan['white_label'] ?? 0) ? '<i class="fas fa-check text-success me-2"></i>White Label' : '<i class="fas fa-times text-muted me-2"></i>No White Label' ?></div>
                        <div class="plan-feature mb-1"><?= ($plan['mlm_engine'] ?? 0) ? '<i class="fas fa-check text-success me-2"></i>MLM Engine' : '<i class="fas fa-times text-muted me-2"></i>No MLM' ?></div>
                        <div class="plan-feature mb-1"><?= ($plan['ai_features'] ?? 0) ? '<i class="fas fa-check text-success me-2"></i>AI Features' : '<i class="fas fa-times text-muted me-2"></i>No AI' ?></div>
                        <div class="plan-feature mb-1"><?= ($plan['mobile_app'] ?? 0) ? '<i class="fas fa-check text-success me-2"></i>Mobile App' : '<i class="fas fa-times text-muted me-2"></i>No Mobile App' ?></div>
                    </div>
                </div>
                <div class="card-footer bg-white text-center">
                    <?php
                    $tenantCount = 0;
                    foreach ($by_plan as $bp) {
                        if ($bp['slug'] === ($plan['slug'] ?? '')) { $tenantCount = $bp['count']; break; }
                    }
                    ?>
                    <span class="plan-tenants text-muted"><?= $tenantCount ?> tenant<?= $tenantCount !== 1 ? 's' : '' ?></span>
                    <span class="badge <?= ($plan['is_active'] ?? 1) ? 'bg-success' : 'bg-secondary' ?> ms-2"><?= ($plan['is_active'] ?? 1) ? 'Active' : 'Inactive' ?></span>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Razorpay Config Status -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-plug me-2"></i>Payment Gateway Status</h6></div>
    <div class="card-body">
        <?php
        $razorpayConfigured = false;
        try {
            $svc = new \App\Services\Gateway\RazorpayService();
            $razorpayConfigured = $svc->isConfigured();
        } catch (\Throwable $e) { error_log(__METHOD__ . ': ' . $e->getMessage()); }
        ?>
        <?php if ($razorpayConfigured): ?>
            <div class="alert alert-success mb-0">
                <i class="fas fa-check-circle me-2"></i>Razorpay is configured and ready. Subscriptions will process via Razorpay.
            </div>
        <?php else: ?>
            <div class="alert alert-warning mb-0">
                <i class="fas fa-exclamation-triangle me-2"></i><strong>Razorpay not configured.</strong> Add API keys in
                <a href="<?= $base ?>/admin/settings">Site Settings â†’ Payment Gateway</a> to enable subscriptions.
                Free plan subscriptions work without Razorpay.
            </div>
        <?php endif; ?>
    </div>
</div>
