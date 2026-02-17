<?php
/**
 * User Sessions Management
 * 
 * View and manage active and past user sessions.
 */

require_once __DIR__ . '/core/init.php';

// Check if user has permission (Superadmin only for session management)
if (!isSuperAdmin()) {
    header('Location: dashboard.php');
    exit();
}

$db = \App\Core\App::database();

// Fetch user sessions with employee details
$query = "SELECT s.id, s.user_id, e.name as employee_name, s.login_time, s.logout_time, s.ip_address, s.status, s.user_agent 
          FROM user_sessions s 
          LEFT JOIN employees e ON s.user_id = e.id 
          ORDER BY s.login_time DESC 
          LIMIT 200";
$sessions = $db->fetchAll($query);

$page_title = 'User Sessions';
require_once __DIR__ . '/admin_header.php';
require_once __DIR__ . '/admin_sidebar.php';
?>

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="system_health.php">System Health</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Sessions</li>
                        </ol>
                    </nav>
                    <h3 class="page-title fw-bold text-primary"><?php echo h($page_title); ?></h3>
                    <p class="text-muted small mb-0">Monitor and manage active and historical user sessions</p>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h4 class="card-title mb-0 fw-bold">User Session History</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-center mb-0 datatable">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Login Time</th>
                                        <th>Logout Time</th>
                                        <th>IP Address</th>
                                        <th>Status</th>
                                        <th>User Agent</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($sessions)): ?>
                                        <?php foreach($sessions as $s): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-medium">
                                                    <a href="employee_details.php?id=<?php echo h($s['user_id']); ?>" class="text-primary text-decoration-none">
                                                        <?php echo h($s['employee_name'] ?? 'Unknown (' . $s['user_id'] . ')'); ?>
                                                    </a>
                                                </div>
                                            </td>
                                            <td><span class="small"><?php echo date('d M Y, H:i:s', strtotime($s['login_time'])); ?></span></td>
                                            <td>
                                                <?php if ($s['logout_time']): ?>
                                                    <span class="small"><?php echo date('d M Y, H:i:s', strtotime($s['logout_time'])); ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-success-light text-success fw-bold">Active</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><code class="text-dark bg-light px-2 py-1 rounded small"><?php echo h($s['ip_address']); ?></code></td>
                                            <td>
                                                <?php 
                                                $status_class = 'bg-info';
                                                if ($s['status'] === 'active') $status_class = 'bg-success';
                                                if ($s['status'] === 'expired') $status_class = 'bg-warning';
                                                if ($s['status'] === 'logged_out') $status_class = 'bg-secondary';
                                                ?>
                                                <span class="badge <?php echo $status_class; ?>">
                                                    <?php echo h(ucfirst(str_replace('_', ' ', $s['status']))); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="text-muted small text-truncate" style="max-width: 200px;" title="<?php echo h($s['user_agent']); ?>">
                                                    <?php echo h($s['user_agent']); ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">No session records found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$include_datatables = true;
require_once __DIR__ . '/admin_footer.php'; 
?>



