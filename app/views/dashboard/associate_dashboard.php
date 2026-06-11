<?php

/**
 * Associate Dashboard View
 */

$page_title = $page_title ?? 'Associate Dashboard';
$current_page = 'dashboard';
$referral_code = $referral_code ?? '';
$associate_name = $associate_name ?? 'Associate';

$stats = $stats ?? [
    'total_leads' => 0, 'active_leads' => 0, 'properties_sold' => 0,
    'total_commission' => 0, 'pending_commission' => 0, 'commission_this_month' => 0,
    'network_size' => 0, 'direct_referrals' => 0, 'level2_count' => 0, 'level3_count' => 0,
    'mlm_level' => 'Associate', 'team_sales' => 0, 'conversion_rate' => 0, 'monthly_growth' => 0
];

$recent_leads = $recent_leads ?? [];
$recent_commissions = $recent_commissions ?? [];
$activities = $activities ?? [];

$gamify = $gamify ?? [];
?>

<!-- Referral Code Banner -->
<div class="alert alert-info bg-gradient d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4 border-0 rounded-3" style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">
    <div class="d-flex align-items-center gap-3">
        <i class="fas fa-ticket-alt fa-2x text-white opacity-75"></i>
        <div>
            <strong class="text-white d-block">Your Referral Code</strong>
            <span class="text-white-50 small">Share this code to earn rewards when others join</span>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <code id="referralCode" class="px-3 py-2 rounded-2 d-inline-block" style="background: rgba(255,255,255,0.2); color: #fff; font-size: 1.2rem; letter-spacing: 2px; border: 1px dashed rgba(255,255,255,0.4);">
            <?php echo htmlspecialchars($referral_code ?: 'N/A'); ?>
        </code>
        <button class="btn btn-light btn-sm px-3" onclick="copyReferralCode()">
            <i class="fas fa-copy me-1"></i> Copy
        </button>
        <a href="<?php echo BASE_URL; ?>/become-associate" class="btn btn-light btn-sm px-3" target="_blank">
            <i class="fas fa-external-link-alt me-1"></i> Shared Page
        </a>
    </div>
</div>

<!-- Gamification Widget -->
<?php if (!empty($gamify) && !empty($gamify['level'])): ?>
<div class="row g-3 mb-4">
    <div class="col-12">
        <?php include __DIR__ . '/../components/gamification_widget.php'; ?>
    </div>
</div>
<?php endif; ?>

<!-- Quick Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-tag"></i></div>
            <div class="stat-value"><?php echo $stats['mlm_level']; ?></div>
            <div class="stat-label">Your Rank</div>
            <div class="stat-trend up"><i class="fas fa-arrow-up"></i> <?php echo number_format($stats['team_sales']); ?> team sales</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <a href="<?php echo BASE_URL; ?>/associate/leads" class="stat-card-link">
            <div class="stat-card clickable">
                <div class="stat-icon green"><i class="fas fa-users"></i></div>
                <div class="stat-value"><?php echo $stats['total_leads']; ?></div>
                <div class="stat-label">Total Leads</div>
                <div class="stat-trend up"><i class="fas fa-arrow-up"></i> <?php echo $stats['direct_referrals']; ?> direct referrals</div>
                <div class="click-hint"><i class="fas fa-external-link-alt"></i> View All</div>
            </div>
        </a>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon orange"><i class="fas fa-money-bill-wave"></i></div>
            <div class="stat-value">₹<?php echo number_format($stats['total_commission']); ?></div>
            <div class="stat-label">Total Commission</div>
            <div class="stat-trend up"><i class="fas fa-arrow-up"></i> ₹<?php echo number_format($stats['commission_this_month']); ?> this month</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <a href="<?php echo BASE_URL; ?>/associate/genealogy" class="stat-card-link">
            <div class="stat-card clickable">
                <div class="stat-icon purple"><i class="fas fa-sitemap"></i></div>
                <div class="stat-value"><?php echo $stats['network_size']; ?></div>
                <div class="stat-label">Network Size</div>
                <div class="stat-trend up"><i class="fas fa-arrow-up"></i> <?php echo $stats['direct_referrals']; ?> direct, <?php echo $stats['level2_count']; ?> L2, <?php echo $stats['level3_count']; ?> L3</div>
                <div class="click-hint"><i class="fas fa-external-link-alt"></i> View Network</div>
            </div>
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <!-- Performance Overview -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="fas fa-chart-line text-primary me-2"></i>Performance Overview</h5>
                </div>
            </div>
            <div class="card-body aps-cp-card-body">
                <div class="row text-center mb-4">
                    <div class="col-3">
                        <h4 class="text-primary mb-1"><?php echo $stats['direct_referrals']; ?></h4>
                        <small class="text-muted">Direct Referrals</small>
                    </div>
                    <div class="col-3">
                        <h4 class="text-warning mb-1">₹<?php echo number_format($stats['pending_commission']); ?></h4>
                        <small class="text-muted">Pending Commission</small>
                    </div>
                    <div class="col-3">
                        <h4 class="text-success mb-1">₹<?php echo number_format($stats['commission_this_month']); ?></h4>
                        <small class="text-muted">Earned (30 days)</small>
                    </div>
                    <div class="col-3">
                        <h4 class="text-info mb-1"><?php echo $stats['network_size']; ?></h4>
                        <small class="text-muted">Network Size</small>
                    </div>
                </div>
                <div style="height: 200px; background: linear-gradient(90deg, #f8fafc 0%, #e2e8f0 50%, #f8fafc 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <div class="text-center text-muted">
                        <i class="fas fa-chart-area fa-3x mb-3"></i>
                        <p>Performance chart will be displayed here</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Leads -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="fas fa-users text-success me-2"></i>Recent Leads</h5>
                    <a href="<?php echo BASE_URL; ?>/associate/leads" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Name</th>
                                <th>Contact</th>
                                <th>Interest</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_leads as $lead): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($lead['name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($lead['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($lead['type']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $lead['status'] === 'hot' ? 'danger' : ($lead['status'] === 'warm' ? 'warning' : 'secondary'); ?>">
                                            <?php echo ucfirst($lead['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d', strtotime($lead['date'])); ?></td>
                                    <td><a href="#" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Commissions -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="fas fa-money-bill-wave text-warning me-2"></i>Recent Commissions</h5>
                    <a href="<?php echo BASE_URL; ?>/associate/commissions" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_commissions as $commission): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($commission['property'] ?? $commission['commission_type'] ?? 'Commission'); ?></td>
                                    <td><strong>₹<?php echo number_format($commission['amount']); ?></strong></td>
                                    <td>
                                        <span class="badge bg-<?php echo $commission['status'] === 'paid' ? 'success' : 'warning'; ?>">
                                            <?php echo ucfirst($commission['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($commission['date'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Quick Actions -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="card-title mb-0"><i class="fas fa-bolt text-warning me-2"></i>Quick Actions</h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <div class="d-grid gap-2">
                    <a href="<?php echo BASE_URL; ?>/associate/leads/add" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add New Lead
                    </a>
                    <a href="<?php echo BASE_URL; ?>/associate/genealogy" class="btn btn-outline-primary">
                        <i class="fas fa-sitemap me-2"></i>View Network Tree
                    </a>
                    <a href="<?php echo BASE_URL; ?>/become-associate" class="btn btn-outline-info" target="_blank">
                        <i class="fas fa-share-alt me-2"></i>Promotion Page
                    </a>
                    <a href="<?php echo BASE_URL; ?>/associate/wallet/withdraw" class="btn btn-outline-success">
                        <i class="fas fa-wallet me-2"></i>Withdraw Commission
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="card-title mb-0"><i class="fas fa-clock text-info me-2"></i>Recent Activity</h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <div class="activity-list">
                    <?php foreach ($activities as $activity): ?>
                        <div class="d-flex gap-3 mb-3 pb-3 border-bottom">
                            <div class="flex-shrink-0">
                                <div class="bg-<?php echo $activity['color']; ?> bg-opacity-10 text-<?php echo $activity['color']; ?> rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="fas <?php echo $activity['icon']; ?>"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <p class="mb-1"><?php echo $activity['text']; ?></p>
                                <small class="text-muted"><?php echo $activity['time']; ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Network Summary -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="card-title mb-0"><i class="fas fa-network-wired text-purple me-2"></i>Network Summary</h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted"><i class="fas fa-user text-primary me-1"></i>Direct Referrals</span>
                    <strong><?php echo $stats['direct_referrals']; ?></strong>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted"><i class="fas fa-users text-info me-1"></i>Level 2</span>
                    <strong><?php echo $stats['level2_count']; ?></strong>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted"><i class="fas fa-users text-secondary me-1"></i>Level 3</span>
                    <strong><?php echo $stats['level3_count']; ?></strong>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Total Network</span>
                    <strong class="text-primary"><?php echo $stats['network_size']; ?></strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Team Sales</span>
                    <strong class="text-success">₹<?php echo number_format($stats['team_sales']); ?></strong>
                </div>
                <div class="mt-3">
                    <a href="<?php echo BASE_URL; ?>/associate/genealogy" class="btn btn-sm btn-outline-primary w-100">
                        <i class="fas fa-sitemap me-1"></i> View Full Genealogy
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyReferralCode() {
    var code = document.getElementById('referralCode');
    if (code) {
        var text = code.textContent.trim();
        navigator.clipboard.writeText(text).then(function() {
            alert('Referral code copied: ' + text);
        }).catch(function(err) {
            console.error('Copy failed', err);
        });
    }
}
</script>

<style>
    .stat-card {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #e2e8f0;
        height: 100%;
        transition: all 0.3s ease;
    }
    .stat-card.clickable:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        border-color: #3b82f6;
    }
    .stat-card-link {
        text-decoration: none;
        color: inherit;
        display: block;
    }
    .stat-card-link:hover { color: inherit; }
    .click-hint {
        font-size: 0.75rem;
        color: #3b82f6;
        margin-top: 10px;
        opacity: 0;
        transition: opacity 0.3s ease;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .stat-card.clickable:hover .click-hint { opacity: 1; }
    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 15px;
    }
    .stat-icon.blue { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
    .stat-icon.green { background: rgba(16, 185, 129, 0.1); color: #10b981; }
    .stat-icon.orange { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
    .stat-icon.purple { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; }
    .stat-value { font-size: 1.75rem; font-weight: 700; color: #1e293b; margin-bottom: 5px; }
    .stat-label { font-size: 0.875rem; color: #64748b; }
    .stat-trend { font-size: 0.8rem; margin-top: 10px; display: flex; align-items: center; gap: 5px; }
    .stat-trend.up { color: #10b981; }
    .activity-list .border-bottom:last-child { border-bottom: none !important; margin-bottom: 0 !important; padding-bottom: 0 !important; }
</style>
