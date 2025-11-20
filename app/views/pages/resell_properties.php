<?php
/**
 * Resell Properties Listing Page
 * Public page showing approved resell properties from individual sellers
 */

session_start();
require_once '../../../includes/db_connection.php';
require_once 'includes/config.php';

$config = AppConfig::getInstance();
$conn = $config->getDatabaseConnection();

// Check if connection is valid
if (!$conn) {
    error_log("Database connection failed in resell_properties.php");
    die("Database connection failed. Please try again later.");
}

// Get filter parameters
$city_filter = $_GET['city'] ?? '';
$type_filter = $_GET['type'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';
$bedrooms = $_GET['bedrooms'] ?? '';
$search_query = $_GET['search'] ?? '';

// Build query for approved properties only
$query = "SELECT rp.*, ru.full_name, ru.mobile 
          FROM resell_properties rp 
          JOIN resell_users ru ON rp.user_id = ru.id 
          WHERE rp.status = 'approved'";

$params = [];
$types = '';

if (!empty($city_filter)) {
    $query .= " AND rp.city = ?";
    $params[] = $city_filter;
    $types .= 's';
}

if (!empty($type_filter)) {
    $query .= " AND rp.property_type = ?";
    $params[] = $type_filter;
    $types .= 's';
}

if (!empty($min_price)) {
    $query .= " AND rp.price >= ?";
    $params[] = $min_price;
    $types .= 'd';
}

if (!empty($max_price)) {
    $query .= " AND rp.price <= ?";
    $params[] = $max_price;
    $types .= 'd';
}

if (!empty($bedrooms)) {
    $query .= " AND rp.bedrooms = ?";
    $params[] = $bedrooms;
    $types .= 'i';
}

if (!empty($search_query)) {
    $query .= " AND (rp.title LIKE ? OR rp.address LIKE ? OR rp.description LIKE ?)";
    $search_param = "%$search_query%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'sss';
}

$query .= " ORDER BY rp.is_featured DESC, rp.created_at DESC";

try {
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute statement: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $properties = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    
} catch (Exception $e) {
    error_log("Database error in resell_properties.php: " . $e->getMessage());
    $properties = [];
}

// Get unique cities and types for filters
try {
    $cities_stmt = $conn->query("SELECT DISTINCT city FROM resell_properties WHERE status = 'approved' ORDER BY city");
    $cities = $cities_stmt ? $cities_stmt->fetch_all(MYSQLI_ASSOC) : [];
    
    $types_stmt = $conn->query("SELECT DISTINCT property_type FROM resell_properties WHERE status = 'approved' ORDER BY property_type");
    $property_types = $types_stmt ? $types_stmt->fetch_all(MYSQLI_ASSOC) : [];
    
    // Get price ranges for filter
    $price_stmt = $conn->query("SELECT MIN(price) as min_price, MAX(price) as max_price FROM resell_properties WHERE status = 'approved'");
    $price_range = $price_stmt ? $price_stmt->fetch_assoc() : ['min_price' => 0, 'max_price' => 0];
    
} catch (Exception $e) {
    error_log("Database error in resell_properties.php (filter queries): " . $e->getMessage());
    $cities = [];
    $property_types = [];
    $price_range = ['min_price' => 0, 'max_price' => 0];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resell Properties - Buy from Individual Sellers - APS Dream Homes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4rem 0;
            margin-bottom: 3rem;
        }
        .property-card {
            transition: transform 0.2s, box-shadow 0.2s;
            border: 1px solid #e0e0e0;
            border-radius: 15px;
            overflow: hidden;
        }
        .property-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        .featured-badge {
            background: linear-gradient(135deg, #ff6b35, #f7931e);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .filter-section {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .price-tag {
            font-size: 1.5rem;
            font-weight: 700;
            color: #28a745;
        }
        .whatsapp-btn {
            background: #25D366;
            color: white;
            border: none;
        }
        .whatsapp-btn:hover {
            background: #128C7E;
            color: white;
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <?php include 'includes/templates/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h1 class="display-4 fw-bold mb-4">Resell Properties Marketplace</h1>
                    <p class="lead mb-4">Buy directly from individual sellers. No brokerage. Verified properties. Trusted transactions.</p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="list_property.php" class="btn btn-light btn-lg">
                            <i class="fas fa-plus me-2"></i>List Your Property
                        </a>
                        <a href="#properties" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-home me-2"></i>Browse Properties
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Filters Section -->
    <section class="container">
        <div class="filter-section">
            <h3 class="mb-4"><i class="fas fa-filter me-2"></i>Find Your Dream Property</h3>
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">City</label>
                    <select name="city" class="form-select">
                        <option value="">All Cities</option>
                        <?php foreach ($cities as $city): ?>
                            <option value="<?= $city['city'] ?>" <?= $city_filter === $city['city'] ? 'selected' : '' ?>><?= $city['city'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Property Type</label>
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        <?php foreach ($property_types as $type): ?>
                            <option value="<?= $type['property_type'] ?>" <?= $type_filter === $type['property_type'] ? 'selected' : '' ?>><?= ucfirst($type['property_type']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Min Price (₹)</label>
                    <input type="number" name="min_price" class="form-control" placeholder="Min" value="<?= $min_price ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Max Price (₹)</label>
                    <input type="number" name="max_price" class="form-control" placeholder="Max" value="<?= $max_price ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Bedrooms</label>
                    <select name="bedrooms" class="form-select">
                        <option value="">Any</option>
                        <option value="1" <?= $bedrooms === '1' ? 'selected' : '' ?>>1 BHK</option>
                        <option value="2" <?= $bedrooms === '2' ? 'selected' : '' ?>>2 BHK</option>
                        <option value="3" <?= $bedrooms === '3' ? 'selected' : '' ?>>3 BHK</option>
                        <option value="4" <?= $bedrooms === '4' ? 'selected' : '' ?>>4 BHK</option>
                        <option value="5" <?= $bedrooms === '5' ? 'selected' : '' ?>>5+ BHK</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Search properties..." value="<?= htmlspecialchars($search_query) ?>">
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i>Search
                    </button>
                    <a href="resell_properties.php" class="btn btn-outline-secondary">
                        <i class="fas fa-refresh me-1"></i>Reset
                    </a>
                </div>
            </form>
            
            <?php if (!empty($city_filter) || !empty($type_filter) || !empty($min_price) || !empty($max_price) || !empty($bedrooms) || !empty($search_query)): ?>
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Filtering by: 
                        <?php 
                            $filters = [];
                            if ($city_filter) $filters[] = "City: $city_filter";
                            if ($type_filter) $filters[] = "Type: " . ucfirst($type_filter);
                            if ($min_price) $filters[] = "Min Price: ₹" . number_format($min_price);
                            if ($max_price) $filters[] = "Max Price: ₹" . number_format($max_price);
                            if ($bedrooms) $filters[] = "Bedrooms: $bedrooms";
                            if ($search_query) $filters[] = "Search: '$search_query'";
                            echo implode(', ', $filters);
                        ?>
                    </small>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Properties Section -->
    <section id="properties" class="container mb-5">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="h4">
                        <i class="fas fa-home me-2"></i>
                        Available Properties
                        <span class="badge bg-primary ms-2"><?= count($properties) ?></span>
                    </h2>
                    <a href="list_property.php" class="btn btn-success">
                        <i class="fas fa-plus me-1"></i>List Your Property FREE
                    </a>
                </div>

                <?php if (empty($properties)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-home fa-4x text-muted mb-4"></i>
                        <h4>No properties found</h4>
                        <p class="text-muted">No properties match your current filters. Try adjusting your search criteria.</p>
                        <a href="resell_properties.php" class="btn btn-primary">
                            <i class="fas fa-refresh me-1"></i>Clear Filters
                        </a>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($properties as $property): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card property-card h-100">
                                    <?php if ($property['is_featured']): ?>
                                        <div class="position-absolute top-0 start-0 m-3">
                                            <span class="featured-badge">
                                                <i class="fas fa-star me-1"></i>Featured
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="card-img-top bg-secondary" style="height: 200px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-home fa-3x text-white"></i>
                                    </div>
                                    
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h5 class="card-title"><?= htmlspecialchars($property['title']) ?></h5>
                                            <span class="price-tag">₹<?= number_format($property['price']) ?></span>
                                        </div>
                                        
                                        <div class="row mb-3">
                                            <div class="col-6">
                                                <small class="text-muted">
                                                    <i class="fas fa-bed me-1"></i><?= $property['bedrooms'] ?> Beds
                                                </small>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">
                                                    <i class="fas fa-bath me-1"></i><?= $property['bathrooms'] ?> Baths
                                                </small>
                                            </div>
                                        </div>
                                        
                                        <p class="card-text text-muted small mb-3">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            <?= htmlspecialchars($property['address']) ?>, <?= $property['city'] ?>
                                        </p>
                                        
                                        <p class="card-text small mb-3">
                                            <?= nl2br(htmlspecialchars(substr($property['description'], 0, 120))) ?><?= strlen($property['description']) > 120 ? '...' : '' ?>
                                        </p>
                                        
                                        <div class="mb-3">
                                            <small class="text-muted">
                                                <i class="fas fa-user me-1"></i>Seller: <?= htmlspecialchars($property['full_name']) ?>
                                            </small>
                                        </div>
                                        
                                        <div class="d-flex gap-2">
                                            <a href="https://wa.me/91<?= $property['mobile'] ?>?text=Hi! I'm interested in your property: <?= urlencode($property['title']) ?> - ₹<?= number_format($property['price']) ?> in <?= $property['city'] ?>" 
                                               class="btn whatsapp-btn btn-sm flex-fill" 
                                               target="_blank">
                                                <i class="fab fa-whatsapp me-1"></i>WhatsApp
                                            </a>
                                            <button class="btn btn-outline-primary btn-sm" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#detailsModal<?= $property['id'] ?>">
                                                <i class="fas fa-eye me-1"></i>View
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="card-footer text-muted small">
                                        <i class="fas fa-clock me-1"></i>Listed <?= date('M d, Y', strtotime($property['created_at'])) ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Details Modal -->
                            <div class="modal fade" id="detailsModal<?= $property['id'] ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title"><?= htmlspecialchars($property['title']) ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="bg-secondary rounded mb-3" style="height: 250px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="fas fa-home fa-4x text-white"></i>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <h4 class="text-primary mb-3">₹<?= number_format($property['price']) ?></h4>
                                                    
                                                    <div class="row mb-3">
                                                        <div class="col-6">
                                                            <strong><i class="fas fa-bed me-1"></i>Bedrooms:</strong><br>
                                                            <?= $property['bedrooms'] ?>
                                                        </div>
                                                        <div class="col-6">
                                                            <strong><i class="fas fa-bath me-1"></i>Bathrooms:</strong><br>
                                                            <?= $property['bathrooms'] ?>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <strong><i class="fas fa-ruler-combined me-1"></i>Area:</strong><br>
                                                        <?= number_format($property['area']) ?> sq.ft.
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <strong><i class="fas fa-map-marker-alt me-1"></i>Location:</strong><br>
                                                        <?= htmlspecialchars($property['address']) ?>, <?= $property['city'] ?>, <?= $property['state'] ?>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-4">
                                                <h6>Description</h6>
                                                <p class="text-muted"><?= nl2br(htmlspecialchars($property['description'])) ?></p>
                                            </div>
                                            
                                            <div class="mb-4">
                                                <h6>Seller Information</h6>
                                                <div class="card">
                                                    <div class="card-body">
                                                        <strong>Name:</strong> <?= htmlspecialchars($property['full_name']) ?><br>
                                                        <strong>Mobile:</strong> <?= htmlspecialchars($property['mobile']) ?><br>
                                                        <strong>Property Type:</strong> <?= ucfirst($property['property_type']) ?><br>
                                                        <strong>Listed On:</strong> <?= date('M d, Y', strtotime($property['created_at'])) ?>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="text-center">
                                                <a href="https://wa.me/91<?= $property['mobile'] ?>?text=Hi! I'm interested in your property: <?= urlencode($property['title']) ?> - ₹<?= number_format($property['price']) ?> in <?= $property['city'] ?>" 
                                                   class="btn whatsapp-btn btn-lg me-2" 
                                                   target="_blank">
                                                    <i class="fab fa-whatsapp me-1"></i>Contact via WhatsApp
                                                </a>
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="bg-light py-5">
        <div class="container text-center">
            <h3 class="mb-4">Ready to Sell Your Property?</h3>
            <p class="lead mb-4">List your property for FREE and connect with genuine buyers directly</p>
            <a href="list_property.php" class="btn btn-primary btn-lg">
                <i class="fas fa-plus me-2"></i>List Your Property Now
            </a>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/templates/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>