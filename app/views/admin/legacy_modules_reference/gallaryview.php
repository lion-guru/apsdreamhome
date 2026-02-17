<?php
require_once __DIR__ . '/core/init.php';
if (!isAdmin()) {
    header('Location: login.php');
    exit();
}

$page_title = "View Gallery";
$include_datatables = true;
$breadcrumbs = ["Gallery" => ""];

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
                        <li class="breadcrumb-item active">Gallery</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">List Of Gallery Images</h4>
                        <?php if(isset($_GET['msg'])): ?>
                            <div class="alert alert-success mt-2"><?php echo h($_GET['msg']); ?></div>
                        <?php endif; ?>
                        <a href="addimage.php" class="btn btn-primary float-right">Add New</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="datatable table table-stripped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Title</th>
                                        <th>Content</th>
                                        <th>Image</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $db = \App\Core\App::database();
                                    $results = $db->fetchAll("SELECT * FROM images ORDER BY id DESC");
                                    $cnt = 1;
                                    foreach ($results as $row) {
                                    ?>
                                    <tr>
                                        <td><?php echo $cnt++; ?></td>
                                        <td><?php echo h($row['title']); ?></td>
                                        <td><?php echo h(substr($row['content'], 0, 100)) . '...'; ?></td>
                                        <td>
                                            <?php if(!empty($row['image'])): ?>
                                                <img src="upload/<?php echo h($row['image']); ?>" alt="Gallery image" height="50px" width="50px">
                                            <?php else: ?>
                                                <span class="text-muted">No Image</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="gallaryedit.php?id=<?php echo (int)$row['id']; ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form method="post" action="delete.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this image?');">
                                                <?php echo getCsrfField(); ?>
                                                <input type="hidden" name="type" value="gallery">
                                                <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
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
