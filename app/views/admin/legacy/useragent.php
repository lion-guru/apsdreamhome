<?php
require_once __DIR__ . '/core/init.php';

use App\Core\Database;

$db = \App\Core\App::database();
$page_title = "Agents";
$include_datatables = true;
$breadcrumbs = ["Agents" => ""];

include('admin_header.php');
include('admin_sidebar.php');
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <h3 class="page-title"><?php echo h($page_title); ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Agent List</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Agent List</h4>
                        <?php if(isset($_GET['msg'])): ?>
                            <div class="alert alert-success mt-2"><?php echo h($_GET['msg']); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="datatable table table-stripped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Contact</th>
                                        <th>Utype</th>
                                        <th>Image</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $agents = $db->fetchAll("SELECT * FROM user WHERE utype='agent' ORDER BY uid DESC");
                                    $cnt = 1;
                                    foreach ($agents as $row):
                                    ?>
                                    <tr>
                                        <td><?php echo $cnt++; ?></td>
                                        <td><?php echo h($row['uname']); ?></td>
                                        <td><?php echo h($row['uemail']); ?></td>
                                        <td><?php echo h($row['uphone']); ?></td>
                                        <td><?php echo h($row['utype']); ?></td>
                                        <td>
                                            <?php if(!empty($row['uimage'])): ?>
                                                <img src="user/<?php echo h($row['uimage']); ?>" height="50px" width="50px" class="rounded-circle">
                                            <?php else: ?>
                                                <span class="text-muted">No Image</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <form method="post" action="delete.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this agent?');">
                                                <?php echo getCsrfField(); ?>
                                                <input type="hidden" name="type" value="useragent">
                                                <input type="hidden" name="id" value="<?php echo h($row['uid']); ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
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

<?php include('admin_footer.php'); ?>


