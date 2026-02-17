<?php
/**
 * Admin List Page
 * Displays list of all administrators with security hardening
 */

require_once __DIR__ . '/core/init.php';

// Additional security check for superadmin
if (!isSuperAdmin()) {
    header('Location: dashboard.php?error=' . urlencode('Access Denied: Superadmin privilege required.'));
    exit();
}

$page_title = "Admin List";
$include_datatables = true;

// Standard Admin Header & Sidebar
include 'admin_header.php';
include 'admin_sidebar.php';
?>

<!-- Main Content -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <h3 class="page-title">Admin List</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Admin List</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">List of Administrators</h4>
                        <?php
                        if(isset($_SESSION['error'])) { echo "<div class='alert alert-danger'>".h($_SESSION['error'])."</div>"; unset($_SESSION['error']); }
                        if(isset($_SESSION['msg'])) { echo "<div class='alert alert-success'>".h($_SESSION['msg'])."</div>"; unset($_SESSION['msg']); }
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
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    try {
                                        $db = \App\Core\App::database();
                                        $admins = $db->fetchAll("SELECT * FROM admin");
                                        foreach ($admins as $row) {
                                    ?>
                                    <tr>
                                        <td><?php echo h($row['id']); ?></td>
                                        <td><?php echo h($row['auser']); ?></td>
                                        <td><?php echo h($row['email']); ?></td>
                                        <td>
                                            <a href="adminedit.php?id=<?php echo h($row['id']); ?>" class="btn btn-sm btn-info">Edit</a>
                                            <form method="post" action="delete.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this admin?');">
                                                <?php echo getCsrfField(); ?>
                                                <input type="hidden" name="type" value="admin">
                                                <input type="hidden" name="id" value="<?php echo h($row['id']); ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php
                                        }
                                    } catch (Exception $e) {
                                        echo "<tr><td colspan='4' class='text-center'>Error loading admins: " . h($e->getMessage()) . "</td></tr>";
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
<!-- /Main Content -->

<?php include 'admin_footer.php'; ?>
