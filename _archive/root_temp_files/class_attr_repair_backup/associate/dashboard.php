<?php
$associate = $associate ?? [];
$network = $network ?? [];
$commissions = $commissions ?? [];
$stats = $stats ?? [];
$page_title = $page_title ?? __('assoc_dashboard');
$base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');

$totalEarnings = $stats['total_earnings'] ?? 0;
$monthEarnings = $stats['month_earnings'] ?? 0;
$networkSize = $stats['network_size'] ?? 0;
$directReferrals = $stats['direct_referrals'] ?? 0;
$walletBalance = $stats['wallet_balance'] ?? 0;
$pendingCommissions = $stats['pending_commissions'] ?? 0;
$activeLeads = $stats['active_leads'] ?? 0;
$pendingBookings = $stats['pending_bookings'] ?? 0;
$rank = htmlspecialchars($associate['rank'] ?? 'associate');

// Enhanced Rank Colors for modern look
$rankTheme = 'primary';
$rankColors = [
    'associate' => '#94a3b8', 
    'senior_associate' => '#f59e0b', 
    'bdm' => '#3b82f6',
    'sr_bdm' => '#06b6d4', 
    'vice_president' => '#8b5cf6', 
    'president' => '#ef4444', 
    'site_manager' => '#10b981'
];
$rankColorHex = $rankColors[$rank] ?? '#64748b';

if (in_array($rank, ['sr_bdm', 'vice_president', 'president', 'site_manager'])) $rankTheme = 'danger';
elseif (in_array($rank, ['bdm', 'sr_associate'])) $rankTheme = 'info';
?>

<style>
/* Modern Glassmorphism & Micro-animations */
:root {
    --glass-bg: rgba(255, 255, 255, 0.9);
    --glass-border: rgba(255, 255, 255, 0.2);
    --card-shadow: 0 8px 32px rgba(31, 38, 135, 0.07);
    --rank-color: <?= $rankColorHex ?>;
}

body {
    background-color: #f8fafc;
}

.modern-welcome {
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
    border-radius: 20px;
    padding: 35px;
    margin-bottom: 30px;
    position: relative;
    overflow: hidden;
    color: #f8fafc;
    box-shadow: 0 15px 35px rgba(15, 23, 42, 0.2);
}

.modern-welcome::after {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, var(--rank-color) 0%, transparent 70%);
    opacity: 0.15;
    filter: blur(40px);
}

.stat-card-glass {
    background: var(--glass-bg);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid var(--glass-border);
    border-radius: 16px;
    padding: 24px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: var(--card-shadow);
    height: 100%;
    position: relative;
    overflow: hidden;
}

.stat-card-glass::before {
    content: '';
    position: absolute;
    top: 0; left: 0; width: 100%; height: 4px;
    background: var(--icon-bg, #cbd5e1);
    transition: height 0.3s ease;
}

.stat-card-glass:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}

.stat-card-glass:hover::before {
    height: 6px;
}

.stat-icon-wrapper {
    width: 54px;
    height: 54px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    color: #fff;
    margin-bottom: 16px;
    background: var(--icon-bg);
    box-shadow: 0 8px 16px var(--icon-shadow);
}

.stat-value {
    font-size: 1.8rem;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.5px;
    line-height: 1.2;
}

.stat-label {
    font-size: 0.85rem;
    color: #64748b;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 8px;
}

.action-pill {
    padding: 10px 20px;
    border-radius: 30px;
    font-weight: 600;
    font-size: 0.85rem;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
    border: none;
    background: rgba(255,255,255,0.1);
    color: #fff;
    backdrop-filter: blur(5px);
    text-decoration: none;
}

.action-pill:hover {
    background: rgba(255,255,255,0.2);
    color: #fff;
    transform: translateY(-2px);
}

.action-pill.primary-pill {
    background: var(--rank-color);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}
.action-pill.primary-pill:hover {
    filter: brightness(1.1);
}

.rank-badge-modern {
    background: var(--rank-color);
    padding: 8px 16px;
    border-radius: 12px;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    font-size: 0.8rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
}

.list-card {
    background: #fff;
    border-radius: 16px;
    border: none;
    box-shadow: var(--card-shadow);
    padding: 20px;
}

.list-item-row {
    padding: 14px 16px;
    border-radius: 12px;
    transition: background 0.2s;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}
.list-item-row:hover {
    background: #f8fafc;
}
</style>

<!-- Welcome Section -->
<div class="modern-welcome">
    <div class="row align-items-center position-relative" class="style-91080">
        <div class="col-md-7">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="rank-badge-modern">
                    <i class="fas fa-gem me-1"></i> <?= ucfirst(str_replace('_', ' ', $rank)) ?>
                </div>
                <span class="opacity-75 small"><i class="far fa-calendar-alt me-1"></i> <?= date('l, F j, Y') ?></span>
            </div>
            
            <h2 class="fw-bold mb-2" class="style-81858">
                Welcome back, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Associate') ?>!
            </h2>
            <p class="mb-0 text-white-50" class="style-67142">
                Referral Code: <strong class="text-white fs-5 ms-1"><?= htmlspecialchars($_SESSION['referral_code'] ?? 'N/A') ?></strong>
            </p>
        </div>
        <div class="col-md-5 text-md-end mt-4 mt-md-0">
            <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                <a href="<?= BASE_URL ?>/associate/leads" class="action-pill">
                    <i class="fas fa-users"></i> Leads
                </a>
                <a href="<?= BASE_URL ?>/associate/crm" class="action-pill">
                    <i class="fas fa-funnel-dollar"></i> CRM
                </a>
                <a href="<?= BASE_URL ?>/associate/book-plot" class="action-pill primary-pill">
                    <i class="fas fa-file-signature"></i> Book Plot
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Primary Stats -->
<div class="row g-4 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card-glass" class="style-47504">
            <div class="stat-icon-wrapper"><i class="fas fa-wallet"></i></div>
            <div class="stat-value">₹<?= number_format($walletBalance) ?></div>
            <div class="stat-label">Wallet Balance</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card-glass" class="style-30926">
            <div class="stat-icon-wrapper"><i class="fas fa-rupee-sign"></i></div>
            <div class="stat-value">₹<?= number_format($totalEarnings) ?></div>
            <div class="stat-label">Total Earnings</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card-glass" class="style-58217">
            <div class="stat-icon-wrapper"><i class="fas fa-clock"></i></div>
            <div class="stat-value">₹<?= number_format($pendingCommissions) ?></div>
            <div class="stat-label">Pending Commission</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card-glass" class="style-90234">
            <div class="stat-icon-wrapper"><i class="fas fa-sitemap"></i></div>
            <div class="stat-value"><?= number_format($networkSize) ?></div>
            <div class="stat-label">Network Size</div>
        </div>
    </div>
</div>

<!-- Secondary Stats -->
<div class="row g-4 mb-5">
    <div class="col-6 col-lg-3">
        <div class="stat-card-glass" class="style-19252">
            <div class="d-flex align-items-center gap-3">
                <div class="style-44749"><i class="fas fa-user-plus"></i></div>
                <div>
                    <div class="stat-value" class="style-79580"><?= number_format($directReferrals) ?></div>
                    <div class="stat-label" class="style-62191">Direct Referrals</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card-glass" class="style-19252">
            <div class="d-flex align-items-center gap-3">
                <div class="style-71531"><i class="fas fa-calendar-check"></i></div>
                <div>
                    <div class="stat-value" class="style-79580">₹<?= number_format($monthEarnings) ?></div>
                    <div class="stat-label" class="style-62191">This Month</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card-glass" class="style-19252">
            <div class="d-flex align-items-center gap-3">
                <div class="style-54870"><i class="fas fa-address-book"></i></div>
                <div>
                    <div class="stat-value" class="style-79580"><?= number_format($activeLeads) ?></div>
                    <div class="stat-label" class="style-62191">Active Leads</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card-glass" class="style-19252">
            <div class="d-flex align-items-center gap-3">
                <div class="style-5712"><i class="fas fa-file-contract"></i></div>
                <div>
                    <div class="stat-value" class="style-79580"><?= number_format($pendingBookings) ?></div>
                    <div class="stat-label" class="style-62191">Pending Bookings</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FOMO & Rank Progress Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="list-card border-0 shadow-lg position-relative overflow-hidden" class="style-4490">
            <div class="position-absolute top-0 end-0 p-3 opacity-10">
                <i class="fas fa-chart-line fa-8x text-primary"></i>
            </div>
            <div class="d-flex flex-column flex-md-row align-items-md-center position-relative z-1">
                <div class="me-md-4 mb-3 mb-md-0 text-center text-md-start">
                    <div class="bg-warning-subtle text-warning rounded-circle d-inline-flex align-items-center justify-content-center mb-2 shadow-sm" class="style-71716">
                        <i class="fas fa-level-up-alt fa-2x"></i>
                    </div>
                </div>
                <div class="flex-grow-1">
                    <h5 class="fw-bold mb-1 text-dark">Unlock Next Tier Earnings! <span class="badge bg-danger ms-2 shadow-sm pulse-badge"><i class="fas fa-fire me-1"></i> Don't Miss Out!</span></h5>
                    <p class="text-muted mb-2">You are currently a <strong class="text-primary"><?= ucfirst(str_replace('_', ' ', $rank)) ?></strong>. Your team is growing fast! Upgrade to the next rank to unlock <strong>higher commission percentages</strong> and exclusive pool bonuses.</p>
                    <div class="progress mb-2 shadow-sm" class="style-56288">
                        <div class="progress-bar bg-warning progress-bar-striped progress-bar-animated" role="progressbar" class="style-32835"></div>
                    </div>
                    <div class="d-flex justify-content-between text-muted small fw-bold">
                        <span class="text-primary"><i class="fas fa-check-circle me-1"></i> Current: <?= ucfirst(str_replace('_', ' ', $rank)) ?></span>
                        <span class="text-warning text-darken"><i class="fas fa-lock-open me-1"></i> Next Tier close!</span>
                    </div>
                </div>
                <div class="ms-md-4 mt-3 mt-md-0 text-center text-md-end">
                    <a href="<?= BASE_URL ?>/associate/network" class="btn btn-warning rounded-pill fw-bold px-4 shadow text-dark border-0 py-2">
                        <i class="fas fa-bolt me-1"></i> Grow Team Now
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes pulse-animation {
    0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
    70% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
    100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
}
.pulse-badge {
    animation: pulse-animation 2s infinite;
}
</style>

<!-- Details Area -->
<div class="row g-4">
    <div class="col-lg-8">
        <div class="list-card h-100">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold m-0" class="style-85421"><i class="fas fa-network-wired text-primary me-2"></i>Network Overview</h5>
                <a href="<?= BASE_URL ?>/associate/network/tree" class="btn btn-sm btn-light rounded-pill px-3 fw-bold">View Tree</a>
            </div>
            
            <?php if (!empty($network)): ?>
                <?php foreach ($network as $level): ?>
                    <div class="list-item-row border-bottom">
                        <div>
                            <span class="badge bg-primary text-white me-2">Level <?= e($level['level'] ?? 0) ?></span>
                            <span class="fw-bold" class="style-97679"><?= e($level['members'] ?? 0) ?> members</span>
                        </div>
                        <div class="text-end">
                            <div class="small text-muted fw-bold">Level Commission</div>
                            <div class="text-success fw-bold">₹<?= number_format($level['commission'] ?? 0) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-5">
                    <div class="mb-3"><i class="fas fa-seedling fa-3x text-muted opacity-50"></i></div>
                    <h6 class="text-muted fw-bold">No Network Yet</h6>
                    <p class="small text-muted mb-0">Share your referral code to start building your team.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="list-card h-100">
            <h5 class="fw-bold mb-4" class="style-85421"><i class="fas fa-history text-primary me-2"></i>Recent Earnings</h5>
            <?php if (!empty($commissions)): ?>
                <?php foreach (array_slice($commissions, 0, 5) as $c): ?>
                    <div class="list-item-row px-0">
                        <div>
                            <div class="fw-bold" class="style-74741"><?= htmlspecialchars($c['commission_type'] ?? 'Commission') ?></div>
                            <div class="small text-muted"><?= date('M d, Y', strtotime($c['created_at'])) ?></div>
                        </div>
                        <div class="fw-bold text-success">
                            +₹<?= number_format($c['amount']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-5">
                    <div class="mb-3"><i class="fas fa-box-open fa-3x text-muted opacity-50"></i></div>
                    <p class="small text-muted mb-0">No commissions earned yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
