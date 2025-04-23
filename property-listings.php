<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions/common-functions.php';

// Set page specific variables
$page_title = "Property Listings - APS Dream Homes";
$meta_description = "Browse our extensive collection of premium properties across Gorakhpur, Lucknow, and Uttar Pradesh. Find your dream home with APS Dream Homes.";

// Additional CSS for this page
$additional_css = '<link rel="stylesheet" href="' . get_asset_url('css/home.css', 'assets') . '">';

// Include header
require_once __DIR__ . '/include/updated-common-header.php';
?>

<!-- Hero Section -->
<section class="hero-section" style="height: 50vh; min-height: 400px; background: linear-gradient(120deg, #2a5298 70%, #e74c3c 100%); border-radius: 0 0 36px 36px; box-shadow: 0 8px 40px rgba(44,62,80,0.15);">
    <div class="slide-bg" style="background-image: url('assets/images/property-banner.jpg'); filter: brightness(0.65);"></div>
    <div class="slide-content text-center text-white" style="position: relative; z-index: 2;">
        <h1 class="display-4 fw-bold" style="text-shadow: 0 8px 32px rgba(44,62,80,0.32), 0 2px 0 #222;">Find Your Dream Property</h1>
        <p class="lead" style="text-shadow: 0 2px 8px #222;">Browse our extensive collection of premium properties</p>
    </div>
</section>

<!-- Property Listings Section -->
<section class="section-padding">
    <div class="container">
        <h2 class="section-header fw-bold text-primary mb-3">Property Listings</h2>
        <p class="lead text-secondary mb-4">Discover a wide range of properties that match your requirements and preferences</p>
        
        <!-- Property Filters -->
        <div class="property-filters">
            <form action="property-listings.php" method="GET">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="filter-label">Property Type</label>
                        <select class="custom-select" name="type">
                            <option value="">All Types</option>
                            <option value="apartment" <?php echo (isset($_GET['type']) && $_GET['type'] == 'apartment') ? 'selected' : ''; ?>>Apartment</option>
                            <option value="house" <?php echo (isset($_GET['type']) && $_GET['type'] == 'house') ? 'selected' : ''; ?>>House</option>
                            <option value="villa" <?php echo (isset($_GET['type']) && $_GET['type'] == 'villa') ? 'selected' : ''; ?>>Villa</option>
                            <option value="office" <?php echo (isset($_GET['type']) && $_GET['type'] == 'office') ? 'selected' : ''; ?>>Office</option>
                            <option value="shop" <?php echo (isset($_GET['type']) && $_GET['type'] == 'shop') ? 'selected' : ''; ?>>Shop</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="filter-label">Status</label>
                        <select class="custom-select" name="stype">
                            <option value="">All Status</option>
                            <option value="sale" <?php echo (isset($_GET['stype']) && $_GET['stype'] == 'sale') ? 'selected' : ''; ?>>For Sale</option>
                            <option value="rent" <?php echo (isset($_GET['stype']) && $_GET['stype'] == 'rent') ? 'selected' : ''; ?>>For Rent</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="filter-label">City</label>
                        <select class="custom-select" name="city">
                            <option value="">All Cities</option>
                            <?php
                            // Fetch cities from database
                            $query = mysqli_query($con, "SELECT DISTINCT city FROM property ORDER BY city ASC");
                            while($row = mysqli_fetch_array($query)) {
                                $selected = (isset($_GET['city']) && $_GET['city'] == $row['city']) ? 'selected' : '';
                                echo "<option value='{$row['city']}' {$selected}>{$row['city']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3 d-flex align-items-end">
                        <button type="submit" name="filter" class="btn btn-primary w-100">Filter Properties</button>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Property Listings -->
        <div class="row">
            <?php
            // Default query to fetch all properties
            $sql = "SELECT property.*, user.uname FROM `property`,`user` WHERE property.uid=user.uid";
            
            // Apply filters if set
            if(isset($_GET['filter'])) {
                $conditions = [];
                
                if(!empty($_GET['type'])) {
                    $type = mysqli_real_escape_string($con, $_GET['type']);
                    $conditions[] = "type='$type'";
                }
                
                if(!empty($_GET['stype'])) {
                    $stype = mysqli_real_escape_string($con, $_GET['stype']);
                    $conditions[] = "stype='$stype'";
                }
                
                if(!empty($_GET['city'])) {
                    $city = mysqli_real_escape_string($con, $_GET['city']);
                    $conditions[] = "city='$city'";
                }
                
                if(!empty($conditions)) {
                    $sql .= " AND " . implode(" AND ", $conditions);
                }
            }
            
            // Add ordering
            $sql .= " ORDER BY date DESC";
            
            // Pagination setup
            $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
            $results_per_page = 9;
            $start_from = ($page - 1) * $results_per_page;
            
            // Get total records for pagination
            $total_query = mysqli_query($con, $sql);
            $total_records = mysqli_num_rows($total_query);
            $total_pages = ceil($total_records / $results_per_page);
            
            // Add limit for pagination
            $sql .= " LIMIT $start_from, $results_per_page";
            
            $result = mysqli_query($con, $sql);
            
            if(mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_assoc($result)) {
            ?>
            <div class="col-md-4 mb-4">
                <div class="property-card shadow-lg rounded-4 position-relative">
                    <div class="property-image position-relative">
                        <img src="<?php echo get_asset_url($row['18'], 'images'); ?>" alt="<?php echo htmlspecialchars($row['1']); ?>" class="img-fluid rounded-top-4" style="height:220px; object-fit:cover; width:100%;">
                        <span class="property-badge"><?php echo htmlspecialchars($row['5']); ?></span>
                        <span class="property-price">₹<?php echo number_format($row['13']); ?></span>
                    </div>
                    <div class="property-content p-3">
                        <h3 class="fw-bold text-primary mb-1" style="font-size:1.18rem;"><?php echo htmlspecialchars($row['1']); ?></h3>
                        <p class="mb-2 text-muted"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($row['14'] . ', ' . $row['15']); ?></p>
                        <div class="property-features mb-3">
                            <span><i class="fas fa-bed"></i> <?php echo $row['6']; ?></span>
                            <span class="ms-3"><i class="fas fa-bath"></i> <?php echo $row['7']; ?></span>
                            <span class="ms-3"><i class="fas fa-vector-square"></i> <?php echo $row['12']; ?> sq.ft</span>
                        </div>
                        <a href="propertydetail.php?pid=<?php echo $row['0']; ?>" class="btn btn-outline-primary w-100 rounded-pill">View Details</a>
                    </div>
                </div>
            </div>
            <?php
                }
            } else {
            ?>
            <div class="col-12 no-results">
                <h3>No Properties Found</h3>
                <p>We couldn't find any properties matching your criteria. Please try adjusting your filters or browse all properties.</p>
            </div>
            <?php
            }
            ?>
        </div>
        
        <!-- Pagination -->
        <?php if($total_records > 0) { ?>
        <div class="row mt-4">
            <div class="col-12">
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <?php if($page > 1) { ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page-1])); ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        <?php } else { ?>
                        <li class="page-item disabled">
                            <span class="page-link">&laquo;</span>
                        </li>
                        <?php } ?>
                        
                        <?php
                        // Show limited page numbers with ellipsis
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        if($start_page > 1) {
                            echo '<li class="page-item"><a class="page-link" href="?'.http_build_query(array_merge($_GET, ['page' => 1])).'">1</a></li>';
                            if($start_page > 2) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                        }
                        
                        for($i = $start_page; $i <= $end_page; $i++) {
                            if($i == $page) {
                                echo '<li class="page-item active"><span class="page-link">'.$i.'</span></li>';
                            } else {
                                echo '<li class="page-item"><a class="page-link" href="?'.http_build_query(array_merge($_GET, ['page' => $i])).'">'.$i.'</a></li>';
                            }
                        }
                        
                        if($end_page < $total_pages) {
                            if($end_page < $total_pages - 1) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                            echo '<li class="page-item"><a class="page-link" href="?'.http_build_query(array_merge($_GET, ['page' => $total_pages])).'">'.$total_pages.'</a></li>';
                        }
                        ?>
                        
                        <?php if($page < $total_pages) { ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page+1])); ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                        <?php } else { ?>
                        <li class="page-item disabled">
                            <span class="page-link">&raquo;</span>
                        </li>
                        <?php } ?>
                    </ul>
                </nav>
            </div>
        </div>
        <?php } ?>
    </div>
</section>

<!-- Call to Action Section -->
<section class="section-padding bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mb-4 mb-lg-0">
                <h2 class="mb-3">Can't Find What You're Looking For?</h2>
                <p class="mb-0">Contact our expert team to help you find the perfect property that meets all your requirements.</p>
            </div>
            <div class="col-lg-4 text-lg-right">
                <a href="<?php echo $base_url; ?>contact.php" class="btn btn-primary">Contact Us</a>
            </div>
        </div>
    </div>
</section>

<?php
// Include footer
require_once __DIR__ . '/include/updated-common-footer.php';
?>