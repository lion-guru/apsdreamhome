<?php
// Start session and include necessary files
session_start();
include("config.php");
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
    <meta name="description" content="APS Dream Homes - Photo Gallery">
    <meta name="keywords" content="real estate, property, gallery, photos, aps dream homes">
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
    <link rel="stylesheet" href="<?php echo get_asset_url('css/gallery.css', 'css'); ?>">

    <!-- Title -->
    <title>Gallery - APS Dream Homes</title>
    
    <style>
        .gallery-container {
            padding: 60px 0;
        }
        .gallery-item {
            margin-bottom: 30px;
            border-radius: 5px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .gallery-item:hover {
            transform: translateY(-5px);
        }
        .gallery-item img {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }
        .gallery-caption {
            padding: 15px;
            background: #fff;
        }
        .gallery-caption h5 {
            margin-bottom: 5px;
            color: #333;
        }
        .gallery-caption p {
            color: #666;
            font-size: 14px;
        }
    </style>
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
            <!-- Header -->
            <?php include("header.php"); ?>
            
            <!-- Banner -->
            <div class="banner-full-row page-banner" style="background-image:url('images/breadcromb.jpg');">
                <div class="container">
                    <div class="row">
                        <div class="col-md-6">
                            <h2 class="page-name float-left text-white text-uppercase mt-1 mb-0"><b>Gallery</b></h2>
                        </div>
                        <div class="col-md-6">
                            <nav aria-label="breadcrumb" class="float-left float-md-right">
                                <ol class="breadcrumb bg-transparent m-0 p-0">
                                    <li class="breadcrumb-item text-white"><a href="index.php">Home</a></li>
                                    <li class="breadcrumb-item active">Gallery</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gallery Section -->
            <div class="full-row">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <h2 class="text-secondary double-down-line text-center mb-5">Our Project Gallery</h2>
                        </div>
                    </div>
                    
                    <!-- Gallery Categories -->
                    <div class="row mb-4">
                        <div class="col-lg-12 text-center">
                            <div class="gallery-filter">
                                <button class="btn btn-primary active" data-filter="*">All</button>
                                <button class="btn btn-outline-primary" data-filter=".gorakhpur">Gorakhpur</button>
                                <button class="btn btn-outline-primary" data-filter=".lucknow">Lucknow</button>
                                <button class="btn btn-outline-primary" data-filter=".kusinagar">Kusinagar</button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Gallery Items -->
                    <div class="row gallery-container">
                        <?php
                        // Try to fetch gallery items from database
                        $gallery_items = [];
                        try {
                            if (isset($con) && $con) {
                                $query = "SELECT * FROM gallery ORDER BY id DESC";
                                $result = mysqli_query($con, $query);
                                if ($result && mysqli_num_rows($result) > 0) {
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        $gallery_items[] = $row;
                                    }
                                }
                            }
                        } catch (Exception $e) {
                            // Fallback to static items if database query fails
                        }
                        
                        // If no items from database, use static items
                        if (empty($gallery_items)) {
                            $gallery_items = [
                                ['id' => 1, 'title' => 'Suryoday Colony', 'description' => 'Residential Plots in Gorakhpur', 'image' => 'images/projects/gorakhpur1.jpg', 'category' => 'gorakhpur'],
                                ['id' => 2, 'title' => 'Raghunath Nagri', 'description' => 'Premium Plots in Gorakhpur', 'image' => 'images/projects/gorakhpur2.jpg', 'category' => 'gorakhpur'],
                                ['id' => 3, 'title' => 'Ram Nagri', 'description' => 'Residential Project in Lucknow', 'image' => 'images/projects/lucknow1.jpg', 'category' => 'lucknow'],
                                ['id' => 4, 'title' => 'Nawab City', 'description' => 'Premium Plots in Lucknow', 'image' => 'images/projects/lucknow2.jpg', 'category' => 'lucknow'],
                                ['id' => 5, 'title' => 'Budha City', 'description' => 'Residential Plots in Kusinagar', 'image' => 'images/projects/kusinagar1.jpg', 'category' => 'kusinagar'],
                                ['id' => 6, 'title' => 'Site Development', 'description' => 'Construction Progress', 'image' => 'images/projects/construction1.jpg', 'category' => 'construction']
                            ];
                        }
                        
                        // Display gallery items
                        foreach ($gallery_items as $item) {
                            $category = isset($item['category']) ? $item['category'] : 'other';
                            $image = isset($item['image']) ? $item['image'] : 'images/default.jpg';
                            $title = isset($item['title']) ? $item['title'] : 'Project';
                            $description = isset($item['description']) ? $item['description'] : 'Description';
                            
                            echo '<div class="col-lg-4 col-md-6 gallery-item ' . $category . '">';
                            echo '<div class="gallery-box">';
                            echo '<img src="' . $image . '" alt="' . $title . '" class="img-fluid">';
                            echo '<div class="gallery-caption">';
                            echo '<h5>' . $title . '</h5>';
                            echo '<p>' . $description . '</p>';
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <?php include("includes/footer.php"); ?>
        </div>
    </div>

    <!-- Scroll to top -->
    <a href="#" class="bg-secondary text-white hover-text-secondary" id="scroll"><i class="fas fa-angle-up"></i></a>

    <!-- JavaScript -->
    <script src="js/jquery.min.js"></script>
    <script src="js/greensock.js"></script>
    <script src="js/layerslider.transitions.js"></script>
    <script src="js/layerslider.kreaturamedia.jquery.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/owl.carousel.min.js"></script>
    <script src="js/tmpl.js"></script>
    <script src="js/jquery.dependClass-0.1.js"></script>
    <script src="js/draggable-0.1.js"></script>
    <script src="js/jquery.slider.js"></script>
    <script src="js/wow.js"></script>
    <script src="js/isotope.pkgd.min.js"></script>
    <script src="js/custom.js"></script>
    
    <script>
        // Initialize isotope for gallery filtering
        $(document).ready(function() {
            var $grid = $('.gallery-container').isotope({
                itemSelector: '.gallery-item',
                layoutMode: 'fitRows'
            });
            
            // Filter items on button click
            $('.gallery-filter').on('click', 'button', function() {
                var filterValue = $(this).attr('data-filter');
                $grid.isotope({ filter: filterValue });
                $('.gallery-filter button').removeClass('active');
                $(this).addClass('active');
            });
        });
    </script>
</body>
</html>