<?php
/**
 * City Edit Page
 * Edits city information
 */

require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/../includes/log_admin_activity.php';

use App\Core\Database;

$db = \App\Core\App::database();

// Set page variables
$page_title = "Edit City";
$include_datatables = false;
$breadcrumbs = ["State & City" => "cityview.php", "Edit City" => ""];

$error = "";
$msg = "";
$cid = $_GET['cid'] ?? $_GET['id'] ?? 0;

// Process update
if (isset($_POST['update'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = "<p class='alert alert-danger'>Invalid CSRF token.</p>";
    } else {
        $ucity = trim($_POST['ucity']);
        $sid = intval($_POST['sid']);
        $cid = intval($_POST['cid']);

        if (!empty($ucity) && $sid > 0) {
            try {
                if ($db->execute("UPDATE city SET cname = :city, sid = :sid WHERE cid = :cid", [
                    'city' => $ucity,
                    'sid' => $sid,
                    'cid' => $cid
                ])) {
                    log_admin_activity('edit_city', 'Edited city ID: ' . $cid . ', new name: ' . $ucity . ', state ID: ' . $sid);
                    $msg = "City updated successfully";
                    header("refresh:2;url=cityview.php?msg=" . urlencode($msg));
                } else {
                    $error = "<p class='alert alert-danger'>Error: City update failed</p>";
                }
            } catch (Exception $e) {
                $error = "<p class='alert alert-danger'>Error: " . h($e->getMessage()) . "</p>";
            }
        } else {
            $error = "<p class='alert alert-warning'>* Please Fill all the Fields</p>";
        }
    }
}

// Fetch current city data
$city_data = null;
if ($cid > 0) {
    $city_data = $db->fetchOne("SELECT * FROM city WHERE cid = :cid", ['cid' => $cid]);
}

if (!$city_data && !isset($_POST['update'])) {
    header("Location: cityview.php?error=" . urlencode("City not found"));
    exit();
}

// Fetch states for dropdown
$states = $db->fetchAll("SELECT * FROM state ORDER BY sname");

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
                        <li class="breadcrumb-item"><a href="cityview.php">State & City</a></li>
                        <li class="breadcrumb-item active">Edit City</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 offset-lg-2">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h4 class="card-title">Edit City</h4>
                    </div>
                    <div class="card-body">
                        <?php if($error) echo $error; ?>
                        <?php if($msg): ?>
                            <div class="alert alert-success"><?php echo h($msg); ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <?php echo getCsrfField(); ?>
                            <input type="hidden" name="cid" value="<?php echo (int)$cid; ?>">
                            
                            <div class="form-group">
                                <label for="sid">State</label>
                                <select class="form-control" id="sid" name="sid" required>
                                    <option value="">Select State</option>
                                    <?php foreach($states as $state): ?>
                                        <option value="<?php echo h($state['sid']); ?>" <?php echo ($state['sid'] == ($city_data['sid'] ?? '')) ? 'selected' : ''; ?>>
                                            <?php echo h($state['sname']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="ucity">City Name</label>
                                <input type="text" class="form-control" id="ucity" name="ucity" placeholder="Enter City Name" required value="<?php echo h($city_data['cname'] ?? ''); ?>">
                            </div>

                            <div class="mt-4">
                                <button type="submit" name="update" class="btn btn-primary">Update City</button>
                                <a href="cityview.php" class="btn btn-secondary">Cancel</a>
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


