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
$rankColor = 'warning';
if (in_array($rank, ['sr_bdm', 'vice_president', 'president', 'site_manager'])) $rankColor = 'danger';
elseif (in_array($rank, ['bdm', 'sr_associate'])) $rankColor = 'info';
?>

<style>
.assoc-welcome {
    background: linear-gradient(135deg, #0d9488 0%, #0f766e 50%, #115e59 100%);
    color: #fff; border-radius: 16px; padding: 28px; margin-bottom: 24px;
    position: relative; overflow: hidden;
}
.assoc-welcome::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="g" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.08)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23g)"/></svg>');
    opacity: 0.4;
}
.assoc-welcome * { position: relative; z-index: 1; }
.assoc-stat {
    background: #fff; border-radius: 14px; padding: 20px; text-align: center;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06); border: 1px solid #f1f5f9;
    transition: all 0.3s ease; height: 100%;
}
.assoc-stat:hover { transform: translateY(-4px); box-shadow: 0 12px 30px rgba(0,0,0,0.1); }
.assoc-stat-icon {
    width: 48px; height: 48px; border-radius: 12px; display: flex;
    align-items: center; justify-content: center; font-size: 1.2rem; margin: 0 auto 10px; color: #fff;
}
.assoc-stat-num { font-size: 1.6rem; font-weight: 800; color: #1e293b; line-height: 1; }
.assoc-stat-label { font-size: 0.75rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 4px; }

.assoc-card {
    background: #fff; border-radius: 14px; box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    border: 1px solid #f1f5f9; margin-bottom: 20px; overflow: hidden;
}
.assoc-card-header {
    padding: 16px 20px; border-bottom: 1px solid #f1f5f9; display: flex;
    justify-content: space-between; align-items: center; background: #fafbfc;
}
.assoc-card-header h6 { margin: 0; font-weight: 700; color: #1e293b; font-size: 0.95rem; }
.assoc-card-body { padding: 18px 20px; }

.assoc-action-btn {
    display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px;
    border-radius: 10px; font-weight: 600; font-size: 0.82rem; text-decoration: none;
    transition: all 0.3s ease; border: none; cursor: pointer;
}
.assoc-action-btn:hover { transform: translateY(-2px); }
.assoc-primary { background: linear-gradient(135deg, #0d9488, #0f766e); color: #fff; }
.assoc-primary:hover { color: #fff; box-shadow: 0 6px 20px rgba(13,148,136,0.4); }
.assoc-success { background: linear-gradient(135deg, #16a34a, #15803d); color: #fff; }
.assoc-success:hover { color: #fff; box-shadow: 0 6px 20px rgba(22,163,74,0.4); }
.assoc-warning { background: linear-gradient(135deg, #d97706, #b45309); color: #fff; }
.assoc-warning:hover { color: #fff; box-shadow: 0 6px 20px rgba(217,119,6,0.4); }

.earning-item {
    display: flex; align-items: center; justify-content: space-between;
    padding: 12px 0; border-bottom: 1px solid #f1f5f9;
}
.earning-item:last-child { border-bottom: none; }

.network-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: 10px 0; border-bottom: 1px solid #f1f5f9;
}
.network-row:last-child { border-bottom: none; }

@media (max-width: 768px) {
    .assoc-welcome { padding: 20px; }
    .assoc-stat-num { font-size: 1.3rem; }
    .assoc-action-btn { width: 100%; justify-content: center; }
}
</style>

<div class="assoc-welcome">
    <div class="row align-items-center">
        <div class="col-md-7">
            <h4 class="fw-bold mb-2">
                <i class="fas fa-handshake me-2"></i><?= sprintf(__('assoc_welcome_back'), htmlspecialchars($_SESSION['user_name'] ?? 'Associate')); ?>!
            </h4>
            <p class="mb-2 opacity-75"><?= date('l, F j, Y') ?></p>
            <p class="mb-0 small opacity-60">
                Referral Code: <strong><?= htmlspecialchars($_SESSION['referral_code'] ?? 'N/A') ?></strong>
            </p>
        </div>
        <div class="col-md-5 text-md-end mt-3 mt-md-0">
            <div class="mb-2">
                <span class="badge bg-<?= $rankColor ?> fs-6 px-3 py-2">
                    <i class="fas fa-trophy me-1"></i><?= ucfirst($rank) ?>
                </span>
            </div>
            <div class="d-flex flex-column flex-md-row gap-2 justify-content-md-end mt-2">
                <a href="<?= BASE_URL ?>/associate/leads" class="assoc-action-btn assoc-primary">
                    <i class="fas fa-users"></i> My Leads
                </a>
                <a href="<?= BASE_URL ?>/associate/crm" class="assoc-action-btn assoc-success">
                    <i class="fas fa-funnel-dollar"></i> CRM
                </a>
                <a href="<?= BASE_URL ?>/associate/book-plot" class="assoc-action-btn assoc-warning">
                    <i class="fas fa-file-signature"></i> Book Plot
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="assoc-stat">
            <div class="assoc-stat-icon" style="background: linear-gradient(135deg, #16a34a, #10b981);"><i class="fas fa-wallet"></i></div>
            <div class="assoc-stat-num">₹<?= number_format($walletBalance) ?></div>
            <div class="assoc-stat-label">Wallet Balance</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="assoc-stat">
            <div class="assoc-stat-icon" style="background: linear-gradient(135deg, #0d9488, #0f766e);"><i class="fas fa-rupee-sign"></i></div>
            <div class="assoc-stat-num">₹<?= number_format($totalEarnings) ?></div>
            <div class="assoc-stat-label">Total Earnings</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="assoc-stat">
            <div class="assoc-stat-icon" style="background: linear-gradient(135deg, #d97706, #f59e0b);"><i class="fas fa-clock"></i></div>
            <div class="assoc-stat-num">₹<?= number_format($pendingCommissions) ?></div>
            <div class="assoc-stat-label">Pending</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="assoc-stat">
            <div class="assoc-stat-icon" style="background: linear-gradient(135deg, #2563eb, #3b82f6);"><i class="fas fa-users"></i></div>
            <div class="assoc-stat-num"><?= number_format($networkSize) ?></div>
            <div class="assoc-stat-label">Network Size</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="assoc-stat" style="padding:16px;">
            <div class="assoc-stat-icon" style="background:linear-gradient(135deg,#7c3aed,#8b5cf6);width:40px;height:40px;font-size:1rem;"><i class="fas fa-user-plus"></i></div>
            <div class="assoc-stat-num" style="font-size:1.3rem;"><?= number_format($directReferrals) ?></div>
            <div class="assoc-stat-label">Direct Referrals</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="assoc-stat" style="padding:16px;">
            <div class="assoc-stat-icon" style="background:linear-gradient(135deg,#ec4899,#f472b6);width:40px;height:40px;font-size:1rem;"><i class="fas fa-calendar-check"></i></div>
            <div class="assoc-stat-num" style="font-size:1.3rem;">₹<?= number_format($monthEarnings) ?></div>
            <div class="assoc-stat-label">This Month</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="assoc-stat" style="padding:16px;">
            <div class="assoc-stat-icon" style="background:linear-gradient(135deg,#059669,#10b981);width:40px;height:40px;font-size:1rem;"><i class="fas fa-address-book"></i></div>
            <div class="assoc-stat-num" style="font-size:1.3rem;"><?= number_format($activeLeads) ?></div>
            <div class="assoc-stat-label">Active Leads</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="assoc-stat" style="padding:16px;">
            <div class="assoc-stat-icon" style="background:linear-gradient(135deg,#ea580c,#f97316);width:40px;height:40px;font-size:1rem;"><i class="fas fa-file-contract"></i></div>
            <div class="assoc-stat-num" style="font-size:1.3rem;"><?= number_format($pendingBookings) ?></div>
            <div class="assoc-stat-label">Pending Bookings</div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="assoc-card">
            <div class="assoc-card-header">
                <h6><i class="fas fa-sitemap me-2"></i><?= __('assoc_network_overview') ?></h6>
                <a href="<?= BASE_URL ?>/associate/network/tree" class="small text-decoration-none" style="color:#0d9488;">View Tree</a>
            </div>
            <div class="assoc-card-body">
                <?php if (!empty($network)): ?>
                    <?php foreach ($network as $level): ?>
                        <div class="network-row">
                            <div>
                                <span class="fw-bold" style="color:#1e293b;">Level <?= $level['level'] ?></span>
                                <span class="text-muted ms-2" style="font-size:0.8rem;"><?= $level['members'] ?? 0 ?> members</span>
                            </div>
                            <div class="text-end">
                                <span class="fw-bold" style="color:#0d9488;">₹<?= number_format($level['commission'] ?? 0) ?></span>
                                <?php if (($level['active'] ?? 0) > 0): ?>
                                    <span class="ms-2 badge bg-success" style="font-size:0.65rem;"><?= $level['active'] ?> active</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-sitemap fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-2" style="font-size:0.9rem;"><?= __('assoc_no_network_data') ?></p>
                        <a href="<?= BASE_URL ?>/associate/referral" class="assoc-action-btn assoc-primary" style="display:inline-flex;">
                            <i class="fas fa-share-alt"></i> <?= __('assoc_invite_members') ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="assoc-card">
            <div class="assoc-card-header">
                <h6><i class="fas fa-money-bill-wave me-2"></i>Recent Earnings</h6>
                <a href="<?= BASE_URL ?>/associate/commissions" class="small text-decoration-none" style="color:#0d9488;">View All</a>
            </div>
            <div class="assoc-card-body">
                <?php if (!empty($commissions)): ?>
                    <?php foreach (array_slice($commissions, 0, 5) as $commission): ?>
                        <div class="earning-item">
                            <div class="d-flex align-items-center">
                                <div style="width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:0.85rem;color:#fff;background:linear-gradient(135deg,#0d9488,#0f766e);margin-right:12px;">
                                    <i class="fas fa-rupee-sign"></i>
                                </div>
                                <div>
                                    <div class="fw-bold" style="font-size:0.85rem;color:#1e293b;"><?= htmlspecialchars($commission['type'] ?? 'Commission') ?></div>
                                    <small class="text-muted"><?= date('M d, Y', strtotime($commission['date'] ?? 'now')) ?></small>
                                </div>
                            </div>
                            <div class="fw-bold" style="color:#16a34a;">₹<?= number_format($commission['amount'] ?? 0) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-coins fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0" style="font-size:0.9rem;"><?= __('assoc_no_recent_earnings') ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="assoc-card">
            <div class="assoc-card-header">
                <h6><i class="fas fa-bolt me-2"></i><?= __('assoc_quick_actions') ?></h6>
            </div>
            <div class="assoc-card-body">
                <div class="d-grid gap-2">
                    <a href="<?= BASE_URL ?>/associate/crm" class="assoc-action-btn assoc-primary justify-content-center">
                        <i class="fas fa-funnel-dollar"></i> CRM Dashboard
                    </a>
                    <a href="<?= BASE_URL ?>/associate/leads" class="assoc-action-btn assoc-success justify-content-center">
                        <i class="fas fa-users"></i> My Leads
                    </a>
                    <a href="<?= BASE_URL ?>/associate/book-plot" class="assoc-action-btn assoc-warning justify-content-center">
                        <i class="fas fa-file-signature"></i> Book Plot
                    </a>
                    <a href="<?= BASE_URL ?>/associate/site-visits" class="assoc-action-btn justify-content-center" style="background:linear-gradient(135deg,#2563eb,#3b82f6);color:#fff;">
                        <i class="fas fa-map-marker-alt"></i> Site Visits
                    </a>
                    <a href="<?= BASE_URL ?>/associate/wallet/withdraw" class="assoc-action-btn justify-content-center" style="background:linear-gradient(135deg,#7c3aed,#8b5cf6);color:#fff;">
                        <i class="fas fa-money-bill-wave"></i> Withdraw
                    </a>
                    <a href="<?= BASE_URL ?>/associate/tools" class="assoc-action-btn justify-content-center" style="background:linear-gradient(135deg,#ea580c,#f97316);color:#fff;">
                        <i class="fas fa-calculator"></i> Tools & Calculators
                    </a>
                </div>
            </div>
        </div>

        <div class="assoc-card">
            <div class="assoc-card-header">
                <h6><i class="fas fa-share-alt me-2"></i>Refer & Earn</h6>
            </div>
            <div class="assoc-card-body text-center">
                <div style="width:60px;height:60px;border-radius:50%;background:linear-gradient(135deg,#fbbf24,#f59e0b);display:flex;align-items:center;justify-content:center;margin:0 auto 12px;color:#fff;font-size:1.5rem;">
                    <i class="fas fa-gift"></i>
                </div>
                <h6 class="mb-1" style="color:#1e293b;">Invite Friends & Earn</h6>
                <p class="text-muted mb-3" style="font-size:0.8rem;">Earn commission for every successful referral in your network.</p>
                <a href="<?= BASE_URL ?>/associate/referral" class="assoc-action-btn assoc-warning justify-content-center w-100">
                    <i class="fas fa-share-alt"></i> Share Referral Link
                </a>
            </div>
        </div>

        <div class="assoc-card">
            <div class="assoc-card-header">
                <h6><i class="fas fa-download me-2"></i>Export Data</h6>
            </div>
            <div class="assoc-card-body">
                <div class="d-grid gap-2">
                    <a href="<?= BASE_URL ?>/associate/export/my-earnings" class="btn btn-outline-dark btn-sm"><i class="fas fa-file-csv me-2"></i>Earnings Report</a>
                    <a href="<?= BASE_URL ?>/associate/export/active-team" class="btn btn-outline-dark btn-sm"><i class="fas fa-file-csv me-2"></i>Team Report</a>
                    <a href="<?= BASE_URL ?>/associate/export/downline" class="btn btn-outline-dark btn-sm"><i class="fas fa-file-csv me-2"></i>Downline Report</a>
                </div>
            </div>
        </div>
    </div>
</div>
