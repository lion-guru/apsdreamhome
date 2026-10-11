<?php
/** @var array $board */
$board = $board ?? [];
$myRank = $myRank ?? 0;
$mySpotBonus = $mySpotBonus ?? 0;
$base = defined('BASE_URL') ? BASE_URL : '';
$medal = [1 => '🥇', 2 => '🥈', 3 => '🥉'];
$badgeClass = ['Gold' => 'warning text-dark', 'Silver' => 'secondary', 'Bronze' => 'danger'];
?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-1"><i class="fas fa-trophy me-2 text-warning"></i>Sunday Conversion Leaderboard</h4>
        <p class="text-muted mb-0 small">Completed site-visit trips · last 30 days · tokens decide the rank</p>
    </div>
    <a href="<?= $base ?>/associate/dashboard" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Dashboard</a>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="aps-cp-card text-center"><div class="aps-cp-card-body py-3">
            <div class="fw-bold fs-3 text-primary"><?= $myRank > 0 ? ('#' . $myRank) : '—' ?></div>
            <div class="text-muted small">Your Rank</div>
        </div></div>
    </div>
    <div class="col-md-6">
        <div class="aps-cp-card text-center"><div class="aps-cp-card-body py-3">
            <div class="fw-bold fs-3 text-success">₹<?= number_format($mySpotBonus, 0) ?></div>
            <div class="text-muted small">Your Spot-Booking Bonuses (₹5,000 × token ≤ 48h of visit)</div>
        </div></div>
    </div>
</div>

<div class="aps-cp-card">
    <div class="aps-cp-card-header"><span><i class="fas fa-medal me-2"></i>Top Converters</span></div>
    <div class="aps-cp-card-body p-0">
        <?php if (empty($board)): ?>
            <div class="text-center text-muted py-4">No completed trips yet — be the first on the board this Sunday!</div>
        <?php else: ?>
        <div class="table-responsive"><table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>#</th><th>Associate</th><th class="text-end">Trips</th><th class="text-end">Visited</th><th class="text-end">Tokens</th><th class="text-end">Conv.</th><th>Badge</th></tr></thead>
            <tbody>
            <?php foreach ($board as $b): ?>
                <tr class="<?= !empty($b['is_me']) ? 'table-primary fw-bold' : '' ?>">
                    <td class="fs-5"><?= $medal[$b['rank']] ?? $b['rank'] ?></td>
                    <td><?= htmlspecialchars($b['name']) ?><?= !empty($b['is_me']) ? ' <span class="badge bg-primary">You</span>' : '' ?></td>
                    <td class="text-end"><?= $b['trips'] ?></td>
                    <td class="text-end"><?= $b['visited'] ?></td>
                    <td class="text-end text-success fw-bold"><?= $b['tokens'] ?></td>
                    <td class="text-end"><?= $b['conv'] ?>%</td>
                    <td><?= $b['badge'] !== '' ? '<span class="badge bg-' . $badgeClass[$b['badge']] . '">' . $b['badge'] . '</span>' : '<span class="text-muted">—</span>' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
        <?php endif; ?>
    </div>
</div>
<div class="alert alert-info mt-3 small"><i class="fas fa-bolt me-1"></i>
    Token within <strong>48 hours</strong> of your completed site visit = automatic <strong>₹5,000 bonus</strong> in your wallet. Close on Sunday, get paid for urgency!
</div>
