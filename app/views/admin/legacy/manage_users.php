<?php
/**
 * Manage Users Page
 * Manages both admin and regular users with role-based access control
 */

require_once __DIR__ . '/core/init.php';

// Require super admin privileges for this page
if (!isSuperAdmin()) {
    header('Location: dashboard.php');
    exit();
}

// Initialize database connection
$db = \App\Core\App::database();

// Handle user actions (activate/deactivate/delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['user_id'], $_POST['user_type'])) {
    $user_id = intval($_POST['user_id']);
    $user_type = $_POST['user_type'];
    $action = $_POST['action'];
    $redirect = 'manage_users.php';
    
    // Verify CSRF token
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = "Invalid request. Please try again.";
        header("Location: $redirect");
        exit();
    }
    
    $table = ($user_type === 'admin') ? 'admin' : 'user';
    $id_field = ($user_type === 'admin') ? 'id' : 'uid';
    
    try {
        switch ($action) {
            case 'activate':
                $result = $db->update($table, ['status' => 'active'], "$id_field = :id", ['id' => $user_id]);
                $message = "User activated successfully";
                break;
                
            case 'deactivate':
                $result = $db->update($table, ['status' => 'inactive'], "$id_field = :id", ['id' => $user_id]);
                $message = "User deactivated successfully";
                break;
                
            case 'delete':
                // Prevent deleting your own account
                if (($user_type === 'admin' && $user_id == getAuthUserId())) {
                    throw new Exception("You cannot delete your own account");
                }
                
                // For admin users, check if they're the last super admin
                if ($user_type === 'admin') {
                    $super_count = $db->fetch("SELECT COUNT(*) as count FROM admin WHERE role = 'superadmin' AND id != :id", ['id' => $user_id])['count'];
                    
                    if ($super_count < 1) {
                        throw new Exception("Cannot delete the last super admin");
                    }
                }
                
                $result = $db->delete($table, "$id_field = :id", ['id' => $user_id]);
                $message = "User deleted successfully";
                break;
                
            default:
                throw new Exception("Invalid action");
        }
        
        if ($result) {
            $_SESSION['success'] = $message;
            
            // Log the action
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $action_type = strtoupper($action);
            $user_identifier = ($user_type === 'admin') ? "Admin ID: $user_id" : "User ID: $user_id";
            $details = "$action_type $user_identifier by Admin ID: " . getAuthUserId();
            
            $db->insert('audit_log', [
                'user_id' => $_SESSION['auth']['user_id'],
                'action' => $action_type,
                'details' => $details,
                'ip_address' => $ip
            ]);
        } else {
            throw new Exception("Failed to execute action");
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
    
    header("Location: $redirect");
    exit();
}

// Fetch all users (both admin and regular users)
$users = [];

try {
    // Fetch admin users
    $admin_users = $db->fetchAll("SELECT id, auser AS name, email, role, status, 'admin' AS type FROM admin");
    if ($admin_users) {
        $users = array_merge($users, $admin_users);
    }

    // Fetch regular users
    $regular_users = $db->fetchAll("SELECT uid as id, uname as name, uemail as email, utype AS role, status, 'user' AS type FROM user");
    if ($regular_users) {
        $users = array_merge($users, $regular_users);
    }
} catch (Exception $e) {
    error_log("Error fetching users: " . $e->getMessage());
}

$page_title = "Manage Users & Admins";
$include_datatables = true;
$breadcrumbs = ["User Management" => ""];

include('admin_header.php');
include('admin_sidebar.php');
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <h3 class="page-title"><?php echo h($page_title); ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Manage Users</li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto">
                    <a href="user_add.php" class="btn add-btn"><i class="fa fa-plus"></i> Add New User</a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">All Users & Admins</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php 
                                echo h($_SESSION['success']);
                                unset($_SESSION['success']);
                                ?>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php 
                                echo h($_SESSION['error']);
                                unset($_SESSION['error']);
                                ?>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        <?php endif; ?>

                        <div class="table-responsive">
                            <table id="usersTable" class="datatable table table-stripped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?php echo h($user['id']); ?></td>
                                            <td><?php echo h($user['name']); ?></td>
                                            <td><?php echo h($user['email']); ?></td>
                                            <td>
                                                <span class="badge badge-<?php 
                                                    $r = strtolower($user['role']);
                                                    echo ($r === 'superadmin') ? 'danger' : 
                                                         (($r === 'admin') ? 'primary' : 'info'); 
                                                ?>">
                                                    <?php echo ucfirst(h($user['role'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-secondary">
                                                    <?php echo ucfirst(h($user['type'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?php 
                                                    echo ($user['status'] === 'active') ? 'success' : 'warning'; 
                                                ?>">
                                                    <?php echo ucfirst(h($user['status'])); ?>
                                                </span>
                                            </td>
                                            <td class="text-right">
                                                <div class="dropdown dropdown-action">
                                                    <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="material-icons">more_vert</i></a>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <a class="dropdown-item" href="user_edit.php?type=<?php echo h($user['type']); ?>&id=<?php echo h($user['id']); ?>"><i class="fa fa-pencil m-r-5"></i> Edit</a>
                                                        
                                                        <?php if ($user['status'] === 'active'): ?>
                                                            <a class="dropdown-item deactivate-user" href="#" data-id="<?php echo h($user['id']); ?>" data-type="<?php echo h($user['type']); ?>"><i class="fa fa-user-times m-r-5"></i> Deactivate</a>
                                                        <?php else: ?>
                                                            <a class="dropdown-item activate-user" href="#" data-id="<?php echo h($user['id']); ?>" data-type="<?php echo h($user['type']); ?>"><i class="fa fa-user-check m-r-5"></i> Activate</a>
                                                        <?php endif; ?>
                                                        
                                                        <a class="dropdown-item delete-user" href="#" data-id="<?php echo h($user['id']); ?>" data-type="<?php echo h($user['type']); ?>" data-name="<?php echo h($user['name']); ?>"><i class="fa fa-trash-o m-r-5"></i> Delete</a>
                                                    </div>
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
        </div>
    </div>
</div>

<!-- Action Forms (Hidden) -->
<form id="userActionForm" method="post" style="display: none;">
    <input type="hidden" name="user_id" id="actionUserId">
    <input type="hidden" name="user_type" id="actionUserType">
    <input type="hidden" name="action" id="actionType">
    <?php echo getCsrfField(); ?>
</form>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle activate/deactivate actions
    $('.activate-user, .deactivate-user').on('click', function(e) {
        e.preventDefault();
        const userId = $(this).data('id');
        const userType = $(this).data('type');
        const action = $(this).hasClass('activate-user') ? 'activate' : 'deactivate';
        
        if (confirm('Are you sure you want to ' + action + ' this user?')) {
            $('#actionUserId').val(userId);
            $('#actionUserType').val(userType);
            $('#actionType').val(action);
            $('#userActionForm').submit();
        }
    });
    
    // Handle delete action
    $('.delete-user').on('click', function(e) {
        e.preventDefault();
        const userId = $(this).data('id');
        const userName = $(this).data('name');
        const userType = $(this).data('type');
        
        if (confirm('Are you sure you want to delete ' + userName + '? This action cannot be undone.')) {
            $('#actionUserId').val(userId);
            $('#actionUserType').val(userType);
            $('#actionType').val('delete');
            $('#userActionForm').submit();
        }
    });
});
</script>

<?php include('admin_footer.php'); ?>



