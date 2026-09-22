<?php
/**
 * Agent Dashboard View - APS Dream Home
 * Supports MLM Company Agents & Freelancer/Independent Agents
 * Renders inside layouts/agent.php
 */
$page_title = $page_title ?? 'Agent Dashboard - APS Dream Home';
$page_description = $page_description ?? 'Manage your real estate business';
$agent_type = $agent_type ?? ($_SESSION['agent_type'] ?? 'freelancer');
$agent_stats = $agent_stats ?? [];
$recent_leads = $recent_leads ?? [];
$assigned_properties = $assigned_properties ?? [];
$my_properties = $my_properties ?? [];
$commission_summary = $commission_summary ?? [];
$network_stats = $network_stats ?? [];
$site_visits = $site_visits ?? [];
$performance = $performance ?? [];
$gamify = $gamify ?? [];
$user_badges = $user_badges ?? [];
$all_badges = $all_badges ?? [];
$user_rank = $user_rank ?? 1;
$user_points = $user_points ?? 0;
$user_level = $user_level ?? 1;

$base = defined('BASE_URL') ? BASE_URL : ('/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/'));
$agent_name = $_SESSION['user_name'] ?? $_SESSION['agent_name'] ?? 'Agent';
?>

<div class="agent-dashboard-content">
    <?php if (!empty($commission_summary['missed_commissions'])): ?>
    <!-- Missed Commissions Alert -->
    <div class="alert alert-danger alert-dismissible fade show mb-4 border-0 d-flex align-items-center rounded-3 shadow-sm" role="alert">
        <div class="rounded-circle bg-danger bg-opacity-10 text-danger p-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
            <i class="fas fa-exclamation-triangle fa-lg"></i>
        </div>
        <div>
            <h6 class="alert-heading fw-bold mb-1">Attention: ₹<?= e($commission_summary['total_missed'] ?? '0.00') ?> in Commissions Pending Activation</h6>
            <p class="mb-0 text-dark small">Your agent tier requires active verification to claim multi-tier network bonuses. 
                <a href="<?= $base ?>/agent/subscription" class="fw-bold text-danger text-decoration-underline ms-1">Activate Now</a>
            </p>
        </div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <!-- Header Action Bar -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h3 class="fw-bold mb-0 text-dark">Agent Cockpit</h3>
                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-1 fw-semibold small">
                    <?= $agent_type === 'freelancer' ? 'Freelancer Agent' : ($agent_type === 'employee_agent' ? 'Employee Agent' : 'Associate Partner') ?>
                </span>
            </div>
            <p class="text-muted small mb-0">Live overview of your pipeline, listings, network volumes, and commissions</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= $base ?>/agent/leads/add" class="btn btn-primary px-3 py-2 rounded-pill fw-medium shadow-sm">
                <i class="fas fa-user-plus me-1"></i> Add Lead
            </a>
            <a href="<?= $base ?>/agent/deals" class="btn btn-outline-primary px-3 py-2 rounded-pill fw-medium">
                <i class="fas fa-handshake me-1"></i> My Deals
            </a>
        </div>
    </div>

    <!-- Main Metric Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-muted small fw-semibold text-uppercase">Total Leads</span>
                        <div class="d-flex align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10 text-primary" style="width: 44px; height: 44px;">
                            <i class="fas fa-bullseye fa-lg"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-1 text-dark"><?= (int)($agent_stats['total_leads'] ?? 0) ?></h3>
                    <small class="text-success fw-medium">
                        <i class="fas fa-check me-1"></i><?= (int)($agent_stats['converted_leads'] ?? 0) ?> converted
                        (<?= htmlspecialchars($agent_stats['conversion_rate'] ?? '0%') ?>)
                    </small>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-muted small fw-semibold text-uppercase"><?= $agent_type === 'freelancer' ? 'My Listings' : 'Properties' ?></span>
                        <div class="d-flex align-items-center justify-content-center rounded-3 bg-info bg-opacity-10 text-info" style="width: 44px; height: 44px;">
                            <i class="fas fa-building fa-lg"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-1 text-dark"><?= (int)($agent_stats['total_properties'] ?? 0) ?></h3>
                    <small class="text-info fw-medium">
                        <i class="fas fa-check-circle me-1"></i><?= (int)($agent_stats['sold_properties'] ?? 0) ?> sold
                    </small>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-muted small fw-semibold text-uppercase">Total Earnings</span>
                        <div class="d-flex align-items-center justify-content-center rounded-3 bg-warning bg-opacity-10 text-warning" style="width: 44px; height: 44px;">
                            <i class="fas fa-rupee-sign fa-lg"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-1 text-dark">₹<?= number_format((float)($commission_summary['total_commission'] ?? ($agent_stats['total_commission'] ?? 0)), 2) ?></h3>
                    <a href="<?= $base ?>/agent/commissions" class="small text-warning text-decoration-none fw-semibold">
                        Earnings Breakdown <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-muted small fw-semibold text-uppercase">
                            <?= ($agent_type === 'freelancer' || $agent_type === 'independent') ? 'This Month Sales' : 'Direct Team' ?>
                        </span>
                        <div class="d-flex align-items-center justify-content-center rounded-3 bg-success bg-opacity-10 text-success" style="width: 44px; height: 44px;">
                            <i class="fas <?= ($agent_type === 'freelancer' || $agent_type === 'independent') ? 'fa-chart-line' : 'fa-users' ?> fa-lg"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-1 text-dark">
                        <?= ($agent_type === 'freelancer' || $agent_type === 'independent') ? (int)($performance['this_month']['count'] ?? 0) : (int)($network_stats['direct_count'] ?? 0) ?>
                    </h3>
                    <small class="text-muted small">
                        <?= ($agent_type === 'freelancer' || $agent_type === 'independent') ? 'Active deals in closing' : 'Direct team associates' ?>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Gamification & Badges Showcase -->
    <?php
    $earnedBadgeIds = !empty($user_badges) ? array_column($user_badges, 'badge_id') : [];
    $earnedBadgeNames = !empty($user_badges) ? array_column($user_badges, 'name') : [];
    $displayBadges = !empty($all_badges) ? $all_badges : [];
    $points = $user_points ?? (int)($gamify['stats']['total_points'] ?? 0);
    $level = $user_level ?? (int)($gamify['stats']['current_level'] ?? 1);
    $nextLevelPoints = ($level >= 10) ? 10000 : max(100, $level * 300);
    $progressPct = min(100, round(($points / max(1, $nextLevelPoints)) * 100));
    $rankNumber = $user_rank ?? (!empty($gamify['rank']) && $gamify['rank'] > 0 ? $gamify['rank'] : 1);
    ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 14px; background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 pb-3 border-bottom gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 52px; height: 52px; background: linear-gradient(135deg, #f59e0b, #d97706); color: white; font-size: 22px;">
                                <i class="fas fa-medal"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                                    Achievements & Milestones
                                    <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1 small">Level <?= $level ?></span>
                                </h5>
                                <small class="text-muted">Earned <?= count($earnedBadgeIds) ?> badges · <?= number_format($points) ?> XP Points</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill fw-bold">
                                <i class="fas fa-trophy text-warning me-1"></i> Rank #<?= $rankNumber ?> on Leaderboard
                            </span>
                        </div>
                    </div>

                    <!-- Level Progress Bar -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between text-muted small fw-bold mb-1">
                            <span><i class="fas fa-flag text-primary me-1"></i> Level <?= $level ?> Progress</span>
                            <span class="text-dark"><?= number_format($points) ?> / <?= number_format($nextLevelPoints) ?> XP (<?= $progressPct ?>%)</span>
                        </div>
                        <div class="progress" style="height: 10px; border-radius: 6px; background-color: #e2e8f0;">
                            <div class="progress-bar" role="progressbar" style="width: <?= $progressPct ?>%; background: linear-gradient(90deg, #3b82f6, #f59e0b);" aria-valuenow="<?= $progressPct ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>

                    <!-- Badges Grid -->
                    <?php if (!empty($displayBadges)): ?>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach (array_slice($displayBadges, 0, 8) as $b): 
                            $isEarned = in_array($b['id'] ?? 0, $earnedBadgeIds) || in_array($b['name'] ?? '', $earnedBadgeNames);
                            $icon = !empty($b['icon']) ? $b['icon'] : 'award';
                        ?>
                        <div class="d-flex align-items-center gap-2 px-3 py-2 rounded-3 border <?= $isEarned ? 'border-warning border-opacity-50 bg-warning bg-opacity-10 shadow-sm' : 'border-light bg-light opacity-75' ?>" style="transition: all 0.2s ease;">
                            <i class="fas fa-<?= htmlspecialchars($icon) ?> <?= $isEarned ? 'text-warning' : 'text-secondary' ?> fs-5"></i>
                            <div>
                                <div class="fw-bold small text-dark"><?= htmlspecialchars($b['display_name'] ?? $b['name']) ?></div>
                                <small class="text-muted" style="font-size: 11px;"><?= $isEarned ? '✓ Unlocked' : htmlspecialchars($b['points_required'] ?? 0) . ' XP' ?></small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Agent Sub-Role Specific Cockpit Section -->
    <?php if ($agent_type === 'mlm_company'): ?>
    <!-- ═══ MLM COMPANY AGENT: Network Overview & Multi-Tier Commission ═══ -->
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-sitemap text-success me-2"></i>Network Overview</h5>
                    <a href="<?= $base ?>/agent/network" class="btn btn-sm btn-outline-success rounded-pill px-3">Network Tree</a>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 text-center">
                        <div class="col-4">
                            <div class="p-3 rounded-3 bg-success bg-opacity-10 border border-success border-opacity-25">
                                <h4 class="fw-bold text-success mb-1"><?= (int)($network_stats['direct_count'] ?? 0) ?></h4>
                                <div class="text-muted small">Direct Referrals</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 rounded-3 bg-primary bg-opacity-10 border border-primary border-opacity-25">
                                <h4 class="fw-bold text-primary mb-1"><?= (int)($network_stats['team_size'] ?? 0) ?></h4>
                                <div class="text-muted small">Total Team Size</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 rounded-3 bg-info bg-opacity-10 border border-info border-opacity-25">
                                <h4 class="fw-bold text-info mb-1">₹<?= number_format((float)($network_stats['team_gv'] ?? 0), 2) ?></h4>
                                <div class="text-muted small">Team Group Volume</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                    <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-chart-pie text-warning me-2"></i>Commission Split</h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2 p-2 rounded-2 bg-light">
                        <span class="text-muted small">Direct Sales</span>
                        <span class="fw-bold text-success">₹<?= number_format((float)($commission_summary['total_direct'] ?? 0), 2) ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3 p-2 rounded-2 bg-light">
                        <span class="text-muted small">Network Override</span>
                        <span class="fw-bold text-info">₹<?= number_format((float)($commission_summary['total_network'] ?? 0), 2) ?></span>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between align-items-center pt-1">
                        <span class="fw-bold text-dark">Total Net Earned</span>
                        <span class="fw-bold text-success fs-5">₹<?= number_format((float)($commission_summary['total_commission'] ?? 0), 2) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <!-- ═══ FREELANCER / INDEPENDENT: Performance Metrics, Brokerage & Site Visits ═══ -->
    <div class="row g-3 mb-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-chart-line text-primary me-2"></i>Sales Performance Metrics</h5>
                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1 rounded-pill">Volume Tracker</span>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 text-center">
                        <div class="col-4">
                            <div class="p-3 rounded-3 bg-primary bg-opacity-10 border border-primary border-opacity-25">
                                <h4 class="fw-bold text-primary mb-1"><?= (int)($performance['this_month']['count'] ?? 0) ?></h4>
                                <div class="text-muted small mb-1">This Month Sales</div>
                                <div class="fw-semibold text-success small">₹<?= number_format((float)($performance['this_month']['volume'] ?? 0), 2) ?></div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 rounded-3 bg-info bg-opacity-10 border border-info border-opacity-25">
                                <h4 class="fw-bold text-info mb-1"><?= (int)($performance['last_month']['count'] ?? 0) ?></h4>
                                <div class="text-muted small mb-1">Last Month Sales</div>
                                <div class="fw-semibold text-info small">₹<?= number_format((float)($performance['last_month']['volume'] ?? 0), 2) ?></div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 rounded-3 bg-warning bg-opacity-10 border border-warning border-opacity-25">
                                <h4 class="fw-bold text-warning mb-1"><?= (int)($performance['career']['count'] ?? 0) ?></h4>
                                <div class="text-muted small mb-1">Career Deals</div>
                                <div class="fw-semibold text-warning small">₹<?= number_format((float)($performance['career']['volume'] ?? 0), 2) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-calendar-check text-success me-2"></i>Upcoming Site Visits</h5>
                    <a href="<?= $base ?>/agent/site-visits" class="btn btn-sm btn-outline-success rounded-pill px-3">Schedule</a>
                </div>
                <div class="card-body p-4">
                    <?php if (empty($site_visits)): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-calendar-alt fa-2x mb-2 d-block opacity-50"></i>
                            <div class="small">No upcoming client site visits scheduled</div>
                        </div>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-2">
                            <?php foreach (array_slice($site_visits, 0, 3) as $visit): ?>
                                <div class="d-flex justify-content-between align-items-center p-2 rounded-2 bg-light border">
                                    <div>
                                        <div class="small fw-bold text-dark"><?= htmlspecialchars($visit['visitor_name'] ?? $visit['lead_name'] ?? 'Client') ?></div>
                                        <div class="small text-muted" style="font-size: 0.75rem;">
                                            <i class="fas fa-clock me-1"></i><?= !empty($visit['visit_time']) ? date('H:i', strtotime($visit['visit_time'])) : '' ?> | 
                                            <?= !empty($visit['visit_date']) ? date('d M Y', strtotime($visit['visit_date'])) : '' ?>
                                        </div>
                                    </div>
                                    <?php if (!empty($visit['colony_name'])): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1 small" style="font-size: 0.7rem;">
                                            <?= htmlspecialchars($visit['colony_name']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- 2-Column Main Content: Recent Leads & Properties -->
    <div class="row g-4 mb-4">
        <!-- Recent Leads List -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-1 text-dark"><i class="fas fa-bullseye text-primary me-2"></i>Recent Leads Pipeline</h5>
                        <p class="text-muted small mb-0">Active inquiries assigned to your portfolio</p>
                    </div>
                    <a href="<?= $base ?>/agent/leads" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                        View All (<?= count($recent_leads) ?>)
                    </a>
                </div>
                <div class="card-body p-4">
                    <?php if (empty($recent_leads)): ?>
                        <div class="text-center py-5 text-muted">
                            <div class="rounded-circle bg-light d-inline-flex p-3 mb-2 text-muted">
                                <i class="fas fa-user-plus fa-2x"></i>
                            </div>
                            <h6 class="fw-semibold">No Leads Assigned Yet</h6>
                            <p class="small mb-3">Add prospective buyers to begin tracking inquiries.</p>
                            <a href="<?= $base ?>/agent/leads/add" class="btn btn-sm btn-primary rounded-pill px-3">Add New Lead</a>
                        </div>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-2">
                            <?php foreach (array_slice($recent_leads, 0, 5) as $lead): ?>
                                <div class="d-flex justify-content-between align-items-center p-3 rounded-3 border bg-light bg-opacity-50">
                                    <div>
                                        <div class="fw-bold text-dark mb-1"><?= htmlspecialchars($lead['name'] ?? 'Unknown Lead') ?></div>
                                        <div class="small text-muted">
                                            <i class="fas fa-phone me-1"></i><?= htmlspecialchars($lead['phone'] ?? 'N/A') ?>
                                            <?php if (!empty($lead['colony_name'])): ?>
                                                <span class="mx-1">•</span> <i class="fas fa-map-marker-alt me-1 text-danger"></i><?= htmlspecialchars($lead['colony_name']) ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1 rounded-pill small mb-1">
                                            <?= htmlspecialchars(ucfirst($lead['status'] ?? 'New')) ?>
                                        </span>
                                        <div class="small text-muted" style="font-size: 0.75rem;">
                                            <?= !empty($lead['created_at']) ? date('d M Y', strtotime($lead['created_at'])) : '' ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Properties & Listings -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-1 text-dark"><i class="fas fa-building text-info me-2"></i><?= $agent_type === 'freelancer' ? 'My Listings' : 'Assigned Plots' ?></h5>
                        <p class="text-muted small mb-0">Active inventory in your scope</p>
                    </div>
                    <a href="<?= $base ?>/agent/properties" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                        View All
                    </a>
                </div>
                <div class="card-body p-4">
                    <?php 
                    $props = $agent_type === 'freelancer' ? ($my_properties ?? []) : ($assigned_properties ?? []);
                    if (empty($props)): 
                    ?>
                        <div class="text-center py-5 text-muted">
                            <div class="rounded-circle bg-light d-inline-flex p-3 mb-2 text-muted">
                                <i class="fas fa-home fa-2x"></i>
                            </div>
                            <h6 class="fw-semibold">No Properties Available</h6>
                            <p class="small mb-0">Check back once listings are assigned to your profile.</p>
                        </div>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-2">
                            <?php foreach (array_slice($props, 0, 4) as $prop): ?>
                                <div class="d-flex justify-content-between align-items-center p-3 rounded-3 border bg-light bg-opacity-50">
                                    <div class="d-flex align-items-center gap-3 overflow-hidden">
                                        <div class="rounded-3 bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; flex-shrink: 0;">
                                            <i class="fas fa-map-marked text-muted"></i>
                                        </div>
                                        <div class="overflow-hidden">
                                            <div class="fw-bold text-dark text-truncate mb-1"><?= htmlspecialchars($prop['title'] ?? 'Colony Plot') ?></div>
                                            <div class="small text-muted text-truncate"><?= htmlspecialchars($prop['colony_name'] ?? $prop['location'] ?? 'Ayodhya Road') ?></div>
                                        </div>
                                    </div>
                                    <div class="text-end text-nowrap ms-2">
                                        <div class="fw-bold text-primary small">₹<?= number_format((float)($prop['price'] ?? 0)) ?></div>
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-0 small" style="font-size: 0.7rem;">
                                            <?= htmlspecialchars(ucfirst($prop['status'] ?? 'Available')) ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>