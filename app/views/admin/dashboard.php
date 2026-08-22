<?php

/**
 * Admin Dashboard - Compatible with AdminBaseController
 * This file renders content when accessed directly
 */

// Auth handled by controller (AdminController and Admin\AdminDashboardController both check admin auth)

// Get user info from session
$currentUser = [
    'id' => $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0,
    'name' => $_SESSION['admin_name'] ?? $_SESSION['user_name'] ?? 'User',
    'email' => $_SESSION['admin_email'] ?? '',
    'role' => $_SESSION['admin_role'] ?? $_SESSION['role'] ?? 'guest'
];

$currentRole = $currentUser['role'];
$roleName = ucwords(str_replace('_', ' ', $currentRole));

// Page title - use data from controller or defaults
$page_title = $page_title ?? 'Dashboard';
$page_description = $page_description ?? 'Welcome to your admin dashboard';
$active_page = $active_page ?? 'dashboard';

// Stats passed from controller (enterpriseDashboard method)
$stats = $stats ?? [
    'total_users' => 0,
    'total_properties' => 0,
    'total_leads' => 0,
    'new_leads_today' => 0,
    'total_associates' => 0,
    'revenue_month' => 0,
    'total_employees' => 0,
    'pending_bookings' => 0
];

// If using a layout wrapper (from AdminBaseController), just render content
if (isset($layout_content) || (isset($is_standalone) && !$is_standalone)) {
    // This section is for when AdminBaseController renders this view
?>

    <!-- Dashboard Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
<h1 class="page-title"><?php echo e($page_title); ?></h1>
<p class="page-description"><?php echo e($page_description); ?></p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary" onclick="location.reload()">
                    <i class="fas fa-sync-alt me-2"></i> <?= __('admin_btn_refresh', null, 'Refresh') ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Cards Row 1 -->
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="aps-cp-stat aps-cp-stat--blue">
                <div class="aps-cp-stat-icon"><i class="fas fa-users"></i></div>
                <div class="aps-cp-stat-body">
                    <div class="aps-cp-stat-label"><?= __('admin_stat_users', null, 'Total Users') ?></div>
                    <div class="aps-cp-stat-value"><?php echo e(number_format($stats['total_users'])); ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="aps-cp-stat aps-cp-stat--green">
                <div class="aps-cp-stat-icon"><i class="fas fa-building"></i></div>
                <div class="aps-cp-stat-body">
                    <div class="aps-cp-stat-label"><?= __('admin_stat_properties', null, 'Properties') ?></div>
                    <div class="aps-cp-stat-value"><?php echo e(number_format($stats['total_properties'])); ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="aps-cp-stat aps-cp-stat--orange">
                <div class="aps-cp-stat-icon"><i class="fas fa-bullseye"></i></div>
                <div class="aps-cp-stat-body">
                    <div class="aps-cp-stat-label"><?= __('admin_stat_leads', null, 'Total Leads') ?></div>
                    <div class="aps-cp-stat-value"><?php echo e(number_format($stats['total_leads'])); ?></div>
                    <div class="aps-cp-stat-trend up"><i class="fas fa-arrow-up"></i> <?php echo e($stats['new_leads_today']); ?> <?= __('admin_today', null, 'today') ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="aps-cp-stat aps-cp-stat--purple">
                <div class="aps-cp-stat-icon"><i class="fas fa-network-wired"></i></div>
                <div class="aps-cp-stat-body">
                    <div class="aps-cp-stat-label"><?= __('admin_stat_associates', null, 'Associates/Agents') ?></div>
                    <div class="aps-cp-stat-value"><?php echo e(number_format($stats['total_associates'])); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Approvals Alerts -->
    <?php $pendingCount = $pendingCount ?? 0; ?>
    <?php if ($pendingCount > 0): ?>
    <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
        <i class="fas fa-user-clock me-3 fa-2x"></i>
        <div class="flex-grow-1">
            <strong><?php echo e($pendingCount); ?> pending registration<?php echo e($pendingCount > 1 ? 's' : ''); ?> awaiting approval.</strong>
            <span class="ms-2">Agent registrations require manual approval before they can login.</span>
        </div>
        <a href="<?php echo BASE_URL; ?>/admin/users/pending" class="btn btn-warning btn-sm">
            <i class="fas fa-eye me-1"></i>Review Now
        </a>
    </div>
    <?php endif; ?>

    <?php
    $pendingBookings = 0;
    try {
        $db = \App\Core\Database\Database::getInstance();
        $pdo = $db->getConnection();
        $stmt = $pdo->query("SELECT COUNT(*) FROM plot_bookings WHERE approval_status IS NULL OR approval_status = 'pending'");
        $pendingBookings = (int)$stmt->fetchColumn();
    } catch (\Throwable $e) { error_log('dashboard.php pending bookings error: ' . $e->getMessage()); }
    ?>
    <?php if ($pendingBookings > 0): ?>
    <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
        <i class="fas fa-clipboard-check me-3 fa-2x"></i>
        <div class="flex-grow-1">
            <strong><?php echo e($pendingBookings); ?> booking<?php echo e($pendingBookings > 1 ? 's' : ''); ?> pending approval.</strong>
            <span class="ms-2">Associate-submitted bookings need your review before processing.</span>
        </div>
        <a href="<?php echo BASE_URL; ?>/admin/sales/approvals" class="btn btn-info btn-sm text-white">
            <i class="fas fa-eye me-1"></i>Review
        </a>
    </div>
    <?php endif; ?>
    </div>

    <!-- Stats Cards Row 2 -->
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="aps-cp-stat aps-cp-stat--indigo">
                <div class="aps-cp-stat-icon"><i class="fas fa-rupee-sign"></i></div>
                <div class="aps-cp-stat-body">
                    <div class="aps-cp-stat-label"><?= __('admin_stat_revenue', null, 'Revenue (30 Days)') ?></div>
                    <div class="aps-cp-stat-value">₹<?php echo e(number_format($stats['revenue_month'], 2)); ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="aps-cp-stat aps-cp-stat--blue">
                <div class="aps-cp-stat-icon"><i class="fas fa-user-tie"></i></div>
                <div class="aps-cp-stat-body">
                    <div class="aps-cp-stat-label"><?= __('admin_stat_employees', null, 'Employees') ?></div>
                    <div class="aps-cp-stat-value"><?php echo e(number_format($stats['total_employees'])); ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="aps-cp-stat aps-cp-stat--orange">
                <div class="aps-cp-stat-icon"><i class="fas fa-file-contract"></i></div>
                <div class="aps-cp-stat-body">
                    <div class="aps-cp-stat-label"><?= __('admin_pending_bookings', null, 'Pending Bookings') ?></div>
                    <div class="aps-cp-stat-value"><?php echo e(number_format($stats['pending_bookings'])); ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="aps-cp-stat aps-cp-stat--green">
                <div class="aps-cp-stat-icon"><i class="fas fa-check-circle"></i></div>
                <div class="aps-cp-stat-body">
                    <div class="aps-cp-stat-label"><?= __('admin_stat_system', null, 'System Status') ?></div>
                    <div class="aps-cp-stat-value text-success"><?= __('admin_online', null, 'Online') ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card aps-cp-card">
                <div class="card-header aps-cp-card-header">
                    <h5 class="card-title"><i class="fas fa-bolt me-2"></i><?= __('admin_quick_actions', null, 'Quick Actions') ?></h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="<?php echo BASE_URL; ?>/admin/leads?action=new" class="btn btn-outline-primary w-100 py-3">
                                <i class="fas fa-user-plus mb-2" class="style-41417"></i>
                                <div><?= __('admin_action_add_lead', null, 'Add New Lead') ?></div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?php echo BASE_URL; ?>/admin/properties?action=new" class="btn btn-outline-success w-100 py-3">
                                <i class="fas fa-plus mb-2" class="style-41417"></i>
                                <div><?= __('admin_action_add_property', null, 'Add Property') ?></div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?php echo BASE_URL; ?>/admin/bookings?action=new" class="btn btn-outline-warning w-100 py-3">
                                <i class="fas fa-file-contract mb-2" class="style-41417"></i>
                                <div><?= __('admin_new_booking', null, 'New Booking') ?></div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?php echo BASE_URL; ?>/admin/mlm/commissions?action=payout" class="btn btn-outline-info w-100 py-3">
                                <i class="fas fa-wallet mb-2" class="style-41417"></i>
                                <div><?= __('admin_process_payout', null, 'Process Payout') ?></div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Two Column Layout -->
    <div class="row g-4">
        <!-- Recent Leads -->
        <div class="col-lg-6">
            <div class="card aps-cp-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title"><i class="fas fa-user-clock me-2"></i><?= __('admin_recent_leads', null, 'Recent Leads') ?></h5>
                    <a href="<?php echo BASE_URL; ?>/admin/leads" class="btn btn-sm btn-primary"><?= __('admin_view_all', null, 'View All') ?></a>
                </div>
                <div class="card-body p-0">
                    <?php
                    $recentLeads = $recent_leads ?? [];
                    if (!empty($recentLeads)):
                    ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($recentLeads as $lead): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="fw-medium"><?php echo htmlspecialchars($lead['name'] ?? 'Unknown'); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars($lead['email'] ?? ''); ?></small>
                                            </div>
                                            <?php $statusColor = $lead['status'] === 'hot' ? 'danger' : ($lead['status'] === 'warm' ? 'warning' : 'secondary'); ?>
                                            <span class="badge bg-<?php echo e($statusColor); ?>">
                                                <?php echo e(ucfirst($lead['status'] ?? 'new')); ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="aps-empty-state">
                                <i class="fas fa-user-plus fa-3x" aria-hidden="true"></i>
                                <p class="mb-0"><?= __('admin_no_leads_found', null, 'No leads found') ?></p>
                            </div>
                        <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- System Overview -->
        <div class="col-lg-6">
            <div class="card aps-cp-card">
                <div class="card-header aps-cp-card-header">
                    <h5 class="card-title"><i class="fas fa-server me-2"></i><?= __('admin_system_overview', null, 'System Overview') ?></h5>
                </div>
                <div class="card-body aps-cp-card-body">
<div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span><?= __('admin_database_tables', null, 'Database Tables') ?></span>
                            <span class="fw-semibold"><?php echo e($stats['database_tables'] ?? '—'); ?></span>
                        </div>
                        <div class="progress" class="style-29939">
                            <div class="progress-bar bg-primary" class="style-13113"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span><?= __('admin_active_users', null, 'Active Users') ?></span>
                            <span class="fw-semibold"><?php echo e(number_format($stats['active_users'] ?? 0)); ?></span>
                        </div>
                        <div class="progress" class="style-29939">
                            <div class="progress-bar bg-success" class="style-58158"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span><?= __('admin_system_health', null, 'System Health') ?></span>
                            <span class="fw-semibold text-success"><?php echo e(number_format($stats['system_health_pct'] ?? 99.9, 1)); ?>%</span>
                        </div>
                        <div class="progress" class="style-29939">
                            <div class="progress-bar bg-info" class="style-10867"></div>
                        </div>
                    </div>
                        <div class="progress" class="style-29939">
                            <div class="progress-bar bg-primary" class="style-13113"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span><?= __('admin_active_users', null, 'Active Users') ?></span>
                            <span class="fw-semibold"><?php echo e(number_format($stats['active_users'] ?? 0)); ?></span>
                        </div>
                        <div class="progress" class="style-29939">
                            <div class="progress-bar bg-success" class="style-14876"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span><?= __('admin_system_health', null, 'System Health') ?></span>
                            <span class="fw-semibold text-success"><?php echo e(number_format($stats['system_health_pct'] ?? 99.9, 1)); ?>%</span>
                        </div>
                        <div class="progress" class="style-29939">
                            <div class="progress-bar bg-info" class="style-97316"></div>
                        </div>
                    </div>

                    <hr>

                    <div class="row text-center">
                        <div class="col-4">
                            <div class="h4 mb-0 text-primary"><?php echo e(number_format($stats['total_properties'])); ?></div>
                            <small class="text-muted"><?= __('admin_property', null, 'Properties') ?></small>
                        </div>
                        <div class="col-4">
                            <div class="h4 mb-0 text-success"><?php echo e(number_format($stats['total_leads'])); ?></div>
                            <small class="text-muted"><?= __('admin_total_leads', null, 'Total Leads') ?></small>
                        </div>
                        <div class="col-4">
                            <div class="h4 mb-0 text-warning"><?php echo e(number_format($stats['pending_bookings'])); ?></div>
                            <small class="text-muted"><?= __('admin_pending', null, 'Pending') ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php } ?>
