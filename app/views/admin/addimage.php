<?php
require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/../includes/log_admin_activity.php';

$error = "";
$msg = "";

if (isset($_POST['addimage'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = "<p class='alert alert-danger'>Invalid CSRF token.</p>";
    } else {
        $db = \App\Core\App::database();
        $title = trim($_POST['title']);
        $content = trim($_POST['content']);
        $type = trim($_POST['type']);
        $aimage = $_FILES['aimage']['name'];
        $temp_name1 = $_FILES['aimage']['tmp_name'];

        if (!empty($title) && !empty($content) && !empty($aimage)) {
            $target_dir = "upload/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            
            $file_ext = pathinfo($aimage, PATHINFO_EXTENSION);
            $new_image_name = "gallery_" . time() . "_" . \App\Helpers\SecurityHelper::generateRandomString(16, false) . "." . $file_ext;
            
            if (move_uploaded_file($temp_name1, $target_dir . $new_image_name)) {
                $sql = "INSERT INTO images (title, content, image, type) VALUES (?, ?, ?, ?)";
                if ($db->execute($sql, [$title, $content, $new_image_name, $type])) {
                    $new_id = $db->getLastInsertId();
                    log_admin_activity('add_image', 'Added new gallery image ID: ' . $new_id);
                    $msg = "Image inserted successfully.";
                    header("Location: gallaryview.php?msg=" . urlencode($msg));
                    exit();
                } else {
                    $error = "<p class='alert alert-danger'>Error inserting image.</p>";
                }
            } else {
                $error = "<p class='alert alert-danger'>Failed to upload image.</p>";
            }
        } else {
            $error = "<p class='alert alert-warning'>Please fill in all required fields and select an image.</p>";
        }
    }
}

$page_title = "Add Gallery Image";
$include_datatables = false;
$breadcrumbs = ["Gallery" => "gallaryview.php", "Add Image" => ""];

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
                        <li class="breadcrumb-item active">Add Image</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Add Gallery Image</h4>
                    </div>
                    <div class="card-body">
                        <?php if($error) echo $error; ?>
                        <?php if($msg) echo $msg; ?>
                        
                        <form method="post" enctype="multipart/form-data">
                            <?php echo getCsrfField(); ?>
                            <div class="row">
                                <div class="col-xl-12">
                                    <div class="form-group row">
                                        <label class="col-lg-2 col-form-label">Title</label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" name="title" required placeholder="Enter image title">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-2 col-form-label">Type/Category</label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" name="type" required placeholder="Enter category (e.g. Interior, Exterior)">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-2 col-form-label">Content</label>
                                        <div class="col-lg-9">
                                            <textarea class="form-control" name="content" rows="5" required placeholder="Enter description"></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-2 col-form-label">Image</label>
                                        <div class="col-lg-9">
                                            <input class="form-control" name="aimage" type="file" required>
                                            <small class="text-muted">Allowed formats: JPG, PNG, GIF. Max size 2MB.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-left">
                                <button type="submit" class="btn btn-primary" name="addimage">Submit</button>
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

