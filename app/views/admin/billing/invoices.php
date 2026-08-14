<!-- Billing Invoices â€” Subscription History for a Tenant -->
<?php
$tenant        = $tenant ?? [];
$subscriptions = $subscriptions ?? [];
$base          = BASE_URL ?? '';
?>
<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.invoices-header { background: linear-gradient(135deg, #1a1a2e, #16213e, #0f3460); color: #fff; border-radius: 12px; padding: 24px; margin-bottom: 20px; }
.invoice-row { transition: background 0.15s; }
.invoice-row:hover { background: #f8fafc; }
</style>

<div class="invoices-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0"><i class="fas fa-receipt me-2"></i>Billing History</h4>
            <p class="mb-0 mt-1" class="style-91394">
                <?= htmlspecialchars($tenant['name'] ?? 'Tenant') ?> â€” All subscription records
            </p>
        </div>
        <div>
            <a href="<?= $base ?>/admin/billing/subscribe/<?= $tenant['id'] ?>" class="btn btn-outline-light btn-sm me-2">
                <i class="fas fa-credit-card me-1"></i>Manage Subscription
            </a>
            <a href="<?= $base ?>/admin/billing" class="btn btn-outline-light btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Back to Billing
            </a>
        </div>
    </div>
</div>

<!-- Summary Stats -->
<?php
$activeCount   = count(array_filter($subscriptions, fn($s) => in_array($s['status'], ['active', 'trialing'])));
$cancelledCount = count(array_filter($subscriptions, fn($s) => $s['status'] === 'cancelled'));
$totalPaid     = array_sum(array_map(fn($s) => (float)($s['amount'] ?? 0), array_filter($subscriptions, fn($s) => $s['status'] === 'active')));
?>
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm text-center">
            <div class="card-body">
                <h3 class="text-primary"><?= count($subscriptions) ?></h3>
                <small class="text-muted">Total Records</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm text-center">
            <div class="card-body">
                <h3 class="text-success"><?= $activeCount ?></h3>
                <small class="text-muted">Active</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm text-center">
            <div class="card-body">
                <h3 class="text-danger"><?= $cancelledCount ?></h3>
                <small class="text-muted">Cancelled</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm text-center">
            <div class="card-body">
                <h3 class="text-success">â‚¹<?= number_format($totalPaid) ?></h3>
                <small class="text-muted">Active Revenue</small>
            </div>
        </div>
    </div>
</div>

<!-- Subscription History Table -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-history me-2"></i>Subscription History</h6>
        <span class="badge bg-secondary"><?= count($subscriptions) ?></span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Plan</th>
                        <th>Cycle</th>
                        <th>Amount</th>
                        <th>Started</th>
                        <th>Period</th>
                        <th>Status</th>
                        <th>Razorpay ID</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($subscriptions)): ?>
                        <?php foreach ($subscriptions as $i => $sub): ?>
                            <tr class="invoice-row">
                                <td class="text-muted"><?= $i + 1 ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($sub['plan_name'] ?? 'Free') ?></td>
                                <td>
                                    <span class="badge bg-light text-dark"><?= ucfirst($sub['billing_cycle'] ?? 'monthly') ?></span>
                                </td>
                                <td class="fw-semibold">â‚¹<?= number_format($sub['amount'] ?? 0) ?></td>
                                <td><?= $sub['created_at'] ? date('d M Y H:i', strtotime($sub['created_at'])) : 'â€”' ?></td>
                                <td>
                                    <?= $sub['current_period_start'] ? date('d M Y', strtotime($sub['current_period_start'])) : 'â€”' ?>
                                    â†’
                                    <?= $sub['current_period_end'] ? date('d M Y', strtotime($sub['current_period_end'])) : 'â€”' ?>
                                </td>
                                <td>
                                    <?php
                                    $sc = ['active' => 'success', 'trialing' => 'info', 'past_due' => 'warning', 'cancelled' => 'danger'];
                                    ?>
                                    <span class="badge bg-<?= $sc[$sub['status']] ?? 'secondary' ?>"><?= ucfirst($sub['status']) ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($sub['razorpay_subscription_id'])): ?>
                                        <code class="small"><?= htmlspecialchars(substr($sub['razorpay_subscription_id'], 0, 20)) ?>...</code>
                                    <?php else: ?>
                                        <span class="text-muted">â€”</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">No subscription history</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
