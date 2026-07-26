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
$byType = $stats['commission_by_type'] ?? [];
$monthByType = $stats['commission_this_month_by_type'] ?? [];
?>
<!-- ROW 1: Core Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="aps-cp-stat bg-primary text-white">
            <div class="aps-cp-stat-value"><?= $totalActive ?></div>
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

<!-- ROW 2: 4 New Commission Streams (This Month) -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="aps-cp-stat" style="background:linear-gradient(135deg,#6366f1,#14b8a6);color:#fff;">
            <div class="aps-cp-stat-value">&#8377;<?= number_format((float)($stats['generation_bonus_this_month'] ?? 0) / 1000, 1) ?>K</div>
            <div class="aps-cp-stat-label">Generation Bonus (Month)</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="aps-cp-stat" style="background:linear-gradient(135deg,#0891b2,#06b6d4);color:#fff;">
            <div class="aps-cp-stat-value">&#8377;<?= number_format((float)($stats['infinity_override_this_month'] ?? 0) / 1000, 1) ?>K</div>
            <div class="aps-cp-stat-label">Infinity Override (Month)</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="aps-cp-stat" style="background:linear-gradient(135deg,#d97706,#f59e0b);color:#fff;">
            <div class="aps-cp-stat-value">&#8377;<?= number_format((float)($stats['matching_bonus_this_month'] ?? 0) / 1000, 1) ?>K</div>
            <div class="aps-cp-stat-label">Matching Bonus (Month)</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="aps-cp-stat" style="background:linear-gradient(135deg,#dc2626,#ef4444);color:#fff;">
            <div class="aps-cp-stat-value">&#8377;<?= number_format((float)($stats['commission_this_month'] ?? 0) / 10000000, 2) ?>Cr</div>
            <div class="aps-cp-stat-label">Total Commission (Month)</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- LEFT: Rank Distribution -->
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
                                <th class="text-end">Rate</th>
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
                                        <?= htmlspecialchars(ucfirst(str_replace('_', ' ', (string)($rb['rank_name'] ?? '')))) ?>
                                    </span>
                                </td>
                                <td class="text-end"><?= $count ?> (<?= $pct ?>%)</td>
                                <td class="text-end"><?= (int)($rb['min_leg_count'] ?? 0) ?></td>
                                <td class="text-end">&#8377;<?= number_format((float)($rb['min_qualifying_volume'] ?? 0) / 1000, 0) ?>K</td>
                                <td class="text-end"><?= number_format((float)($rb['direct_sale_pct'] ?? 0), 1) ?>%</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Commission by Type Breakdown -->
        <div class="aps-cp-card mt-4">
            <div class="aps-cp-card-header">
                <h5 class="m-0"><i class="fas fa-layer-group me-2"></i>Commission Streams — All Time</h5>
            </div>
            <div class="aps-cp-card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm m-0">
                        <thead>
                            <tr>
                                <th>Stream</th>
                                <th class="text-end">Count</th>
                                <th class="text-end">Total (All Time)</th>
                                <th class="text-end">This Month</th>
                                <th class="text-end">% of Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $allTotal = array_sum(array_column($byType, 'total'));
                            $streamLabels = [
                                'direct_sale' => ['Direct Sale', 'fa-handshake', 'text-primary'],
                                'level_bonus' => ['Level Bonus (Upline)', 'fa-layer-group', 'text-info'],
                                'override' => ['Override', 'fa-code-branch', 'text-success'],
                                'rank_bonus' => ['Rank Advancement', 'fa-trophy', 'text-warning'],
                                'generation_bonus' => ['Generation Bonus', 'fa-sitemap', 'text-info'],
                                'infinity_override' => ['Infinity Override', 'fa-infinity', 'text-info'],
                                'matching_bonus' => ['Matching Bonus', 'fa-equals', 'text-warning'],
                                'royalty_pool' => ['Royalty Pool', 'fa-crown', 'text-danger'],
                                'team_bonus' => ['Team Bonus', 'fa-users', 'text-secondary'],
                                'performance_bonus' => ['Performance Bonus', 'fa-chart-line', 'text-muted'],
                                'clawback' => ['Clawback', 'fa-undo', 'text-danger'],
                                'investment_sale' => ['Investment Sale', 'fa-coins', 'text-success'],
                                'mlm_level_1' => ['MLM L1', 'fa-arrow-up', 'text-primary'],
                                'mlm_level_2' => ['MLM L2', 'fa-arrow-up', 'text-info'],
                                'mlm_level_3' => ['MLM L3', 'fa-arrow-up', 'text-secondary'],
                            ];
                            foreach ($byType as $type => $data):
                                if ($data['total'] <= 0) continue;
                                $label = $streamLabels[$type] ?? [$type, 'fa-circle', 'text-muted'];
                                $monthTotal = $monthByType[$type]['total'] ?? 0;
                                $pctTotal = $allTotal > 0 ? round(($data['total'] / $allTotal) * 100, 1) : 0;
                            ?>
                            <tr>
                                <td>
                                    <i class="fas <?= $label[1] ?> me-1 <?= $label[2] ?>"></i>
                                    <?= $label[0] ?>
                                </td>
                                <td class="text-end"><?= $data['count'] ?></td>
                                <td class="text-end fw-bold">&#8377;<?= number_format($data['total'], 0) ?></td>
                                <td class="text-end">&#8377;<?= number_format($monthTotal, 0) ?></td>
                                <td class="text-end">
                                    <div class="progress" style="height:6px;width:60px;display:inline-block;">
                                        <div class="progress-bar bg-<?= $label[2] === 'text-danger' ? 'danger' : ($label[2] === 'text-primary' ? 'primary' : ($label[2] === 'text-success' ? 'success' : 'secondary')) ?>" style="width:<?= $pctTotal ?>%"></div>
                                    </div>
                                    <?= $pctTotal ?>%
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-active fw-bold">
                                <td>TOTAL</td>
                                <td class="text-end"><?= array_sum(array_column($byType, 'count')) ?></td>
                                <td class="text-end">&#8377;<?= number_format($allTotal, 0) ?></td>
                                <td class="text-end">&#8377;<?= number_format(array_sum(array_column($monthByType, 'total')), 0) ?></td>
                                <td class="text-end">100%</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- RIGHT: Cron + Quick Actions -->
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

        <div class="aps-cp-card mb-4">
            <div class="aps-cp-card-header">
                <h5 class="m-0"><i class="fas fa-calculator me-2"></i>Commission Model</h5>
            </div>
            <div class="aps-cp-card-body">
                <div class="mb-2"><strong>Model:</strong> Differential (upline earns gap between their rate & downline's)</div>
                <div class="mb-2"><strong>Global Cap:</strong> 20% per sale</div>
                <div class="mb-2"><strong>Depth:</strong> 7-level upline walk</div>
                <div class="mb-2"><strong>Same-Rank Breakaway:</strong> 2% Gen1 → 1% Gen2 → 0% Gen3+</div>
                <hr>
                <div class="mb-1"><strong>Monthly Streams:</strong></div>
                <ul class="list-unstyled ps-3">
                    <li>💎 <strong>Generation Bonus</strong> — 5% of gen volume (President/SM only)</li>
                    <li>♾️ <strong>Infinity Override</strong> — 1% of deep downline (VP+ only)</li>
                    <li>🤝 <strong>Matching Bonus</strong> — 100%/50%/25% match (President+ only)</li>
                    <li>👑 <strong>Royalty Pool</strong> — 2% of sales (Site Manager only)</li>
                </ul>
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
