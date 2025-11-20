<?php
// property-details.new.php

// Define a constant for the root directory of the application if not already defined
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__FILE__)); // Or a more fixed path like __DIR__ if this file is always in the root
}
if (!defined('INCLUDES_DIR')) {
    define('INCLUDES_DIR', APP_ROOT . '/includes');
}

require_once INCLUDES_DIR . '/config/config.php'; // Defines DB_CONN, SITE_URL, etc., and connects to DB
require_once INCLUDES_DIR . '/functions/common.php'; // For secure_session_start, e(), log_message, etc.
require_once INCLUDES_DIR . '/functions/template.php'; // For render_template, get_header, get_footer
require_once INCLUDES_DIR . '/models/PropertyModel.php';
require_once INCLUDES_DIR . '/Cache.php';

start_secure_session(SESSION_NAME); // Assuming SESSION_NAME is defined in config.php or common.php

// Get Property ID from URL, ensuring it's a positive integer
$property_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$property_slug = filter_input(INPUT_GET, 'slug', FILTER_SANITIZE_FULL_SPECIAL_CHARS); // Optional slug for SEO

if ($property_id === false || $property_id <= 0) {
    // Invalid or missing property ID, redirect to 404 or property listing page
    log_message('Invalid property ID requested: ' . print_r($_GET, true), 'warning');
    // render('errors/404'); // Assuming a 404 template exists
    // For now, let's just die with a message or redirect to homepage
    header('Location: ' . SITE_URL . '/404.php'); // Or a generic error page
    exit;
}

// The database connection $con is established in config.php and should be available here.
// If config.php uses a different variable name, adjust accordingly.
$db_conn = $con; // Use the connection from config.php
$propertyModel = new PropertyModel($db_conn);
$cache = Cache::getInstance();

$page_data = [
    'title' => 'Property Details',
    'description' => 'View details for the selected property.',
    'keywords' => 'property, details, real estate',
    'canonical_url' => SITE_URL . '/property-details.php?id=' . $property_id . ($property_slug ? '&slug=' . $property_slug : ''),
    'property' => null, // Will be populated by PropertyModel
    'similar_properties' => [],
    'structured_data_json' => ''
];

// Attempt to get property details from cache or database
$cache_key = 'property_details_' . $property_id;
$property_details = $cache->get($cache_key);

// Try to get data from cache first
$cached_data = $cache->get($cache_key);

if ($cached_data !== null) {
    log_message("Property details for ID {$property_id} (v2) fetched from cache.", 'info');
    $property_details = $cached_data['property_details'] ?? null;
    $similar_properties = $cached_data['similar_properties'] ?? [];
} else {
    log_message("Cache miss for property_details_v2_{$property_id}. Fetching from DB.", 'info');
    // Data not in cache, fetch from database using PropertyModel
    $fetched_data = $propertyModel->getPropertyDetailsById($property_id);

    if ($fetched_data && !empty($fetched_data['main_details'])) {
        $property_details = $fetched_data['main_details'];
        // getPropertyDetailsById should structure 'main_details' to include gallery, features, amenities directly
        // And 'similar_properties' should be a top-level key in $fetched_data
        $similar_properties = $fetched_data['similar_properties'] ?? [];

        // Prepare data for caching
        $data_to_cache = [
            'property_details' => $property_details,
            'similar_properties' => $similar_properties
        ];
        $cache->set($cache_key, $data_to_cache, 3600); // Cache for 1 hour
        log_message("Property details for ID {$property_id} (v2) fetched from DB and cached.", 'info');
    } else {
        log_message("Failed to fetch property details for ID {$property_id} (v2) from DB or main_details empty.", 'warning');
        // Property not found or error, $property_details will remain null
    }
}

if ($property_details === null) {
    // Property not found or error fetching
    log_message('Property not found or error fetching details for ID: ' . $property_id, 'error');
    // render('errors/404', ['page_title' => 'Property Not Found']);
    // For now, let's die or redirect
    echo "Property not found."; // Or redirect to a 404 page
    exit;
}

// Update page_data with fetched property details
$page_data['property'] = $property_details; // $property_details is the main property data array
$page_data['title'] = e($property_details['title'] ?? 'Property Details') . ' | APS Dream Home';
$page_data['meta_description'] = e(mb_strimwidth($property_details['description'] ?? 'View details about this property.', 0, 160, '...'));
$page_data['meta_keywords'] = 'property, real estate, ' . e($property_details['property_type'] ?? '') . ', ' . e($property_details['city'] ?? '');
$page_data['canonical_url'] = SITE_URL . '/property-details.new.php?id=' . $property_id;
$page_data['similar_properties'] = is_array($similar_properties) ? $similar_properties : []; // Ensure it's an array

// Generate JSON-LD structured data (simplified version, expand as needed)
$structured_data = [
    '@context' => 'https://schema.org',
    '@type' => 'SingleFamilyResidence', // Or other appropriate type like 'ApartmentComplex'
    'name' => $property_details['title'] ?? '',
    'description' => strip_tags($property_details['description'] ?? ''),
    'image' => $property_details['main_image'] ?? '',
    'address' => [
        '@type' => 'PostalAddress',
        'streetAddress' => $property_details['address'] ?? '',
        'addressLocality' => $property_details['city'] ?? ''
    ],
    'offers' => [
        '@type' => 'Offer',
        'price' => $property_details['price'] ?? 0,
        'priceCurrency' => 'INR',
        'availability' => ($property_details['status'] ?? 'available') === 'available' ? 'https://schema.org/InStock' : 'https://schema.org/SoldOut',
        'url' => $page_data['canonical_url']
    ]
];
if (!empty($property_details['latitude']) && !empty($property_details['longitude'])) {
    $structured_data['geo'] = [
        '@type' => 'GeoCoordinates',
        'latitude' => $property_details['latitude'],
        'longitude' => $property_details['longitude']
    ];
}
$page_data['structured_data_json'] = json_encode($structured_data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

// Render the page
render('header', ['page_data' => $page_data]);

// We will create these section templates next
render('sections/gallery', ['property' => $page_data['property']]);
render('sections/summary_sidebar', ['property' => $page_data['property']]); // Adjusted path // For summary and agent/inquiry form
render('sections/description_details', ['property' => $page_data['property']]); // Adjusted path // For description, features, amenities, map
render('sections/similar_properties', ['similar_properties' => $page_data['similar_properties']]); // Adjusted path

// The main content is now rendered through the section templates.
// The placeholder div below can be removed once layout is confirmed.
// echo "<div class='container py-5'><h1 class='text-center'>Property Details Page Refactor In Progress (TEMPLATES LOADED)</h1></div>";

render('footer', ['page_data' => $page_data]);

?>
