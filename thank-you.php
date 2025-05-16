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
<link rel="stylesheet" type="text/css" href="assets/fonts/flaticon/flaticon.css">
<link rel="stylesheet" href="<?php echo get_asset_url('css/style.css', 'css'); ?>">



<!-- Title -->
<title>Thank You</title>
</head>
<body>

<!--	Page Loader -->
 <div class="page-loader position-fixed z-index-9999 w-100 bg-white vh-100">
    <div class="d-flex justify-content-center y-middle position-relative">
      <div class="spinner-border" role="status">
        <span class="sr-only">Loading...</span>
      </div>
    </div>
</div> 
  
<div id="page-wrapper">
    <div class="row">
        <!--	Header start  -->
        <?php include(__DIR__ . '/includes/header.php');?>
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
        <div class="full-row">
            <div class="container">


                <div class="full-row">
    <div class="container">
        <?php
        $image_dir = 'images/thank/';
        $files = scandir($image_dir);
        $random_image = $files[array_rand($files)];
        ?>
       
       <img src="<?php echo $image_dir. $random_image;?>" width="50%" height="100%" alt="Thank You Image" id="thank-you-image">
    </div>
</div>
<!--
<!-- Add thumbs up animation -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.9.3/lottie.min.js"></script>
<div class="thumbs-up-animation" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);"></div>
<!--
 <script>
    // Load thumbs up animation
    lottie.loadAnimation({
        container: document.querySelector('.thumbs-up-animation'),
        animationData: {
            "v": "5.9.3",
            "fr": 30,
            "ip": 0,
            "op": 30,
            "w": 100,
            "h": 100,
            "nm": "thumbs up",
            "ddd": 0,
            "assets": [],
            "layers": [
                {
                    "ddd": 0,
                    "ind": 1,
                    "ty": 4,
                    "nm": "thumbs up",
                    "sr": 1,
                    "ks": {
                        "o": {
                            "k": 100
                        },
                        "r": {
                            "k": 0
                        },
                        "p": {
                            "k": [100, 100]
                        },
                        "a": {
                            "k": 0
                        },
                        "s": {
                            "k": 100
                        }
                    },
                    "ao": 0,
                    "shapes": [
                        {
                            "ty": "gr",
                            "it": [
                                {
                                    "ty": "rc",
                                    "d": 1,
                                    "p": {
                                        "k": [50, 50]
                                    },
                                    "s": {
                                        "k": [100, 100]
                                    },
                                    "r": {
                                        "k": 0
                                    },
                                    "nm": "Oval"
                                }
                            ],
                            "nm": "thumbs up",
                            "np": 2,
                            "cix": 2
                        }
                    ]
                }
            ]
        },
        renderer: 'svg',
        loop: true,
        autoplay: true
    });
</script> -->

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
<script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.9.3/lottie.min.js"></script>
</body>

</html>