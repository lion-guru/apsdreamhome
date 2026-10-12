<?php
/** Race leaderboard with progress bars. */
/** @var array $race */
/** @var array $standings */
$race = $race ?? [];
$standings = $standings ?? [];
$base = defined('BASE_URL') ? BASE_URL : '';
$e = function ($v) { return htmlspecialchars((string)($v ?? '')); };
$target = max(1, (int)($race['target_bookings'] ?? 5));
$medals = ['🥇', '🥈', '🥉'];
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1"><?= $medals[0] ?> <?= $e($race['race_name'] ?? '') ?></h4>
        <p class="text-muted mb-0"><?= $e($race['start_date'] ?? '') ?> → <?= $e($race['end_date'] ?? '') ?> · target <?= $target ?> bookings<?= $e($race['prize_text'] ?? '') !== '' ? ' · prize: ' . $e($race['prize_text']) : '' ?></p>
    </div>
    <a href="<?= $base ?>/admin/sales/bonanza-races" class="btn btn-outline-secondary btn-sm">← Races</a>
</div>
<div class="aps-cp-card"><div class="aps-cp-card-body">
    <?php if (empty($standings)): ?>
        <p class="text-muted mb-0">No bookings in this window yet — the board lights up with the first sale.</p>
    <?php else: foreach ($standings as $i => $s):
        $pct = min(100, round(((int)$s['booked'] / $target) * 100));
    ?>
        <div class="mb-3">
            <div class="d-flex justify-content-between small mb-1">
                <span><?= $e($medals[$i] ?? (($i + 1) . '.')) ?> <strong><?= $e($s['name'] ?? '') ?></strong></span>
                <span><?= (int)$s['booked'] ?>/<?= $target ?> bookings</span>
            </div>
            <div class="progress" style="height:10px;"><div class="progress-bar <?= $pct >= 100 ? 'bg-success' : 'bg-primary' ?>" style="width:<?= $pct ?>%"></div></div>
        </div>
    <?php endforeach; endif; ?>
</div></div>
