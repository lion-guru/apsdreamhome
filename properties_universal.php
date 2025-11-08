<?php
// Enhanced Properties.php using Universal Template System
// All functionality from old properties.php preserved and enhanced

require_once __DIR__ . '/includes/enhanced_universal_template.php';

// Get database connection and data (preserved from old properties.php)
require_once 'includes/config.php';
require_once 'includes/db_connection.php';
$conn = getDbConnection();

// Get search parameters (identical to old properties.php)
$search_type = trim($_GET['type'] ?? '');
$search_location = trim($_GET['location'] ?? '');
$search_min_price = trim($_GET['min_price'] ?? '');
$search_max_price = trim($_GET['max_price'] ?? '');
$search_bedrooms = trim($_GET['bedrooms'] ?? '');

// Build query (same logic as old properties.php)
$query = "
    SELECT p.id, p.title, p.address, p.price, p.bedrooms, p.bathrooms, p.area, p.status, p.description,
           u.first_name, u.last_name, u.phone as agent_phone, u.email as agent_email,
           pt.name as property_type,
           (SELECT pi.image_url FROM property_images pi WHERE pi.property_id = p.id ORDER BY pi.is_primary DESC, pi.id ASC LIMIT 1) as main_image
    FROM properties p
    LEFT JOIN users u ON p.agent_id = u.id
    LEFT JOIN property_types pt ON p.property_type_id = pt.id
    WHERE p.status = 'available'";

$params = [];
$conditions = [];

// Add search conditions (identical to old properties.php)
if (!empty($search_type)) {
    $conditions[] = "pt.name = ?";
    $params[] = $search_type;
}

if (!empty($search_location)) {
    $conditions[] = "(p.address LIKE ? OR p.city LIKE ?)";
    $params[] = "%$search_location%";
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

// Add conditions to query (same as old properties.php)
if (!empty($conditions)) {
    $query .= " AND " . implode(" AND ", $conditions);
}

$query .= " ORDER BY p.created_at DESC";

// Execute query (identical to old properties.php)
$properties = [];
try {
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
} catch (Exception $e) {
    error_log('Error fetching properties: ' . $e->getMessage());
}

// Get property types for filter (same as old properties.php)
$property_types = [];
try {
    $query = "SELECT DISTINCT name FROM property_types ORDER BY name";
    $result = $conn->query($query);
    if ($result) {
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $property_types[] = $row['name'];
        }
    }
} catch (Exception $e) {
    error_log('Error fetching property types: ' . $e->getMessage());
}

// Get locations for filter (same as old properties.php)
$locations = [];
try {
    $query = "SELECT DISTINCT city FROM properties WHERE status = 'available' AND city IS NOT NULL ORDER BY city";
    $result = $conn->query($query);
    if ($result) {
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            if (!empty($row['city'])) {
                $locations[] = $row['city'];
            }
        }
    }
} catch (Exception $e) {
    error_log('Error fetching locations: ' . $e->getMessage());
}

// Build the complete content with all old properties.php features
$content = "
<!-- Properties Hero Section (Identical to old properties.php) -->
<section class='py-5 bg-light'>
    <div class='container'>
        <div class='row'>
            <div class='col-12 text-center mb-5'>
                <h1 class='display-4 fw-bold mb-3'>Properties</h1>
                <p class='lead text-muted'>Find your perfect property from our extensive collection</p>
                <p class='text-muted'>
                    " . count($properties) . " properties found
                    " . (!empty($search_type) || !empty($search_location) ? "matching your search criteria" : "") . "
                </p>
            </div>
        </div>

        <!-- Advanced Search Form (identical to old properties.php) -->
        <div class='row justify-content-center'>
            <div class='col-lg-10'>
                <div class='card shadow-sm'>
                    <div class='card-body p-4'>
                        <form method='GET' action='properties.php' class='row g-3'>
                            <div class='col-md-3'>
                                <label class='form-label'>Property Type</label>
                                <select class='form-select' name='type'>
                                    <option value=''>All Types</option>";
                                    foreach ($property_types as $type) {
                                        $content .= "
                                    <option value='" . htmlspecialchars($type) . "'
                                            " . ($search_type === $type ? 'selected' : '') . ">
                                        " . htmlspecialchars($type) . "
                                    </option>";
                                    }
                                    $content .= "
                                </select>
                            </div>

                            <div class='col-md-3'>
                                <label class='form-label'>Location</label>
                                <select class='form-select' name='location'>
                                    <option value=''>All Locations</option>";
                                    foreach ($locations as $location) {
                                        $content .= "
                                    <option value='" . htmlspecialchars($location) . "'
                                            " . ($search_location === $location ? 'selected' : '') . ">
                                        " . htmlspecialchars($location) . "
                                    </option>";
                                    }
                                    $content .= "
                                </select>
                            </div>

                            <div class='col-md-2'>
                                <label class='form-label'>Min Price</label>
                                <input type='number' class='form-control' name='min_price'
                                       value='" . htmlspecialchars($search_min_price) . "'
                                       placeholder='₹0'>
                            </div>

                            <div class='col-md-2'>
                                <label class='form-label'>Max Price</label>
                                <input type='number' class='form-control' name='max_price'
                                       value='" . htmlspecialchars($search_max_price) . "'
                                       placeholder='No limit'>
                            </div>

                            <div class='col-md-2'>
                                <label class='form-label'>Bedrooms</label>
                                <select class='form-select' name='bedrooms'>
                                    <option value=''>Any</option>
                                    <option value='1' " . ($search_bedrooms === '1' ? 'selected' : '') . ">1+ BHK</option>
                                    <option value='2' " . ($search_bedrooms === '2' ? 'selected' : '') . ">2+ BHK</option>
                                    <option value='3' " . ($search_bedrooms === '3' ? 'selected' : '') . ">3+ BHK</option>
                                    <option value='4' " . ($search_bedrooms === '4' ? 'selected' : '') . ">4+ BHK</option>
                                </select>
                            </div>

                            <div class='col-12'>
                                <div class='d-flex gap-2'>
                                    <button type='submit' class='btn btn-primary flex-fill'>
                                        <i class='fas fa-search me-2'></i>Search Properties
                                    </button>
                                    <a href='properties.php' class='btn btn-outline-secondary'>
                                        <i class='fas fa-times me-2'></i>Clear Filters
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Properties Grid (Identical to old properties.php) -->
<section class='py-5'>
    <div class='container'>";
        if (empty($properties)) {
            $content .= "
        <div class='row'>
            <div class='col-12'>
                <div class='text-center py-5'>
                    <i class='fas fa-search fa-4x text-muted mb-4'></i>
                    <h3 class='text-muted mb-3'>No Properties Found</h3>
                    <p class='text-muted mb-4'>";
                    if (!empty($search_type) || !empty($search_location) || !empty($search_min_price) || !empty($search_max_price) || !empty($search_bedrooms)) {
                        $content .= "No properties match your current search criteria. Try adjusting your filters.";
                    } else {
                        $content .= "No properties are available at the moment. Please check back later.";
                    }
                    $content .= "
                    </p>
                    <a href='properties.php' class='btn btn-primary'>
                        <i class='fas fa-arrow-left me-2'></i>View All Properties
                    </a>
                </div>
            </div>
        </div>";
        } else {
            $content .= "
        <div class='row g-4'>";
                foreach ($properties as $property) {
                    $content .= "
            <div class='col-lg-4 col-md-6' data-aos='fade-up'>
                <div class='card h-100 shadow-sm hover-lift'>
                    <div class='position-relative'>
                        <img src='" . htmlspecialchars($property['main_image'] ?? 'assets/images/property-placeholder.jpg') . "'
                             alt='" . htmlspecialchars($property['title'] ?? 'Property') . "'
                             class='card-img-top' style='height: 250px; object-fit: cover;'>

                        <div class='position-absolute top-0 end-0 m-2'>
                            <span class='badge bg-primary'>" . htmlspecialchars($property['property_type'] ?? 'Property') . "</span>
                        </div>

                        <div class='position-absolute top-0 start-0 m-2'>
                            <span class='badge bg-success'>₹" . number_format($property['price'] ?? 0) . "</span>
                        </div>

                        <button class='btn btn-light btn-sm position-absolute bottom-0 end-0 m-2' onclick='toggleFavorite(" . ($property['id'] ?? 0) . ")'>
                            <i class='far fa-heart'></i>
                        </button>
                    </div>

                    <div class='card-body'>
                        <h5 class='card-title'>
                            <a href='property-details.php?id=" . ($property['id'] ?? 0) . "' class='text-decoration-none'>
                                " . htmlspecialchars($property['title'] ?? 'Untitled Property') . "
                            </a>
                        </h5>

                        <p class='card-text text-muted'>
                            <i class='fas fa-map-marker-alt me-1'></i>
                            " . htmlspecialchars($property['address'] ?? 'Location not specified') . "
                        </p>

                        <div class='d-flex justify-content-between align-items-center mb-3'>
                            <span><i class='fas fa-bed me-1'></i> " . ($property['bedrooms'] ?? 0) . " Beds</span>
                            <span><i class='fas fa-bath me-1'></i> " . ($property['bathrooms'] ?? 0) . " Baths</span>
                            <span><i class='fas fa-ruler-combined me-1'></i> " . number_format($property['area'] ?? 0) . " sq.ft</span>
                        </div>

                        <div class='d-flex justify-content-between align-items-center'>
                            <small class='text-muted'>
                                <i class='fas fa-user me-1'></i>
                                " . htmlspecialchars(($property['first_name'] ?? '') . ' ' . ($property['last_name'] ?? '')) . "
                            </small>
                            <span class='badge bg-" . (($property['status'] ?? '') === 'available' ? 'success' : 'secondary') . "'>
                                " . ucfirst($property['status'] ?? 'Unknown') . "
                            </span>
                        </div>
                    </div>

                    <div class='card-footer bg-transparent border-0'>
                        <div class='d-grid gap-2'>
                            <a href='property-details.php?id=" . ($property['id'] ?? 0) . "' class='btn btn-primary btn-sm'>
                                <i class='fas fa-eye me-1'></i> View Details
                            </a>
                            <button class='btn btn-outline-primary btn-sm' onclick='scheduleVisit(" . ($property['id'] ?? 0) . ")'>
                                <i class='fas fa-calendar me-1'></i> Schedule Visit
                            </button>
                        </div>
                    </div>
                </div>
            </div>";
                }
                $content .= "
        </div>

        <!-- Pagination (identical to old properties.php) -->
        <div class='row mt-5'>
            <div class='col-12'>
                <nav aria-label='Property pagination'>
                    <ul class='pagination justify-content-center'>
                        <li class='page-item disabled'>
                            <span class='page-link'>Previous</span>
                        </li>
                        <li class='page-item active'>
                            <span class='page-link'>1</span>
                        </li>
                        <li class='page-item'>
                            <a class='page-link' href='#'>2</a>
                        </li>
                        <li class='page-item'>
                            <a class='page-link' href='#'>3</a>
                        </li>
                        <li class='page-item'>
                            <a class='page-link' href='#'>Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>";
        }
        $content .= "
    </div>
</section>

<!-- Property Statistics (Identical to old properties.php) -->
<section class='py-5 bg-light'>
    <div class='container'>
        <div class='row'>
            <div class='col-12 text-center mb-5'>
                <h2 class='display-5 fw-bold mb-3'>Property Statistics</h2>
                <p class='lead text-muted'>Comprehensive market insights and trends</p>
            </div>
        </div>

        <div class='row g-4'>
            <div class='col-lg-3 col-md-6'>
                <div class='text-center p-4 bg-white rounded shadow-sm'>
                    <i class='fas fa-chart-line fa-3x text-primary mb-3'></i>
                    <h3 class='h2 fw-bold text-primary'>" . count($properties) . "</h3>
                    <p class='mb-0'>Available Properties</p>
                </div>
            </div>
            <div class='col-lg-3 col-md-6'>
                <div class='text-center p-4 bg-white rounded shadow-sm'>
                    <i class='fas fa-map-marked-alt fa-3x text-success mb-3'></i>
                    <h3 class='h2 fw-bold text-success'>" . count(array_unique(array_column($properties, 'address'))) . "</h3>
                    <p class='mb-0'>Unique Locations</p>
                </div>
            </div>
            <div class='col-lg-3 col-md-6'>
                <div class='text-center p-4 bg-white rounded shadow-sm'>
                    <i class='fas fa-building fa-3x text-info mb-3'></i>
                    <h3 class='h2 fw-bold text-info'>" . count($property_types) . "</h3>
                    <p class='mb-0'>Property Types</p>
                </div>
            </div>
            <div class='col-lg-3 col-md-6'>
                <div class='text-center p-4 bg-white rounded shadow-sm'>
                    <i class='fas fa-star fa-3x text-warning mb-3'></i>
                    <h3 class='h2 fw-bold text-warning'>4.8</h3>
                    <p class='mb-0'>Average Rating</p>
                </div>
            </div>
        </div>
    </div>
</section>";

// Add all JavaScript functionality from old properties.php (preserved)
$template->addJS("
document.addEventListener('DOMContentLoaded', function() {
    // Form validation (from old properties.php)
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const message = document.getElementById('message').value.trim();

            if (!name || !email || !message) {
                e.preventDefault();
                showToast('Please fill in all required fields.', 'danger');
                return;
            }

            if (!isValidEmail(email)) {
                e.preventDefault();
                showToast('Please enter a valid email address.', 'danger');
                return;
            }

            // Show loading state (from old properties.php)
            const submitBtn = contactForm.querySelector('button[type=\"submit\"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class=\"fas fa-spinner fa-spin me-2\"></i>Sending...';
            submitBtn.disabled = true;
        });
    }

    // Email validation (from old properties.php)
    function isValidEmail(email) {
        const emailRegex = /^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$/;
        return emailRegex.test(email);
    }

    // Toast notification function (from old properties.php)
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        toast.innerHTML = `
            ` + message + `
            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
        `;

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 3000);
    }
});
");
