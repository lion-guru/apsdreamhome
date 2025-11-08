<?php
/**
 * APS Dream Home - Modern Homepage
 * Enhanced UI/UX design with clean, modern interface
 */

// Get featured properties
$featured_properties_query = "SELECT * FROM properties WHERE featured = 1 LIMIT 6";
$featured_properties = $conn->query($featured_properties_query);

// Get latest properties
$latest_properties_query = "SELECT * FROM properties ORDER BY created_at DESC LIMIT 6";
$latest_properties = $conn->query($latest_properties_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APS Dream Home - Find Your Perfect Property</title>

    <!-- Modern CSS Framework -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --warning-gradient: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            scroll-behavior: smooth;
        }

        /* Modern Hero Section */
        .hero-section {
            min-height: 100vh;
            background: var(--primary-gradient);
            color: white;
            display: flex;
            align-items: center;
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
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 600px;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 1.5rem;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        .hero-subtitle {
            font-size: 1.25rem;
            line-height: 1.6;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        /* Advanced Search Card */
        .search-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            padding: 40px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .search-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .search-group {
            position: relative;
        }

        .search-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            display: block;
            font-size: 0.9rem;
        }

        .modern-select {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 12px 20px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
            width: 100%;
        }

        .modern-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }

        .btn-search {
            background: var(--primary-gradient);
            border: none;
            padding: 15px 40px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
            width: 100%;
        }

        .btn-search:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(102, 126, 234, 0.4);
        }

        /* Property Cards */
        .property-card-modern {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            transition: all 0.4s ease;
            border: 2px solid transparent;
            margin-bottom: 30px;
        }

        .property-card-modern:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            border-color: #667eea;
        }

        .property-image-modern {
            position: relative;
            height: 250px;
            overflow: hidden;
        }

        .property-image-modern img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .property-card-modern:hover .property-image-modern img {
            transform: scale(1.05);
        }

        .property-badges {
            position: absolute;
            top: 15px;
            left: 15px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .property-badge {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .property-content-modern {
            padding: 25px;
        }

        .property-title-modern {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1a237e;
            margin-bottom: 8px;
            line-height: 1.3;
        }

        .property-location-modern {
            color: #667eea;
            font-size: 0.9rem;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
        }

        .property-price-modern {
            font-size: 1.5rem;
            font-weight: 800;
            color: #28a745;
            margin-bottom: 15px;
        }

        .property-features-modern {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .feature-item {
            display: flex;
            align-items: center;
            background: #f8f9fa;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 0.85rem;
            color: #666;
        }

        .feature-item i {
            margin-right: 5px;
            color: #667eea;
        }

        .property-actions-modern {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-view-details {
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            flex-grow: 1;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-view-details:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
            color: white;
            text-decoration: none;
        }

        .btn-virtual-tour {
            background: linear-gradient(135deg, #17a2b8, #20c997);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-virtual-tour:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(23, 162, 184, 0.3);
            color: white;
            text-decoration: none;
        }

        /* Section Styling */
        .section-header {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: #1a237e;
            margin-bottom: 1rem;
            position: relative;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--primary-gradient);
            border-radius: 2px;
        }

        .section-subtitle {
            font-size: 1.1rem;
            color: #666;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }

            .search-card {
                padding: 25px;
                margin-top: 2rem;
            }

            .search-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .section-title {
                font-size: 2rem;
            }
        }

        /* Trust Indicators */
        .trust-indicators {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 20px;
            margin-top: 2rem;
        }

        .trust-avatar-group {
            position: relative;
            display: inline-block;
        }

        .trust-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 3px solid white;
            margin-left: -10px;
            object-fit: cover;
        }

        .trust-avatar:first-child {
            margin-left: 0;
        }

        /* Animations */
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

        .animate-fade-up {
            animation: fadeInUp 0.8s ease-out;
        }

        /* Loading States */
        .loading-pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
    </style>
</head>
<body>

<?php include '../app/views/layouts/header.php'; ?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <div class="hero-content animate-fade-up">
                    <span class="badge bg-primary bg-opacity-20 text-white mb-3 px-4 py-2 rounded-pill d-inline-flex align-items-center">
                        <i class="fas fa-star me-2"></i>Trusted by 10,000+ Clients
                    </span>

                    <h1 class="hero-title">
                        Find Your <span style="background: linear-gradient(135deg, #ffc107, #fd7e14); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Dream Home</span>
                    </h1>

                    <p class="hero-subtitle">
                        Discover exclusive properties with the most trusted real estate platform in Gorakhpur.
                        From luxury apartments to prime commercial spaces, we have it all.
                    </p>

                    <!-- Quick Actions -->
                    <div class="d-flex flex-wrap gap-3 mb-5">
                        <a href="#featured-properties" class="btn btn-primary btn-lg px-4 py-3 d-flex align-items-center">
                            <i class="fas fa-home me-2"></i>Explore Properties
                        </a>
                        <a href="#contact" class="btn btn-outline-light btn-lg px-4 py-3 d-flex align-items-center">
                            <i class="fas fa-phone-alt me-2"></i>Contact Agent
                        </a>
                    </div>

                    <!-- Trust Indicators -->
                    <div class="trust-indicators">
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

            <!-- Advanced Search Card -->
            <div class="col-lg-6">
                <div class="search-card animate-fade-up" data-aos="fade-left">
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
                                <label class="search-label">
                                    <i class="fas fa-building me-2"></i>Property Type
                                </label>
                                <select class="modern-select" name="type">
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
                                <label class="search-label">
                                    <i class="fas fa-map-marker-alt me-2"></i>Location
                                </label>
                                <select class="modern-select" name="location">
                                    <option value="">All Locations</option>
                                    <option value="gorakhpur">Gorakhpur</option>
                                    <option value="lucknow">Lucknow</option>
                                    <option value="delhi">Delhi</option>
                                    <option value="mumbai">Mumbai</option>
                                    <option value="bangalore">Bangalore</option>
                                </select>
                            </div>

                            <!-- Price Range -->
                            <div class="search-group">
                                <label class="search-label">
                                    <i class="fas fa-indian-rupee-sign me-2"></i>Price Range
                                </label>
                                <select class="modern-select" name="price_range">
                                    <option value="">Any Price</option>
                                    <option value="under_10L">Under ₹10 Lakhs</option>
                                    <option value="10L_25L">₹10L - ₹25L</option>
                                    <option value="25L_50L">₹25L - ₹50L</option>
                                    <option value="50L_1Cr">₹50L - ₹1Cr</option>
                                    <option value="1Cr_2Cr">₹1Cr - ₹2Cr</option>
                                    <option value="above_2Cr">Above ₹2Cr</option>
                                </select>
                            </div>

                            <!-- Bedrooms -->
                            <div class="search-group">
                                <label class="search-label">
                                    <i class="fas fa-bed me-2"></i>Bedrooms
                                </label>
                                <select class="modern-select" name="bedrooms">
                                    <option value="">🛏️ Any</option>
                                    <option value="1">1 BHK</option>
                                    <option value="2">2 BHK</option>
                                    <option value="3">3 BHK</option>
                                    <option value="4">4+ BHK</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-search">
                            <i class="fas fa-search me-2"></i>🔍 Search Properties
                        </button>
                    </form>

                    <!-- Advanced Features -->
                    <div class="row mt-4">
                        <div class="col-md-4 text-center">
                            <i class="fas fa-robot fa-2x text-primary mb-2"></i>
                            <h6>AI Assistant</h6>
                            <small class="text-muted">24/7 property help</small>
                        </div>
                        <div class="col-md-4 text-center">
                            <i class="fas fa-vr-cardboard fa-2x text-success mb-2"></i>
                            <h6>VR Tours</h6>
                            <small class="text-muted">Virtual property tours</small>
                        </div>
                        <div class="col-md-4 text-center">
                            <i class="fas fa-shield-alt fa-2x text-warning mb-2"></i>
                            <h6>Blockchain</h6>
                            <small class="text-muted">Secure verification</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Properties Section -->
<section id="featured-properties" class="py-5 bg-light">
    <div class="container">
        <div class="section-header animate-fade-up">
            <h2 class="section-title">
                <i class="fas fa-home me-3"></i>Featured Properties
            </h2>
            <p class="section-subtitle">
                Handpicked premium properties for discerning buyers
            </p>
        </div>

        <?php if ($featured_properties && $featured_properties->num_rows > 0): ?>
            <div class="row">
                <?php while ($property = $featured_properties->fetch_assoc()): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="property-card-modern animate-fade-up">
                            <div class="property-image-modern">
                                <img src="/uploads/properties/<?php echo $property['image']; ?>" alt="<?php echo $property['title']; ?>">
                                <div class="property-badges">
                                    <span class="property-badge">Featured</span>
                                    <?php if ($property['featured']): ?>
                                        <span class="property-badge featured">Premium</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="property-content-modern">
                                <h3 class="property-title-modern"><?php echo $property['title']; ?></h3>
                                <div class="property-location-modern">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo $property['city'] . ', ' . $property['state']; ?>
                                </div>
                                <div class="property-price-modern">
                                    ₹<?php echo number_format($property['price']); ?>
                                </div>
                                <div class="property-features-modern">
                                    <span class="feature-item">
                                        <i class="fas fa-bed"></i><?php echo $property['bedrooms']; ?> Beds
                                    </span>
                                    <span class="feature-item">
                                        <i class="fas fa-bath"></i><?php echo $property['bathrooms']; ?> Baths
                                    </span>
                                    <span class="feature-item">
                                        <i class="fas fa-ruler-combined"></i><?php echo $property['area_sqft']; ?> sq.ft
                                    </span>
                                </div>
                                <div class="property-actions-modern">
                                    <a href="/property/<?php echo $property['id']; ?>" class="btn-view-details">
                                        View Details
                                    </a>
                                    <a href="/virtual-tour/<?php echo $property['id']; ?>" class="btn-virtual-tour">
                                        <i class="fas fa-vr-cardboard"></i> VR Tour
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-home fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">No featured properties available</h4>
                <p class="text-muted mb-4">Please check back later for new property listings.</p>
                <a href="/properties" class="btn btn-primary">
                    <i class="fas fa-search me-2"></i>View All Properties
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Latest Properties Section -->
<section class="py-5">
    <div class="container">
        <div class="section-header animate-fade-up">
            <h2 class="section-title">
                <i class="fas fa-clock me-3"></i>Latest Properties
            </h2>
            <p class="section-subtitle">
                Recently added properties in prime locations
            </p>
        </div>

        <?php if ($latest_properties && $latest_properties->num_rows > 0): ?>
            <div class="row">
                <?php while ($property = $latest_properties->fetch_assoc()): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="property-card-modern animate-fade-up">
                            <div class="property-image-modern">
                                <img src="/uploads/properties/<?php echo $property['image']; ?>" alt="<?php echo $property['title']; ?>">
                                <div class="property-badges">
                                    <span class="property-badge">New</span>
                                </div>
                            </div>
                            <div class="property-content-modern">
                                <h3 class="property-title-modern"><?php echo $property['title']; ?></h3>
                                <div class="property-location-modern">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo $property['city'] . ', ' . $property['state']; ?>
                                </div>
                                <div class="property-price-modern">
                                    ₹<?php echo number_format($property['price']); ?>
                                </div>
                                <div class="property-features-modern">
                                    <span class="feature-item">
                                        <i class="fas fa-bed"></i><?php echo $property['bedrooms']; ?> Beds
                                    </span>
                                    <span class="feature-item">
                                        <i class="fas fa-bath"></i><?php echo $property['bathrooms']; ?> Baths
                                    </span>
                                    <span class="feature-item">
                                        <i class="fas fa-ruler-combined"></i><?php echo $property['area_sqft']; ?> sq.ft
                                    </span>
                                </div>
                                <div class="property-actions-modern">
                                    <a href="/property/<?php echo $property['id']; ?>" class="btn-view-details">
                                        View Details
                                    </a>
                                    <a href="/virtual-tour/<?php echo $property['id']; ?>" class="btn-virtual-tour">
                                        <i class="fas fa-vr-cardboard"></i> VR Tour
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-home fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">No properties available</h4>
                <p class="text-muted">Please check back later for new property listings.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Features Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h2 class="section-title">Why Choose APS Dream Home?</h2>
            <p class="section-subtitle">Experience the future of real estate with our cutting-edge features</p>
        </div>

        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-robot fa-3x text-primary"></i>
                    </div>
                    <h5>AI Assistant</h5>
                    <p class="text-muted">24/7 intelligent property assistant to help you find the perfect home</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-vr-cardboard fa-3x text-success"></i>
                    </div>
                    <h5>VR Tours</h5>
                    <p class="text-muted">Experience properties virtually with immersive 3D tours</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-shield-alt fa-3x text-warning"></i>
                    </div>
                    <h5>Blockchain Security</h5>
                    <p class="text-muted">Secure property verification with blockchain technology</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-mobile-alt fa-3x text-info"></i>
                    </div>
                    <h5>Mobile First</h5>
                    <p class="text-muted">Progressive web app works perfectly on all devices</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-5" style="background: var(--primary-gradient);">
    <div class="container text-center">
        <h2 class="text-white mb-4">Ready to Find Your Dream Home?</h2>
        <p class="text-white-50 mb-4">Join thousands of satisfied customers who found their perfect property with us</p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="/properties" class="btn btn-light btn-lg px-4 py-3">
                <i class="fas fa-search me-2"></i>Browse Properties
            </a>
            <a href="/contact" class="btn btn-outline-light btn-lg px-4 py-3">
                <i class="fas fa-phone me-2"></i>Contact Us
            </a>
            <a href="/chatbot" class="btn btn-warning btn-lg px-4 py-3">
                <i class="fas fa-robot me-2"></i>Talk to AI Assistant
            </a>
        </div>
    </div>
</section>

<?php include '../app/views/layouts/footer.php'; ?>

<!-- JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    // Initialize AOS
    AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: true
    });

    // Search form enhancement
    document.querySelector('.modern-search-form').addEventListener('submit', function(e) {
        e.preventDefault();

        // Get form data
        const formData = new FormData(this);
        const searchParams = new URLSearchParams();

        for (let [key, value] of formData.entries()) {
            if (value) {
                searchParams.append(key, value);
            }
        }

        // Redirect to properties page with search parameters
        window.location.href = '/properties?' + searchParams.toString();
    });

    // Loading animation for property cards
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observe property cards
    document.querySelectorAll('.property-card-modern').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });
</script>

</body>
</html>
