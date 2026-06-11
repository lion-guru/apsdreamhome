<?php
$pageTitle = $pageTitle ?? 'Member Details';
$base = $base ?? (defined('BASE_URL') ? BASE_URL : '/apsdreamhome');
$member = $member ?? ['id' => 0, 'name' => '', 'email' => '', 'phone' => '', 'points' => 0, 'tier' => '', 'status' => 'active', 'join_date' => '', 'total_redeemed' => 0];
$points_history = $points_history ?? [];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-user me-2 text-primary"></i>Member Details</h1>
        <a href="<?= $base ?>/admin/loyalty/members" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="row">
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Member Info</h6></div>
                <div class="card-body aps-cp-card-body">
                    <table class="table table-sm">
                        <tr><th>Name</th><td><?= htmlspecialchars($member['name'] ?? '') ?></td></tr>
                        <tr><th>Email</th><td><?= htmlspecialchars($member['email'] ?? '') ?></td></tr>
                        <tr><th>Phone</th><td><?= htmlspecialchars($member['phone'] ?? 'N/A') ?></td></tr>
                        <tr><th>Tier</th><td><?php $t = $member['tier'] ?? 'bronze'; ?>
                            <span class="badge bg-<?= $t === 'diamond' ? 'dark' : ($t === 'platinum' ? 'primary' : ($t === 'gold' ? 'warning' : ($t === 'silver' ? 'secondary' : 'light text-dark'))) ?>"><?= ucfirst($t) ?></span>
                        </td></tr>
                        <tr><th>Points</th><td><strong class="text-warning"><?= number_format(intval($member['points'] ?? 0)) ?></strong></td></tr>
                        <tr><th>Status</th><td><span class="badge bg-<?= ($member['status'] ?? 'active') === 'active' ? 'success' : 'danger' ?>"><?= ucfirst($member['status'] ?? 'active') ?></span></td></tr>
                        <tr><th>Joined</th><td><?= htmlspecialchars($member['join_date'] ?? $member['created_at'] ?? '') ?></td></tr>
                        <tr><th>Total Redeemed</th><td><?= number_format(intval($member['total_redeemed'] ?? 0)) ?> pts</td></tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Points History</h6></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (empty($points_history)): ?>
                        <p class="text-muted text-center py-3"><i class="fas fa-coins fa-2x d-block mb-2"></i>No points history available.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead><tr><th>Date</th><th>Type</th><th>Points</th><th>Description</th></tr></thead>
                                <tbody>
                                    <?php foreach ($points_history as $h): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($h['created_at'] ?? $h['date'] ?? '') ?></td>
                                        <td><?php $type = $h['type'] ?? ''; ?>
                                            <span class="badge bg-<?= $type === 'earned' ? 'success' : ($type === 'redeemed' ? 'danger' : 'info') ?>"><?= ucfirst($type) ?></span>
                                        </td>
                                        <td><strong class="<?= ($h['points'] ?? 0) >= 0 ? 'text-success' : 'text-danger' ?>"><?= ($h['points'] ?? 0) >= 0 ? '+' : '' ?><?= number_format(intval($h['points'] ?? 0)) ?></strong></td>
                                        <td><?= htmlspecialchars($h['description'] ?? '') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card shadow">
                <div class="card-header py-3"><h6 class="m-0 fw-bold text-success">Rewards Claimed</h6></div>
                <div class="card-body aps-cp-card-body">
                    <?php $rewards = $member['rewards_claimed'] ?? $member['redemptions'] ?? []; ?>
                    <?php if (empty($rewards)): ?>
                        <p class="text-muted text-center py-3">No rewards claimed yet.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead><tr><th>Reward</th><th>Points Spent</th><th>Date</th><th>Status</th></tr></thead>
                                <tbody>
                                    <?php foreach ($rewards as $r): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($r['reward_name'] ?? $r['name'] ?? '') ?></td>
                                        <td><?= number_format(intval($r['points_spent'] ?? $r['points'] ?? 0)) ?></td>
                                        <td><?= htmlspecialchars($r['created_at'] ?? $r['date'] ?? '') ?></td>
                                        <td><span class="badge bg-<?= ($r['status'] ?? 'completed') === 'completed' ? 'success' : 'warning' ?>"><?= ucfirst($r['status'] ?? 'completed') ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
