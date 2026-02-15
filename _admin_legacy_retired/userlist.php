<?php
/**
 * User List Page
 * Displays list of all users
 */

// Start session
session_start();

// Include config and functions
require_once __DIR__ . '/includes/session_manager.php';
require_once __DIR__ . '/../includes/db_config.php';

// Role-based access control: Only superadmin can access user management
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || $_SESSION['admin_role'] !== 'superadmin') {
    header('Location: unauthorized.php');
    exit();
}

// Set page variables
$page_title = "User List";
$include_datatables = true;
$breadcrumbs = ["Users" => ""];

// Start capturing content
ob_start();

// Check if admin is logged in
if(!isset($_SESSION['auser'])) {
    header("location:index.php");
    exit();
}

// Delete now handled by centralized delete.php
?>

<!-- Main Content -->
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">User List</h4>
                <?php 
                if(isset($error)) { echo htmlspecialchars($error); }
                if(isset($msg)) { echo htmlspecialchars($msg); }
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
                                <th>Contact</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = mysqli_query($con, "SELECT * FROM user");
                            while($row = mysqli_fetch_array($query)) {
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['uid']); ?></td>
                                <td><?php echo htmlspecialchars($row['uname']); ?></td>
                                <td><?php echo htmlspecialchars($row['uemail']); ?></td>
                                <td><?php echo htmlspecialchars($row['uphone']); ?></td>
                                <td><?php echo htmlspecialchars($row['urole']); ?></td>
                                <td>
                                    <a href="useredit.php?id=<?php echo htmlspecialchars($row['uid']); ?>" class="btn btn-info">Edit</a>
                                    <a href="delete.php?type=user&id=<?php echo htmlspecialchars($row['uid']); ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
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

// Set the content file variable
$content_file = __FILE__;

// Include the admin wrapper
include("../includes/templates/header.php");
?>
