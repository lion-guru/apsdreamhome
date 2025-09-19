<?php
/**
 * APS Dream Home - Main Index File
 * 
 * This is the main entry point for the APS Dream Home website.
 * Combines the best features from all index versions with modern practices.
 */

// Start output buffering
ob_start();

// Define application start time for performance measurement
$start_time = microtime(true);

// Set error reporting based on environment
if (getenv('ENVIRONMENT') === 'development') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(E_ALL & ~E_DEPRECATED);
}

// Define application paths
define('APP_ROOT', __DIR__);
define('INCLUDES_DIR', APP_ROOT . '/includes');

// Load configuration and functions
require_once INCLUDES_DIR . '/config/config.php';
require_once INCLUDES_DIR . '/functions/common.php';
require_once INCLUDES_DIR . '/functions/template.php';
require_once INCLUDES_DIR . '/models/PropertyModel.php';

// Start secure session
start_secure_session(SESSION_NAME);

// Set default timezone
date_default_timezone_set('Asia/Kolkata');

// Load database configuration first
require_once INCLUDES_DIR . '/config/DatabaseConfig.php';

// Initialize database connection
try {
    // Initialize the database configuration
    DatabaseConfig::init();
    
    // Get the database connection
    $conn = DatabaseConfig::getConnection();
    
    if (!$conn) {
        throw new Exception('Failed to establish database connection');
    }
    
    // Set charset to ensure proper encoding
    $conn->set_charset('utf8mb4');
    
    log_message('Database connection established successfully', 'info');
} catch (Exception $e) {
    log_message('Database connection failed: ' . $e->getMessage(), 'error');
    http_response_code(503);
    // Try to include the error template, but don't fail if it doesn't exist
    $error_template = INCLUDES_DIR . '/templates/errors/503.php';
    if (file_exists($error_template)) {
        include $error_template;
    } else {
        echo '<!DOCTYPE html><html><head><title>Service Unavailable</title></head><body><h1>Service Unavailable</h1><p>The server is temporarily unable to service your request. Please try again later.</p></body></html>';
    }
    exit;
}

// Get property and agent counts with caching
function get_property_counts($conn) {
    $counts = [
        'total_properties' => 0,
        'sold_properties' => 0,
        'rental_properties' => 0,
        'total_agents' => 0
    ];

    try {
        // Get total properties
        $query = "SELECT COUNT(*) as count FROM properties WHERE status = 'active'";
        $result = $conn->query($query);
        if ($result) {
            $row = $result->fetch_assoc();
            $counts['total_properties'] = (int)$row['count'];
        }

        // Get sold properties
        $query = "SELECT COUNT(*) as count FROM properties WHERE status = 'sold' AND sale_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)";
        $result = $conn->query($query);
        if ($result) {
            $row = $result->fetch_assoc();
            $counts['sold_properties'] = (int)$row['count'];
        }

        // Get rental properties
        $query = "SELECT COUNT(*) as count FROM properties WHERE property_type = 'rental' AND status = 'active'";
        $result = $conn->query($query);
        if ($result) {
            $row = $result->fetch_assoc();
            $counts['rental_properties'] = (int)$row['count'];
        }

        // Get total agents
        $query = "SELECT COUNT(*) as count FROM users WHERE role = 'agent' AND status = 'active'";
        $result = $conn->query($query);
        if ($result) {
            $row = $result->fetch_assoc();
            $counts['total_agents'] = (int)$row['count'];
        }
    } catch (Exception $e) {
        error_log('Error getting property counts: ' . $e->getMessage());
    }

    return $counts;
}

// Get counts
$counts = get_property_counts($conn);

// Get featured properties
try {
    $propertyModel = new PropertyModel($conn);
    $featured_properties = $propertyModel->getFeaturedProperties(6);
} catch (Exception $e) {
    log_message('Error fetching featured properties: ' . $e->getMessage(), 'error');
    $featured_properties = [];
}

// Start output buffering for the main content
ob_start();
?>
<!DOCTYPE html>
<html lang="en" class="no-js">
<head>
    <!-- Primary Meta Tags -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Find your dream home with APS Dream Home. Browse our exclusive collection of properties for sale and rent in prime locations.">
    <meta name="keywords" content="real estate, property, home for sale, rent, apartments, houses, APS Dream Home">
    <meta name="author" content="APS Dream Home">
    <meta name="robots" content="index, follow">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo SITE_URL; ?>/">
    <meta property="og:title" content="APS Dream Home | Find Your Dream Property">
    <meta property="og:description" content="Discover exclusive properties for sale and rent in prime locations. Your dream home is just a click away!">
    <meta property="og:image" content="<?php echo SITE_URL; ?>/assets/images/og-image.jpg">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?php echo SITE_URL; ?>/">
    <meta name="twitter:title" content="APS Dream Home | Find Your Dream Property">
    <meta name="twitter:description" content="Discover exclusive properties for sale and rent in prime locations. Your dream home is just a click away!">
    <meta name="twitter:image" content="<?php echo SITE_URL; ?>/assets/images/og-image.jpg">

    <title>APS Dream Home | Find Your Dream Property</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo SITE_URL; ?>/assets/images/favicon.png">
    <link rel="apple-touch-icon" href="<?php echo SITE_URL; ?>/assets/images/apple-touch-icon.png">

    <!-- Preload Critical CSS -->
    <link rel="preload" href="<?php echo SITE_URL; ?>/assets/css/bootstrap.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/bootstrap.min.css"></noscript>
    
    <!-- Main CSS -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/plugins/font-awesome/css/all.min.css"
          onerror="this.onerror=null; this.href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Critical CSS (inlined) -->
    <style>
        /* Critical CSS for above-the-fold content */
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #fd7e14;
            --dark-color: #212529;
            --light-color: #f8f9fa;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: #333;
            overflow-x: hidden;
        }
        
        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), 
                        url('<?php echo SITE_URL; ?>/assets/images/hero/home-hero.jpg') no-repeat center center/cover;
            min-height: 80vh;
            display: flex;
            align-items: center;
            position: relative;
            color: #fff;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
        }
        
        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }
        
        .hero-subtitle {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            .hero-subtitle {
                font-size: 1.1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include INCLUDES_DIR . '/templates/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h1 class="hero-title">Find Your Dream Home</h1>
                    <p class="hero-subtitle">Discover the perfect property that matches your lifestyle and budget</p>
                    
                    <!-- Property Search Form -->
                    <div class="search-container bg-white p-4 rounded shadow">
                        <form action="properties.php" method="GET" class="row g-3">
                            <div class="col-md-4">
                                <select name="type" class="form-select">
                                    <option value="">Property Type</option>
                                    <option value="house">House</option>
                                    <option value="apartment">Apartment</option>
                                    <option value="villa">Villa</option>
                                    <option value="plot">Plot</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select name="purpose" class="form-select">
                                    <option value="sale">For Sale</option>
                                    <option value="rent">For Rent</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-2"></i> Search Properties
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row g-4 text-center">
                <div class="col-6 col-md-3">
                    <div class="stat-card p-4 bg-white rounded shadow-sm">
                        <div class="stat-icon mb-3">
                            <i class="fas fa-home fa-2x text-primary"></i>
                        </div>
                        <h3 class="stat-number"><?php echo number_format($counts['total_properties']); ?>+</h3>
                        <p class="stat-label mb-0">Properties</p>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card p-4 bg-white rounded shadow-sm">
                        <div class="stat-icon mb-3">
                            <i class="fas fa-key fa-2x text-primary"></i>
                        </div>
                        <h3 class="stat-number"><?php echo number_format($counts['sold_properties']); ?>+</h3>
                        <p class="stat-label mb-0">Recently Sold</p>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card p-4 bg-white rounded shadow-sm">
                        <div class="stat-icon mb-3">
                            <i class="fas fa-building fa-2x text-primary"></i>
                        </div>
                        <h3 class="stat-number"><?php echo number_format($counts['rental_properties']); ?>+</h3>
                        <p class="stat-label mb-0">For Rent</p>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card p-4 bg-white rounded shadow-sm">
                        <div class="stat-icon mb-3">
                            <i class="fas fa-user-tie fa-2x text-primary"></i>
                        </div>
                        <h3 class="stat-number"><?php echo number_format($counts['total_agents']); ?>+</h3>
                        <p class="stat-label mb-0">Agents</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Properties -->
    <section class="py-5">
        <div class="container">
            <div class="section-title text-center mb-5">
                <span class="subtitle text-primary">EXCLUSIVE PROPERTIES</span>
                <h2 class="fw-bold">Featured Properties</h2>
                <div class="title-line mx-auto"></div>
                <p class="text-muted">Discover our handpicked selection of premium properties</p>
            </div>

            <div class="row g-4">
                <?php if (empty($featured_properties)): ?>
                    <div class="col-12 text-center py-5">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> No properties available at the moment. Please check back later.
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($featured_properties as $property): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="property-card h-100">
                                <div class="property-image">
                                    <img src="<?php echo !empty($property['main_image']) ? htmlspecialchars($property['main_image']) : SITE_URL . '/assets/images/property-placeholder.jpg'; ?>" 
                                         alt="<?php echo htmlspecialchars($property['title']); ?>" class="img-fluid">
                                    <div class="property-badge">
                                        <span class="badge bg-primary"><?php echo ucfirst($property['status']); ?></span>
                                    </div>
                                </div>
                                <div class="property-details p-3">
                                    <h3 class="h5">
                                        <a href="property-details.php?id=<?php echo (int)$property['id']; ?>">
                                            <?php echo htmlspecialchars($property['title']); ?>
                                        </a>
                                    </h3>
                                    <p class="text-muted">
                                        <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                        <?php echo htmlspecialchars($property['location']); ?>
                                    </p>
                                    <div class="property-features d-flex justify-content-between mb-3">
                                        <span><i class="fas fa-bed me-1"></i> <?php echo (int)$property['bedrooms']; ?> Beds</span>
                                        <span><i class="fas fa-bath me-1"></i> <?php echo (int)$property['bathrooms']; ?> Baths</span>
                                        <span><i class="fas fa-vector-square me-1"></i> <?php echo number_format((float)$property['area']); ?> sq.ft</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h4 class="h5 mb-0 text-primary">
                                            <?php echo !empty($property['price']) ? '₹' . number_format((float)$property['price']) : 'Contact for Price'; ?>
                                        </h4>
                                        <a href="property-details.php?id=<?php echo (int)$property['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <?php if (!empty($featured_properties)): ?>
                <div class="text-center mt-5">
                    <a href="properties.php" class="btn btn-primary btn-lg px-5">
                        View All Properties <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-5 bg-primary text-white">
        <div class="container text-center">
            <h2 class="mb-4">Looking to Buy or Rent a Property?</h2>
            <p class="lead mb-4">Our expert agents are ready to help you find your dream home.</p>
            <a href="contact.php" class="btn btn-light btn-lg">Contact Us Today</a>
        </div>
    </section>

    <!-- Footer -->
    <?php include INCLUDES_DIR . '/templates/footer.php'; ?>

    <!-- JavaScript Libraries -->
    <script src="<?php echo SITE_URL; ?>/assets/js/jquery.min.js"
            onerror="this.onerror=null; this.src='https://code.jquery.com/jquery-3.6.0.min.js'"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/bootstrap.bundle.min.js"
            onerror="this.onerror=null; this.src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'"></script>
    
    <!-- Custom JS -->
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    
    <script>
        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Back to top button
            var backToTopButton = document.getElementById('backToTop');
            if (backToTopButton) {
                window.addEventListener('scroll', function() {
                    if (window.pageYOffset > 300) {
                        backToTopButton.style.display = 'block';
                    } else {
                        backToTopButton.style.display = 'none';
                    }
                });
                
                backToTopButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    window.scrollTo({top: 0, behavior: 'smooth'});
                });
            }
        });
    </script>
</body>
</html>
<?php
// Get the buffered content and clean the buffer
$content = ob_get_clean();

// Output the content
echo $content;

// Close database connection
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}

// Log page generation time
$end_time = microtime(true);
$generation_time = round(($end_time - $start_time) * 1000, 2);
log_message("Page generated in {$generation_time}ms", 'info');
?>
