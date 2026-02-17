<?php
require_once __DIR__ . '/core/init.php';
if (!isAdmin()) {
    header('Location: login.php');
    exit();
}

$error = "";
$msg = "";
$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: gallaryview.php');
    exit();
}

if (isset($_POST['update'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = "<p class='alert alert-danger'>Invalid CSRF token.</p>";
    } else {
        $db = \App\Core\App::database();
        $title = trim($_POST['utitle']);
        $content = trim($_POST['ucontent']);
        $type = trim($_POST['utype']);
        $aimage = $_FILES['aimage']['name'];
        $temp_name1 = $_FILES['aimage']['tmp_name'];

        if (!empty($aimage)) {
            $target_dir = "upload/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            
            $file_ext = pathinfo($aimage, PATHINFO_EXTENSION);
            $new_image_name = "gallery_" . time() . "_" . \App\Helpers\SecurityHelper::generateRandomString(16, false) . "." . $file_ext;
            
            if (move_uploaded_file($temp_name1, $target_dir . $new_image_name)) {
                $sql = "UPDATE images SET title = ?, content = ?, image = ?, type = ? WHERE id = ?";
                if ($db->execute($sql, [$title, $content, $new_image_name, $type, $id])) {
                    log_admin_activity('edit_gallery', 'Updated gallery image ID: ' . $id);
                    $msg = "Gallery updated successfully.";
                    header("Location: gallaryview.php?msg=" . urlencode($msg));
                    exit();
                } else {
                    $error = "<p class='alert alert-danger'>Error updating gallery.</p>";
                }
            } else {
                $error = "<p class='alert alert-danger'>Failed to upload new image.</p>";
            }
        } else {
            $sql = "UPDATE images SET title = ?, content = ?, type = ? WHERE id = ?";
            if ($db->execute($sql, [$title, $content, $type, $id])) {
                log_admin_activity('edit_gallery', 'Updated gallery image ID: ' . $id);
                $msg = "Gallery updated successfully.";
                header("Location: gallaryview.php?msg=" . urlencode($msg));
                exit();
            } else {
                $error = "<p class='alert alert-danger'>Error updating gallery.</p>";
            }
        }
    }
}

// Fetch current data
$db = \App\Core\App::database();
$row = $db->fetch("SELECT * FROM images WHERE id = :id", ['id' => $id]);

if (!$row) {
    header('Location: gallaryview.php');
    exit();
}

$page_title = "Edit Gallery Image";
$include_datatables = false;
$breadcrumbs = ["Gallery" => "gallaryview.php", "Edit Image" => ""];

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
                        <li class="breadcrumb-item"><a href="gallaryview.php">Gallery</a></li>
                        <li class="breadcrumb-item active">Edit Image</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Edit Gallery Image</h4>
                    </div>
                    <div class="card-body">
                        <?php if($error) echo $error; ?>
                        
                        <form method="post" enctype="multipart/form-data">
                            <?php echo getCsrfField(); ?>
                            <div class="row">
                                <div class="col-xl-12">
                                    <div class="form-group row">
                                        <label class="col-lg-2 col-form-label">Title</label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" name="utitle" value="<?php echo h($row['title']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-2 col-form-label">Type/Category</label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" name="utype" value="<?php echo h($row['type']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-2 col-form-label">Content</label>
                                        <div class="col-lg-9">
                                            <textarea class="form-control" name="ucontent" rows="5" required><?php echo h($row['content']); ?></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-2 col-form-label">Current Image</label>
                                        <div class="col-lg-9">
                                            <?php if(!empty($row['image'])): ?>
                                                <img src="upload/<?php echo h($row['image']); ?>" alt="Current image" height="100px">
                                            <?php else: ?>
                                                <span class="text-muted">No Image</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-2 col-form-label">Change Image</label>
                                        <div class="col-lg-9">
                                            <input class="form-control" name="aimage" type="file">
                                            <small class="text-muted">Leave blank to keep current image. Allowed formats: JPG, PNG, GIF.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-left">
                                <button type="submit" class="btn btn-primary" name="update">Update</button>
                                <a href="gallaryview.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('admin_footer.php'); ?>

