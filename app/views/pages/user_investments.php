<?php
/**
 * Customer My Investments Page
 * @var array $userInvestments
 * @var array $userStats
 * @var string $base
 * @var string $csrf_token
 */
$base = BASE_URL;
$page_title = $page_title ?? 'My Investments';
?>
<?php include __DIR__ . '/../layouts/customer.php'; ?>
<?php ob_start(); ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h4 class="mb-0"><i class="fas fa-briefcase me-2 text-primary"></i>My Investments</h4>
        <a href="<?= $base ?>/investment-plans" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Browse Plans</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center bg-gradient-primary text-white h-100">
                <div class="card-body">
                    <div class="fs-4 fw-bold">₹<?= number_format($userStats['total_invested'] ?? 0) ?></div>
                    <small>Total Invested</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center bg-gradient-success text-white h-100">
                <div class="card-body">
                    <div class="fs-4 fw-bold">₹<?= number_format($userStats['current_value'] ?? 0) ?></div>
                    <small>Current Value</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center bg-gradient-warning text-white h-100">
                <div class="card-body">
                    <div class="fs-4 fw-bold">₹<?= number_format($userStats['total_returns'] ?? 0) ?></div>
                    <small>Total Returns</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center bg-gradient-info text-white h-100">
                <div class="card-body">
                    <div class="fs-4 fw-bold"><?= count($userInvestments ?? []) ?></div>
                    <small>Active Investments</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Investments Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-0">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Investment Portfolio</h5>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($userInvestments)): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Plan</th>
                            <th>Category</th>
                            <th>Invested</th>
                            <th>Current Value</th>
                            <th>Returns</th>
                            <th>Status</th>
                            <th>Started</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($userInvestments as $inv): 
                            $gain = ($inv['current_value'] ?? 0) - ($inv['amount'] ?? 0);
                            $gainPct = ($inv['amount'] ?? 0) > 0 ? ($gain / $inv['amount']) * 100 : 0;
                            $statusClass = match($inv['status'] ?? 'active') {
                                'active' => 'bg-success',
                                'matured' => 'bg-info',
                                'cancelled' => 'bg-secondary',
                                default => 'bg-warning'
                            };
                        ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($inv['plan_name'] ?? '') ?></strong>
                                <br><small class="text-muted"><?= htmlspecialchars($inv['plan_code'] ?? '') ?></small>
                            </td>
                            <td>
                                <span class="badge bg-<?= $inv['plan_category'] === 'sip' ? 'primary' : ($inv['plan_category'] === 'lumpsum' ? 'success' : ($inv['plan_category'] === 'real_estate_fund' ? 'warning' : 'info')) ?>">
                                    <?= ucfirst($inv['plan_category'] ?? '') ?>
                                </span>
                            </td>
                            <td>₹<?= number_format($inv['amount'] ?? 0) ?></td>
                            <td>₹<?= number_format($inv['current_value'] ?? 0) ?></td>
                            <td class="<?= $gain >= 0 ? 'text-success' : 'text-danger' ?> fw-bold">
                                <?= $gain >= 0 ? '+' : '' ?>₹<?= number_format(abs($gain)) ?>
                                <br><small>(<?= $gain >= 0 ? '+' : '' ?><?= number_format($gainPct, 2) ?>%)</small>
                            </td>
                            <td><span class="badge <?= $statusClass ?>"><?= ucfirst($inv['status'] ?? 'active') ?></span></td>
                            <td><?= date('d M Y', strtotime($inv['started_at'] ?? $inv['created_at'])) ?></td>
                            <td>
                                <?php if (($inv['status'] ?? 'active') === 'active'): ?>
                                    <form method="POST" action="<?= $base ?>/user/investment/cancel" class="d-inline" onsubmit="return confirm('Cancel this investment? Early withdrawal charges apply.');">
                                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                        <input type="hidden" name="investment_id" value="<?= $inv['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-times me-1"></i>Cancel</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-piggy-bank fa-3x text-muted mb-3"></i>
                <h5>No investments yet</h5>
                <p class="text-muted">Start your investment journey today</p>
                <a href="<?= $base ?>/investment-plans" class="btn btn-primary mt-2"><i class="fas fa-plus me-1"></i>Browse Plans</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layouts/customer.php'; ?>