<?php
/** @var array $rankBenefits */
$rankBenefits = $rankBenefits ?? [];
$base = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="aps-cp-card mb-4">
    <div class="aps-cp-card-header d-flex justify-content-between align-items-center">
        <h5 class="m-0"><i class="fas fa-medal me-2"></i>Rank Benefits & Tiers</h5>
        <a href="<?= htmlspecialchars($base) ?>/admin/mlm/associate-ranks" class="btn btn-link btn-sm">View Associates</a>
    </div>
    <div class="aps-cp-card-body">
        <div class="row g-3">
            <?php foreach ($rankBenefits as $rb):
                $color = (string)($rb['color_code'] ?? '#94a3b8');
                $icon  = (string)($rb['badge_icon'] ?? 'fa-user');
                $benefits = json_decode($rb['perks'] ?? '{}', true) ?: [];
            ?>
                <div class="col-md-6 col-lg-4">
                    <div class="aps-cp-rank-card">
                        <div class="aps-cp-rank-banner" style="background:<?= htmlspecialchars($color) ?>;">
                            <i class="fas <?= htmlspecialchars($icon) ?>"></i>
                        </div>
                        <div class="aps-cp-rank-body">
                            <h4 class="m-0 mb-1"><?= htmlspecialchars(ucfirst((string)$rb['rank_name'])) ?></h4>
                            <small class="text-muted">Tier <?= (int)$rb['rank_order'] ?></small>
                            <hr class="my-2">
                            <div class="table-responsive"><table class="table table-sm m-0">
                                <tbody>
                                    <tr>
                                        <th>Direct %</th>
                                        <td class="text-end"><?= number_format((float)$rb['direct_sale_pct'], 2) ?>%</td>
                                    </tr>
                                    <tr>
                                        <th>L1 %</th>
                                        <td class="text-end"><?= number_format((float)$rb['l1_pct'], 2) ?>%</td>
                                    </tr>
                                    <tr>
                                        <th>L2 %</th>
                                        <td class="text-end"><?= number_format((float)$rb['l2_pct'], 2) ?>%</td>
                                    </tr>
                                    <tr>
                                        <th>L3 %</th>
                                        <td class="text-end"><?= number_format((float)$rb['l3_pct'], 2) ?>%</td>
                                    </tr>
                                    <tr>
                                        <th>Qualifying Volume</th>
                                        <td class="text-end">&#8377;<?= number_format((float)($rb['min_qualifying_volume'] ?? 0)) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Legs Required</th>
                                        <td class="text-end"><?= (int)($rb['min_leg_count'] ?? 0) ?></td>
                                    </tr>
                                </tbody>
                            </table></div>
                            <?php if (!empty($benefits)): ?>
                                <div class="mt-2">
                                    <small class="text-muted">Benefits:</small>
                                    <ul class="small m-0">
                                        <?php foreach ($benefits as $k => $v): ?>
                                            <li><strong><?= htmlspecialchars(ucwords(str_replace('_', ' ', (string)$k))) ?>:</strong> <?= htmlspecialchars((string)(is_array($v) ? implode(', ', $v) : $v)) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
