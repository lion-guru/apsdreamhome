<?php
$page_title = $page_title ?? 'Referral Leaderboard';
$base = defined('BASE_URL') ? BASE_URL : '';
$leaderboard = $leaderboard ?? [];
$tiers = $tiers ?? [];
$current_period = $current_period ?? 'all';
?>
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="m-0"><i class="fas fa-trophy me-2 text-warning"></i>Referral Leaderboard</h4>
        <div class="d-flex gap-2">
            <a href="?period=all" class="btn btn-sm <?= $current_period === 'all' ? 'btn-primary' : 'btn-outline-secondary' ?>">All Time</a>
            <a href="?period=yearly" class="btn btn-sm <?= $current_period === 'yearly' ? 'btn-primary' : 'btn-outline-secondary' ?>">Yearly</a>
            <a href="?period=monthly" class="btn btn-sm <?= $current_period === 'monthly' ? 'btn-primary' : 'btn-outline-secondary' ?>">Monthly</a>
            <a href="?period=weekly" class="btn btn-sm <?= $current_period === 'weekly' ? 'btn-primary' : 'btn-outline-secondary' ?>">Weekly</a>
        </div>
    </div>

    <!-- Top 3 Podium -->
    <?php if (count($leaderboard) >= 3): ?>
    <div class="row mb-4">
        <div class="col-md-4 offset-md-4">
            <div class="card border-0 shadow-sm text-center py-4 style-32232">
                <div class="style-20922">ðŸ‘‘</div>
                <h5 class="mb-1"><?= htmlspecialchars($leaderboard[0]['name'] ?? '') ?></h5>
                <div class="text-muted small mb-2"><?= $leaderboard[0]['referral_count'] ?? 0 ?> referrals</div>
                <span class="badge style-95202">
                    <i class="<?= $leaderboard[0]['tier_icon'] ?? 'fas fa-medal' ?> me-1"></i><?= ucfirst($leaderboard[0]['tier'] ?? 'bronze') ?>
                </span>
            </div>
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center py-3 style-52654">
                <div class="style-46757">ðŸ¥ˆ</div>
                <h6 class="mb-1"><?= htmlspecialchars($leaderboard[1]['name'] ?? '') ?></h6>
                <div class="text-muted small"><?= $leaderboard[1]['referral_count'] ?? 0 ?> referrals</div>
                <span class="badge mt-1 style-70306"><?= ucfirst($leaderboard[1]['tier'] ?? 'silver') ?></span>
            </div>
        </div>
        <div class="col-md-4 offset-md-4">
            <div class="card border-0 shadow-sm text-center py-3 style-43816">
                <div class="style-46757">ðŸ¥‰</div>
                <h6 class="mb-1"><?= htmlspecialchars($leaderboard[2]['name'] ?? '') ?></h6>
                <div class="text-muted small"><?= $leaderboard[2]['referral_count'] ?? 0 ?> referrals</div>
                <span class="badge mt-1 style-60503"><?= ucfirst($leaderboard[2]['tier'] ?? 'bronze') ?></span>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Full Leaderboard Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white"><h5 class="m-0">All Rankings</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="60" class="text-center">Rank</th>
                            <th>Name</th>
                            <th class="text-center">Tier</th>
                            <th class="text-center">Referrals</th>
                            <th class="text-center">Signups</th>
                            <th class="text-center">Bookings</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($leaderboard)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">No referrals found</td></tr>
                        <?php else: ?>
                        <?php foreach ($leaderboard as $entry): ?>
                        <tr>
                            <td class="text-center">
                                <?php if (($entry['rank'] ?? 0) <= 3): ?>
                                <span class="style-30322">
                                    <?= $entry['rank'] == 1 ? 'ðŸ¥‡' : ($entry['rank'] == 2 ? 'ðŸ¥ˆ' : 'ðŸ¥‰') ?>
                                </span>
                                <?php else: ?>
                                <strong>#<?= $entry['rank'] ?></strong>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($entry['name'] ?? '') ?></strong>
                                <?php if (!empty($entry['referral_code'])): ?>
                                <br><small class="text-muted">Code: <?= htmlspecialchars($entry['referral_code'] ?? '') ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge style-8334">
                                    <i class="<?= $entry['tier_icon'] ?? 'fas fa-medal' ?> me-1"></i><?= ucfirst($entry['tier'] ?? 'bronze') ?>
                                </span>
                            </td>
                            <td class="text-center"><strong><?= $entry['referral_count'] ?? 0 ?></strong></td>
                            <td class="text-center"><?= $entry['total_signups'] ?? 0 ?></td>
                            <td class="text-center"><?= $entry['booked_count'] ?? 0 ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
