<!-- Tenant Detail / Show — Super Admin -->
<?php
$tenant = $tenant ?? [];
$plans = $plans ?? [];
$base = BASE_URL ?? '';
$usage = $tenant['usage'] ?? [];

$statusColors = ['active' => 'success', 'trial' => 'warning', 'suspended' => 'danger', 'cancelled' => 'secondary'];

function tenantUsageBar($used, $max, $color = 'primary') {
    $pct = $max > 0 ? round(($used / $max) * 100) : 0;
    $barColor = $pct > 80 ? '#ef4444' : ($pct > 50 ? '#f59e0b' : '#10b981');
    return "<div class='progress mb-1' style='height:8px;border-radius:4px;'>
                <div class='progress-bar' style='width:{$pct}%;background:{$barColor};border-radius:4px;'></div>
            </div>
            <small class='text-muted'>{$used} / {$max} ({$pct}%)</small>";
}
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.tenant-detail-header { background: linear-gradient(135deg, <?= $tenant['primary_color'] ?? '#667eea' ?>, <?= $tenant['secondary_color'] ?? '#764ba2' ?>); color: #fff; border-radius: 12px; padding: 24px; margin-bottom: 20px; }
.info-label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; margin-bottom: 2px; }
.info-value { font-size: 0.95rem; font-weight: 600; color: #1e293b; }
</style>

<!-- Header -->
<div class="tenant-detail-header">
    <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
            <div style="width:60px;height:60px;border-radius:12px;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;font-size:1.5rem;font-weight:700;"><?= strtoupper(substr($tenant['name'] ?? 'T', 0, 2)) ?></div>
            <div>
                <h4 class="mb-0"><?= htmlspecialchars($tenant['name'] ?? '') ?></h4>
                <p class="mb-0" style="opacity:0.85;">
                    <code><?= htmlspecialchars($tenant['slug'] ?? '') ?></code>
                    <?php if ($tenant['domain'] ?? ''): ?> · <?= htmlspecialchars($tenant['domain']) ?><?php endif; ?>
                </p>
            </div>
        </div>
        <div>
            <span class="badge bg-white text-<?= $statusColors[$tenant['status']] ?? 'secondary' ?>" style="font-size:0.9rem;padding:6px 14px;">
                <?= ucfirst($tenant['status'] ?? 'unknown') ?>
                <?php if (($tenant['status'] ?? '') === 'trial' && ($tenant['trial_ends_at'] ?? '')): ?>
                    · Ends <?= date('d M', strtotime($tenant['trial_ends_at'])) ?>
                <?php endif; ?>
            </span>
        </div>
    </div>
</div>

<!-- Flash -->
<?php if (!empty($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?= $_SESSION['success'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>
<?php if (!empty($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle me-2"></i><?= $_SESSION['error'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="row">
    <!-- Left: Info + Usage -->
    <div class="col-lg-8">
        <!-- Usage Meters -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Resource Usage</h6></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <label class="info-label">Users</label>
                        <?= tenantUsageBar($tenant['users_count'] ?? 0, $tenant['max_users'] ?? 1) ?>
                    </div>
                    <div class="col-md-4">
                        <label class="info-label">Leads</label>
                        <?= tenantUsageBar($tenant['leads_count'] ?? 0, $tenant['max_leads'] ?? 50) ?>
                    </div>
                    <div class="col-md-4">
                        <label class="info-label">Properties</label>
                        <?= tenantUsageBar($tenant['properties_count'] ?? 0, $tenant['max_properties'] ?? 10) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Info -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-address-card me-2"></i>Contact Information</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="info-label">Contact Name</div>
                        <div class="info-value"><?= htmlspecialchars($tenant['contact_name'] ?? '—') ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-label">Email</div>
                        <div class="info-value"><?= htmlspecialchars($tenant['contact_email'] ?? '—') ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-label">Phone</div>
                        <div class="info-value"><?= htmlspecialchars($tenant['contact_phone'] ?? '—') ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-label">City</div>
                        <div class="info-value"><?= htmlspecialchars($tenant['city'] ?? '—') ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-label">State</div>
                        <div class="info-value"><?= htmlspecialchars($tenant['state'] ?? '—') ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-label">Address</div>
                        <div class="info-value"><?= htmlspecialchars($tenant['address'] ?? '—') ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Plan + Actions -->
    <div class="col-lg-4">
        <!-- Plan Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-body text-center">
                <h5 class="text-muted mb-1">Current Plan</h5>
                <h3 class="text-primary mb-1"><?= htmlspecialchars($tenant['plan_name'] ?? 'Free') ?></h3>
                <p class="text-muted mb-3">₹<?= number_format($tenant['price_monthly'] ?? 0) ?>/month</p>
                <hr>
                <div class="text-start small">
                    <div class="d-flex justify-content-between mb-1"><span>Storage Limit</span><strong><?= $tenant['storage_limit_mb'] ?? 100 ?> MB</strong></div>
                    <div class="d-flex justify-content-between mb-1"><span>Created</span><strong><?= date('d M Y', strtotime($tenant['created_at'] ?? 'now')) ?></strong></div>
                    <?php if ($tenant['onboarded_at'] ?? ''): ?>
                        <div class="d-flex justify-content-between mb-1"><span>Onboarded</span><strong><?= date('d M Y', strtotime($tenant['onboarded_at'])) ?></strong></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-cog me-2"></i>Actions</h6></div>
            <div class="card-body d-grid gap-2">
                <a href="<?= $base ?>/admin/tenants/<?= $tenant['id'] ?>/edit" class="btn btn-outline-primary"><i class="fas fa-edit me-2"></i>Edit Tenant</a>

                <?php if (($tenant['status'] ?? '') !== 'suspended'): ?>
                    <form method="POST" action="<?= $base ?>/admin/tenants/<?= $tenant['id'] ?>/suspend" onsubmit="return confirm('Suspend this tenant?')">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <input type="hidden" name="reason" value="Suspended by admin">
                        <button type="submit" class="btn btn-outline-warning w-100"><i class="fas fa-pause me-2"></i>Suspend</button>
                    </form>
                <?php else: ?>
                    <form method="POST" action="<?= $base ?>/admin/tenants/<?= $tenant['id'] ?>/restore">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <button type="submit" class="btn btn-outline-success w-100"><i class="fas fa-play me-2"></i>Restore</button>
                    </form>
                <?php endif; ?>

                <?php if (($tenant['slug'] ?? '') !== 'apsdreamhome'): ?>
                    <form method="POST" action="<?= $base ?>/admin/tenants/<?= $tenant['id'] ?>/delete" onsubmit="return confirm('DELETE this tenant? This is soft-delete and can be restored.')">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <button type="submit" class="btn btn-outline-danger w-100"><i class="fas fa-trash me-2"></i>Delete Tenant</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-link me-2"></i>Quick Links</h6></div>
            <div class="card-body d-grid gap-2">
                <a href="<?= $base ?>/admin/tenants" class="btn btn-outline-secondary btn-sm"><i class="fas fa-list me-2"></i>All Tenants</a>
                <a href="<?= $base ?>/admin/tenants/dashboard" class="btn btn-outline-secondary btn-sm"><i class="fas fa-chart-line me-2"></i>Dashboard</a>
                <a href="<?= $base ?>/admin/tenants/create" class="btn btn-outline-secondary btn-sm"><i class="fas fa-plus me-2"></i>New Tenant</a>
            </div>
        </div>
    </div>
</div>
