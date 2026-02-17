<?php
/**
 * Properties Listing Page
 * 
 * @package APS Dream Homes
 * @since 1.0.0
 */

// Set page metadata
$page_title = "Properties - APS Dream Homes";
$meta_description = "Browse our exclusive collection of premium properties across Uttar Pradesh. Find your dream home with APS Dream Homes.";
$current_page = 'properties';

// Include necessary files
require_once 'includes/config/config.php';
require_once 'includes/config/database.php';
require_once 'includes/helpers/format_helpers.php';

// Start secure session
start_secure_session('aps_dream_home');

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Process search/filter parameters
$search_params = [
    'location' => isset($_GET['location']) ? htmlspecialchars($_GET['location'], ENT_QUOTES, 'UTF-8') : '',
    'type' => isset($_GET['type']) ? htmlspecialchars($_GET['type'], ENT_QUOTES, 'UTF-8') : '',
    'min_price' => isset($_GET['min_price']) ? filter_var($_GET['min_price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : 0,
    'max_price' => isset($_GET['max_price']) ? filter_var($_GET['max_price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : PHP_FLOAT_MAX,
    'bedrooms' => filter_input(INPUT_GET, 'bedrooms', FILTER_VALIDATE_INT) ?? 0,
    'bathrooms' => filter_input(INPUT_GET, 'bathrooms', FILTER_VALIDATE_INT) ?? 0,
    'page' => max(1, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?? 1),
    'per_page' => 12 // Items per page
];

// Get sort parameters
$sort = isset($_GET['sort']) ? htmlspecialchars($_GET['sort'], ENT_QUOTES, 'UTF-8') : 'featured';
$order = isset($_GET['order']) ? htmlspecialchars($_GET['order'], ENT_QUOTES, 'UTF-8') : 'DESC';

// Include header template
require_once __DIR__ . '/includes/templates/header.php';

// Database connection and property fetching
try {
    $db = DatabaseConfig::getConnection();
    
    // Get property types for filter
    $type_query = "SELECT id, name, slug FROM property_types WHERE status = 'active' ORDER BY name ASC";
    $type_stmt = $db->prepare($type_query);
    $type_stmt->execute();
    $type_result = $type_stmt->get_result();
    $property_types = $type_result->fetch_all(MYSQLI_ASSOC);
    
    // Get locations for filter
    $loc_query = "SELECT DISTINCT location FROM properties WHERE status = 'active' AND location IS NOT NULL AND location != '' ORDER BY location ASC";
    $loc_stmt = $db->prepare($loc_query);
    $loc_stmt->execute();
    $loc_result = $loc_stmt->get_result();
    $locations = $loc_result->fetch_all(MYSQLI_ASSOC);
    
    // Build base query with parameter placeholders
    $query = "SELECT SQL_CALC_FOUND_ROWS
                p.id, p.title, p.location, p.price, p.bedrooms, p.bathrooms,
                p.area, p.area_unit, p.featured_image, p.listing_status, p.is_featured,
                p.created_at, p.updated_at, p.description,
                pt.name as property_type, pt.slug as type_slug,
                GROUP_CONCAT(DISTINCT pa.amenity_name) as amenities
              FROM properties p
              LEFT JOIN property_types pt ON p.property_type_id = pt.id
              LEFT JOIN property_amenities pa ON p.id = pa.property_id
              WHERE p.status = 'active'";
    
    // Add search conditions based on parameters
    $conditions = [];
    $params = [];
    $types = '';
    
    if (!empty($search_params['location'])) {
        $conditions[] = "(p.location LIKE ? OR p.city LIKE ? OR p.state LIKE ?)";
        $location_param = "%" . $search_params['location'] . "%";
        $params[] = $location_param;
        $params[] = $location_param;
        $params[] = $location_param;
        $types .= 'sss';
    }
    
    if (!empty($search_params['type'])) {
        $conditions[] = "pt.slug = ?";
        $params[] = $search_params['type'];
        $types .= 's';
    }
    
    if ($search_params['bedrooms'] > 0) {
        $conditions[] = "p.bedrooms >= ?";
        $params[] = $search_params['bedrooms'];
        $types .= 'i';
    }
    
    if ($search_params['bathrooms'] > 0) {
        $conditions[] = "p.bathrooms >= ?";
        $params[] = $search_params['bathrooms'];
        $types .= 'i';
    }
    
    // Add price range conditions
    if ($search_params['min_price'] > 0 || $search_params['max_price'] < PHP_FLOAT_MAX) {
        $conditions[] = "p.price BETWEEN ? AND ?";
        $params[] = $search_params['min_price'];
        $params[] = $search_params['max_price'];
        $types .= 'dd';
    }
    
    // Add conditions to query if any exist
    if (!empty($conditions)) {
        $query .= " AND " . implode(" AND ", $conditions);
    }
    
    // Group by and order
    $query .= " GROUP BY p.id";
    
    // Add sorting
    $sort_options = [
        'price' => 'p.price',
        'date' => 'p.created_at',
        'featured' => 'p.is_featured',
        'name' => 'p.title'
    ];
    
    $sort_column = $sort_options[$sort] ?? 'p.is_featured';
    $order = in_array(strtoupper($order), ['ASC', 'DESC']) ? strtoupper($order) : 'DESC';
    
    $query .= " ORDER BY {$sort_column} {$order}, p.created_at DESC";
    
    // Add pagination
    $offset = ($search_params['page'] - 1) * $search_params['per_page'];
    $query .= " LIMIT ? OFFSET ?";
    $params[] = $search_params['per_page'];
    $params[] = $offset;
    $types .= 'ii';
    
    // Prepare and execute the query
    $stmt = $db->prepare($query);
    
    // Bind parameters if any exist
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $properties = $result->fetch_all(MYSQLI_ASSOC);
    
    // Get total count for pagination
    $total_result = $db->query("SELECT FOUND_ROWS() as total");
    $total_rows = $total_result->fetch_assoc()['total'];
    $total_pages = ceil($total_rows / $search_params['per_page']);
    
    // Format properties data
    $formatted_properties = [];
    foreach ($properties as $property) {
        $formatted_properties[] = [
            'id' => $property['id'],
            'title' => htmlspecialchars($property['title']),
            'location' => htmlspecialchars($property['location']),
            'price' => format_currency($property['price']),
            'price_raw' => (float)$property['price'],
            'bedrooms' => $property['bedrooms'] ?? 0,
            'bathrooms' => $property['bathrooms'] ?? 0,
            'area' => number_format($property['area']) . ' ' . ($property['area_unit'] ?? 'sq.ft'),
            'image' => !empty($property['featured_image']) ? 
                      htmlspecialchars($property['featured_image']) : 
                      SITE_URL . '/assets/images/properties/default.jpg',
            'type' => htmlspecialchars($property['property_type'] ?? 'Property'),
            'type_slug' => $property['type_slug'] ?? 'property',
            'status' => ucfirst($property['listing_status'] ?? 'Available'),
            'is_featured' => (bool)($property['is_featured'] ?? false),
            'amenities' => !empty($property['amenities']) ? 
                          array_map('htmlspecialchars', explode(',', $property['amenities'])) : [],
            'created_at' => $property['created_at'] ?? '',
            'description' => htmlspecialchars($property['description'] ?? '')
        ];
    }
    
    $properties = $formatted_properties;
    
} catch (Exception $e) {
    // Log error and show user-friendly message
    error_log('Database error in properties.php: ' . $e->getMessage());
    $error_message = 'Unable to load properties at this time. Please try again later.';
    $properties = [];
    $total_pages = 0;
    $total_rows = 0;
}
?>

<!-- Hero Section with Search -->
<section class="hero-section bg-primary text-white py-5">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-5 fw-bold mb-3">Find Your Dream Home</h1>
                <p class="lead mb-4">Discover the perfect property that matches your lifestyle and budget</p>
                
                <!-- Search Form -->
                <form action="" method="GET" class="search-form bg-white p-4 rounded-3 shadow">
                    <input type="hidden" name="page" value="1">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="fas fa-map-marker-alt text-muted"></i></span>
                                <select class="form-select" name="location" id="location">
                                    <option value="">Any Location</option>
                                    <?php foreach ($locations as $loc): ?>
                                        <option value="<?php echo htmlspecialchars($loc['location']); ?>" <?php echo ($search_params['location'] === $loc['location']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($loc['location']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="fas fa-home text-muted"></i></span>
                                <select class="form-select" name="type" id="propertyType">
                                    <option value="">Any Type</option>
                                    <?php foreach ($property_types as $type): ?>
                                        <option value="<?php echo htmlspecialchars($type['slug']); ?>" <?php echo ($search_params['type'] === $type['slug']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($type['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i> Search
                            </button>
                        </div>
                    </div>
                    
                    <!-- Advanced Filters Toggle -->
                    <div class="text-center mt-3">
                        <a href="#advanced-filters" class="text-decoration-none small" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="advanced-filters">
                            <i class="fas fa-sliders-h me-1"></i> Advanced Filters
                        </a>
                    </div>
                    
                    <!-- Advanced Filters -->
                    <div class="collapse mt-3" id="advanced-filters">
                        <div class="row g-3 pt-3 border-top">
                            <div class="col-md-4">
                                <label for="minPrice" class="form-label small text-muted mb-1">Min Price</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" class="form-control" id="minPrice" name="min_price" min="0" step="100000" value="<?php echo $search_params['min_price'] > 0 ? $search_params['min_price'] : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="maxPrice" class="form-label small text-muted mb-1">Max Price</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" class="form-control" id="maxPrice" name="max_price" min="0" step="100000" value="<?php echo $search_params['max_price'] < PHP_FLOAT_MAX ? $search_params['max_price'] : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label for="bedrooms" class="form-label small text-muted mb-1">Beds</label>
                                <select class="form-select" id="bedrooms" name="bedrooms">
                                    <option value="0">Any</option>
                                    <?php for ($i = 1; $i <= 6; $i++): ?>
                                        <option value="<?php echo $i; ?>" <?php echo ($search_params['bedrooms'] == $i) ? 'selected' : ''; ?>>
                                            <?php echo $i; ?>+ <?php echo ($i === 1) ? 'Bed' : 'Beds'; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="bathrooms" class="form-label small text-muted mb-1">Baths</label>
                                <select class="form-select" id="bathrooms" name="bathrooms">
                                    <option value="0">Any</option>
                                    <?php for ($i = 1; $i <= 6; $i++): ?>
                                        <option value="<?php echo $i; ?>" <?php echo ($search_params['bathrooms'] == $i) ? 'selected' : ''; ?>>
                                            <?php echo $i; ?>+ <?php echo ($i === 1) ? 'Bath' : 'Baths'; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<main class="py-5">
    <div class="container">
        <!-- Results Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div class="mb-3 mb-md-0">
                <h2 class="h4 mb-1"><?php echo $total_rows; ?> Properties Found</h2>
                <p class="text-muted small mb-0">Showing <?php echo count($properties); ?> of <?php echo $total_rows; ?> results</p>
            </div>
            
            <!-- Sort Options -->
            <div class="d-flex align-items-center">
                <div class="me-3 d-none d-md-block">
                    <span class="small text-muted">Sort by:</span>
                </div>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="sortDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php 
                        $sort_labels = [
                            'featured' => 'Featured',
                            'price-asc' => 'Price: Low to High',
                            'price-desc' => 'Price: High to Low',
                            'date' => 'Newest First',
                            'name' => 'Name (A-Z)'
                        ];
                        $current_sort = (isset($_GET['sort']) ? $_GET['sort'] : 'featured') . (isset($_GET['order']) && $_GET['order'] === 'asc' ? '-asc' : '-desc');
                        ?>
                        <?php echo $sort_labels[explode('-', $current_sort)[0]] ?? 'Featured'; ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="sortDropdown">
                        <li><h6 class="dropdown-header">Sort Options</h6></li>
                        <li><a class="dropdown-item <?php echo ($current_sort === 'featured-desc') ? 'active' : ''; ?>" href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'featured', 'order' => 'desc', 'page' => 1])); ?>">Featured</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item <?php echo ($current_sort === 'price-asc') ? 'active' : ''; ?>" href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'price', 'order' => 'asc', 'page' => 1])); ?>">Price: Low to High</a></li>
                        <li><a class="dropdown-item <?php echo ($current_sort === 'price-desc') ? 'active' : ''; ?>" href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'price', 'order' => 'desc', 'page' => 1])); ?>">Price: High to Low</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item <?php echo ($current_sort === 'date-desc') ? 'active' : ''; ?>" href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'date', 'order' => 'desc', 'page' => 1])); ?>">Newest First</a></li>
                        <li><a class="dropdown-item <?php echo ($current_sort === 'name-asc') ? 'active' : ''; ?>" href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'name', 'order' => 'asc', 'page' => 1])); ?>">Name (A-Z)</a></li>
                    </ul>
                </div>
                
                <!-- View Toggle -->
                <div class="btn-group ms-3 d-none d-md-flex" role="group" aria-label="View options">
                    <input type="radio" class="btn-check" name="view-options" id="grid-view" autocomplete="off" checked>
                    <label class="btn btn-outline-secondary" for="grid-view" title="Grid View">
                        <i class="fas fa-th-large"></i>
                    </label>
                    
                    <input type="radio" class="btn-check" name="view-options" id="list-view" autocomplete="off">
                    <label class="btn btn-outline-secondary" for="list-view" title="List View">
                        <i class="fas fa-list"></i>
                    </label>
                </div>
            </div>
        </div>
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <!-- Properties Grid -->
        <div class="row g-4 mb-5" id="properties-container">
            <?php if (!empty($properties)): ?>
                <?php foreach ($properties as $property): ?>
                    <div class="col-12 col-md-6 col-lg-4 col-xxl-3">
                        <div class="property-card card h-100 border-0 shadow-sm">
                            <!-- Property Image -->
                            <div class="position-relative">
                                <a href="property-details.php?id=<?php echo $property['id']; ?>" class="text-decoration-none">
                                    <img src="<?php echo $property['image']; ?>" class="card-img-top property-image" alt="<?php echo $property['title']; ?>" loading="lazy">
                                </a>
                                
                                <!-- Status Badge -->
                                <div class="position-absolute top-2 start-2">
                                    <span class="badge bg-<?php echo strtolower($property['status']) === 'for sale' ? 'success' : 'primary'; ?> bg-opacity-90">
                                        <?php echo $property['status']; ?>
                                    </span>
                                </div>
                                
                                <!-- Featured Badge -->
                                <?php if ($property['is_featured']): ?>
                                    <div class="position-absolute top-2 end-2">
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-star me-1"></i> Featured
                                        </span>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Quick Actions -->
                                <div class="position-absolute bottom-0 end-0 p-2">
                                    <button type="button" class="btn btn-light btn-sm rounded-circle shadow-sm me-1" data-bs-toggle="tooltip" title="Add to favorites">
                                        <i class="far fa-heart"></i>
                                    </button>
                                    <button type="button" class="btn btn-light btn-sm rounded-circle shadow-sm" data-bs-toggle="tooltip" title="Share">
                                        <i class="fas fa-share-alt"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Property Details -->
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <span class="badge bg-light text-dark mb-2"><?php echo $property['type']; ?></span>
                                        <h3 class="h5 card-title mb-1">
                                            <a href="property-details.php?id=<?php echo $property['id']; ?>" class="text-decoration-none text-dark">
                                                <?php echo $property['title']; ?>
                                            </a>
                                        </h3>
                                        <p class="text-muted small mb-2">
                                            <i class="fas fa-map-marker-alt text-primary me-1"></i>
                                            <?php echo $property['location']; ?>
                                        </p>
                                    </div>
                                    <div class="text-end">
                                        <div class="h5 text-primary mb-0"><?php echo $property['price']; ?></div>
                                        <small class="text-muted"><?php echo $property['area']; ?></small>
                                    </div>
                                </div>
                                
                                <!-- Property Features -->
                                <div class="property-features d-flex justify-content-between text-center border-top border-bottom py-2 my-3">
                                    <div>
                                        <div class="text-muted small">Bedrooms</div>
                                        <div class="fw-bold"><?php echo $property['bedrooms']; ?></div>
                                    </div>
                                    <div class="border-start border-end px-3 mx-3">
                                        <div class="text-muted small">Bathrooms</div>
                                        <div class="fw-bold"><?php echo $property['bathrooms']; ?></div>
                                    </div>
                                    <div>
                                        <div class="text-muted small">Area</div>
                                        <div class="fw-bold"><?php echo $property['area']; ?></div>
                                    </div>
                                </div>
                                
                                <!-- Amenities -->
                                <?php if (!empty($property['amenities'])): ?>
                                    <div class="amenities mb-3">
                                        <div class="d-flex flex-wrap gap-1">
                                            <?php foreach (array_slice($property['amenities'], 0, 3) as $amenity): ?>
                                                <span class="badge bg-light text-dark border small" data-bs-toggle="tooltip" title="<?php echo htmlspecialchars($amenity); ?>">
                                                    <i class="fas fa-check-circle text-success me-1"></i>
                                                    <?php echo $amenity; ?>
                                                </span>
                                            <?php endforeach; ?>
                                            <?php if (count($property['amenities']) > 3): ?>
                                                <span class="badge bg-light text-muted small" data-bs-toggle="tooltip" title="<?php echo htmlspecialchars(implode(', ', array_slice($property['amenities'], 3))); ?>">
                                                    +<?php echo count($property['amenities']) - 3; ?> more
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Action Buttons -->
                                <div class="d-grid gap-2">
                                    <a href="property-details.php?id=<?php echo $property['id']; ?>" class="btn btn-primary">
                                        <i class="far fa-eye me-2"></i> View Details
                                    </a>
                                    <button type="button" class="btn btn-outline-primary schedule-visit-btn" 
                                            data-property-id="<?php echo $property['id']; ?>"
                                            data-bs-toggle="modal" data-bs-target="#scheduleVisitModal">
                                        <i class="far fa-calendar-alt me-2"></i> Schedule Visit
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <div class="empty-state">
                        <i class="fas fa-search fa-3x text-muted mb-4"></i>
                        <h3>No Properties Found</h3>
                        <p class="text-muted mb-4">We couldn't find any properties matching your criteria.</p>
                        <a href="?" class="btn btn-primary">
                            <i class="fas fa-sync-alt me-2"></i> Reset Filters
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <nav aria-label="Property pagination" class="mt-5">
                <ul class="pagination justify-content-center">
                    <!-- Previous Page Link -->
                    <li class="page-item <?php echo $search_params['page'] <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $search_params['page'] - 1])); ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    
                    <!-- Page Numbers -->
                    <?php 
                    $start_page = max(1, $search_params['page'] - 2);
                    $end_page = min($total_pages, $start_page + 4);
                    $start_page = max(1, $end_page - 4);
                    
                    if ($start_page > 1): ?>
                        <li class="page-item"><a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>">1</a></li>
                        <?php if ($start_page > 2): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <li class="page-item <?php echo $i == $search_params['page'] ? 'active' : ''; ?>">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($end_page < $total_pages): ?>
                        <?php if ($end_page < $total_pages - 1): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $total_pages])); ?>">
                                <?php echo $total_pages; ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <!-- Next Page Link -->
                    <li class="page-item <?php echo $search_params['page'] >= $total_pages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $search_params['page'] + 1])); ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</main>

<!-- Schedule Visit Modal -->
<div class="modal fade" id="scheduleVisitModal" tabindex="-1" aria-labelledby="scheduleVisitModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="scheduleVisitModalLabel">Schedule a Visit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="scheduleVisitForm" method="POST" action="process_visit.php">
                <input type="hidden" name="property_id" id="schedulePropertyId" value="">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="visitDate" class="form-label">Preferred Date</label>
                        <input type="date" class="form-control" id="visitDate" name="visit_date" required 
                               min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="visitTime" class="form-label">Preferred Time</label>
                        <select class="form-select" id="visitTime" name="visit_time" required>
                            <option value="">Select a time</option>
                            <option value="09:00">09:00 AM</option>
                            <option value="10:00">10:00 AM</option>
                            <option value="11:00">11:00 AM</option>
                            <option value="12:00">12:00 PM</option>
                            <option value="13:00">01:00 PM</option>
                            <option value="14:00">02:00 PM</option>
                            <option value="15:00">03:00 PM</option>
                            <option value="16:00">04:00 PM</option>
                            <option value="17:00">05:00 PM</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="visitorName" class="form-label">Your Name</label>
                        <input type="text" class="form-control" id="visitorName" name="visitor_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="visitorEmail" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="visitorEmail" name="visitor_email" required>
                    </div>
                    <div class="mb-3">
                        <label for="visitorPhone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="visitorPhone" name="visitor_phone" required>
                    </div>
                    <div class="mb-3">
                        <label for="visitNotes" class="form-label">Additional Notes (Optional)</label>
                        <textarea class="form-control" id="visitNotes" name="visit_notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Schedule Visit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Share Modal -->
<div class="modal fade" id="shareModal" tabindex="-1" aria-labelledby="shareModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="shareModalLabel">Share Property</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="shareLink" class="form-label">Property Link</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="shareLink" readonly>
                        <button class="btn btn-outline-secondary" type="button" id="copyLinkBtn">
                            <i class="far fa-copy"></i> Copy
                        </button>
                    </div>
                </div>
                <div class="social-share mt-4">
                    <p class="mb-2">Share via:</p>
                    <div class="d-flex gap-2">
                        <a href="#" class="btn btn-outline-primary btn-sm" id="shareFacebook">
                            <i class="fab fa-facebook-f me-1"></i> Facebook
                        </a>
                        <a href="#" class="btn btn-outline-info btn-sm" id="shareTwitter">
                            <i class="fab fa-twitter me-1"></i> Twitter
                        </a>
                        <a href="#" class="btn btn-outline-success btn-sm" id="shareWhatsApp">
                            <i class="fab fa-whatsapp me-1"></i> WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/templates/footer.php'; ?>

<script>
// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Handle schedule visit button clicks
    document.querySelectorAll('.schedule-visit-btn').forEach(button => {
        button.addEventListener('click', function() {
            const propertyId = this.getAttribute('data-property-id');
            document.getElementById('schedulePropertyId').value = propertyId;
        });
    });
    
    // Handle share button clicks
    document.querySelectorAll('[data-bs-toggle="tooltip"][title="Share"]').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const propertyCard = this.closest('.property-card');
            const propertyLink = propertyCard.querySelector('a[href^="property-details.php"]').href;
            const propertyTitle = propertyCard.querySelector('.card-title').textContent.trim();
            
            const shareModal = new bootstrap.Modal(document.getElementById('shareModal'));
            const shareLinkInput = document.getElementById('shareLink');
            
            shareLinkInput.value = propertyLink;
            
            // Set up share buttons
            document.getElementById('shareFacebook').href = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(propertyLink)}`;
            document.getElementById('shareTwitter').href = `https://twitter.com/intent/tweet?url=${encodeURIComponent(propertyLink)}&text=${encodeURIComponent(propertyTitle)}`;
            document.getElementById('shareWhatsApp').href = `https://wa.me/?text=${encodeURIComponent(propertyTitle + ' - ' + propertyLink)}`;
            
            shareModal.show();
        });
    });
    
    // Handle copy link button
    document.getElementById('copyLinkBtn')?.addEventListener('click', function() {
        const shareLink = document.getElementById('shareLink');
        shareLink.select();
        document.execCommand('copy');
        
        // Show feedback
        const originalText = this.innerHTML;
        this.innerHTML = '<i class="fas fa-check"></i> Copied!';
        
        setTimeout(() => {
            this.innerHTML = originalText;
        }, 2000);
    });
    
    // Handle form submission
    document.getElementById('scheduleVisitForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
        const submitButton = this.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Scheduling...';
        
        // Simulate form submission (replace with actual AJAX call)
        setTimeout(() => {
            // Reset button state
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
            
            // Show success message
            const modal = bootstrap.Modal.getInstance(document.getElementById('scheduleVisitModal'));
            modal.hide();
            
            // Show success alert
            const alert = document.createElement('div');
            alert.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
            alert.style.zIndex = '1060';
            alert.role = 'alert';
            alert.innerHTML = `
                <i class="fas fa-check-circle me-2"></i> Your visit has been scheduled successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            
            document.body.appendChild(alert);
            
            // Initialize and show the alert
            const bsAlert = new bootstrap.Alert(alert);
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                bsAlert.close();
            }, 5000);
            
            // Reset form
            this.reset();
            
        }, 1500);
    });
    
    // Handle view toggle
    const gridView = document.getElementById('grid-view');
    const listView = document.getElementById('list-view');
    const propertiesContainer = document.getElementById('properties-container');
    
    if (gridView && listView && propertiesContainer) {
        gridView.addEventListener('change', function() {
            if (this.checked) {
                propertiesContainer.classList.remove('list-view');
                propertiesContainer.classList.add('grid-view');
            }
        });
        
        listView.addEventListener('change', function() {
            if (this.checked) {
                propertiesContainer.classList.remove('grid-view');
                propertiesContainer.classList.add('list-view');
                
                // Update property cards for list view
                document.querySelectorAll('.property-card').forEach(card => {
                    const img = card.querySelector('.property-image');
                    const details = card.querySelector('.card-body');
                    
                    if (img && details) {
                        card.classList.add('flex-row');
                        img.parentElement.classList.add('col-md-5');
                        details.classList.add('col-md-7');
                    }
                });
            }
        });
    }
});
</script>
