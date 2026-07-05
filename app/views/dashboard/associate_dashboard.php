<?php

/**
 * Associate Dashboard View
 */

$page_title = $page_title ?? __('assoc_dashboard', [], 'Associate Dashboard');
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
$rank_progress = $rank_progress ?? [];
$wallet_balance = $wallet_balance ?? 0;
$recent_bookings = $recent_bookings ?? [];
$emi_summary = $emi_summary ?? ['total_emi' => 0, 'paid_emi' => 0, 'pending_emi' => 0, 'overdue_emi' => 0];
$property_views = $property_views ?? 0;
$total_inquiries = $total_inquiries ?? 0;

// Format raw DB rank names (e.g. "sr_bdm" → "Sr. BDM", "vice_president" → "Vice President")
$formatRank = function (?string $rank): string {
    if (!$rank) return '';
    $map = [
        'associate' => 'Associate',
        'senior_associate' => 'Senior Associate',
        'bdm' => 'BDM',
        'sr_bdm' => 'Sr. BDM',
        'vice_president' => 'Vice President',
        'president' => 'President',
        'site_manager' => 'Site Manager',
    ];
    $lower = strtolower(trim($rank));
    return $map[$lower] ?? ucwords(str_replace('_', ' ', $lower));
};
?>

<!-- Referral Code Banner -->
<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4 border-0 rounded-3 p-3" style="background: linear-gradient(135deg, #6366f1, #14b8a6);">
    <div class="d-flex align-items-center gap-3">
        <i class="fas fa-ticket-alt fa-2x text-white opacity-75"></i>
        <div>
            <strong class="text-white d-block"><?php echo __('assoc_dash_referral_code', [], 'Your Referral Code'); ?></strong>
            <span class="small" style="color: rgba(255,255,255,0.7);"><?php echo __('assoc_dash_share_rewards', [], 'Share this code to earn rewards when others join'); ?></span>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <code id="referralCode" class="px-3 py-2 rounded-2 d-inline-block" style="background: rgba(255,255,255,0.2); color: #fff; font-size: 1.2rem; letter-spacing: 2px; border: 1px dashed rgba(255,255,255,0.4);">
            <?php echo htmlspecialchars($referral_code ?: 'N/A'); ?>
        </code>
        <button class="btn btn-light btn-sm px-3" onclick="copyReferralCode()">
            <i class="fas fa-copy me-1"></i> <?php echo __('assoc_dash_copy', [], 'Copy'); ?>
        </button>
        <a href="<?php echo BASE_URL; ?>/become-associate" class="btn btn-light btn-sm px-3" target="_blank">
            <i class="fas fa-external-link-alt me-1"></i> <?php echo __('assoc_dash_share', [], 'Share'); ?>
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

<!-- Rank Progress Widget -->
<?php if (!empty($rank_progress) && !empty($rank_progress['next_rank'])): ?>
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); color: #fff;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="mb-1 text-white"><i class="fas fa-trophy me-2" style="color: #fbbf24;"></i><?php echo __('assoc_dash_rank_progress', [], 'Rank Progress'); ?></h5>
                        <p class="mb-0 text-white-50 small"><?php echo __('assoc_dash_rank_journey', [], 'Your journey to the next rank'); ?></p>
                    </div>
                    <div class="text-end">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge px-3 py-2" style="background: rgba(255,255,255,0.15); font-size: 0.85rem;">
                                <i class="fas fa-medal me-1"></i><?php echo htmlspecialchars($formatRank($rank_progress['current_rank'])); ?>
                            </span>
                            <i class="fas fa-arrow-right text-white-50"></i>
                            <span class="badge px-3 py-2" style="background: #fbbf24; color: #1e293b; font-size: 0.85rem;">
                                <i class="fas fa-crown me-1"></i><?php echo htmlspecialchars($formatRank($rank_progress['next_rank'])); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="text-white-50"><?php echo __('assoc_dash_overall_progress', [], 'Overall Progress'); ?></small>
                        <small class="text-white fw-bold"><?php echo $rank_progress['progress_pct']; ?>%</small>
                    </div>
                    <div class="progress" style="height: 12px; background: rgba(255,255,255,0.15); border-radius: 6px;">
                        <div class="progress-bar" role="progressbar" style="width: <?php echo $rank_progress['progress_pct']; ?>%; background: linear-gradient(90deg, #fbbf24, #f59e0b); border-radius: 6px;"></div>
                    </div>
                </div>

                <!-- Requirements -->
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background: rgba(255,255,255,0.08);">
                            <div class="flex-shrink-0">
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; background: rgba(59,130,246,0.2);">
                                    <i class="fas fa-rupee-sign text-info"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="text-white-50 small"><?php echo __('assoc_dash_group_bv', [], 'Group Business Volume'); ?></div>
                                <div class="fw-bold text-white">₹<?php echo number_format($rank_progress['current_gbv']); ?></div>
                                <div class="text-white-50 small"><?php echo __('assoc_dash_required', [], 'Required'); ?>: ₹<?php echo number_format($rank_progress['next_rank_gbv']); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background: rgba(255,255,255,0.08);">
                            <div class="flex-shrink-0">
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; background: rgba(16,185,129,0.2);">
                                    <i class="fas fa-users text-success"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="text-white-50 small"><?php echo __('assoc_dash_direct_legs', [], 'Direct Legs'); ?></div>
                                <div class="fw-bold text-white"><?php echo $rank_progress['current_legs']; ?></div>
                                <div class="text-white-50 small"><?php echo __('assoc_dash_required', [], 'Required'); ?>: <?php echo $rank_progress['next_rank_legs']; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php elseif (!empty($rank_progress) && empty($rank_progress['next_rank'])): ?>
<!-- Already at highest rank -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); color: #1e293b;">
            <div class="card-body p-4 text-center">
                <i class="fas fa-crown fa-3x mb-3" style="color: #92400e;"></i>
                <h4 class="mb-1"><?php echo __('assoc_dash_highest_rank', [], "You've Reached the Highest Rank!"); ?></h4>
                <p class="mb-0"><?php echo __('assoc_dash_congrats', [], 'Congratulations! You are at'); ?> <strong><?php echo htmlspecialchars($formatRank($rank_progress['current_rank'])); ?></strong> &mdash; <?php echo __('assoc_dash_highest_rank_top', [], 'the top of the pyramid.'); ?></p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Quick Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-tag"></i></div>
            <div class="stat-value"><?php echo htmlspecialchars($formatRank($stats['mlm_level'])); ?></div>
            <div class="stat-label"><?php echo __('assoc_dash_your_rank', [], 'Your Rank'); ?></div>
            <div class="stat-trend up"><i class="fas fa-arrow-up"></i> ₹<?php echo number_format($stats['team_sales']); ?> <?php echo __('assoc_dash_team_sales', [], 'team sales'); ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <a href="<?php echo BASE_URL; ?>/associate/leads" class="stat-card-link">
            <div class="stat-card clickable">
                <div class="stat-icon green"><i class="fas fa-users"></i></div>
                <div class="stat-value"><?php echo $stats['total_leads']; ?></div>
                <div class="stat-label"><?php echo __('assoc_dash_total_leads', [], 'Total Leads'); ?></div>
                <div class="stat-trend up"><i class="fas fa-arrow-up"></i> <?php echo $stats['direct_referrals']; ?> <?php echo __('assoc_dash_direct_refs_small', [], 'direct referrals'); ?></div>
                <div class="click-hint"><i class="fas fa-external-link-alt"></i> <?php echo __('assoc_dash_view_all', [], 'View All'); ?></div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="<?php echo BASE_URL; ?>/associate/commissions" class="stat-card-link">
            <div class="stat-card clickable">
                <div class="stat-icon orange"><i class="fas fa-money-bill-wave"></i></div>
                <div class="stat-value">₹<?php echo number_format($stats['total_commission']); ?></div>
                <div class="stat-label"><?php echo __('assoc_dash_total_commission', [], 'Total Commission'); ?></div>
                <div class="stat-trend up"><i class="fas fa-arrow-up"></i> ₹<?php echo number_format($stats['commission_this_month']); ?> <?php echo __('assoc_dash_this_month', [], 'this month'); ?></div>
                <div class="click-hint"><i class="fas fa-external-link-alt"></i> <?php echo __('assoc_dash_view_all', [], 'View All'); ?></div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="<?php echo BASE_URL; ?>/associate/genealogy" class="stat-card-link">
            <div class="stat-card clickable">
                <div class="stat-icon purple"><i class="fas fa-sitemap"></i></div>
                <div class="stat-value"><?php echo $stats['network_size']; ?></div>
                <div class="stat-label"><?php echo __('assoc_network_size', [], 'Network Size'); ?></div>
                <div class="stat-trend up"><i class="fas fa-arrow-up"></i> <?php echo $stats['direct_referrals']; ?> <?php echo __('assoc_dash_direct', [], 'direct'); ?>, <?php echo $stats['level2_count']; ?> L2, <?php echo $stats['level3_count']; ?> L3</div>
                <div class="click-hint"><i class="fas fa-external-link-alt"></i> <?php echo __('assoc_dash_view_network', [], 'View Network'); ?></div>
            </div>
        </a>
    </div>
</div>

<!-- Wallet + Property Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <a href="<?php echo BASE_URL; ?>/associate/wallet" class="stat-card-link">
            <div class="stat-card clickable">
                <div class="stat-icon" style="background: rgba(16,185,129,0.1); color: #10b981;"><i class="fas fa-wallet"></i></div>
                <div class="stat-value">₹<?php echo number_format($wallet_balance ?? 0); ?></div>
                <div class="stat-label"><?php echo __('assoc_dash_wallet_balance', [], 'Wallet Balance'); ?></div>
                <div class="click-hint"><i class="fas fa-external-link-alt"></i> <?php echo __('assoc_dash_view_wallet', [], 'View Wallet'); ?></div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="<?php echo BASE_URL; ?>/associate/properties" class="stat-card-link">
            <div class="stat-card clickable">
                <div class="stat-icon" style="background: rgba(59,130,246,0.1); color: #3b82f6;"><i class="fas fa-eye"></i></div>
                <div class="stat-value"><?php echo number_format($property_views ?? 0); ?></div>
                <div class="stat-label"><?php echo __('assoc_dash_property_views', [], 'Property Views'); ?></div>
                <div class="stat-trend"><i class="fas fa-info-circle"></i> <?php echo number_format($total_inquiries ?? 0); ?> <?php echo __('assoc_dash_inquiries', [], 'inquiries'); ?></div>
                <div class="click-hint"><i class="fas fa-external-link-alt"></i> <?php echo __('assoc_dash_view_properties', [], 'View Properties'); ?></div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(245,158,11,0.1); color: #f59e0b;"><i class="fas fa-file-invoice-dollar"></i></div>
            <div class="stat-value"><?php echo number_format($emi_summary['paid_emi'] ?? 0); ?>/<?php echo number_format($emi_summary['total_emi'] ?? 0); ?></div>
            <div class="stat-label"><?php echo __('assoc_dash_emi_paid_total', [], 'EMI Paid/Total'); ?></div>
            <?php if (($emi_summary['overdue_emi'] ?? 0) > 0): ?>
                <div class="stat-trend" style="color: #ef4444;"><i class="fas fa-exclamation-triangle"></i> <?php echo $emi_summary['overdue_emi']; ?> <?php echo __('assoc_dash_overdue', [], 'overdue'); ?></div>
            <?php elseif (($emi_summary['pending_emi'] ?? 0) > 0): ?>
                <div class="stat-trend" style="color: #f59e0b;"><i class="fas fa-clock"></i> <?php echo $emi_summary['pending_emi']; ?> <?php echo __('assoc_dash_pending', [], 'pending'); ?></div>
            <?php else: ?>
                <div class="stat-trend up"><i class="fas fa-check-circle"></i> <?php echo __('assoc_dash_all_clear', [], 'All clear'); ?></div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(20,184,166,0.1); color: #14b8a6;"><i class="fas fa-hand-holding-usd"></i></div>
            <div class="stat-value">₹<?php echo number_format($stats['pending_commission'] ?? 0); ?></div>
            <div class="stat-label"><?php echo __('assoc_dash_pending_commission', [], 'Pending Commission'); ?></div>
            <div class="stat-trend"><i class="fas fa-info-circle"></i> <?php echo __('assoc_dash_awaiting_approval', [], 'Awaiting approval'); ?></div>
        </div>
    </div>
</div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <!-- Performance Overview -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="fas fa-chart-line text-primary me-2"></i><?php echo __('assoc_dash_perf_overview', [], 'Performance Overview'); ?></h5>
                </div>
            </div>
            <div class="card-body aps-cp-card-body">
                <div class="row text-center mb-4">
                    <div class="col-3">
                        <h4 class="text-primary mb-1"><?php echo $stats['direct_referrals']; ?></h4>
                        <small class="text-muted"><?php echo __('assoc_direct_referrals', [], 'Direct Referrals'); ?></small>
                    </div>
                    <div class="col-3">
                        <h4 class="text-warning mb-1">₹<?php echo number_format($stats['pending_commission']); ?></h4>
                        <small class="text-muted"><?php echo __('assoc_dash_pending_commission', [], 'Pending Commission'); ?></small>
                    </div>
                    <div class="col-3">
                        <h4 class="text-success mb-1">₹<?php echo number_format($stats['commission_this_month']); ?></h4>
                        <small class="text-muted"><?php echo __('assoc_dash_earned_30d', [], 'Earned (30 days)'); ?></small>
                    </div>
                    <div class="col-3">
                        <h4 class="text-info mb-1"><?php echo $stats['network_size']; ?></h4>
                        <small class="text-muted"><?php echo __('assoc_network_size', [], 'Network Size'); ?></small>
                    </div>
                </div>
                <div style="height: 200px; background: linear-gradient(90deg, #f8fafc 0%, #e2e8f0 50%, #f8fafc 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <div class="text-center text-muted">
                        <i class="fas fa-chart-area fa-3x mb-3"></i>
                        <p><?php echo __('assoc_dash_chart_placeholder', [], 'Performance chart will be displayed here'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bookings by Referrals -->
        <?php if (!empty($recent_bookings)): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="fas fa-calendar-check text-success me-2"></i><?php echo __('assoc_dash_recent_bookings', [], 'Recent Bookings (Your Referrals)'); ?></h5>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr><th><?php echo __('assoc_dash_customer', [], 'Customer'); ?></th><th><?php echo __('assoc_dash_plot', [], 'Plot'); ?></th><th><?php echo __('assoc_dash_amount', [], 'Amount'); ?></th><th><?php echo __('assoc_dash_status', [], 'Status'); ?></th><th><?php echo __('assoc_dash_date', [], 'Date'); ?></th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_bookings as $b): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($b['customer_name'] ?? ''); ?></strong></td>
                                <td><?php echo htmlspecialchars(($b['plot_name'] ?? '') . ' - ' . ($b['colony_name'] ?? '')); ?></td>
                                <td><strong>₹<?php echo number_format($b['total_plot_value'] ?? 0); ?></strong></td>
                                <td><span class="badge bg-<?php echo ($b['booking_status'] ?? '') === 'confirmed' ? 'success' : (($b['booking_status'] ?? '') === 'cancelled' ? 'danger' : 'warning'); ?>"><?php echo ucfirst($b['booking_status'] ?? 'pending'); ?></span></td>
                                <td><?php echo date('M d, Y', strtotime($b['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Recent Leads -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="fas fa-users text-success me-2"></i><?php echo __('assoc_dash_recent_leads', [], 'Recent Leads'); ?></h5>
                    <a href="<?php echo BASE_URL; ?>/associate/leads" class="btn btn-sm btn-outline-primary"><?php echo __('assoc_dash_view_all', [], 'View All'); ?></a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th><?php echo __('assoc_dash_name', [], 'Name'); ?></th>
                                <th><?php echo __('assoc_dash_contact', [], 'Contact'); ?></th>
                                <th><?php echo __('assoc_dash_interest', [], 'Interest'); ?></th>
                                <th><?php echo __('assoc_dash_status', [], 'Status'); ?></th>
                                <th><?php echo __('assoc_dash_date', [], 'Date'); ?></th>
                                <th><?php echo __('assoc_dash_action', [], 'Action'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_leads as $lead): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($lead['name'] ?? ''); ?></strong></td>
                                    <td><?php echo htmlspecialchars($lead['phone'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($lead['type'] ?? ''); ?></td>
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
                    <h5 class="card-title mb-0"><i class="fas fa-money-bill-wave text-warning me-2"></i><?php echo __('assoc_dash_recent_commissions', [], 'Recent Commissions'); ?></h5>
                    <a href="<?php echo BASE_URL; ?>/associate/commissions" class="btn btn-sm btn-outline-primary"><?php echo __('assoc_dash_view_all', [], 'View All'); ?></a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th><?php echo __('assoc_dash_type', [], 'Type'); ?></th>
                                <th><?php echo __('assoc_dash_amount', [], 'Amount'); ?></th>
                                <th><?php echo __('assoc_dash_status', [], 'Status'); ?></th>
                                <th><?php echo __('assoc_dash_date', [], 'Date'); ?></th>
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
                <h5 class="card-title mb-0"><i class="fas fa-bolt text-warning me-2"></i><?php echo __('assoc_quick_actions', [], 'Quick Actions'); ?></h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <div class="d-grid gap-2">
                    <a href="<?php echo BASE_URL; ?>/associate/leads/add" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i><?php echo __('assoc_dash_add_lead', [], 'Add New Lead'); ?>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/associate/genealogy" class="btn btn-outline-primary">
                        <i class="fas fa-sitemap me-2"></i><?php echo __('assoc_dash_view_network_tree', [], 'View Network Tree'); ?>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/become-associate" class="btn btn-outline-info" target="_blank">
                        <i class="fas fa-share-alt me-2"></i><?php echo __('assoc_dash_promotion_page', [], 'Promotion Page'); ?>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/associate/wallet/withdraw" class="btn btn-outline-success">
                        <i class="fas fa-wallet me-2"></i><?php echo __('assoc_dash_withdraw_commission', [], 'Withdraw Commission'); ?>
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="card-title mb-0"><i class="fas fa-clock text-info me-2"></i><?php echo __('assoc_dash_recent_activity', [], 'Recent Activity'); ?></h5>
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
                <h5 class="card-title mb-0"><i class="fas fa-network-wired text-purple me-2"></i><?php echo __('assoc_dash_network_summary', [], 'Network Summary'); ?></h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted"><i class="fas fa-user text-primary me-1"></i><?php echo __('assoc_direct_referrals', [], 'Direct Referrals'); ?></span>
                    <strong><?php echo $stats['direct_referrals']; ?></strong>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted"><i class="fas fa-users text-info me-1"></i><?php echo __('assoc_dash_level_2', [], 'Level 2'); ?></span>
                    <strong><?php echo $stats['level2_count']; ?></strong>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted"><i class="fas fa-users text-secondary me-1"></i><?php echo __('assoc_dash_level_3', [], 'Level 3'); ?></span>
                    <strong><?php echo $stats['level3_count']; ?></strong>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted"><?php echo __('assoc_dash_total_network', [], 'Total Network'); ?></span>
                    <strong class="text-primary"><?php echo $stats['network_size']; ?></strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted"><?php echo __('assoc_dash_team_sales_title', [], 'Team Sales'); ?></span>
                    <strong class="text-success">₹<?php echo number_format($stats['team_sales']); ?></strong>
                </div>
                <div class="mt-3">
                    <a href="<?php echo BASE_URL; ?>/associate/genealogy" class="btn btn-sm btn-outline-primary w-100">
                        <i class="fas fa-sitemap me-1"></i> <?php echo __('assoc_dash_view_genealogy', [], 'View Full Genealogy'); ?>
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
            alert('<?php echo __('assoc_dash_code_copied', [], 'Referral code copied:'); ?> ' + text);
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
    .stat-icon.purple { background: rgba(139, 92, 246, 0.1); color: #14b8a6; }
    .stat-value { font-size: 1.75rem; font-weight: 700; color: #1e293b; margin-bottom: 5px; }
    .stat-label { font-size: 0.875rem; color: #64748b; }
    .stat-trend { font-size: 0.8rem; margin-top: 10px; display: flex; align-items: center; gap: 5px; }
    .stat-trend.up { color: #10b981; }
    .activity-list .border-bottom:last-child { border-bottom: none !important; margin-bottom: 0 !important; padding-bottom: 0 !important; }
</style>
