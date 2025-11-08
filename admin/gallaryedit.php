<?php
session_start();
include("config.php");
require_once __DIR__ . '/../includes/log_admin_activity.php';
// Secure authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}
if(isset($_POST['update']))
{
    $aid = $_GET['id'];
    $title=$_POST['utitle'];
    $content=$_POST['ucontent'];

    $aimage=$_FILES['aimage']['name'];

    $temp_name1 = $_FILES['aimage']['tmp_name'];


    move_uploaded_file($temp_name1,"upload/$aimage");

    $sql = "UPDATE images SET title = '{$title}' , content = '{$content}', image ='{$aimage}' WHERE id = {$aid}";
    $result=mysqli_query($con,$sql);
    if($result == true)
    {
        log_admin_activity('edit_gallery', 'Edited gallery image ID: ' . $aid);
        $msg="<p class='alert alert-success'>Gallary Updated</p>";
        header("Location:gallaryview.php?msg=$msg");
    }
    else{
        $msg="<p class='alert alert-warning'>About Not Updated</p>";
        header("Location:gallaryview.php?msg=$msg");
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
        <title>APS DREAM HOMES - Vertical Form</title>

        <!-- Favicon -->
        <link rel="shortcut icon" type="image/x-icon" href="assets/<?php echo get_asset_url('favicon.png', 'images'); ?>">

        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="<?php echo get_asset_url('css/bootstrap.min.css', 'css'); ?>">

        <!-- Fontawesome CSS -->
        <link rel="stylesheet" href="<?php echo get_asset_url('css/font-awesome.min.css', 'css'); ?>">

        <!-- Feathericon CSS -->
        <link rel="stylesheet" href="<?php echo get_asset_url('css/feathericon.min.css', 'css'); ?>">

        <!-- // SECURITY: Removed potentially dangerous code>
        <link rel="stylesheet" href="<?php echo get_asset_url('css/select2.min.css', 'css'); ?>">

        <link rel="stylesheet" href="assets\plugins\summernote\dist\summernote-bs4.css">
        <!-- Main CSS -->
        <link rel="stylesheet" href="<?php echo get_asset_url('css/style.css', 'css'); ?>">

        <!--[if lt IE 9]>
            <script src="<?php echo get_asset_url('js/html5shiv.min.js', 'js'); ?>"></script>
            <script src="<?php echo get_asset_url('js/respond.min.js', 'js'); ?>"></script>
        <![endif]-->
    </head>
    <body>

        <!-- Main Wrapper -->

            <!-- Header -->
            <?php include("header.php"); ?>
            <!-- /Sidebar -->

            <!-- Page Wrapper -->
            <div class="page-wrapper">

                <div class="content container-fluid">

                    <!-- Page Header -->
                    <div class="page-header">
                        <div class="row">
                            <div class="col">
                                <h3 class="page-title">Gallary</h3>
                                <ul class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Gallary</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!-- /Page Header -->

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h2 class="card-title">Gallary Image</h2>
                                </div>
                                <?php
                                $aid = $_GET['id'];
                                $sql = "SELECT * FROM images where id = {$aid}";
                                $result = mysqli_query($con, $sql);
                                while($row = mysqli_fetch_row($result))
                                {
                                ?>
                                <form method="post" enctype="multipart/form-data">
                                <div class="card-body">
                                        <div class="row">
                                            <div class="col-xl-12">
                                                <h5 class="card-title">Gallary </h5>
                                                <div class="form-group row">
                                                    <label class="col-lg-2 col-form-label">Title</label>
                                                    <div class="col-lg-9">
                                                        <input type="text" class="form-control" name="utitle" value="<?php echo $row['1']; ?>">
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-lg-2 col-form-label">Content</label>
                                                    <div class="col-lg-9">
                                                        <textarea class="tinymce form-control" name="ucontent" rows="10" cols="30"><?php echo $row['2']; ?></textarea>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-lg-2 col-form-label">Image</label>
                                                    <div class="col-lg-9">
                                                        <img src="upload/<?php echo $row['3']; ?>" height="200px" width="200px"><br><br>
                                                        <input class="form-control" name="aimage" type="file" required="">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-left">
                                            <input type="submit" class="btn btn-primary"  value="Submit" name="update" style="margin-left:200px;">
                                        </div>
                                    </form>
                                </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
            <!-- /Page Wrapper -->
        <!-- /Main Wrapper -->
        <script src="assets/plugins/tinymce/tinymce.min.js"></script>
        <script src="assets/plugins/tinymce/init-tinymce.min.js"></script>
        <!-- jQuery -->
        <script src="<?php echo get_asset_url('js/jquery-3.2.1.min.js', 'js'); ?>"></script>

        <!-- Bootstrap Core JS -->
        <script src="<?php echo get_asset_url('js/popper.min.js', 'js'); ?>"></script>
        <script src="<?php echo get_asset_url('js/bootstrap.min.js', 'js'); ?>"></script>

        <!-- Slimscroll JS -->
        <script src="assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>

        <!-- // SECURITY: Removed potentially dangerous code>
        <script src="<?php echo get_asset_url('js/select2.min.js', 'js'); ?>"></script>

        <!-- Custom JS -->
        <script src="<?php echo get_asset_url('js/script.js', 'js'); ?>"></script>
    </body>


</html>
