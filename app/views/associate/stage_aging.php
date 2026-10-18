<?php
$rows = $rows ?? [];
?>
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-1">
        <div>
            <h4 class="mb-1"><i class="fas fa-hourglass-half text-primary me-2"></i><?= __('assoc_age_title', [], 'Stage Aging') ?></h4>
            <small class="text-muted"><?= __('assoc_age_sub', [], 'Average days your leads spend per stage — the longest bar is your bottleneck') ?></small>
        </div>
        <a href="<?= BASE_URL ?>/associate/reports/business" class="btn btn-outline-secondary btn-sm"><i class="fas fa-chart-line me-1"></i><?= __('assoc_age_reports', [], 'Business Report') ?></a>
    </div>
    <div class="card border-0 shadow-sm mt-3"><div class="card-body p-0">
        <?php if (empty($rows)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-hourglass fa-3x mb-3"></i>
                <h5><?= __('assoc_age_empty', [], 'Not enough movement yet') ?></h5>
                <p class="small mb-0"><?= __('assoc_age_empty_desc', [], 'Move leads through stages — aging appears after a few transitions.') ?></p>
            </div>
        <?php else: ?>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0">
            <thead class="bg-light"><tr><th><?= __('assoc_age_th_stage', [], 'Stage') ?></th><th class="text-end"><?= __('assoc_age_th_avg', [], 'Avg days') ?></th><th class="text-end"><?= __('assoc_age_th_exits', [], 'Exits') ?></th></tr></thead>
            <tbody>
            <?php $max = max(array_column($rows, 'avg_days')) ?: 1; ?>
            <?php foreach ($rows as $r): $w = min(100, round($r['avg_days'] / $max * 100)); ?>
                <tr>
                    <td><strong><?= htmlspecialchars($r['stage'] ?? '') ?></strong><div class="progress mt-1" style="height:6px;max-width:220px"><div class="progress-bar <?= $w >= 70 ? 'bg-danger' : ($w >= 40 ? 'bg-warning' : 'bg-success') ?>" style="width:<?= $w ?>%"></div></div></td>
                    <td class="text-end fw-bold"><?= $r['avg_days'] ?></td>
                    <td class="text-end text-muted"><?= number_format($r['exits']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
        <?php endif; ?>
    </div></div>
    <p class="text-muted small mt-3"><i class="fas fa-info-circle me-1"></i><?= __('assoc_age_note', [], 'Computed from your status-change timeline (last 5,000 events). Stages never exited yet do not appear.') ?></p>
</div>
