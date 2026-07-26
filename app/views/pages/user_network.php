<?php
/**
 * User MLM Network Tree Page
 * Shows downline team tree visualization
 */

// Auth handled by UserController::network() (requireLogin() added in Phase 1.5)

$db = \App\Core\Database\Database::getInstance();
$userId = $_SESSION['user_id'];

// Get user info
$user = $db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);

// Check if MLM is enabled for this user
$mlmEnabled = !empty($user['referral_code']);

// Get direct referrals (Level 1)
$directReferrals = $db->fetchAll(
    "SELECT u.id, u.name, u.email, u.phone, u.created_at, u.role as user_type,
            (SELECT COUNT(*) FROM users WHERE referred_by = u.id) as downline_count
     FROM users u 
     WHERE u.referred_by = ?
     ORDER BY u.created_at DESC",
    [$userId]
);

// Get total downline count (all levels)
$totalDownline = $db->fetch(
    "SELECT COUNT(*) as cnt FROM users WHERE referred_by IN (
        SELECT id FROM users WHERE referred_by = ?
    )",
    [$userId]
);

try {
    // Get MLM stats
    $stats = [
        'direct_referrals' => count($directReferrals),
        'total_downline' => $totalDownline['cnt'] ?? 0,
        'total_points' => 0,
        'total_earnings' => $db->fetch("SELECT COALESCE(SUM(amount), 0) as total FROM mlm_commission_ledger WHERE beneficiary_user_id = ?", [$userId])['total'] ?? 0,
    ];
} catch (\Throwable $e) {
    $stats = [
        'direct_referrals' => count($directReferrals),
        'total_downline' => $totalDownline['cnt'] ?? 0,
        'total_points' => 0,
        'total_earnings' => 0,
    ];
}

// Get recent activity
try {
    $recentActivity = $db->fetchAll(
        "SELECT l.*, u.name as from_user
         FROM mlm_commission_ledger l
         LEFT JOIN users u ON l.beneficiary_user_id = u.id
         WHERE l.beneficiary_user_id = ?
         ORDER BY l.created_at DESC LIMIT 10",
        [$userId]
    );
} catch (\Throwable $e) {
    $recentActivity = [];
}
?>

<div class="container-fluid py-4">
    <div class="row">
        <!-- Header -->
        <div class="col-12 mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-sitemap me-2 text-primary"></i><?php echo __('network_title', [], 'My Network'); ?></h2>
                <a href="/user/dashboard" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i><?php echo __('network_back_dashboard', [], 'Back to Dashboard'); ?>
                </a>
            </div>
        </div>

        <?php if (!$mlmEnabled): ?>
        <!-- MLM Not Enabled -->
        <div class="col-12">
            <div class="alert alert-info">
                <h5><i class="fas fa-info-circle me-2"></i><?php echo __('network_mlm_not_enabled', [], 'MLM Program Not Enabled'); ?></h5>
                <p><?php echo __('network_mlm_not_enabled_desc', [], 'Contact support to enable MLM features for your account and start earning from referrals!'); ?></p>
                <a href="/contact" class="btn btn-primary"><?php echo __('network_contact_support', [], 'Contact Support'); ?></a>
            </div>
        </div>
        <?php else: ?>

        <!-- Stats Cards -->
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?= $stats['direct_referrals'] ?></h3>
                    <small><?php echo __('network_direct_referrals', [], 'Direct Referrals'); ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?= $stats['total_downline'] ?></h3>
                    <small><?php echo __('network_total_team', [], 'Total Team Size'); ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?= number_format($stats['total_points'] ?? 0) ?></h3>
                    <small><?php echo __('network_total_points', [], 'Total Points'); ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">₹<?= number_format($stats['total_earnings'] ?? 0) ?></h3>
                    <small><?php echo __('network_total_earnings', [], 'Total Earnings'); ?></small>
                </div>
            </div>
        </div>

        <!-- Referral Link -->
        <div class="col-12 mt-4">
            <div class="card border-primary">
                <div class="card-body aps-cp-card-body">
                    <h5><i class="fas fa-link me-2"></i><?php echo __('network_referral_link', [], 'Your Referral Link'); ?></h5>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" value="<?= BASE_URL ?>/register?ref=<?= htmlspecialchars($user['referral_code'] ?? $userId) ?>" readonly>
                        <button class="btn btn-primary" onclick="copyReferralLink()">
                            <i class="fas fa-copy"></i> <?php echo __('network_copy', [], 'Copy'); ?>
                        </button>
                    </div>
                    <small class="text-muted"><?php echo __('network_share_desc', [], 'Share this link with friends - They\'ll get 10% extra discount, you\'ll earn commission!'); ?></small>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <!-- Direct Referrals -->
            <div class="col-lg-8">
                <div class="card aps-cp-card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i><?php echo __('network_direct_referrals_count', [], 'Direct Referrals'); ?> (<?= count($directReferrals) ?>)</h5>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <?php if (empty($directReferrals)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-user-friends fa-4x text-muted mb-3"></i>
                                <p class="text-muted"><?php echo __('network_no_referrals', [], 'No referrals yet. Share your referral link to grow your network!'); ?></p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <div class="table-responsive"><table class="table table-hover table-responsive">
                                    <thead>
                                        <tr>
                                            <th><?php echo __('network_th_name', [], 'Name'); ?></th>
                                            <th><?php echo __('network_th_contact', [], 'Contact'); ?></th>
                                            <th><?php echo __('network_th_type', [], 'Type'); ?></th>
                                            <th><?php echo __('network_th_team', [], 'Team'); ?></th>
                                            <th><?php echo __('network_th_joined', [], 'Joined'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($directReferrals as $ref): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($ref['name'] ?? 'N/A') ?></strong>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($ref['phone'] ?? 'N/A') ?><br>
                                                <small class="text-muted"><?= htmlspecialchars($ref['email'] ?? '') ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $ref['user_type'] === 'agent' ? 'success' : 'secondary' ?>">
                                                    <?= ucfirst($ref['user_type'] ?? 'customer') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($ref['downline_count'] > 0): ?>
                                                    <span class="badge bg-info"><?= $ref['downline_count'] ?> <?php echo __('network_members', [], 'members'); ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?= date('d M Y', strtotime($ref['created_at'])) ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="col-lg-4">
                <div class="card aps-cp-card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i><?php echo __('network_recent_activity', [], 'Recent Activity'); ?></h5>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <?php if (empty($recentActivity)): ?>
                            <p class="text-muted text-center"><?php echo __('network_no_activity', [], 'No activity yet'); ?></p>
                        <?php else: ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($recentActivity as $activity): ?>
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <small><?= htmlspecialchars(str_replace('_', ' ', $activity['commission_type'] ?? 'Activity')) ?></small>
                                            <?php if (!empty($activity['from_user'])): ?>
                                                <br><small class="text-muted">From: <?= htmlspecialchars($activity['from_user']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-end">
                                            <?php if (($activity['amount'] ?? 0) > 0): ?>
                                                <span class="text-success">+₹<?= number_format($activity['amount']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <small class="text-muted"><?= date('d M, h:i A', strtotime($activity['created_at'])) ?></small>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Network Tree Visualization -->
        <div class="col-12 mt-4">
            <div class="card aps-cp-card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-sitemap me-2"></i><?php echo __('network_tree', [], 'Network Tree'); ?></h5>
                </div>
                <div class="card-body text-center" style="min-height: 400px; overflow-x: auto;">
                    <!-- Simple Tree Visualization -->
                    <div class="network-tree">
                        <div class="tree-level">
                            <!-- Root Node - Current User -->
                            <div class="tree-node root">
                                <div class="node-content">
                                    <i class="fas fa-user-circle fa-2x mb-2"></i>
                                    <div class="fw-bold"><?= htmlspecialchars($user['name'] ?? 'You') ?></div>
                                    <small><?= count($directReferrals) ?> referrals</small>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (!empty($directReferrals)): ?>
                        <div class="tree-connector"></div>
                        <div class="tree-level">
                            <?php foreach ($directReferrals as $ref): ?>
                            <div class="tree-node">
                                <div class="node-content">
                                    <i class="fas fa-user fa-lg mb-2"></i>
                                    <div class="fw-bold"><?= htmlspecialchars($ref['name'] ?? 'Member') ?></div>
                                    <small><?= $ref['downline_count'] ?> downline</small>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="mt-4">
                            <p class="text-muted"><?php echo __('network_team_empty', [], 'Your team will appear here when you have referrals'); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php endif; ?>
    </div>
</div>

<!-- Copy to Clipboard Script -->
<script>
function copyReferralLink() {
    const input = document.querySelector('input[readonly]');
    input.select();
    document.execCommand('copy');
    
    // Show feedback
    const btn = document.querySelector('button[onclick="copyReferralLink()"]');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check"></i> ' + '<?php echo addslashes(__('network_copied', [], 'Copied!')); ?>';
    btn.classList.add('btn-success');
    
    setTimeout(() => {
        btn.innerHTML = originalText;
        btn.classList.remove('btn-success');
    }, 2000);
}
</script>

<style>
.network-tree {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px;
}

.tree-level {
    display: flex;
    justify-content: center;
    gap: 15px;
    flex-wrap: wrap;
}

.tree-node {
    background: #f8f9fa;
    border: 2px solid #dee2e6;
    border-radius: 10px;
    padding: 15px 20px;
    min-width: 120px;
    transition: all 0.3s;
}

.tree-node:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.tree-node.root {
    background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
    color: white;
    border-color: #0d9488;
}

.tree-node .node-content {
    text-align: center;
}

.tree-connector {
    width: 2px;
    height: 30px;
    background: #dee2e6;
    margin: 10px 0;
}
</style>
