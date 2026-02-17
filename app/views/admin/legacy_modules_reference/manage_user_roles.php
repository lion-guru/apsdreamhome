<?php

/**
 * Manage User Roles
 * 
 * Interface to manage role assignments for employees.
 */

require_once __DIR__ . '/core/init.php';

// Check if user has permission to manage user roles (Superadmin only)
if (!isSuperAdmin()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = $_GET['msg'] ?? '';

// Handle Role Assignment (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_role'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid security token.";
    } else {
        $user_id = intval($_POST['user_id'] ?? 0);
        $role_id = intval($_POST['role_id'] ?? 0);

        if ($user_id > 0 && $role_id > 0) {
            $db = \App\Core\App::database();
            // Prevent duplicate role assignment
            $exists = $db->fetch("SELECT 1 FROM user_roles WHERE user_id = :user_id AND role_id = :role_id", [
                'user_id' => $user_id,
                'role_id' => $role_id
            ]);

            if (!$exists) {
                if ($db->insert("INSERT INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)", [
                    'user_id' => $user_id,
                    'role_id' => $role_id
                ])) {
                    // Log audit trail
                    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
                    $action = 'Role Assignment';
                    $details = "Assigned role ID $role_id to user ID $user_id";
                    $admin_id = $_SESSION['auser_id'] ?? 0;
                    $db->insert("INSERT INTO audit_log (user_id, action, details, ip_address) VALUES (:user_id, :action, :details, :ip)", [
                        'user_id' => $admin_id,
                        'action' => $action,
                        'details' => $details,
                        'ip' => $ip
                    ]);

                    // Fetch names for notification
                    $u_row = $db->fetch("SELECT name FROM employees WHERE id = :id", ['id' => $user_id]);
                    $u_name = $u_row['name'] ?? "User #$user_id";

                    $r_row = $db->fetch("SELECT role_name FROM roles WHERE id = :id", ['id' => $role_id]);
                    $r_name = $r_row['role_name'] ?? "Role #$role_id";

                    // Send notification
                    require_once __DIR__ . '/../includes/notification_manager.php';
                    require_once __DIR__ . '/../includes/email_service.php';
                    $nm = new NotificationManager($db->getConnection(), new EmailService());
                    $nm->send([
                        'user_id' => 1,
                        'template' => 'USER_ROLE_UPDATED',
                        'data' => [
                            'user_name' => $u_name,
                            'role_name' => $r_name,
                            'action' => 'assigned',
                            'admin_name' => $_SESSION['auser'] ?? 'Admin'
                        ],
                        'channels' => ['db']
                    ]);

                    $success = 'Role assigned successfully.';
                } else {
                    $error = 'Error assigning role.';
                }
            } else {
                $error = 'User already has this role.';
            }
        } else {
            $error = 'Please select both a user and a role.';
        }
    }
}

// Handle Role Removal (POST for security)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_role_assignment'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid security token.";
    } else {
        $user_id = intval($_POST['remove_user_id'] ?? 0);
        $role_id = intval($_POST['remove_role_id'] ?? 0);

        if ($user_id > 0 && $role_id > 0) {
            $db = \App\Core\App::database();
            if ($db->query("DELETE FROM user_roles WHERE user_id = :user_id AND role_id = :role_id", [
                'user_id' => $user_id,
                'role_id' => $role_id
            ])) {
                // Log audit trail
                $ip = $_SERVER['REMOTE_ADDR'] ?? '';
                $action = 'Role Removal';
                $details = "Removed role ID $role_id from user ID $user_id";
                $admin_id = $_SESSION['auser_id'] ?? 0;
                $db->insert("INSERT INTO audit_log (user_id, action, details, ip_address) VALUES (:user_id, :action, :details, :ip)", [
                    'user_id' => $admin_id,
                    'action' => $action,
                    'details' => $details,
                    'ip' => $ip
                ]);

                // Fetch names for notification
                $u_row = $db->fetch("SELECT name FROM employees WHERE id = :id", ['id' => $user_id]);
                $u_name = $u_row['name'] ?? "User #$user_id";

                $r_row = $db->fetch("SELECT role_name FROM roles WHERE id = :id", ['id' => $role_id]);
                $r_name = $r_row['role_name'] ?? "Role #$role_id";

                // Send notification
                require_once __DIR__ . '/../includes/notification_manager.php';
                require_once __DIR__ . '/../includes/email_service.php';
                $nm = new NotificationManager($db->getConnection(), new EmailService());
                $nm->send([
                    'user_id' => 1,
                    'template' => 'USER_ROLE_UPDATED',
                    'data' => [
                        'user_name' => $u_name,
                        'role_name' => $r_name,
                        'action' => 'removed',
                        'admin_name' => $_SESSION['auser'] ?? 'Admin'
                    ],
                    'channels' => ['db']
                ]);

                $success = 'Role removed successfully.';
            } else {
                $error = 'Error removing role assignment.';
            }
        }
    }
}

// Fetch data for display
$db = \App\Core\App::database();
$users = $db->fetchAll("SELECT id, name FROM employees WHERE status='active' ORDER BY name");
$roles = $db->fetchAll("SELECT id, name FROM roles ORDER BY name");
$assignments = $db->fetchAll("SELECT e.id as user_id, e.name as user, r.id as role_id, r.name as role 
                           FROM user_roles ur 
                           JOIN employees e ON ur.user_id=e.id 
                           JOIN roles r ON ur.role_id=r.id 
                           ORDER BY e.name, r.name");

$page_title = 'Manage User Roles';
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
                        <li class="breadcrumb-item active">User Roles</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Assign New Role</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <?php echo getCsrfField(); ?>
                            <div class="form-group">
                                <label>Employee</label>
                                <select name="user_id" class="form-control select" required>
                                    <option value="">Select Employee</option>
                                    <?php foreach ($users as $u): ?>
                                        <option value="<?php echo $u['id']; ?>"><?php echo h($u['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Role</label>
                                <select name="role_id" class="form-control select" required>
                                    <option value="">Select Role</option>
                                    <?php foreach ($roles as $r): ?>
                                        <option value="<?php echo $r['id']; ?>"><?php echo h($r['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mt-4">
                                <button type="submit" name="assign_role" class="btn btn-primary btn-block">Assign Role</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Current Assignments</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-center mb-0 datatable">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Role</th>
                                        <th class="text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($assignments && count($assignments) > 0): ?>
                                        <?php foreach ($assignments as $a): ?>
                                            <tr>
                                                <td><?php echo h($a['user']); ?></td>
                                                <td>
                                                    <span class="badge badge-pill bg-info-light">
                                                        <?php echo h($a['role']); ?>
                                                    </span>
                                                </td>
                                                <td class="text-right">
                                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to remove this role assignment?');">
                                                        <?php echo getCsrfField(); ?>
                                                        <input type="hidden" name="remove_user_id" value="<?php echo $a['user_id']; ?>">
                                                        <input type="hidden" name="remove_role_id" value="<?php echo $a['role_id']; ?>">
                                                        <button type="submit" name="remove_role_assignment" class="btn btn-sm bg-danger-light">
                                                            <i class="fas fa-trash"></i> Remove
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center">No assignments found</td>
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