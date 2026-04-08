<?php
/**
 * Admin Dashboard Index View
 * Main dashboard for admin users
 */
$dashboard_stats = $dashboard_stats ?? [];
$recent_activities = $recent_activities ?? [];
$quick_actions = $quick_actions ?? [];
$page_title = $page_title ?? 'Admin Dashboard';
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Admin Dashboard</h2>
                <p class="text-muted mb-0">Welcome to your administration panel</p>
            </div>
            <div class="text-end">
                <span class="badge bg-primary"><?php echo date('l, F j, Y'); ?></span>
            </div>
        </div>
        
        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1">Total Properties</p>
                                <h3 class="mb-0"><?php echo $dashboard_stats['total_properties'] ?? 0; ?></h3>
                            </div>
                            <div class="bg-primary bg-opacity-10 p-2 rounded">
                                <i class="fas fa-building text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1">Total Users</p>
                                <h3 class="mb-0"><?php echo $dashboard_stats['total_users'] ?? 0; ?></h3>
                            </div>
                            <div class="bg-success bg-opacity-10 p-2 rounded">
                                <i class="fas fa-users text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1">Active Leads</p>
                                <h3 class="mb-0"><?php echo $dashboard_stats['active_leads'] ?? 0; ?></h3>
                            </div>
                            <div class="bg-warning bg-opacity-10 p-2 rounded">
                                <i class="fas fa-user-tie text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1">Bookings</p>
                                <h3 class="mb-0"><?php echo $dashboard_stats['total_bookings'] ?? 0; ?></h3>
                            </div>
                            <div class="bg-info bg-opacity-10 p-2 rounded">
                                <i class="fas fa-calendar-check text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-md-2">
                                <a href="<?php echo $base; ?>/admin/properties/create" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-plus me-2"></i>Add Property
                                </a>
                            </div>
                            <div class="col-md-2">
                                <a href="<?php echo $base; ?>/admin/users/create" class="btn btn-outline-success w-100">
                                    <i class="fas fa-user-plus me-2"></i>Add User
                                </a>
                            </div>
                            <div class="col-md-2">
                                <a href="<?php echo $base; ?>/admin/leads" class="btn btn-outline-warning w-100">
                                    <i class="fas fa-address-book me-2"></i>View Leads
                                </a>
                            </div>
                            <div class="col-md-2">
                                <a href="<?php echo $base; ?>/admin/bookings" class="btn btn-outline-info w-100">
                                    <i class="fas fa-book me-2"></i>Bookings
                                </a>
                            </div>
                            <div class="col-md-2">
                                <a href="<?php echo $base; ?>/admin/campaigns/create" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-bullhorn me-2"></i>New Campaign
                                </a>
                            </div>
                            <div class="col-md-2">
                                <a href="<?php echo $base; ?>/admin/settings" class="btn btn-outline-dark w-100">
                                    <i class="fas fa-cog me-2"></i>Settings
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Activities -->
        <div class="row">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Activities</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recent_activities)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($recent_activities as $activity): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-circle text-primary me-2 small"></i>
                                            <?php echo htmlspecialchars($activity['description'] ?? 'Activity'); ?>
                                        </div>
                                        <small class="text-muted"><?php echo isset($activity['created_at']) ? date('M d, H:i', strtotime($activity['created_at'])) : '-'; ?></small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No recent activities</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-link me-2"></i>Quick Links</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <a href="<?php echo $base; ?>/admin/properties" class="list-group-item list-group-item-action">
                                <i class="fas fa-building me-2 text-primary"></i>Properties
                            </a>
                            <a href="<?php echo $base; ?>/admin/users" class="list-group-item list-group-item-action">
                                <i class="fas fa-users me-2 text-success"></i>Users
                            </a>
                            <a href="<?php echo $base; ?>/admin/leads" class="list-group-item list-group-item-action">
                                <i class="fas fa-user-tie me-2 text-warning"></i>Leads
                            </a>
                            <a href="<?php echo $base; ?>/admin/network" class="list-group-item list-group-item-action">
                                <i class="fas fa-sitemap me-2 text-info"></i>Network
                            </a>
                            <a href="<?php echo $base; ?>/admin/commission" class="list-group-item list-group-item-action">
                                <i class="fas fa-percentage me-2 text-secondary"></i>Commission
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
