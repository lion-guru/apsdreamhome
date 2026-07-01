<?php
$page_title = $page_title ?? "Ranks & Tiers";
$ranksData = $ranksData ?? ['benefits' => [], 'rankCounts' => [], 'recentPromotions' => [], 'stats' => []];
$base = defined('BASE_URL') ? BASE_URL : '';
$benefits = $ranksData['benefits'] ?? [];
$rankCounts = $ranksData['rankCounts'] ?? [];
$recentPromotions = $ranksData['recentPromotions'] ?? [];
$stats = $ranksData['stats'] ?? [];
$totalMembers = (int)($stats['total_members'] ?? 0);
$rankColors = [];
foreach ($benefits as $b) { $rankColors[strtolower($b['rank_name'])] = $b['color_code'] ?? '#94a3b8'; }
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="m-0"><i class="fas fa-medal me-2"></i>MLM Ranks & Tiers</h4>
        <a href="<?= htmlspecialchars($base) ?>/admin/mlm" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to MLM</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body text-center">
                    <div style="font-size:1.8rem;font-weight:700;color:var(--primary);"><?= (int)($stats['total_ranks'] ?? 0) ?></div>
                    <div class="text-muted small">Total Ranks</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body text-center">
                    <div style="font-size:1.8rem;font-weight:700;color:#10b981;"><?= $totalMembers ?></div>
                    <div class="text-muted small">Total Members</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body text-center">
                    <?php $avgTime = '-'; $withTime = 0; $totalDays = 0; ?>
                    <?php foreach ($recentPromotions as $rp): ?>
                        <?php if (!empty($rp['rank_updated_at'])): ?>
                            <?php $d = (new DateTime())->diff(new DateTime($rp['rank_updated_at'])); $totalDays += $d->days; $withTime++; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <div style="font-size:1.8rem;font-weight:700;color:#f59e0b;"><?= $withTime > 0 ? round($totalDays / $withTime) . 'd' : '-' ?></div>
                    <div class="text-muted small">Avg Time to Promote</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body text-center">
                    <?php $highestRank = '-'; $maxOrder = 0; foreach ($benefits as $b) { if (($rankCounts[strtolower($b['rank_name'])] ?? 0) > 0 && $b['rank_order'] > $maxOrder) { $maxOrder = $b['rank_order']; $highestRank = ucfirst($b['rank_name']); } } ?>
                    <div style="font-size:1.4rem;font-weight:700;color:#0f766e;"><i class="fas fa-crown me-1"></i><?= htmlspecialchars($highestRank) ?></div>
                    <div class="text-muted small">Highest Rank Achieved</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header">
                    <h5 class="m-0"><i class="fas fa-layer-group me-2"></i>Rank Structure</h5>
                </div>
                <div class="aps-cp-card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Rank</th>
                                    <th class="text-center">Members</th>
                                    <th class="text-center">Direct Sale %</th>
                                    <th class="text-center">L1 %</th>
                                    <th class="text-center">L2 %</th>
                                    <th class="text-center">L3 %</th>
                                    <th class="text-end">Min Volume</th>
                                    <th class="text-end">Min Legs</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($benefits)): ?>
                                    <tr><td colspan="8" class="text-center text-muted py-4">No rank data found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($benefits as $b):
                                        $name = strtolower($b['rank_name'] ?? '');
                                        $color = $b['color_code'] ?? '#94a3b8';
                                        $icon = $b['badge_icon'] ?? 'fa-user';
                                        $count = $rankCounts[$name] ?? 0;
                                        $pct = $totalMembers > 0 ? round(($count / $totalMembers) * 100) : 0;
                                    ?>
                                    <tr>
                                        <td>
                                            <span class="badge" style="background:<?= htmlspecialchars($color) ?>;color:#fff;padding:6px 12px;font-size:0.8rem;">
                                                <i class="fas <?= htmlspecialchars($icon) ?> me-1"></i><?= htmlspecialchars(ucfirst($name)) ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <strong><?= $count ?></strong>
                                            <span class="text-muted small ms-1">(<?= $pct ?>%)</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="progress" style="height:6px;width:80px;margin:0 auto;">
                                                <div class="progress-bar" style="width:<?= min(100, (float)$b['direct_sale_pct'] * 20) ?>%;background:<?= htmlspecialchars($color) ?>;"></div>
                                            </div>
                                            <small class="text-muted"><?= number_format((float)$b['direct_sale_pct'], 1) ?>%</small>
                                        </td>
                                        <td class="text-center">
                                            <div class="progress" style="height:6px;width:80px;margin:0 auto;">
                                                <div class="progress-bar" style="width:<?= min(100, (float)$b['l1_pct'] * 20) ?>%;background:<?= htmlspecialchars($color) ?>;"></div>
                                            </div>
                                            <small class="text-muted"><?= number_format((float)$b['l1_pct'], 1) ?>%</small>
                                        </td>
                                        <td class="text-center">
                                            <div class="progress" style="height:6px;width:80px;margin:0 auto;">
                                                <div class="progress-bar" style="width:<?= min(100, (float)$b['l2_pct'] * 20) ?>%;background:<?= htmlspecialchars($color) ?>;"></div>
                                            </div>
                                            <small class="text-muted"><?= number_format((float)$b['l2_pct'], 1) ?>%</small>
                                        </td>
                                        <td class="text-center">
                                            <div class="progress" style="height:6px;width:80px;margin:0 auto;">
                                                <div class="progress-bar" style="width:<?= min(100, (float)$b['l3_pct'] * 20) ?>%;background:<?= htmlspecialchars($color) ?>;"></div>
                                            </div>
                                            <small class="text-muted"><?= number_format((float)$b['l3_pct'], 1) ?>%</small>
                                        </td>
                                        <td class="text-end">&#8377;<?= number_format((float)($b['min_qualifying_volume'] ?? 0)) ?></td>
                                        <td class="text-end"><?= (int)($b['min_leg_count'] ?? 0) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="aps-cp-card mb-4">
                <div class="aps-cp-card-header">
                    <h5 class="m-0"><i class="fas fa-trophy me-2"></i>Member Distribution</h5>
                </div>
                <div class="aps-cp-card-body">
                    <?php if (empty($benefits)): ?>
                        <p class="text-muted text-center">No data available.</p>
                    <?php else: ?>
                        <?php foreach ($benefits as $b):
                            $name = strtolower($b['rank_name'] ?? '');
                            $color = $b['color_code'] ?? '#94a3b8';
                            $count = $rankCounts[$name] ?? 0;
                            $pct = $totalMembers > 0 ? round(($count / $totalMembers) * 100) : 0;
                        ?>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <span class="badge" style="background:<?= htmlspecialchars($color) ?>;color:#fff;padding:4px 10px;">
                                    <i class="fas <?= htmlspecialchars($b['badge_icon'] ?? 'fa-user') ?> me-1"></i><?= htmlspecialchars(ucfirst($name)) ?>
                                </span>
                            </div>
                            <div class="d-flex align-items-center" style="min-width:180px;">
                                <div class="progress flex-grow-1 me-2" style="height:8px;">
                                    <div class="progress-bar" style="width:<?= $pct ?>%;background:<?= htmlspecialchars($color) ?>;"></div>
                                </div>
                                <span class="small fw-bold" style="min-width:40px;text-align:right;"><?= $count ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($benefits)): ?>
            <div class="aps-cp-card mb-4">
                <div class="aps-cp-card-header">
                    <h5 class="m-0"><i class="fas fa-star me-2"></i>Perks by Rank</h5>
                </div>
                <div class="aps-cp-card-body p-0">
                    <?php foreach ($benefits as $b):
                        $name = strtolower($b['rank_name'] ?? '');
                        $color = $b['color_code'] ?? '#94a3b8';
                        $perks = json_decode($b['perks'] ?? '{}', true) ?: [];
                    ?>
                    <div class="border-bottom px-3 py-2" style="border-left:3px solid <?= htmlspecialchars($color) ?> !important;">
                        <div class="fw-bold small"><?= htmlspecialchars(ucfirst($name)) ?></div>
                        <?php if (!empty($perks)): ?>
                            <?php foreach ($perks as $k => $v): ?>
                                <div class="text-muted" style="font-size:0.78rem;"><i class="fas fa-check text-success me-1"></i><?= htmlspecialchars($v) ?></div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-muted small">No perks defined</div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="aps-cp-card">
                <div class="aps-cp-card-header">
                    <h5 class="m-0"><i class="fas fa-history me-2"></i>Recent Promotions</h5>
                </div>
                <div class="aps-cp-card-body p-0">
                    <?php if (empty($recentPromotions)): ?>
                        <p class="text-muted text-center py-3">No promotions recorded yet.</p>
                    <?php else: ?>
                        <?php foreach ($recentPromotions as $rp): ?>
                        <div class="d-flex align-items-center border-bottom px-3 py-2">
                            <div class="flex-grow-1">
                                <div class="small fw-bold"><?= htmlspecialchars($rp['name'] ?? 'Unknown') ?></div>
                                <div class="text-muted" style="font-size:0.75rem;"><?= htmlspecialchars($rp['current_level'] ?? '') ?></div>
                            </div>
                            <div class="text-end">
                                <div class="badge bg-success" style="font-size:0.7rem;"><i class="fas fa-arrow-up me-1"></i>Promoted</div>
                                <div class="text-muted" style="font-size:0.7rem;"><?= htmlspecialchars($rp['rank_updated_at'] ?? '') ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
