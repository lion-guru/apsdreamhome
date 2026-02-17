<?php
/**
 * State Edit Page
 * Edits state information
 */

require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/../includes/log_admin_activity.php';

use App\Core\Database;

$db = \App\Core\App::database();

// Set page variables
$page_title = "Edit State";
$include_datatables = false;
$breadcrumbs = ["State & City" => "stateview.php", "Edit State" => ""];

$error = "";
$msg = "";
$sid = $_GET['sid'] ?? $_GET['id'] ?? 0;

if (isset($_POST['update'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = "<p class='alert alert-danger'>Invalid CSRF token.</p>";
    } else {
        $ustate = trim($_POST['ustate']);
        $sid = intval($_POST['sid']);

        if (!empty($ustate)) {
            if ($db->execute("UPDATE state SET sname = :name WHERE sid = :sid", ['name' => $ustate, 'sid' => $sid])) {
                log_admin_activity('edit_state', 'Edited state ID: ' . $sid . ', new name: ' . $ustate);
                $msg = "State updated successfully";
                header("refresh:2;url=stateview.php?msg=" . urlencode($msg));
            } else {
                $error = "<p class='alert alert-warning'>State Not Updated.</p>";
            }
        } else {
            $error = "<p class='alert alert-warning'>* Please Fill all the Fields</p>";
        }
    }
}

// Fetch current state data
$state_data = null;
if ($sid > 0) {
    $state_data = $db->fetchOne("SELECT * FROM state WHERE sid = :sid", ['sid' => $sid]);
}

if (!$state_data && !isset($_POST['update'])) {
    header("Location: stateview.php?error=" . urlencode("State not found"));
    exit();
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
                    <h3 class="page-title"><?php echo h($page_title); ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="stateview.php">State & City</a></li>
                        <li class="breadcrumb-item active">Edit State</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 offset-lg-2">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h4 class="card-title">Edit State</h4>
                    </div>
                    <div class="card-body">
                        <?php if($error) echo $error; ?>
                        <?php if($msg): ?>
                            <div class="alert alert-success"><?php echo h($msg); ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <?php echo getCsrfField(); ?>
                            <input type="hidden" name="sid" value="<?php echo (int)$sid; ?>">
                            <div class="form-group">
                                <label for="ustate">State Name</label>
                                <input type="text" class="form-control" id="ustate" name="ustate" placeholder="Enter State Name" required value="<?php echo h($state_data['sname'] ?? ''); ?>">
                            </div>
                            <div class="mt-4">
                                <button type="submit" name="update" class="btn btn-primary">Update State</button>
                                <a href="stateview.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
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


