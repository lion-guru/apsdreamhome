<?php
$user = $user ?? [];
$directReferrals = $directReferrals ?? [];
$team = $team ?? [];
$mlmProfile = $mlmProfile ?? null;
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?= $base ?>/admin/users/<?= $user['id'] ?>" class="text-decoration-none text-muted"><i class="fas fa-arrow-left me-2"></i><?= htmlspecialchars($user['name'] ?? '') ?></a>
        <h4 class="mt-2 mb-0"><i class="fas fa-users me-2 text-warning"></i>Team & Downline</h4>
    </div>
</div>

<!-- MLM Profile Summary -->
<?php if ($mlmProfile): ?>
<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h6 class="text-muted">Total Team</h6><h3><?= $mlmProfile['total_team_size'] ?? 0 ?></h3></div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h6 class="text-muted">Direct Referrals</h6><h3><?= $mlmProfile['direct_referrals'] ?? 0 ?></h3></div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h6 class="text-muted">Total Commission</h6><h3>₹<?= number_format((float)($mlmProfile['total_commission'] ?? 0)) ?></h3></div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h6 class="text-muted">Referral Code</h6><h5><code><?= htmlspecialchars($mlmProfile['referral_code'] ?? 'N/A') ?></code></h5></div></div></div>
</div>
<?php endif; ?>

<!-- Direct Referrals -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-user-friends me-2"></i>Direct Referrals (<?= count($directReferrals) ?>)</h6></div>
    <div class="card-body p-0">
        <?php if (empty($directReferrals)): ?>
        <div class="text-center py-4 text-muted">No direct referrals yet</div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light"><tr><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Status</th><th>Joined</th></tr></thead>
                <tbody>
                <?php foreach ($directReferrals as $m): ?>
                <tr>
                    <td><a href="<?= $base ?>/admin/users/<?= $m['associate_id'] ?>"><?= htmlspecialchars($m['name'] ?? '') ?></a></td>
                    <td><?= htmlspecialchars($m['email'] ?? '') ?></td>
                    <td><?= htmlspecialchars($m['phone'] ?? '') ?></td>
                    <td><span class="badge bg-info"><?= ucfirst($m['role'] ?? '') ?></span></td>
                    <td><span class="badge bg-<?= ($m['status'] ?? '') === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($m['status'] ?? '') ?></span></td>
                    <td><small><?= isset($m['created_at']) ? date('M d, Y', strtotime($m['created_at'])) : '' ?></small></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Full Team (3 Levels) -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-project-diagram me-2"></i>Full Team — 3 Levels Deep (<?= count($team) ?>)</h6></div>
    <div class="card-body p-0">
        <?php if (empty($team)): ?>
        <div class="text-center py-4 text-muted">No team members found</div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light"><tr><th>Level</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th></tr></thead>
                <tbody>
                <?php foreach ($team as $m): ?>
                <tr>
                    <td><span class="badge bg-<?= $m['depth'] <= 1 ? 'primary' : ($m['depth'] <= 2 ? 'info' : 'secondary') ?>">Gen <?= $m['depth'] ?></span></td>
                    <td><a href="<?= $base ?>/admin/users/<?= $m['associate_id'] ?>"><?= htmlspecialchars($m['name'] ?? '') ?></a></td>
                    <td><?= htmlspecialchars($m['email'] ?? '') ?></td>
                    <td><span class="badge bg-info"><?= ucfirst($m['role'] ?? '') ?></span></td>
                    <td><span class="badge bg-<?= ($m['status'] ?? '') === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($m['status'] ?? '') ?></span></td>
                    <td><small><?= isset($m['created_at']) ? date('M d, Y', strtotime($m['created_at'])) : '' ?></small></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
