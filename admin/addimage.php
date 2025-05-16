<?php
session_start();
require("config.php");

if(!isset($_SESSION['auser']))
{
    header("location:index.php");
}

//// add code
$error="";
$msg="";
if(isset($_POST['addimage']))
{

    $title=$_POST['title'];
    $content=$_POST['content'];
    $aimage=$_FILES['aimage']['name'];
    $type=$_POST['type'];

    $temp_name1 = $_FILES['aimage']['tmp_name'];


    move_uploaded_file($temp_name1,"upload/$aimage");

    $sql="insert into images (title,content,image,type) values('$title','$content','$aimage' ,'$type')";
    $result=mysqli_query($con,$sql);
    if($result)
        {
            $msg="<p class='alert alert-success'>Inserted Successfully</p>";

        }
        else
        {
            $error="<p class='alert alert-warning'>* Not Inserted Some Error</p>";
        }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
        <title>APS DREAM HOMES | Gallary</title>

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

        <!-- Main CSS -->
        <link rel="stylesheet" href="<?php echo get_asset_url('css/style.css', 'css'); ?>">

        <!--[if lt IE 9]>
            <script src="<?php echo get_asset_url('js/html5shiv.min.js', 'js'); ?>"></script>
            <script src="<?php echo get_asset_url('js/respond.min.js', 'js'); ?>"></script>
        <![endif]-->
    </head>
    <body>
    <?php include __DIR__ . '/../includes/templates/dynamic_header.php'; ?>
        <!-- Main Wrapper -->

            <!-- Page Wrapper -->
            <div class="page-wrapper">

                <div class="content container-fluid">

                    <!-- Page Header -->
                    <div class="page-header">
                        <div class="row">
                            <div class="col">
                                <h3 class="page-title">Gallary Image</h3>
                                <ul class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Gallary Image</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!-- /Page Header -->

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h2 class="card-title">Add Gallary Image</h2>
                                </div>
                                <form method="post" enctype="multipart/form-data">
                                <div class="card-body">
                                        <div class="row">
                                            <div class="col-xl-12">
                                                <h5 class="card-title">Add Gallary Image </h5>
                                                <?php echo $error; ?>
                                                <?php echo $msg; ?>
                                                <div class="form-group row">
                                                    <label class="col-lg-2 col-form-label">Title</label>
                                                    <div class="col-lg-9">
                                                        <input type="text" class="form-control" name="title" required="">
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-lg-2 col-form-label">IMAGE TYPE</label>
                                                  <div class="col-lg-9">  
                                                    <select name="type">
        <option value="legal">Legal</option>
        <option value="gallary">Gallary</option>
        <option value="suryoday_colony">Suryoday Colony</option>
        <option value="raghunath_nagri">Raghunath Nagri</option>
    </select>
                                                  </div>  
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-lg-2 col-form-label">Image</label>
                                                    <div class="col-lg-9">
                                                        <input class="form-control" name="aimage" type="file" required="">
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-lg-2 col-form-label">Content</label>
                                                    <div class="col-lg-9">
                                                        <textarea class="tinymce form-control" name="content" rows="10" cols="30"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-left">
                                            <input type="submit" class="btn btn-primary"  value="Submit" name="addimage" style="margin-left:200px;">
                                        </div>
                                    </form>
                                </div>

                            </div>
                        </div>
                    </div>


                </div>
            </div>
            <!-- /Page Wrapper -->
    <?php include __DIR__ . '/../includes/templates/new_footer.php'; ?>
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
