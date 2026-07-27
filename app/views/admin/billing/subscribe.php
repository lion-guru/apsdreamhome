<!-- Subscribe Tenant — Plan Selection & Subscription Management -->
<?php
$tenant  = $tenant ?? [];
$plans   = $plans ?? [];
$current = $current ?? [];
$base    = BASE_URL ?? '';
$cycle   = $current['billing_cycle'] ?? 'monthly';
?>
<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.subscribe-header { background: linear-gradient(135deg, #0f3460, #16213e, #1a1a2e); color: #fff; border-radius: 12px; padding: 24px; margin-bottom: 20px; }
.current-plan-card { border: 2px solid #10b981; border-radius: 12px; background: linear-gradient(135deg, #f0fdf4, #dcfce7); }
.plan-option { border: 2px solid #e5e7eb; border-radius: 12px; cursor: pointer; transition: all 0.2s; padding: 16px; }
.plan-option:hover { border-color: #6366f1; background: #eef2ff; }
.plan-option.selected { border-color: #6366f1; background: #e0e7ff; }
.plan-option input[type="radio"] { display: none; }
.cycle-toggle .btn { border-radius: 0; }
.cycle-toggle .btn:first-child { border-radius: 8px 0 0 8px; }
.cycle-toggle .btn:last-child { border-radius: 0 8px 8px 0; }
</style>

<!-- Flash messages -->
<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i><?= $_SESSION['flash_success'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>
<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-2"></i><?= $_SESSION['flash_error'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<div class="subscribe-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0"><i class="fas fa-credit-card me-2"></i>Manage Subscription</h4>
            <p class="mb-0 mt-1" style="opacity:0.85;">
                <?= htmlspecialchars($tenant['name'] ?? 'Tenant') ?> — <?= htmlspecialchars($tenant['slug'] ?? '') ?>
            </p>
        </div>
        <a href="<?= $base ?>/admin/billing" class="btn btn-outline-light btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back to Billing
        </a>
    </div>
</div>

<!-- Current Subscription -->
<?php if (!empty($current)): ?>
    <div class="card current-plan-card shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <h6 class="text-success mb-1"><i class="fas fa-check-circle me-1"></i>Current Plan</h6>
                    <h3 class="mb-0"><?= htmlspecialchars($current['plan_name'] ?? 'Free') ?></h3>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">Amount</small>
                    <strong>₹<?= number_format($current['amount'] ?? 0) ?>/<?= $cycle ?></strong>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">Renews / Expires</small>
                    <strong><?= $current['current_period_end'] ? date('d M Y', strtotime($current['current_period_end'])) : '—' ?></strong>
                </div>
                <div class="col-md-2 text-end">
                    <form method="POST" action="<?= $base ?>/admin/billing/cancel/<?= $tenant['id'] ?>" class="d-inline"
                          onsubmit="return confirm('Cancel this subscription? Tenant will be downgraded to Free plan.');">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-times me-1"></i>Cancel
                        </button>
                    </form>
                </div>
            </div>
            <?php if (!empty($current['razorpay_subscription_id'])): ?>
                <div class="mt-2">
                    <small class="text-muted">Razorpay ID: <code><?= htmlspecialchars($current['razorpay_subscription_id']) ?></code></small>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<!-- New Subscription Form -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <h6 class="mb-0"><i class="fas fa-plus-circle me-2"></i><?= $current ? 'Change Plan' : 'Activate Subscription' ?></h6>
    </div>
    <div class="card-body">
        <form method="POST" action="<?= $base ?>/admin/billing/subscribe/<?= $tenant['id'] ?>" id="subscribeForm">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

            <!-- Billing Cycle Toggle -->
            <div class="text-center mb-4">
                <div class="btn-group cycle-toggle" role="group">
                    <input type="radio" class="btn-check" name="billing_cycle" id="cycleMonthly" value="monthly" <?= $cycle === 'monthly' ? 'checked' : '' ?>>
                    <label class="btn btn-outline-primary" for="cycleMonthly">Monthly</label>
                    <input type="radio" class="btn-check" name="billing_cycle" id="cycleYearly" value="yearly" <?= $cycle === 'yearly' ? 'checked' : '' ?>>
                    <label class="btn btn-outline-primary" for="cycleYearly">Yearly <span class="badge bg-success ms-1">Save 17%</span></label>
                </div>
            </div>

            <!-- Plan Selection -->
            <div class="row">
                <?php foreach ($plans as $plan): ?>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <label class="plan-option <?= ($current['plan_id'] ?? 0) == $plan['id'] ? 'selected' : '' ?>" onclick="selectPlan(this, <?= $plan['id'] ?>)">
                            <input type="radio" name="plan_id" value="<?= $plan['id'] ?>" <?= ($current['plan_id'] ?? 0) == $plan['id'] ? 'checked' : '' ?>>
                            <div class="text-center">
                                <h6 class="mb-2"><?= htmlspecialchars($plan['name']) ?></h6>
                                <div class="plan-price <?= ($plan['slug'] ?? '') === 'free' ? 'text-secondary' : 'text-primary' ?>" style="font-size:1.5rem;">
                                    ₹<span class="price-monthly"><?= number_format($plan['price_monthly']) ?></span>
                                    <span class="price-yearly" style="display:none;"><?= number_format($plan['price_yearly']) ?></span>
                                    <small class="text-muted fs-6 cycle-label">/mo</small>
                                </div>
                                <div class="mt-2 small text-muted">
                                    <?= $plan['max_users'] ?> users · <?= $plan['max_leads'] ?> leads · <?= $plan['max_properties'] ?> properties
                                </div>
                            </div>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-3">
                <button type="submit" class="btn btn-primary btn-lg px-5" id="subscribeBtn">
                    <i class="fas fa-check me-2"></i><?= $current ? 'Change Plan' : 'Activate Subscription' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
function selectPlan(el, planId) {
    document.querySelectorAll('.plan-option').forEach(p => p.classList.remove('selected'));
    el.classList.add('selected');
    el.querySelector('input[type="radio"]').checked = true;
}

document.querySelectorAll('input[name="billing_cycle"]').forEach(r => {
    r.addEventListener('change', function() {
        const yearly = this.value === 'yearly';
        document.querySelectorAll('.price-monthly').forEach(e => e.style.display = yearly ? 'none' : '');
        document.querySelectorAll('.price-yearly').forEach(e => e.style.display = yearly ? '' : 'none');
        document.querySelectorAll('.cycle-label').forEach(e => e.textContent = yearly ? '/yr' : '/mo');
    });
});

// Trigger initial state
document.querySelector('input[name="billing_cycle"]:checked')?.dispatchEvent(new Event('change'));
</script>
