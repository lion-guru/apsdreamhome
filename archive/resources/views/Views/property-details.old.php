
<?php
// Start session and include configuration
session_start();
require_once 'includes/config/db_config.php';
require_once 'includes/helpers/file_helpers.php';

// Get database connection
$conn = getMysqliConnection();
if ($conn === null) {
    die('Database connection failed');
}

// Set default timezone
date_default_timezone_set('Asia/Kolkata');

// Get property ID from URL
$property_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;

// Initialize property data
$property = [
    'id' => 0,
    'title' => 'Property Not Found',
    'description' => 'The requested property could not be found.',
    'price' => 0,
    'bedrooms' => 0,
    'bathrooms' => 0,
    'area' => 0,
    'address' => 'N/A',
    'location' => 'N/A',
    'property_type' => 'N/A',
    'status' => 'available',
    'features' => [],
    'amenities' => [],
    'gallery_images' => ['assets/images/property-placeholder.jpg'],
    'agent_id' => 0,
    'agent_name' => 'Our Agent',
    'agent_phone' => '',
    'agent_email' => '',
    'agent_photo' => 'assets/images/agent-placeholder.jpg',
    'listed_date' => date('Y-m-d H:i:s'),
    'latitude' => 0,
    'longitude' => 0
];

// Fetch property details from database
if ($property_id > 0) {
    try {
        $query = "
            SELECT 
                p.*, 
                pt.name as property_type,
                CONCAT(u.first_name, ' ', u.last_name) as agent_name,
                u.phone as agent_phone,
                u.email as agent_email,
                u.profile_photo as agent_photo
            FROM properties p
            LEFT JOIN property_types pt ON p.property_type_id = pt.id
            LEFT JOIN users u ON p.agent_id = u.id
            WHERE p.id = ? AND p.status = 'available'
            LIMIT 1
        ";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $property_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $property = array_merge($property, $result->fetch_assoc());
            
            // Fetch property images
            $image_query = "SELECT image_url FROM property_images WHERE property_id = ? ORDER BY is_primary DESC, id ASC";
            $img_stmt = $conn->prepare($image_query);
            $img_stmt->bind_param('i', $property_id);
            $img_stmt->execute();
            $img_result = $img_stmt->get_result();
            
            $gallery_images = [];
            while ($row = $img_result->fetch_assoc()) {
                $gallery_images[] = $row['image_url'];
            }
            
            if (!empty($gallery_images)) {
                $property['gallery_images'] = $gallery_images;
            }
            
            // Fetch property features
            $features_query = "
                SELECT f.name, f.icon 
                FROM property_features pf
                JOIN features f ON pf.feature_id = f.id
                WHERE pf.property_id = ?
            ";
            $features_stmt = $conn->prepare($features_query);
            $features_stmt->bind_param('i', $property_id);
            $features_stmt->execute();
            $features_result = $features_stmt->get_result();
            
            $features = [];
            while ($row = $features_result->fetch_assoc()) {
                $features[] = $row;
            }
            $property['features'] = $features;
            
            // Fetch property amenities
            $amenities_query = "
                SELECT a.name, a.icon 
                FROM property_amenities pa
                JOIN amenities a ON pa.amenity_id = a.id
                WHERE pa.property_id = ?
            ";
            $amenities_stmt = $conn->prepare($amenities_query);
            $amenities_stmt->bind_param('i', $property_id);
            $amenities_stmt->execute();
            $amenities_result = $amenities_stmt->get_result();
            
            $amenities = [];
            while ($row = $amenities_result->fetch_assoc()) {
                $amenities[] = $row;
            }
            $property['amenities'] = $amenities;
        }
    } catch (Exception $e) {
        error_log('Error fetching property details: ' . $e->getMessage());
    }
}

// Format price
function formatPrice($price) {
    if (empty($price)) return 'Contact for Price';
    return '₹' . number_format($price);
}

// Generate canonical URL
$canonical_url = 'https://' . $_SERVER['HTTP_HOST'] . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Prepare property status for display
$status_map = [
    'available' => 'Available',
    'sold' => 'Sold',
    'pending' => 'Under Contract',
    'rented' => 'Rented'
];

// Page title and meta
$page_title = htmlspecialchars($property['title']) . ' | ' . number_format($property['price']) . ' | ' . $property['property_type'] . ' | APS Dream Home';
$meta_description = 'View this ' . $property['bedrooms'] . ' BHK ' . strtolower($property['property_type']) . ' ' . 
                  ($property['status'] === 'sale' ? 'for sale' : 'for rent') . ' at ' . htmlspecialchars($property['address']) . 
                  '. ' . strip_tags(substr($property['description'], 0, 155)) . '...';

// Generate structured data for rich snippets
$structured_data = [
    '@context' => 'https://schema.org',
    '@type' => 'SingleFamilyResidence',
    'name' => $property['title'],
    'description' => strip_tags($property['description']),
    'numberOfRooms' => $property['bedrooms'],
    'numberOfBathroomsTotal' => $property['bathrooms'],
    'floorSize' => [
        '@type' => 'QuantitativeValue',
        'value' => $property['area'],
        'unitText' => 'sq.ft.'
    ],
    'address' => [
        '@type' => 'PostalAddress',
        'streetAddress' => $property['address'],
        'addressLocality' => $property['location']
    ],
    'offers' => [
        '@type' => 'Offer',
        'price' => $property['price'],
        'priceCurrency' => 'INR',
        'availability' => $property['status'] === 'available' ? 'https://schema.org/InStock' : 'https://schema.org/SoldOut',
        'url' => $canonical_url
    ],
    'image' => !empty($property['gallery_images']) ? $property['gallery_images'][0] : ''
];

// Add geo coordinates if available
if (!empty($property['latitude']) && !empty($property['longitude'])) {
    $structured_data['geo'] = [
        '@type' => 'GeoCoordinates',
        'latitude' => $property['latitude'],
        'longitude' => $property['longitude']
    ];
}

// Add agent information if available
if (!empty($property['agent_id'])) {
    $structured_data['realEstateAgent'] = [
        '@type' => 'RealEstateAgent',
        'name' => $property['agent_name'],
        'telephone' => $property['agent_phone']
    ];
}

// Convert structured data to JSON-LD
$structured_data_json = json_encode($structured_data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

// Fetch similar properties
$similarProperties = [];
if ($property_id > 0) {
    $similar_query = "
        SELECT p.*, pt.name as property_type, 
               (SELECT image_url FROM property_images WHERE property_id = p.id LIMIT 1) as main_image
        FROM properties p
        LEFT JOIN property_types pt ON p.property_type_id = pt.id
        WHERE p.id != ? 
          AND p.property_type_id = ? 
          AND p.status = 'available'
          AND p.price BETWEEN ? * 0.7 AND ? * 1.3
        ORDER BY 
            CASE 
                WHEN p.bedrooms = ? AND p.bathrooms = ? THEN 1
                WHEN p.bedrooms = ? OR p.bathrooms = ? THEN 2
                ELSE 3
            END,
            ABS(p.price - ?) / ? * 100
        LIMIT 4
    ";
    
    $stmt = $conn->prepare($similar_query);
    $stmt->bind_param(
        'iiiiiiiii',
        $property_id,
        $property['property_type_id'],
        $property['price'],
        $property['price'],
        $property['bedrooms'],
        $property['bathrooms'],
        $property['bedrooms'],
        $property['bathrooms'],
        $property['price'],
        $property['price'] > 0 ? $property['price'] : 1
    );
    $stmt->execute();
    $similar_result = $stmt->get_result();
    
    while ($row = $similar_result->fetch_assoc()) {
        $row['gallery_images'] = [$row['main_image'] ?: 'assets/images/property-placeholder.jpg'];
        $similarProperties[] = $row;
    }
}

// Process gallery images if they exist
if (isset($gallery_result) && $gallery_result) {
    while ($row = $gallery_result->fetch_assoc()) {
        $property['gallery_images'][] = $row['image_path'];
    }
}

// AI Valuation
if ($property_id > 0) {
    try {
        $valuation_sql = 'SELECT predicted_value FROM ai_property_valuation WHERE property_id = ? ORDER BY created_at DESC LIMIT 1';
        $stmt = $conn->prepare($valuation_sql);
        $stmt->bind_param('i', $property_id);
        $stmt->execute();
        $valuation_result = $stmt->get_result()->fetch_assoc();
        $property['ai_valuation'] = $valuation_result ? $valuation_result['predicted_value'] : 0;
        $stmt->close();
    } catch (Exception $e) {
        error_log("Error fetching AI valuation: " . $e->getMessage());
        $property['ai_valuation'] = 0;
    }
} else {
    $property['ai_valuation'] = 0;
}

// Close database connection if open
if (isset($conn) && $conn) {
    $conn->close();
}

// Start output buffering for extra head content
ob_start();
?>
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tX/miZyoHS5obTRR9BMY="
      crossorigin=""/>
<style>
    /* Property Map Styles */
    #propertyMap {
        min-height: 300px;
        background-color: #f8f9fa;
        border-radius: 8px;
    }
    
    /* Similar Properties */
    .shadow-sm-hover {
        transition: all 0.3s ease;
    }
    
    .shadow-sm-hover:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        transform: translateY(-3px);
    }
    
    .object-fit-cover {
        object-fit: cover;
    }
    
    .property-feature {
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
        border-radius: 0.5rem;
        padding: 1rem;
        text-align: center;
    }
    
    .property-feature i {
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
        color: #0d6efd;
    }
    
    .similar-property {
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
        border-radius: 0.5rem;
        overflow: hidden;
    }
    
    .similar-property:hover {
        transform: translateY(-5px);
        box-shadow: 0 1rem 2rem rgba(0, 0, 0, 0.1) !important;
    }
    
    .similar-property img {
        height: 180px;
        object-fit: cover;
    }
    
    .property-description {
        line-height: 1.8;
    }
    
    .property-description p:last-child {
        margin-bottom: 0;
    }
</style>
<?php
$extra_head = ob_get_clean();

// Include header with extra head content
include 'includes/header.php';
?>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>
?>

<!-- Property Details Hero Section -->
<section class="property-hero py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/apsdreamhome/">Home</a></li>
                        <li class="breadcrumb-item"><a href="/apsdreamhome/properties.php">Properties</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($property['title']); ?></li>
                    </ol>
                </nav>
                <div class="property-title mb-4">
                    <h1 class="mb-2"><?php echo htmlspecialchars($property['title']); ?></h1>
                    <p class="mb-0"><i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($property['location']); ?></p>
                    <span class="badge bg-success"><?php echo $property['status'] === 'available' ? 'For Sale' : 'Sold'; ?></span>
                </div>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="price-box">
                    <span class="price-label">Price</span>
                    <h2 class="price mb-3"><?php echo htmlspecialchars($property['price']); ?></h2>
                    <div class="d-flex justify-content-end gap-2 mb-3">
                        <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#inquiryModal">
                            <i class="fas fa-envelope me-2"></i>Inquire Now
                        </button>
                    </div>
                    <button class="btn btn-outline-primary btn-lg" data-bs-toggle="modal" data-bs-target="#scheduleModal">
                        <i class="fas fa-calendar me-2"></i>Schedule Visit
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Property Images Section -->
<section class="property-images py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="property-image-main mb-4 position-relative">
                    <img id="mainPropertyImage" 
                         src="<?php echo !empty($property['gallery_images']) ? htmlspecialchars($property['gallery_images'][0]) : '/apsdreamhome/assets/images/property-placeholder.jpg'; ?>" 
                         alt="<?php echo htmlspecialchars($property['title']); ?> - Main view" 
                         class="img-fluid rounded shadow w-100" 
                         width="1200" 
                         height="800"
                         loading="eager"
                         aria-describedby="mainImageDesc">
                    <div id="mainImageDesc" class="visually-hidden">
                        Main image of <?php echo htmlspecialchars($property['title']); ?>
                    </div>
                    <?php if (!empty($property['gallery_images']) && count($property['gallery_images']) > 1): ?>
                    <div class="position-absolute bottom-0 end-0 m-3">
                        <button type="button" 
                                class="btn btn-primary btn-sm" 
                                onclick="openImageGallery(0)" 
                                aria-label="View all images of <?php echo htmlspecialchars($property['title']); ?>">
                            <i class="fas fa-images me-1" aria-hidden="true"></i> View All Photos
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
                <?php if (!empty($property['gallery_images']) && count($property['gallery_images']) > 1): ?>
                <div class="property-image-thumbs d-flex overflow-auto pb-2" role="list" aria-label="Property image thumbnails">
                    <?php foreach ($property['gallery_images'] as $index => $image): ?>
                        <div class="position-relative me-2" role="listitem">
                            <img src="<?php echo htmlspecialchars($image); ?>" 
                                 alt="Thumbnail <?php echo $index + 1; ?> of <?php echo count($property['gallery_images']); ?>: <?php echo htmlspecialchars($property['title']); ?>" 
                                 class="thumb-image cursor-pointer" 
                                 onclick="updateMainImage('<?php echo htmlspecialchars($image); ?>', <?php echo $index; ?>)" 
                                 width="150" 
                                 height="100" 
                                 loading="lazy"
                                 role="button"
                                 tabindex="0"
                                 onkeydown="if(event.key === 'Enter') { updateMainImage('<?php echo htmlspecialchars($image); ?>', <?php echo $index; ?>); }"
                                 aria-label="View image <?php echo $index + 1; ?> of <?php echo count($property['gallery_images']); ?>">
                            <?php if ($index === 0): ?>
                            <div class="position-absolute top-0 start-0 bg-primary text-white px-2 py-1 small">
                                Main
                            </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Property Details Section -->
<section class="property-details py-5" aria-labelledby="property-details-heading">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <!-- Overview -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h2 id="property-details-heading" class="h4 card-title">Property Overview</h2>
                        <div class="row g-4" role="list" aria-label="Property features">
                            <div class="col-6 col-md-3" role="listitem">
                                <div class="feature-box text-center">
                                    <i class="fas fa-bed fa-2x mb-2 text-primary" aria-hidden="true"></i>
                                    <p class="text-muted mb-1">Bedrooms</p>
                                    <p class="h6 mb-0">
                                        <span class="visually-hidden">Number of bedrooms: </span>
                                        <?php echo htmlspecialchars($property['bedrooms']); ?>
                                    </p>
                                </div>
                            </div>
                            <div class="col-6 col-md-3" role="listitem">
                                <div class="feature-box text-center">
                                    <i class="fas fa-bath fa-2x mb-2 text-primary" aria-hidden="true"></i>
                                    <p class="text-muted mb-1">Bathrooms</p>
                                    <p class="h6 mb-0">
                                        <span class="visually-hidden">Number of bathrooms: </span>
                                        <?php echo htmlspecialchars($property['bathrooms']); ?>
                                    </p>
                                </div>
                            </div>
                            <div class="col-6 col-md-3" role="listitem">
                                <div class="feature-box text-center">
                                    <i class="fas fa-ruler-combined fa-2x mb-2 text-primary" aria-hidden="true"></i>
                                    <p class="text-muted mb-1">Area</p>
                                    <p class="h6 mb-0">
                                        <span class="visually-hidden">Total area: </span>
                                        <?php echo number_format($property['area']); ?> sq.ft.
                                    </p>
                                </div>
                            </div>
                            <div class="col-6 col-md-3" role="listitem">
                                <div class="feature-box text-center">
                                    <i class="fas fa-home fa-2x mb-2 text-primary" aria-hidden="true"></i>
                                    <p class="text-muted mb-1">Property Type</p>
                                    <p class="h6 mb-0">
                                        <span class="visually-hidden">Type of property: </span>
                                        <?php echo htmlspecialchars($property['property_type']); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Description -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h3 class="card-title">Description</h3>
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($property['description'])); ?></p>
                    </div>
                </div>
                
                <!-- Amenities -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h3 class="card-title">Amenities</h3>
                        <div class="row">
                            <?php foreach ($property['amenities'] as $amenity): ?>
                                <div class="col-md-4 mb-2">
                                    <div class="amenity-item">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        <?php echo htmlspecialchars($amenity); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Location -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h3 class="card-title">Location</h3>
                        <div class="ratio ratio-16x9">
                            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3887.9976945384793!2d77.5945627!3d12.9715987!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3bae1670c9b44e6d%3A0xf8dfc3e8517e4fe0!2sBangalore%2C%20Karnataka%2C%20India!5e0!3m2!1sen!2sus!4v1620000000000!5m2!1sen!2sus" 
                                    width="600" 
                                    height="450" 
                                    style="border:0;" 
                                    allowfullscreen="" 
                                    loading="lazy">
                            </iframe>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- AI Valuation -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h3 class="card-title">AI Property Valuation</h3>
                        <div class="text-center mb-3">
                            <span class="display-6 text-primary">₹<?php echo number_format($property['ai_valuation']); ?></span>
                        </div>
                        <p class="card-text">This AI-powered valuation is based on property features, location, and market trends. Actual value may vary.</p>
                    </div>
                </div>
                
                <!-- Contact Agent -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h3 class="card-title">Contact Agent</h3>
                        <div class="text-center mb-3">
                            <img src="/apsdreamhome/assets/images/agent-placeholder.jpg" alt="Agent" class="rounded-circle" width="100" height="100">
                            <h4 class="h5 mt-2"><?php echo htmlspecialchars($property['owner_name'] ?? 'APS Dream Homes Agent'); ?></h4>
                        </div>
                        <div class="d-grid gap-2">
                            <a href="tel:+919876543210" class="btn btn-outline-primary"><i class="fas fa-phone me-2"></i>Call Agent</a>
                            <a href="mailto:agent@apsdreamhomes.com" class="btn btn-outline-primary"><i class="fas fa-envelope me-2"></i>Email Agent</a>
                        </div>
                    </div>
                </div>
                
                <!-- Similar Properties -->
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title">Similar Properties</h3>
                        <div class="similar-property mb-3">
                            <img src="/apsdreamhome/assets/images/property-placeholder.jpg" alt="Similar Property" class="img-fluid rounded mb-2">
                            <h4 class="h6 mb-1">Luxury Villa in Whitefield</h4>
                            <p class="text-primary mb-0">₹1,80,00,000</p>
                        </div>
                        <div class="similar-property mb-3">
                            <img src="/apsdreamhome/assets/images/property-placeholder.jpg" alt="Similar Property" class="img-fluid rounded mb-2">
                            <h4 class="h6 mb-1">Modern Apartment in Indiranagar</h4>
                            <p class="text-primary mb-0">₹1,20,00,000</p>
                        </div>
                        <div class="text-center">
                            <a href="/apsdreamhome/properties.php" class="btn btn-link">View More Properties</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Property Gallery -->
<section class="property-gallery py-5 bg-light">
    <div class="container">
        <div class="row g-4">
            <!-- Main Image -->
            <div class="col-lg-8">
                <div class="property-main-image position-relative rounded-3 overflow-hidden shadow-sm" style="height: 500px;">
                    <img src="<?php echo !empty($property['gallery_images'][0]) ? htmlspecialchars($property['gallery_images'][0]) : 'assets/images/property-placeholder.jpg'; ?>" 
                         alt="<?php echo htmlspecialchars($property['title']); ?>" 
                         class="w-100 h-100 object-fit-cover"
                         id="mainPropertyImage">
                    <button class="btn btn-light btn-view-gallery position-absolute top-3 end-3 rounded-circle shadow-sm" 
                            data-bs-toggle="modal" data-bs-target="#galleryModal">
                        <i class="fas fa-expand"></i>
                    </button>
                </div>
            </div>
            
            <!-- Thumbnails -->
            <div class="col-lg-4">
                <div class="row g-3 h-100">
                    <?php 
                    $gallery_images = array_slice($property['gallery_images'], 0, 4);
                    foreach ($gallery_images as $index => $image): 
                        $is_last = ($index === 3 && count($property['gallery_images']) > 4);
                    ?>
                        <div class="col-6">
                            <div class="gallery-thumb position-relative rounded-3 overflow-hidden shadow-sm" 
                                 style="height: <?php echo $index === 0 ? '100%' : '160px'; ?>; cursor: pointer;"
                                 onclick="changeMainImage('<?php echo htmlspecialchars($image); ?>')">
                                <img src="<?php echo htmlspecialchars($image); ?>" 
                                     alt="Thumbnail <?php echo $index + 1; ?>" 
                                     class="w-100 h-100 object-fit-cover">
                                <?php if ($is_last): ?>
                                    <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 d-flex align-items-center justify-content-center">
                                        <span class="text-white fw-bold">+<?php echo count($property['gallery_images']) - 4; ?> more</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Property Header -->
<section class="property-header py-5 bg-white border-bottom">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="h2 fw-bold mb-2"><?php echo htmlspecialchars($property['title']); ?></h1>
                <div class="d-flex flex-wrap align-items-center text-muted mb-3">
                    <div class="d-flex align-items-center me-4 mb-2">
                        <i class="fas fa-map-marker-alt text-primary me-2"></i>
                        <span><?php echo htmlspecialchars($property['address']); ?></span>
                    </div>
                    <div class="d-flex align-items-center me-4 mb-2">
                        <i class="far fa-calendar-alt text-primary me-2"></i>
                        <span>Listed: <?php echo date('M d, Y', strtotime($property['listed_date'])); ?></span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-<?php echo $property['status'] === 'available' ? 'success' : 'secondary'; ?>-subtle text-<?php echo $property['status'] === 'available' ? 'success' : 'secondary'; ?> px-3 py-1 rounded-pill small fw-medium">
                            <?php echo ucfirst($property['status']); ?>
                        </span>
                    </div>
                </div>
                <div class="property-price d-flex align-items-center">
                    <div class="display-5 fw-bold text-primary me-3">
                        <?php echo formatPrice($property['price']); ?>
                    </div>
                    <?php if ($property['status'] === 'rent'): ?>
                        <span class="text-muted">/ month</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-4 mt-4 mt-lg-0">
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary flex-grow-1" onclick="window.print()">
                        <i class="fas fa-print me-2"></i> Print
                    </button>
                    <button class="btn btn-primary flex-grow-1" data-bs-toggle="modal" data-bs-target="#scheduleModal">
                        <i class="far fa-calendar-alt me-2"></i> Schedule Visit
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Property Info -->
<section class="property-info py-5 bg-light">
    <div class="container">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Description -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h2 class="h4 fw-bold mb-4">Description</h2>
                        <div class="property-description">
                            <?php echo nl2br(htmlspecialchars($property['description'])); ?>
                        </div>
                    </div>
                </div>
                
                <!-- Property Features & Amenities -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="row">
                            <!-- Features -->
                            <div class="col-md-6">
                                <h3 class="h5 fw-bold mb-4">Property Features</h3>
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="property-feature text-center p-3">
                                            <i class="fas fa-bed text-primary"></i>
                                            <div class="fw-medium"><?php echo $property['bedrooms']; ?> Bedrooms</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="property-feature text-center p-3">
                                            <i class="fas fa-bath text-primary"></i>
                                            <div class="fw-medium"><?php echo $property['bathrooms']; ?> Bathrooms</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="property-feature text-center p-3">
                                            <i class="fas fa-ruler-combined text-primary"></i>
                                            <div class="fw-medium"><?php echo number_format($property['area']); ?> sq.ft</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="property-feature text-center p-3">
                                            <i class="fas fa-warehouse text-primary"></i>
                                            <div class="fw-medium"><?php echo $property['garage'] ?? 0; ?> Garage</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Amenities -->
                            <div class="col-md-6 mt-4 mt-md-0">
                                <h3 class="h5 fw-bold mb-4">Amenities</h3>
                                <div class="row g-3">
                                    <?php 
                                    $amenities = [
                                        'swimming_pool' => ['icon' => 'swimming-pool', 'label' => 'Swimming Pool'],
                                        'gym' => ['icon' => 'dumbbell', 'label' => 'Gym'],
                                        'garden' => ['icon' => 'tree', 'label' => 'Garden'],
                                        'security' => ['icon' => 'shield-alt', 'label' => '24/7 Security'],
                                        'parking' => ['icon' => 'parking', 'label' => 'Parking'],
                                        'wifi' => ['icon' => 'wifi', 'label' => 'WiFi']
                                    ];
                                    
                                    foreach ($amenities as $key => $amenity): 
                                        $hasAmenity = isset($property[$key]) && $property[$key] == 1;
                                        if ($hasAmenity):
                                    ?>
                                    <div class="col-6">
                                        <div class="property-feature text-center p-3">
                                            <i class="fas fa-<?php echo $amenity['icon']; ?> text-primary"></i>
                                            <div class="fw-medium"><?php echo $amenity['label']; ?></div>
                                        </div>
                                    </div>
                                    <?php 
                                        endif;
                                    endforeach; 
                                    ?>
                                </div>
                        </div>
                    </div>
                </div>
                
                <!-- Virtual Tour Section -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h3 class="h5 fw-bold mb-4">Virtual Tour</h3>
                        <div class="ratio ratio-16x9 mb-4 rounded overflow-hidden">
                            <?php if (!empty($property['video_tour'])): ?>
                                <iframe src="<?php echo htmlspecialchars($property['video_tour']); ?>" 
                                        title="Property Video Tour" 
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                        allowfullscreen>
                                </iframe>
                            <?php else: ?>
                                <div class="position-relative h-100 bg-light d-flex align-items-center justify-content-center">
                                    <div class="text-center p-4">
                                        <i class="fas fa-video fa-3x text-muted mb-3"></i>
                                        <p class="mb-0">No video tour available</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (isset($property['virtual_tour']) && !empty($property['virtual_tour'])): ?>
                        <div class="text-center mt-3">
                            <a href="<?php echo htmlspecialchars($property['virtual_tour']); ?>" 
                               class="btn btn-outline-primary" 
                               target="_blank">
                                <i class="fas fa-street-view me-2"></i>Start 3D Virtual Tour
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Features -->
                <?php if (!empty($property['features'])): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h3 class="h5 fw-bold mb-4">Property Features</h3>
                        <div class="row g-3">
                            <?php 
                            $features = explode(",", $property['features']);
                            foreach ($features as $feature): 
                                if (!empty(trim($feature))):
                            ?>
                            <div class="col-6 col-md-4">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <span><?php echo htmlspecialchars(trim($feature)); ?></span>
                                </div>
                            </div>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Neighborhood Information -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h3 class="h5 fw-bold mb-4">Neighborhood Information</h3>
                        <div class="row g-4">
                            <div class="col-12">
                                <div class="d-flex align-items-start">
                                    <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-3">
                                        <i class="fas fa-map-marker-alt text-primary fs-4"></i>
                                    </div>
                                    <div>
                                        <h4 class="h6 fw-bold mb-1">Location</h4>
                                        <p class="mb-0 text-muted"><?php echo htmlspecialchars($property['location']); ?></p>
                                        <a href="#propertyMap" class="small text-primary text-decoration-none" data-bs-toggle="tooltip" title="View on map">
                                            View on map <i class="fas fa-arrow-right ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php 
                            // Sample neighborhood data - replace with actual data from your database
                            $neighborhoodData = [
                                ['icon' => 'school', 'title' => 'Schools', 'value' => 'Good', 'description' => 'Multiple schools within 2km radius'],
                                ['icon' => 'bus', 'title' => 'Transportation', 'value' => 'Excellent', 'description' => 'Bus stop 200m, Metro 1.2km'],
                                ['icon' => 'shopping-cart', 'title' => 'Shopping', 'value' => 'Good', 'description' => 'Mall 1.5km, Supermarket 800m'],
                                ['icon' => 'hospital', 'title' => 'Healthcare', 'value' => 'Good', 'description' => 'Hospital 2km, Clinic 500m'],
                                ['icon' => 'utensils', 'title' => 'Dining', 'value' => 'Excellent', 'description' => '20+ restaurants within 1km'],
                                ['icon' => 'tree', 'title' => 'Parks', 'value' => 'Good', 'description' => 'Park 400m, Playground 600m']
                            ];
                            
                            foreach ($neighborhoodData as $item): 
                            ?>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start h-100">
                                    <div class="bg-light p-3 rounded-3 me-3">
                                        <i class="fas fa-<?php echo $item['icon']; ?> text-muted"></i>
                                    </div>
                                    <div>
                                        <h4 class="h6 fw-bold mb-1"><?php echo $item['title']; ?></h4>
                                        <div class="d-flex align-items-center mb-1">
                                            <span class="badge bg-<?php echo strtolower($item['value']) === 'excellent' ? 'success' : 'primary'; ?> bg-opacity-10 text-<?php echo strtolower($item['value']) === 'excellent' ? 'success' : 'primary'; ?> me-2">
                                                <?php echo $item['value']; ?>
                                            </span>
                                        </div>
                                        <p class="small text-muted mb-0"><?php echo $item['description']; ?></p>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Price History & Market Trends -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3 class="h5 fw-bold mb-0">Price History</h3>
                            <span class="badge bg-light text-dark">Last updated: <?php echo date('M j, Y'); ?></span>
                        </div>
                        
                        <div class="price-history-chart mb-4" style="height: 250px;">
                            <canvas id="priceHistoryChart"></canvas>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Event</th>
                                        <th class="text-end">Price</th>
                                        <th class="text-end">Price/sq.ft</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Sample price history data - replace with actual data from your database
                                    $priceHistory = [
                                        [
                                            'date' => date('M j, Y', strtotime('-2 days')), 
                                            'event' => 'Price change', 
                                            'price' => $property['price'],
                                            'price_per_sqft' => round($property['price'] / $property['area'])
                                        ],
                                        [
                                            'date' => date('M j, Y', strtotime('-30 days')), 
                                            'event' => 'Listed for sale', 
                                            'price' => $property['price'] * 1.05, // 5% higher
                                            'price_per_sqft' => round(($property['price'] * 1.05) / $property['area'])
                                        ]
                                    ];
                                    
                                    foreach ($priceHistory as $history): 
                                    ?>
                                    <tr>
                                        <td class="text-nowrap"><?php echo $history['date']; ?></td>
                                        <td class="text-nowrap"><?php echo $history['event']; ?></td>
                                        <td class="text-nowrap text-end">₹<?php echo number_format($history['price']); ?></td>
                                        <td class="text-nowrap text-end">₹<?php echo number_format($history['price_per_sqft']); ?>/sq.ft</td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if (!empty($property['price_insight'])): ?>
                        <div class="alert alert-light mt-3 mb-0">
                            <div class="d-flex">
                                <i class="fas fa-info-circle text-primary mt-1 me-2"></i>
                                <div>
                                    <h4 class="h6 fw-bold mb-1">Price Insight</h4>
                                    <p class="mb-0 small"><?php echo htmlspecialchars($property['price_insight']); ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Schedule a Visit -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h3 class="h5 fw-bold mb-4">Schedule a Visit</h3>
                        <form id="visitForm" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="visitDate" class="form-label small fw-medium">Preferred Date</label>
                                <input type="date" class="form-control" id="visitDate" required 
                                       min="<?php echo date('Y-m-d'); ?>" 
                                       value="<?php echo date('Y-m-d'); ?>">
                                <div class="invalid-feedback">
                                    Please select a valid date.
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="visitTime" class="form-label small fw-medium">Preferred Time</label>
                                <select class="form-select" id="visitTime" required>
                                    <option value="">Select a time</option>
                                    <option value="09:00">09:00 AM</option>
                                    <option value="10:00">10:00 AM</option>
                                    <option value="11:00">11:00 AM</option>
                                    <option value="12:00">12:00 PM</option>
                                    <option value="14:00">02:00 PM</option>
                                    <option value="15:00">03:00 PM</option>
                                    <option value="16:00">04:00 PM</option>
                                    <option value="17:00">05:00 PM</option>
                                </select>
                                <div class="invalid-feedback">
                                    Please select a time slot.
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="visitName" class="form-label small fw-medium">Your Name</label>
                                <input type="text" class="form-control" id="visitName" required>
                                <div class="invalid-feedback">
                                    Please provide your name.
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="visitPhone" class="form-label small fw-medium">Phone Number</label>
                                <input type="tel" class="form-control" id="visitPhone" required>
                                <div class="invalid-feedback">
                                    Please provide a valid phone number.
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-calendar-check me-2"></i>Schedule Visit
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Request a Callback -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-primary bg-opacity-10 p-2 rounded-3 me-3">
                                <i class="fas fa-phone-alt text-primary"></i>
                            </div>
                            <div>
                                <h3 class="h5 fw-bold mb-0">Request a Callback</h3>
                                <p class="small text-muted mb-0">Get a call from our property expert</p>
                            </div>
                        </div>
                        <form id="callbackForm" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="callbackName" class="form-label small fw-medium">Your Name</label>
                                <input type="text" class="form-control" id="callbackName" required>
                                <div class="invalid-feedback">
                                    Please provide your name.
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="callbackPhone" class="form-label small fw-medium">Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text">+91</span>
                                    <input type="tel" class="form-control" id="callbackPhone" pattern="[0-9]{10}" required>
                                </div>
                                <div class="invalid-feedback">
                                    Please provide a valid 10-digit phone number.
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="callbackTime" class="form-label small fw-medium">Preferred Time to Call</label>
                                <select class="form-select" id="callbackTime" required>
                                    <option value="">Select a time slot</option>
                                    <option value="9am-12pm">Morning (9 AM - 12 PM)</option>
                                    <option value="12pm-3pm">Afternoon (12 PM - 3 PM)</option>
                                    <option value="3pm-6pm">Evening (3 PM - 6 PM)</option>
                                    <option value="6pm-8pm">Late Evening (6 PM - 8 PM)</option>
                                </select>
                                <div class="invalid-feedback">
                                    Please select a time slot.
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-phone-alt me-2"></i>Request Callback
                            </button>
                        </form>
                        <div class="text-center mt-3">
                            <p class="small text-muted mb-0">
                                <i class="fas fa-shield-alt text-success me-1"></i>
                                Your information is secure and will not be shared
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Share & Save -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h3 class="h5 fw-bold mb-4">Share & Save</h3>
                        <div class="text-center">
                            <p class="text-muted mb-4">Share this property with friends or save it for later</p>
                            <div class="d-flex justify-content-center gap-3 mb-4">
                                <a href="#" class="btn btn-outline-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;" data-bs-toggle="tooltip" title="Share on WhatsApp">
                                    <i class="fab fa-whatsapp fs-5"></i>
                                </a>
                                <a href="#" class="btn btn-outline-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;" data-bs-toggle="tooltip" title="Share on Facebook">
                                    <i class="fab fa-facebook-f fs-5"></i>
                                </a>
                                <a href="#" class="btn btn-outline-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;" data-bs-toggle="tooltip" title="Share on Twitter">
                                    <i class="fab fa-twitter fs-5"></i>
                                </a>
                                <a href="#" class="btn btn-outline-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;" data-bs-toggle="tooltip" title="Copy Link">
                                    <i class="fas fa-link fs-5"></i>
                                </a>
                            </div>
                            <button class="btn btn-outline-secondary w-100" id="savePropertyBtn">
                                <i class="far fa-bookmark me-2"></i>Save Property
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Property Documents -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-primary bg-opacity-10 p-2 rounded-3 me-3">
                                <i class="fas fa-file-alt text-primary"></i>
                            </div>
                            <div>
                                <h3 class="h5 fw-bold mb-0">Property Documents</h3>
                                <p class="small text-muted mb-0">Important documents related to this property</p>
                            </div>
                        </div>
                        
                        <div class="document-list">
                            <div class="document-item d-flex align-items-center justify-content-between p-3 border rounded-3 mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="bg-light p-2 rounded-3 me-3">
                                        <i class="fas fa-file-pdf text-danger"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-medium">Sale Deed</h6>
                                        <small class="text-muted">PDF • 2.4 MB</small>
                                    </div>
                                </div>
                                <a href="#" class="btn btn-sm btn-outline-primary" download>
                                    <i class="fas fa-download me-1"></i> Download
                                </a>
                            </div>
                            
                            <div class="document-item d-flex align-items-center justify-content-between p-3 border rounded-3 mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="bg-light p-2 rounded-3 me-3">
                                        <i class="fas fa-file-pdf text-danger"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-medium">Khata Certificate</h6>
                                        <small class="text-muted">PDF • 1.8 MB</small>
                                    </div>
                                </div>
                                <a href="#" class="btn btn-sm btn-outline-primary" download>
                                    <i class="fas fa-download me-1"></i> Download
                                </a>
                            </div>
                            
                            <div class="document-item d-flex align-items-center justify-content-between p-3 border rounded-3 mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="bg-light p-2 rounded-3 me-3">
                                        <i class="fas fa-file-image text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-medium">Approved Plan</h6>
                                        <small class="text-muted">JPG • 3.2 MB</small>
                                    </div>
                                </div>
                                <a href="#" class="btn btn-sm btn-outline-primary" download>
                                    <i class="fas fa-download me-1"></i> Download
                                </a>
                            </div>
                            
                            <div class="document-item d-flex align-items-center justify-content-between p-3 border rounded-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-light p-2 rounded-3 me-3">
                                        <i class="fas fa-file-alt text-info"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-medium">Encumbrance Certificate</h6>
                                        <small class="text-muted">PDF • 1.5 MB</small>
                                    </div>
                                </div>
                                <a href="#" class="btn btn-sm btn-outline-primary" download>
                                    <i class="fas fa-download me-1"></i> Download
                                </a>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#requestDocumentsModal">
                                <i class="fas fa-envelope me-2"></i>Request All Documents
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Virtual Tour -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-primary bg-opacity-10 p-2 rounded-3 me-3">
                                <i class="fas fa-vr-cardboard text-primary"></i>
                            </div>
                            <div>
                                <h3 class="h5 fw-bold mb-0">Virtual Tour</h3>
                                <p class="small text-muted mb-0">Explore this property from the comfort of your home</p>
                            </div>
                        </div>
                        
                        <div class="ratio ratio-16x9 rounded-3 overflow-hidden mb-4">
                            <iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ?rel=0&amp;controls=1&amp;showinfo=0" 
                                    title="Property Virtual Tour" 
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                    allowfullscreen></iframe>
                        </div>
                        
                        <div class="text-center">
                            <p class="text-muted mb-3">Can't visit in person? Take a virtual tour now!</p>
                            <div class="d-flex justify-content-center gap-3">
                                <a href="#" class="btn btn-primary px-4">
                                    <i class="fas fa-play-circle me-2"></i>Start 3D Tour
                                </a>
                                <a href="#" class="btn btn-outline-primary px-4">
                                    <i class="fas fa-expand me-2"></i>Full Screen
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Request Documents Modal -->
                <div class="modal fade" id="requestDocumentsModal" tabindex="-1" aria-labelledby="requestDocumentsModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title fw-bold" id="requestDocumentsModalLabel">Request Property Documents</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body pt-0">
                                <p class="text-muted mb-4">Please provide your details to receive all property documents via email.</p>
                                
                                <form id="documentsRequestForm" class="needs-validation" novalidate>
                                    <div class="mb-3">
                                        <label for="documentsName" class="form-label small fw-medium">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="documentsName" required>
                                        <div class="invalid-feedback">
                                            Please provide your name.
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="documentsEmail" class="form-label small fw-medium">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="documentsEmail" required>
                                        <div class="invalid-feedback">
                                            Please provide a valid email address.
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="documentsPhone" class="form-label small fw-medium">Phone Number <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">+91</span>
                                            <input type="tel" class="form-control" id="documentsPhone" pattern="[0-9]{10}" required>
                                        </div>
                                        <div class="invalid-feedback">
                                            Please provide a valid 10-digit phone number.
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-paper-plane me-2"></i>Send Request
                                        </button>
                                    </div>
                                </form>
                                
                                <div class="mt-3 small text-muted">
                                    <i class="fas fa-shield-alt text-success me-1"></i>
                                    Your information is secure and will not be shared with third parties.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Property Financing -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <ul class="nav nav-pills nav-fill mb-4" id="financingTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="emi-tab" data-bs-toggle="pill" data-bs-target="#emi-calculator" type="button" role="tab" aria-controls="emi-calculator" aria-selected="true">
                                    <i class="fas fa-calculator me-2"></i>EMI Calculator
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="preapproval-tab" data-bs-toggle="pill" data-bs-target="#preapproval" type="button" role="tab" aria-controls="preapproval" aria-selected="false">
                                    <i class="fas fa-file-invoice-dollar me-2"></i>Get Pre-Approved
                                </button>
                            </li>
                        </ul>
                        
                        <div class="tab-content" id="financingTabsContent">
                            <!-- EMI Calculator Tab -->
                            <div class="tab-pane fade show active" id="emi-calculator" role="tabpanel" aria-labelledby="emi-tab">
                                <div class="mb-4">
                                    <label for="loanAmount" class="form-label small fw-medium">Loan Amount (₹)</label>
                                    <div class="input-group mb-2">
                                        <span class="input-group-text">₹</span>
                                        <input type="number" class="form-control" id="loanAmount" value="<?php echo (int)filter_var($property['price'], FILTER_SANITIZE_NUMBER_INT); ?>">
                                    </div>
                                    <div class="range">
                                        <input type="range" class="form-range" id="loanAmountRange" min="100000" max="10000000" step="100000" value="<?php echo (int)filter_var($property['price'], FILTER_SANITIZE_NUMBER_INT); ?>">
                                    </div>
                                    <div class="d-flex justify-content-between small text-muted">
                                        <span>1L</span>
                                        <span>1Cr</span>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="interestRate" class="form-label small fw-medium">Interest Rate (% p.a.)</label>
                                    <div class="input-group mb-2">
                                        <input type="number" class="form-control" id="interestRate" step="0.1" value="8.5">
                                        <span class="input-group-text">%</span>
                                    </div>
                                    <div class="range">
                                        <input type="range" class="form-range" id="interestRateRange" min="6" max="15" step="0.1" value="8.5">
                                    </div>
                                    <div class="d-flex justify-content-between small text-muted">
                                        <span>6%</span>
                                        <span>15%</span>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="loanTenure" class="form-label small fw-medium">Loan Tenure (Years)</label>
                                    <div class="input-group mb-2">
                                        <input type="number" class="form-control" id="loanTenure" min="1" max="30" value="20">
                                        <span class="input-group-text">Years</span>
                                    </div>
                                    <div class="range">
                                        <input type="range" class="form-range" id="loanTenureRange" min="1" max="30" value="20">
                                    </div>
                                    <div class="d-flex justify-content-between small text-muted">
                                        <span>1 Year</span>
                                        <span>30 Years</span>
                                    </div>
                                </div>
                                
                                <div class="bg-light p-3 rounded-3 mb-4">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Monthly EMI:</span>
                                        <span class="fw-bold" id="monthlyEmi">₹0</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Total Interest:</span>
                                        <span class="text-danger" id="totalInterest">₹0</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-0">
                                        <span class="text-muted">Total Payment:</span>
                                        <span class="text-success" id="totalPayment">₹0</span>
                                    </div>
                                </div>
                                
                                <button class="btn btn-primary w-100" id="applyForLoanBtn">
                                    <i class="fas fa-paper-plane me-2"></i>Apply for Home Loan
                                </button>
                                
                                <div class="mt-3 small text-muted text-center">
                                    EMI calculated for the year 2025. Actual terms may vary.
                                </div>
                            </div>
                            
                            <!-- Pre-Approval Tab -->
                            <div class="tab-pane fade" id="preapproval" role="tabpanel" aria-labelledby="preapproval-tab">
                                <div class="text-center mb-4">
                                    <div class="bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center rounded-circle p-3 mb-3">
                                        <i class="fas fa-file-invoice-dollar text-primary" style="font-size: 2rem;"></i>
                                    </div>
                                    <h4 class="h5 fw-bold">Get Pre-Approved</h4>
                                    <p class="text-muted">Know your home loan eligibility and get pre-approved in minutes!</p>
                                </div>
                                
                                <form id="preapprovalForm" class="needs-validation" novalidate>
                                    <div class="mb-3">
                                        <label for="preapprovalName" class="form-label small fw-medium">Full Name</label>
                                        <input type="text" class="form-control" id="preapprovalName" required>
                                        <div class="invalid-feedback">
                                            Please provide your name.
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="preapprovalEmail" class="form-label small fw-medium">Email</label>
                                            <input type="email" class="form-control" id="preapprovalEmail" required>
                                            <div class="invalid-feedback">
                                                Please provide a valid email.
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="preapprovalPhone" class="form-label small fw-medium">Phone</label>
                                            <div class="input-group">
                                                <span class="input-group-text">+91</span>
                                                <input type="tel" class="form-control" id="preapprovalPhone" pattern="[0-9]{10}" required>
                                            </div>
                                            <div class="invalid-feedback">
                                                Please provide a valid 10-digit phone number.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="annualIncome" class="form-label small fw-medium">Annual Income (₹)</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₹</span>
                                            <input type="number" class="form-control" id="annualIncome" required>
                                        </div>
                                        <div class="invalid-feedback">
                                            Please provide your annual income.
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="employmentType" class="form-label small fw-medium">Employment Type</label>
                                        <select class="form-select" id="employmentType" required>
                                            <option value="">Select employment type</option>
                                            <option value="salaried">Salaried</option>
                                            <option value="self_employed">Self-Employed</option>
                                            <option value="business">Business</option>
                                            <option value="professional">Professional</option>
                                        </select>
                                        <div class="invalid-feedback">
                                            Please select your employment type.
                                        </div>
                                    </div>
                                    
                                    <div class="form-check mb-4">
                                        <input class="form-check-input" type="checkbox" value="" id="termsAgreement" required>
                                        <label class="form-check-label small" for="termsAgreement">
                                            I agree to the <a href="#">Terms & Conditions</a> and authorize APS Dream Home & its banking partners to contact me with reference to my loan application.
                                        </label>
                                        <div class="invalid-feedback">
                                            You must agree to the terms and conditions.
                                        </div>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-check-circle me-2"></i>Get Pre-Approved
                                    </button>
                                </form>
                                
                                <div class="mt-3 small text-muted">
                                    <i class="fas fa-shield-alt text-success me-1"></i>
                                    Your information is secure and will be shared only with our banking partners.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                        <form id="mortgageCalculator" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="homePrice" class="form-label small fw-medium">Home Price (₹)</label>
                                <input type="number" class="form-control" id="homePrice" value="<?php echo $property['price']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="downPayment" class="form-label small fw-medium">Down Payment (₹)</label>
                                <input type="number" class="form-control" id="downPayment" value="<?php echo round($property['price'] * 0.2); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="loanTerm" class="form-label small fw-medium">Loan Term (years)</label>
                                <select class="form-select" id="loanTerm" required>
                                    <option value="10">10 years</option>
                                    <option value="15">15 years</option>
                                    <option value="20" selected>20 years</option>
                                    <option value="25">25 years</option>
                                    <option value="30">30 years</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="interestRate" class="form-label small fw-medium">Interest Rate (%)</label>
                                <input type="number" class="form-control" id="interestRate" step="0.01" value="7.5" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Calculate</button>
                        </form>
                        <div id="mortgageResult" class="mt-4 d-none">
                            <hr>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Monthly Payment:</span>
                                <strong id="monthlyPayment" class="text-primary">₹0</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Total Interest:</span>
                                <strong id="totalInterest" class="text-muted">₹0</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Total Payment:</span>
                                <strong id="totalPayment" class="text-muted">₹0</strong>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Contact Agent -->
                <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                    <div class="card-body p-4">
                        <h3 class="h5 fw-bold mb-4">Contact Agent</h3>
                        <div class="d-flex align-items-center mb-4">
                            <img src="<?php echo htmlspecialchars($property['agent_photo']); ?>" 
                                 alt="<?php echo htmlspecialchars($property['agent_name']); ?>" 
                                 class="rounded-circle me-3" 
                                 width="70" 
                                 height="70"
                                 onerror="this.src='assets/images/agent-placeholder.jpg'">
                            <div>
                                <h4 class="h6 fw-bold mb-1"><?php echo htmlspecialchars($property['agent_name']); ?></h4>
                                <p class="text-muted small mb-1">Real Estate Agent</p>
                                <div class="agent-rating text-warning mb-1">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star-half-alt"></i>
                                    <span class="text-muted ms-1 small">(24 reviews)</span>
                                </div>
                            </div>
                        </div>
                        
                        <form id="contactAgentForm" class="needs-validation" novalidate>
                            <input type="hidden" name="property_id" value="<?php echo $property['id']; ?>">
                            <input type="hidden" name="agent_id" value="<?php echo $property['agent_id']; ?>">
                            
                            <div class="mb-3">
                                <label for="name" class="form-label small fw-medium">Your Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                                <div class="invalid-feedback">Please enter your name</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label small fw-medium">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                                <div class="invalid-feedback">Please enter a valid email</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label small fw-medium">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                                <div class="invalid-feedback">Please enter your phone number</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="message" class="form-label small fw-medium">Message</label>
                                <textarea class="form-control" id="message" name="message" rows="3" required>I'm interested in <?php echo htmlspecialchars($property['title']); ?>. Please contact me with more details.</textarea>
                                <div class="invalid-feedback">Please enter your message</div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-paper-plane me-2"></i> Send Message
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Property Location -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h4 fw-bold mb-4">Location</h2>
                    <div id="propertyMap" style="height: 400px; width: 100%; border-radius: 8px; overflow: hidden;"></div>
                    <div class="mt-3 text-muted small">
                        <i class="fas fa-map-marker-alt text-danger me-2"></i>
                        <?php echo htmlspecialchars($property['address']); ?>
                    </div>
                </div>
            </div>
            
            <!-- Similar Properties -->
            <?php if (!empty($similarProperties)): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h4 fw-bold mb-4">Similar Properties</h2>
                    <div class="row g-4">
                        <?php foreach (array_slice($similarProperties, 0, 3) as $similar): ?>
                        <div class="col-md-6 col-lg-12">
                            <div class="card h-100 border-0 shadow-sm-hover">
                                <div class="row g-0 h-100">
                                    <div class="col-md-4">
                                        <img src="<?php echo !empty($similar['gallery_images']) ? htmlspecialchars($similar['gallery_images'][0]) : 'assets/images/property-placeholder.jpg'; ?>" 
                                             class="img-fluid rounded-start h-100 object-fit-cover" 
                                             alt="<?php echo htmlspecialchars($similar['title']); ?>"
                                             onerror="this.src='assets/images/property-placeholder.jpg'">
                                    </div>
                                    <div class="col-md-8">
                                        <div class="card-body d-flex flex-column h-100">
                                            <h5 class="card-title mb-1">
                                                <a href="property-details.php?id=<?php echo $similar['id']; ?>" class="text-decoration-none text-dark">
                                                    <?php echo htmlspecialchars($similar['title']); ?>
                                                </a>
                                            </h5>
                                            <p class="text-muted small mb-2">
                                                <i class="fas fa-map-marker-alt text-primary me-1"></i>
                                                <?php echo htmlspecialchars($similar['location']); ?>
                                            </p>
                                            <div class="d-flex align-items-center mb-2">
                                                <span class="badge bg-primary bg-opacity-10 text-primary me-2">
                                                    <?php echo $similar['bedrooms']; ?> Beds
                                                </span>
                                                <span class="badge bg-primary bg-opacity-10 text-primary me-2">
                                                    <?php echo $similar['bathrooms']; ?> Baths
                                                </span>
                                                <span class="badge bg-primary bg-opacity-10 text-primary">
                                                    <?php echo number_format($similar['area']); ?> sq.ft
                                                </span>
                                            </div>
                                            <div class="mt-auto">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="h5 mb-0 text-primary">
                                                        <?php echo formatPrice($similar['price']); ?>
                                                    </span>
                                                    <a href="property-details.php?id=<?php echo $similar['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        View Details
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Inquiry Modal -->
<div class="modal fade" id="inquiryModal" tabindex="-1" aria-labelledby="inquiryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="inquiryModalLabel">Inquire About This Property</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="inquiryForm" class="needs-validation" novalidate action="/apsdreamhome/process_lead.php" method="post">
                    <input type="hidden" name="property_id" value="<?php echo $property['id']; ?>">
                    <div class="mb-3">
                        <label for="inquiryName" class="form-label">Your Name</label>
                        <input type="text" class="form-control" id="inquiryName" name="name" required>
                        <div class="invalid-feedback">Please provide your name.</div>
                    </div>
                    <div class="mb-3">
                        <label for="inquiryEmail" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="inquiryEmail" name="email" required>
                        <div class="invalid-feedback">Please provide a valid email.</div>
                    </div>
                    <div class="mb-3">
                        <label for="inquiryPhone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="inquiryPhone" name="phone" required>
                        <div class="invalid-feedback">Please provide your phone number.</div>
                    </div>
                    <div class="mb-3">
                        <label for="inquiryMessage" class="form-label">Message</label>
                        <textarea class="form-control" id="inquiryMessage" name="message" rows="3" required></textarea>
                        <div class="invalid-feedback">Please provide a message.</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="submitInquiry()">Submit Inquiry</button>
            </div>
        </div>
    </div>
</div>

<!-- Schedule Visit Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1" aria-labelledby="scheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="scheduleModalLabel">Schedule a Visit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="scheduleForm" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="scheduleName" class="form-label">Your Name</label>
                        <input type="text" class="form-control" id="scheduleName" required>
                        <div class="invalid-feedback">Please provide your name.</div>
                    </div>
                    <div class="mb-3">
                        <label for="scheduleEmail" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="scheduleEmail" required>
                        <div class="invalid-feedback">Please provide a valid email.</div>
                    </div>
                    <div class="mb-3">
                        <label for="schedulePhone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="schedulePhone" required>
                        <div class="invalid-feedback">Please provide your phone number.</div>
                    </div>
                    <div class="mb-3">
                        <label for="scheduleDate" class="form-label">Preferred Date</label>
                        <input type="date" class="form-control" id="scheduleDate" required>
                        <div class="invalid-feedback">Please select a date.</div>
                    </div>
                    <div class="mb-3">
                        <label for="scheduleTime" class="form-label">Preferred Time</label>
                        <select class="form-select" id="scheduleTime" required>
                            <option value="" selected disabled>Select a time</option>
                            <?php
                            $times = array('10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00');
                            foreach ($times as $time):
                            ?>
                            <option value="<?php echo $time; ?>"><?php echo date('h:i A', strtotime($time)); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="scheduleNotes" class="form-label">Additional Notes</label>
                        <textarea class="form-control" id="scheduleNotes" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="submitSchedule()">Schedule Visit</button>
            </div>
        </div>
    </div>
</div>

<!-- Property Details CSS -->
<style>
    /* Additional styles moved to head section */
    
    /* Property Header Styles */
    .property-header {
        position: relative;
        z-index: 10;
    }
    
    .property-price {
        font-family: 'Poppins', sans-serif;
    }
    
    .property-info {
        background-color: #f8f9fa;
    }
    
    .property-features .card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .property-features .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    
    /* Contact Form Styles */
    .contact-agent .form-control:focus {
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 0.25rem rgba(var(--bs-primary-rgb), 0.25);
    }
    
    .contact-agent .btn-primary {
        padding: 0.75rem 1.5rem;
        font-weight: 500;
    }
    
    /* Agent Card */
    .agent-card {
        transition: all 0.3s ease;
    }
    
    .agent-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    
    .agent-rating i {
        font-size: 0.9rem;
    }
    
    /* Gallery Styles */
    .property-gallery {
        position: relative;
    }
    
    .property-main-image {
        transition: transform 0.3s ease;
    }
    
    .property-main-image:hover {
        transform: scale(1.01);
    }
    
    .gallery-thumb {
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    
    .gallery-thumb:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        border-color: var(--bs-primary);
    }
    
    .btn-view-gallery {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* General Styles */
    .property-hero {
        background-color: #f8f9fa;
    }
    
    .price-box {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 0.5rem;
        display: inline-block;
    }
    
    .price-label {
        color: #6c757d;
        font-size: 0.9rem;
        display: block;
    }
    
    .price {
        color: #0d6efd;
        font-size: 2rem;
        font-weight: bold;
        margin: 0;
    }
    
    .property-image-main img {
        width: 100%;
        height: 500px;
        object-fit: cover;
    }
    
    .property-image-thumbs {
        scrollbar-width: thin;
        scrollbar-color: #0d6efd #f8f9fa;
    }
    
    .property-image-thumbs::-webkit-scrollbar {
        height: 6px;
    }
    
    .property-image-thumbs::-webkit-scrollbar-track {
        background: #f8f9fa;
    }
    
    .property-image-thumbs::-webkit-scrollbar-thumb {
        background-color: #0d6efd;
        border-radius: 3px;
    }
    
    .thumb-image {
        cursor: pointer;
        border: 2px solid transparent;
        border-radius: 0.25rem;
        transition: border-color 0.3s ease;
    }
    
    .thumb-image:hover {
        border-color: #0d6efd;
    }
    
    .feature-box {
        padding: 1rem;
        border-radius: 0.5rem;
        background: #f8f9fa;
    }
    
    .amenity-item {
        padding: 0.5rem;
        background: #f8f9fa;
        border-radius: 0.25rem;
        margin-bottom: 0.5rem;
    }
    
    @media (max-width: 768px) {
        .property-image-main img {
            height: 300px;
        }
    }
    
    @media (prefers-reduced-motion: reduce) {
        .thumb-image {
            transition: none;
        }
    }
</style>

<!-- Property Details JavaScript -->
<script>
function updateMainImage(src) {
    document.getElementById('mainPropertyImage').src = src;
}

function submitInquiry() {
    // In a real implementation, this would send the form data to the server
    const form = document.getElementById('inquiryForm');
    if (form.checkValidity()) {
        alert('Thank you for your inquiry. We will contact you soon!');
        $('#inquiryModal').modal('hide');
        form.reset();
    } else {
        form.classList.add('was-validated');
    }
}

function submitSchedule() {
    // In a real implementation, this would send the form data to the server
    const form = document.getElementById('scheduleForm');
    if (form.checkValidity()) {
        alert('Thank you for scheduling a visit. We will confirm your appointment soon!');
        $('#scheduleModal').modal('hide');
        form.reset();
    } else {
        form.classList.add('was-validated');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    var forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
});
</script>

<?php
// Close PHP tag to start JavaScript code
?>

<script>
// Mortgage Calculator Functionality
function calculateMortgage(principal, years, rate) {
    var monthlyRate = rate / 100 / 12;
    var numPayments = years * 12;
    var x = Math.pow(1 + monthlyRate, numPayments);
    var monthly = (principal * monthlyRate * x) / (x - 1);
    var totalPayment = monthly * numPayments;
    var totalInterest = totalPayment - principal;
    
    return {
        monthly: Math.round(monthly),
        totalPayment: Math.round(totalPayment),
        totalInterest: Math.round(totalInterest)
    };
}

// Format currency in Indian Rupees
function formatINR(amount) {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        maximumFractionDigits: 0
    }).format(amount);
}

// Handle visit form submission
function handleVisitFormSubmit(e) {
    e.preventDefault();
    
    var form = e.target;
    if (!form.checkValidity()) {
        e.stopPropagation();
        form.classList.add('was-validated');
        return;
    }
    
    // Get form data
    var formData = {
        propertyId: <?php echo json_encode($property_id); ?>,
        propertyTitle: <?php echo json_encode($property['title']); ?>,
        date: document.getElementById('visitDate').value,
        time: document.getElementById('visitTime').value,
        name: document.getElementById('visitName').value,
        phone: document.getElementById('visitPhone').value
    };
    
    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Scheduling...';
    
    // Simulate API call (replace with actual API call)
    setTimeout(() => {
        // Reset button state
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
        
        // Show success message
        const alertHtml = `
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                Your visit has been scheduled successfully! Our agent will contact you shortly to confirm.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        // Insert alert before form
        form.insertAdjacentHTML('beforebegin', alertHtml);
        
        // Reset form
        form.reset();
        form.classList.remove('was-validated');
        
        // Scroll to top of form
        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
        
    }, 1500);
}

// Initialize Price History Chart
function initPriceHistoryChart() {
    var ctx = document.getElementById('priceHistoryChart');
    if (!ctx) return;
    
    // Sample data - replace with actual data from your database
    const labels = [
        'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
        'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
    ];
    
    // Generate some sample data around the current price
    const basePrice = <?php echo $property['price']; ?>;
    const priceData = [];
    for (let i = 0; i < 12; i++) {
        // Generate some random variation around the base price
        const variation = (Math.random() * 0.1) - 0.05; // -5% to +5%
        priceData.push(basePrice * (1 + variation));
    }
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Price Trend',
                data: priceData,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                tension: 0.4,
                fill: true,
                borderWidth: 2,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#0d6efd',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return '₹' + context.raw.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    grid: {
                        drawBorder: false
                    },
                    ticks: {
                        callback: function(value) {
                            return '₹' + (value / 100000).toFixed(1) + 'L';
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

// Initialize tooltips
function initTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// Handle Callback Form Submission
function handleCallbackFormSubmit(e) {
    e.preventDefault();
    
    const form = e.target;
    if (!form.checkValidity()) {
        e.stopPropagation();
        form.classList.add('was-validated');
        return;
    }
    
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;
    
    // Show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing...';
    
    // Simulate API call (replace with actual API call)
    setTimeout(() => {
        // Reset button state
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
        
        // Show success message
        const alertHtml = `
            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                Thank you! Our representative will call you shortly.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        // Insert alert before form
        form.insertAdjacentHTML('afterend', alertHtml);
        
        // Reset form
        form.reset();
        form.classList.remove('was-validated');
        
    }, 1500);
}

// Handle Save Property Button
function handleSaveProperty() {
    var btn = document.getElementById('savePropertyBtn');
    if (!btn) return;
    
    var icon = btn.querySelector('i');
    const isSaved = btn.classList.contains('saved');
    
    if (isSaved) {
        btn.classList.remove('saved', 'btn-primary');
        btn.classList.add('btn-outline-secondary');
        icon.className = 'far fa-bookmark me-2';
        btn.innerHTML = '<i class="far fa-bookmark me-2"></i>Save Property';
        // Show toast or alert for unsaved
        showToast('Property removed from saved list', 'info');
    } else {
        btn.classList.add('saved', 'btn-primary');
        btn.classList.remove('btn-outline-secondary');
        icon.className = 'fas fa-bookmark me-2';
        btn.innerHTML = '<i class="fas fa-bookmark me-2"></i>Saved';
        // Show toast or alert for saved
        showToast('Property saved to your list', 'success');
    }
}

// Show toast notification
function showToast(message, type = 'success') {
    const toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) return;
    
    const toastId = 'toast-' + Date.now();
    const toastHtml = `
        <div id="${toastId}" class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-info-circle'} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    var toastEl = document.getElementById(toastId);
    var toast = new bootstrap.Toast(toastEl, { delay: 3000 });
    toast.show();
    
    // Remove toast after it's hidden
    toastEl.addEventListener('hidden.bs.toast', function () {
        toastEl.remove();
    });
}

// Handle Schedule Visit Modal
function initScheduleVisitModal() {
    const modal = new bootstrap.Modal(document.getElementById('scheduleVisitModal'));
    const successModal = new bootstrap.Modal(document.getElementById('successModal'));
    const visitForm = document.getElementById('scheduleVisitForm');
    
    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('visitDate').min = today;
    
    // Handle schedule visit button clicks
    document.querySelectorAll('.schedule-visit-btn').forEach(button => {
        button.addEventListener('click', function() {
            const propertyId = this.getAttribute('data-property-id');
            const propertyTitle = this.getAttribute('data-property-title');
            
            document.getElementById('propertyId').value = propertyId;
            document.getElementById('selectedPropertyTitle').textContent = propertyTitle;
            
            // Reset form
            if (visitForm) {
                visitForm.reset();
                visitForm.classList.remove('was-validated');
            }
            
            modal.show();
        });
    });
    
    // Handle form submission
    if (visitForm) {
        visitForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!this.checkValidity()) {
                e.stopPropagation();
                this.classList.add('was-validated');
                return;
            }
            
            // Get form data
            const formData = {
                propertyId: document.getElementById('propertyId').value,
                name: document.getElementById('visitName').value,
                email: document.getElementById('visitEmail').value,
                phone: document.getElementById('visitPhone').value,
                date: document.getElementById('visitDate').value,
                time: document.getElementById('visitTime').value,
                message: document.getElementById('visitMessage').value
            };
            
            // Here you would typically send the data to your server
            console.log('Scheduling visit:', formData);
            
            // Simulate API call
            setTimeout(() => {
                // Hide the form modal
                modal.hide();
                
                // Show success modal
                successModal.show();
                
                // Reset form
                this.reset();
                this.classList.remove('was-validated');
                
            }, 1000);
        });
    }
}

// Format currency in Indian format
function formatIndianCurrency(amount) {
    amount = Math.round(amount);
    var isNegative = amount < 0;
    amount = Math.abs(amount);
    
    if (amount === 0) return '₹0';
    
    const isCrore = amount >= 10000000;
    const isLakh = amount >= 100000;
    
    let formattedAmount;
    
    if (isCrore) {
        formattedAmount = '₹' + (amount / 10000000).toFixed(2) + ' Cr';
    } else if (isLakh) {
        formattedAmount = '₹' + (amount / 100000).toFixed(2) + ' L';
    } else {
        formattedAmount = '₹' + amount.toLocaleString('en-IN');
    }
    
    return isNegative ? '-' + formattedAmount : formattedAmount;
}

// Calculate EMI
function calculateEMI(principal, rate, years) {
    const monthlyRate = rate / 12 / 100;
    const numberOfPayments = years * 12;
    
    if (monthlyRate === 0) {
        return principal / numberOfPayments;
    }
    
    const emi = principal * monthlyRate * Math.pow(1 + monthlyRate, numberOfPayments) / 
                (Math.pow(1 + monthlyRate, numberOfPayments) - 1);
    
    return emi;
}

// Update EMI Calculator
function updateEmiCalculator() {
    const principal = parseFloat(document.getElementById('loanAmount').value) || 0;
    const rate = parseFloat(document.getElementById('interestRate').value) || 0;
    const years = parseInt(document.getElementById('loanTenure').value) || 1;
    
    // Ensure minimum values
    const safePrincipal = Math.max(100000, principal);
    const safeRate = Math.max(1, Math.min(30, rate));
    const safeYears = Math.max(1, Math.min(30, years));
    
    // Update sliders
    document.getElementById('loanAmount').value = safePrincipal;
    document.getElementById('loanAmountRange').value = safePrincipal;
    document.getElementById('interestRate').value = safeRate;
    document.getElementById('interestRateRange').value = safeRate;
    document.getElementById('loanTenure').value = safeYears;
    document.getElementById('loanTenureRange').value = safeYears;
    
    // Calculate EMI
    const emi = calculateEMI(safePrincipal, safeRate, safeYears);
    const totalPayment = emi * safeYears * 12;
    const totalInterest = totalPayment - safePrincipal;
    
    // Update UI
    document.getElementById('monthlyEmi').textContent = formatIndianCurrency(emi);
    document.getElementById('totalInterest').textContent = formatIndianCurrency(totalInterest);
    document.getElementById('totalPayment').textContent = formatIndianCurrency(totalPayment);
}

// Initialize EMI Calculator
function initEmiCalculator() {
    // Sync range sliders with number inputs
    document.getElementById('loanAmount').addEventListener('input', function() {
        document.getElementById('loanAmountRange').value = this.value;
        updateEmiCalculator();
    });
    
    document.getElementById('loanAmountRange').addEventListener('input', function() {
        document.getElementById('loanAmount').value = this.value;
        updateEmiCalculator();
    });
    
    document.getElementById('interestRate').addEventListener('input', function() {
        document.getElementById('interestRateRange').value = this.value;
        updateEmiCalculator();
    });
    
    document.getElementById('interestRateRange').addEventListener('input', function() {
        document.getElementById('interestRate').value = this.value;
        updateEmiCalculator();
    });
    
    document.getElementById('loanTenure').addEventListener('input', function() {
        document.getElementById('loanTenureRange').value = this.value;
        updateEmiCalculator();
    });
    
    document.getElementById('loanTenureRange').addEventListener('input', function() {
        document.getElementById('loanTenure').value = this.value;
        updateEmiCalculator();
    });
    
    // Initial calculation
    updateEmiCalculator();
    
    // Handle apply for loan button
    const applyBtn = document.getElementById('applyForLoanBtn');
    if (applyBtn) {
        applyBtn.addEventListener('click', function() {
            // Switch to pre-approval tab
            const preapprovalTab = new bootstrap.Tab(document.getElementById('preapproval-tab'));
            preapprovalTab.show();
            
            // Scroll to the form
            document.getElementById('preapproval').scrollIntoView({ behavior: 'smooth' });
        });
    }
    
    // Handle pre-approval form submission
    const preapprovalForm = document.getElementById('preapprovalForm');
    if (preapprovalForm) {
        preapprovalForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!this.checkValidity()) {
                e.stopPropagation();
                this.classList.add('was-validated');
                return;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing...';
            
            // Simulate API call
            setTimeout(() => {
                // Reset button state
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
                
                // Show success message
                showToast('Pre-approval request submitted successfully!', 'success');
                
                // Reset form
                this.reset();
                this.classList.remove('was-validated');
                
            }, 2000);
        });
    }
}

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    // Initialize EMI Calculator
    if (typeof initEmiCalculator === 'function') {
        initEmiCalculator();
    }
    // Initialize schedule visit modal
    if (typeof initScheduleVisitModal === 'function') {
        initScheduleVisitModal();
    }
    // Initialize tooltips
    initTooltips();
    
    // Initialize price history chart
    initPriceHistoryChart();
    
    // Initialize callback form
    const callbackForm = document.getElementById('callbackForm');
    if (callbackForm) {
        callbackForm.addEventListener('submit', handleCallbackFormSubmit);
    }
    
    // Initialize save property button
    const savePropertyBtn = document.getElementById('savePropertyBtn');
    if (savePropertyBtn) {
        savePropertyBtn.addEventListener('click', handleSaveProperty);
    }
    // Initialize visit form
    const visitForm = document.getElementById('visitForm');
    if (visitForm) {
        visitForm.addEventListener('submit', handleVisitFormSubmit);
    }
    
    // Initialize mortgage calculator
    const mortgageForm = document.getElementById('mortgageCalculator');
    if (mortgageForm) {
        mortgageForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const price = parseFloat(document.getElementById('homePrice').value) || 0;
            const downPayment = parseFloat(document.getElementById('downPayment').value) || 0;
            const loanTerm = parseInt(document.getElementById('loanTerm').value) || 20;
            const interestRate = parseFloat(document.getElementById('interestRate').value) || 7.5;
            
            const loanAmount = price - downPayment;
            
            if (loanAmount <= 0) {
                alert('Down payment must be less than home price');
                return;
            }
            
            const result = calculateMortgage(loanAmount, loanTerm, interestRate);
            
            document.getElementById('monthlyPayment').textContent = formatINR(result.monthly);
            document.getElementById('totalInterest').textContent = formatINR(result.totalInterest);
            document.getElementById('totalPayment').textContent = formatINR(result.totalPayment + downPayment);
            
            document.getElementById('mortgageResult').classList.remove('d-none');
        });
        
        // Trigger calculation on page load
        mortgageForm.dispatchEvent(new Event('submit'));
    }
    
    // Initialize map if element exists
    if (document.getElementById('propertyMap')) {
        // Use Leaflet.js for the map
        const map = L.map('propertyMap').setView([<?php echo $property['latitude'] ?? '20.5937'; ?>, <?php echo $property['longitude'] ?? '78.9629'; ?>], 15);
        
        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        
        // Add a marker for the property
        L.marker([<?php echo $property['latitude'] ?? '20.5937'; ?>, <?php echo $property['longitude'] ?? '78.9629'; ?>])
            .addTo(map)
            .bindPopup('<?php echo addslashes($property['title']); ?>')
            .openPopup();
    }
    
    // Handle contact form submission
    const contactForm = document.getElementById('contactAgentForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!contactForm.checkValidity()) {
                e.stopPropagation();
                contactForm.classList.add('was-validated');
                return;
            }
            
            // Get form data
            const formData = new FormData(contactForm);
            
            // Show loading state
            const submitBtn = contactForm.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Sending...';
            
            // Send form data via AJAX
            fetch('api/contact_agent.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-success mt-3';
                    alert.role = 'alert';
                    alert.innerHTML = 'Your message has been sent successfully!';
                    contactForm.parentNode.insertBefore(alert, contactForm.nextSibling);
                    
                    // Reset form
                    contactForm.reset();
                    contactForm.classList.remove('was-validated');
                    
                    // Remove success message after 5 seconds
                    setTimeout(() => {
                        alert.remove();
                    }, 5000);
                } else {
                    throw new Error(data.message || 'Failed to send message');
                }
            })
            .catch(error => {
                // Show error message
                const alert = document.createElement('div');
                alert.className = 'alert alert-danger mt-3';
                alert.role = 'alert';
                alert.textContent = error.message || 'Failed to send message. Please try again.';
                contactForm.parentNode.insertBefore(alert, contactForm.nextSibling);
                
                // Remove error message after 5 seconds
                setTimeout(() => {
                    alert.remove();
                }, 5000);
            })
            .finally(() => {
                // Reset button state
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            });
        });
    }
});

// Initialize image gallery
function initPropertyGallery() {
    var thumbnails = document.querySelectorAll('.gallery-thumbnail');
    const mainImage = document.getElementById('mainGalleryImage');
    
    if (thumbnails.length > 0 && mainImage) {
        thumbnails.forEach(thumb => {
            thumb.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Update main image
                mainImage.src = this.href;
                mainImage.alt = this.dataset.title || 'Property Image';
                
                // Update active thumbnail
                document.querySelector('.gallery-thumbnail.active')?.classList.remove('active');
                this.classList.add('active');
                
                // Prevent default link behavior
                return false;
            });
        });
    }
}

// Call the gallery initialization when the page loads
document.addEventListener('DOMContentLoaded', function() {
    initPropertyGallery();
    
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Function to handle fullscreen gallery
function openFullscreenGallery() {
    // This would be implemented with a lightbox library like GLightbox or similar
    console.log('Opening fullscreen gallery');
    // Example implementation would go here
}
</script>

<?php
require_once __DIR__ . '/includes/templates/dynamic_footer.php';
?>
