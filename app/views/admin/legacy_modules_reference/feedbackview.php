<?php
require_once __DIR__ . '/core/init.php';
if (!isAdmin()) {
    header('Location: login.php');
    exit();
}

$page_title = "User Feedback";
$include_datatables = true;
$breadcrumbs = ["Feedback" => ""];

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
                        <li class="breadcrumb-item active">Feedback</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Feedback List</h4>
                        <p class="text-muted small">Select feedbacks to display as testimonials (Status '1' = Visible on site).</p>
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
                                        <th>Feedback</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $db = \App\Core\App::database();
                                    $results = $db->fetchAll("SELECT * FROM feedback ORDER BY fid DESC");
                                    $cnt = 1;
                                    foreach ($results as $row) {
                                    ?>
                                    <tr>
                                        <td><?php echo $cnt++; ?></td>
                                        <td><?php echo h($row['fusername']); ?></td>
                                        <td><?php echo h($row['femail']); ?></td>
                                        <td><?php echo h($row['fdescription']); ?></td>
                                        <td>
                                            <?php if($row['status'] == 1): ?>
                                                <span class="badge badge-success">Testimonial</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Pending</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="feedbackedit.php?id=<?php echo (int)$row['fid']; ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-edit"></i> Edit Status
                                            </a>
                                            <form method="post" action="delete.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this feedback?');">
                                                <?php echo getCsrfField(); ?>
                                                <input type="hidden" name="type" value="feedback">
                                                <input type="hidden" name="id" value="<?php echo (int)$row['fid']; ?>">
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
