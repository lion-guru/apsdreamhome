<?php
ini_set('session.cache_limiter','public');
session_cache_limiter(false);
session_start();
include("config.php");								
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
<meta name="description" content="Real Estate PHP">
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

<!-- Title -->
<title>APS Dream Homes</title>
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
		<?php include(__DIR__ . '/includes/header.php');?>
        <!--	Header end  -->
        
        <div class="full-row">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <h2 class="text-secondary double-down-line text-center mb-5">Lucknow Projects</h2>
                        
                        <!-- end header inner -->
  <div class="clearfix"></div>
  <section class="section-light section-side-image clearfix">
    <div class="container">
      <div class="row">
      <div class="col-sm-12">
        <h4><b style="color:#000;">RAM NAGRI </h4>
        <p><b>RAM NAGRI Situated At Haidergarh highway.APS Dream Homes is an integrated township located at Prime Location of Kushinagar. The township spread across more than 15 acres being developed in blocks, which includes Plots, Row Houses and commercial space along with Entrance Gate, Electricity, Drainage system, Shopping Centre etc</b>.<br><b style="color:#000;">Our Township is located nearby maximum number of Engineering Colleges Educational Institutions, Dental Colleges, Railway Station,Petrol Pumps, Airport, service stations, Banks, ATM, and City Mall.</b></p>
        
        </div>
      </div>
    </div>
  </section>
  <div class="clearfix"></div>
<!-- Top Amenities Section -->
<section class="section-light sec-tpadding-0">
    <div class="full-row">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <h4 class="text-secondary text-center mb-5">TOP AMENITIES</h4>
      </div>
    </div>
    <div class="owl-carousel owl-theme">
      <!-- Slide 1 -->
      <div class="item">
        <img src="assets/<?php echo get_asset_url('amenities/1.jpg', 'images'); ?>" alt="Amenity 1">
      </div>
      <!-- Slide 2 -->
      <div class="item">
        <img src="assets/<?php echo get_asset_url('amenities/2.jpg', 'images'); ?>" alt="Amenity 2">
      </div>
      <!-- Slide 3 -->
      <div class="item">
        <img src="assets/<?php echo get_asset_url('amenities/3.jpg', 'images'); ?>" alt="Amenity 3">
      </div>
       <!-- Slide 3 -->
      <div class="item">
        <img src="assets/<?php echo get_asset_url('amenities/4.jpg', 'images'); ?>" alt="Amenity 3">
      </div>
       <!-- Slide 3 -->
      <div class="item">
        <img src="assets/<?php echo get_asset_url('amenities/5.jpg', 'images'); ?>" alt="Amenity 3">
      </div>
       <!-- Slide 3 -->
      <div class="item">
        <img src="assets/<?php echo get_asset_url('amenities/6.jpg', 'images'); ?>" alt="Amenity 3">
      </div>
       <!-- Slide 3 -->
      <div class="item">
        <img src="assets/<?php echo get_asset_url('amenities/7.jpg', 'images'); ?>" alt="Amenity 3">
      </div>
      <!-- Add more slides as needed -->
    </div>
  </div>
</section>

    
  
       </div>
      </div>
                <div class="row">
                </div>
            </div>
       <!--	Footer   start-->
		<?php include(__DIR__ . '/includes/footer.php');?>
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
<script src="<?php echo get_asset_url('js/script.js', 'js'); ?>"></script>
</body>

</html>