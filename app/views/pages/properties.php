<?php

/**
 * Enhanced Properties Page - APS Dream Home
 * Advanced property listings with search, filtering, and enhanced user experience
 */

// Define constant to allow database connection
define('INCLUDED_FROM_MAIN', true);
// Include database connection
require_once 'includes/db_connection.php';

// Get properties from database
$properties = [];
$search_query = "";
$filters = [
    'type' => $_GET['type'] ?? '',
    'min_price' => $_GET['min_price'] ?? '',
    'max_price' => $_GET['max_price'] ?? '',
    'location' => $_GET['location'] ?? '',
    'bedrooms' => $_GET['bedrooms'] ?? ''
];

try {
    global $pdo;
    $conn = $pdo;

    // Build query based on filters
    $sql = "SELECT * FROM properties WHERE status = 'available'";
    $params = [];

    if (!empty($filters['type'])) {
        $sql .= " AND property_type = :type";
        $params[':type'] = $filters['type'];
    }
    if (!empty($filters['min_price'])) {
        $sql .= " AND price >= :min_price";
        $params[':min_price'] = $filters['min_price'];
    }
    if (!empty($filters['max_price'])) {
        $sql .= " AND price <= :max_price";
        $params[':max_price'] = $filters['max_price'];
    }
    if (!empty($filters['location'])) {
        $sql .= " AND location LIKE :location";
        $params[':location'] = '%' . $filters['location'] . '%';
    }
    if (!empty($filters['bedrooms'])) {
        $sql .= " AND bedrooms = :bedrooms";
        $params[':bedrooms'] = $filters['bedrooms'];
    }

    $sql .= " ORDER BY created_at DESC LIMIT 20";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_message = "Unable to load properties.";
}

// Sample properties if database is empty
if (empty($properties)) {
    $properties = [
        [
            'id' => 1,
            'title' => 'Luxury 3BHK Apartment',
            'type' => 'Residential',
            'location' => 'Gorakhpur City Center',
            'price' => 4500000,
            'bedrooms' => 3,
            'bathrooms' => 2,
            'area' => 1500,
            'description' => 'Spacious 3BHK apartment in prime location with modern amenities.',
            'image' => 'property1.jpg',
            'featured' => true
        ],
        [
            'id' => 2,
            'title' => 'Modern 2BHK Flat',
            'type' => 'Residential',
            'location' => 'Raghunath Nagar',
            'price' => 2800000,
            'bedrooms' => 2,
            'bathrooms' => 2,
            'area' => 1000,
            'description' => 'Well-designed 2BHK flat with excellent connectivity.',
            'image' => 'property2.jpg',
            'featured' => false
        ],
        [
            'id' => 3,
            'title' => 'Commercial Shop Space',
            'type' => 'Commercial',
            'location' => 'Gorakhpur Market',
            'price' => 12000000,
            'bedrooms' => 0,
            'bathrooms' => 1,
            'area' => 500,
            'description' => 'Prime commercial space in busy market area.',
            'image' => 'property3.jpg',
            'featured' => true
        ]
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Properties - APS Dream Home</title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="Browse premium properties by APS Dream Home. Find residential and commercial properties in Gorakhpur with advanced search and filtering options.">
    <meta name="keywords" content="properties Gorakhpur, real estate APS Dream Home, residential properties, commercial properties, property search">
    
    <!-- Modern CSS Framework -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            --warning-gradient: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
            --info-gradient: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
            --dark-gradient: linear-gradient(135deg, #343a40 0%, #495057 100%);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .hero-section {
            background: var(--primary-gradient);
            color: white;
            padding: 100px 0;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="0.1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,133.3C960,128,1056,96,1152,90.7C1248,85,1344,107,1392,117.3L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
            background-size: cover;
        }

        .hero-title {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .hero-subtitle {
            font-size: 1.3rem;
            opacity: 0.9;
        }

        .filter-section {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin: -50px auto 3rem;
            position: relative;
            z-index: 10;
        }

        .property-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
            border: none;
        }

        .property-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }

        .property-image {
            height: 250px;
            background: var(--light-gradient);
            position: relative;
            overflow: hidden;
        }

        .property-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--warning-gradient);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .property-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 0.5rem;
        }

        .property-features {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .property-feature {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #6c757d;
            font-size: 0.9rem;
        }

        .btn-gradient {
            background: var(--primary-gradient);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
            color: white;
        }

        .search-form {
            background: var(--light-gradient);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 3rem;
        }

        .page-link {
            padding: 0.5rem 1rem;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            color: #667eea;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .page-link:hover,
        .page-link.active {
            background: var(--primary-gradient);
            color: white;
            border-color: transparent;
        }

        @media (max-width: 768px) {
            .hero-section {
                padding: 60px 0;
            }
            
            .hero-title {
                font-size: 2rem;
            }
            
            .filter-section {
                margin: -30px 1rem 2rem;
                padding: 1.5rem;
            }
            
            .property-features {
                flex-direction: column;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero-section" data-aos="fade-up">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="hero-title" data-aos="fade-right">Find Your Dream Property</h1>
                    <p class="hero-subtitle" data-aos="fade-right" data-aos-delay="100">
                        Discover premium residential and commercial properties in Gorakhpur and surrounding areas
                    </p>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="stats-card">
                                <div class="stats-number">50+</div>
                                <p class="text-muted mb-0">Properties Available</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stats-card">
                                <div class="stats-number">15+</div>
                                <p class="text-muted mb-0">Years Experience</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stats-card">
                                <div class="stats-number">5000+</div>
                                <p class="text-muted mb-0">Happy Customers</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stats-card">
                                <div class="stats-number">100%</div>
                                <p class="text-muted mb-0">Satisfaction</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Search and Filter Section -->
    <section class="container">
        <div class="filter-section" data-aos="fade-up">
            <h3 class="mb-4">Search Properties</h3>
            <form method="GET" class="search-form">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Property Type</label>
                        <select class="form-select" name="type">
                            <option value="">All Types</option>
                            <option value="Residential" <?= $filters['type'] == 'Residential' ? 'selected' : '' ?>>Residential</option>
                            <option value="Commercial" <?= $filters['type'] == 'Commercial' ? 'selected' : '' ?>>Commercial</option>
                            <option value="Plot" <?= $filters['type'] == 'Plot' ? 'selected' : '' ?>>Plot</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Location</label>
                        <input type="text" class="form-control" name="location" placeholder="Enter location" value="<?= htmlspecialchars($filters['location']) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Min Price</label>
                        <input type="number" class="form-control" name="min_price" placeholder="Min" value="<?= htmlspecialchars($filters['min_price']) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Max Price</label>
                        <input type="number" class="form-control" name="max_price" placeholder="Max" value="<?= htmlspecialchars($filters['max_price']) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Bedrooms</label>
                        <select class="form-select" name="bedrooms">
                            <option value="">Any</option>
                            <option value="1" <?= $filters['bedrooms'] == '1' ? 'selected' : '' ?>>1</option>
                            <option value="2" <?= $filters['bedrooms'] == '2' ? 'selected' : '' ?>>2</option>
                            <option value="3" <?= $filters['bedrooms'] == '3' ? 'selected' : '' ?>>3</option>
                            <option value="4" <?= $filters['bedrooms'] == '4' ? 'selected' : '' ?>>4+</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-gradient">
                            <i class="fas fa-search me-2"></i>Search Properties
                        </button>
                        <a href="?reset=1" class="btn btn-outline-secondary ms-2">
                            <i class="fas fa-redo me-2"></i>Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <!-- Properties Grid -->
    <section class="container mb-5">
        <div class="row g-4">
            <?php foreach ($properties as $property): ?>
                <div class="col-lg-4 col-md-6" data-aos="fade-up">
                    <div class="property-card">
                        <div class="property-image">
                            <img src="assets/images/properties/<?= $property['image'] ?? 'default-property.jpg' ?>" 
                                 alt="<?= htmlspecialchars($property['title']) ?>" 
                                 class="w-100 h-100 object-fit-cover"
                                 onerror="this.src='https://via.placeholder.com/400x250/667eea/ffffff?text=Property'">
                            <?php if ($property['featured'] ?? false): ?>
                                <span class="property-badge">Featured</span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($property['title']) ?></h5>
                            <p class="text-muted mb-2">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                <?= htmlspecialchars($property['location']) ?>
                            </p>
                            <div class="property-price">
                                ₹<?= number_format($property['price']) ?>
                            </div>
                            <div class="property-features">
                                <div class="property-feature">
                                    <i class="fas fa-bed"></i>
                                    <?= $property['bedrooms'] ?> Beds
                                </div>
                                <div class="property-feature">
                                    <i class="fas fa-bath"></i>
                                    <?= $property['bathrooms'] ?> Baths
                                </div>
                                <div class="property-feature">
                                    <i class="fas fa-ruler-combined"></i>
                                    <?= $property['area'] ?> sqft
                                </div>
                            </div>
                            <p class="text-muted small mb-3">
                                <?= htmlspecialchars(substr($property['description'], 0, 100)) ?>...
                            </p>
                            <div class="d-grid gap-2">
                                <a href="property-detail?id=<?= $property['id'] ?>" class="btn btn-gradient">
                                    <i class="fas fa-eye me-2"></i>View Details
                                </a>
                                <button class="btn btn-outline-primary" onclick="addToFavorites(<?= $property['id'] ?>)">
                                    <i class="fas fa-heart me-2"></i>Add to Favorites
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (empty($properties)): ?>
            <div class="text-center py-5" data-aos="fade-up">
                <i class="fas fa-home fa-4x text-muted mb-3"></i>
                <h4>No Properties Found</h4>
                <p class="text-muted">Try adjusting your search criteria or browse all properties.</p>
                <a href="?" class="btn btn-gradient">
                    <i class="fas fa-search me-2"></i>View All Properties
                </a>
            </div>
        <?php endif; ?>

        <!-- Pagination -->
        <div class="pagination">
            <a href="#" class="page-link active">1</a>
            <a href="#" class="page-link">2</a>
            <a href="#" class="page-link">3</a>
            <a href="#" class="page-link">Next</a>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5 bg-primary text-white">
        <div class="container text-center">
            <h2 class="mb-4" data-aos="fade-up">Can't Find What You're Looking For?</h2>
            <p class="lead mb-4" data-aos="fade-up" data-aos-delay="100">
                Let our experts help you find the perfect property
            </p>
            <div data-aos="fade-up" data-aos-delay="200">
                <a href="/contact" class="btn btn-warning btn-lg me-3">
                    <i class="fas fa-phone me-2"></i>Contact Our Team
                </a>
                <a href="/property-enquiry" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-edit me-2"></i>Submit Requirements
                </a>
            </div>
        </div>
    </section>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });

        // Add to favorites function
        function addToFavorites(propertyId) {
            // Get existing favorites from localStorage
            let favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
            
            // Check if property is already in favorites
            if (favorites.includes(propertyId)) {
                alert('This property is already in your favorites!');
                return;
            }
            
            // Add to favorites
            favorites.push(propertyId);
            localStorage.setItem('favorites', JSON.stringify(favorites));
            
            // Show success message
            alert('Property added to favorites!');
            
            // Update button text
            event.target.innerHTML = '<i class="fas fa-heart me-2"></i>Added to Favorites';
            event.target.disabled = true;
        }

        // Price formatting
        function formatPrice(price) {
            return new Intl.NumberFormat('en-IN', {
                style: 'currency',
                currency: 'INR',
                maximumFractionDigits: 0
            }).format(price);
        }

        // Search form enhancement
        document.querySelector('.search-form').addEventListener('submit', function(e) {
            const minPrice = document.querySelector('input[name="min_price"]').value;
            const maxPrice = document.querySelector('input[name="max_price"]').value;
            
            if (minPrice && maxPrice && parseInt(minPrice) > parseInt(maxPrice)) {
                e.preventDefault();
                alert('Minimum price cannot be greater than maximum price!');
            }
        });
    </script>
</body>
</html>
