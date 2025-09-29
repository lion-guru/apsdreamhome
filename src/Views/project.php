<?php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cache_limiter','public');
    session_cache_limiter(false);
    session_start();
}
include("config.php");
include(__DIR__ . '/includes/updated-config-paths.php');
include(__DIR__ . '/includes/functions/common-functions.php');
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
<meta name="description" content="APS Dream Homes">
<meta name="keywords" content="">
<meta name="author" content="Unicoder">
<link rel="shortcut icon" href="images/favicon.ico">

<!-- Fonts -->
<link href="https://fonts.googleapis.com/css?family=Muli:400,400i,500,600,700&amp;display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Comfortaa:400,700" rel="stylesheet">

<!-- Css Link -->
  
<link rel="stylesheet" href="<?php echo get_asset_url('css/bootstrap.min.css', 'css'); ?>">
<link rel="stylesheet" href="<?php echo get_asset_url('css/bootstrap-slider.css', 'css'); ?>">
<link rel="stylesheet" href="<?php echo get_asset_url('css/jquery-ui.css', 'css'); ?>">
<link rel="stylesheet" href="<?php echo get_asset_url('css/layerslider.css', 'css'); ?>">
<link rel="stylesheet" href="<?php echo get_asset_url('css/color.css', 'css'); ?>" id="color-change">
<link rel="stylesheet" href="<?php echo get_asset_url('css/owl.carousel.min.css', 'css'); ?>">
<link rel="stylesheet" href="<?php echo get_asset_url('css/font-awesome.min.css', 'css'); ?>">
<link rel="stylesheet" type="text/css" href="fonts/flaticon/flaticon.css">
<link rel="stylesheet" href="<?php echo get_asset_url('css/style.css', 'css'); ?>">
<link rel="stylesheet" href="assets/css/modern-ui.css">

<!-- Title -->
<title>APS DReam Homes</title>
</head>
<body>

<!--	Page Loader -->
<!-- <div class="page-loader position-fixed z-index-9999 w-100 bg-white vh-100">
    <div class="d-flex justify-content-center y-middle position-relative">
      <div class="spinner-border" role="status">
        <span class="sr-only">Loading...</span>
      </div>
    </div>
</div> -->
  
<div id="page-wrapper">
    <div class="row">
        <!--	Header start  -->
        <?php include(__DIR__ . '/includes/templates/dynamic_header.php');?>
        <!--	Header end  -->

        <!--	Banner   --->
        <!-- <div class="banner-full-row page-banner" style="background-image:url('<?php echo get_asset_url('breadcromb.jpg', 'images'); ?>');">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <h2 class="page-name float-left text-white text-uppercase mt-1 mb-0"><b>About US</b></h2>
                    </div>
                    <div class="col-md-6">
                        <nav aria-label="breadcrumb" class="float-left float-md-right">
                            <ol class="breadcrumb bg-transparent m-0 p-0">
                                <li class="breadcrumb-item text-white"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active">About Us</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div> -->
         <!--	Banner   --->
           
        <!--	About Our Company -->
       
        
     <div>
    <div style="display: flex; flex-direction: column; align-items: center; margin: 40px 0;">
      <div style="background: #fff; box-shadow: 0 4px 24px rgba(0,0,0,0.12); border-radius: 16px; padding: 24px; max-width: 950px; width: 100%;">
        <h2 style="font-family: 'Segoe UI', Arial, sans-serif; color: #222; margin-bottom: 16px; text-align: center;">Suryoday Colony Map</h2>
        <object data="images/mapsuryoday.pdf" type="application/pdf" width="900" height="400" style="border-radius: 8px; border: 1px solid #e0e0e0; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
          <div style="padding: 32px; text-align: center; color: #666;">
            <p>Unable to display PDF. <a href="images/mapsuryoday.pdf" target="_blank" style="color: #1976d2; text-decoration: underline;">Click here to download or view in a new tab.</a></p>
          </div>
        </object>
      </div>
    </div>
</div>
    
            
               
        <!--	About Our Company -->

       <!--	Footer   start-->
        <?php include(__DIR__ . '/includes/templates/new_footer.php');?>
        <!--	Footer   start-->

        <!-- Scroll to top -->
        <a href="#" class="bg-secondary text-white hover-text-secondary" id="scroll"><i class="fas fa-angle-up"></i></a>
        <!-- End Scroll To top -->
    </div>  
</div>
<!-- Wrapper End -->

<!--	Js Link
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
<script src="<?php echo get_asset_url('js/jquery.cookie.js', 'js'); ?>"></script>
<script src="<?php echo get_asset_url('js/custom.js', 'js'); ?>"></script>
</body>

</html>