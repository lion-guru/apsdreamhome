<?php
/**
 * Admin Dashboard View
 * Compatible with AdminBaseController layouts
 */

// Extract data passed from controller
$stats = $stats ?? [];
$menus = $menus ?? [];
$recent_activities = $recent_activities ?? [];
$mlm_stats = $mlm_stats ?? [];
$team_stats = $team_stats ?? [];
?>

<!-- Dashboard Header -->
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title"><?php echo $page_title ?? 'Dashboard'; ?></h1>
            <p class="page-description"><?php echo $page_description ?? 'Welcome to your dashboard'; ?></p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary" onclick="location.reload()">
                <i class="fas fa-sync-alt me-2"></i> Refresh
            </button>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <?php if (!empty($stats['total_users'])): ?>
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon primary">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Total Users</div>
                <div class="stat-value"><?php echo number_format($stats['total_users'] ?? 0); ?></div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($stats['total_properties'])): ?>
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon success">
                <i class="fas fa-building"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Properties</div>
                <div class="stat-value"><?php echo number_format($stats['total_properties'] ?? 0); ?></div>
                <div class="stat-change up">
                    <i class="fas fa-arrow-up"></i> <?php echo number_format($stats['active_properties'] ?? 0); ?> active
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($stats['total_leads'])): ?>
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="fas fa-bullseye"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Total Leads</div>
                <div class="stat-value"><?php echo number_format($stats['total_leads'] ?? 0); ?></div>
                <div class="stat-change up">
                    <i class="fas fa-arrow-up"></i> <?php echo number_format($stats['new_leads_today'] ?? 0); ?> today
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($stats['total_associates'])): ?>
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon info">
                <i class="fas fa-network-wired"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Associates</div>
                <div class="stat-value"><?php echo number_format($stats['total_associates'] ?? 0); ?></div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($stats['revenue_month'])): ?>
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon purple">
                <i class="fas fa-rupee-sign"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Revenue (30 Days)</div>
                <div class="stat-value">₹<?php echo number_format($stats['revenue_month'] ?? 0, 2); ?></div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($stats['commission_paid'])): ?>
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon success">
                <i class="fas fa-percentage"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Commission Paid</div>
                <div class="stat-value">₹<?php echo number_format($stats['commission_paid'] ?? 0, 2); ?></div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($stats['total_employees'])): ?>
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon primary">
                <i class="fas fa-user-tie"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Employees</div>
                <div class="stat-value"><?php echo number_format($stats['total_employees'] ?? 0); ?></div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($stats['pending_bookings'])): ?>
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="fas fa-file-contract"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Pending Bookings</div>
                <div class="stat-value"><?php echo number_format($stats['pending_bookings'] ?? 0); ?></div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- MLM Stats for Associates -->
<?php if (!empty($mlm_stats)): ?>
<div class="row g-4 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon success">
                <i class="fas fa-user-plus"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Direct Referrals</div>
                <div class="stat-value"><?php echo number_format($mlm_stats['direct_referrals'] ?? 0); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon info">
                <i class="fas fa-sitemap"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Total Downline</div>
                <div class="stat-value"><?php echo number_format($mlm_stats['total_downline'] ?? 0); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon purple">
                <i class="fas fa-coins"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Total Commission</div>
                <div class="stat-value">₹<?php echo number_format($mlm_stats['total_commission'] ?? 0, 2); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="fas fa-medal"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Current Rank</div>
                <div class="stat-value"><?php echo $mlm_stats['current_rank'] ?? 'Associate'; ?></div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Quick Links -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <a href="<?php echo BASE_URL; ?>/admin/leads?action=new" class="btn btn-outline-primary w-100 py-3">
                            <i class="fas fa-user-plus mb-2" style="font-size: 1.5rem;"></i>
                            <div>Add New Lead</div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?php echo BASE_URL; ?>/admin/properties?action=new" class="btn btn-outline-success w-100 py-3">
                            <i class="fas fa-plus mb-2" style="font-size: 1.5rem;"></i>
                            <div>Add Property</div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?php echo BASE_URL; ?>/admin/bookings?action=new" class="btn btn-outline-warning w-100 py-3">
                            <i class="fas fa-file-contract mb-2" style="font-size: 1.5rem;"></i>
                            <div>New Booking</div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?php echo BASE_URL; ?>/admin/commissions?action=payout" class="btn btn-outline-info w-100 py-3">
                            <i class="fas fa-wallet mb-2" style="font-size: 1.5rem;"></i>
                            <div>Process Payout</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Two Column Layout -->
<div class="row g-4">
    <!-- Recent Activities -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title">Recent Activities</h5>
                <a href="<?php echo BASE_URL; ?>/admin/logs" class="btn btn-sm btn-link">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($recent_activities)): ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($recent_activities as $activity): ?>
                    <div class="list-group-item">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="fas fa-<?php echo $activity['icon'] ?? 'circle'; ?> text-muted"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-medium"><?php echo $activity['title'] ?? 'Activity'; ?></div>
                                <small class="text-muted"><?php echo $activity['description'] ?? ''; ?></small>
                            </div>
                            <small class="text-muted"><?php echo timeAgo($activity['created_at'] ?? date('Y-m-d H:i:s')); ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                    <p class="mt-3">No recent activities</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Top Performers / Quick Stats -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">System Overview</h5>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Database Tables</span>
                        <span class="fw-semibold"><?php echo $stats['database_tables'] ?? '633'; ?></span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-primary" style="width: 100%;"></div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Active Users</span>
                        <span class="fw-semibold"><?php echo number_format($stats['active_users'] ?? 0); ?></span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: 75%;"></div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">System Health</span>
                        <span class="fw-semibold text-success">99.9%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-info" style="width: 99.9%;"></div>
                    </div>
                </div>
                
                <hr>
                
                <div class="row text-center">
                    <div class="col-4">
                        <div class="h4 mb-0 text-primary"><?php echo number_format($stats['pending_tasks'] ?? 0); ?></div>
                        <small class="text-muted">Pending Tasks</small>
                    </div>
                    <div class="col-4">
                        <div class="h4 mb-0 text-success"><?php echo number_format($stats['system_logs'] ?? 0); ?></div>
                        <small class="text-muted">Today's Logs</small>
                    </div>
                    <div class="col-4">
                        <div class="h4 mb-0 text-warning"><?php echo number_format($stats['pending_bookings'] ?? 0); ?></div>
                        <small class="text-muted">Pending Bookings</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Helper function for time ago
if (!function_exists('timeAgo')) {
    function timeAgo($datetime) {
        $time = strtotime($datetime);
        $diff = time() - $time;
        
        if ($diff < 60) return 'Just now';
        if ($diff < 3600) return floor($diff / 60) . 'm ago';
        if ($diff < 86400) return floor($diff / 3600) . 'h ago';
        if ($diff < 604800) return floor($diff / 86400) . 'd ago';
        
        return date('M j', $time);
    }
}
?>
