<!-- Billing Dashboard â€” SaaS Revenue Overview -->
<?php
$stats         = $stats ?? [];
$trend         = $trend ?? [];
$plans         = $plans ?? [];
$subscriptions = $subscriptions ?? [];
$base = BASE_URL ?? '';
?>
<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.billing-header { background: linear-gradient(135deg, #1a1a2e, #16213e, #0f3460); color: #fff; border-radius: 12px; padding: 24px; margin-bottom: 20px; }
.billing-stat { border: none; border-radius: 12px; transition: transform 0.2s; overflow: hidden; }
.billing-stat:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,0.12); }
.mrr-value { font-size: 2rem; font-weight: 700; color: #10b981; }
.arr-value { font-size: 1.4rem; font-weight: 600; color: #6366f1; }
.trend-bar { height: 6px; border-radius: 3px; background: #e5e7eb; overflow: hidden; }
.trend-fill { height: 100%; border-radius: 3px; transition: width 0.6s ease; }
</style>

<div class="billing-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0"><i class="fas fa-credit-card me-2"></i>Billing & Subscriptions</h4>
            <p class="mb-0 mt-1" class="style-91394">Revenue, MRR/ARR, active subscriptions</p>
        </div>
        <div>
            <a href="<?= $base ?>/admin/billing/plans" class="btn btn-outline-light btn-sm me-2">
                <i class="fas fa-tags me-1"></i>Manage Plans
            </a>
            <a href="<?= $base ?>/admin/tenants" class="btn btn-light btn-sm">
                <i class="fas fa-building me-1"></i>All Tenants
            </a>
        </div>
    </div>
</div>

<!-- KPI Cards -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card billing-stat shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-success mb-2"><i class="fas fa-chart-line fa-2x"></i></div>
                <div class="mrr-value">â‚¹<?= number_format($stats['total_mrr'] ?? 0) ?></div>
                <small class="text-muted">Monthly Recurring Revenue</small>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card billing-stat shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-primary mb-2"><i class="fas fa-calendar-check fa-2x"></i></div>
                <div class="arr-value">â‚¹<?= number_format(($stats['total_arr'] ?? 0)) ?></div>
                <small class="text-muted">Annual Revenue (yearly subs)</small>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card billing-stat shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-info mb-2"><i class="fas fa-users fa-2x"></i></div>
                <h2 class="mb-1"><?= $stats['active_subscriptions'] ?? 0 ?></h2>
                <small class="text-muted">Active Subscriptions</small>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card billing-stat shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-warning mb-2"><i class="fas fa-exclamation-triangle fa-2x"></i></div>
                <h2 class="mb-1"><?= ($stats['past_due_subscriptions'] ?? 0) + ($stats['cancelled_subscriptions'] ?? 0) ?></h2>
                <small class="text-muted">Past Due / Cancelled</small>
            </div>
        </div>
    </div>
</div>

<!-- Revenue by Plan + Trend -->
<div class="row mb-4">
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-chart-bar me-2 text-primary"></i>Revenue by Plan</h6></div>
            <div class="card-body">
                <?php if (!empty($stats['by_plan'])): ?>
                    <?php foreach ($stats['by_plan'] as $bp): ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-semibold"><?= htmlspecialchars($bp['name']) ?></span>
                                <span class="text-muted">â‚¹<?= number_format($bp['revenue']) ?> (<?= $bp['count'] ?> tenants)</span>
                            </div>
                            <div class="trend-bar">
                                <?php
                                $maxRev = max(array_column($stats['by_plan'], 'revenue'));
                                $pct = $maxRev > 0 ? ($bp['revenue'] / $maxRev) * 100 : 0;
                                $colors = ['#6366f1', '#10b981', '#f59e0b', '#ef4444'];
                                $color = $colors[array_search($bp, $stats['by_plan']) % 4] ?? '#6366f1';
                                ?>
                                <div class="trend-fill" class="style-61744"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted text-center py-3">No revenue data yet</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-chart-area me-2 text-success"></i>Revenue Trend (6 months)</h6></div>
            <div class="card-body">
                <?php if (!empty($trend)): ?>
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Month</th><th>New Subs</th><th class="text-end">Revenue</th></tr></thead>
                        <tbody>
                            <?php foreach ($trend as $row): ?>
                                <tr>
                                    <td><?= $row['month'] ?></td>
                                    <td><?= $row['new_subscriptions'] ?></td>
                                    <td class="text-end fw-semibold">â‚¹<?= number_format($row['revenue']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-muted text-center py-3">No trend data yet</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Active Subscriptions Table -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-list me-2"></i>Active Subscriptions</h6>
        <span class="badge bg-primary"><?= count($subscriptions) ?></span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tenant</th>
                        <th>Plan</th>
                        <th>Cycle</th>
                        <th>Amount</th>
                        <th>Period End</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($subscriptions)): ?>
                        <?php foreach ($subscriptions as $sub): ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($sub['tenant_name'] ?? 'Unknown') ?></td>
                                <td><span class="badge bg-info"><?= htmlspecialchars($sub['plan_name'] ?? 'Free') ?></span></td>
                                <td><?= ucfirst($sub['billing_cycle'] ?? 'monthly') ?></td>
                                <td class="fw-semibold">â‚¹<?= number_format($sub['amount'] ?? 0) ?></td>
                                <td><?= $sub['current_period_end'] ? date('d M Y', strtotime($sub['current_period_end'])) : 'â€”' ?></td>
                                <td>
                                    <?php
                                    $sc = ['active' => 'success', 'past_due' => 'warning', 'cancelled' => 'danger'];
                                    ?>
                                    <span class="badge bg-<?= $sc[$sub['status']] ?? 'secondary' ?>"><?= ucfirst($sub['status']) ?></span>
                                </td>
                                <td>
                                    <a href="<?= $base ?>/admin/billing/subscribe/<?= $sub['tenant_id'] ?>" class="btn btn-sm btn-outline-primary" title="Manage">
                                        <i class="fas fa-cog"></i>
                                    </a>
                                    <a href="<?= $base ?>/admin/billing/invoices/<?= $sub['tenant_id'] ?>" class="btn btn-sm btn-outline-secondary" title="History">
                                        <i class="fas fa-receipt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">No active subscriptions yet</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Plan Cards -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-tags me-2"></i>Available Plans</h6></div>
    <div class="card-body">
        <div class="row">
            <?php foreach ($plans as $plan): ?>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border h-100 <?= ($plan['slug'] ?? '') === 'free' ? 'border-secondary' : 'border-primary' ?>">
                        <div class="card-body text-center">
                            <h6 class="card-title"><?= htmlspecialchars($plan['name']) ?></h6>
                            <h3 class="<?= ($plan['slug'] ?? '') === 'free' ? 'text-secondary' : 'text-primary' ?>">
                                â‚¹<?= number_format($plan['price_monthly']) ?><small class="text-muted fs-6">/mo</small>
                            </h3>
                            <hr>
                            <ul class="list-unstyled small text-start">
                                <li class="mb-1"><i class="fas fa-users text-muted me-2"></i><?= $plan['max_users'] ?> users</li>
                                <li class="mb-1"><i class="fas fa-bullseye text-muted me-2"></i><?= $plan['max_leads'] ?> leads</li>
                                <li class="mb-1"><i class="fas fa-home text-muted me-2"></i><?= $plan['max_properties'] ?> properties</li>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
