<?php
// Start session and include necessary files
session_start();
include("config.php");
include(__DIR__ . '/includes/updated-config-paths.php');
include(__DIR__ . '/includes/common-functions.php');

// Set page specific variables
$page_title = "Our Properties - APS Dream Homes";
$meta_description = "Explore our premium residential and commercial properties in Gorakhpur, Lucknow, and across Uttar Pradesh.";

// Get properties from database
$query = mysqli_query($con, "SELECT property.*, user.uname,user.utype,user.uimage FROM `property`,`user` WHERE property.uid=user.uid");
$counts = mysqli_num_rows($query);

// Additional CSS for this page
$additional_css = '<style>
    /* Property Page Specific Styles */
    .property-section {
        padding: 80px 0;
        background-color: #f8f9fa;
    }
    
    .property-card {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
        background-color: #fff;
        transition: all 0.3s ease;
    }
    
    .property-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
    }
    
    .property-image {
        height: 250px;
        overflow: hidden;
        position: relative;
    }
    
    .property-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    
    .property-card:hover .property-image img {
        transform: scale(1.1);
    }
    
    .property-label {
        position: absolute;
        top: 15px;
        left: 15px;
        background-color: var(--primary-color);
        color: #fff;
        padding: 5px 15px;
        border-radius: 3px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    
    .property-price {
        position: absolute;
        bottom: 15px;
        left: 15px;
        background-color: rgba(0, 0, 0, 0.7);
        color: #fff;
        padding: 5px 15px;
        border-radius: 3px;
        font-size: 1rem;
        font-weight: 600;
    }
    
    .property-details {
        padding: 20px;
    }
    
    .property-title {
        font-size: 1.3rem;
        margin-bottom: 10px;
        color: var(--primary-color);
    }
    
    .property-location {
        color: #777;
        margin-bottom: 15px;
        font-size: 0.9rem;
    }
    
    .property-features {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid #eee;
    }
    
    .property-feature {
        display: flex;
        align-items: center;
        font-size: 0.9rem;
        color: #555;
    }
    
    .property-feature i {
        margin-right: 5px;
        color: var(--primary-color);
    }
    
    .property-agent {
        display: flex;
        align-items: center;
    }
    
    .agent-image {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        overflow: hidden;
        margin-right: 10px;
    }
    
    .agent-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .agent-name {
        font-size: 0.9rem;
        color: #555;
    }
    
    .filter-section {
        background-color: #fff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        margin-bottom: 30px;
    }
    
    .filter-title {
        font-size: 1.2rem;
        margin-bottom: 20px;
        color: var(--primary-color);
        position: relative;
        padding-bottom: 10px;
    }
    
    .filter-title:after {
        content: "";
        position: absolute;
        bottom: 0;
        left: 0;
        width: 50px;
        height: 2px;
        background-color: var(--primary-color);
    }
    
    .filter-form .form-group {
        margin-bottom: 15px;
    }
    
    .filter-form .form-control {
        border-radius: 5px;
        border: 1px solid #ddd;
    }
    
    .filter-form .btn-filter {
        background-color: var(--primary-color);
        color: #fff;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        font-weight: 600;
        transition: all 0.3s ease;
        width: 100%;
    }
    
    .filter-form .btn-filter:hover {
        background-color: var(--secondary-color);
    }
    
    .pagination-container {
        margin-top: 30px;
    }
    
    .pagination .page-item .page-link {
        color: var(--primary-color);
        border-color: #ddd;
    }
    
    .pagination .page-item.active .page-link {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        color: #fff;
    }
    
    @media (max-width: 767px) {
        .filter-section {
            margin-bottom: 30px;
        }
    }
</style>';

// Include the updated common header
include(__DIR__ . '/includes/updated-common-header.php');
?>

<!-- Page Banner Section -->
<div class="page-banner" style="background-image: url('<?php echo get_asset_url("banner/property-banner.jpg", "images"); ?>')">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1 class="page-title">Our Properties</h1>
                <ul class="breadcrumb">
                    <li><a href="<?php echo BASE_URL; ?>/index.php">Home</a></li>
                    <li>Properties</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Property Section -->
<section class="property-section">
    <div class="container">
        <div class="row">
            <!-- Sidebar / Filter Section -->
            <div class="col-lg-4">
                <div class="filter-section">
                    <h3 class="filter-title">Find Your Property</h3>
                    <form class="filter-form" method="post" action="property-search.php">
                        <div class="form-group">
                            <label for="location">Location</label>
                            <select class="form-control" id="location" name="location">
                                <option value="">Select Location</option>
                                <option value="Gorakhpur">Gorakhpur</option>
                                <option value="Lucknow">Lucknow</option>
                                <option value="Varanasi">Varanasi</option>
                                <option value="Kushinagar">Kushinagar</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="property-type">Property Type</label>
                            <select class="form-control" id="property-type" name="type">
                                <option value="">Select Type</option>
                                <option value="Residential Plot">Residential Plot</option>
                                <option value="Commercial Plot">Commercial Plot</option>
                                <option value="Apartment">Apartment</option>
                                <option value="Villa">Villa</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control" id="status" name="status">
                                <option value="">Select Status</option>
                                <option value="Available">Available</option>
                                <option value="Sold Out">Sold Out</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="price-range">Price Range</label>
                            <select class="form-control" id="price-range" name="price">
                                <option value="">Select Price Range</option>
                                <option value="1">Below ₹ 10 Lakh</option>
                                <option value="2">₹ 10 Lakh - ₹ 20 Lakh</option>
                                <option value="3">₹ 20 Lakh - ₹ 30 Lakh</option>
                                <option value="4">₹ 30 Lakh - ₹ 50 Lakh</option>
                                <option value="5">Above ₹ 50 Lakh</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <button type="submit" name="filter" class="btn btn-filter">Search Property</button>
                        </div>
                    </form>
                </div>
                
                <!-- Featured Projects -->
                <div class="filter-section">
                    <h3 class="filter-title">Featured Projects</h3>
                    <div class="featured-projects-list">
                        <div class="media mb-4">
                            <img src="<?php echo get_asset_url('property/APS1.jpg', 'images'); ?>" class="mr-3" alt="Suryoday Colony" style="width: 80px; height: 60px; object-fit: cover;">
                            <div class="media-body">
                                <h5 class="mt-0">Suryoday Colony</h5>
                                <p class="mb-0">Gorakhpur, Uttar Pradesh</p>
                                <a href="<?php echo BASE_URL; ?>/gorakhpur-suryoday-colony.php" class="text-primary">View Details</a>
                            </div>
                        </div>
                        <div class="media mb-4">
                            <img src="<?php echo get_asset_url('property/zillhms2.jpg', 'images'); ?>" class="mr-3" alt="Ram Nagri" style="width: 80px; height: 60px; object-fit: cover;">
                            <div class="media-body">
                                <h5 class="mt-0">Ram Nagri</h5>
                                <p class="mb-0">Lucknow, Uttar Pradesh</p>
                                <a href="<?php echo BASE_URL; ?>/lucknow-ram-nagri.php" class="text-primary">View Details</a>
                            </div>
                        </div>
                        <div class="media">
                            <img src="<?php echo get_asset_url('property/zillhms3.jpg', 'images'); ?>" class="mr-3" alt="Budha City" style="width: 80px; height: 60px; object-fit: cover;">
                            <div class="media-body">
                                <h5 class="mt-0">Budha City</h5>
                                <p class="mb-0">Kusinagar, Uttar Pradesh</p>
                                <a href="<?php echo BASE_URL; ?>/budhacity.php" class="text-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Property Listings -->
            <div class="col-lg-8">
                <div class="row">
                    <div class="col-md-12 mb-4">
                        <h2 class="section-title">Available Properties</h2>
                        <p class="lead">Discover our premium residential and commercial properties</p>
                    </div>
                </div>
                
                <div class="row">
                    <?php
                    if($counts > 0) {
                        while($row = mysqli_fetch_array($query)) {
                    ?>
                    <!-- Property Card -->
                    <div class="col-md-6">
                        <div class="property-card">
                            <div class="property-image">
                                <img src="<?php echo BASE_URL; ?>/admin/property/<?php echo $row['pimage']; ?>" alt="<?php echo $row['title']; ?>" class="lazy-load">
                                <div class="property-label"><?php echo $row['type']; ?></div>
                                <div class="property-price">₹ <?php echo number_format($row['price']); ?></div>
                            </div>
                            <div class="property-details">
                                <h3 class="property-title"><?php echo $row['title']; ?></h3>
                                <p class="property-location"><i class="fas fa-map-marker-alt"></i> <?php echo $row['city']; ?>, <?php echo $row['state']; ?></p>
                                <div class="property-features">
                                    <div class="property-feature">
                                        <i class="fas fa-bed"></i> <?php echo $row['bhk']; ?> BHK
                                    </div>
                                    <div class="property-feature">
                                        <i class="fas fa-bath"></i> <?php echo $row['bathroom']; ?> Bath
                                    </div>
                                    <div class="property-feature">
                                        <i class="fas fa-chart-area"></i> <?php echo $row['size']; ?> sq ft
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="property-agent">
                                        <div class="agent-image">
                                            <?php if($row['uimage'] != "") { ?>
                                            <img src="<?php echo BASE_URL; ?>/admin/user/<?php echo $row['uimage']; ?>" alt="<?php echo $row['uname']; ?>">
                                            <?php } else { ?>
                                            <img src="<?php echo get_asset_url('user/default-user.png', 'images'); ?>" alt="Default User">
                                            <?php } ?>
                                        </div>
                                        <div class="agent-name"><?php echo $row['uname']; ?></div>
                                    </div>
                                    <a href="<?php echo BASE_URL; ?>/propertydetail.php?pid=<?php echo $row['pid']; ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                        }
                    } else {
                    ?>
                    <div class="col-md-12">
                        <div class="alert alert-info">No properties found. Please check back later.</div>
                    </div>
                    <?php } ?>
                </div>
                
                <!-- Pagination -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="pagination-container text-center">
                            <ul class="pagination">
                                <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                                <li class="page-item"><a class="page-link" href="#">Next</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Additional JS for this page
$additional_js = '<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Lazy loading for images
        const lazyImages = document.querySelectorAll(".lazy-load");
        if ("IntersectionObserver" in window) {
            let lazyImageObserver = new IntersectionObserver(function(entries, observer) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        let lazyImage = entry.target;
                        lazyImage.classList.remove("lazy-load");
                        lazyImageObserver.unobserve(lazyImage);
                    }
                });
            });

            lazyImages.forEach(function(lazyImage) {
                lazyImageObserver.observe(lazyImage);
            });
        }
        
        console.log("Property page loaded successfully!");
    });
</script>';

// Include the updated common footer
include(__DIR__ . '/includes/updated-common-footer.php');
?>