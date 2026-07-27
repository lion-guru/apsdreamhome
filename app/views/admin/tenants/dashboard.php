<!-- Tenant Dashboard — Super Admin Overview -->
<?php
$stats = $stats ?? [];
$plans = $plans ?? [];
$base = BASE_URL ?? '';
?>
<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.tenant-dash-header { background: linear-gradient(135deg, #0f2027, #203a43, #2c5364); color: #fff; border-radius: 12px; padding: 24px; margin-bottom: 20px; }
.stat-card-tenant { border: none; border-radius: 12px; transition: transform 0.2s; overflow: hidden; }
.stat-card-tenant:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,0.15); }
.plan-badge { display: inline-block; padding: 2px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
</style>

<div class="tenant-dash-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0"><i class="fas fa-cloud me-2"></i>SaaS Tenant Dashboard</h4>
            <p class="mb-0 mt-1" style="opacity:0.85;">Manage all tenants, plans, and subscriptions</p>
        </div>
        <a href="<?= $base ?>/admin/tenants/onboard" class="btn btn-light btn-sm">
            <i class="fas fa-plus me-1"></i>New Tenant
        </a>
    </div>
</div>

<!-- Stats -->
<div class="row mb-4">
    <div class="col-lg-3 col-6">
        <div class="card stat-card-tenant shadow-sm">
            <div class="card-body text-center">
                <div class="text-primary mb-2"><i class="fas fa-building fa-2x"></i></div>
                <h3 class="mb-1"><?= $stats['total_tenants'] ?? 0 ?></h3>
                <small class="text-muted">Total Tenants</small>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="card stat-card-tenant shadow-sm">
            <div class="card-body text-center">
                <div class="text-success mb-2"><i class="fas fa-check-circle fa-2x"></i></div>
                <h3 class="mb-1"><?= $stats['active_tenants'] ?? 0 ?></h3>
                <small class="text-muted">Active</small>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="card stat-card-tenant shadow-sm">
            <div class="card-body text-center">
                <div class="text-warning mb-2"><i class="fas fa-flask fa-2x"></i></div>
                <h3 class="mb-1"><?= $stats['trial_tenants'] ?? 0 ?></h3>
                <small class="text-muted">Trial</small>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="card stat-card-tenant shadow-sm">
            <div class="card-body text-center">
                <div class="text-danger mb-2"><i class="fas fa-pause-circle fa-2x"></i></div>
                <h3 class="mb-1"><?= $stats['suspended_tenants'] ?? 0 ?></h3>
                <small class="text-muted">Suspended</small>
            </div>
        </div>
    </div>
</div>

<!-- Revenue + Plan Distribution -->
<div class="row">
    <div class="col-lg-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-rupee-sign me-2 text-success"></i>Monthly Revenue</h6></div>
            <div class="card-body text-center">
                <h2 style="color:#10b981;">₹<?= number_format($stats['monthly_revenue'] ?? 0) ?></h2>
                <small class="text-muted">from active subscriptions</small>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-chart-pie me-2 text-primary"></i>Plan Distribution</h6></div>
            <div class="card-body">
                <?php if (!empty($stats['by_plan'])): ?>
                    <div class="d-flex flex-wrap gap-3">
                        <?php foreach ($stats['by_plan'] as $plan): ?>
                            <div class="text-center flex-fill" style="min-width:100px;">
                                <h4><?= $plan['count'] ?></h4>
                                <small class="text-muted"><?= htmlspecialchars($plan['name']) ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">No plan data</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Tenants -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-clock me-2"></i>Recent Tenants</h6>
        <a href="<?= $base ?>/admin/tenants" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Plan</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($stats['recent_tenants'])): ?>
                        <?php foreach ($stats['recent_tenants'] as $t): ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($t['name']) ?></td>
                                <td><code><?= htmlspecialchars($t['slug']) ?></code></td>
                                <td><span class="plan-badge bg-info text-white"><?= htmlspecialchars($t['plan_name'] ?? 'Free') ?></span></td>
                                <td>
                                    <?php
                                    $statusColors = ['active' => 'success', 'trial' => 'warning', 'suspended' => 'danger', 'cancelled' => 'secondary'];
                                    $color = $statusColors[$t['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $color ?>"><?= ucfirst($t['status']) ?></span>
                                </td>
                                <td><?= date('d M Y', strtotime($t['created_at'])) ?></td>
                                <td><a href="<?= $base ?>/admin/tenants/<?= $t['slug'] ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No tenants yet</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Subscription Plans -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-tags me-2"></i>Subscription Plans</h6></div>
    <div class="card-body">
        <div class="row">
            <?php foreach ($plans as $plan): ?>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border h-100">
                        <div class="card-body text-center">
                            <h5 class="card-title"><?= htmlspecialchars($plan['name']) ?></h5>
                            <h3 class="text-primary">₹<?= number_format($plan['price_monthly']) ?><small class="text-muted fs-6">/mo</small></h3>
                            <hr>
                            <ul class="list-unstyled small text-start">
                                <li class="mb-1"><i class="fas fa-users text-muted me-2"></i><?= $plan['max_users'] ?> users</li>
                                <li class="mb-1"><i class="fas fa-bullseye text-muted me-2"></i><?= $plan['max_leads'] ?> leads</li>
                                <li class="mb-1"><i class="fas fa-home text-muted me-2"></i><?= $plan['max_properties'] ?> properties</li>
                                <li class="mb-1"><i class="fas fa-database text-muted me-2"></i><?= $plan['storage_limit_mb'] ?> MB storage</li>
                                <?php if ($plan['api_access']): ?><li class="mb-1"><i class="fas fa-plug text-success me-2"></i>API Access</li><?php endif; ?>
                                <?php if ($plan['white_label']): ?><li class="mb-1"><i class="fas fa-palette text-success me-2"></i>White Label</li><?php endif; ?>
                                <?php if ($plan['mlm_engine']): ?><li class="mb-1"><i class="fas fa-project-diagram text-success me-2"></i>MLM Engine</li><?php endif; ?>
                                <?php if ($plan['ai_features']): ?><li class="mb-1"><i class="fas fa-robot text-success me-2"></i>AI Features</li><?php endif; ?>
                                <?php if ($plan['mobile_app']): ?><li class="mb-1"><i class="fas fa-mobile-alt text-success me-2"></i>Mobile App</li><?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
