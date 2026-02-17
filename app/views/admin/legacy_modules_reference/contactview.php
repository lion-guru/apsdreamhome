<?php
require_once __DIR__ . '/core/init.php';

if (!isAdmin()) {
    header('Location: login.php');
    exit();
}

$page_title = "Contact Messages";
$include_datatables = true;
$breadcrumbs = ["Contact" => ""];

include('admin_header.php');
include('admin_sidebar.php');
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <h3 class="page-title"><?php echo $page_title; ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Contact Messages</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Contact Message List</h4>
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
                                        <th>Phone</th>
                                        <th>Subject</th>
                                        <th>Message</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $db = \App\Core\App::database();
                                    $results = $db->fetchAll("SELECT * FROM contact ORDER BY cid DESC");
                                    $cnt = 1;
                                    foreach ($results as $row) {
                                    ?>
                                    <tr>
                                        <td><?php echo $cnt++; ?></td>
                                        <td><?php echo h($row['name']); ?></td>
                                        <td><?php echo h($row['email']); ?></td>
                                        <td><?php echo h($row['phone']); ?></td>
                                        <td><?php echo h($row['subject']); ?></td>
                                        <td><?php echo h($row['message']); ?></td>
                                        <td><?php echo isset($row['date']) ? h($row['date']) : 'N/A'; ?></td>
                                        <td>
                                            <form method="post" action="delete.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this message?');">
                                                <?php echo getCsrfField(); ?>
                                                <input type="hidden" name="type" value="contact">
                                                <input type="hidden" name="id" value="<?php echo h($row['cid']); ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
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

<?php include('admin_footer.php'); ?>
