<?php
/**
 * City View Page
 * Displays list of cities with state information
 */

require_once __DIR__ . '/core/init.php';

use App\Core\Database;

$db = \App\Core\App::database();

// Set page variables
$page_title = "View Cities";
$include_datatables = true;
$breadcrumbs = ["State & City" => "", "View Cities" => ""];

// Fetch cities with state information
$query = "SELECT city.*, state.sname as state_name 
          FROM city 
          JOIN state ON city.sid = state.sid 
          ORDER BY city.cid DESC";

$cities = $db->fetchAll($query);

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
                        <li class="breadcrumb-item active">View Cities</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">City List</h4>
                        <a href="cityadd.php" class="btn btn-primary float-right">Add New City</a>
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
                                    <?php if (empty($cities)): ?>
                                        <tr><td colspan="4" class="text-center">No cities found. <a href="cityadd.php">Add a city</a></td></tr>
                                    <?php else: ?>
                                        <?php foreach ($cities as $city): ?>
                                            <tr>
                                                <td><?php echo h($city['cid']); ?></td>
                                                <td><?php echo h($city['cname']); ?></td>
                                                <td><?php echo h($city['state_name']); ?></td>
                                                <td>
                                                    <a href="cityedit.php?cid=<?php echo h($city['cid']); ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                    <form method="post" action="delete.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this city?');">
                                                        <?php echo getCsrfField(); ?>
                                                        <input type="hidden" name="type" value="city">
                                                        <input type="hidden" name="id" value="<?php echo h($city['cid']); ?>">
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


