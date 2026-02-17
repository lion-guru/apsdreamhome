<?php
/**
 * State Add Page
 * Adds states and displays list
 */

require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/../includes/log_admin_activity.php';

// Set page variables
$page_title = "Add State";
$include_datatables = true;
$breadcrumbs = ["State & City" => "", "Add State" => ""];

$error = "";
$msg = "";

if (isset($_POST['insert'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = "<p class='alert alert-danger'>Invalid CSRF token.</p>";
    } else {
        $state = trim($_POST['state']);
        
        if (!empty($state)) {
            try {
                $db = \App\Core\App::database();
                if ($db->execute("INSERT INTO state (sname) VALUES (:state)", ['state' => $state])) {
                    log_admin_activity('add_state', 'Added state: ' . $state);
                    $msg = "<p class='alert alert-success'>State Inserted Successfully</p>";
                } else {
                    $error = "<p class='alert alert-warning'>* State Not Inserted</p>";
                }
            } catch (Exception $e) {
                $error = "<p class='alert alert-warning'>* State Not Inserted: " . h($e->getMessage()) . "</p>";
            }
        } else {
            $error = "<p class='alert alert-warning'>* Fill all the Fields</p>";
        }
    }
}

// Include header
include('admin_header.php');
// Include sidebar
include('admin_sidebar.php');
?>

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <h3 class="page-title"><?php echo $page_title; ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item">State & City</li>
                        <li class="breadcrumb-item active">Add State</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h4 class="card-title">Add State</h4>
                    </div>
                    <div class="card-body">
                        <?php if($error) echo $error; ?>
                        <?php if($msg) echo $msg; ?>
                        
                        <form method="POST" action="">
                            <?php echo getCsrfField(); ?>
                            <div class="form-group">
                                <label for="state">State Name</label>
                                <input type="text" class="form-control" id="state" name="state" placeholder="Enter State Name" required>
                            </div>
                            <div class="mt-4">
                                <button type="submit" name="insert" class="btn btn-primary">Add State</button>
                                <a href="stateview.php" class="btn btn-secondary">View States</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- State List Preview -->
        <div class="row mt-4">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Recently Added States</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="datatable table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>State Name</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $db = \App\Core\App::database();
                                    $states = $db->fetchAll("SELECT * FROM state ORDER BY sid DESC LIMIT 10");
                                    foreach ($states as $row) {
                                    ?>
                                        <tr>
                                            <td><?php echo h($row['sid']); ?></td>
                                            <td><?php echo h($row['sname']); ?></td>
                                            <td>
                                                <a href="stateedit.php?sid=<?php echo h($row['sid']); ?>" class="btn btn-sm btn-primary">Edit</a>
                                                <form method="post" action="delete.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this state?');">
                                                    <?php echo getCsrfField(); ?>
                                                    <input type="hidden" name="type" value="state">
                                                    <input type="hidden" name="id" value="<?php echo h($row['sid']); ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php 
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
<!-- /Page Wrapper -->

<?php
// Include footer
include('admin_footer.php');
?>


