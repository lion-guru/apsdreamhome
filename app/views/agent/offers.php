<?php
$page_title = $page_title ?? 'Offers - Agent Portal';
$offers = $offers ?? [];
?>
<div class="container-fluid px-4">
    <h3 class="mt-3 mb-1"><i class="fas fa-tags me-2"></i>Company Offers</h3>
    <p class="text-muted mb-4">Live festival &amp; season campaigns and your progress toward each target.</p>
    <?php if (empty($offers)): ?>
    <div class="alert alert-info">No live offers right now — check back soon.</div>
    <?php endif; ?>
    <div class="row">
        <?php foreach ($offers as $o): ?>
        <?php
            $target = (float)($o['criteria_value'] ?? 0);
            $val = (float)($o['progress']['value'] ?? 0);
            $pct = $target > 0 ? min(100, round($val / $target * 100)) : 0;
            $isCount = ($o['criteria_type'] ?? '') === 'booking_count';
        ?>
        <div class="col-md-6 mb-4">
            <div class="card h-100 <?= !empty($o['achieved']) ? 'border-success' : '' ?>">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="card-title mb-0"><?= htmlspecialchars($o['title'] ?? '') ?></h5>
                        <?php if (!empty($o['achieved'])): ?><span class="badge bg-success">Achieved</span><?php endif; ?>
                    </div>
                    <p class="text-muted small"><?= htmlspecialchars($o['description'] ?? '') ?></p>
                    <p class="mb-1"><strong>Reward:</strong>
                        <?= ($o['reward_type'] ?? '') === 'commission_boost_pct' ? '+' . htmlspecialchars($o['reward_value']) . '% commission' : (($o['reward_type'] ?? '') === 'gift' ? 'Gift: ' . htmlspecialchars($o['reward_value']) : '₹' . number_format($o['reward_value'] ?? 0, 0) . ' bonus') ?></p>
                    <p class="mb-1 small text-muted">Valid <?= htmlspecialchars($o['starts_at'] ?? '') ?> → <?= htmlspecialchars($o['ends_at'] ?? '') ?><?= !empty($o['colony_name']) ? ' · ' . htmlspecialchars($o['colony_name']) : '' ?></p>
                    <div class="d-flex justify-content-between small mb-1"><span>Your progress</span><span><strong><?= $isCount ? (int)$val . ' / ' . (int)$target . ' bookings' : '₹' . number_format($val, 0) . ' / ₹' . number_format($target, 0) ?></strong></span></div>
                    <div class="progress"><div class="progress-bar <?= !empty($o['achieved']) ? 'bg-success' : '' ?>" role="progressbar" style="width: <?= $pct ?>%"><?= $pct ?>%</div></div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
