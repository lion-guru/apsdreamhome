<?php

/**
 * HR Dashboard
 * 
 * HR-specific dashboard with relevant metrics and actions.
 */

require_once __DIR__ . '/core/init.php';

// Check if user is logged in
adminAccessControl(['HR', 'super_admin', 'superadmin', 'admin']);

$admin_id = getAuthUserId();
$admin_role = getAuthSubRole();

// Initialize database connection
$db = \App\Core\App::database();

$perfManager = PerformanceManager::getInstance();

// HR-specific queries with caching
try {
    $total_employees_data = $perfManager->executeCachedQuery("SELECT COUNT(*) as count FROM employees WHERE status='active'", 3600);
    $total_employees = $total_employees_data[0]['count'] ?? 0;

    $on_leave_data = $perfManager->executeCachedQuery("SELECT COUNT(*) as count FROM leave_requests WHERE status='approved' AND CURDATE() BETWEEN start_date AND end_date", 300);
    $on_leave = $on_leave_data[0]['count'] ?? 0;

    // Fixed variables for display
    $total_leaves = $on_leave;

    $new_hires_data = $perfManager->executeCachedQuery("SELECT COUNT(*) as count FROM employees WHERE hire_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)", 3600);
    $new_hires = $new_hires_data[0]['count'] ?? 0;

    $pending_requests_data = $perfManager->executeCachedQuery("SELECT COUNT(*) as count FROM leave_requests WHERE status='pending'", 300);
    $pending_requests = $pending_requests_data[0]['count'] ?? 0;

    // Fixed variable for display
    $total_attendance_data = $perfManager->executeCachedQuery("SELECT COUNT(*) as count FROM attendance WHERE attendance_date = CURDATE()", 300);
    $total_attendance = $total_attendance_data[0]['count'] ?? 0;
} catch (Exception $e) {
    // Log error but don't break the page
    error_log("HR Dashboard Query Error: " . $e->getMessage());
}

$page_title = 'HR Dashboard';
require_once __DIR__ . '/admin_header.php';
require_once __DIR__ . '/admin_sidebar.php';
?>

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <h3 class="page-title"><?php echo h($page_title); ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">HR Dashboard</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <span class="dash-widget-icon text-primary border-primary">
                                <i class="fe fe-users"></i>
                            </span>
                            <div class="dash-count">
                                <h3><?php echo $total_employees; ?></h3>
                            </div>
                        </div>
                        <div class="dash-widget-info">
                            <h6 class="text-muted">Total Employees</h6>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-primary w-100"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <span class="dash-widget-icon text-success">
                                <i class="fe fe-calendar"></i>
                            </span>
                            <div class="dash-count">
                                <h3><?php echo $total_leaves; ?></h3>
                            </div>
                        </div>
                        <div class="dash-widget-info">
                            <h6 class="text-muted">On Leave Today</h6>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-success w-100"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <span class="dash-widget-icon text-danger">
                                <i class="fe fe-check-square"></i>
                            </span>
                            <div class="dash-count">
                                <h3><?php echo $total_attendance; ?></h3>
                            </div>
                        </div>
                        <div class="dash-widget-info">
                            <h6 class="text-muted">Attendance Today</h6>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-danger w-100"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <span class="dash-widget-icon text-warning">
                                <i class="fe fe-clock"></i>
                            </span>
                            <div class="dash-count">
                                <h3><?php echo $pending_requests; ?></h3>
                            </div>
                        </div>
                        <div class="dash-widget-info">
                            <h6 class="text-muted">Pending Requests</h6>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-warning w-100"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Quick Actions</h4>
                    </div>
                    <div class="card-body">
                        <div class="btn-group">
                            <a href="employees.php" class="btn btn-primary">Manage Employees</a>
                            <a href="leaves.php" class="btn btn-info">Manage Leaves</a>
                            <a href="attendance.php" class="btn btn-success">Manage Attendance</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>

