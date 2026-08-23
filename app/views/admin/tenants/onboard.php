<!-- Tenant Onboarding Wizard — 5-Step Guided Setup -->
<?php
$plans      = $plans ?? [];
$step       = $step ?? 1;
$wizardData = $wizardData ?? [];
$base       = BASE_URL ?? '';

$steps = [
    ['num' => 1, 'icon' => 'fas fa-building',       'label' => 'Company Info'],
    ['num' => 2, 'icon' => 'fas fa-palette',         'label' => 'Branding'],
    ['num' => 3, 'icon' => 'fas fa-tags',             'label' => 'Select Plan'],
    ['num' => 4, 'icon' => 'fas fa-user-plus',       'label' => 'Invite Users'],
    ['num' => 5, 'icon' => 'fas fa-rocket',          'label' => 'Review & Launch'],
];
?>
<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.onboard-header { background: linear-gradient(135deg, #1a1a2e, #16213e, #0f3460); color: #fff; border-radius: 12px; padding: 24px; margin-bottom: 20px; }
.step-progress { display: flex; justify-content: center; gap: 0; margin-bottom: 30px; }
.step-item { display: flex; align-items: center; gap: 8px; padding: 8px 16px; border-radius: 8px; font-size: 0.85rem; color: #94a3b8; transition: all 0.3s; }
.step-item.active { background: #6366f1; color: #fff; font-weight: 600; }
.step-item.done { color: #10b981; }
.step-item .step-num { width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; border: 2px solid #94a3b8; }
.step-item.active .step-num { background: #fff; color: #6366f1; border-color: #fff; }
.step-item.done .step-num { background: #10b981; color: #fff; border-color: #10b981; }
.step-connector { width: 40px; height: 2px; background: #e5e7eb; align-self: center; }
.step-item.done + .step-connector { background: #10b981; }
.wizard-card { border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
.color-swatch { width: 40px; height: 40px; border-radius: 8px; border: 2px solid #e5e7eb; cursor: pointer; }
.plan-wizard-card { border: 2px solid #e5e7eb; border-radius: 12px; cursor: pointer; transition: all 0.2s; padding: 16px; text-align: center; }
.plan-wizard-card:hover { border-color: #6366f1; background: #eef2ff; }
.plan-wizard-card.selected { border-color: #6366f1; background: #e0e7ff; }
.review-table td { padding: 10px 16px; border-bottom: 1px solid #f1f5f9; }
.review-table .label { color: #64748b; font-weight: 500; }
</style>

<!-- Flash messages -->
<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?= $_SESSION['flash_success'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>
<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle me-2"></i><?= $_SESSION['flash_error'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<div class="onboard-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0"><i class="fas fa-magic me-2"></i>Tenant Onboarding Wizard</h4>
            <p class="mb-0 mt-1 style-91394">Set up a new tenant in 5 easy steps</p>
        </div>
        <a href="<?= $base ?>/admin/tenants" class="btn btn-outline-light btn-sm"><i class="fas fa-times me-1"></i>Cancel</a>
    </div>
</div>

<!-- Step Progress Bar -->
<div class="step-progress">
    <?php foreach ($steps as $i => $s): ?>
        <?php if ($i > 0): ?><div class="step-connector <?= $step > $s['num'] ? 'done' : '' ?>"></div><?php endif; ?>
        <div class="step-item <?= $step == $s['num'] ? 'active' : ($step > $s['num'] ? 'done' : '') ?>">
            <span class="step-num"><?= $step > $s['num'] ? '<i class="fas fa-check"></i>' : $s['num'] ?></span>
            <span class="d-none d-md-inline"><?= $s['label'] ?></span>
        </div>
    <?php endforeach; ?>
</div>

<!-- Step Content -->
<div class="card wizard-card">
    <div class="card-body p-4">

        <!-- STEP 1: Company Info -->
        <?php if ($step === 1): ?>
            <h5 class="mb-4"><i class="fas fa-building me-2 text-primary"></i>Company Information</h5>
            <form method="POST" action="<?= $base ?>/admin/tenants/onboard/save">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <input type="hidden" name="step" value="1">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Company Name *</label>
                        <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($wizardData['name'] ?? '') ?>" placeholder="e.g. Sunshine Realty">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">URL Slug *</label>
                        <input type="text" name="slug" class="form-control" required value="<?= htmlspecialchars($wizardData['slug'] ?? '') ?>" placeholder="sunshine-realty">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Custom Domain</label>
                        <input type="text" name="domain" class="form-control" value="<?= htmlspecialchars($wizardData['domain'] ?? '') ?>" placeholder="crm.sunshinerealty.com">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Contact Name</label>
                        <input type="text" name="contact_name" class="form-control" value="<?= htmlspecialchars($wizardData['contact_name'] ?? '') ?>" placeholder="Admin Name">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Contact Email</label>
                        <input type="email" name="contact_email" class="form-control" value="<?= htmlspecialchars($wizardData['contact_email'] ?? '') ?>" placeholder="admin@company.com">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Contact Phone</label>
                        <input type="text" name="contact_phone" class="form-control" value="<?= htmlspecialchars($wizardData['contact_phone'] ?? '') ?>" placeholder="+91 98765 43210">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">City</label>
                        <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($wizardData['city'] ?? '') ?>" placeholder="New Delhi">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">State</label>
                        <input type="text" name="state" class="form-control" value="<?= htmlspecialchars($wizardData['state'] ?? '') ?>" placeholder="Delhi">
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label fw-semibold">Address</label>
                        <textarea name="address" class="form-control" rows="2" placeholder="Full address"><?= htmlspecialchars($wizardData['address'] ?? '') ?></textarea>
                    </div>
                </div>
                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-primary px-4">Next: Branding <i class="fas fa-arrow-right ms-1"></i></button>
                </div>
            </form>

        <!-- STEP 2: Branding -->
        <?php elseif ($step === 2): ?>
            <h5 class="mb-4"><i class="fas fa-palette me-2 text-primary"></i>Branding & Theme</h5>
            <form method="POST" action="<?= $base ?>/admin/tenants/onboard/save">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <input type="hidden" name="step" value="2">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Logo URL</label>
                        <input type="url" name="logo_url" class="form-control" value="<?= htmlspecialchars($wizardData['logo_url'] ?? '') ?>" placeholder="https://example.com/logo.png">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-semibold">Primary Color</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="color" name="primary_color" value="<?= htmlspecialchars($wizardData['primary_color'] ?? '#667eea') ?>" class="form-control form-control-color">
                            <span class="text-muted small"><?= htmlspecialchars($wizardData['primary_color'] ?? '#667eea') ?></span>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-semibold">Secondary Color</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="color" name="secondary_color" value="<?= htmlspecialchars($wizardData['secondary_color'] ?? '#764ba2') ?>" class="form-control form-control-color">
                            <span class="text-muted small"><?= htmlspecialchars($wizardData['secondary_color'] ?? '#764ba2') ?></span>
                        </div>
                    </div>
                </div>
                <!-- Live Preview -->
                <div class="p-3 rounded mb-3 style-40363">
                    <h5 class="mb-0"><?= htmlspecialchars($wizardData['name'] ?? 'Company Name') ?></h5>
                    <small class="style-91394">Preview: This is how the sidebar header will look</small>
                </div>
                <div class="d-flex justify-content-between mt-3">
                    <a href="<?= $base ?>/admin/tenants/onboard?step=1" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
                    <button type="submit" class="btn btn-primary px-4">Next: Plan <i class="fas fa-arrow-right ms-1"></i></button>
                </div>
            </form>

        <!-- STEP 3: Plan Selection -->
        <?php elseif ($step === 3): ?>
            <h5 class="mb-4"><i class="fas fa-tags me-2 text-primary"></i>Select Subscription Plan</h5>
            <form method="POST" action="<?= $base ?>/admin/tenants/onboard/save">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <input type="hidden" name="step" value="3">
                <input type="hidden" name="plan_id" id="selectedPlan" value="<?= $wizardData['plan_id'] ?? 1 ?>">
                <div class="row mb-4">
                    <?php foreach ($plans as $plan): ?>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="plan-wizard-card <?= ($wizardData['plan_id'] ?? 1) == $plan['id'] ? 'selected' : '' ?>" onclick="selectPlan(this, <?= $plan['id'] ?>)">
                                <h6 class="mb-2"><?= htmlspecialchars($plan['name'] ?? '') ?></h6>
                                <h4 class="<?= ($plan['slug'] ?? '') === 'free' ? 'text-secondary' : 'text-primary' ?>">₹<?= number_format($plan['price_monthly']) ?><small class="text-muted fs-6">/mo</small></h4>
                                <div class="small text-muted mt-2">
                                    <?= $plan['max_users'] ?> users Â· <?= $plan['max_leads'] ?> leads<br>
                                    <?= $plan['max_properties'] ?> props Â· <?= $plan['storage_limit_mb'] ?>MB
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="d-flex justify-content-between mt-3">
                    <a href="<?= $base ?>/admin/tenants/onboard?step=2" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
                    <button type="submit" class="btn btn-primary px-4">Next: Invites <i class="fas fa-arrow-right ms-1"></i></button>
                </div>
            </form>

        <!-- STEP 4: User Invites -->
        <?php elseif ($step === 4): ?>
            <h5 class="mb-4"><i class="fas fa-user-plus me-2 text-primary"></i>Invite Team Members</h5>
            <form method="POST" action="<?= $base ?>/admin/tenants/onboard/save">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <input type="hidden" name="step" value="4">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Email Addresses (one per line)</label>
                    <textarea name="invite_emails" class="form-control" rows="6" placeholder="user1@company.com&#10;user2@company.com&#10;manager@company.com"><?= htmlspecialchars(implode("\n", $wizardData['invite_emails'] ?? [])) ?></textarea>
                    <small class="text-muted">These users will receive an invitation email once the tenant is active.</small>
                </div>
                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle me-2"></i>You can also add users later from the tenant management panel.
                </div>
                <div class="d-flex justify-content-between mt-3">
                    <a href="<?= $base ?>/admin/tenants/onboard?step=3" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
                    <button type="submit" class="btn btn-primary px-4">Next: Review <i class="fas fa-arrow-right ms-1"></i></button>
                </div>
            </form>

        <!-- STEP 5: Review & Launch -->
        <?php elseif ($step === 5): ?>
            <h5 class="mb-4"><i class="fas fa-rocket me-2 text-primary"></i>Review & Launch</h5>
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-muted mb-3">Company Details</h6>
                    <table class="review-table w-100">
                        <tr><td class="label">Name</td><td class="fw-semibold"><?= htmlspecialchars($wizardData['name'] ?? '—') ?></td></tr>
                        <tr><td class="label">Slug</td><td><code><?= htmlspecialchars($wizardData['slug'] ?? '—') ?></code></td></tr>
                        <tr><td class="label">Domain</td><td><?= htmlspecialchars($wizardData['domain'] ?? 'Auto') ?></td></tr>
                        <tr><td class="label">Contact</td><td><?= htmlspecialchars($wizardData['contact_name'] ?? '—') ?></td></tr>
                        <tr><td class="label">Email</td><td><?= htmlspecialchars($wizardData['contact_email'] ?? '—') ?></td></tr>
                        <tr><td class="label">Phone</td><td><?= htmlspecialchars($wizardData['contact_phone'] ?? '—') ?></td></tr>
                        <tr><td class="label">Location</td><td><?= htmlspecialchars(($wizardData['city'] ?? '') . ', ' . ($wizardData['state'] ?? '')) ?></td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="text-muted mb-3">Plan & Branding</h6>
                    <?php
                    $selectedPlan = null;
                    foreach ($plans as $p) { if ($p['id'] == ($wizardData['plan_id'] ?? 1)) { $selectedPlan = $p; break; } }
                    ?>
                    <table class="review-table w-100">
                        <tr><td class="label">Plan</td><td class="fw-semibold"><?= htmlspecialchars($selectedPlan['name'] ?? 'Free') ?></td></tr>
                        <tr><td class="label">Price</td><td>₹<?= number_format($selectedPlan['price_monthly'] ?? 0) ?>/mo</td></tr>
                        <tr><td class="label">Users</td><td><?= $wizardData['max_users'] ?? $selectedPlan['max_users'] ?? 1 ?></td></tr>
                        <tr><td class="label">Leads</td><td><?= $wizardData['max_leads'] ?? $selectedPlan['max_leads'] ?? 50 ?></td></tr>
                        <tr><td class="label">Properties</td><td><?= $wizardData['max_properties'] ?? $selectedPlan['max_properties'] ?? 10 ?></td></tr>
                        <tr><td class="label">Storage</td><td><?= $wizardData['storage_limit_mb'] ?? $selectedPlan['storage_limit_mb'] ?? 100 ?>MB</td></tr>
                        <tr><td class="label">Theme</td><td>
                            <span class="d-inline-block rounded style-21195"></span>
                            <?= htmlspecialchars($wizardData['primary_color'] ?? '#667eea') ?>
                        </td></tr>
                    </table>

                    <?php if (!empty($wizardData['invite_emails'])): ?>
                        <h6 class="text-muted mb-2 mt-3">Invites (<?= count($wizardData['invite_emails']) ?>)</h6>
                        <ul class="list-unstyled small">
                            <?php foreach ($wizardData['invite_emails'] as $email): ?>
                                <li><i class="fas fa-envelope me-1 text-muted"></i><?= htmlspecialchars($email ?? '') ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>

            <form method="POST" action="<?= $base ?>/admin/tenants/onboard/launch" class="mt-4">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <div class="d-flex justify-content-between">
                    <a href="<?= $base ?>/admin/tenants/onboard?step=4" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
                    <button type="submit" class="btn btn-success btn-lg px-5" data-aps-confirm="Launch this tenant?">
                        <i class="fas fa-rocket me-2"></i>Launch Tenant
                    </button>
                </div>
            </form>
        <?php endif; ?>

    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
function selectPlan(el, planId) {
    document.querySelectorAll('.plan-wizard-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('selectedPlan').value = planId;
}

// Auto-generate slug from name
document.querySelector('input[name="name"]')?.addEventListener('input', function() {
    const slugInput = document.querySelector('input[name="slug"]');
    if (slugInput && !slugInput.dataset.manual) {
        slugInput.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
    }
});
document.querySelector('input[name="slug"]')?.addEventListener('input', function() {
    this.dataset.manual = '1';
});
</script>
