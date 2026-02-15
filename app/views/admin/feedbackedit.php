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
    header('Location: feedbackview.php');
    exit();
}

if (isset($_POST['update'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = "<p class='alert alert-danger'>Invalid CSRF token.</p>";
    } else {
        $db = \App\Core\App::database();
        $status = $_POST['status'];

        if ($db->execute("UPDATE feedback SET status = :status WHERE fid = :id", [
            'status' => $status,
            'id' => $id
        ])) {
            $msg = "Feedback status updated successfully.";
            header("Location: feedbackview.php?msg=" . urlencode($msg));
            exit();
        } else {
            $error = "<p class='alert alert-danger'>Error updating feedback status.</p>";
        }
    }
}

// Fetch data
$db = \App\Core\App::database();
$row = $db->fetch("SELECT * FROM feedback WHERE fid = :id", ['id' => $id]);

if (!$row) {
    header('Location: feedbackview.php');
    exit();
}

$page_title = "Edit Feedback Status";
$include_datatables = false;
$breadcrumbs = ["Feedback" => "feedbackview.php", "Edit Status" => ""];

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
                        <li class="breadcrumb-item"><a href="feedbackview.php">Feedback</a></li>
                        <li class="breadcrumb-item active">Edit Status</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Update Feedback Visibility</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error) echo $error; ?>

                        <div class="row">
                            <div class="col-xl-12">
                                <div class="form-group row">
                                    <label class="col-lg-2 col-form-label">User Name</label>
                                    <div class="col-lg-9">
                                        <input type="text" class="form-control" value="<?php echo h($row['fusername']); ?>" readonly>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-lg-2 col-form-label">Feedback</label>
                                    <div class="col-lg-9">
                                        <textarea class="form-control" rows="5" readonly><?php echo h($row['fdescription']); ?></textarea>
                                    </div>
                                </div>

                                <form method="post">
                                    <?php echo getCsrfField(); ?>
                                    <div class="form-group row">
                                        <label class="col-lg-2 col-form-label">Status</label>
                                        <div class="col-lg-9">
                                            <select class="form-control" name="status" required>
                                                <option value="0" <?php echo ($row['status'] == 0) ? 'selected' : ''; ?>>Pending (Hidden)</option>
                                                <option value="1" <?php echo ($row['status'] == 1) ? 'selected' : ''; ?>>Testimonial (Visible)</option>
                                            </select>
                                            <small class="text-muted">Setting status to 'Testimonial' will display this feedback on the public website.</small>
                                        </div>
                                    </div>
                                    <div class="text-left">
                                        <button type="submit" class="btn btn-primary" name="update">Update Status</button>
                                        <a href="feedbackview.php" class="btn btn-secondary">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('admin_footer.php'); ?>