<?php
$ranks = $ranks ?? [];
$base  = defined('BASE_URL') ? BASE_URL : '';

$commColors = ['5' => 'secondary', '7' => 'info', '10' => 'primary', '12' => 'primary', '15' => 'warning', '18' => 'danger', '20' => 'danger'];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="m-0"><i class="fas fa-sitemap me-2"></i>MLM Rank Structure Management</h4>
        <a href="<?= $base ?>/admin/network/royalty" class="btn btn-warning btn-sm">
            <i class="fas fa-coins me-1"></i>Royalty Pool Distribution
        </a>
    </div>

    <div class="alert alert-info py-2 mb-3">
        <i class="fas fa-info-circle me-2"></i>
        <strong>Fully DB-Driven:</strong> You can change rank names, GBV thresholds, commission rates, rewards, and royalty settings directly here.
        Changes take effect immediately — no code change needed.
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Rank</th>
                            <th class="text-end">Min GBV</th>
                            <th class="text-end">Max GBV</th>
                            <th class="text-center">Commission %</th>
                            <th class="text-center">Royalty Eligible</th>
                            <th class="text-center">Pool Share %</th>
                            <th>Reward</th>
                            <th class="text-center">Active</th>
                            <th class="text-center">Edit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ranks as $rank): ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?= (int)($rank['sort_order'] ?? 0) ?></span></td>
                                <td>
                                    <strong><?= htmlspecialchars($rank['rank_name'] ?? '') ?></strong><br>
                                    <code class="small text-muted"><?= htmlspecialchars($rank['rank_slug'] ?? '') ?></code>
                                </td>
                                <td class="text-end">₹<?= number_format((float)($rank['min_gbv'] ?? 0), 0) ?></td>
                                <td class="text-end">
                                    <?= (float)($rank['max_gbv'] ?? 0) > 0 ? '₹' . number_format((float)$rank['max_gbv'], 0) : '<span class="text-muted">No Limit</span>' ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary fs-6"><?= number_format((float)($rank['commission_rate'] ?? 0), 1) ?>%</span>
                                </td>
                                <td class="text-center">
                                    <?php if ($rank['royalty_eligible']): ?>
                                        <span class="badge bg-success"><i class="fas fa-star me-1"></i>Yes</span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-dark">No</span>
                                    <?php endif; ?>
                                    <?php if ($rank['profit_share_eligible']): ?>
                                        <span class="badge bg-warning text-dark ms-1">Shareholder</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ((float)($rank['royalty_pool_share_pct'] ?? 0) > 0): ?>
                                        <strong><?= number_format((float)$rank['royalty_pool_share_pct'], 2) ?>%</strong>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($rank['reward_name'])): ?>
                                        <span class="badge bg-light text-dark border">
                                            🎁 <?= htmlspecialchars($rank['reward_name']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($rank['is_active']): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="<?= $base ?>/admin/network/ranks/<?= (int)$rank['id'] ?>/edit"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- How Track A/B/C works — embedded explanation -->
    <div class="card shadow-sm mt-4 border-0 bg-light">
        <div class="card-body">
            <h6><i class="fas fa-question-circle me-2"></i>How the Commission System Works</h6>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card border-primary h-100">
                        <div class="card-header bg-primary text-white py-2">Track A — 15% (Slab Differential)</div>
                        <div class="card-body small">
                            Each rank gets paid the <strong>difference</strong> between their rate and the level below.
                            Example: VP (15%) sells a plot — they get 15%.<br>
                            If their downline (BDM 10%) sells — VP gets 15%-10% = <strong>5% only</strong>.
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-success h-100">
                        <div class="card-header bg-success text-white py-2">Track B — 3% (Team Rollup)</div>
                        <div class="card-body small">
                            Every sale sends 3% distributed among the <strong>upline chain</strong> as a performance bonus.
                            This is independent of Track A — extra income for team builders.
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-warning h-100">
                        <div class="card-header bg-warning text-dark py-2">Track C — 2% (Reward Escrow)</div>
                        <div class="card-body small">
                            2% goes into a <strong>Royalty Pool Fund</strong>.
                            Month-end: all Royalty Director+ level members share this pool proportionally.
                            Below Royalty Director level: used for gifting (bike, car, tour) on target completion.
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-3 text-muted small"><strong>Total Cap:</strong> Track A (15%) + Track B (3%) + Track C (2%) = 20% maximum. Company never pays more than 20% on any booking.</div>
        </div>
    </div>
</div>
