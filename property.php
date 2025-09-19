<?php
// Start session and include necessary files
session_start();
include("config.php");
include(__DIR__ . '/includes/functions/common-functions.php');

// Get property listings from database
$properties = [];
try {
    if (isset($con) && $con) {
        $query = "SELECT p.*, u.first_name, u.last_name, u.mobile, pt.name as property_type 
                 FROM properties p 
                 LEFT JOIN users u ON p.user_id = u.id 
                 LEFT JOIN property_types pt ON p.property_type_id = pt.id 
                 WHERE p.status = 'available' AND p.is_resale = 1 
                 ORDER BY p.created_at DESC";
        $result = mysqli_query($con, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $properties[] = $row;
            }
        }
    }
} catch (Exception $e) {
    // Fallback to static properties if database query fails
}

// If no properties from database, use static properties
if (empty($properties)) {
    $properties = [
        [
            'id' => 1,
            'title' => 'Luxury Villa in Gorakhpur',
            'description' => 'Beautiful 4 bedroom villa with modern amenities',
            'price' => '1500000',
            'location' => 'Gorakhpur, UP',
            'area' => '3000',
            'bedrooms' => 4,
            'bathrooms' => 3,
            'property_type' => 'Villa',
            'first_name' => 'Rahul',
            'last_name' => 'Sharma',
            'mobile' => '9876543210',
            'image' => 'images/property/1.jpg'
        ],
        [
            'id' => 2,
            'title' => 'Modern Apartment in Lucknow',
            'description' => 'Spacious 3 bedroom apartment in prime location',
            'price' => '900000',
            'location' => 'Lucknow, UP',
            'area' => '1800',
            'bedrooms' => 3,
            'bathrooms' => 2,
            'property_type' => 'Apartment',
            'first_name' => 'Priya',
            'last_name' => 'Verma',
            'mobile' => '8765432109',
            'image' => 'images/property/2.jpg'
        ],
        [
            'id' => 3,
            'title' => 'Commercial Space in Gorakhpur',
            'description' => 'Prime commercial property for business',
            'price' => '2500000',
            'location' => 'Gorakhpur, UP',
            'area' => '5000',
            'bedrooms' => 0,
            'bathrooms' => 2,
            'property_type' => 'Commercial',
            'first_name' => 'Amit',
            'last_name' => 'Singh',
            'mobile' => '7654321098',
            'image' => 'images/property/3.jpg'
        ]
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
    <meta name="description" content="APS Dream Homes - Resale Properties">
    <meta name="keywords" content="real estate, property, resale, buy, sell, aps dream homes">
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
    <title>Resale Properties - APS Dream Homes</title>
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
                            <h2 class="page-name float-left text-white text-uppercase mt-1 mb-0"><b>Resale Properties</b></h2>
                        </div>
                        <div class="col-md-6">
                            <nav aria-label="breadcrumb" class="float-left float-md-right">
                                <ol class="breadcrumb bg-transparent m-0 p-0">
                                    <li class="breadcrumb-item text-white"><a href="index.php">Home</a></li>
                                    <li class="breadcrumb-item active">Resale Properties</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Property List Section -->
            <div class="full-row">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="row">
                                <?php if (!empty($properties)): ?>
                                    <?php foreach ($properties as $property): ?>
                                        <div class="col-md-6">
                                            <div class="property-grid-1 property-block bg-white transation mb-4">
                                                <div class="overflow-hidden position-relative transation">
                                                    <a href="property-detail.php?id=<?php echo $property['id']; ?>">
                                                        <img src="<?php echo isset($property['image']) ? $property['image'] : 'images/property/default.jpg'; ?>" alt="<?php echo $property['title']; ?>" class="img-fluid">
                                                    </a>
                                                    <div class="btn-primary position-absolute hover-text-white text-center py-1 px-3 m-3"><?php echo $property['property_type']; ?></div>
                                                    <div class="text-primary position-absolute hover-text-white text-center py-1 px-3 m-3 bg-white" style="right:0">₹<?php echo number_format($property['price']); ?></div>
                                                </div>
                                                <div class="p-3">
                                                    <h5 class="text-secondary hover-text-primary mb-2"><a href="property-detail.php?id=<?php echo $property['id']; ?>"><?php echo $property['title']; ?></a></h5>
                                                    <span class="text-primary"><?php echo $property['location']; ?></span>
                                                    <div class="d-flex mt-2">
                                                        <?php if (isset($property['bedrooms']) && $property['bedrooms'] > 0): ?>
                                                            <span class="mr-4"><i class="fas fa-bed text-primary mr-1"></i><?php echo $property['bedrooms']; ?> Bed</span>
                                                        <?php endif; ?>
                                                        <?php if (isset($property['bathrooms']) && $property['bathrooms'] > 0): ?>
                                                            <span class="mr-4"><i class="fas fa-bath text-primary mr-1"></i><?php echo $property['bathrooms']; ?> Bath</span>
                                                        <?php endif; ?>
                                                        <span><i class="fas fa-ruler-combined text-primary mr-1"></i><?php echo $property['area']; ?> sq ft</span>
                                                    </div>
                                                </div>
                                                <div class="border-top p-3">
                                                    <div class="d-flex justify-content-between">
                                                        <span><i class="fas fa-user text-primary mr-1"></i><?php echo $property['first_name'] . ' ' . $property['last_name']; ?></span>
                                                        <span><i class="fas fa-phone text-primary mr-1"></i><?php echo $property['mobile']; ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="col-md-12">
                                        <div class="alert alert-info">No resale properties available at the moment. Please check back later or <a href="submitproperty.php">add your property</a>.</div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Sidebar -->
                        <div class="col-lg-4">
                            <div class="sidebar-widget">
                                <h4 class="double-down-line-left text-secondary position-relative pb-4 mb-4">Search Property</h4>
                                <form method="GET" action="property-search.php" class="bg-white">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <input type="text" name="keyword" class="form-control" placeholder="Keyword...">
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <select name="type" class="form-control">
                                                    <option value="">Select Type</option>
                                                    <option value="apartment">Apartment</option>
                                                    <option value="villa">Villa</option>
                                                    <option value="plot">Plot</option>
                                                    <option value="commercial">Commercial</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <select name="location" class="form-control">
                                                    <option value="">Select Location</option>
                                                    <option value="gorakhpur">Gorakhpur</option>
                                                    <option value="lucknow">Lucknow</option>
                                                    <option value="kusinagar">Kusinagar</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <button type="submit" class="btn btn-primary w-100">Search Property</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            
                            <div class="sidebar-widget mt-5">
                                <h4 class="double-down-line-left text-secondary position-relative pb-4 mb-4">Recently Added</h4>
                                <ul class="property_list_widget">
                                    <?php 
                                    $recent_properties = array_slice($properties, 0, 3);
                                    foreach ($recent_properties as $property): 
                                    ?>
                                    <li>
                                        <img src="<?php echo isset($property['image']) ? $property['image'] : 'images/property/default.jpg'; ?>" alt="<?php echo $property['title']; ?>">
                                        <h6 class="text-secondary hover-text-primary text-capitalize"><a href="property-detail.php?id=<?php echo $property['id']; ?>"><?php echo $property['title']; ?></a></h6>
                                        <span class="font-14"><i class="fas fa-map-marker-alt icon-primary icon-small"></i> <?php echo $property['location']; ?></span>
                                        <div class="mt-2"><span class="text-primary">₹<?php echo number_format($property['price']); ?></span></div>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
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
    <script src="js/custom.js"></script>
</body>
</html>