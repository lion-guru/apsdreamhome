<?php

/**
 * About View Page
 * Displays about page content from database
 */

// Set page variables
$page_title = "About Content";
$include_datatables = true;
$breadcrumbs = ["About" => ""];

// Start capturing content
ob_start();

// Include config and functions
require_once(__DIR__ . '/../app/bootstrap.php');
$db = \App\Core\App::database();
require_once("admin-functions.php");

// Check if admin is logged in
if (!isset($_SESSION['auser'])) {
    header("location:index.php");
    exit();
}

// Process delete operation
// Delete now handled by centralized delete.php
?>

<?php include("../includes/templates/dynamic_header.php"); ?>
<!-- Main Content -->
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">About List</h4>
                <?php
                if (isset($error)) {
                    echo $error;
                }
                if (isset($msg)) {
                    echo $msg;
                }
                ?>
                <a href="aboutadd.php" class="btn btn-primary float-right">Add New</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="datatable table table-stripped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Content</th>
                                <th>Image</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $about_items = $db->fetch("SELECT * FROM about");
                            foreach ($about_items as $row) {
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($row['content'], 0, 100)) . '...'; ?></td>
                                    <td>
                                        <img src="../upload/<?php echo htmlspecialchars($row['image']); ?>" alt="about image" height="50px" width="50px">
                                    </td>
                                    <td>
                                        <a href="aboutedit.php?id=<?php echo htmlspecialchars($row['id']); ?>" class="btn btn-info">Edit</a>
                                        <a href="delete.php?type=about&id=<?php echo htmlspecialchars($row['id']); ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this item?');">Delete</a>
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
<?php include("../includes/templates/new_footer.php"); ?>