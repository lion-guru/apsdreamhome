<?php
/**
 * Customer Referral Leaderboard View
 * @var array $leaderboard
 * @var array $myRank
 * @var array $myStats
 * @var string $base
 * @var string $csrf_token
 */
$base = BASE_URL;
$page_title = $page_title ?? 'Referral Leaderboard';
?>
<?php include __DIR__ . '/../layouts/customer.php'; ?>
<?php ob_start(); ?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h3 class="mb-0"><i class="fas fa-trophy me-2 text-warning"></i>Referral Leaderboard</h3>
        <a href="<?= $base ?>/user/referrals" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to Referrals</a>
    </div>

    <!-- My Rank Card -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="mb-3"><i class="fas fa-user me-2"></i>Your Position</h5>
                    <?php if ($myRank['rank'] > 0): ?>
                        <div class="d-flex align-items-center gap-4">
                            <div class="d-flex align-items-center justify-content-center rounded-circle text-white fw-bold" 
                                 style="width:80px;height:80px;font-size:2rem;background:linear-gradient(135deg,<?= $myRank['tier'] === 'platinum' ? '#6366f1' : ($myRank['tier'] === 'gold' ? '#f59e0b' : ($myRank['tier'] === 'silver' ? '#94a3b8' : '#CD7F32')) ?>,<?= $myRank['tier'] === 'platinum' ? '#4f46e5' : ($myRank['tier'] === 'gold' ? '#d97706' : ($myRank['tier'] === 'silver' ? '#64748b' : '#b45309')) ?>);">
                                #<?= $myRank['rank'] ?>
                            </div>
                            <div>
                                <h3 class="mb-1">Rank #<?= $myRank['rank'] ?></h3>
                                <p class="text-muted mb-1">out of <?= $myRank['total'] ?> referrers</p>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge" style="background:<?= $myRank['tier'] === 'platinum' ? '#6366f1' : ($myRank['tier'] === 'gold' ? '#f59e0b' : ($myRank['tier'] === 'silver' ? '#94a3b8' : '#CD7F32')) ?>">
                                        <i class="fas fa-<?= $myRank['tier'] === 'platinum' ? 'gem' : ($myRank['tier'] === 'gold' ? 'crown' : 'medal') ?> me-1"></i>
                                        <?= ucfirst($myRank['tier']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-trophy fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">Not ranked yet</h4>
                            <p class="text-muted">Make your first referral to enter the leaderboard</p>
                            <a href="<?= $base ?>/user/referrals" class="btn btn-primary mt-2">Share Your Code</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="mb-3"><i class="fas fa-chart-line me-2"></i>Your Stats</h5>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="card bg-light border-0">
                                <div class="card-body text-center">
                                    <div class="fw-bold fs-4 text-primary"><?= number_format($myStats['total_referrals'] ?? 0) ?></div>
                                    <small class="text-muted">Total Referrals</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card bg-light border-0">
                                <div class="card-body text-center">
                                    <div class="fw-bold fs-4 text-success">₹<?= number_format((float)($myStats['total_earnings'] ?? 0), 2) ?></div>
                                    <small class="text-muted">Total Earnings</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card bg-light border-0">
                                <div class="card-body text-center">
                                    <div class="fw-bold fs-4 text-info"><?= number_format($myStats['this_month_referrals'] ?? 0) ?></div>
                                    <small class="text-muted">This Month</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card bg-light border-0">
                                <div class="card-body text-center">
                                    <div class="fw-bold fs-4 text-warning">₹<?= number_format((float)($myStats['this_month_earnings'] ?? 0), 2) ?></div>
                                    <small class="text-muted">This Month Earnings</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Share Your Code -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center g-3">
                <div class="col-md-8">
                    <h5 class="mb-1"><i class="fas fa-share-alt me-2 text-info"></i>Share Your Referral Code</h5>
                    <p class="text-muted mb-0">Invite friends & family to earn rewards on every signup and booking</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="input-group">
                        <input type="text" class="form-control" value="<?= $base ?>/register?ref=<?= $_SESSION['referral_code'] ?? '' ?>" readonly id="shareUrl">
                        <button class="btn btn-outline-primary" onclick="copyShareUrl()"><i class="fas fa-copy"></i> Copy</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaderboard Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Top Referrers</h5>
            <div class="btn-group btn-group-sm">
                <button class="btn btn-outline-primary active" data-period="all">All Time</button>
                <button class="btn btn-outline-secondary" data-period="yearly">This Year</button>
                <button class="btn btn-outline-secondary" data-period="monthly">This Month</button>
                <button class="btn btn-outline-secondary" data-period="weekly">This Week</button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="leaderboardTable">
                    <thead class="table-light">
                        <tr>
                            <th class="px-3 py-3" style="width:60px">Rank</th>
                            <th class="px-3 py-3">Referrer</th>
                            <th class="px-3 py-3" style="width:100px">Code</th>
                            <th class="px-3 py-3" style="width:120px">Referrals</th>
                            <th class="px-3 py-3" style="width:120px">Bookings</th>
                            <th class="px-3 py-3" style="width:80px">Tier</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($leaderboard as $entry): ?>
                            <tr class="<?= $entry['rank'] <= 3 ? 'table-warning' : '' ?>">
                                <td class="px-3 fw-bold">
                                    <?php if ($entry['rank'] === 1): ?>
                                        <i class="fas fa-crown text-warning me-1"></i>
                                    <?php elseif ($entry['rank'] === 2): ?>
                                        <i class="fas fa-medal text-secondary me-1"></i>
                                    <?php elseif ($entry['rank'] === 3): ?>
                                        <i class="fas fa-medal text-warning me-1"></i>
                                    <?php endif; ?>
                                    #<?= $entry['rank'] ?>
                                </td>
                                <td class="px-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-sm rounded-circle bg-gradient-primary d-flex align-items-center justify-content-center text-white fw-bold">
                                            <?= strtoupper(substr($entry['name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="fw-medium"><?= htmlspecialchars($entry['name']) ?></div>
                                            <small class="text-muted"><?= $entry['referral_count'] ?> signups</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3">
                                    <code class="bg-light px-2 py-1 rounded"><?= htmlspecialchars($entry['referral_code']) ?></code>
                                </td>
                                <td class="px-3 fw-bold text-primary"><?= $entry['referral_count'] ?></td>
                                <td class="px-3 fw-bold text-success"><?= $entry['booked_count'] ?? 0 ?></td>
                                <td class="px-3">
                                    <span class="badge" style="background:<?= $entry['tier_color'] ?>;">
                                        <i class="fas fa-<?= $entry['tier_icon'] ?> me-1"></i>
                                        <?= ucfirst($entry['tier']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if (empty($leaderboard)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No referrers on the leaderboard yet</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="text-center mt-4">
        <a href="<?= $base ?>/user/referrals" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back to Referrals Dashboard</a>
    </div>
</div>

<style>
.avatar-sm { width: 40px; height: 40px; font-size: 1rem; }
.bg-gradient-primary { background: linear-gradient(135deg, #0d9488, #14b8a6) !important; }
</style>

<script>
document.querySelectorAll('[data-period]').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('[data-period]').forEach(b => b.classList.remove('active', 'btn-outline-primary'), b.classList.add('btn-outline-secondary'));
        this.classList.add('active', 'btn-outline-primary');
        this.classList.remove('btn-outline-secondary');
        loadLeaderboard(this.dataset.period);
    });
});

async function loadLeaderboard(period) {
    try {
        const response = await fetch('<?= $base ?>/user/referrals/leaderboard/data?period=' + period, {
            headers: { 'X-CSRF-Token': '<?= $csrf_token ?>' }
        });
        const data = await response.json();
        if (data.success) {
            renderLeaderboard(data.leaderboard);
        }
    } catch (e) {
        console.error(e);
    }
}

function renderLeaderboard(leaderboard) {
    const tbody = document.querySelector('#leaderboardTable tbody');
    tbody.innerHTML = leaderboard.map((entry, i) => `
        <tr class="${entry.rank <= 3 ? 'table-warning' : ''}">
            <td class="px-3 fw-bold">
                ${entry.rank === 1 ? '<i class="fas fa-crown text-warning me-1"></i>' : ''}
                ${entry.rank === 2 ? '<i class="fas fa-medal text-secondary me-1"></i>' : ''}
                ${entry.rank === 3 ? '<i class="fas fa-medal text-warning me-1"></i>' : ''}
                #${entry.rank}
            </td>
            <td class="px-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="avatar-sm rounded-circle bg-gradient-primary d-flex align-items-center justify-content-center text-white fw-bold">
                        ${entry.name.charAt(0).toUpperCase()}
                    </div>
                    <div>
                        <div class="fw-medium">${entry.name}</div>
                        <small class="text-muted">${entry.referral_count} signups</small>
                    </div>
                </div>
            </td>
            <td class="px-3"><code class="bg-light px-2 py-1 rounded">${entry.referral_code}</code></td>
            <td class="px-3 fw-bold text-primary">${entry.referral_count}</td>
            <td class="px-3 fw-bold text-success">${entry.booked_count ?? 0}</td>
            <td class="px-3">
                <span class="badge" style="background:${entry.tier_color};">
                    <i class="fas fa-${entry.tier_icon} me-1"></i>
                    ${entry.tier.charAt(0).toUpperCase() + entry.tier.slice(1)}
                </span>
            </td>
        </tr>
    `).join('');
}
</script>

<script>
function copyShareUrl() {
    const input = document.getElementById('shareUrl');
    navigator.clipboard.writeText(input.value).then(() => {
        const btn = event.target.closest('button');
        const original = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check me-1"></i>Copied!';
        setTimeout(() => btn.innerHTML = original, 1500);
    });
}
</script>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layouts/customer.php'; ?>