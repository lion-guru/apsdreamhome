<?php
session_start();
require("config.php");

// Secure authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>APS Dream Homes | Job Applicant</title>
    <link rel="shortcut icon" type="image/x-icon" href="assets/<?php echo get_asset_url('favicon.png', 'images'); ?>">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/bootstrap.min.css', 'css'); ?>">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/font-awesome.min.css', 'css'); ?>">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/feathericon.min.css', 'css'); ?>">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/style.css', 'css'); ?>">
    <!--[if lt IE 9]>
        <script src="<?php echo get_asset_url('js/html5shiv.min.js', 'js'); ?>"></script>
        <script src="<?php echo get_asset_url('js/respond.min.js', 'js'); ?>"></script>
    <![endif]-->
</head>
<body>
    <?php include __DIR__ . '/../includes/templates/dynamic_header.php'; ?>
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col">
                        <h3 class="page-title">View Job Applicant</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">View Applicant</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">List Of Applicant</h4>
                            <?php if (isset($_GET['msg'])) echo htmlspecialchars($_GET['msg']); ?>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Phone</th>
                                            <th>Email</th>
                                            <th>Resume</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                            <th>Upload Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $query = mysqli_query($con, "SELECT * FROM career_applications");
                                        $cnt = 1;
                                        while ($row = mysqli_fetch_assoc($query)) {
                                        ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($cnt); ?></td>
                                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                                <td>
                                                    <?php
                                                    $resumePath = 'C:/xampp/htdocs/aps_dream_homes/admin/upload/job_application_resum' . htmlspecialchars($row['resume']);
                                                    ?>
                                                    <a href="<?php echo htmlspecialchars($resumePath); ?>" class="view-resume" target="_blank">
                                                        <button class="btn btn-danger">View Resume</button>
                                                    </a>
                                                    <a href="<?php echo htmlspecialchars($resumePath); ?>" download>
                                                        <button class="btn btn-success">Download Resume</button>
                                                    </a>
                                                </td>
                                                <td>
                                                    <select name="status" class="status-select" data-applicant-id="<?php echo htmlspecialchars($row['id']); ?>">
                                                        <option value="accepted" <?php echo ($row['status'] == 'accepted') ? 'selected' : ''; ?>>Accepted</option>
                                                        <option value="rejected" <?php echo ($row['status'] == 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <button class="btn btn-danger">Delete</button>
                                                </td>
                                                <td><?php echo (!empty($row['upload_date'])) ? htmlspecialchars(date('Y-m-d H:i:s', strtotime($row['upload_date']))) : 'N/A'; ?></td>
                                            </tr>
                                        <?php
                                            $cnt++;
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
    <?php include __DIR__ . '/../includes/templates/new_footer.php'; ?>
</body>
</html>

<script src="<?php echo get_asset_url('js/jquery-3.2.1.min.js', 'js'); ?>"></script>
<script src="<?php echo get_asset_url('js/popper.min.js', 'js'); ?>"></script>
<script src="<?php echo get_asset_url('js/bootstrap.min.js', 'js'); ?>"></script>
<script src="assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script src="<?php echo get_asset_url('js/script.js', 'js'); ?>"></script>

<script>
$(document).ready(function() {
    $('select[name="status"]').on('change', function() {
        var status = $(this).val();
        var applicantId = $(this).data('applicant-id');
        $.ajax({
            type: 'POST',
            url: 'update_status.php',
            data: {status: status, applicantId: applicantId},
            success: function(response) {
                alert('Status updated successfully!');
            },
            error: function() {
                alert('Error updating status.');
            }
        });
    });

    $('.view-resume').on('click', function(e) {
        e.preventDefault();
        var resumeUrl = $(this).attr('href');
        window.open(resumeUrl, '_blank');
    });
});
</script>
