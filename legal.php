<?php
ini_set('session.cache_limiter','public');
session_cache_limiter(false);
session_start();
require_once(__DIR__ . "/includes/config/config.php");
// require_once(__DIR__ . "/includes/functions/asset_helper.php"); // Deprecated, use get_asset_url() from common-functions.php or updated-config-paths.php instead
require_once(__DIR__ . '/includes/updated-config-paths.php');
require_once(__DIR__ . '/includes/common-functions.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
<!-- Required meta tags -->
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

<!-- Meta Tags -->
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<link rel="shortcut icon" href="<?php echo get_asset_url('favicon.ico', 'images'); ?>">

<!-- CSS -->
<link rel="stylesheet" href="<?php echo get_asset_url('css/bootstrap.min.css', 'css'); ?>">
<link rel="stylesheet" href="<?php echo get_asset_url('css/bootstrap-slider.css', 'css'); ?>">
<link rel="stylesheet" href="<?php echo get_asset_url('css/jquery-ui.css', 'css'); ?>">
<link rel="stylesheet" href="<?php echo get_asset_url('css/layerslider.css', 'css'); ?>">
<link rel="stylesheet" href="<?php echo get_asset_url('css/color.css', 'css'); ?>" id="color-change">
<link rel="stylesheet" href="<?php echo get_asset_url('css/owl.carousel.min.css', 'css'); ?>">
<link rel="stylesheet" href="<?php echo get_asset_url('css/font-awesome.min.css', 'css'); ?>">
<link rel="stylesheet" href="<?php echo get_asset_url('css/flaticon.css', 'css'); ?>">
<link rel="stylesheet" href="<?php echo get_asset_url('css/style.css', 'css'); ?>">

<!-- Title -->
<title>Legal - APS Dream Homes</title>
</head>
<body>

<!-- Page Loader -->
<div class="page-loader position-fixed z-index-9999 w-100 bg-white vh-100">
    <div class="d-flex justify-content-center y-middle position-relative">
      <div class="spinner-border" role="status">
        <span class="sr-only">Loading...</span>
      </div>
    </div>
</div>

<div id="page-wrapper">
    <div class="row">
        <!-- Header One -->
        <!-- Header start  -->
        <?php include(__DIR__ . '/includes/header.php');?>
        <!-- Header end -->

        <!-- Banner   
        <div class="banner-full-row page-banner" style="background-image:url('<?php echo get_asset_url('breadcromb.jpg', 'images'); ?>');">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <h2 class="page-name float-left text-white text-uppercase mt-1 mb-0 float-md-right"><b>Legal</b></h2>
                    </div>
                    <div class="col-md-6">
                        <nav aria-label="breadcrumb" class="float-left float-md-right">
                            <ol class="breadcrumb bg-transparent m-0 p-0">
                                <li class="breadcrumb-item text-white"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active">Legal</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div> --->
         <!-- Banner   --->


        <div class="full-row">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <h2 class="text-secondary double-down-line text-center mb-5">Legal Documents</h2>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 col-lg-3">
                        <div class="legal-doc-box text-center">
                            <img src="<?php echo get_asset_url('legal/one.jpg', 'images'); ?>" alt="Legal Document 1" class="img-fluid">
                            <h4>Document 1</h4>
                            <p>Important legal document description</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="legal-doc-box text-center">
                            <img src="<?php echo get_asset_url('legal/two.jpg', 'images'); ?>" alt="Legal Document 2" class="img-fluid">
                            <h4>Document 2</h4>
                            <p>Important legal document description</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="legal-doc-box text-center">
                            <img src="<?php echo get_asset_url('legal/three.jpg', 'images'); ?>" alt="Legal Document 3" class="img-fluid">
                            <h4>Document 3</h4>
                            <p>Important legal document description</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="legal-doc-box text-center">
                            <img src="<?php echo get_asset_url('legal/four.jpg', 'images'); ?>" alt="Legal Document 4" class="img-fluid">
                            <h4>Document 4</h4>
                            <p>Important legal document description</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer   start-->
        <?php include(__DIR__ . '/includes/footer.php');?>
        <!-- Footer   start-->

        <!-- Scroll to top -->
        <a href="#" class="bg-secondary text-white hover-text-secondary" id="scroll"><i class="fas fa-angle-up"></i></a>
        <!-- End Scroll To top -->
    </div>
</div>
<!-- Wrapper End -->

<!-- Js Link
============================================================-->
<script src="<?php echo get_asset_url('js/jquery.min.js', 'js'); ?>"></script>
<!--jQuery Layer Slider -->
<script src="<?php echo get_asset_url('js/greensock.js', 'js'); ?>"></script>
<script src="<?php echo get_asset_url('js/layerslider.transitions.js', 'js'); ?>"></script>
<script src="<?php echo get_asset_url('js/layerslider.kreaturamedia.jquery.js', 'js'); ?>"></script>
<!--jQuery Layer Slider -->
<script src="<?php echo get_asset_url('js/popper.min.js', 'js'); ?>"></script>
<script src="<?php echo get_asset_url('js/bootstrap.min.js', 'js'); ?>"></script>
<script src="<?php echo get_asset_url('js/owl.carousel.min.js', 'js'); ?>"></script>
<script src="<?php echo get_asset_url('js/tmpl.js', 'js'); ?>"></script>
<script src="<?php echo get_asset_url('js/jquery.dependClass-0.1.js', 'js'); ?>"></script>
<script src="<?php echo get_asset_url('js/draggable-0.1.js', 'js'); ?>"></script>
<script src="<?php echo get_asset_url('js/jquery.slider.js', 'js'); ?>"></script>
<script src="<?php echo get_asset_url('js/wow.js', 'js'); ?>"></script>

<script src="<?php echo get_asset_url('js/custom.js', 'js'); ?>"></script>
</body>

</html>