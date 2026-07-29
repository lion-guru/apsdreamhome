<?php
/**
 * Royalty Pool — Site Manager 2% Global Pool
 *
 * Shows current month's pool status: contributions, qualified managers, per-share.
 */
$pool = $pool ?? [];
$base = defined('BASE_URL') ? BASE_URL : '';
$csrf_token = $csrf_token ?? '';

$totalPool   = (float)($pool['total_pool_amount'] ?? 0);
$qualified   = (int)($pool['qualified_managers'] ?? 0);
$perShare    = (float)($pool['per_manager_share'] ?? 0);
$status      = $pool['distributed_status'] ?? 'accumulating';
$monthYear   = $pool['month_year'] ?? date('Y-m');
$contribCount = (int)($pool['contributions_count'] ?? 0);
$contribTotal = (float)($pool['contributions_total'] ?? 0);
$distAt      = $pool['distributed_at'] ?? null;
?>

<div class="aps-cp-card mb-4">
    <div class="aps-cp-card-header d-flex justify-content-between align-items-center">
        <h5 class="m-0"><i class="fas fa-trophy me-2 text-warning"></i>Site Manager Royalty Pool — <?= htmlspecialchars($monthYear) ?></h5>
        <a href="<?= htmlspecialchars($base) ?>/admin/mlm/payout-simulator" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-calculator me-1"></i>Payout Simulator
        </a>
    </div>
    <div class="aps-cp-card-body">
        <p class="text-muted mb-3">
            2% of every company sale is contributed to this monthly pool.
            At month-end, the pool is divided equally among qualifying Site Managers (≥₹50L monthly GBV).
        </p>

        <div class="row g-3 mb-4">
            <div class="col-md-3 col-6">
                <div class="aps-cp-stat bg-warning text-dark">
                    <div class="aps-cp-stat-value">&#8377;<?= number_format($totalPool / 1000, 1) ?>K</div>
                    <div class="aps-cp-stat-label">Pool Total</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="aps-cp-stat bg-primary text-white">
                    <div class="aps-cp-stat-value"><?= $contribCount ?></div>
                    <div class="aps-cp-stat-label">Contributions</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="aps-cp-stat bg-success text-white">
                    <div class="aps-cp-stat-value"><?= $qualified ?></div>
                    <div class="aps-cp-stat-label">Qualified Managers</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="aps-cp-stat bg-info text-white">
                    <div class="aps-cp-stat-value">&#8377;<?= number_format($perShare / 1000, 1) ?>K</div>
                    <div class="aps-cp-stat-label">Per Manager Share</div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <div class="aps-cp-card">
                    <div class="aps-cp-card-header">
                        <h6 class="m-0">Pool Status</h6>
                    </div>
                    <div class="aps-cp-card-body">
                        <div class="table-responsive"><table class="table table-sm mb-0">
                            <tbody>
                                <tr><td class="text-muted">Month</td><td class="fw-bold"><?= htmlspecialchars($monthYear) ?></td></tr>
                                <tr><td class="text-muted">Total Contributed</td><td class="fw-bold">&#8377;<?= number_format($contribTotal, 2) ?></td></tr>
                                <tr><td class="text-muted">Status</td>
                                    <td>
                                        <?php if ($status === 'distributed'): ?>
                                            <span class="badge bg-success">Distributed</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Accumulating</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php if ($distAt): ?>
                                <tr><td class="text-muted">Distributed At</td><td><?= htmlspecialchars($distAt) ?></td></tr>
                                <?php endif; ?>
                                <tr><td class="text-muted">Qualification Threshold</td><td>&#8377;50,00,000 (₹50 Lakhs GBV)</td></tr>
                                <tr><td class="text-muted">Distribution Method</td><td>Equal split among qualifying Site Managers</td></tr>
                            </tbody>
                        </table></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="aps-cp-card">
                    <div class="aps-cp-card-header">
                        <h6 class="m-0">How It Works</h6>
                    </div>
                    <div class="aps-cp-card-body">
                        <ol class="mb-0 ps-3">
                            <li class="mb-2"><strong>Accumulation:</strong> Each sale contributes 2% to the monthly pool via <code>contributeToRoyaltyPool()</code>.</li>
                            <li class="mb-2"><strong>Qualification:</strong> At month-end, managers with ≥₹50L GBV qualify for a share.</li>
                            <li class="mb-2"><strong>Distribution:</strong> <code>distributeRoyaltyPool()</code> splits the pool equally among qualified managers and writes ledger entries.</li>
                            <li class="mb-2"><strong>Idempotent:</strong> Distribution can only happen once per month (guarded by <code>distributed_status</code>).</li>
                            <li class="mb-2"><strong>Cron schedule:</strong> Run <code>scripts/run_all_crons.php</code> to trigger daily and monthly tasks.</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
