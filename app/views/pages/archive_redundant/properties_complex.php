<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include the template system
require_once __DIR__ . '/includes/enhanced_universal_template.php';

// Create template instance
$template = new EnhancedUniversalTemplate();

// Set page metadata
$page_title = 'Properties - APS Dream Home';
$page_description = 'Browse our extensive collection of properties for sale and rent. Find apartments, villas, houses, plots, and commercial properties in prime locations.';

// Include database connection
require_once 'includes/db_connection.php';

// Get database connection
try {
    $conn = getMysqliConnection();

    // Set default timezone
    date_default_timezone_set('Asia/Kolkata');

    // Define base URL
    $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/' . basename(dirname(__FILE__));
    define('BASE_URL', rtrim($base_url, '/'));

    // Get search parameters
    $search_type = $_GET['type'] ?? '';
    $search_location = $_GET['location'] ?? '';
    $search_min_price = $_GET['min_price'] ?? '';
    $search_max_price = $_GET['max_price'] ?? '';
    $search_bedrooms = $_GET['bedrooms'] ?? '';

    // Build search query
    $query = "
        SELECT p.id, p.title, p.address, p.price, p.bedrooms, p.bathrooms, p.area, p.status, p.description,
               u.first_name, u.last_name, u.phone as agent_phone, u.email as agent_email,
               pt.name as property_type,
               (SELECT pi.image_url FROM property_images pi WHERE pi.property_id = p.id ORDER BY pi.is_primary DESC, pi.id ASC LIMIT 1) as main_image
        FROM properties p
        LEFT JOIN users u ON p.agent_id = u.id
        LEFT JOIN property_types pt ON p.property_type_id = pt.id
        WHERE p.status = 'available'
    ";

    $params = [];
    $conditions = [];

    // Apply search filters
    if (!empty($search_type)) {
        $conditions[] = "pt.name = ?";
        $params[] = $search_type;
    }

    if (!empty($search_location)) {
        $conditions[] = "p.address LIKE ?";
        $params[] = "%$search_location%";
    }

    if (!empty($search_min_price)) {
        $conditions[] = "p.price >= ?";
        $params[] = $search_min_price;
    }

    if (!empty($search_max_price)) {
        $conditions[] = "p.price <= ?";
        $params[] = $search_max_price;
    }

    if (!empty($search_bedrooms)) {
        $conditions[] = "p.bedrooms >= ?";
        $params[] = $search_bedrooms;
    }

    if (!empty($conditions)) {
        $query .= " AND " . implode(" AND ", $conditions);
    }

    $query .= " ORDER BY p.created_at DESC";

    // Pagination
    $items_per_page = 12;
    $current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $offset = ($current_page - 1) * $items_per_page;

    $query .= " LIMIT $items_per_page OFFSET $offset";

    // Get total count for pagination
    $count_query = "SELECT COUNT(*) as total FROM properties p LEFT JOIN property_types pt ON p.property_type_id = pt.id WHERE p.status = 'available'";
    if (!empty($conditions)) {
        $count_query .= " AND " . implode(" AND ", $conditions);
    }

    $total_result = $conn->query($count_query);
    $total_properties = $total_result ? $total_result->fetch(PDO::FETCH_ASSOC)['total'] : 0;
    $total_pages = ceil($total_properties / $items_per_page);

    // Execute main query
    $properties = [];
    if (!empty($params)) {
        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $result = $conn->query($query);
        if ($result) {
            $properties = $result->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    // Get property types for filter dropdown
    $property_types = [];
    $type_query = "SELECT DISTINCT pt.name FROM property_types pt JOIN properties p ON p.property_type_id = pt.id WHERE p.status = 'available'";
    $type_result = $conn->query($type_query);
    if ($type_result) {
        while ($row = $type_result->fetch(PDO::FETCH_ASSOC)) {
            if (!empty($row['name'])) {
                $property_types[] = $row['name'];
            }
        }
    }

    // Get locations for filter dropdown
    $locations = [];
    $location_query = "SELECT DISTINCT SUBSTRING_INDEX(address, ',', 1) as city FROM properties WHERE status = 'available'";
    $location_result = $conn->query($location_query);
    if ($location_result) {
        while ($row = $location_result->fetch(PDO::FETCH_ASSOC)) {
            if (!empty($row['city'])) {
                $locations[] = $row['city'];
            }
        }
    }

} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    $error_message = "Sorry, we're experiencing technical difficulties. Please try again later.";
}

// Start output buffering
ob_start();
?>
<!-- Page Header -->
<section class="py-5 bg-primary text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="display-4 fw-bold mb-3">Properties</h1>
                <p class="lead mb-0">Find your perfect property from our extensive collection</p>
            </div>
        </div>
    </div>
</section>

<!-- Advanced Search Section -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-4">
                        <h3 class="card-title text-center mb-4">
                            <i class="fas fa-search me-2"></i>Advanced Property Search
                        </h3>
                        <form method="GET" action="properties.php" class="search-form">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="type" class="form-label fw-bold">Property Type</label>
                                    <select class="form-select" id="type" name="type">
                                        <option value="">All Types</option>
                                        <?php foreach (array_unique($property_types) as $type): ?>
                                            <option value="<?php echo htmlspecialchars($type); ?>" <?php echo $search_type === $type ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($type); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="location" class="form-label fw-bold">Location</label>
                                    <select class="form-select" id="location" name="location">
                                        <option value="">All Locations</option>
                                        <?php foreach (array_unique($locations) as $location): ?>
                                            <option value="<?php echo htmlspecialchars($location); ?>" <?php echo $search_location === $location ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($location); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="min_price" class="form-label fw-bold">Min Price</label>
                                    <input type="number" class="form-control" id="min_price" name="min_price"
                                           value="<?php echo htmlspecialchars($search_min_price); ?>" placeholder="₹0">
                                </div>
                                <div class="col-md-4">
                                    <label for="max_price" class="form-label fw-bold">Max Price</label>
                                    <input type="number" class="form-control" id="max_price" name="max_price"
                                           value="<?php echo htmlspecialchars($search_max_price); ?>" placeholder="No limit">
                                </div>
                                <div class="col-md-4">
                                    <label for="bedrooms" class="form-label fw-bold">Bedrooms</label>
                                    <select class="form-select" id="bedrooms" name="bedrooms">
                                        <option value="">Any</option>
                                        <option value="1" <?php echo $search_bedrooms === '1' ? 'selected' : ''; ?>>1 BHK</option>
                                        <option value="2" <?php echo $search_bedrooms === '2' ? 'selected' : ''; ?>>2 BHK</option>
                                        <option value="3" <?php echo $search_bedrooms === '3' ? 'selected' : ''; ?>>3 BHK</option>
                                        <option value="4" <?php echo $search_bedrooms === '4' ? 'selected' : ''; ?>>4+ BHK</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                        <button type="submit" class="btn btn-primary btn-lg px-4">
                                            <i class="fas fa-search me-2"></i>Search Properties
                                        </button>
                                        <a href="properties.php" class="btn btn-outline-secondary btn-lg px-4">
                                            <i class="fas fa-eraser me-2"></i>Clear Filters
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Search Results Summary -->
<section class="py-3 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h6 class="mb-0">
                            <?php if (!empty($search_type) || !empty($search_location) || !empty($search_min_price) || !empty($search_max_price) || !empty($search_bedrooms)): ?>
                                Search Results: <?php echo count($properties); ?> properties found
                                <?php if (!empty($search_type)): ?>
                                    <span class="badge bg-primary ms-2">Type: <?php echo htmlspecialchars($search_type); ?></span>
                                <?php endif; ?>
                                <?php if (!empty($search_location)): ?>
                                    <span class="badge bg-success ms-2">Location: <?php echo htmlspecialchars($search_location); ?></span>
                                <?php endif; ?>
                            <?php else: ?>
                                Showing <?php echo count($properties); ?> of <?php echo $total_properties; ?> total properties
                            <?php endif; ?>
                        </h6>
                    </div>
                    <div>
                        <span class="text-muted">Page <?php echo $current_page; ?> of <?php echo $total_pages; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Properties Grid -->
<section class="py-5">
    <div class="container">
        <?php if (empty($properties)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-info text-center py-5">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h4>No Properties Found</h4>
                        <p class="mb-0">Try adjusting your search criteria to find more properties.</p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($properties as $property): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card h-100 border-0 shadow-sm hover-lift">
                            <div class="position-relative">
                                <img src="<?php echo htmlspecialchars($property['main_image'] ?? 'assets/images/property-placeholder.jpg'); ?>"
                                     alt="<?php echo htmlspecialchars($property['title'] ?? 'Property'); ?>"
                                     class="card-img-top" style="height: 250px; object-fit: cover;">

                                <div class="position-absolute top-0 end-0 m-2">
                                    <span class="badge bg-primary"><?php echo htmlspecialchars($property['property_type'] ?? 'Property'); ?></span>
                                </div>

                                <div class="position-absolute top-0 start-0 m-2">
                                    <span class="badge bg-success">₹<?php echo number_format($property['price'] ?? 0); ?></span>
                                </div>

                                <button class="btn btn-light btn-sm position-absolute bottom-0 end-0 m-2" onclick="toggleFavorite(<?php echo $property['id'] ?? 0; ?>)">
                                    <i class="far fa-heart"></i>
                                </button>
                            </div>

                            <div class="card-body">
                                <h5 class="card-title">
                                    <a href="property-details.php?id=<?php echo $property['id'] ?? 0; ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($property['title'] ?? 'Untitled Property'); ?>
                                    </a>
                                </h5>

                                <p class="card-text text-muted">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    <?php echo htmlspecialchars($property['address'] ?? 'Location not specified'); ?>
                                </p>

                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span><i class="fas fa-bed me-1"></i> <?php echo $property['bedrooms'] ?? 0; ?> Beds</span>
                                    <span><i class="fas fa-bath me-1"></i> <?php echo $property['bathrooms'] ?? 0; ?> Baths</span>
                                    <span><i class="fas fa-ruler-combined me-1"></i> <?php echo number_format($property['area'] ?? 0); ?> sq.ft</span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="fas fa-user me-1"></i>
                                        <?php echo htmlspecialchars(($property['first_name'] ?? '') . ' ' . ($property['last_name'] ?? '')); ?>
                                    </small>
                                    <span class="badge bg-success">Available</span>
                                </div>
                            </div>

                            <div class="card-footer bg-transparent border-0">
                                <div class="d-grid gap-2">
                                    <a href="property-details.php?id=<?php echo $property['id'] ?? 0; ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-eye me-1"></i> View Details
                                    </a>
                                    <button class="btn btn-outline-primary btn-sm" onclick="scheduleVisit(<?php echo $property['id'] ?? 0; ?>)">
                                        <i class="fas fa-calendar me-1"></i> Schedule Visit
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="row mt-5">
                <div class="col-12">
                    <nav aria-label="Property pagination">
                        <ul class="pagination justify-content-center">
                            <?php if ($current_page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $current_page - 1])); ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php
                            $start_page = max(1, $current_page - 2);
                            $end_page = min($total_pages, $current_page + 2);

                            for ($i = $start_page; $i <= $end_page; $i++):
                            ?>
                                <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($current_page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $current_page + 1])); ?>">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<!-- Property Statistics -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="display-5 fw-bold mb-3">Property Statistics</h2>
                <p class="lead text-muted">Current market insights</p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="text-center p-4 bg-white rounded shadow-sm">
                    <i class="fas fa-home fa-3x text-primary mb-3"></i>
                    <h3 class="h2 fw-bold text-primary"><?php echo number_format($total_properties); ?></h3>
                    <p class="mb-0">Total Properties</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="text-center p-4 bg-white rounded shadow-sm">
                    <i class="fas fa-search fa-3x text-success mb-3"></i>
                    <h3 class="h2 fw-bold text-success"><?php echo count($properties); ?></h3>
                    <p class="mb-0">Search Results</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="text-center p-4 bg-white rounded shadow-sm">
                    <i class="fas fa-map-marker-alt fa-3x text-info mb-3"></i>
                    <h3 class="h2 fw-bold text-info"><?php echo count(array_unique($locations)); ?></h3>
                    <p class="mb-0">Locations</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="text-center p-4 bg-white rounded shadow-sm">
                    <i class="fas fa-building fa-3x text-warning mb-3"></i>
                    <h3 class="h2 fw-bold text-warning"><?php echo count(array_unique($property_types)); ?></h3>
                    <p class="mb-0">Property Types</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-primary text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h2 class="display-5 fw-bold mb-4">Can't Find What You're Looking For?</h2>
                <p class="lead mb-4">Let our experts help you find the perfect property</p>
                <div class="d-flex gap-3 justify-content-center flex-wrap">
                    <a href="contact.php" class="btn btn-light btn-lg">
                        <i class="fas fa-phone me-2"></i>Contact Us
                    </a>
                    <a href="about.php" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-info-circle me-2"></i>Learn More
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// End output buffering and get content
$content = ob_get_clean();

// Add JavaScript
$template->addJS("
document.addEventListener('DOMContentLoaded', function() {
    // Property card hover effects
    const propertyCards = document.querySelectorAll('.card');
    propertyCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 10px 25px rgba(0,0,0,0.1)';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = '';
            this.style.boxShadow = '';
        });
    });

    // Property favorites and scheduling
    function toggleFavorite(propertyId) {
        // Add to favorites functionality
        console.log('Toggling favorite for property:', propertyId);

        // Show visual feedback
        const button = event.target.closest('button');
        const icon = button.querySelector('i');

        if (icon.classList.contains('far')) {
            icon.classList.remove('far');
            icon.classList.add('fas');
            button.classList.add('btn-danger');
            button.classList.remove('btn-light');
        } else {
            icon.classList.remove('fas');
            icon.classList.add('far');
            button.classList.add('btn-light');
            button.classList.remove('btn-danger');
        }
    }

    function scheduleVisit(propertyId) {
        // Schedule visit functionality
        console.log('Scheduling visit for property:', propertyId);
        alert('Visit scheduling feature coming soon! Contact us directly for now.');
    }

    // Search form enhancement
    const searchForm = document.querySelector('.search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            const type = document.getElementById('type').value;
            const location = document.getElementById('location').value;
            const minPrice = document.getElementById('min_price').value;
            const maxPrice = document.getElementById('max_price').value;

            // Validate price range
            if (minPrice && maxPrice && parseInt(minPrice) > parseInt(maxPrice)) {
                e.preventDefault();
                alert('Minimum price cannot be greater than maximum price.');
                return false;
            }

            // Show loading state
            const submitBtn = e.target.querySelector('button[type=\"submit\"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class=\"fas fa-spinner fa-spin me-2\"></i>Searching...';
            submitBtn.disabled = true;

            // Re-enable after 2 seconds (in case of slow response)
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 2000);
        });
    }

    // Pagination enhancement
    const paginationLinks = document.querySelectorAll('.pagination a');
    paginationLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Add loading state to pagination
            this.innerHTML = '<i class=\"fas fa-spinner fa-spin\"></i>';
            this.style.pointerEvents = 'none';
        });
    });
});
");

// Add CSS
$template->addCSS("
.card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.hover-lift {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.btn {
    border-radius: 8px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.form-select, .form-control {
    border-radius: 8px;
    border: 2px solid #e9ecef;
    padding: 12px 16px;
}

.form-select:focus, .form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.badge {
    font-size: 0.75em;
    padding: 6px 12px;
    border-radius: 20px;
}

.pagination .page-link {
    color: #667eea;
    border-color: #667eea;
}

.pagination .page-item.active .page-link {
    background-color: #667eea;
    border-color: #667eea;
}

.alert {
    border-radius: 10px;
    border: none;
}

.card-img-top {
    transition: transform 0.3s ease;
}

.card:hover .card-img-top {
    transform: scale(1.05);
}

.search-form .card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
}

@media (max-width: 768px) {
    .display-4 {
        font-size: 2rem;
    }

    .display-5 {
        font-size: 1.8rem;
    }

    .card-body {
        padding: 1rem;
    }

    .btn-lg {
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
    }
}
");

// Set page data
$page_data = [
    'title' => $page_title,
    'description' => $page_description,
    'content' => $content
];

// Render the page using universal template
$template->render($content);
?>
