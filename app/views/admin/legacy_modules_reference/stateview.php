<?php
/**
 * State View Page
 * Displays list of states
 */

require_once __DIR__ . '/core/init.php';

use App\Core\Database;

$db = \App\Core\App::database();

// Set page variables
$page_title = "View States";
$include_datatables = true;
$breadcrumbs = ["State & City" => "", "View States" => ""];

// Initialize variables
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';

// Fetch states
$states = $db->fetchAll("SELECT * FROM state ORDER BY sid DESC");

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
                    <h3 class="page-title"><?php echo h($page_title); ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item">State & City</li>
                        <li class="breadcrumb-item active">View States</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">State List</h4>
                        <a href="stateadd.php" class="btn btn-primary float-right">Add New State</a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($msg)): ?>
                            <div class="alert alert-success"><?php echo h($msg); ?></div>
                        <?php endif; ?>
                        
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?php echo h($error); ?></div>
                        <?php endif; ?>
                        
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
                                    <?php if (empty($states)): ?>
                                        <tr>
                                            <td colspan="3" class="text-center">No states found. <a href="stateadd.php">Add a state</a></td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($states as $state): ?>
                                            <tr>
                                                <td><?php echo h($state['sid']); ?></td>
                                                <td><?php echo h($state['sname']); ?></td>
                                                <td>
                                                    <a href="stateedit.php?sid=<?php echo h($state['sid']); ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                    <form method="post" action="delete.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this state? This action cannot be undone.');">
                                                        <?php echo getCsrfField(); ?>
                                                        <input type="hidden" name="type" value="state">
                                                        <input type="hidden" name="id" value="<?php echo h($state['sid']); ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="fas fa-trash-alt"></i> Delete
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
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
<!-- /Page Wrapper -->

<?php
// Include footer
include('admin_footer.php');
?>


