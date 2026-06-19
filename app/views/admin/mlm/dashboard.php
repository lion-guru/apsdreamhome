<?php
/** @var array $stats */
/** @var array $rankBenefits */
/** @var array $cron */
$stats = $stats ?? [];
$rankBenefits = $rankBenefits ?? [];
$cron = $cron ?? [];
$csrf_token = $csrf_token ?? '';
$base = defined('BASE_URL') ? BASE_URL : '';

$rankDist = $stats['rank_distribution'] ?? [];
$totalActive = (int)($stats['active_associates'] ?? 0);
?>
<div class="aps-cp-card mb-4">
    <div class="aps-cp-card-header d-flex justify-content-between align-items-center">
        <h5 class="m-0"><i class="fas fa-network-wired me-2"></i>Module 4: MLM Commission Engine</h5>
        <div>
            <a href="<?= htmlspecialchars($base) ?>/admin/mlm/payouts/batches/create" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i>New Payout Batch
            </a>
            <a href="<?= htmlspecialchars($base) ?>/admin/mlm/associate-ranks" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-trophy me-1"></i>Associate Ranks
            </a>
        </div>
    </div>
    <div class="aps-cp-card-body">
        <div class="row g-3">
            <div class="col-md-3 col-6">
                <div class="aps-cp-stat bg-primary text-white">
                    <div class="aps-cp-stat-value"><?= (int)($stats['active_associates'] ?? 0) ?></div>
                    <div class="aps-cp-stat-label">Active Associates</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="aps-cp-stat bg-success text-white">
                    <div class="aps-cp-stat-value">&#8377;<?= number_format((float)($stats['commission_this_month'] ?? 0) / 1000, 1) ?>K</div>
                    <div class="aps-cp-stat-label">Commission This Month</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="aps-cp-stat bg-warning text-dark">
                    <div class="aps-cp-stat-value"><?= (int)($stats['pending_payouts'] ?? 0) ?></div>
                    <div class="aps-cp-stat-label">Pending Payouts</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="aps-cp-stat bg-danger text-white">
                    <div class="aps-cp-stat-value">&#8377;<?= number_format((float)($stats['total_clawback'] ?? 0) / 1000, 1) ?>K</div>
                    <div class="aps-cp-stat-label">Total Clawback</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="aps-cp-card">
            <div class="aps-cp-card-header">
                <h5 class="m-0"><i class="fas fa-chart-pie me-2"></i>Rank Distribution</h5>
            </div>
            <div class="aps-cp-card-body">
                <canvas id="mlm-rank-chart" height="200"></canvas>
                <div class="table-responsive mt-3">
                    <table class="table table-sm m-0">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th class="text-end">Associates</th>
                                <th class="text-end">Min Legs</th>
                                <th class="text-end">Min Volume</th>
                                <th class="text-end">Direct %</th>
                                <th class="text-end">L1 / L2 / L3 %</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rankBenefits as $rb):
                                $count = (int)($rankDist[$rb['rank_name']] ?? 0);
                                $pct = $totalActive > 0 ? round(($count / $totalActive) * 100, 1) : 0;
                            ?>
                            <tr>
                                <td>
                                    <span class="badge" style="background:<?= htmlspecialchars((string)($rb['color_code'] ?? '#94a3b8')) ?>;color:#fff;">
                                        <i class="fas <?= htmlspecialchars((string)($rb['badge_icon'] ?? 'fa-user')) ?> me-1"></i>
                                        <?= htmlspecialchars(ucfirst((string)($rb['rank_name'] ?? ''))) ?>
                                    </span>
                                </td>
                                <td class="text-end"><?= $count ?> (<?= $pct ?>%)</td>
                                <td class="text-end"><?= (int)($rb['min_leg_count'] ?? 0) ?></td>
                                <td class="text-end">&#8377;<?= number_format((float)($rb['min_qualifying_volume'] ?? 0) / 1000, 0) ?>K</td>
                                <td class="text-end"><?= number_format((float)($rb['direct_sale_pct'] ?? 0), 1) ?>%</td>
                                <td class="text-end">
                                    <?= number_format((float)($rb['l1_pct'] ?? 0), 1) ?> /
                                    <?= number_format((float)($rb['l2_pct'] ?? 0), 1) ?> /
                                    <?= number_format((float)($rb['l3_pct'] ?? 0), 1) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="aps-cp-card mb-4">
            <div class="aps-cp-card-header">
                <h5 class="m-0"><i class="fas fa-clock me-2"></i>Recent Cron Runs</h5>
                <a href="<?= htmlspecialchars($base) ?>/admin/mlm/cron-log" class="btn btn-link btn-sm">View all</a>
            </div>
            <div class="aps-cp-card-body p-0">
                <table class="table table-sm m-0">
                    <thead>
                        <tr>
                            <th>Cron</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th class="text-end">Items</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($cron)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-3">No cron runs yet</td></tr>
                        <?php else: foreach ($cron as $r): ?>
                            <tr>
                                <td><?= htmlspecialchars((string)($r['cron_name'] ?? '')) ?></td>
                                <td><?= htmlspecialchars((string)($r['run_date'] ?? '')) ?></td>
                                <td>
                                    <?php $st = (string)($r['status'] ?? ''); ?>
                                    <span class="badge bg-<?= $st === 'completed' ? 'success' : ($st === 'failed' ? 'danger' : 'warning') ?>">
                                        <?= htmlspecialchars($st) ?>
                                    </span>
                                </td>
                                <td class="text-end"><?= (int)($r['items_processed'] ?? 0) ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="aps-cp-card">
            <div class="aps-cp-card-header">
                <h5 class="m-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
            </div>
            <div class="aps-cp-card-body">
                <div class="d-grid gap-2">
                    <a href="<?= htmlspecialchars($base) ?>/admin/mlm/commissions" class="btn btn-outline-primary">
                        <i class="fas fa-list me-1"></i>View Commissions Ledger
                    </a>
                    <a href="<?= htmlspecialchars($base) ?>/admin/mlm/payouts/batches" class="btn btn-outline-primary">
                        <i class="fas fa-money-check-alt me-1"></i>Payout Batches
                    </a>
                    <a href="<?= htmlspecialchars($base) ?>/admin/mlm/clawbacks" class="btn btn-outline-warning">
                        <i class="fas fa-undo me-1"></i>Clawback Log
                    </a>
                    <a href="<?= htmlspecialchars($base) ?>/admin/mlm/rank-benefits" class="btn btn-outline-secondary">
                        <i class="fas fa-cogs me-1"></i>Rank Benefits
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var ctx = document.getElementById('mlm-rank-chart');
    if (!ctx) { return; }
    
    function hexToRgba(hex, alpha) {
        hex = hex.replace('#', '');
        if (hex.length === 3) {
            hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
        }
        var r = parseInt(hex.substring(0, 2), 16);
        var g = parseInt(hex.substring(2, 4), 16);
        var b = parseInt(hex.substring(4, 6), 16);
        return 'rgba(' + r + ', ' + g + ', ' + b + ', ' + alpha + ')';
    }

    var dbColors = <?= json_encode(array_map(fn($r) => (string)($r['color_code'] ?? '#94a3b8'), $rankBenefits)) ?>;
    var bgColors = dbColors.map(function(hex) { return hexToRgba(hex, 0.75); });
    var borderColors = dbColors.map(function(hex) { return hexToRgba(hex, 1.0); });

    var data = {
        labels: <?= json_encode(array_map(fn($r) => ucfirst((string)($r['rank_name'] ?? '')), $rankBenefits)) ?>,
        datasets: [{
            data: <?= json_encode(array_map(fn($r) => (int)($rankDist[$r['rank_name']] ?? 0), $rankBenefits)) ?>,
            backgroundColor: bgColors,
            borderColor: borderColors,
            borderWidth: 2,
            hoverOffset: 6
        }]
    };

    new Chart(ctx, { 
        type: 'doughnut', 
        data: data, 
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        boxWidth: 12,
                        padding: 15,
                        usePointStyle: true,
                        pointStyle: 'circle',
                        font: {
                            family: "'Inter', 'Segoe UI', sans-serif",
                            size: 11,
                            weight: '600'
                        }
                    }
                },
                tooltip: {
                    backgroundColor: '#1e293b',
                    titleFont: { family: "'Inter', sans-serif", size: 12, weight: 'bold' },
                    bodyFont: { family: "'Inter', sans-serif", size: 12 },
                    padding: 10,
                    cornerRadius: 8,
                    usePointStyle: true,
                    boxWidth: 8,
                    boxHeight: 8
                }
            }
        } 
    });
});
</script>
