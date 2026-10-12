<?php
/** Associate bonanza view: my progress + top 10. */
/** @var array $my_races */
/** @var array $leaders */
/** @var array|null $active_race */
$my_races = $my_races ?? [];
$leaders = $leaders ?? [];
$active_race = $active_race ?? null;
$e = function ($v) { return htmlspecialchars((string)($v ?? '')); };
$medals = ['🥇', '🥈', '🥉'];
?>
<div class="container-fluid px-4 py-3">
    <h4 class="fw-bold mb-1"><i class="fas fa-trophy text-warning me-2"></i>Bonanza Races</h4>
    <p class="text-muted">Seasonal booking contests — hit the target, win the prize.</p>
    <?php if (empty($my_races)): ?>
        <div class="alert alert-info">No live race right now — check back soon.</div>
    <?php else: foreach ($my_races as $mr):
        $race = $mr['race']; $mine = (int)$mr['mine']; $target = max(1, (int)($race['target_bookings'] ?? 5));
        $pct = min(100, round(($mine / $target) * 100));
    ?>
        <div class="card border-0 shadow-sm mb-3"><div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div><strong><?= $e($race['race_name'] ?? '') ?></strong>
                    <div class="small text-muted"><?= $e($race['start_date'] ?? '') ?> → <?= $e($race['end_date'] ?? '') ?><?= $e($race['prize_text'] ?? '') !== '' ? ' · 🏆 ' . $e($race['prize_text']) : '' ?></div>
                </div>
                <div class="text-end"><span class="fs-5 fw-bold"><?= $mine ?>/<?= $target ?></span><div class="small text-muted">your bookings</div></div>
            </div>
            <div class="progress mt-2" style="height:12px;"><div class="progress-bar <?= $pct >= 100 ? 'bg-success' : 'bg-primary' ?>" style="width:<?= $pct ?>%"></div></div>
        </div></div>
    <?php endforeach; endif; ?>
    <?php if ($active_race): ?>
        <div class="card border-0 shadow-sm"><div class="card-body">
            <h6 class="fw-bold">🏆 Leaderboard — <?= $e($active_race['race_name'] ?? '') ?></h6>
            <?php if (empty($leaders)): ?>
                <p class="text-muted small mb-0">No sales yet — be the first!</p>
            <?php else: foreach ($leaders as $i => $s): ?>
                <div class="d-flex justify-content-between small py-1 border-bottom">
                    <span><?= $e($medals[$i] ?? (($i + 1) . '.')) ?> <?= $e($s['name'] ?? '') ?></span>
                    <strong><?= (int)$s['booked'] ?> bookings</strong>
                </div>
            <?php endforeach; endif; ?>
        </div></div>
    <?php endif; ?>
</div>
