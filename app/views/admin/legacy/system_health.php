<?php
/**
 * System Health Monitor
 * 
 * Overview of system status and performance metrics.
 */

require_once __DIR__ . '/core/init.php';

$db = \App\Core\App::database();

// Check if user has permission (Superadmin only for system health)
if (!isSuperAdmin()) {
    header('Location: dashboard.php');
    exit();
}

$health = [];
// Check DB connection
$health['database'] = $db->getConnection()->ping() ? 'OK' : 'FAIL';
// Check PHP version
$health['php_version'] = phpversion();

// Check disk space (Handle potential errors if disk_free_space fails)
try {
    $root_path = DIRECTORY_SEPARATOR === '\\' ? "C:" : "/";
    $free_space = @disk_free_space($root_path);
    $total_space = @disk_total_space($root_path);
    
    $health['disk_free'] = $free_space ? round($free_space / 1024 / 1024 / 1024, 2) . ' GB' : 'Unknown';
    $health['disk_total'] = $total_space ? round($total_space / 1024 / 1024 / 1024, 2) . ' GB' : 'Unknown';
} catch (Exception $e) {
    $health['disk_free'] = 'Error';
    $health['disk_total'] = 'Error';
}

// Check recent failed logins
$failed_login_row = $db->fetchOne("SELECT COUNT(*) as c FROM audit_log WHERE action='Login Failed' AND created_at >= NOW() - INTERVAL 1 DAY");
$health['failed_logins_24h'] = $failed_login_row['c'] ?? 0;

$page_title = 'System Health Monitor';
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
                    <h3 class="page-title"><?php echo $page_title; ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">System Health</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">System Status</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Component</th>
                                        <th>Status/Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Database Connection</td>
                                        <td>
                                            <?php if ($health['database'] === 'OK'): ?>
                                                <span class="badge badge-pill bg-success-light">Connected</span>
                                            <?php else: ?>
                                                <span class="badge badge-pill bg-danger-light">Failed</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>PHP Version</td>
                                        <td><span class="badge badge-pill bg-info-light"><?php echo h($health['php_version']); ?></span></td>
                                    </tr>
                                    <tr>
                                        <td>Disk Space (Free / Total)</td>
                                        <td><?php echo $health['disk_free']; ?> / <?php echo $health['disk_total']; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Failed Logins (Last 24h)</td>
                                        <td>
                                            <?php if ($health['failed_logins_24h'] > 10): ?>
                                                <span class="badge badge-pill bg-danger-light"><?php echo $health['failed_logins_24h']; ?> (High)</span>
                                            <?php else: ?>
                                                <span class="badge badge-pill bg-success-light"><?php echo $health['failed_logins_24h']; ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Quick Actions</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <a href="backup.php" class="btn btn-outline-primary btn-block mb-3">
                                    <i class="fas fa-database"></i> Database Backup
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="audit_log.php" class="btn btn-outline-info btn-block mb-3">
                                    <i class="fas fa-history"></i> View Audit Logs
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="manage_site_settings.php" class="btn btn-outline-secondary btn-block mb-3">
                                    <i class="fas fa-cog"></i> System Settings
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="user_sessions.php" class="btn btn-outline-warning btn-block mb-3">
                                    <i class="fas fa-users"></i> Active Sessions
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
require_once __DIR__ . '/admin_footer.php'; 
?>



