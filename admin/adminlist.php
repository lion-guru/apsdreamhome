<?php
/**
 * Admin List Page
 * Displays list of all administrators
 */

// Unified Session Manager and Helper
require_once(__DIR__ . '/includes/session_manager.php');
require_once(__DIR__ . '/includes/superadmin_helpers.php');
initAdminSession();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}
if (!isSuperAdmin()) {
    header('Location: index.php');
    exit();
}

// Set page variables
$page_title = "Admin List";
$include_datatables = true;
$breadcrumbs = ["Users" => "userlist.php", "Admin" => ""];

// Start capturing content
ob_start();

// Include config and functions
require("config.php");
require_once("admin-functions.php");

// Check if admin is logged in
if(!isset($_SESSION['auser'])) {
    header("location:index.php");
    exit();
}

// Process delete operation
if(isset($_GET['delid'])) {
    $id = $_GET['delid'];
    $sql = "DELETE FROM admin WHERE aid = $id";
    $result = mysqli_query($con, $sql);
    require_once __DIR__ . '/../includes/log_admin_action_db.php';
    if($result) {
        $msg = "<p class='alert alert-success'>Admin Deleted Successfully!</p>";
        log_admin_action_db('delete_admin', "Deleted admin with ID: $id (by Super Admin)");
    } else {
        $error = "<p class='alert alert-warning'>Admin Not Deleted!</p>";
        log_admin_action_db('delete_admin_failed', "Failed to delete admin with ID: $id (by Super Admin)");
    }
}
?>

<!-- Main Content -->
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Admin List</h4>
                <?php 
                if(isset($error)) { echo $error; }
                if(isset($msg)) { echo $msg; }
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
                            $query = mysqli_query($con, "SELECT * FROM admin");
                            while($row = mysqli_fetch_array($query)) {
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['aid']); ?></td>
                                <td><?php echo htmlspecialchars($row['auser']); ?></td>
                                <td><?php echo htmlspecialchars($row['aemail']); ?></td>
                                <td>
                                    <a href="adminedit.php?id=<?php echo htmlspecialchars($row['aid']); ?>" class="btn btn-info">Edit</a>
                                    <a href="adminlist.php?delid=<?php echo htmlspecialchars($row['aid']); ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this admin?');">Delete</a>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /Main Content -->

<?php
// Get the buffered content
$content = ob_get_clean();
?>

<html>
<body>
<?php include __DIR__ . '/../includes/templates/dynamic_header.php'; ?>
<?php echo $content; ?>
<?php include __DIR__ . '/../includes/templates/new_footer.php'; ?>
</body>
</html>
