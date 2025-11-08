<?php
/**
 * Modern Homepage for APS Dream Home
 * Uses the new modern design system
 */

// Include configuration and setup
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/enhanced_universal_template.php';

// Initialize company stats with default values
$company_stats = [
    'properties_listed' => 0,
    'properties_sold' => 0,
    'happy_customers' => 0,
    'expert_agents' => 0,
    'years_experience' => 15,
    'cities_covered' => 0,
    'awards_won' => 25,
    'projects_completed' => 0,
    'client_satisfaction' => 4.8,
    'repeat_customers' => 0
];

// Get company statistics from database if available
try {
    if (isset($pdo)) {
        // Get comprehensive company statistics
        $company_stats_query = "
            SELECT
                (SELECT COUNT(*) FROM properties WHERE status IN ('available', 'sold')) as total_properties,
                (SELECT COUNT(*) FROM properties WHERE status = 'sold') as sold_properties,
                (SELECT COUNT(*) FROM users WHERE role = 'customer' AND status = 'active') as total_customers,
                (SELECT COUNT(*) FROM users WHERE role = 'agent' AND status = 'active') as total_agents,
                (SELECT COUNT(DISTINCT city) FROM properties WHERE city IS NOT NULL AND city != '') as cities_count,
                (SELECT AVG(rating) FROM property_reviews WHERE rating > 0) as avg_rating,
                (SELECT COUNT(*) FROM property_reviews WHERE rating >= 4) as satisfied_customers
        ";

        $company_stmt = $pdo->prepare($company_stats_query);
        $company_stmt->execute();
        $company_data = $company_stmt->fetch(PDO::FETCH_ASSOC);

        if ($company_data) {
            $company_stats = [
                'properties_listed' => (int)($company_data['total_properties'] ?? 0),
                'properties_sold' => (int)($company_data['sold_properties'] ?? 0),
                'happy_customers' => (int)($company_data['total_customers'] ?? 0),
                'expert_agents' => (int)($company_data['total_agents'] ?? 0),
                'years_experience' => 15,
                'cities_covered' => (int)($company_data['cities_count'] ?? 0),
                'awards_won' => 25,
                'projects_completed' => (int)($company_data['sold_properties'] ?? 0) * 3,
                'client_satisfaction' => $company_data['avg_rating'] ? round($company_data['avg_rating'], 1) : 4.8,
                'repeat_customers' => (int)($company_data['satisfied_customers'] ?? 0)
            ];
        }
    }
} catch (PDOException $e) {
    error_log('Company stats query error: ' . $e->getMessage());
    // Keep default values
} catch (Exception $e) {
    error_log('Database error in index_modern.php: ' . $e->getMessage());
}

// Initialize template system
$template = new EnhancedUniversalTemplate();

// Set page metadata
$page_title = "Your Dream Home Awaits in Gorakhpur | APS Dream Home";
$page_description = "Discover exclusive properties with the most trusted real estate platform in Gorakhpur. From luxury apartments to prime commercial spaces, we have it all.";

$template->setTitle($page_title);
$template->setDescription($page_description);

// Load CSS assets with modern design system first
$css_assets = [
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
    'https://unpkg.com/swiper@8/swiper-bundle.min.css',
    'https://unpkg.com/aos@2.3.1/dist/aos.css',
    '/assets/css/modern-design-system.css',
    '/assets/css/home.css',
    '/assets/css/modern-style.css',
    '/assets/css/custom-styles.css'
];

foreach ($css_assets as $css) {
    $template->addCSS($css);
}

// Add JavaScript assets
$js_assets = [
    'https://code.jquery.com/jquery-3.6.0.min.js',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
    'https://unpkg.com/swiper@8/swiper-bundle.min.js',
    'https://unpkg.com/aos@2.3.1/dist/aos.js',
    '/assets/js/main.js'
];

foreach ($js_assets as $js) {
    $template->addJS($js);
}

// Define current URL for Open Graph tags
$current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

// Add comprehensive meta tags
$template->addMeta('keywords', 'real estate, property, home for sale, apartment, villa, plot, commercial, Gorakhpur, Uttar Pradesh, India');
$template->addMeta('author', 'APS Dream Home');
$template->addMeta('viewport', 'width=device-width, initial-scale=1.0, shrink-to-fit=no');
$template->addMeta('robots', 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1');
$template->addMeta('theme-color', '#0d6efd');
$template->addMeta('msapplication-TileColor', '#0d6efd');

// Open Graph meta tags for social media
$template->addMeta('og:title', $page_title);
$template->addMeta('og:description', $page_description);
$template->addMeta('og:image', BASE_URL . 'assets/images/og-image.jpg');
$template->addMeta('og:url', $current_url);
$template->addMeta('og:type', 'website');
$template->addMeta('og:site_name', 'APS Dream Home');
$template->addMeta('og:locale', 'en_IN');

// Twitter Card meta tags
$template->addMeta('twitter:card', 'summary_large_image');
$template->addMeta('twitter:title', $page_title);
$template->addMeta('twitter:description', $page_description);
$template->addMeta('twitter:image', BASE_URL . 'assets/images/og-image.jpg');

// Generate canonical URL
$canonical_url = BASE_URL . 'index.php';
$template->addMeta('canonical', $canonical_url, 'rel');

// Set content type and charset header
header('Content-Type: text/html; charset=UTF-8');

// Start output buffering for content
ob_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta tags are handled by the template -->
</head>
<body class="modern-body">

<!-- Modern Hero Section -->
<section class="modern-hero-section">
    <!-- Background Slider -->
    <div class="hero-bg">
        <div class="hero-slider swiper">
            <div class="swiper-wrapper">
                <div class="swiper-slide" style="background-image: url('assets/images/hero-1.jpg');"></div>
                <div class="swiper-slide" style="background-image: url('assets/images/hero-2.jpg');"></div>
                <div class="swiper-slide" style="background-image: url('assets/images/hero-3.jpg');"></div>
            </div>
            <div class="swiper-pagination"></div>
        </div>
        <div class="hero-overlay"></div>
    </div>
    
    <div class="container">
        <div class="row align-items-center min-h-screen-70">
            <div class="col-lg-6 mb-8 mb-lg-0">
                <div class="hero-content" data-aos="fade-right">
                    <!-- Trust Badge -->
                    <div class="trust-badge" data-aos="fade-up" data-aos-delay="100">
                        <span class="badge bg-primary-soft text-primary px-4 py-2 rounded-full d-inline-flex align-items-center gap-2">
                            <i class="fas fa-star text-primary"></i>Trusted by 10,000+ Clients
                        </span>
                    </div>
                    
                    <!-- Main Title -->
                    <h1 class="hero-title text-white mb-6" data-aos="fade-up" data-aos-delay="150">
                        Your Dream Home 
                        <span class="text-gradient-primary">Awaits</span> 
                        in Gorakhpur
                    </h1>
                    
                    <!-- Subtitle -->
                    <p class="hero-subtitle text-white-80 mb-8" data-aos="fade-up" data-aos-delay="200">
                        Discover exclusive properties with the most trusted real estate platform in Gorakhpur. 
                        From luxury apartments to prime commercial spaces, we have it all.
                    </p>

                    <!-- Call-to-Action Buttons -->
                    <div class="d-flex flex-wrap gap-4 mb-8" data-aos="fade-up" data-aos-delay="250">
                        <a href="#featured-properties" class="btn btn-primary btn-lg px-6 py-4 d-inline-flex align-items-center gap-3">
                            <i class="fas fa-home"></i>
                            Explore Properties
                        </a>
                        <a href="#contact" class="btn btn-outline-light btn-lg px-6 py-4 d-inline-flex align-items-center gap-3">
                            <i class="fas fa-phone-alt"></i>
                            Contact Agent
                        </a>
                    </div>

                    <!-- Trust Indicators -->
                    <div class="trust-indicators" data-aos="fade-up" data-aos-delay="300">
                        <div class="d-flex align-items-center flex-wrap gap-6">
                            <!-- Happy Clients -->
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-group">
                                    <img src="https://randomuser.me/api/portraits/women/32.jpg" class="avatar avatar-sm" alt="Client">
                                    <img src="https://randomuser.me/api/portraits/men/44.jpg" class="avatar avatar-sm" alt="Client">
                                    <img src="https://randomuser.me/api/portraits/women/68.jpg" class="avatar avatar-sm" alt="Client">
                                </div>
                                <div class="trust-info">
                                    <div class="text-white font-semibold">5,000+</div>
                                    <div class="text-white-60 text-sm">Happy Clients</div>
                                </div>
                            </div>

                            <!-- Divider -->
                            <div class="divider-vertical bg-white-30 d-none d-md-block"></div>

                            <!-- Ratings -->
                            <div class="d-flex align-items-center gap-3">
                                <div class="rating-stars">
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star-half-alt text-warning"></i>
                                </div>
                                <div class="rating-info">
                                    <div class="text-white-60 text-sm">4.8/5 Rating</div>
                                    <div class="text-white-60 text-xs">(2,500+ Reviews)</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modern Search Card -->
            <div class="col-lg-6" data-aos="fade-left">
                <div class="modern-search-card glass-card">
                    <div class="search-header text-center mb-8">
                        <div class="search-icon mb-4">
                            <div class="icon-wrapper bg-primary-gradient">
                                <i class="fas fa-search-location text-white"></i>
                            </div>
                        </div>
                        <h3 class="search-title text-gray-900 mb-3 fw-bold">
                            Find Your Dream Home
                        </h3>
                        <p class="search-subtitle text-gray-600">
                            Discover premium properties with our intelligent search
                        </p>
                    </div>

                    <form action="properties.php" method="GET" class="modern-search-form">
                        <div class="search-grid">
                            <!-- Property Type -->
                            <div class="search-group" data-aos="fade-up" data-aos-delay="50">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-building text-primary me-2"></i>
                                    Property Type
                                </label>
                                <div class="select-wrapper">
                                    <select class="form-select modern-select" name="type">
                                        <option value="">All Types</option>
                                        <option value="apartment">Apartment</option>
                                        <option value="villa">Villa</option>
                                        <option value="house">Independent House</option>
                                        <option value="plot">Plot/Land</option>
                                        <option value="commercial">Commercial</option>
                                    </select>
                                    <i class="select-arrow fas fa-chevron-down"></i>
                                </div>
                            </div>

                            <!-- Location -->
                            <div class="search-group" data-aos="fade-up" data-aos-delay="100">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                    Location
                                </label>
                                <div class="select-wrapper">
                                    <select class="form-select modern-select" name="location" id="location-select">
                                        <option value="">All Locations</option>
                                        <?php 
                                        $current_state = '';
                                        foreach ($locations as $location): 
                                            if ($location['state'] !== $current_state) {
                                                if ($current_state !== '') echo '</optgroup>';
                                                echo '<optgroup label="' . htmlspecialchars($location['state']) . '">';
                                                $current_state = $location['state'];
                                            }
                                        ?>
                                            <option value="<?php echo htmlspecialchars($location['city']); ?>">
                                                <?php echo htmlspecialchars($location['city']); ?>
                                            </option>
                                        <?php 
                                        endforeach; 
                                        if ($current_state !== '') echo '</optgroup>';
                                        ?>
                                    </select>
                                    <i class="select-arrow fas fa-chevron-down"></i>
                                </div>
                            </div>

                            <!-- Price Range -->
                            <div class="search-group" data-aos="fade-up" data-aos-delay="150">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-indian-rupee-sign text-primary me-2"></i>
                                    Price Range
                                </label>
                                <div class="price-range-container">
                                    <div class="price-slider" id="price-slider"></div>
                                    <div class="price-inputs">
                                        <div class="price-input-wrapper">
                                            <span class="price-prefix">₹</span>
                                            <input type="text" id="min-price" name="min_price" class="form-control price-input" placeholder="Min" readonly>
                                        </div>
                                        <span class="price-separator">-</span>
                                        <div class="price-input-wrapper">
                                            <span class="price-prefix">₹</span>
                                            <input type="text" id="max-price" name="max_price" class="form-control price-input" placeholder="Max" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Bedrooms -->
                            <div class="search-group" data-aos="fade-up" data-aos-delay="200">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-bed text-primary me-2"></i>
                                    Bedrooms
                                </label>
                                <div class="select-wrapper">
                                    <select class="form-select modern-select" name="bedrooms">
                                        <option value="">Any</option>
                                        <option value="1">1 BHK</option>
                                        <option value="2">2 BHK</option>
                                        <option value="3">3 BHK</option>
                                        <option value="4">4+ BHK</option>
                                    </select>
                                    <i class="select-arrow fas fa-chevron-down"></i>
                                </div>
                            </div>

                            <!-- Additional Filters -->
                            <div class="search-group" data-aos="fade-up" data-aos-delay="250">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-ruler-combined text-primary me-2"></i>
                                    Area (sq ft)
                                </label>
                                <div class="area-inputs">
                                    <input type="number" name="min_area" class="form-control" placeholder="Min Area" min="0">
                                    <input type="number" name="max_area" class="form-control" placeholder="Max Area" min="0">
                                </div>
                            </div>

                            <!-- Property Status -->
                            <div class="search-group" data-aos="fade-up" data-aos-delay="300">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-tag text-primary me-2"></i>
                                    Status
                                </label>
                                <div class="select-wrapper">
                                    <select class="form-select modern-select" name="status">
                                        <option value="">All Status</option>
                                        <option value="available">Available</option>
                                        <option value="sold">Sold</option>
                                        <option value="reserved">Reserved</option>
                                    </select>
                                    <i class="select-arrow fas fa-chevron-down"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Advanced Search Toggle -->
                        <div class="advanced-search-toggle text-center mb-4" data-aos="fade-up" data-aos-delay="350">
                            <button type="button" class="btn btn-link text-primary text-decoration-none" id="toggle-advanced-search">
                                <i class="fas fa-cog me-2"></i>
                                Advanced Filters
                            </button>
                        </div>

                        <!-- Advanced Search Options -->
                        <div class="advanced-search-options" id="advanced-search-options" style="display: none;">
                            <div class="search-grid">
                                <!-- Amenities -->
                                <div class="search-group">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-concierge-bell text-primary me-2"></i>
                                        Amenities
                                    </label>
                                    <div class="amenities-checkboxes">
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="amenities[]" value="parking">
                                            <span class="checkmark"></span>
                                            Parking
                                        </label>
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="amenities[]" value="garden">
                                            <span class="checkmark"></span>
                                            Garden
                                        </label>
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="amenities[]" value="pool">
                                            <span class="checkmark"></span>
                                            Pool
                                        </label>
                                    </div>
                                </div>

                                <!-- Furnishing -->
                                <div class="search-group">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-couch text-primary me-2"></i>
                                        Furnishing
                                    </label>
                                    <div class="select-wrapper">
                                        <select class="form-select modern-select" name="furnishing">
                                            <option value="">Any</option>
                                            <option value="furnished">Furnished</option>
                                            <option value="semi-furnished">Semi-Furnished</option>
                                            <option value="unfurnished">Unfurnished</option>
                                        </select>
                                        <i class="select-arrow fas fa-chevron-down"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary-gradient btn-lg w-100 search-btn" data-aos="fade-up" data-aos-delay="400">
                            <i class="fas fa-search me-2"></i>
                            Search Properties
                        </button>

                        <!-- Quick Actions -->
                        <div class="quick-actions mt-4 text-center" data-aos="fade-up" data-aos-delay="450">
                            <div class="d-flex justify-content-center gap-3">
                                <a href="#" class="text-primary text-decoration-none small">
                                    <i class="fas fa-heart me-1"></i>Save Search
                                </a>
                                <span class="text-muted">•</span>
                                <a href="#" class="text-primary text-decoration-none small">
                                    <i class="fas fa-bell me-1"></i>Get Alerts
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modern Featured Properties Section -->
<section id="featured-properties" class="modern-featured-properties-section py-8 bg-gray-50">
    <div class="container">
        <div class="section-header text-center mb-8" data-aos="fade-up">
            <h2 class="section-title text-gray-900 mb-4">
                <i class="fas fa-home text-primary me-3"></i>Featured Properties
            </h2>
            <p class="section-subtitle text-gray-600" data-aos="fade-up" data-aos-delay="100">
                Handpicked premium properties for discerning buyers
            </p>
        </div>

        <?php if (empty($featured_properties) || !isset($featured_properties)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="empty-state text-center py-8" data-aos="fade-up">
                        <div class="empty-icon mb-4">
                            <i class="fas fa-home fa-4x text-gray-300"></i>
                        </div>
                        <h4 class="text-gray-500 mb-2">No featured properties available</h4>
                        <p class="text-gray-400 mb-6">Please check back later for new property listings or browse all available properties.</p>
                        <div class="empty-actions">
                            <a href="properties_template.php" class="btn btn-primary me-3">
                                <i class="fas fa-search me-2"></i>View All Properties
                            </a>
                            <a href="#contact" class="btn btn-outline-primary">
                                <i class="fas fa-phone me-2"></i>Contact Us
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="modern-properties-grid">
                <?php foreach ($featured_properties as $index => $property):
                    // Enhanced property data validation
                    $property_id = $property['id'] ?? 0;
                    $property_title = $property['title'] ?? 'Untitled Property';
                    $property_price = $property['price'] ?? 0;
                    $property_address = $property['address'] ?? 'Location not specified';
                    $property_type = $property['property_type'] ?? 'Property';
                    $property_status = $property['status'] ?? 'available';
                    $agent_name = $property['agent_name'] ?? 'Agent';
                    $main_image = $property['main_image'] ?? '';

                    // Determine status styling
                    $status_class = ($property_status === 'available') ? 'success' : 'secondary';
                    $status_text = ucfirst($property_status);

                    // Format price with validation
                    if ($property_price > 0) {
                        $formatted_price = '₹' . number_format($property_price);
                    } else {
                        $formatted_price = 'Price on Request';
                    }

                    // Generate fallback image if none exists
                    if (empty($main_image)) {
                        $main_image = 'https://via.placeholder.com/800x600/667eea/ffffff?text=' . urlencode($property_title);
                    }
                ?>
                    <div class="modern-property-card" data-aos="fade-up" data-aos-delay="<?php echo ($index * 100); ?>">
                        <div class="modern-property-card-inner">
                            <div class="modern-property-image-container">
                                <div class="modern-property-badges">
                                    <span class="badge bg-<?php echo $status_class; ?>-soft text-<?php echo $status_class; ?> status-badge">
                                        <i class="fas fa-check-circle me-1"></i><?php echo $status_text; ?>
                                    </span>
                                    <span class="badge bg-primary-soft text-primary type-badge">
                                        <i class="fas fa-building me-1"></i><?php echo htmlspecialchars($property_type); ?>
                                    </span>
                                    <?php if (isset($property['featured_until']) && !empty($property['featured_until'])): ?>
                                        <span class="badge bg-warning-soft text-warning featured-badge">
                                            <i class="fas fa-star me-1"></i>Featured
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <!-- Property Image with Error Handling -->
                                <img src="<?php echo htmlspecialchars($main_image); ?>"
                                     alt="<?php echo htmlspecialchars($property_title); ?>"
                                     class="modern-property-image"
                                     loading="lazy"
                                     onerror="this.src='https://via.placeholder.com/800x600/667eea/ffffff?text=Property+Image'">

                                <!-- Property Overlay Actions -->
                                <div class="modern-property-overlay">
                                    <div class="modern-property-actions">
                                        <button class="btn btn-action" onclick="toggleFavorite(<?php echo $property_id; ?>)" title="Add to favorites">
                                            <i class="far fa-heart"></i>
                                        </button>
                                        <a href="property_details.php?id=<?php echo $property_id; ?>" class="btn btn-action" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button class="btn btn-action" onclick="shareProperty(<?php echo $property_id; ?>)" title="Share Property">
                                            <i class="fas fa-share-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="modern-property-content">
                                <div class="modern-property-header">
                                    <h3 class="modern-property-title">
                                        <a href="property_details.php?id=<?php echo $property_id; ?>" class="modern-property-link">
                                            <?php echo htmlspecialchars($property_title); ?>
                                        </a>
                                    </h3>
                                    <div class="modern-property-price h5 text-primary mb-0"><?php echo $formatted_price; ?></div>
                                </div>

                                <div class="modern-property-location">
                                    <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                    <span class="text-gray-600"><?php echo htmlspecialchars($property_address); ?></span>
                                </div>

                                <div class="modern-property-features">
                                    <div class="modern-feature-item">
                                        <i class="fas fa-bed text-primary"></i>
                                        <span class="text-gray-700"><?php echo $property['bedrooms'] ?? 0; ?> <small>Beds</small></span>
                                    </div>
                                    <div class="modern-feature-item">
                                        <i class="fas fa-bath text-primary"></i>
                                        <span class="text-gray-700"><?php echo $property['bathrooms'] ?? 0; ?> <small>Baths</small></span>
                                    </div>
                                    <div class="modern-feature-item">
                                        <i class="fas fa-ruler-combined text-primary"></i>
                                        <span class="text-gray-700"><?php echo number_format($property['area_sqft'] ?? 0); ?> <small>sq.ft</small></span>
                                    </div>
                                    <div class="modern-feature-item">
                                        <i class="fas fa-layer-group text-primary"></i>
                                        <span class="text-gray-700"><?php echo $property['floors'] ?? 1; ?> <small>Floors</small></span>
                                    </div>
                                </div>

                                <div class="modern-property-footer">
                                    <div class="modern-property-agent">
                                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($agent_name); ?>&background=667eea&color=ffffff&size=40"
                                             alt="<?php echo htmlspecialchars($agent_name); ?>"
                                             class="modern-agent-avatar"
                                             onerror="this.src='https://ui-avatars.com/api/?name=Agent&background=6c757d&color=ffffff&size=40'">
                                        <div class="modern-agent-info">
                                            <div class="modern-agent-name text-gray-900"><?php echo htmlspecialchars($agent_name); ?></div>
                                            <div class="modern-agent-company text-gray-400 small">APS Dream Home</div>
                                        </div>
                                    </div>
                                    <a href="property_details.php?id=<?php echo $property_id; ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-arrow-right me-1"></i>View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Load More Button -->
            <div class="text-center mt-8" data-aos="fade-up">
                <a href="properties_template.php" class="btn btn-outline-primary btn-lg px-6 py-3">
                    <i class="fas fa-arrow-right me-2"></i> View All Properties
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Modern Achievements & Stats Section -->
<section id="achievements" class="modern-achievements-section py-8 bg-white">
    <div class="container">
        <div class="section-header text-center mb-8" data-aos="fade-up">
            <h2 class="section-title text-gray-900 mb-4">
                <i class="fas fa-trophy text-primary me-3"></i>Our Achievements
            </h2>
            <p class="section-subtitle text-gray-600" data-aos="fade-up" data-aos-delay="100">
                Building trust through excellence and experience
            </p>
        </div>

        <!-- Company Highlights -->
        <div class="modern-highlights-grid mb-8" data-aos="fade-up">
            <div class="modern-highlight-card">
                <div class="highlight-icon">
                    <i class="fas fa-calendar-alt text-primary"></i>
                </div>
                <div class="highlight-content">
                    <h3 class="highlight-value text-primary"><?php echo $company_stats['years_experience']; ?>+</h3>
                    <p class="highlight-label text-gray-600">Years of Experience</p>
                </div>
            </div>

            <div class="modern-highlight-card">
                <div class="highlight-icon">
                    <i class="fas fa-city text-primary"></i>
                </div>
                <div class="highlight-content">
                    <h3 class="highlight-value text-primary"><?php echo $company_stats['cities_covered']; ?>+</h3>
                    <p class="highlight-label text-gray-600">Cities Covered</p>
                </div>
            </div>

            <div class="modern-highlight-card">
                <div class="highlight-icon">
                    <i class="fas fa-award text-primary"></i>
                </div>
                <div class="highlight-content">
                    <h3 class="highlight-value text-primary"><?php echo $company_stats['awards_won']; ?>+</h3>
                    <p class="highlight-label text-gray-600">Awards Won</p>
                </div>
            </div>

            <div class="modern-highlight-card">
                <div class="highlight-icon">
                    <i class="fas fa-smile text-primary"></i>
                </div>
                <div class="highlight-content">
                    <h3 class="highlight-value text-primary"><?php echo $company_stats['client_satisfaction']; ?>/5</h3>
                    <p class="highlight-label text-gray-600">Client Satisfaction</p>
                </div>
            </div>
        </div>

        <!-- Detailed Statistics -->
        <div class="modern-stats-grid" data-aos="fade-up" data-aos-delay="200">
            <div class="modern-stat-card">
                <div class="stat-icon">
                    <i class="fas fa-home text-primary"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value text-gray-900"><?php echo number_format($company_stats['properties_listed']); ?></h3>
                    <p class="stat-label text-gray-600">Properties Listed</p>
                </div>
            </div>

            <div class="modern-stat-card">
                <div class="stat-icon">
                    <i class="fas fa-check-circle text-success"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value text-gray-900"><?php echo number_format($company_stats['properties_sold']); ?></h3>
                    <p class="stat-label text-gray-600">Properties Sold</p>
                </div>
            </div>

            <div class="modern-stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users text-primary"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value text-gray-900"><?php echo number_format($company_stats['happy_customers']); ?></h3>
                    <p class="stat-label text-gray-600">Happy Customers</p>
                </div>
            </div>

            <div class="modern-stat-card">
                <div class="stat-icon">
                    <i class="fas fa-user-tie text-primary"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value text-gray-900"><?php echo number_format($company_stats['expert_agents']); ?></h3>
                    <p class="stat-label text-gray-600">Expert Agents</p>
                </div>
            </div>

            <div class="modern-stat-card">
                <div class="stat-icon">
                    <i class="fas fa-building text-primary"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value text-gray-900"><?php echo number_format($company_stats['projects_completed']); ?></h3>
                    <p class="stat-label text-gray-600">Projects Completed</p>
                </div>
            </div>

            <div class="modern-stat-card">
                <div class="stat-icon">
                    <i class="fas fa-redo text-primary"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value text-gray-900"><?php echo number_format($company_stats['repeat_customers']); ?></h3>
                    <p class="stat-label text-gray-600">Repeat Customers</p>
                </div>
            </div>
        </div>

        <!-- Call to Action -->
        <div class="text-center mt-8" data-aos="fade-up" data-aos-delay="300">
            <div class="cta-content">
                <h3 class="cta-title text-gray-900 mb-4">Ready to Find Your Dream Home?</h3>
                <p class="cta-subtitle text-gray-600 mb-6">Join thousands of satisfied customers who found their perfect property with us</p>
                <div class="cta-actions">
                    <a href="#featured-properties" class="btn btn-primary btn-lg px-6 py-3 me-4">
                        <i class="fas fa-home me-2"></i>Browse Properties
                    </a>
                    <a href="#contact" class="btn btn-outline-primary btn-lg px-6 py-3">
                        <i class="fas fa-phone me-2"></i>Get Consultation
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Get the buffered content
$content = ob_get_clean();

// Render the template with the content using renderPage method
$template->renderPage($content, $page_title);
?>