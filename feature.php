<?php
// Start session and include necessary files
session_start();
include("config.php");
include(__DIR__ . '/includes/functions/common-functions.php');

// Check if user is logged in
if(!isset($_SESSION['uemail'])) {
    header("location:login.php");
    exit;
}

// Get user ID from session
$uid = $_SESSION['uid'];

// Get user's properties from database
$properties = [];
try {
    if (isset($con) && $con) {
        $query = "SELECT p.*, pt.name as property_type 
                 FROM properties p 
                 LEFT JOIN property_types pt ON p.property_type_id = pt.id 
                 WHERE p.user_id = $uid 
                 ORDER BY p.created_at DESC";
        $result = mysqli_query($con, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $properties[] = $row;
            }
        }
    }
} catch (Exception $e) {
    // Handle database error
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
    <meta name="description" content="APS Dream Homes - Your Properties">
    <meta name="keywords" content="real estate, property, my properties, aps dream homes">
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
    <title>Your Properties - APS Dream Homes</title>
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
                            <h2 class="page-name float-left text-white text-uppercase mt-1 mb-0"><b>Your Properties</b></h2>
                        </div>
                        <div class="col-md-6">
                            <nav aria-label="breadcrumb" class="float-left float-md-right">
                                <ol class="breadcrumb bg-transparent m-0 p-0">
                                    <li class="breadcrumb-item text-white"><a href="index.php">Home</a></li>
                                    <li class="breadcrumb-item active">Your Properties</li>
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
                        <div class="col-lg-12">
                            <div class="mb-4">
                                <h4 class="text-secondary">Your Listed Properties</h4>
                                <p>Manage your property listings here. You can add new properties or edit existing ones.</p>
                                <a href="submitproperty.php" class="btn btn-primary">Add New Property</a>
                            </div>
                            
                            <?php if (!empty($properties)): ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="bg-primary text-white">
                                            <tr>
                                                <th>Title</th>
                                                <th>Type</th>
                                                <th>Price</th>
                                                <th>Location</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($properties as $property): ?>
                                                <tr>
                                                    <td><?php echo $property['title']; ?></td>
                                                    <td><?php echo $property['property_type']; ?></td>
                                                    <td>₹<?php echo number_format($property['price']); ?></td>
                                                    <td><?php echo $property['location']; ?></td>
                                                    <td>
                                                        <span class="badge <?php echo ($property['status'] == 'available') ? 'badge-success' : 'badge-secondary'; ?>">
                                                            <?php echo ucfirst($property['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="property-detail.php?id=<?php echo $property['id']; ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                                        <a href="submitpropertyupdate.php?id=<?php echo $property['id']; ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                                                        <a href="submitpropertydelete.php?id=<?php echo $property['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this property?')"><i class="fas fa-trash"></i></a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <p>You haven't listed any properties yet. <a href="submitproperty.php">Click here</a> to add your first property.</p>
                                </div>
                            <?php endif; ?>
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