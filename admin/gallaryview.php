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
        <title>APS Dream Homes | Gallary</title>

        <!-- Favicon -->
        <link rel="shortcut icon" type="image/x-icon" href="assets/<?php echo get_asset_url('favicon.png', 'images'); ?>">

        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="<?php echo get_asset_url('css/bootstrap.min.css', 'css'); ?>">

        <!-- Fontawesome CSS -->
        <link rel="stylesheet" href="<?php echo get_asset_url('css/font-awesome.min.css', 'css'); ?>">

        <!-- Feathericon CSS -->
        <link rel="stylesheet" href="<?php echo get_asset_url('css/feathericon.min.css', 'css'); ?>">

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
                                <h3 class="page-title">View Gallary</h3>
                                <ul class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active">View Gallary</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!-- /Page Header -->

                    <div class="row">
                        <div class="col-sm-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">List Of Gallary Image</h4>
                                    <?php
                                            if(isset($_GET['msg']))
                                            echo $_GET['msg'];

                                    ?>
                                </div>
                                <div class="card-body">

                                    <div class="table-responsive">
                                        <table class="table table-stripped table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Title</th>
                                                    <th>Content</th>
                                                    <th>Image</th>
                                                    <th>Actions</th>

                                                </tr>
                                            </thead>
                                            <?php

                                                    $query=mysqli_query($con,"select * from images");
                                                    $cnt=1;
                                                    while($row=mysqli_fetch_row($query))
                                                        {
                                            ?>
                                            <tbody>
                                                <tr>
                                                    <td><?php echo $cnt; ?></td>
                                                    <td><?php echo $row['1']; ?></td>
                                                    <td><?php echo $row['2']; ?></td>
                                                    <td><img src="upload/<?php echo $row['3']; ?>" height="200px" width="200px"></td>
                                                    <td><a href="gallaryedit.php?id=<?php echo $row['0']; ?>"><button class="btn btn-info">Edit</button></a>
                                                    <a href="gallarydelete.php?id=<?php echo $row['0']; ?>"><button class="btn btn-danger">Delete</button></a></td>
                                                </tr>
                                            </tbody>
                                                <?php
                                                $cnt=$cnt+1;
                                                }
                                                ?>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <!-- /Main Wrapper -->


        <!-- jQuery -->
        <script src="<?php echo get_asset_url('js/jquery-3.2.1.min.js', 'js'); ?>"></script>

        <!-- Bootstrap Core JS -->
        <script src="<?php echo get_asset_url('js/popper.min.js', 'js'); ?>"></script>
        <script src="<?php echo get_asset_url('js/bootstrap.min.js', 'js'); ?>"></script>

        <!-- Slimscroll JS -->
        <script src="assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>

        <!-- Custom JS -->
        <script src="<?php echo get_asset_url('js/script.js', 'js'); ?>"></script>

    </body>
</html>
