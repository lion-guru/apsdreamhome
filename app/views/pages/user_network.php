<?php
/**
 * User MLM Network Tree Page
 * Shows downline team tree visualization
 */

// Check if user is logged in
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    $_SESSION['flash_error'] = 'Please login first';
    header('Location: /login?redirect=/user/network');
    exit;
}

$db = \App\Core\Database\Database::getInstance();
$userId = $_SESSION['user_id'];

// Get user info
$user = $db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);

// Check if MLM is enabled for this user
$mlmEnabled = !empty($user['referral_code']);

// Get direct referrals (Level 1)
$directReferrals = $db->fetchAll(
    "SELECT u.id, u.name, u.email, u.phone, u.created_at, u.user_type,
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

// Get MLM stats
$stats = [
    'direct_referrals' => count($directReferrals),
    'total_downline' => $totalDownline['cnt'] ?? 0,
    'total_points' => $db->fetch("SELECT SUM(points) as total FROM mlm_points WHERE user_id = ?", [$userId])['total'] ?? 0,
    'total_earnings' => $db->fetch("SELECT SUM(amount) as total FROM mlm_earnings WHERE user_id = ?", [$userId])['total'] ?? 0,
];

// Get recent activity
$recentActivity = $db->fetchAll(
    "SELECT m.*, u.name as from_user 
     FROM mlm_transactions m 
     LEFT JOIN users u ON m.from_user_id = u.id 
     WHERE m.user_id = ? 
     ORDER BY m.created_at DESC LIMIT 10",
    [$userId]
);
?>

<div class="container-fluid py-4">
    <div class="row">
        <!-- Header -->
        <div class="col-12 mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-sitemap me-2 text-primary"></i>My Network</h2>
                <a href="/user/dashboard" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>

        <?php if (!$mlmEnabled): ?>
        <!-- MLM Not Enabled -->
        <div class="col-12">
            <div class="alert alert-info">
                <h5><i class="fas fa-info-circle me-2"></i>MLM Program Not Enabled</h5>
                <p>Contact support to enable MLM features for your account and start earning from referrals!</p>
                <a href="/contact" class="btn btn-primary">Contact Support</a>
            </div>
        </div>
        <?php else: ?>

        <!-- Stats Cards -->
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?= $stats['direct_referrals'] ?></h3>
                    <small>Direct Referrals</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?= $stats['total_downline'] ?></h3>
                    <small>Total Team Size</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?= number_format($stats['total_points'] ?? 0) ?></h3>
                    <small>Total Points</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">₹<?= number_format($stats['total_earnings'] ?? 0) ?></h3>
                    <small>Total Earnings</small>
                </div>
            </div>
        </div>

        <!-- Referral Link -->
        <div class="col-12 mt-4">
            <div class="card border-primary">
                <div class="card-body">
                    <h5><i class="fas fa-link me-2"></i>Your Referral Link</h5>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" value="<?= BASE_URL ?>/register?ref=<?= htmlspecialchars($user['referral_code'] ?? $userId) ?>" readonly>
                        <button class="btn btn-primary" onclick="copyReferralLink()">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>
                    <small class="text-muted">Share this link with friends - They'll get 10% extra discount, you'll earn commission!</small>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <!-- Direct Referrals -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Direct Referrals (<?= count($directReferrals) ?>)</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($directReferrals)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-user-friends fa-4x text-muted mb-3"></i>
                                <p class="text-muted">No referrals yet. Share your referral link to grow your network!</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Contact</th>
                                            <th>Type</th>
                                            <th>Team</th>
                                            <th>Joined</th>
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
                                                    <span class="badge bg-info"><?= $ref['downline_count'] ?> members</span>
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
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Activity</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentActivity)): ?>
                            <p class="text-muted text-center">No activity yet</p>
                        <?php else: ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($recentActivity as $activity): ?>
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <small><?= htmlspecialchars($activity['transaction_type'] ?? 'Activity') ?></small>
                                            <?php if ($activity['from_user']): ?>
                                                <br><small class="text-muted">From: <?= htmlspecialchars($activity['from_user']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-end">
                                            <?php if ($activity['amount'] > 0): ?>
                                                <span class="text-success">+₹<?= number_format($activity['amount']) ?></span>
                                            <?php elseif ($activity['points'] != 0): ?>
                                                <span class="text-info"><?= $activity['points'] > 0 ? '+' : '' ?><?= $activity['points'] ?> pts</span>
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
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-sitemap me-2"></i>Network Tree</h5>
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
                            <p class="text-muted">Your team will appear here when you have referrals</p>
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
    btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
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
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-color: #667eea;
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
