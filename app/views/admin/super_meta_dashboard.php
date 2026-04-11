<?php
/**
 * SUPER ADMIN META DASHBOARD
 * The Ultimate Control Center for APS Dream Home
 * 
 * Features:
 * - Role Switcher: Admin can become ANY role instantly
 * - User Impersonation: Login as any user without password
 * - Unified View: All dashboards data in one place
 * - Quick Management: Manage all roles from one screen
 */

require __DIR__ . '/layouts/unified_start.php';

// Get system stats
$db = \App\Core\Database::getInstance();

// Total users by role
$roleStats = $db->fetchAll("SELECT role, COUNT(*) as count FROM users GROUP BY role");
$roleCounts = array_column($roleStats, 'count', 'role');

// Today's activity
$todayUsers = $db->fetchOne("SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = CURDATE()")['count'] ?? 0;
$todayLeads = $db->fetchOne("SELECT COUNT(*) as count FROM leads WHERE DATE(created_at) = CURDATE()")['count'] ?? 0;
$todayBookings = $db->fetchOne("SELECT COUNT(*) as count FROM bookings WHERE DATE(created_at) = CURDATE()")['count'] ?? 0;

// Pending items
$pendingApprovals = $db->fetchOne("SELECT COUNT(*) as count FROM user_properties WHERE status = 'pending'")['count'] ?? 0;
$pendingPayouts = $db->fetchOne("SELECT COUNT(*) as count FROM payouts WHERE status = 'pending'")['count'] ?? 0;
$pendingTickets = $db->fetchOne("SELECT COUNT(*) as count FROM support_tickets WHERE status = 'open'")['count'] ?? 0;

// Active sessions (approximation)
$activeUsers = $db->fetchOne("SELECT COUNT(DISTINCT user_id) as count FROM user_activity_logs WHERE created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)")['count'] ?? 0;

// All defined roles and their controllers
$roleDashboards = [
    ['role' => 'super_admin', 'name' => 'Super Admin', 'icon' => 'fa-crown', 'url' => '/admin/dashboard', 'color' => 'danger', 'desc' => 'Full system access'],
    ['role' => 'ceo', 'name' => 'CEO', 'icon' => 'fa-user-tie', 'url' => '/admin/ceo-dashboard', 'color' => 'primary', 'desc' => 'Executive overview'],
    ['role' => 'cfo', 'name' => 'CFO', 'icon' => 'fa-chart-line', 'url' => '/admin/cfo-dashboard', 'color' => 'success', 'desc' => 'Financial control'],
    ['role' => 'cmo', 'name' => 'CMO', 'icon' => 'fa-bullhorn', 'url' => '/admin/cm-dashboard', 'color' => 'info', 'desc' => 'Marketing head'],
    ['role' => 'coo', 'name' => 'COO', 'icon' => 'fa-cogs', 'url' => '/admin/coo-dashboard', 'color' => 'warning', 'desc' => 'Operations control'],
    ['role' => 'cto', 'name' => 'CTO', 'icon' => 'fa-laptop-code', 'url' => '/admin/cto-dashboard', 'color' => 'secondary', 'desc' => 'Tech management'],
    ['role' => 'chro', 'name' => 'CHRO', 'icon' => 'fa-users', 'url' => '/admin/hr-dashboard', 'color' => 'dark', 'desc' => 'HR management'],
    ['role' => 'director', 'name' => 'Director', 'icon' => 'fa-user-shield', 'url' => '/admin/director-dashboard', 'color' => 'primary', 'desc' => 'Directorial view'],
    ['role' => 'admin', 'name' => 'Admin', 'icon' => 'fa-user-cog', 'url' => '/admin/dashboard', 'color' => 'info', 'desc' => 'Admin panel'],
    ['role' => 'manager', 'name' => 'Manager', 'icon' => 'fa-user-friends', 'url' => '/manager/dashboard', 'color' => 'warning', 'desc' => 'Team management'],
    ['role' => 'agent', 'name' => 'Agent', 'icon' => 'fa-headset', 'url' => '/agent/dashboard', 'color' => 'success', 'desc' => 'Sales agent view'],
    ['role' => 'associate', 'name' => 'Associate', 'icon' => 'fa-handshake', 'url' => '/associate/dashboard', 'color' => 'primary', 'desc' => 'MLM member view'],
    ['role' => 'employee', 'name' => 'Employee', 'icon' => 'fa-briefcase', 'url' => '/employee/dashboard', 'color' => 'secondary', 'desc' => 'Employee portal'],
    ['role' => 'builder', 'name' => 'Builder', 'icon' => 'fa-hard-hat', 'url' => '/builder/dashboard', 'color' => 'dark', 'desc' => 'Builder dashboard'],
];

$allRoles = [
    ['role' => 'super_admin', 'name' => 'Super Admin', 'users' => $roleCounts['super_admin'] ?? 0],
    ['role' => 'admin', 'name' => 'Admin', 'users' => $roleCounts['admin'] ?? 0],
    ['role' => 'manager', 'name' => 'Manager', 'users' => $roleCounts['manager'] ?? 0],
    ['role' => 'agent', 'name' => 'Agent', 'users' => $roleCounts['agent'] ?? 0],
    ['role' => 'associate', 'name' => 'Associate', 'users' => $roleCounts['associate'] ?? 0],
    ['role' => 'employee', 'name' => 'Employee', 'users' => $roleCounts['employee'] ?? 0],
    ['role' => 'customer', 'name' => 'Customer', 'users' => $roleCounts['customer'] ?? 0],
    ['role' => 'farmer', 'name' => 'Farmer', 'users' => $roleCounts['farmer'] ?? 0],
    ['role' => 'builder', 'name' => 'Builder', 'users' => $roleCounts['builder'] ?? 0],
];
?>

<style>
.super-admin-dashboard .role-card {
    transition: all 0.3s ease;
    border: none;
    border-radius: 16px;
    overflow: hidden;
    position: relative;
}
.super-admin-dashboard .role-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.1);
}
.super-admin-dashboard .role-card .card-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-bottom: 15px;
}
.super-admin-dashboard .impersonate-btn {
    position: absolute;
    top: 15px;
    right: 15px;
    opacity: 0;
    transition: opacity 0.3s;
}
.super-admin-dashboard .role-card:hover .impersonate-btn {
    opacity: 1;
}
.super-admin-dashboard .god-mode-badge {
    position: fixed;
    top: 80px;
    right: 20px;
    background: linear-gradient(135deg, #FFD700, #FFA500);
    color: #000;
    padding: 10px 20px;
    border-radius: 30px;
    font-weight: bold;
    z-index: 9999;
    box-shadow: 0 4px 15px rgba(255, 215, 0, 0.4);
    animation: pulse 2s infinite;
}
@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}
.super-admin-dashboard .quick-action-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
}
.super-admin-dashboard .stat-glow {
    text-shadow: 0 0 20px rgba(79, 70, 229, 0.5);
}
</style>

<!-- GOD MODE BADGE -->
<div class="god-mode-badge">
    <i class="fas fa-crown me-2"></i>GOD MODE ACTIVE
</div>

<!-- PAGE HEADER -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-1 fw-bold"><i class="fas fa-infinity text-warning me-2"></i>Super Admin Meta Dashboard</h1>
        <p class="text-muted mb-0">Control everything. Be anyone. Manage all.</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#impersonateModal">
            <i class="fas fa-user-secret me-2"></i>Impersonate User
        </button>
        <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#roleSwitchModal">
            <i class="fas fa-mask me-2"></i>Switch Role
        </button>
        <button class="btn btn-primary" onclick="location.reload()">
            <i class="fas fa-sync-alt me-2"></i>Refresh
        </button>
    </div>
</div>

<!-- CRITICAL ALERTS -->
<?php if ($pendingApprovals > 0 || $pendingPayouts > 0 || $pendingTickets > 0): ?>
<div class="alert alert-warning alert-dismissible fade show mb-4 border-warning">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <strong>Action Required:</strong>
    <?php if ($pendingApprovals > 0): ?> <span class="badge bg-danger ms-2"><?php echo $pendingApprovals; ?> Properties Pending</span><?php endif; ?>
    <?php if ($pendingPayouts > 0): ?> <span class="badge bg-warning ms-2"><?php echo $pendingPayouts; ?> Payouts Pending</span><?php endif; ?>
    <?php if ($pendingTickets > 0): ?> <span class="badge bg-info ms-2"><?php echo $pendingTickets; ?> Tickets Open</span><?php endif; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- QUICK STATS ROW -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-white-50 mb-1">Total Users</h6>
                        <h3 class="mb-0 fw-bold stat-glow"><?php echo number_format(array_sum($roleCounts)); ?></h3>
                    </div>
                    <div class="bg-white bg-opacity-20 rounded-circle p-3">
                        <i class="fas fa-users fa-2x text-white"></i>
                    </div>
                </div>
                <small class="text-white-50">+<?php echo $todayUsers; ?> today</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-white-50 mb-1">Today's Leads</h6>
                        <h3 class="mb-0 fw-bold stat-glow"><?php echo number_format($todayLeads); ?></h3>
                    </div>
                    <div class="bg-white bg-opacity-20 rounded-circle p-3">
                        <i class="fas fa-bullseye fa-2x text-white"></i>
                    </div>
                </div>
                <small class="text-white-50">New inquiries</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #fc4a1a 0%, #f7b733 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-white-50 mb-1">Today's Bookings</h6>
                        <h3 class="mb-0 fw-bold stat-glow"><?php echo number_format($todayBookings); ?></h3>
                    </div>
                    <div class="bg-white bg-opacity-20 rounded-circle p-3">
                        <i class="fas fa-file-contract fa-2x text-white"></i>
                    </div>
                </div>
                <small class="text-white-50">Revenue generating</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-white-50 mb-1">Active Now</h6>
                        <h3 class="mb-0 fw-bold stat-glow"><?php echo number_format($activeUsers); ?></h3>
                    </div>
                    <div class="bg-white bg-opacity-20 rounded-circle p-3">
                        <i class="fas fa-signal fa-2x text-white"></i>
                    </div>
                </div>
                <small class="text-white-50">Last 1 hour</small>
            </div>
        </div>
    </div>
</div>

<!-- ROLE DASHBOARDS GRID -->
<div class="card mb-4">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0"><i class="fas fa-layer-group me-2 text-primary"></i>Role Dashboards</h5>
        <span class="text-muted small">Click any to view as that role</span>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <?php foreach ($roleDashboards as $dashboard): ?>
            <div class="col-md-4 col-lg-3">
                <div class="card role-card h-100 shadow-sm">
                    <div class="card-body">
                        <button class="btn btn-sm btn-outline-secondary impersonate-btn" 
                                onclick="impersonateRole('<?php echo $dashboard['role']; ?>')"
                                title="Enter as <?php echo $dashboard['name']; ?>">
                            <i class="fas fa-sign-in-alt"></i>
                        </button>
                        <div class="card-icon bg-<?php echo $dashboard['color']; ?> bg-opacity-10 text-<?php echo $dashboard['color']; ?>">
                            <i class="fas <?php echo $dashboard['icon']; ?>"></i>
                        </div>
                        <h5 class="card-title mb-1"><?php echo $dashboard['name']; ?></h5>
                        <p class="text-muted small mb-3"><?php echo $dashboard['desc']; ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-light text-dark">
                                <?php echo $dashboard['users']; ?> Users
                            </span>
                            <a href="<?php echo $base . $dashboard['url']; ?>?view_as=<?php echo $dashboard['role']; ?>" 
                               class="btn btn-sm btn-<?php echo $dashboard['color']; ?>">
                                Enter <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- USER MANAGEMENT QUICK VIEW -->
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0"><i class="fas fa-users-cog me-2 text-primary"></i>User Distribution</h5>
                <a href="<?php echo $base; ?>/admin/users" class="btn btn-sm btn-outline-primary">Manage All Users</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Role</th>
                                <th>Total Users</th>
                                <th>Status</th>
                                <th>Quick Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allRoles as $role): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-<?php echo $role['users'] > 0 ? 'primary' : 'secondary'; ?>">
                                        <?php echo $role['name']; ?>
                                    </span>
                                </td>
                                <td>
                                    <h5 class="mb-0"><?php echo number_format($role['users']); ?></h5>
                                </td>
                                <td>
                                    <?php if ($role['users'] > 0): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">No Users</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?php echo $base; ?>/admin/users?role=<?php echo $role['role']; ?>" 
                                           class="btn btn-outline-primary">
                                            <i class="fas fa-list"></i>
                                        </a>
                                        <a href="<?php echo $base; ?>/admin/users/create?role=<?php echo $role['role']; ?>" 
                                           class="btn btn-outline-success">
                                            <i class="fas fa-plus"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- QUICK ACTIONS -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-transparent py-3">
                <h5 class="mb-0"><i class="fas fa-bolt me-2 text-warning"></i>God Mode Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?php echo $base; ?>/admin/user-properties" class="btn btn-outline-warning d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-home me-2"></i>Approve Properties</span>
                        <?php if ($pendingApprovals > 0): ?>
                        <span class="badge bg-danger"><?php echo $pendingApprovals; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="<?php echo $base; ?>/admin/payouts