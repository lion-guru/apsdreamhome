<?php
/**
 * Properties Page - APS Dream Homes
 * Display property listings with advanced filtering
 */

require_once 'core/functions.php';
require_once 'includes/db_connection.php';

try {
    $pdo = getMysqliConnection();

    // Get properties with basic info for initial load
    $propertiesQuery = "SELECT * FROM properties WHERE status = 'available' ORDER BY featured DESC, created_at DESC LIMIT 20";
    $propertiesStmt = $pdo->query($propertiesQuery);
    $properties = $propertiesStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get property types for filter
    $typesQuery = "SELECT DISTINCT property_type FROM properties WHERE status = 'available'";
    $typesStmt = $pdo->query($typesQuery);
    $propertyTypes = $typesStmt->fetchAll(PDO::FETCH_COLUMN);

    // Get locations for filter
    $locationsQuery = "SELECT DISTINCT location FROM properties WHERE status = 'available'";
    $locationsStmt = $pdo->query($locationsQuery);
    $locations = $locationsStmt->fetchAll(PDO::FETCH_COLUMN);

} catch (Exception $e) {
    error_log('Properties page database error: ' . $e->getMessage());
    $properties = [];
    $propertyTypes = [];
    $locations = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    // Include site settings
    require_once 'includes/site_settings.php';
    ?>
    <title><?php echo getSiteSetting('site_title', 'APS Dream Homes'); ?> - Properties</title>
    <meta name="description" content="Browse our premium property listings including apartments, villas, plots, and commercial spaces in prime locations.">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        .properties-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0 60px;
            text-align: center;
        }

        .property-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }

        .property-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }

        .property-image {
            height: 250px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .property-card:hover .property-image {
            transform: scale(1.05);
        }

        .price-tag {
            position: absolute;
            top: 15px;
            right: 15px;
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: bold;
            font-size: 1.2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .property-type-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: rgba(255,255,255,0.95);
            color: #333;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            backdrop-filter: blur(10px);
        }

        .stats-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 60px 0;
        }

        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            transition: transform 0.3s ease;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }

        .search-section {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            margin: 30px 0;
        }

        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
            margin-bottom: 30px;
        }

        .feature-card:hover {
            transform: translateY(-5px);
        }

        .feature-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin: 0 auto 20px;
            font-size: 1.8rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .cta-section {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 60px 0;
            text-align: center;
        }

        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            padding: 15px 35px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(45deg, #764ba2, #667eea);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }

        .location-badge {
            background: linear-gradient(45deg, #ffc107, #fd7e14);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 15px;
        }

        .property-status {
            position: absolute;
            top: 15px;
            right: 15px;
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/templates/header.php'; ?>

    <!-- Hero Section -->
    <section class="properties-hero">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <h1 class="display-4 fw-bold mb-4" data-aos="fade-up">Premium Properties</h1>
                    <p class="lead mb-4" data-aos="fade-up" data-aos-delay="100">
                        Discover exceptional real estate opportunities with APS Dream Homes.<br>
                        Your trusted partner for residential and commercial properties in Eastern UP.
                    </p>

                    <!-- Search and Filter Section -->
                    <div class="search-section">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <select class="form-select" id="propertyTypeFilter">
                                    <option value="">All Types</option>
                                    <option value="apartment">Apartments</option>
                                    <option value="villa">Villas</option>
                                    <option value="plot">Plots</option>
                                    <option value="commercial">Commercial</option>
                                    <option value="house">Houses</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" id="locationFilter">
                                    <option value="">All Locations</option>
                                    <option value="Gorakhpur">Gorakhpur</option>
                                    <option value="Lucknow">Lucknow</option>
                                    <option value="Varanasi">Varanasi</option>
                                    <option value="Allahabad">Allahabad</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" id="priceFilter">
                                    <option value="">All Prices</option>
                                    <option value="0-3000000">Under ₹30L</option>
                                    <option value="3000000-5000000">₹30L - ₹50L</option>
                                    <option value="5000000-10000000">₹50L - ₹1Cr</option>
                                    <option value="10000000-20000000">₹1Cr - ₹2Cr</option>
                                    <option value="20000000">Above ₹2Cr</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-light w-100" id="searchBtn">
                                    <i class="fas fa-search me-2"></i>Search Properties
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="stats-card">
                        <i class="fas fa-home fa-3x mb-3"></i>
                        <h2 class="mb-2">500+</h2>
                        <p class="mb-0">Properties Delivered</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <i class="fas fa-users fa-3x mb-3"></i>
                        <h2 class="mb-2">1000+</h2>
                        <p class="mb-0">Happy Families</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <i class="fas fa-map-marker-alt fa-3x mb-3"></i>
                        <h2 class="mb-2">15+</h2>
                        <p class="mb-0">Prime Locations</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <i class="fas fa-award fa-3x mb-3"></i>
                        <h2 class="mb-2">8+</h2>
                        <p class="mb-0">Years Experience</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Advanced Search and Filter Section -->
    <section class="py-4 bg-light">
        <div class="container">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form id="propertySearchForm" class="row g-3">
                        <div class="col-md-3">
                            <label for="searchQuery" class="form-label">Search</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="searchQuery" placeholder="Search by name, location, or features...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label for="propertyTypeFilter" class="form-label">Property Type</label>
                            <select class="form-select" id="propertyTypeFilter">
                                <option value="">All Types</option>
                                <option value="plot">Plots</option>
                                <option value="apartment">Apartments</option>
                                <option value="villa">Villas</option>
                                <option value="commercial">Commercial</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="locationFilter" class="form-label">Location</label>
                            <select class="form-select" id="locationFilter">
                                <option value="">All Locations</option>
                                <option value="gorakhpur">Gorakhpur</option>
                                <option value="lucknow">Lucknow</option>
                                <option value="varanasi">Varanasi</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="priceRangeFilter" class="form-label">Price Range</label>
                            <select class="form-select" id="priceRangeFilter">
                                <option value="">Any Price</option>
                                <option value="0-1000000">Under ₹10 Lakhs</option>
                                <option value="1000000-3000000">₹10L - ₹30L</option>
                                <option value="3000000-5000000">₹30L - ₹50L</option>
                                <option value="5000000-10000000">₹50L - ₹1 Cr</option>
                                <option value="10000000">Above ₹1 Cr</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="sortBy" class="form-label">Sort By</label>
                            <select class="form-select" id="sortBy">
                                <option value="featured">Featured</option>
                                <option value="price_asc">Price: Low to High</option>
                                <option value="price_desc">Price: High to Low</option>
                                <option value="newest">Newest First</option>
                                <option value="area_asc">Area: Small to Large</option>
                                <option value="area_desc">Area: Large to Small</option>
                            </select>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="button" id="advancedFiltersBtn" class="btn btn-outline-secondary w-100" data-bs-toggle="collapse" data-bs-target="#advancedFilters">
                                <i class="fas fa-sliders-h"></i>
                            </button>
                        </div>
                    </form>

                    <!-- Advanced Filters -->
                    <div class="collapse mt-3" id="advancedFilters">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Bedrooms</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="bedrooms" id="bedroomAny" value="" checked>
                                    <label class="btn btn-outline-primary" for="bedroomAny">Any</label>
                                    <input type="radio" class="btn-check" name="bedrooms" id="bedroom1" value="1">
                                    <label class="btn btn-outline-primary" for="bedroom1">1+</label>
                                    <input type="radio" class="btn-check" name="bedrooms" id="bedroom2" value="2">
                                    <label class="btn btn-outline-primary" for="bedroom2">2+</label>
                                    <input type="radio" class="btn-check" name="bedrooms" id="bedroom3" value="3">
                                    <label class="btn btn-outline-primary" for="bedroom3">3+</label>
                                    <input type="radio" class="btn-check" name="bedrooms" id="bedroom4" value="4">
                                    <label class="btn btn-outline-primary" for="bedroom4">4+</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Area (sq ft)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="minArea" placeholder="Min">
                                    <span class="input-group-text">to</span>
                                    <input type="number" class="form-control" id="maxArea" placeholder="Max">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Amenities</label>
                                <select class="form-select" id="amenitiesFilter" multiple>
                                    <option value="parking">Parking</option>
                                    <option value="garden">Garden</option>
                                    <option value="security">24/7 Security</option>
                                    <option value="gym">Gym</option>
                                    <option value="pool">Swimming Pool</option>
                                    <option value="lift">Lift</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="button" id="resetFilters" class="btn btn-outline-danger w-100">
                                    <i class="fas fa-undo me-1"></i> Reset Filters
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Properties Grid -->
    <section class="py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0">🏗️ Our Premium Projects</h2>
                    <p class="text-muted mb-0">Handpicked properties that match your criteria</p>
                </div>
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span id="propertyCount" class="badge bg-primary rounded-pill"><?php echo count($properties); ?></span>
                        <span class="ms-1">Properties Found</span>
                    </div>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-secondary active view-toggle" data-view="grid">
                            <i class="fas fa-th-large"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary view-toggle" data-view="list">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="row g-4" id="propertiesGrid">
                <?php if (!empty($properties)): ?>
                <?php foreach ($properties as $property): ?>
                <div class="col-lg-4 col-md-6" data-aos="fade-up">
                    <div class="property-card">
                        <div class="position-relative">
                            <img src="<?php echo htmlspecialchars($property['image_url'] ?? 'assets/images/property-placeholder.jpg'); ?>"
                                 alt="<?php echo htmlspecialchars($property['title']); ?>" class="card-img-top property-image">
                            <?php if ($property['featured'] == 1): ?>
                            <div class="badge bg-warning position-absolute top-0 start-0 m-2">
                                <i class="fas fa-star me-1"></i>Featured
                            </div>
                            <?php endif; ?>
                            <div class="price-tag">
                                ₹<?php echo htmlspecialchars(number_format($property['price'])); ?>
                            </div>
                            <div class="property-type-badge">
                                <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $property['property_type']))); ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title mb-2"><?php echo htmlspecialchars($property['title']); ?></h5>
                            <p class="card-text text-muted mb-3">
                                <i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($property['location']); ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <?php if (!empty($property['bedrooms'])): ?>
                                <span class="badge bg-light text-dark"><i class="fas fa-bed me-1"></i><?php echo $property['bedrooms']; ?> Bed</span>
                                <?php endif; ?>
                                <?php if (!empty($property['bathrooms'])): ?>
                                <span class="badge bg-light text-dark"><i class="fas fa-bath me-1"></i><?php echo $property['bathrooms']; ?> Bath</span>
                                <?php endif; ?>
                                <?php if (!empty($property['area'])): ?>
                                <span class="badge bg-light text-dark"><i class="fas fa-ruler-combined me-1"></i><?php echo $property['area']; ?> sq.ft</span>
                                <?php endif; ?>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="property-details.php?id=<?php echo $property['id']; ?>"
                                   class="btn btn-primary btn-sm flex-fill">
                                    <i class="fas fa-eye me-1"></i>View Details
                                </a>
                                <a href="contact.php?property=<?php echo $property['id']; ?>"
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-phone"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="col-12 text-center py-5">
                    <i class="fas fa-search fa-4x text-muted mb-4"></i>
                    <h4 class="text-muted">No properties found</h4>
                    <p class="text-muted">Try adjusting your search criteria</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2>Why Choose APS Dream Homes?</h2>
                <p class="lead text-muted">Experience the difference with our professional approach</p>
            </div>

            <div class="row g-4">
                <div class="col-lg-4" data-aos="fade-up">
                    <div class="feature-card h-100">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h5>Registered Company</h5>
                        <p class="text-muted">Licensed under Companies Act 2013 with proper legal compliance and transparent operations</p>
                    </div>
                </div>

                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-card h-100">
                        <div class="feature-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h5>Prime Locations</h5>
                        <p class="text-muted">Strategically located projects in high-growth areas with excellent connectivity and infrastructure</p>
                    </div>
                </div>

                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-card h-100">
                        <div class="feature-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h5>Customer First</h5>
                        <p class="text-muted">Every decision we make prioritizes customer satisfaction and long-term relationships</p>
                    </div>
                </div>

                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-card h-100">
                        <div class="feature-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h5>Timely Delivery</h5>
                        <p class="text-muted">Proven track record of delivering projects on time without compromising quality standards</p>
                    </div>
                </div>

                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="feature-card h-100">
                        <div class="feature-icon">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <h5>Sustainable Development</h5>
                        <p class="text-muted">Eco-friendly construction practices with green spaces and energy-efficient designs</p>
                    </div>
                </div>

                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="500">
                    <div class="feature-card h-100">
                        <div class="feature-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h5>24/7 Support</h5>
                        <p class="text-muted">Round-the-clock customer support and dedicated relationship managers for all clients</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h2 class="mb-4">Ready to Find Your Dream Property?</h2>
                    <p class="lead mb-4">
                        Join thousands of satisfied customers who have found their perfect home with APS Dream Homes
                    </p>
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <a href="contact.php" class="btn btn-light btn-lg">
                            <i class="fas fa-phone me-2"></i>Contact Us Today
                        </a>
                        <a href="about.php" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-info-circle me-2"></i>Learn More About Us
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/templates/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });

        // Property filtering functionality (simplified for demo)
        document.getElementById('searchBtn').addEventListener('click', function() {
            // In a real application, this would filter properties via AJAX
            alert('Search functionality would be implemented here');
        });

        // View toggle functionality
        document.querySelectorAll('.view-toggle').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.view-toggle').forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                const view = this.getAttribute('data-view');
                const grid = document.getElementById('propertiesGrid');

                if (view === 'list') {
                    grid.classList.add('row-cols-1');
                    grid.classList.remove('row-cols-md-2', 'row-cols-lg-3');
                } else {
                    grid.classList.remove('row-cols-1');
                    grid.classList.add('row-cols-md-2', 'row-cols-lg-3');
                }
            });
        });
    </script>
</body>
</html>

    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 0;
            text-align: center;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .property-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 30px;
        }

        .property-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }

        .property-image {
            height: 250px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .property-card:hover .property-image {
            transform: scale(1.05);
        }

        .price-tag {
            position: absolute;
            top: 15px;
            right: 15px;
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: bold;
            font-size: 1.2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .property-type-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: rgba(255,255,255,0.95);
            color: #333;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            backdrop-filter: blur(10px);
        }

        .stats-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 60px 0;
        }

        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            transition: transform 0.3s ease;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }

        .search-section {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            margin: 30px 0;
        }

        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
            margin-bottom: 30px;
        }

        .feature-card:hover {
            transform: translateY(-5px);
        }

        .feature-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin: 0 auto 20px;
            font-size: 1.8rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .cta-section {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 60px 0;
            text-align: center;
        }

        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            padding: 15px 35px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(45deg, #764ba2, #667eea);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }

        .location-badge {
            background: linear-gradient(45deg, #ffc107, #fd7e14);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 15px;
        }

        .property-status {
            position: absolute;
            top: 15px;
            right: 15px;
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-home me-2"></i>APS Dream Homes Pvt Ltd
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="properties.php">Properties</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                </ul>
                <div class="d-flex">
                    <a href="tel:+919554000001" class="btn btn-outline-success me-2">
                        <i class="fas fa-phone me-1"></i>+91-9554000001
                    </a>
                    <a href="customer_login.php" class="btn btn-outline-light me-2">Login</a>
                    <a href="customer_registration.php" class="btn btn-success">Register</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <h1 class="section-title">🏠 Premium Properties in Gorakhpur</h1>
                    <p class="lead mb-4">
                        Discover exceptional real estate opportunities with APS Dream Homes Pvt Ltd.<br>
                        Your trusted partner for residential and commercial properties in Eastern UP.
                    </p>

                    <!-- Search and Filter Section -->
                    <div class="search-section">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <select class="form-select" id="propertyTypeFilter">
                                    <option value="">All Types</option>
                                    <option value="apartment">Apartments</option>
                                    <option value="villa">Villas</option>
                                    <option value="plot">Plots</option>
                                    <option value="commercial">Commercial</option>
                                    <option value="house">Houses</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" id="locationFilter">
                                    <option value="">All Locations</option>
                                    <option value="Gorakhpur">Gorakhpur</option>
                                    <option value="Lucknow">Lucknow</option>
                                    <option value="Varanasi">Varanasi</option>
                                    <option value="Allahabad">Allahabad</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" id="priceFilter">
                                    <option value="">All Prices</option>
                                    <option value="0-3000000">Under ₹30L</option>
                                    <option value="3000000-5000000">₹30L - ₹50L</option>
                                    <option value="5000000-10000000">₹50L - ₹1Cr</option>
                                    <option value="10000000-20000000">₹1Cr - ₹2Cr</option>
                                    <option value="20000000">Above ₹2Cr</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-light w-100" id="searchBtn">
                                    <i class="fas fa-search me-2"></i>Search Properties
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="stats-card">
                        <i class="fas fa-home fa-3x mb-3"></i>
                        <h2 class="mb-2">500+</h2>
                        <p class="mb-0">Properties Delivered</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <i class="fas fa-users fa-3x mb-3"></i>
                        <h2 class="mb-2">1000+</h2>
                        <p class="mb-0">Happy Families</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <i class="fas fa-map-marker-alt fa-3x mb-3"></i>
                        <h2 class="mb-2">15+</h2>
                        <p class="mb-0">Prime Locations</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <i class="fas fa-award fa-3x mb-3"></i>
                        <h2 class="mb-2">8+</h2>
                        <p class="mb-0">Years Experience</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Search and Filter Section -->
    <section class="py-4 bg-light">
        <div class="container">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form id="propertySearchForm" class="row g-3">
                        <div class="col-md-3">
                            <label for="searchQuery" class="form-label">Search</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="searchQuery" placeholder="Search by name, location, or features...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label for="propertyTypeFilter" class="form-label">Property Type</label>
                            <select class="form-select" id="propertyTypeFilter">
                                <option value="">All Types</option>
                                <option value="plot">Plots</option>
                                <option value="apartment">Apartments</option>
                                <option value="villa">Villas</option>
                                <option value="commercial">Commercial</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="locationFilter" class="form-label">Location</label>
                            <select class="form-select" id="locationFilter">
                                <option value="">All Locations</option>
                                <option value="gorakhpur">Gorakhpur</option>
                                <option value="lucknow">Lucknow</option>
                                <option value="varanasi">Varanasi</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="priceRangeFilter" class="form-label">Price Range</label>
                            <select class="form-select" id="priceRangeFilter">
                                <option value="">Any Price</option>
                                <option value="0-1000000">Under ₹10 Lakhs</option>
                                <option value="1000000-3000000">₹10L - ₹30L</option>
                                <option value="3000000-5000000">₹30L - ₹50L</option>
                                <option value="5000000-10000000">₹50L - ₹1 Cr</option>
                                <option value="10000000">Above ₹1 Cr</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="sortBy" class="form-label">Sort By</label>
                            <select class="form-select" id="sortBy">
                                <option value="featured">Featured</option>
                                <option value="price_asc">Price: Low to High</option>
                                <option value="price_desc">Price: High to Low</option>
                                <option value="newest">Newest First</option>
                                <option value="area_asc">Area: Small to Large</option>
                                <option value="area_desc">Area: Large to Small</option>
                            </select>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="button" id="advancedFiltersBtn" class="btn btn-outline-secondary w-100" data-bs-toggle="collapse" data-bs-target="#advancedFilters">
                                <i class="fas fa-sliders-h"></i>
                            </button>
                        </div>
                    </form>
                    
                    <!-- Advanced Filters -->
                    <div class="collapse mt-3" id="advancedFilters">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Bedrooms</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="bedrooms" id="bedroomAny" value="" checked>
                                    <label class="btn btn-outline-primary" for="bedroomAny">Any</label>
                                    <input type="radio" class="btn-check" name="bedrooms" id="bedroom1" value="1">
                                    <label class="btn btn-outline-primary" for="bedroom1">1+</label>
                                    <input type="radio" class="btn-check" name="bedrooms" id="bedroom2" value="2">
                                    <label class="btn btn-outline-primary" for="bedroom2">2+</label>
                                    <input type="radio" class="btn-check" name="bedrooms" id="bedroom3" value="3">
                                    <label class="btn btn-outline-primary" for="bedroom3">3+</label>
                                    <input type="radio" class="btn-check" name="bedrooms" id="bedroom4" value="4">
                                    <label class="btn btn-outline-primary" for="bedroom4">4+</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Area (sq ft)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="minArea" placeholder="Min">
                                    <span class="input-group-text">to</span>
                                    <input type="number" class="form-control" id="maxArea" placeholder="Max">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Amenities</label>
                                <select class="form-select" id="amenitiesFilter" multiple>
                                    <option value="parking">Parking</option>
                                    <option value="garden">Garden</option>
                                    <option value="security">24/7 Security</option>
                                    <option value="gym">Gym</option>
                                    <option value="pool">Swimming Pool</option>
                                    <option value="lift">Lift</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="button" id="resetFilters" class="btn btn-outline-danger w-100">
                                    <i class="fas fa-undo me-1"></i> Reset Filters
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Properties Grid -->
    <section class="py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0">🏗️ Our Premium Projects</h2>
                    <p class="text-muted mb-0">Handpicked properties that match your criteria</p>
                </div>
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span id="propertyCount" class="badge bg-primary rounded-pill">0</span> 
                        <span class="ms-1">Properties Found</span>
                    </div>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-secondary active view-toggle" data-view="grid">
                            <i class="fas fa-th-large"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary view-toggle" data-view="list">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="row g-4" id="propertiesGrid">
                <!-- Properties will be loaded here via JavaScript -->
            </div>

            <div class="text-center mt-5" id="loadingSpinner">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3">Loading premium properties...</p>
            </div>

            <div class="text-center mt-5 d-none" id="noProperties">
                <i class="fas fa-search fa-4x text-muted mb-4"></i>
                <h4 class="text-muted">No properties found matching your criteria</h4>
                <p class="text-muted">Try adjusting your filters or browse all properties</p>
                <button class="btn btn-primary" onclick="loadAllProperties()">Show All Properties</button>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2>Why Choose APS Dream Homes Pvt Ltd?</h2>
                <p class="lead text-muted">Experience the difference with our professional approach</p>
            </div>

            <div class="row g-4">
                <div class="col-lg-4" data-aos="fade-up">
                    <div class="feature-card h-100">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h5>Registered Company</h5>
                        <p class="text-muted">Licensed under Companies Act 2013 with proper legal compliance and transparent operations</p>
                    </div>
                </div>

                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-card h-100">
                        <div class="feature-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h5>Prime Locations</h5>
                        <p class="text-muted">Strategically located projects in high-growth areas with excellent connectivity and infrastructure</p>
                    </div>
                </div>

                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-card h-100">
                        <div class="feature-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h5>Customer First</h5>
                        <p class="text-muted">Every decision we make prioritizes customer satisfaction and long-term relationships</p>
                    </div>
                </div>

                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-card h-100">
                        <div class="feature-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h5>Timely Delivery</h5>
                        <p class="text-muted">Proven track record of delivering projects on time without compromising quality standards</p>
                    </div>
                </div>

                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="feature-card h-100">
                        <div class="feature-icon">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <h5>Sustainable Development</h5>
                        <p class="text-muted">Eco-friendly construction practices with green spaces and energy-efficient designs</p>
                    </div>
                </div>

                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="500">
                    <div class="feature-card h-100">
                        <div class="feature-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h5>24/7 Support</h5>
                        <p class="text-muted">Round-the-clock customer support and dedicated relationship managers for all clients</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h2 class="mb-4">Ready to Find Your Dream Property?</h2>
                    <p class="lead mb-4">
                        Join thousands of satisfied customers who have found their perfect home with APS Dream Homes Pvt Ltd
                    </p>
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <a href="contact.php" class="btn btn-light btn-lg">
                            <i class="fas fa-phone me-2"></i>Contact Us Today
                        </a>
                        <a href="about.php" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-info-circle me-2"></i>Learn More About Us
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5 class="mb-3">
                        <i class="fas fa-home me-2"></i>APS Dream Homes Pvt Ltd
                    </h5>
                    <p>Leading real estate developer in Gorakhpur with 8+ years of excellence in property development and customer satisfaction.</p>
                    <div class="social-links mt-3">
                        <a href="https://facebook.com/apsdreamhomes" class="text-white me-3" target="_blank">
                            <i class="fab fa-facebook-f fa-lg"></i>
                        </a>
                        <a href="https://instagram.com/apsdreamhomes" class="text-white me-3" target="_blank">
                            <i class="fab fa-instagram fa-lg"></i>
                        </a>
                        <a href="https://linkedin.com/company/aps-dream-homes-pvt-ltd" class="text-white me-3" target="_blank">
                            <i class="fab fa-linkedin-in fa-lg"></i>
                        </a>
                        <a href="https://youtube.com/apsdreamhomes" class="text-white" target="_blank">
                            <i class="fab fa-youtube fa-lg"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6 mb-4">
                    <h5 class="mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="index.php" class="text-white-50">Home</a></li>
                        <li class="mb-2"><a href="properties.php" class="text-white-50">Properties</a></li>
                        <li class="mb-2"><a href="about.php" class="text-white-50">About Us</a></li>
                        <li class="mb-2"><a href="contact.php" class="text-white-50">Contact</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <h5 class="mb-3">Contact Info</h5>
                    <ul class="list-unstyled text-white-50">
                        <li class="mb-2">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            123, Kunraghat Main Road<br>
                            Near Railway Station<br>
                            Gorakhpur, UP - 273008
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-phone-alt me-2"></i>
                            <a href="tel:+919554000001" class="text-white-50">+91-9554000001</a>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-envelope me-2"></i>
                            <a href="mailto:info@apsdreamhomes.com" class="text-white-50">info@apsdreamhomes.com</a>
                        </li>
                        <li>
                            <i class="fas fa-clock me-2"></i>
                            Mon-Sat: 9:30 AM - 7:00 PM<br>
                            Sun: 10:00 AM - 5:00 PM
                        </li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <h5 class="mb-3">Newsletter</h5>
                    <p class="text-white-50">Subscribe for latest property updates and exclusive deals</p>
                    <form class="mb-3" id="newsletterForm">
                        <div class="input-group">
                            <input type="email" class="form-control" placeholder="Your Email" required id="newsletterEmail">
                            <button class="btn btn-primary" type="submit">Subscribe</button>
                        </div>
                    </form>
                </div>
            </div>

            <hr class="my-4 bg-white">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0 text-white-50">
                        &copy; 2025 APS Dream Homes Pvt Ltd. All rights reserved.<br>
                        <small>Registration No: U70109UP2022PTC163047</small>
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#" class="text-white-50 me-3">Privacy Policy</a>
                    <a href="#" class="text-white-50">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });

        // Sample properties data (in real app, this would come from database)
        const propertiesData = [
            {
                id: 1,
                title: "APS Green Valley - 2BHK Premium Apartments",
                description: "Modern 2BHK apartments with world-class amenities in the heart of Gorakhpur. Features spacious living areas, contemporary design, and eco-friendly construction.",
                price: 4500000,
                type: "apartment",
                location: "Gorakhpur",
                bedrooms: 2,
                bathrooms: 2,
                area: 1200,
                features: ["Clubhouse", "Swimming Pool", "Gymnasium", "Security", "Parking"],
                image: "https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=800",
                status: "Available"
            },
            {
                id: 2,
                title: "APS Royal Residency - 3BHK Luxury Flats",
                description: "Experience luxury living with premium 3BHK apartments featuring Italian marble flooring, modular kitchens, and modern bathrooms with exclusive amenities.",
                price: 7500000,
                type: "apartment",
                location: "Gorakhpur",
                bedrooms: 3,
                bathrooms: 3,
                area: 1650,
                features: ["Rooftop Pool", "Banquet Hall", "Concierge", "Smart Home", "Security"],
                image: "https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800",
                status: "Available"
            },
            {
                id: 3,
                title: "APS Lakeview Plots - Investment Opportunity",
                description: "Prime residential plots in a rapidly developing area with excellent connectivity. Perfect for building your dream home or investment purposes.",
                price: 3500000,
                type: "plot",
                location: "Gorakhpur",
                bedrooms: 0,
                bathrooms: 0,
                area: 1200,
                features: ["Scenic Location", "Infrastructure", "Investment", "Clear Title"],
                image: "https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=800",
                status: "Available"
            },
            {
                id: 4,
                title: "APS Smart Villas - 4BHK Independent Houses",
                description: "Technology-integrated luxury villas with smart home features, private gardens, and premium construction quality for discerning homeowners.",
                price: 15000000,
                type: "villa",
                location: "Gorakhpur",
                bedrooms: 4,
                bathrooms: 4,
                area: 2500,
                features: ["Smart Technology", "Private Garden", "Swimming Pool", "Security", "Premium"],
                image: "https://images.unsplash.com/photo-1613977257363-707ba9348227?w=800",
                status: "Available"
            },
            {
                id: 5,
                title: "APS Business Park - Commercial Spaces",
                description: "Premium commercial office spaces designed for modern businesses with state-of-the-art infrastructure and professional management services.",
                price: 8500000,
                type: "commercial",
                location: "Gorakhpur",
                bedrooms: 0,
                bathrooms: 2,
                area: 1500,
                features: ["Modern Infrastructure", "High-Speed Internet", "Conference Rooms", "Parking", "Management"],
                image: "https://images.unsplash.com/photo-1497366216548-37526070297c?w=800",
                status: "Available"
            },
            {
                id: 6,
                title: "APS Affordable Housing - 1BHK Starter Homes",
                description: "Quality 1BHK apartments for first-time buyers and young families. Modern amenities at budget-friendly prices with excellent construction quality.",
                price: 2800000,
                type: "apartment",
                location: "Gorakhpur",
                bedrooms: 1,
                bathrooms: 1,
                area: 650,
                features: ["Affordable", "Quality Construction", "Community Center", "Security", "Parking"],
                image: "https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800",
                status: "Available"
            }
        ];

        // Load properties on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadAllProperties();

            // Newsletter form
            document.getElementById('newsletterForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const email = document.getElementById('newsletterEmail').value;
                if (email) {
                    alert('Thank you for subscribing to our newsletter!');
                    this.reset();
                }
            });
        });

        // Filter properties
        document.getElementById('searchBtn').addEventListener('click', filterProperties);
        document.getElementById('propertyTypeFilter').addEventListener('change', filterProperties);
        document.getElementById('locationFilter').addEventListener('change', filterProperties);
        document.getElementById('priceFilter').addEventListener('change', filterProperties);

        function filterProperties() {
            const typeFilter = document.getElementById('propertyTypeFilter').value;
            const locationFilter = document.getElementById('locationFilter').value;
            const priceFilter = document.getElementById('priceFilter').value;

            const filteredProperties = propertiesData.filter(property => {
                const typeMatch = !typeFilter || property.type === typeFilter;
                const locationMatch = !locationFilter || property.location === locationFilter;
                let priceMatch = true;

                if (priceFilter) {
                    const [min, max] = priceFilter.split('-').map(v => v ? parseInt(v) : null);
                    if (max) {
                        priceMatch = property.price >= min && property.price <= max;
                    } else {
                        priceMatch = property.price >= min;
                    }
                }

                return typeMatch && locationMatch && priceMatch;
            });

            displayProperties(filteredProperties);
        }

        function loadAllProperties() {
            displayProperties(propertiesData);
        }

        // Display properties in grid or list view
        function displayProperties(properties) {
            const grid = document.getElementById('propertiesGrid');
            const spinner = document.getElementById('loadingSpinner');
            const noProperties = document.getElementById('noProperties');
            const propertyCount = document.getElementById('propertyCount');

            spinner.classList.remove('d-none');
            grid.innerHTML = '';
            propertyCount.textContent = properties.length;

            // Simulate API delay
            setTimeout(() => {
                spinner.classList.add('d-none');

                if (properties.length === 0) {
                    noProperties.classList.remove('d-none');
                    return;
                }

                noProperties.classList.add('d-none');

                if (currentView === 'grid') {
                    grid.className = 'row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4';
                    grid.innerHTML = properties.map(property => createPropertyCard(property)).join('');
                } else {
                    grid.className = 'row row-cols-1 g-4';
                    grid.innerHTML = properties.map(property => createPropertyListItem(property)).join('');
                }

                // Initialize tooltips
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });

            }, 500);
        }

        // Create property card for grid view
        function createPropertyCard(property) {
            return `
                <div class="col" data-aos="fade-up">
                    <div class="card property-card h-100 shadow-sm">
                        <div class="position-relative">
                            <img src="${property.image}" class="card-img-top property-image" alt="${property.title}" loading="lazy">
                            ${property.featured ? '<div class="featured-badge">Featured</div>' : ''}
                            <div class="property-type-badge">${property.type.charAt(0).toUpperCase() + property.type.slice(1)}</div>
                            <div class="property-actions">
                                <button class="btn btn-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="Add to favorites">
                                    <i class="far fa-heart"></i>
                                </button>
                                <button class="btn btn-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="Share property">
                                    <i class="fas fa-share-alt"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title fw-bold mb-0">${property.title}</h5>
                                <div class="text-end">
                                    <div class="text-primary fw-bold fs-4">${formatPrice(property.price)}</div>
                                    <small class="text-muted">${(property.price / property.area).toFixed(2)}/sq.ft</small>
                                </div>
                            </div>
                            
                            <div class="mb-2">
                                <span class="location-badge">
                                    <i class="fas fa-map-marker-alt text-danger me-1"></i>
                                    ${property.location.charAt(0).toUpperCase() + property.location.slice(1)}
                                </span>
                            </div>
                            
                            <p class="card-text text-muted mb-3 flex-grow-1">
                                ${property.description.substring(0, 100)}...
                                <a href="#" class="text-decoration-none" onclick="viewProperty(${property.id}); return false;">Read more</a>
                            </p>

                            <div class="property-features mb-3">
                                ${property.bedrooms ? `
                                    <div class="feature">
                                        <i class="fas fa-bed"></i>
                                        <span>${property.bedrooms} ${property.bedrooms > 1 ? 'Beds' : 'Bed'}</span>
                                    </div>
                                ` : ''}
                                ${property.bathrooms ? `
                                    <div class="feature">
                                        <i class="fas fa-bath"></i>
                                        <span>${property.bathrooms} ${property.bathrooms > 1 ? 'Baths' : 'Bath'}</span>
                                    </div>
                                ` : ''}
                                <div class="feature">
                                    <i class="fas fa-vector-square"></i>
                                    <span>${formatArea(property.area)} sq.ft</span>
                                </div>
                            </div>

                            ${property.amenities && property.amenities.length > 0 ? `
                                <div class="amenities mb-3">
                                    ${property.amenities.slice(0, 3).map(amenity => `
                                        <span class="badge bg-light text-dark me-1 mb-1" data-bs-toggle="tooltip" title="${amenity.charAt(0).toUpperCase() + amenity.slice(1)}">
                                            <i class="fas fa-${getAmenityIcon(amenity)} me-1"></i>
                                            ${amenity.charAt(0).toUpperCase() + amenity.slice(1)}
                                        </span>
                                    `).join('')}
                                    ${property.amenities.length > 3 ? `
                                        <span class="badge bg-light text-dark" data-bs-toggle="tooltip" title="${property.amenities.slice(3).join(', ')}">
                                            +${property.amenities.length - 3} more
                                        </span>
                                    ` : ''}
                                </div>
                            ` : ''}

                            <div class="d-grid gap-2 mt-auto">
                                <button class="btn btn-primary" onclick="viewProperty(${property.id}); return false;">
                                    <i class="fas fa-eye me-1"></i> View Details
                                </button>
                                <button class="btn btn-outline-secondary" onclick="scheduleVisit(${property.id}, '${property.title.replace(/'/g, "\\'")}'); return false;">
                                    <i class="far fa-calendar-alt me-1"></i> Schedule Visit
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Create property list item for list view
        function createPropertyListItem(property) {
            return `
                <div class="col" data-aos="fade-up">
                    <div class="card property-list-item h-100 shadow-sm">
                        <div class="row g-0">
                            <div class="col-md-4">
                                <div class="position-relative h-100">
                                    <img src="${property.image}" class="img-fluid rounded-start h-100 w-100" style="object-fit: cover;" alt="${property.title}" loading="lazy">
                                    ${property.featured ? '<div class="featured-badge">Featured</div>' : ''}
                                    <div class="property-type-badge">${property.type.charAt(0).toUpperCase() + property.type.slice(1)}</div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="card-body h-100 d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h5 class="card-title fw-bold mb-1">${property.title}</h5>
                                            <div class="mb-2">
                                                <span class="location-badge">
                                                    <i class="fas fa-map-marker-alt text-danger me-1"></i>
                                                    ${property.location.charAt(0).toUpperCase() + property.location.slice(1)}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="text-primary fw-bold fs-4">${formatPrice(property.price)}</div>
                                            <small class="text-muted">${(property.price / property.area).toFixed(2)}/sq.ft</small>
                                        </div>
                                    </div>
                                    
                                    <p class="card-text text-muted mb-3">
                                        ${property.description.substring(0, 200)}...
                                        <a href="#" class="text-decoration-none" onclick="viewProperty(${property.id}); return false;">Read more</a>
                                    </p>

                                    <div class="property-features mb-3">
                                        ${property.bedrooms ? `
                                            <div class="feature">
                                                <i class="fas fa-bed"></i>
                                                <span>${property.bedrooms} ${property.bedrooms > 1 ? 'Beds' : 'Bed'}</span>
                                            </div>
                                        ` : ''}
                                        ${property.bathrooms ? `
                                            <div class="feature">
                                                <i class="fas fa-bath"></i>
                                                <span>${property.bathrooms} ${property.bathrooms > 1 ? 'Baths' : 'Bath'}</span>
                                            </div>
                                        ` : ''}
                                        <div class="feature">
                                            <i class="fas fa-vector-square"></i>
                                            <span>${formatArea(property.area)} sq.ft</span>
                                        </div>
                                    </div>

                                    ${property.amenities && property.amenities.length > 0 ? `
                                        <div class="amenities mb-3">
                                            ${property.amenities.slice(0, 5).map(amenity => `
                                                <span class="badge bg-light text-dark me-1 mb-1">
                                                    <i class="fas fa-${getAmenityIcon(amenity)} me-1"></i>
                                                    ${amenity.charAt(0).toUpperCase() + amenity.slice(1)}
                                                </span>
                                            `).join('')}
                                            ${property.amenities.length > 5 ? `
                                                <span class="badge bg-light text-dark" data-bs-toggle="tooltip" title="${property.amenities.slice(5).join(', ')}">
                                                    +${property.amenities.length - 5} more
                                                </span>
                                            ` : ''}
                                        </div>
                                    ` : ''}

                                    <div class="d-flex justify-content-between align-items-center mt-auto">
                                        <div class="property-actions">
                                            <button class="btn btn-sm btn-outline-secondary me-2" data-bs-toggle="tooltip" title="Add to favorites">
                                                <i class="far fa-heart"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary me-2" data-bs-toggle="tooltip" title="Share property">
                                                <i class="fas fa-share-alt"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="tooltip" title="Print details">
                                                <i class="fas fa-print"></i>
                                            </button>
                                        </div>
                                        <div>
                                            <button class="btn btn-outline-primary me-2" onclick="scheduleVisit(${property.id}, '${property.title.replace(/'/g, "\\'")}'); return false;">
                                                <i class="far fa-calendar-alt me-1"></i> Schedule Visit
                                            </button>
                                            <button class="btn btn-primary" onclick="viewProperty(${property.id}); return false;">
                                                <i class="fas fa-eye me-1"></i> View Details
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Get icon for amenity
        function getAmenityIcon(amenity) {
            const icons = {
                'parking': 'parking',
                'garden': 'tree',
                'security': 'shield-alt',
                'gym': 'dumbbell',
                'pool': 'swimming-pool',
                'lift': 'elevator'
            };
            return icons[amenity] || 'check-circle';
        }

        // View property details
        function viewProperty(propertyId) {
            // In a real application, this would navigate to property detail page
            // For now, show a modal with property details
            const property = propertiesData.find(p => p.id === propertyId);
            if (!property) return;
            
            // Create modal HTML
            const modalHTML = `
                <div class="modal fade" id="propertyModal" tabindex="-1" aria-labelledby="propertyModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="propertyModalLabel">${property.title}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-lg-7">
                                        <div id="propertyCarousel" class="carousel slide" data-bs-ride="carousel">
                                            <div class="carousel-inner rounded">
                                                <div class="carousel-item active">
                                                    <img src="${property.image}" class="d-block w-100" alt="${property.title}">
                                                </div>
                                                <!-- Additional images would go here -->
                                            </div>
                                            <button class="carousel-control-prev" type="button" data-bs-target="#propertyCarousel" data-bs-slide="prev">
                                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                <span class="visually-hidden">Previous</span>
                                            </button>
                                            <button class="carousel-control-next" type="button" data-bs-target="#propertyCarousel" data-bs-slide="next">
                                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                <span class="visually-hidden">Next</span>
                                            </button>
                                        </div>
                                        
                                        <div class="mt-4">
                                            <h5>Description</h5>
                                            <p class="text-muted">${property.description}</p>
                                            
                                            <div class="row mt-4">
                                                <div class="col-md-6">
                                                    <h5>Property Details</h5>
                                                    <ul class="list-unstyled">
                                                        <li class="mb-2">
                                                            <i class="fas fa-home me-2 text-primary"></i>
                                                            <strong>Type:</strong> ${property.type.charAt(0).toUpperCase() + property.type.slice(1)}
                                                        </li>
                                                        ${property.bedrooms ? `
                                                            <li class="mb-2">
                                                                <i class="fas fa-bed me-2 text-primary"></i>
                                                                <strong>Bedrooms:</strong> ${property.bedrooms}
                                                            </li>
                                                        ` : ''}
                                                        ${property.bathrooms ? `
                                                            <li class="mb-2">
                                                                <i class="fas fa-bath me-2 text-primary"></i>
                                                                <strong>Bathrooms:</strong> ${property.bathrooms}
                                                            </li>
                                                        ` : ''}
                                                        <li class="mb-2">
                                                            <i class="fas fa-vector-square me-2 text-primary"></i>
                                                            <strong>Area:</strong> ${formatArea(property.area)} sq.ft
                                                        </li>
                                                        <li class="mb-2">
                                                            <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                                                            <strong>Location:</strong> ${property.location.charAt(0).toUpperCase() + property.location.slice(1)}
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div class="col-md-6">
                                                    <h5>Price Details</h5>
                                                    <ul class="list-unstyled">
                                                        <li class="mb-2">
                                                            <i class="fas fa-tag me-2 text-primary"></i>
                                                            <strong>Price:</strong> ${formatPrice(property.price)}
                                                        </li>
                                                        <li class="mb-2">
                                                            <i class="fas fa-calculator me-2 text-primary"></i>
                                                            <strong>Price per sq.ft:</strong> ₹${(property.price / property.area).toFixed(2)}
                                                        </li>
                                                        <li class="mb-2">
                                                            <i class="fas fa-file-invoice-dollar me-2 text-primary"></i>
                                                            <strong>Maintenance:</strong> Included
                                                        </li>
                                                    </ul>
                                                    
                                                    <div class="mt-4">
                                                        <button class="btn btn-primary w-100 mb-2" onclick="scheduleVisit(${property.id}, '${property.title.replace(/'/g, "\\'")}'); $('#propertyModal').modal('hide');">
                                                            <i class="far fa-calendar-alt me-1"></i> Schedule a Visit
                                                        </button>
                                                        <button class="btn btn-outline-primary w-100">
                                                            <i class="fas fa-phone-alt me-1"></i> Contact Agent
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-5">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <h5>Request Information</h5>
                                                <form id="propertyInquiryForm" class="mt-3">
                                                    <input type="hidden" name="property_id" value="${property.id}">
                                                    <input type="hidden" name="property_title" value="${property.title}">
                                                    
                                                    <div class="mb-3">
                                                        <label for="inquiryName" class="form-label">Your Name</label>
                                                        <input type="text" class="form-control" id="inquiryName" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="inquiryEmail" class="form-label">Email Address</label>
                                                        <input type="email" class="form-control" id="inquiryEmail" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="inquiryPhone" class="form-label">Phone Number</label>
                                                        <input type="tel" class="form-control" id="inquiryPhone" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="inquiryMessage" class="form-label">Message</label>
                                                        <textarea class="form-control" id="inquiryMessage" rows="3" placeholder="I'm interested in this property..."></textarea>
                                                    </div>
                                                    <button type="submit" class="btn btn-primary w-100">
                                                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                                        <span class="btn-text">Send Inquiry</span>
                                                    </button>
                                                </form>
                                                
                                                <div class="mt-4">
                                                    <h6>Share this property</h6>
                                                    <div class="d-flex gap-2">
                                                        <button class="btn btn-outline-secondary btn-sm">
                                                            <i class="fab fa-facebook-f"></i>
                                                        </button>
                                                        <button class="btn btn-outline-secondary btn-sm">
                                                            <i class="fab fa-whatsapp"></i>
                                                        </button>
                                                        <button class="btn btn-outline-secondary btn-sm">
                                                            <i class="fas fa-link"></i>
                                                        </button>
                                                        <button class="btn btn-outline-secondary btn-sm ms-auto" onclick="window.print()">
                                                            <i class="fas fa-print"></i> Print
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Add modal to DOM
            const modalContainer = document.createElement('div');
            modalContainer.innerHTML = modalHTML;
            document.body.appendChild(modalContainer);
            
            // Initialize and show modal
            const propertyModal = new bootstrap.Modal(document.getElementById('propertyModal'));
            propertyModal.show();
            
            // Handle form submission
            const form = document.getElementById('propertyInquiryForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const submitBtn = form.querySelector('button[type="submit"]');
                    const spinner = submitBtn.querySelector('.spinner-border');
                    const btnText = submitBtn.querySelector('.btn-text');
                    
                    // Show loading state
                    spinner.classList.remove('d-none');
                    btnText.textContent = 'Sending...';
                    
                    // Simulate form submission
                    setTimeout(() => {
                        // Reset form
                        form.reset();
                        
                        // Hide loading state
                        spinner.classList.add('d-none');
                        btnText.textContent = 'Message Sent!';
                        
                        // Show success message
                        showToast('Your inquiry has been sent successfully! Our team will contact you shortly.', 'success');
                        
                        // Reset button text after delay
                        setTimeout(() => {
                            btnText.textContent = 'Send Inquiry';
                        }, 2000);
                    }, 1500);
                });
            }
            
            // Clean up modal on close
            document.getElementById('propertyModal').addEventListener('hidden.bs.modal', function() {
                modalContainer.remove();
            });
        }
    </script>
</body>
</html>
