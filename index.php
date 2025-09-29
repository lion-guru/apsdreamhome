<?php
/**
 * APS Dream Home - Modern Homepage
 * Enhanced with modern UI/UX design
 */

// Define security constant for database connection
define('INCLUDED_FROM_MAIN', true);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include the enhanced universal template system
require_once 'includes/enhanced_universal_template.php';

// Create template instance
$template = new EnhancedUniversalTemplate();

// Set page metadata
$template->setTitle('APS Dream Home - Find Your Perfect Property');
$template->setDescription('APS Dream Home - Premium real estate platform for buying, selling, and renting properties in Gorakhpur and across India');
// Add modern CSS
$template->addCSS('assets/css/modern-style.css');
$template->addCSS('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');
$template->addCSS('assets/css/custom-styles.css');

// Add modern JavaScript
$template->addJS('https://unpkg.com/aos@2.3.1/dist/aos.js');
$template->addJS('https://cdn.jsdelivr.net/npm/vanilla-lazyload@17.8.3/dist/lazyload.min.js');
$template->addJS('assets/js/custom.js');

// Include database connection
require_once 'includes/db_connection.php';

// Initialize variables
$featured_properties = [];
$stats = [
    'properties' => 0, 
    'customers' => 0, 
    'agents' => 0, 
    'revenue' => 0
];

// Get database connection and fetch data
try {
    if (isset($pdo) && $pdo) {
        // Set default timezone
        date_default_timezone_set('Asia/Kolkata');

        // Define base URL
        $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/' . basename(dirname(__FILE__));
        define('BASE_URL', rtrim($base_url, '/'));

        // Fetch featured properties with improved query
        $query = "
            SELECT p.id, p.title, p.address, p.price, p.bedrooms, p.bathrooms, p.area_sqft, 
                   p.status, p.description, p.created_at, p.city, p.state,
                   u.name as agent_name, u.phone as agent_phone, u.email as agent_email,
                   pt.name as property_type,
                   (SELECT pi.image_path FROM property_images pi 
                    WHERE pi.property_id = p.id 
                    ORDER BY pi.is_primary DESC, pi.sort_order ASC LIMIT 1) as main_image
            FROM properties p
            LEFT JOIN users u ON p.created_by = u.id
            LEFT JOIN property_types pt ON p.property_type_id = pt.id
            WHERE p.status = 'available' AND p.featured = 1
            ORDER BY p.created_at DESC
            LIMIT 6
        ";
        $result = $pdo->query($query);
        if ($result) {
            $featured_properties = $result->fetchAll(PDO::FETCH_ASSOC);
        }

        // Fetch distinct locations for search dropdown
        $location_query = "SELECT DISTINCT city, state FROM properties 
                          WHERE city IS NOT NULL AND city != '' 
                          ORDER BY state, city";
        $location_result = $pdo->query($location_query);
        $locations = [];
        if ($location_result) {
            $locations = $location_result->fetchAll(PDO::FETCH_ASSOC);
        }

        // Get statistics
        $stats_query = "
            SELECT 
                (SELECT COUNT(*) FROM properties WHERE status = 'available') as properties,
                (SELECT COUNT(*) FROM customers) as customers,
                (SELECT COUNT(*) FROM users WHERE type = 'agent' AND status = 'active') as agents,
                (SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE status = 'completed') as revenue
        ";
        $stats_result = $pdo->query($stats_query);
        if ($stats_result) {
            $stats = $stats_result->fetch(PDO::FETCH_ASSOC);
        }
    }
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
}

// Start output buffering for content
ob_start();
?>

<!-- JavaScript functionality has been moved to assets/js/custom.js -->

<!-- Hero Section with Background Slider -->
<section class="hero-section">
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
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <div class="hero-content" data-aos="fade-right">
                    <span class="badge bg-primary bg-opacity-20 text-white mb-3 px-4 py-2 rounded-pill d-inline-flex align-items-center" data-aos="fade-up" data-aos-delay="100">
                        <i class="fas fa-star me-2"></i>Trusted by 10,000+ Clients
                    </span>
                    
                    <h1 class="hero-title text-white mb-4" data-aos="fade-up" data-aos-delay="150">
                        Your Dream Home <span class="text-primary">Awaits</span> in Gorakhpur
                    </h1>
                    
                    <p class="hero-subtitle text-white-75 mb-5" data-aos="fade-up" data-aos-delay="200">
                        Discover exclusive properties with the most trusted real estate platform in Gorakhpur. 
                        From luxury apartments to prime commercial spaces, we have it all.
                    </p>

                    <!-- Quick Actions -->
                    <div class="d-flex flex-wrap gap-3 mb-5" data-aos="fade-up" data-aos-delay="250">
                        <a href="#featured-properties" class="btn btn-primary btn-lg px-4 py-3 d-flex align-items-center">
                            <i class="fas fa-home me-2"></i>Explore Properties
                        </a>
                        <a href="#contact" class="btn btn-outline-light btn-lg px-4 py-3 d-flex align-items-center">
                            <i class="fas fa-phone-alt me-2"></i>Contact Agent
                        </a>
                    </div>

                    <!-- Trust Indicators -->
                    <div class="trust-indicators" data-aos="fade-up" data-aos-delay="300">
                        <div class="d-flex align-items-center flex-wrap gap-4">
                            <div class="d-flex align-items-center">
                                <div class="trust-avatar-group me-2">
                                    <img src="https://randomuser.me/api/portraits/women/32.jpg" class="trust-avatar" alt="Client">
                                    <img src="https://randomuser.me/api/portraits/men/44.jpg" class="trust-avatar" alt="Client">
                                    <img src="https://randomuser.me/api/portraits/women/68.jpg" class="trust-avatar" alt="Client">
                                </div>
                                <div class="trust-text">
                                    <div class="text-white fw-bold">5,000+</div>
                                    <small class="text-white-50">Happy Clients</small>
                                </div>
                            </div>
                            <div class="vr text-white-50 d-none d-md-block"></div>
                            <div class="d-flex align-items-center">
                                <div class="me-2">
                                    <div class="text-warning">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star-half-alt"></i>
                                    </div>
                                    <div class="text-white-50 small">4.8/5 (2,500+ Reviews)</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search Card -->
            <div class="col-lg-6" data-aos="fade-left">
                <div class="search-card">
                    <div class="search-header text-center mb-4">
                        <h3 class="search-title mb-2">
                            <i class="fas fa-search-location text-primary me-2"></i>
                            Find Your Dream Home
                        </h3>
                        <p class="search-subtitle text-muted">Search from our premium collection of properties</p>
                    </div>

                    <form action="properties.php" method="GET" class="modern-search-form">
                        <div class="search-grid">
                            <!-- Property Type -->
                            <div class="search-group">
                                <label class="form-label fw-medium mb-2">
                                    <i class="fas fa-building me-2 text-primary"></i>Property Type
                                </label>
                                <select class="form-select" name="type">
                                    <option value="">All Types</option>
                                    <option value="apartment">Apartment</option>
                                    <option value="villa">Villa</option>
                                    <option value="house">Independent House</option>
                                    <option value="plot">Plot/Land</option>
                                    <option value="commercial">Commercial</option>
                                </select>
                            </div>

                            <!-- Location -->
                            <div class="search-group">
                                <label class="form-label fw-medium mb-2">
                                    <i class="fas fa-map-marker-alt me-2 text-primary"></i>Location
                                </label>
                                <select class="form-select" name="location" id="location-select">
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
                            </div>

                            <!-- Price Range -->
                            <div class="search-group">
                                <label class="form-label fw-medium mb-2">
                                    <i class="fas fa-indian-rupee-sign me-2 text-primary"></i>Price Range
                                </label>
                                <div class="price-range-slider mb-3">
                                    <div id="price-slider" class="mb-3"></div>
                                    <div class="d-flex justify-content-between">
                                        <input type="text" id="min-price" name="min_price" class="form-control form-control-sm w-45" placeholder="Min Price" readonly>
                                        <span class="mx-2 my-auto">-</span>
                                        <input type="text" id="max-price" name="max_price" class="form-control form-control-sm w-45" placeholder="Max Price" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="search-group">
                                <label class="search-label">
                                    <i class="fas fa-bed me-2"></i>Bedrooms
                                </label>
                                <select class="form-select modern-select" name="bedrooms">
                                    <option value="">🛏️ Any</option>
                                    <option value="1">1 BHK</option>
                                    <option value="2">2 BHK</option>
                                    <option value="3">3 BHK</option>
                                    <option value="4">4+ BHK</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-search w-100">
                            <i class="fas fa-search me-2"></i>🔍 Search Properties
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Properties Section -->
<section id="properties" class="featured-properties-section py-5">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h2 class="section-title" data-aos="fade-up">
                <i class="fas fa-home me-3"></i>Featured Properties
            </h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">
                Handpicked premium properties for discerning buyers
            </p>
        </div>

        <?php if (empty($featured_properties)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="empty-state text-center py-5">
                        <div class="empty-icon mb-4">
                            <i class="fas fa-home fa-4x text-muted"></i>
                        </div>
                        <h4 class="text-muted">No featured properties available</h4>
                        <p class="text-muted mb-4">Please check back later for new property listings.</p>
                        <a href="properties_template.php" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>View All Properties
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="properties-grid">
                <?php foreach ($featured_properties as $property): 
                    $status_class = ($property['status'] ?? '') === 'available' ? 'success' : 'secondary';
                    $status_text = ucfirst($property['status'] ?? 'Unknown');
                    $price = isset($property['price']) ? '₹' . number_format($property['price']) : 'Price on Request';
                ?>
                    <div class="property-card" data-aos="fade-up">
                        <div class="property-card-inner">
                            <div class="property-image-container">
                                <div class="property-badges">
                                    <span class="badge bg-<?php echo $status_class; ?> status-badge"><?php echo $status_text; ?></span>
                                    <span class="badge bg-primary type-badge"><?php echo htmlspecialchars($property['property_type'] ?? 'Property'); ?></span>
                                </div>
                                <img src="<?php echo htmlspecialchars($property['main_image'] ?? 'https://via.placeholder.com/800x600/667eea/ffffff?text=Property+Image'); ?>"
                                     alt="<?php echo htmlspecialchars($property['title'] ?? 'Property'); ?>"
                                     class="property-image">
                                <div class="property-overlay">
                                    <div class="property-actions">
                                        <button class="btn btn-action" onclick="toggleFavorite(<?php echo $property['id'] ?? 0; ?>)" title="Add to favorites">
                                            <i class="far fa-heart"></i>
                                        </button>
                                        <a href="property_details.php?id=<?php echo $property['id'] ?? 0; ?>" class="btn btn-action" title="Quick View">
                                            <i class="fas fa-expand"></i>
                                        </a>
                                        <button class="btn btn-action" onclick="shareProperty(<?php echo $property['id'] ?? 0; ?>)" title="Share">
                                            <i class="fas fa-share-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="property-content">
                                <div class="property-header">
                                    <h3 class="property-title">
                                        <a href="property_details.php?id=<?php echo $property['id'] ?? 0; ?>">
                                            <?php echo htmlspecialchars($property['title'] ?? 'Untitled Property'); ?>
                                        </a>
                                    </h3>
                                    <div class="property-price"><?php echo $price; ?></div>
                                </div>

                                <div class="property-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><?php echo htmlspecialchars($property['address'] ?? 'Location not specified'); ?></span>
                                </div>

                                <div class="property-features">
                                    <div class="feature-item">
                                        <i class="fas fa-bed"></i>
                                        <span><?php echo $property['bedrooms'] ?? 0; ?> <small>Beds</small></span>
                                    </div>
                                    <div class="feature-item">
                                        <i class="fas fa-bath"></i>
                                        <span><?php echo $property['bathrooms'] ?? 0; ?> <small>Baths</small></span>
                                    </div>
                                    <div class="feature-item">
                                        <i class="fas fa-ruler-combined"></i>
                                        <span><?php echo number_format($property['area_sqft'] ?? 0); ?> <small>sq.ft</small></span>
                                    </div>
                                    <div class="feature-item">
                                        <i class="fas fa-layer-group"></i>
                                        <span><?php echo $property['floors'] ?? 1; ?> <small>Floors</small></span>
                                    </div>
                                </div>

                                <div class="property-footer">
                                    <div class="property-agent">
                                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($property['agent_name'] ?? 'Agent'); ?>" 
                                             alt="Agent" class="agent-avatar">
                                        <div class="agent-info">
                                            <div class="agent-name"><?php echo htmlspecialchars($property['agent_name'] ?? 'Agent'); ?></div>
                                            <div class="agent-company">APS Dream Home</div>
                                        </div>
                                    </div>
                                    <a href="property_details.php?id=<?php echo $property['id'] ?? 0; ?>" class="btn btn-view-details">
                                        <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-5">
                <a href="properties_template.php" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-arrow-right me-2"></i> View All Properties
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Statistics Section -->
<section class="statistics-section py-5">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h2 class="section-title" data-aos="fade-up">
                <i class="fas fa-chart-bar me-3"></i>Our Achievements
            </h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">
                Trusted by thousands of customers across India
            </p>
        </div>

        <div class="stats-grid-enhanced">
            <div class="stat-card-enhanced" data-aos="fade-up">
                <div class="stat-icon-container">
                    <i class="fas fa-home fa-3x"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-number counter" data-count="<?php echo $stats['properties']; ?>">0</div>
                    <div class="stat-label">Total Properties</div>
                </div>
            </div>

            <div class="stat-card-enhanced" data-aos="fade-up" data-aos-delay="100">
                <div class="stat-icon-container">
                    <i class="fas fa-shopping-cart fa-3x"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-number counter" data-count="<?php echo $stats['revenue'] > 0 ? floor($stats['revenue'] / 1000000) : 0; ?>">0</div>
                    <div class="stat-label">Properties Sold</div>
                </div>
            </div>

            <div class="stat-card-enhanced" data-aos="fade-up" data-aos-delay="200">
                <div class="stat-icon-container">
                    <i class="fas fa-users fa-3x"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-number counter" data-count="<?php echo $stats['customers']; ?>">0</div>
                    <div class="stat-label">Happy Customers</div>
                </div>
            </div>

            <div class="stat-card-enhanced" data-aos="fade-up" data-aos-delay="300">
                <div class="stat-icon-container">
                    <i class="fas fa-user-tie fa-3x"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-number counter" data-count="<?php echo $stats['agents']; ?>">0</div>
                    <div class="stat-label">Expert Agents</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section py-5" id="contact">
    <div class="container">
        <div class="cta-content text-center">
            <h2 class="cta-title" data-aos="fade-up">
                <i class="fas fa-rocket me-3"></i>Ready to Find Your Dream Home?
            </h2>
            <p class="cta-subtitle" data-aos="fade-up" data-aos-delay="100">
                Get started today with our expert team
            </p>
            <div class="cta-actions" data-aos="fade-up" data-aos-delay="200">
                <a href="properties_template.php" class="btn btn-primary btn-lg me-3">
                    <i class="fas fa-search me-2"></i>Browse Properties
                </a>
                <a href="contact_template.php" class="btn btn-outline-light btn-lg me-3">
                    <i class="fas fa-phone me-2"></i>Contact Us
                </a>
                <a href="about_template.php" class="btn btn-success btn-lg">
                    <i class="fas fa-info-circle me-2"></i>Learn More
                </a>
            </div>
        </div>
    </div>
</section>

<?php
// End output buffering and get content
$content = ob_get_clean();

// Add JavaScript
$template->addJS("// All JavaScript functionality is in assets/js/custom.js");

// Add custom CSS file
$template->addCSS('assets/css/custom-styles.css');

// Render the page using universal template
page($content, 'APS Dream Home - Your Dream Home Awaits');
?>
    margin-bottom: 2rem !important;
    opacity: 0.95 !important;
    font-weight: 400 !important;
    line-height: 1.6 !important;
}

.stats-grid {
    display: flex !important;
    justify-content: center !important;
    gap: 3rem !important;
    margin-top: 3rem !important;
    flex-wrap: wrap !important;
}

.stat-item {
    text-align: center !important;
}

.stat-icon {
    width: 60px !important;
    height: 60px !important;
    background: rgba(255, 255, 255, 0.1) !important;
    border-radius: 50% !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    margin: 0 auto 1rem !important;
    color: #ffc107 !important;
    font-size: 1.5rem !important;
}

.stat-number {
    font-size: 2.5rem !important;
    font-weight: 800 !important;
    color: #ffc107 !important;
    display: block !important;
    line-height: 1 !important;
}

.stat-label {
    font-size: 0.9rem !important;
    opacity: 0.8 !important;
    text-transform: uppercase !important;
    letter-spacing: 1px !important;
}

.hero-actions {
    display: flex !important;
    gap: 1rem !important;
    justify-content: center !important;
    flex-wrap: wrap !important;
    margin-top: 2rem !important;
}

.search-card {
    background: white !important;
    padding: 30px !important;
    border-radius: 15px !important;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important;
}

.search-title {
    color: #1a237e !important;
    font-weight: 700 !important;
    margin-bottom: 0.5rem !important;
}

.search-subtitle {
    color: #666 !important;
    margin-bottom: 2rem !important;
}

.search-grid {
    display: grid !important;
    grid-template-columns: 1fr 1fr !important;
    gap: 1rem !important;
    margin-bottom: 2rem !important;
}

.search-group {
    display: flex !important;
    flex-direction: column !important;
}

.search-label {
    font-weight: 600 !important;
    margin-bottom: 0.5rem !important;
    color: #333 !important;
    font-size: 0.9rem !important;
}

.modern-select,
.form-control {
    padding: 12px 16px !important;
    border: 1px solid #e0e0e0 !important;
    border-radius: 8px !important;
    font-size: 1rem !important;
    transition: all 0.3s ease !important;
}

.modern-select:focus,
.form-control:focus {
    border-color: #667eea !important;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1) !important;
    outline: none !important;
}

.price-inputs {
    display: flex !important;
    align-items: center !important;
    gap: 0.5rem !important;
}

.price-separator {
    color: #666 !important;
    font-weight: 600 !important;
}

.btn-search {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    color: white !important;
    border: none !important;
    padding: 15px !important;
    border-radius: 8px !important;
    font-weight: 600 !important;
    transition: all 0.3s ease !important;
}

.btn-search:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3) !important;
}

.featured-properties-section {
    padding: 80px 0 !important;
    background: #f8f9fa !important;
}

.section-title {
    font-size: 2rem !important;
    font-weight: 600 !important;
    color: #1a237e !important;
    margin-bottom: 2rem !important;
    text-align: center !important;
}

.section-subtitle {
    font-size: 1.1rem !important;
    color: #666 !important;
    margin-bottom: 3rem !important;
    text-align: center !important;
}

.properties-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)) !important;
    gap: 2rem !important;
    margin-bottom: 3rem !important;
}

.property-card-enhanced {
    background: white !important;
    border-radius: 20px !important;
    overflow: hidden !important;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important;
    transition: all 0.4s ease !important;
}

.property-card-enhanced:hover {
    transform: translateY(-10px) scale(1.02) !important;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15) !important;
}

.property-image-container {
    position: relative !important;
    height: 250px !important;
    overflow: hidden !important;
}

.property-image {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    transition: transform 0.6s ease !important;
}

.property-card-enhanced:hover .property-image {
    transform: scale(1.1) !important;
}

.property-overlay {
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
    background: rgba(0,0,0,0.3) !important;
    display: flex !important;
    align-items: flex-end !important;
    justify-content: space-between !important;
    padding: 1rem !important;
    opacity: 0 !important;
    transition: all 0.3s ease !important;
}

.property-card-enhanced:hover .property-overlay {
    opacity: 1 !important;
}

.property-badges {
    display: flex !important;
    gap: 0.5rem !important;
    flex-wrap: wrap !important;
}

.badge {
    padding: 0.5rem 1rem !important;
    border-radius: 25px !important;
    font-size: 0.75rem !important;
    font-weight: 600 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
}

.badge-primary {
    background: #667eea !important;
    color: white !important;
}

.badge-success {
    background: #28a745 !important;
    color: white !important;
}

.btn-favorite {
    background: rgba(255, 255, 255, 0.9) !important;
    border: none !important;
    border-radius: 50% !important;
    width: 40px !important;
    height: 40px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    transition: all 0.3s ease !important;
}

.btn-favorite:hover {
    background: #dc3545 !important;
    color: white !important;
}

.property-content {
    padding: 25px !important;
}

.property-title {
    font-size: 1.25rem !important;
    font-weight: 700 !important;
    margin-bottom: 10px !important;
}

.property-link {
    color: #1a237e !important;
    text-decoration: none !important;
    transition: all 0.3s ease !important;
}

.property-link:hover {
    color: #667eea !important;
}

.property-location {
    color: #666 !important;
    font-size: 0.9rem !important;
    margin-bottom: 15px !important;
}

.property-features {
    display: flex !important;
    justify-content: space-between !important;
    padding: 15px 0 !important;
    border-top: 1px solid rgba(0,0,0,0.05) !important;
    border-bottom: 1px solid rgba(0,0,0,0.05) !important;
    margin-bottom: 15px !important;
    flex-wrap: wrap !important;
    gap: 10px !important;
}

.feature-item {
    display: flex !important;
    align-items: center !important;
    color: #666 !important;
    font-size: 0.9rem !important;
}

.feature-item i {
    color: #667eea !important;
    margin-right: 5px !important;
    font-size: 1rem !important;
}

.property-footer {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    margin-bottom: 15px !important;
}

.property-agent {
    color: #666 !important;
    font-size: 0.9rem !important;
}

.badge-status {
    padding: 5px 10px !important;
    border-radius: 20px !important;
    font-size: 0.75rem !important;
    font-weight: 600 !important;
}

.property-actions {
    display: flex !important;
    gap: 0.5rem !important;
}

.property-actions .btn {
    flex: 1 !important;
    font-size: 0.85rem !important;
    padding: 0.5rem 0.75rem !important;
}

.statistics-section {
    padding: 80px 0 !important;
    background: white !important;
}

.stats-grid-enhanced {
    display: grid !important;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)) !important;
    gap: 2rem !important;
    margin-bottom: 3rem !important;
}

.stat-card-enhanced {
    background: white !important;
    padding: 30px !important;
    border-radius: 15px !important;
    text-align: center !important;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important;
    transition: all 0.3s ease !important;
    display: flex !important;
    align-items: center !important;
    gap: 1rem !important;
}

.stat-card-enhanced:hover {
    transform: translateY(-5px) !important;
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15) !important;
}

.stat-icon-container {
    width: 80px !important;
    height: 80px !important;
    background: linear-gradient(135deg, #667eea, #764ba2) !important;
    border-radius: 50% !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    color: white !important;
    flex-shrink: 0 !important;
}

.stat-info {
    flex: 1 !important;
    text-align: left !important;
}

.stat-number {
    font-size: 2.5rem !important;
    font-weight: 800 !important;
    color: #667eea !important;
    line-height: 1 !important;
    margin-bottom: 0.5rem !important;
}

.stat-label {
    font-size: 1rem !important;
    color: #666 !important;
    font-weight: 600 !important;
}

.cta-section {
    background: linear-gradient(135deg, #1a237e 0%, #3949ab 100%) !important;
    color: white !important;
    padding: 80px 0 !important;
    text-align: center !important;
}

.cta-title {
    font-size: 2.5rem !important;
    font-weight: 700 !important;
    margin-bottom: 1rem !important;
}

.cta-subtitle {
    font-size: 1.2rem !important;
    margin-bottom: 2rem !important;
    opacity: 0.9 !important;
}

.cta-actions {
    display: flex !important;
    gap: 1rem !important;
    justify-content: center !important;
    flex-wrap: wrap !important;
}

.gradient-text {
    background: linear-gradient(135deg, #ffc107 0%, #ff8f00 100%) !important;
    -webkit-background-clip: text !important;
    -webkit-text-fill-color: transparent !important;
    background-clip: text !important;
}

@media (max-width: 768px) {
    .search-grid {
        grid-template-columns: 1fr !important;
    }

    .properties-grid {
        grid-template-columns: 1fr !important;
    }

    .stats-grid {
        gap: 2rem !important;
    }

    .hero-actions,
    .cta-actions {
        flex-direction: column !important;
        align-items: center !important;
    }

    .stat-card-enhanced {
        flex-direction: column !important;
        text-align: center !important;
    }

    .stat-info {
        text-align: center !important;
// Render the page using universal template
page($content, 'APS Dream Home - Your Dream Home Awaits');
?>
