<?php
// Performance and security configuration
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

// Output buffering for improved performance
ob_start();

// Caching and security headers
header('Cache-Control: public, max-age=3600, stale-while-revalidate=86400');
header('Pragma: cache');
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(self), camera=(), microphone=()');
header('Content-Security-Policy: default-src \'self\'; 
    script-src \'self\' https://cdn.jsdelivr.net https://unpkg.com https://www.googletagmanager.com; 
    style-src \'self\' https://cdn.jsdelivr.net; 
    img-src \'self\' data: https:; 
    connect-src \'self\' https://www.google-analytics.com');

// Performance tracking
$start_time = microtime(true);

// Include necessary files with error handling
try {
    require_once(__DIR__ . '/includes/db_settings.php');
    require_once(__DIR__ . '/includes/functions/common-functions.php');
} catch (Exception $e) {
    error_log('Critical include error: ' . $e->getMessage());
    die('System initialization error');
}

// Get database connection with error handling
try {
    $conn = get_db_connection();
} catch (Exception $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    die('Database connection error');
}

// Fetch featured properties with prepared statement
$featured_properties = [];
try {
    // Validate and sanitize input parameters
    $status = 'available';
    $featured = 1;
    $limit = 6;

    // Prepared statement with parameterized query
    $stmt = $conn->prepare("
        SELECT 
            p.id, 
            p.title, 
            p.description, 
            p.price, 
            p.bedrooms, 
            p.bathrooms, 
            p.area, 
            p.location, 
            p.image_url, 
            p.type,
            u.name AS agent_name,
            u.phone AS agent_phone
        FROM properties p
        JOIN users u ON p.owner_id = u.id
        WHERE p.status = ? AND p.featured = ?
        ORDER BY p.created_at DESC
        LIMIT ?
    ");
    
    // Bind parameters with type specification
    $stmt->bind_param('sii', $status, $featured, $limit);
    
    // Execute and handle potential errors
    if (!$stmt->execute()) {
        throw new Exception('Query execution failed: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        error_log('No featured properties found');
    }
    
    while ($row = $result->fetch_assoc()) {
        // Comprehensive data sanitization
        $sanitized_row = [];
        foreach ($row as $key => $value) {
            switch ($key) {
                case 'price':
                    $sanitized_row[$key] = number_format(floatval($value), 2);
                    break;
                case 'bedrooms':
                case 'bathrooms':
                    $sanitized_row[$key] = intval($value);
                    break;
                default:
                    $sanitized_row[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
            }
        }
        
        // Add visit availability tracking
        $visit_availability = get_property_visit_availability($conn, $sanitized_row['id']);
        $sanitized_row['visit_availability'] = $visit_availability;
        
        $featured_properties[] = $sanitized_row;
    }
    
    $stmt->close();
} catch (Exception $e) {
    // Enhanced error logging
    error_log('Featured properties query error: ' . $e->getMessage());
    error_log('Error details: ' . print_r([
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ], true));
    
    // Fallback data
    $featured_properties = [
        [
            'id' => 0,
            'title' => 'No Properties Available',
            'description' => 'We are currently updating our property listings.',
            'price' => '0.00',
            'bedrooms' => 0,
            'bathrooms' => 0,
            'area' => 0,
            'location' => 'N/A',
            'image_url' => '/assets/images/placeholder.jpg',
            'agent_name' => 'APS Dream Homes',
            'visit_availability' => []
        ]
    ];
}

// Function to get property visit availability
function get_property_visit_availability($conn, $property_id) {
    // Input validation
    if (!is_numeric($property_id) || $property_id <= 0) {
        error_log('Invalid property ID: ' . $property_id);
        return [];
    }

    $availability = [];
    try {
        // Validate database connection
        if (!$conn || $conn->connect_error) {
            throw new Exception('Database connection is invalid');
        }

        // Prepared statement with additional security
        $stmt = $conn->prepare("
            SELECT 
                day_of_week, 
                start_time, 
                end_time, 
                max_visits_per_slot,
                (SELECT COUNT(*) FROM property_visits 
                 WHERE property_id = ? 
                 AND visit_date = CURRENT_DATE 
                 AND status = 'scheduled') AS current_visits
            FROM visit_availability 
            WHERE property_id = ?
            AND (
                day_of_week = DAYNAME(CURRENT_DATE) 
                OR day_of_week IS NULL
            )
        ");

        // Bind parameters twice for the two placeholders
        $stmt->bind_param('ii', $property_id, $property_id);
        
        // Enhanced error handling for execution
        if (!$stmt->execute()) {
            throw new Exception('Failed to execute visit availability query: ' . $stmt->error);
        }

        $result = $stmt->get_result();
        
        // Check if any results were found
        if ($result->num_rows === 0) {
            error_log('No visit availability found for property ID: ' . $property_id);
        }

        while ($row = $result->fetch_assoc()) {
            // Additional data transformation and validation
            $sanitized_row = [
                'day_of_week' => htmlspecialchars($row['day_of_week'] ?? 'Any Day'),
                'start_time' => date('H:i', strtotime($row['start_time'])),
                'end_time' => date('H:i', strtotime($row['end_time'])),
                'max_visits_per_slot' => intval($row['max_visits_per_slot']),
                'current_visits' => intval($row['current_visits']),
                'slots_available' => max(0, intval($row['max_visits_per_slot']) - intval($row['current_visits']))
            ];

            $availability[] = $sanitized_row;
        }

        $stmt->close();
    } catch (Exception $e) {
        // Comprehensive error logging
        error_log('Visit availability error for property ' . $property_id . ': ' . $e->getMessage());
        error_log('Error details: ' . print_r([
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ], true));
    }

    return $availability;
}

// Fetch testimonials with prepared statement
$testimonials = [];
try {
    $stmt = $conn->prepare("
        SELECT 
            c.name, 
            c.image_url, 
            t.content, 
            t.rating 
        FROM testimonials t
        JOIN customers c ON t.customer_id = c.id
        WHERE t.status = 'approved'
        LIMIT 4
    ");
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $testimonials[] = [
                'name' => htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'),
                'image_url' => htmlspecialchars($row['image_url'], ENT_QUOTES, 'UTF-8'),
                'content' => htmlspecialchars($row['content'], ENT_QUOTES, 'UTF-8'),
                'rating' => intval($row['rating'])
            ];
        }
    } else {
        // Default testimonial if none found
        $testimonials = [
            [
                'name' => 'APS Dream Homes',
                'image_url' => '/assets/images/default-avatar.png',
                'content' => 'We are committed to helping you find your dream home.',
                'rating' => 5
            ]
        ];
    }
    $stmt->close();
} catch (Exception $e) {
    error_log('Testimonials query failed: ' . $e->getMessage());
    $testimonials = [
        [
            'name' => 'APS Dream Homes',
            'image_url' => '/assets/images/default-avatar.png',
            'content' => 'We are committed to helping you find your dream home.',
            'rating' => 5
        ]
    ];
}

// Page performance logging
$end_time = microtime(true);
$page_load_time = round($end_time - $start_time, 4);
error_log("Homepage load time: {$page_load_time} seconds");

// Page metadata with enhanced SEO
$page_title = 'APS Dream Homes - Premium Real Estate in Uttar Pradesh';
$meta_description = 'Discover your dream property in Uttar Pradesh. APS Dream Homes offers premium residential and commercial properties with expert guidance and transparent services.';
$canonical_url = 'https://www.apsdreamhomes.com/';

// Structured data for enhanced SEO
$structured_data = json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'RealEstateAgency',
    'name' => 'APS Dream Homes',
    'description' => $meta_description,
    'url' => $canonical_url,
    'logo' => $canonical_url . 'assets/images/logo.png',
    'contactPoint' => [
        '@type' => 'ContactPoint',
        'telephone' => '+91-9876543210',
        'contactType' => 'Customer Service'
    ],
    'potentialAction' => [
        '@type' => 'SearchAction',
        'target' => $canonical_url . 'search?q={search_term_string}',
        'query-input' => 'required name=search_term_string'
    ],
    'offers' => [
        '@type' => 'AggregateOffer',
        'priceCurrency' => 'INR',
        'lowPrice' => min(array_column($featured_properties, 'price')),
        'highPrice' => max(array_column($featured_properties, 'price')),
        'offerCount' => count($featured_properties),
        'offers' => array_map(function($property) use ($canonical_url) {
            return [
                '@type' => 'Offer',
                'url' => $canonical_url . 'property/' . $property['id'],
                'price' => $property['price'],
                'availability' => 'https://schema.org/InStock',
                'priceCurrency' => 'INR'
            ];
        }, $featured_properties)
    ],
    'address' => [
        '@type' => 'PostalAddress',
        'streetAddress' => 'Gorakhpur',
        'addressLocality' => 'Uttar Pradesh',
        'addressCountry' => 'IN'
    ]
]);
?>
<!DOCTYPE html>
<html lang="en" prefix="og: https://ogp.me/ns#">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($meta_description); ?>">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="<?php echo htmlspecialchars($canonical_url); ?>" />
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo htmlspecialchars($canonical_url); ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($page_title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($meta_description); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($canonical_url . 'assets/images/og-banner.jpg'); ?>">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?php echo htmlspecialchars($canonical_url); ?>">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($page_title); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($meta_description); ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($canonical_url . 'assets/images/twitter-banner.jpg'); ?>">

    <!-- Structured Data -->
    <script type="application/ld+json">
    <?php echo $structured_data; ?>
    </script>

    <!-- Preload critical assets -->
    <link rel="preload" href="/assets/css/main.css" as="style">
    <link rel="preload" href="/assets/js/main.js" as="script">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.2.1/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/main.css">
</head>
<body>
    <!-- Rest of the HTML remains the same as the previous version -->
    <!-- Navigation, Hero Section, Featured Properties, Testimonials, CTA, Footer -->
    <!-- ... (previous HTML content) ... -->

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/main.js"></script>
</body>
</html>
<?php 
// Flush output buffer
ob_end_flush(); 
?>
