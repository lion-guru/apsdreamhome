<?php
/**
 * APS Dream Home - Modern Homepage
 * Enhanced with modern UI/UX design and optimized performance
 *
 * @package APSDreamHome
 * @version 2.0.0
 */

// ==========================================
// INITIALIZATION & CONFIGURATION
// ==========================================

// Define security constant for database connection
if (!defined('INCLUDED_FROM_MAIN')) {
    define('INCLUDED_FROM_MAIN', true);
}

// Set error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', '0'); // Disable direct display for better error handling
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/logs/php_errors.log');

// Define environment (development/production)
define('ENVIRONMENT', 'production'); // Set to 'production' for live site

// Set error display based on environment
if (ENVIRONMENT === 'development') {
    // For development, we'll log errors but handle them through our custom handler
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    // For production, we'll only log errors and use our custom error pages
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}

// Start session with enhanced security
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 86400,
        'cookie_secure' => isset($_SERVER['HTTPS']),
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'use_strict_mode' => true,
        'sid_length' => 48,
        'sid_bits_per_character' => 6
    ]);
}

// Set default timezone
date_default_timezone_set('Asia/Kolkata');

// Include required files with error handling
$required_files = [
    __DIR__ . '/includes/config.php',
    __DIR__ . '/includes/enhanced_universal_template.php',
    __DIR__ . '/includes/db_connection.php'
];

foreach ($required_files as $file) {
    if (!file_exists($file)) {
        die('Required file missing: ' . basename($file));
    }
    require_once $file;
}

// Ensure logs directory exists
$logs_dir = __DIR__ . '/logs';
if (!is_dir($logs_dir)) {
    mkdir($logs_dir, 0755, true);
}

// ==========================================
// TEMPLATE & ASSET INITIALIZATION
// ==========================================

$template = new EnhancedUniversalTemplate();

// Page metadata
$page_title = 'APS Dream Home - Find Your Perfect Property';
$page_description = 'APS Dream Home - Premium real estate platform for buying, selling, and renting properties in Gorakhpur and across India';
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/apsdreamhomefinal';
$current_url = $base_url . $_SERVER['REQUEST_URI'];

// Set page metadata
$template->setTitle($page_title);
$template->setDescription($page_description);

// Load CSS assets
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

// Load JavaScript assets with optimization
$js_assets = [
    ['url' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js', 'defer' => false, 'async' => true],
    ['url' => 'https://unpkg.com/swiper@8/swiper-bundle.min.js', 'defer' => true, 'async' => true],
    ['url' => 'https://unpkg.com/aos@2.3.1/dist/aos.js', 'defer' => true, 'async' => true],
    ['url' => 'https://cdn.jsdelivr.net/npm/vanilla-lazyload@17.8.3/dist/lazyload.min.js', 'defer' => true, 'async' => true],
    ['url' => '/assets/js/main.js', 'defer' => false, 'async' => true],
    ['url' => '/assets/js/custom.js', 'defer' => false, 'async' => true]
];

foreach ($js_assets as $js) {
    $template->addJS($js['url'], $js['defer'], $js['async']);
}

// Enhanced CSS for comprehensive achievements section
$template->addCustomCss('
/* Enhanced Achievements Section */
.achievements-section {
    position: relative;
    overflow: hidden;
}

.achievements-section::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23667eea\' fill-opacity=\'0.03\'%3E%3Ccircle cx=\'30\' cy=\'30\' r=\'2\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    z-index: 0;
}

.achievements-section .container {
    position: relative;
    z-index: 1;
}

/* Company Highlights Card */
.company-highlights-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.8);
}

.highlight-item {
    text-align: center;
    padding: 20px;
}

.highlight-icon {
    margin-bottom: 15px;
    display: flex;
    justify-content: center;
    align-items: center;
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 50%;
    margin: 0 auto 15px;
    box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
}

.highlight-number {
    font-size: 2.5rem;
    font-weight: 800;
    color: #1a237e;
    margin-bottom: 5px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.highlight-label {
    font-size: 1rem;
    color: #666;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* Stats Container */
.stats-container {
    background: white;
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
    height: 100%;
}

.stats-heading {
    color: #1a237e;
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 30px;
    border-bottom: 3px solid #667eea;
    padding-bottom: 15px;
}

.stats-grid-primary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.stat-card-primary {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 15px;
    padding: 25px;
    display: flex;
    align-items: center;
    gap: 20px;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.stat-card-primary:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    border-color: #667eea;
}

.stat-icon-container {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    flex-shrink: 0;
}

.stat-info {
    flex-grow: 1;
}

.stat-number {
    font-size: 2rem;
    font-weight: 800;
    color: #667eea;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 1.1rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
}

.stat-description {
    font-size: 0.9rem;
    color: #666;
    line-height: 1.4;
}

/* Company Timeline */
.company-timeline {
    background: white;
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
    height: 100%;
}

.timeline-heading {
    color: #1a237e;
    font-size: 1.3rem;
    font-weight: 700;
    margin-bottom: 25px;
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: "";
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: linear-gradient(135deg, #667eea, #764ba2);
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
    padding-left: 20px;
}

.timeline-marker {
    position: absolute;
    left: -38px;
    top: 5px;
    width: 30px;
    height: 30px;
    background: white;
    border: 3px solid #667eea;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
}

.timeline-year {
    font-size: 1.2rem;
    font-weight: 700;
    color: #667eea;
    margin-bottom: 8px;
}

.timeline-description {
    color: #666;
    font-size: 0.95rem;
    line-height: 1.5;
}

/* Company Values */
.values-heading {
    color: #1a237e;
    font-size: 1.1rem;
    font-weight: 700;
    margin-bottom: 20px;
}

.values-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.value-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 10px;
    transition: all 0.3s ease;
}

.value-item:hover {
    background: #667eea;
    color: white;
    transform: translateX(5px);
}

.value-item i {
    font-size: 1.2rem;
    width: 20px;
    text-align: center;
}

/* Featured Projects */
.featured-projects-section {
    margin-top: 40px;
}

.projects-heading {
    color: #1a237e;
    font-size: 1.8rem;
    font-weight: 700;
}

.project-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.project-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    border-color: #667eea;
}

.project-image {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.project-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.project-card:hover .project-image img {
    transform: scale(1.05);
}

.project-overlay {
    position: absolute;
    top: 15px;
    right: 15px;
}

.project-badge {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    padding: 8px 15px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.project-content {
    padding: 20px;
}

.project-title {
    color: #1a237e;
    font-size: 1.2rem;
    font-weight: 700;
    margin-bottom: 10px;
}

.project-description {
    color: #666;
    font-size: 0.9rem;
    line-height: 1.5;
    margin-bottom: 15px;
}

.project-stats {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.project-stats .stat {
    font-size: 0.85rem;
    color: #667eea;
    display: flex;
    align-items: center;
    gap: 5px;
}

/* Testimonials Section */
.testimonials-section {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
}

.testimonial-card {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    height: 100%;
    transition: all 0.3s ease;
    border-left: 5px solid #667eea;
}

.testimonial-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
}

.testimonial-rating {
    margin-bottom: 20px;
}

.testimonial-text {
    font-size: 1rem;
    line-height: 1.6;
    color: #333;
    margin-bottom: 25px;
    font-style: italic;
}

.testimonial-author {
    display: flex;
    align-items: center;
    gap: 15px;
}

.testimonial-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #667eea;
}

.author-name {
    font-weight: 700;
    color: #1a237e;
    margin-bottom: 3px;
}

.author-location {
    color: #666;
    font-size: 0.9rem;
}

/* Enhanced Responsive Design */
@media (max-width: 768px) {
    .company-highlights-card {
        padding: 25px;
    }

    .highlight-number {
        font-size: 2rem;
    }

    .stats-grid-primary {
        grid-template-columns: 1fr;
    }

    .stat-card-primary {
        padding: 20px;
    }

    .stat-number {
        font-size: 1.5rem;
    }

    .timeline {
        padding-left: 20px;
    }

    .timeline-marker {
        left: -28px;
        width: 25px;
        height: 25px;
    }

    .project-card {
        margin-bottom: 20px;
    }

    .project-stats {
        flex-direction: column;
        gap: 8px;
    }
}

/* Animation Enhancements */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.achievements-section .row {
    animation: fadeInUp 0.8s ease-out;
}

.achievements-section .row:nth-child(2) {
    animation-delay: 0.2s;
}

.achievements-section .row:nth-child(3) {
    animation-delay: 0.4s;
}

/* Loading Animation for Stats */
.stat-card-primary {
    opacity: 0;
    animation: fadeInUp 0.6s ease-out forwards;
}

.stat-card-primary:nth-child(1) { animation-delay: 0.1s; }
.stat-card-primary:nth-child(2) { animation-delay: 0.2s; }
.stat-card-primary:nth-child(3) { animation-delay: 0.3s; }
.stat-card-primary:nth-child(4) { animation-delay: 0.4s; }
.stat-card-primary:nth-child(5) { animation-delay: 0.5s; }
.stat-card-primary:nth-child(6) { animation-delay: 0.6s; }
');

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
$template->addMeta('og:image', $base_url . '/assets/images/og-image.jpg');
$template->addMeta('og:url', $current_url);
$template->addMeta('og:type', 'website');
$template->addMeta('og:site_name', 'APS Dream Home');
$template->addMeta('og:locale', 'en_IN');

// Twitter Card meta tags
$template->addMeta('twitter:card', 'summary_large_image');
$template->addMeta('twitter:title', $page_title);
$template->addMeta('twitter:description', $page_description);
$template->addMeta('twitter:image', $base_url . '/assets/images/og-image.jpg');

// ==========================================
// DATA INITIALIZATION & VALIDATION
// ==========================================

$featured_properties = [];
$locations = [];
$stats = [
    'properties' => 0,
    'customers' => 0,
    'agents' => 0,
    'revenue' => 0,
    'properties_formatted' => '0',
    'customers_formatted' => '0',
    'agents_formatted' => '0',
    'revenue_formatted' => '₹0'
];

// Validate database connection
if (!isset($pdo) || !$pdo) {
    error_log('Database connection not available in homepage.php');
    $db_error = true;
} else {
    $db_error = false;
}

// Fetch data from database
if (!$db_error && isset($pdo) && $pdo) {
    // Fetch featured properties with optimized query
    $query = "
        SELECT
            p.id,
            p.title,
            p.address,
            p.price,
            p.bedrooms,
            p.bathrooms,
            p.area_sqft,
            p.status,
            p.description,
            p.created_at,
            p.city,
            p.state,
            p.latitude,
            p.longitude,
            p.featured,
            p.featured_until,
            p.floors,
            u.id as agent_id,
            u.name as agent_name,
            u.phone as agent_phone,
            u.email as agent_email,
            u.profile_image as agent_image,
            u.status as agent_status,
            pt.id as property_type_id,
            pt.name as property_type,
            pt.icon as property_type_icon,
            (SELECT pi.image_path
             FROM property_images pi
             WHERE pi.property_id = p.id
             ORDER BY pi.is_primary DESC, pi.sort_order ASC
             LIMIT 1) as main_image,
            (SELECT COUNT(*) FROM property_images pi2 WHERE pi2.property_id = p.id) as total_images,
            (SELECT AVG(rating) FROM property_reviews pr WHERE pr.property_id = p.id) as avg_rating,
            (SELECT COUNT(*) FROM property_reviews pr2 WHERE pr2.property_id = p.id) as total_reviews
        FROM properties p
        LEFT JOIN users u ON p.created_by = u.id AND u.status = 'active' AND u.role = 'agent'
        LEFT JOIN property_types pt ON p.property_type_id = pt.id
        WHERE p.status = 'available'
          AND p.featured = 1
          AND (p.featured_until IS NULL OR p.featured_until >= NOW())
        ORDER BY
            CASE WHEN p.featured_until IS NOT NULL THEN 0 ELSE 1 END,
            p.featured_until ASC,
            p.created_at DESC
        LIMIT 12
    ";

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $featured_properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Featured properties query error: ' . $e->getMessage());
        $featured_properties = [];
    }

    // Fetch distinct locations for search dropdown with better grouping
    $location_query = "
        SELECT
            city,
            state,
            COUNT(*) as property_count
        FROM properties
        WHERE city IS NOT NULL
          AND city != ''
          AND status = 'available'
        GROUP BY city, state
        HAVING property_count > 0
        ORDER BY property_count DESC, state ASC, city ASC
        LIMIT 50
    ";

    try {
        $location_stmt = $pdo->prepare($location_query);
        $location_stmt->execute();
        $raw_locations = $location_stmt->fetchAll(PDO::FETCH_ASSOC);

        // Group locations by state for better organization
        $locations = [];
        foreach ($raw_locations as $location) {
            $state = $location['state'] ?? 'Other';
            if (!isset($locations[$state])) {
                $locations[$state] = [];
            }
            $locations[$state][] = [
                'city' => $location['city'],
                'count' => $location['property_count']
            ];
        }
    } catch (PDOException $e) {
        error_log('Locations query error: ' . $e->getMessage());
        $locations = [];
    }
}

// Enhanced database queries for comprehensive company achievements
$company_stats = [
    'properties_listed' => 0,
    'properties_sold' => 0,
    'happy_customers' => 0,
    'expert_agents' => 0,
    'years_experience' => 0,
    'cities_covered' => 0,
    'awards_won' => 0,
    'projects_completed' => 0,
    'client_satisfaction' => 0,
    'repeat_customers' => 0
];

try {
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
            'years_experience' => 15, // Company experience (can be made dynamic)
            'cities_covered' => (int)($company_data['cities_count'] ?? 0),
            'awards_won' => 25, // Company awards (can be made dynamic)
            'projects_completed' => (int)($company_data['sold_properties'] ?? 0) * 3, // Estimated projects
            'client_satisfaction' => $company_data['avg_rating'] ? round($company_data['avg_rating'], 1) : 4.8,
            'repeat_customers' => (int)($company_data['satisfied_customers'] ?? 0)
        ];
    }
} catch (PDOException $e) {
    error_log('Company stats query error: ' . $e->getMessage());
    // Keep default values
} catch (Exception $e) {
    error_log('Database error in homepage.php: ' . $e->getMessage());
    $featured_properties = [];
    $locations = [];
}

// Set content type and charset header
header('Content-Type: text/html; charset=UTF-8');

// Generate canonical URL
$canonical_url = $base_url . '/';

// Add canonical URL as meta tag
$template->addMeta('canonical', $canonical_url, 'rel');

// Start output buffering for content
ob_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta tags are handled by the template -->
</head>
<body>

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

                    <form action="properties" method="GET" class="modern-search-form">
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
                                    foreach ($locations as $state => $cities):
                                        if (!empty($cities)):
                                            echo '<optgroup label="' . htmlspecialchars($state) . '">';
                                            foreach ($cities as $city):
                                    ?>
                                        <option value="<?php echo htmlspecialchars($city['city']); ?>">
                                            <?php echo htmlspecialchars($city['city']); ?>
                                        </option>
                                    <?php
                                            endforeach;
                                            echo '</optgroup>';
                                        endif;
                                    endforeach;
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
<section id="featured-properties" class="featured-properties-section py-5">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h2 class="section-title" data-aos="fade-up">
                <i class="fas fa-home me-3"></i>Featured Properties
            </h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">
                Handpicked premium properties for discerning buyers
            </p>
        </div>

        <?php if (empty($featured_properties) || !isset($featured_properties)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="empty-state text-center py-5">
                        <div class="empty-icon mb-4">
                            <i class="fas fa-home fa-4x text-muted"></i>
                        </div>
                        <h4 class="text-muted">No featured properties available</h4>
                        <p class="text-muted mb-4">Please check back later for new property listings or browse all available properties.</p>
                        <div class="empty-actions">
                            <a href="properties" class="btn btn-primary me-3">
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
            <div class="properties-grid">
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
                    <div class="property-card" data-aos="fade-up" data-aos-delay="<?php echo ($index * 100); ?>">
                        <div class="property-card-inner">
                            <div class="property-image-container">
                                <div class="property-badges">
                                    <span class="badge bg-<?php echo $status_class; ?> status-badge">
                                        <i class="fas fa-check-circle me-1"></i><?php echo $status_text; ?>
                                    </span>
                                    <span class="badge bg-primary type-badge">
                                        <i class="fas fa-building me-1"></i><?php echo htmlspecialchars($property_type); ?>
                                    </span>
                                    <?php if (isset($property['featured_until']) && !empty($property['featured_until'])): ?>
                                        <span class="badge bg-warning text-dark featured-badge">
                                            <i class="fas fa-star me-1"></i>Featured
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <!-- Property Image with Error Handling -->
                                <img src="<?php echo htmlspecialchars($main_image); ?>"
                                     alt="<?php echo htmlspecialchars($property_title); ?>"
                                     class="property-image"
                                     loading="lazy"
                                     onerror="this.src='https://via.placeholder.com/800x600/667eea/ffffff?text=Property+Image'">

                                <!-- Property Actions Overlay -->
                                <div class="property-actions-overlay">
                                    <a href="property?id=<?php echo $property_id; ?>" class="btn btn-light btn-sm">
                                        <i class="fas fa-eye me-1"></i>View Details
                                    </a>
                                </div>
                            </div>

                            <div class="property-content">
                                <h3 class="property-title">
                                    <a href="property?id=<?php echo $property_id; ?>"><?php echo htmlspecialchars($property_title); ?></a>
                                </h3>

                                <div class="property-location mb-2">
                                    <i class="fas fa-map-marker-alt text-primary me-1"></i>
                                    <?php echo htmlspecialchars($property_address); ?>
                                </div>

                                <div class="property-price mb-3">
                                    <span class="fw-bold text-primary fs-5"><?php echo $formatted_price; ?></span>
                                </div>

                                <div class="property-features mb-3">
                                    <div class="row g-2">
                                        <?php if (!empty($property['bedrooms'])): ?>
                                            <div class="col-4">
                                                <div class="feature-item">
                                                    <i class="fas fa-bed text-muted me-1"></i>
                                                    <span><?php echo $property['bedrooms']; ?> BR</span>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($property['bathrooms'])): ?>
                                            <div class="col-4">
                                                <div class="feature-item">
                                                    <i class="fas fa-bath text-muted me-1"></i>
                                                    <span><?php echo $property['bathrooms']; ?> BA</span>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($property['area_sqft'])): ?>
                                            <div class="col-4">
                                                <div class="feature-item">
                                                    <i class="fas fa-ruler-combined text-muted me-1"></i>
                                                    <span><?php echo number_format($property['area_sqft']); ?> sq.ft</span>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="property-agent d-flex align-items-center">
                                    <img src="<?php echo !empty($property['agent_image']) ? htmlspecialchars($property['agent_image']) : 'https://via.placeholder.com/40x40/667eea/ffffff?text=AG'; ?>"
                                         alt="<?php echo htmlspecialchars($agent_name); ?>"
                                         class="agent-avatar me-2"
                                         onerror="this.src='https://via.placeholder.com/40x40/667eea/ffffff?text=AG'">
                                    <div class="agent-info">
                                        <div class="agent-name"><?php echo htmlspecialchars($agent_name); ?></div>
                                        <div class="agent-role text-muted small">Property Agent</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
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
$template->renderPage($content, $page_title);
?>
