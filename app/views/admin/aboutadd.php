<?php
/**
 * Add About Content Page
 */

require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/../includes/log_admin_activity.php';

if (!isAdmin()) {
    header('Location: login.php');
    exit();
}

$error = "";
$msg = "";

if (isset($_POST['addabout'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = "<p class='alert alert-danger'>Invalid CSRF token.</p>";
    } else {
        $title = trim($_POST['title']);
        $content = trim($_POST['content']);
        $aimage = $_FILES['aimage']['name'];
        $temp_name1 = $_FILES['aimage']['tmp_name'];

        if (!empty($title) && !empty($content)) {
            try {
                $db = \App\Core\App::database();
                $success = false;
                
                if (!empty($aimage)) {
                    $target_dir = "upload/";
                    if (!is_dir($target_dir)) {
                        mkdir($target_dir, 0755, true);
                    }
                    
                    $file_ext = pathinfo($aimage, PATHINFO_EXTENSION);
                    $new_image_name = "about_" . time() . "." . $file_ext;
                    
                    if (move_uploaded_file($temp_name1, $target_dir . $new_image_name)) {
                        $success = $db->execute(
                            "INSERT INTO about (title, content, image) VALUES (?, ?, ?)",
                            [$title, $content, $new_image_name]
                        );
                    } else {
                        throw new Exception("Failed to upload image.");
                    }
                } else {
                    $success = $db->execute(
                        "INSERT INTO about (title, content) VALUES (?, ?)",
                        [$title, $content]
                    );
                }
                
                if ($success) {
                    $new_id = $db->getConnection()->insert_id;
                    log_admin_activity('add_about', 'Added new about section ID: ' . $new_id);
                    $msg = "About section added successfully.";
                    header("Location: aboutview.php?msg=" . urlencode($msg));
                    exit();
                } else {
                    throw new Exception("Insert failed.");
                }
            } catch (Exception $e) {
                $error = "<p class='alert alert-warning'>Error: " . h($e->getMessage()) . "</p>";
            }
        } else {
            $error = "<p class='alert alert-warning'>Please fill in all required fields.</p>";
        }
    }
}

// Set page variables
$page_title = "Add About Content";
$include_datatables = false;
$breadcrumbs = ["About" => "aboutview.php", "Add About" => ""];

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
                    <h3 class="page-title"><?php echo $page_title; ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="aboutview.php">About</a></li>
                        <li class="breadcrumb-item active">Add About Content</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h4 class="card-title">Add About Us Content</h4>
                    </div>
                    <div class="card-body">
                        <?php if($error) echo $error; ?>
                        
                        <form method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                            <?php echo getCsrfField(); ?>
                            <div class="row">
                                <div class="col-xl-12">
                                    <div class="form-group row">
                                        <label class="col-lg-2 col-form-label">Title</label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" name="title" required placeholder="Enter Title">
                                            <div class="invalid-feedback">Please enter a title.</div>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-2 col-form-label">Content</label>
                                        <div class="col-lg-9">
                                            <textarea class="form-control" name="content" rows="10" required placeholder="Enter Content"></textarea>
                                            <div class="invalid-feedback">Please enter content.</div>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-2 col-form-label">Image</label>
                                        <div class="col-lg-9">
                                            <input class="form-control" name="aimage" type="file">
                                            <small class="form-text text-muted">Optional. Recommended size: 800x600px.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-left mt-4">
                                <button type="submit" class="btn btn-primary" name="addabout" style="margin-left:200px;">
                                    <i class="fas fa-plus me-2"></i> Submit
                                </button>
                                <a href="aboutview.php" class="btn btn-secondary ms-2">Cancel</a>
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


