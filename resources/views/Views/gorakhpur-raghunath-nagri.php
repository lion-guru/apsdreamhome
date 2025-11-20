<?php
// --- Dynamic Data Section ---
// Try to fetch amenities and videos from DB, else use static fallback
$amenities = [];
$videos = [];
$db_failed = false;

// 1. Try to fetch amenities from DB (if table exists)
try {
    if (isset($conn) && $conn) {
        $result = $conn->query("SELECT title, image, alt_text FROM amenities_gorakhpur ORDER BY id ASC");
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $amenities[] = $row;
            }
        }
    }
} catch (Exception $e) {
    $db_failed = true;
}
// Fallback static amenities if DB fails or is empty
if (empty($amenities)) {
    $amenities = [
        ['title' => '24/7 Security', 'image' => 'amenities/1.jpg', 'alt_text' => '24/7 Security'],
        ['title' => 'Clubhouse', 'image' => 'amenities/2.jpg', 'alt_text' => 'Clubhouse'],
        ['title' => 'Gymnasium', 'image' => 'amenities/3.jpg', 'alt_text' => 'Gymnasium'],
        ['title' => 'Swimming Pool', 'image' => 'amenities/4.jpg', 'alt_text' => 'Swimming Pool'],
        ['title' => "Children's Play Area", 'image' => 'amenities/5.jpg', 'alt_text' => "Children's Play Area"],
        ['title' => 'Landscaped Gardens', 'image' => 'amenities/6.jpg', 'alt_text' => 'Landscaped Gardens'],
        ['title' => 'Power Backup', 'image' => 'amenities/7.jpg', 'alt_text' => 'Power Backup'],
    ];
}
// 2. Try to fetch videos from DB (if table exists)
try {
    if (isset($conn) && $conn) {
        $result = $conn->query("SELECT title, youtube_id FROM project_videos_gorakhpur ORDER BY id ASC");
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $videos[] = $row;
            }
        }
    }
} catch (Exception $e) {
    $db_failed = true;
}
// Fallback static videos if DB fails or is empty
if (empty($videos)) {
    $videos = [
        ['title' => 'Project Overview', 'youtube_id' => 'BhUOvYwfIcQ'],
        ['title' => 'Location Walkthrough', 'youtube_id' => '8aR_447wdnQ'],
        ['title' => 'Amenities Showcase', 'youtube_id' => 'VhGCJ6P_PjU'],
        ['title' => 'Customer Testimonials', 'youtube_id' => 'BhUOvYwfIcQ'],
    ];
}
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
<link rel="shortcut icon" href="<?php echo get_asset_url('favicon.ico', 'images'); ?>">

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
<link rel="stylesheet" type="text/css" href="<?php echo get_asset_url('fonts/flaticon/flaticon.css', 'css'); ?>">
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
		<?php include(__DIR__ . '/includes/templates/dynamic_header.php');?>
        <!--	Header end  -->
        
        <div class="full-row">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <h2 class="text-secondary double-down-line text-center mb-5">Gorakhpur Projects</h2>
                        
                        <!-- end header inner -->
  <div class="clearfix"></div>
  <section class="section-light section-side-image clearfix">
    <div class="container">
      <div class="row">
      <div class="col-sm-12">
          <h4 class="text-secondary text-center mb-5">Raghunath Nagri</h4>
        <h4><b class="text-light"> </b></h4>
        <p class="text-light"><b>RAGHUNATH NAGRI Situated At Motiram to jhangha Road. APS Dream Homes is an integrated township located at Prime Location of Gorakhpur. The township spread across more than 15 acres being developed in blocks, which includes Plots, Row Houses and commercial space along with Entrance Gate, Electricity, Drainage system, Shopping Centre, Park etc</b>.<br><b class="text-light">Our Township is located nearby maximum number of Engineering Colleges Educational Institutions, Dental Colleges, Railway Station, Petrol Pumps, Airport, service stations, Banks, ATM, and City Mall.</b></p>
      </div>
    </div>
  </div>
</section>

<!-- Site Map Section -->
<section class="section-light sec-tpadding-0">
  <div class="full-row">
    <div class="container">
      <div class="row">
        <div class="col-sm-12 col-md-8 offset-md-2 col-lg-6 offset-lg-3">
          <h4 class="text-secondary text-center mb-4">Site Map Layout</h4>
          <img src="<?php echo get_asset_url('site_photo/gorakhpur/raghunath nagri motiram.JPG', 'images'); ?>" alt="Raghunath Nagri Site Map" class="img-fluid mx-auto d-block mb-4">
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Video Section -->
<section class="section-light sec-tpadding-0">
  <div class="full-row">
    <div class="container">
      <div class="row">
        <div class="col-12 text-center">
          <h4 class="text-secondary text-center mb-4">Watch Our Site Videos</h4>
          <div class="d-flex flex-wrap justify-content-center align-items-center mb-4">
            <?php foreach ($videos as $video): ?>
              <div class="video-wrap m-2">
                <iframe width="250" height="150" src="https://www.youtube.com/embed/<?php echo htmlspecialchars($video['youtube_id']); ?>" frameborder="0" allowfullscreen title="<?php echo htmlspecialchars($video['title']); ?>"></iframe>
                <div class="text-light mt-2 small"><?php echo htmlspecialchars($video['title']); ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Top Amenities Section -->
<section class="section-light sec-tpadding-0">
  <div class="full-row">
    <div class="container">
      <div class="row">
        <div class="col-12">
          <h4 class="text-secondary text-center mb-4">Top Amenities</h4>
          <div class="row justify-content-center">
            <?php foreach ($amenities as $amenity): ?>
              <div class="col-md-4 col-sm-6 mb-4">
                <img src="<?php echo get_asset_url($amenity['image'], 'images'); ?>" alt="<?php echo htmlspecialchars($amenity['alt_text']); ?>" class="img-fluid rounded mb-2">
                <div class="text-light text-center small"><?php echo htmlspecialchars($amenity['title']); ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<style>
.video-wrap iframe { border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.15); }
</style>

  <div class="clearfix"></div>
<!-- Top Amenities Section -->
<section class="section-light sec-tpadding-0">
    <div class="full-row">
            <div class="container">
                <div class="row">
                    <div class="col-sm-12">
                        <h4 class="text-secondary text-center mb-5">Top Amenities</h4>
      </div>
    </div>
    <div class="owl-carousel owl-theme">
      <!-- Slide 1 -->
      <?php foreach ($amenities as $amenity): ?>
      <div class="item">
        <img src="<?php echo get_asset_url($amenity['image'], 'images'); ?>" alt="<?php echo htmlspecialchars($amenity['alt_text']); ?>">
      </div>
      <?php endforeach; ?>
      <!-- Add more slides as needed -->
    </div>
  </div>
</section>



<!-- ... (rest of the code remains the same) ... -->

    
  
       </div>
      </div>
                <div class="row">
                </div>
            </div>
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
<script src="<?php echo get_asset_url('js/script.js', 'js'); ?>"></script>
</body>

</html>