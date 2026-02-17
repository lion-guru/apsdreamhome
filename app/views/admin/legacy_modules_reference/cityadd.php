<?php
/**
 * City Add Page
 * Adds cities with state information
 */

require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/../includes/log_admin_activity.php';

use App\Core\Database;

$db = \App\Core\App::database();

// Set page variables
$page_title = "Add City";
$include_datatables = true;
$breadcrumbs = ["State & City" => "", "Add City" => ""];

$error = "";
$msg = "";

if (isset($_POST['insert'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = "<p class='alert alert-danger'>Invalid CSRF token.</p>";
    } else {
        $state = intval($_POST['state']);
        $city = trim($_POST['city']);
        
        if (!empty($state) && !empty($city)) {
            if ($db->execute("INSERT INTO city (cname, sid) VALUES (:city, :state)", ['city' => $city, 'state' => $state])) {
                log_admin_activity('add_city', 'Added city: ' . $city . ', state ID: ' . $state);
                $msg = "<p class='alert alert-success'>City Inserted Successfully</p>";
            } else {
                $error = "<p class='alert alert-warning'>* City Not Inserted.</p>";
            }
        } else {
            $error = "<p class='alert alert-warning'>* Please fill all fields</p>";
        }
    }
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
                        <li class="breadcrumb-item">State & City</li>
                        <li class="breadcrumb-item active">Add City</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h4 class="card-title">Add City</h4>
                    </div>
                    <div class="card-body">
                        <?php if($error) echo $error; ?>
                        <?php if($msg) echo $msg; ?>
                        
                        <form method="POST" action="">
                            <?php echo getCsrfField(); ?>
                            <div class="form-group">
                                <label for="state">Select State</label>
                                <select name="state" id="state" class="form-control" required>
                                    <option value="">-- Select State --</option>
                                    <?php foreach($states as $state): ?>
                                        <option value="<?php echo h($state['sid']); ?>">
                                            <?php echo h($state['sname']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="city">City Name</label>
                                <input type="text" name="city" id="city" class="form-control" placeholder="Enter City Name" required>
                            </div>
                            <div class="mt-4">
                                <button type="submit" name="insert" class="btn btn-primary">Add City</button>
                                <a href="cityview.php" class="btn btn-secondary">View Cities</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- City List Preview -->
        <div class="row mt-4">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Recently Added Cities</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="datatable table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>City Name</th>
                                        <th>State</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $recent_cities = $db->fetchAll("SELECT city.*, state.sname FROM city JOIN state ON city.sid = state.sid ORDER BY city.cid DESC LIMIT 10");
                                    foreach($recent_cities as $row):
                                    ?>
                                        <tr>
                                            <td><?php echo h($row['cid']); ?></td>
                                            <td><?php echo h($row['cname']); ?></td>
                                            <td><?php echo h($row['sname']); ?></td>
                                            <td>
                                                <a href="cityedit.php?cid=<?php echo h($row['cid']); ?>" class="btn btn-sm btn-primary">Edit</a>
                                                <form method="post" action="delete.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this city?');">
                                                    <?php echo getCsrfField(); ?>
                                                    <input type="hidden" name="type" value="city">
                                                    <input type="hidden" name="id" value="<?php echo h($row['cid']); ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                </form>
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
<!-- /Page Wrapper -->

<?php
// Include footer
include('admin_footer.php');
?>


