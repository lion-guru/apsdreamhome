<?php
/**
 * User List Page
 * Displays list of all users with security hardening
 */

require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/includes/superadmin_helpers.php';

// Role-based access control: Only superadmin can access user management
if (!isSuperAdmin()) {
    header('Location: dashboard.php?error=' . urlencode('Access Denied: Superadmin privilege required.'));
    exit();
}

$page_title = "User Management";
$include_datatables = true;
$breadcrumbs = ["Users" => ""];

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
                        <li class="breadcrumb-item active">User List</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Registered Users</h4>
                        <?php
                        if(isset($_SESSION['error'])) { echo "<div class='alert alert-danger mt-2'>".h($_SESSION['error'])."</div>"; unset($_SESSION['error']); }
                        if(isset($_SESSION['msg'])) { echo "<div class='alert alert-success mt-2'>".h($_SESSION['msg'])."</div>"; unset($_SESSION['msg']); }
                        if(isset($_GET['msg'])) { echo "<div class='alert alert-success mt-2'>".h($_GET['msg'])."</div>"; }
                        ?>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="datatable table table-stripped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Role</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = "SELECT u.*, r.name as role_name FROM user u LEFT JOIN roles r ON u.utype = r.id ORDER BY u.uid DESC";
                                    try {
                                        $db = \App\Core\App::database();
                                        $users = $db->fetchAll($query);
                                        foreach ($users as $row) {
                                            $display_role = $row['role_name'] ?: ($row['utype'] == '2' ? 'Agent' : ($row['utype'] == 'user' ? 'User' : ($row['utype'] == 'builder' ? 'Builder' : $row['utype'])));
                                    ?>
                                    <tr>
                                        <td><?php echo h($row['uid']); ?></td>
                                        <td><?php echo h($row['uname']); ?></td>
                                        <td><?php echo h($row['uemail']); ?></td>
                                        <td><?php echo h($row['uphone']); ?></td>
                                        <td><?php echo h($display_role); ?></td>
                                        <td>
                                            <a href="useredit.php?id=<?php echo h($row['uid']); ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form method="post" action="delete.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                <?php echo getCsrfField(); ?>
                                                <input type="hidden" name="type" value="user">
                                                <input type="hidden" name="id" value="<?php echo h($row['uid']); ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php
                                        }
                                    } catch (Exception $e) {
                                        echo "<tr><td colspan='6' class='text-center text-danger'>Error loading users: " . h($e->getMessage()) . "</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('admin_footer.php'); ?>
