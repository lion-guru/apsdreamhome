<?php
/**
 * Edit About Content Page
 */

require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/../includes/log_admin_activity.php';

if (!isAdmin()) {
    header('Location: login.php');
    exit();
}

$error = "";
$msg = "";
$aid = intval($_GET['id'] ?? 0);

if ($aid <= 0) {
    header("Location: aboutview.php?error=" . urlencode("Invalid ID"));
    exit();
}

if (isset($_POST['update'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = "<p class='alert alert-danger'>Invalid CSRF token.</p>";
    } else {
        $title = trim($_POST['utitle']);
        $content = trim($_POST['ucontent']);
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
                            "UPDATE about SET title = ?, content = ?, image = ? WHERE id = ?",
                            [$title, $content, $new_image_name, $aid]
                        );
                    } else {
                        throw new Exception("Failed to upload image.");
                    }
                } else {
                    $success = $db->execute(
                        "UPDATE about SET title = ?, content = ? WHERE id = ?",
                        [$title, $content, $aid]
                    );
                }
                
                if ($success) {
                    log_admin_activity('edit_about', 'Edited about section ID: ' . $aid);
                    $msg = "About section updated successfully.";
                    header("Location: aboutview.php?msg=" . urlencode($msg));
                    exit();
                } else {
                    throw new Exception("Update failed.");
                }
            } catch (Exception $e) {
                $error = "<p class='alert alert-warning'>Error: " . h($e->getMessage()) . "</p>";
            }
        } else {
            $error = "<p class='alert alert-warning'>Please fill in all required fields.</p>";
        }
    }
}

// Fetch current about data
$about_data = null;
try {
    $db = \App\Core\App::database();
    $about_data = $db->fetchOne("SELECT * FROM about WHERE id = ?", [$aid]);
} catch (Exception $e) {
    // Error handled by $about_data being null
}

if (!$about_data) {
    header("Location: aboutview.php?error=" . urlencode("About section not found"));
    exit();
}

// Set page variables
$page_title = "Edit About Content";
$include_datatables = false;
$breadcrumbs = ["About" => "aboutview.php", "Edit About" => ""];

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
                        <li class="breadcrumb-item active">Edit About Content</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h4 class="card-title">Edit About Us Content</h4>
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
                                            <input type="text" class="form-control" name="utitle" required value="<?php echo h($about_data['title']); ?>">
                                            <div class="invalid-feedback">Please enter a title.</div>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-2 col-form-label">Content</label>
                                        <div class="col-lg-9">
                                            <textarea class="form-control" name="ucontent" rows="10" required><?php echo h($about_data['content']); ?></textarea>
                                            <div class="invalid-feedback">Please enter content.</div>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-2 col-form-label">Image</label>
                                        <div class="col-lg-9">
                                            <input class="form-control" name="aimage" type="file">
                                            <small class="form-text text-muted">Leave blank to keep current image.</small>
                                            <?php if(!empty($about_data['image'])): ?>
                                                <div class="mt-2">
                                                    <img src="upload/<?php echo h($about_data['image']); ?>" alt="Current image" class="img-thumbnail" style="max-width: 150px;">
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-left mt-4">
                                <button type="submit" class="btn btn-primary" name="update" style="margin-left:200px;">
                                    <i class="fas fa-save me-2"></i> Update
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


