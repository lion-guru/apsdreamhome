<?php
// Enhanced Property Listings with Competitor-Inspired Features
require_once __DIR__ . '/init.php';

// Enhanced property data with competitor-inspired features
$enhanced_properties = [
    [
        'id' => 1,
        'name' => 'APS Anant City',
        'type' => 'Residential Plots',
        'location' => 'Gorakhpur - NH-28',
        'size' => '1000-5000 Sq.Ft',
        'price' => '₹2,500/Sq.Ft',
        'status' => 'Available',
        'phase' => 'Phase 1',
        'security' => true,
        'roads' => '25-30 Feet Wide',
        'drainage' => true,
        'park' => true,
        'amenities' => ['24/7 Security', 'Wide Roads', 'Underground Drainage', 'Central Park', 'Street Lighting'],
        'image' => getPlaceholderUrl(400, 300, 'APS Anant City'),
        'featured' => true,
        'rera' => 'UPRERAAGT12345',
        'description' => 'Inspired by Anantjit Infraworld success, APS Anant City brings premium township living with modern amenities.'
    ],
    [
        'id' => 2,
        'name' => 'APS Royal Enclave',
        'type' => 'Residential Plots',
        'location' => 'Gorakhpur - Bypass Road',
        'size' => '1200-3000 Sq.Ft',
        'price' => '₹2,800/Sq.Ft',
        'status' => 'Available',
        'phase' => 'Phase 1',
        'security' => true,
        'roads' => '30 Feet Wide',
        'drainage' => true,
        'park' => true,
        'amenities' => ['Gated Community', 'CCTV Surveillance', 'Children Play Area', 'Jogging Track', 'Clubhouse'],
        'image' => getPlaceholderUrl(400, 300, 'APS Royal Enclave'),
        'featured' => true,
        'rera' => 'UPRERAAGT12346',
        'description' => 'Premium residential plots with world-class amenities inspired by Royal Group developments.'
    ],
    [
        'id' => 3,
        'name' => 'APS Green Valley',
        'type' => 'Farm Houses',
        'location' => 'Gorakhpur - outskirts',
        'size' => '5000-10000 Sq.Ft',
        'price' => '₹1,800/Sq.Ft',
        'status' => 'Limited',
        'phase' => 'Launch',
        'security' => true,
        'roads' => '40 Feet Wide',
        'drainage' => true,
        'park' => true,
        'amenities' => ['Farm House Plots', 'Green Belt', 'Water Bodies', 'Private Security', 'Landscaped Gardens'],
        'image' => getPlaceholderUrl(400, 300, 'APS Green Valley'),
        'featured' => false,
        'rera' => 'UPRERAAGT12347',
        'description' => 'Luxury farm house plots inspired by Royal Upvan concept with premium amenities.'
    ],
    [
        'id' => 4,
        'name' => 'APS Heritage Homes',
        'type' => 'Row Houses',
        'location' => 'Gorakhpur - Civil Lines',
        'size' => '1500-2500 Sq.Ft',
        'price' => '₹45 Lakhs - 75 Lakhs',
        'status' => 'Booking Open',
        'phase' => 'Phase 1',
        'security' => true,
        'roads' => '25 Feet Wide',
        'drainage' => true,
        'park' => true,
        'amenities' => ['Modern Row Houses', 'Private Garden', 'Parking Space', 'Community Center', 'Smart Home Features'],
        'image' => getPlaceholderUrl(400, 300, 'APS Heritage Homes'),
        'featured' => false,
        'rera' => 'UPRERAAGT12348',
        'description' => 'Modern row houses with smart technology and premium finishing.'
    ],
    [
        'id' => 5,
        'name' => 'APS Aman Vihar',
        'type' => 'Residential Plots',
        'location' => 'Gorakhpur - Azamgarh Road',
        'size' => '800-4000 Sq.Ft',
        'price' => '₹2,200/Sq.Ft',
        'status' => 'Available',
        'phase' => 'Phase 2',
        'security' => true,
        'roads' => '25 Feet Wide',
        'drainage' => true,
        'park' => true,
        'amenities' => ['Affordable Plots', 'Basic Infrastructure', 'Security', 'Park Area', 'Water Supply'],
        'image' => getPlaceholderUrl(400, 300, 'APS Aman Vihar'),
        'featured' => false,
        'rera' => 'UPRERAAGT12349',
        'description' => 'Affordable residential plots with essential amenities for budget-conscious buyers.'
    ],
    [
        'id' => 6,
        'name' => 'APS Balaji Tower',
        'type' => 'Apartments',
        'location' => 'Gorakhpur - City Center',
        'size' => '2/3 BHK',
        'price' => '₹35 Lakhs - 65 Lakhs',
        'status' => 'Under Construction',
        'phase' => 'Phase 1',
        'security' => true,
        'roads' => '30 Feet Wide',
        'drainage' => true,
        'park' => true,
        'amenities' => ['Modern Apartments', 'Lift', 'Parking', 'Power Backup', 'Gym', 'Community Hall'],
        'image' => getPlaceholderUrl(400, 300, 'APS Balaji Tower'),
        'featured' => false,
        'rera' => 'UPRERAAGT12350',
        'description' => 'Modern residential apartments with all modern amenities in prime location.'
    ]
];

// Set page metadata
$page_title = 'Enhanced Property Listings - APS Dream Homes';
$page_description = 'Premium residential plots, apartments, and farm houses with advanced amenities inspired by market leaders';
$page_keywords = 'APS Dream Homes, property listings, residential plots, Gorakhpur properties, real estate';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <meta name="description" content="<?php echo $page_description; ?>">
    <meta name="keywords" content="<?php echo $page_keywords; ?>">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --accent-color: #f093fb;
            --success-color: #28a745;
            --info-color: #17a2b8;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --dark-color: #2c3e50;
            --light-color: #ecf0f1;
        }
        
        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            padding: 120px 0 80px;
            color: white;
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
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff20" fill-opacity="1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,138.7C960,139,1056,117,1152,96C1248,75,1344,53,1392,42.7L1440,32L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
            background-size: cover;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
        }
        
        /* Enhanced Property Cards */
        .enhanced-listings-section {
            padding: 80px 0;
            background: #f8f9fa;
        }
        
        .property-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
            position: relative;
        }
        
        .property-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
        }
        
        .property-card.featured {
            border: 3px solid var(--primary-color);
        }
        
        .property-card.featured::before {
            content: 'FEATURED';
            position: absolute;
            top: 20px;
            right: -30px;
            background: var(--primary-color);
            color: white;
            padding: 8px 40px;
            font-weight: 700;
            font-size: 0.85rem;
            transform: rotate(45deg);
            z-index: 10;
        }
        
        .property-image {
            position: relative;
            height: 250px;
            overflow: hidden;
        }
        
        .property-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .property-card:hover .property-image img {
            transform: scale(1.1);
        }
        
        .property-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: var(--success-color);
            color: white;
            padding: 8px 15px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .property-badge.limited {
            background: var(--warning-color);
        }
        
        .property-badge.booking {
            background: var(--info-color);
        }
        
        .property-content {
            padding: 30px;
        }
        
        .property-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 10px;
        }
        
        .property-type {
            color: #666;
            font-size: 0.95rem;
            margin-bottom: 15px;
        }
        
        .property-location {
            display: flex;
            align-items: center;
            color: #666;
            margin-bottom: 20px;
        }
        
        .property-location i {
            color: var(--primary-color);
            margin-right: 8px;
        }
        
        .property-features {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .feature-tag {
            background: #f8f9fa;
            color: #666;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            border: 1px solid #e9ecef;
        }
        
        .feature-tag.highlight {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
        }
        
        .property-specs {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .spec-item {
            display: flex;
            align-items: center;
        }
        
        .spec-item i {
            color: var(--primary-color);
            margin-right: 8px;
            width: 20px;
        }
        
        .spec-item span {
            color: #666;
            font-size: 0.9rem;
        }
        
        .property-price {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--primary-color);
            margin-bottom: 20px;
        }
        
        .property-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-property {
            flex: 1;
            padding: 12px 20px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            text-align: center;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        
        .btn-primary-property {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }
        
        .btn-primary-property:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            color: white;
        }
        
        .btn-outline-property {
            background: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
        }
        
        .btn-outline-property:hover {
            background: var(--primary-color);
            color: white;
        }
        
        /* Filter Section */
        .filter-section {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            margin-bottom: 40px;
        }
        
        .filter-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 25px;
        }
        
        .filter-group {
            margin-bottom: 20px;
        }
        
        .filter-group label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 10px;
            display: block;
        }
        
        .filter-options {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .filter-option {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            padding: 8px 15px;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        
        .filter-option:hover,
        .filter-option.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        /* Stats Section */
        .stats-section {
            padding: 60px 0;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
        }
        
        .stat-card {
            text-align: center;
            padding: 30px;
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 10px;
        }
        
        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .hero-section {
                padding: 80px 0 60px;
            }
            
            .property-card.featured::before {
                right: -40px;
                padding: 6px 30px;
                font-size: 0.75rem;
            }
            
            .property-content {
                padding: 20px;
            }
            
            .property-specs {
                grid-template-columns: 1fr;
            }
            
            .filter-section {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include_once __DIR__ . '/includes/templates/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center hero-content" data-aos="fade-up">
                    <div class="badge bg-white text-primary mb-3 px-3 py-2 rounded-pill">
                        <i class="fas fa-building me-2"></i>Enhanced Property Listings
                    </div>
                    <h1 class="display-3 fw-bold mb-4">Premium Properties with Advanced Features</h1>
                    <p class="lead mb-4">
                        Inspired by Gorakhpur's best real estate developments, our enhanced property listings 
                        feature premium amenities, advanced security, and modern infrastructure.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Filter Section -->
    <section class="enhanced-listings-section">
        <div class="container">
            <div class="filter-section" data-aos="fade-up">
                <h3 class="filter-title">
                    <i class="fas fa-filter me-2"></i>Find Your Dream Property
                </h3>
                
                <div class="row">
                    <div class="col-lg-4 col-md-6">
                        <div class="filter-group">
                            <label>Property Type</label>
                            <div class="filter-options">
                                <div class="filter-option active" data-filter="all">All Types</div>
                                <div class="filter-option" data-filter="plots">Plots</div>
                                <div class="filter-option" data-filter="apartments">Apartments</div>
                                <div class="filter-option" data-filter="farmhouse">Farm House</div>
                                <div class="filter-option" data-filter="rowhouse">Row House</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6">
                        <div class="filter-group">
                            <label>Features</label>
                            <div class="filter-options">
                                <div class="filter-option active" data-feature="all">All</div>
                                <div class="filter-option" data-feature="security">24/7 Security</div>
                                <div class="filter-option" data-feature="park">Park Area</div>
                                <div class="filter-option" data-feature="drainage">Drainage</div>
                                <div class="filter-option" data-feature="clubhouse">Clubhouse</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6">
                        <div class="filter-group">
                            <label>Status</label>
                            <div class="filter-options">
                                <div class="filter-option active" data-status="all">All</div>
                                <div class="filter-option" data-status="available">Available</div>
                                <div class="filter-option" data-status="limited">Limited</div>
                                <div class="filter-option" data-status="booking">Booking</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Property Listings -->
            <div class="row" id="propertyListings">
                <?php foreach ($enhanced_properties as $property): ?>
                    <div class="col-lg-4 col-md-6 mb-4 property-item" 
                         data-type="<?php echo strtolower(str_replace(' ', '', $property['type'])); ?>"
                         data-status="<?php echo strtolower($property['status']); ?>"
                         data-features="<?php echo implode(',', array_map('strtolower', $property['amenities'])); ?>">
                        <div class="property-card <?php echo $property['featured'] ? 'featured' : ''; ?>" data-aos="fade-up" data-aos-delay="<?php echo array_search($property, $enhanced_properties) * 100; ?>">
                            <div class="property-image">
                                <img src="<?php echo $property['image']; ?>" alt="<?php echo $property['name']; ?>">
                                <div class="property-badge <?php 
                                    echo $property['status'] === 'Limited' ? 'limited' : 
                                         ($property['status'] === 'Booking Open' ? 'booking' : ''); 
                                ?>">
                                    <?php echo $property['status']; ?>
                                </div>
                            </div>
                            
                            <div class="property-content">
                                <h3 class="property-title"><?php echo $property['name']; ?></h3>
                                <p class="property-type"><?php echo $property['type']; ?></p>
                                
                                <div class="property-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><?php echo $property['location']; ?></span>
                                </div>
                                
                                <div class="property-features">
                                    <?php 
                                    $highlight_features = array_slice($property['amenities'], 0, 3);
                                    foreach ($highlight_features as $feature): ?>
                                        <span class="feature-tag <?php echo in_array(strtolower($feature), ['24/7 security', 'wide roads', 'underground drainage']) ? 'highlight' : ''; ?>">
                                            <?php echo $feature; ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                                
                                <div class="property-specs">
                                    <div class="spec-item">
                                        <i class="fas fa-ruler-combined"></i>
                                        <span><?php echo $property['size']; ?></span>
                                    </div>
                                    <div class="spec-item">
                                        <i class="fas fa-road"></i>
                                        <span><?php echo $property['roads']; ?></span>
                                    </div>
                                    <div class="spec-item">
                                        <i class="fas fa-shield-alt"></i>
                                        <span><?php echo $property['security'] ? '24/7 Security' : 'Basic Security'; ?></span>
                                    </div>
                                    <div class="spec-item">
                                        <i class="fas fa-certificate"></i>
                                        <span>RERA: <?php echo $property['rera']; ?></span>
                                    </div>
                                </div>
                                
                                <div class="property-price">
                                    <?php echo $property['price']; ?>
                                </div>
                                
                                <div class="property-actions">
                                    <a href="#" class="btn-property btn-primary-property" onclick="showPropertyDetails(<?php echo $property['id']; ?>)">
                                        <i class="fas fa-info-circle me-2"></i>Details
                                    </a>
                                    <a href="contact.php?property=<?php echo $property['id']; ?>" class="btn-property btn-outline-property">
                                        <i class="fas fa-phone me-2"></i>Inquire
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="stat-card">
                        <div class="stat-number">6+</div>
                        <div class="stat-label">Premium Projects</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="stat-card">
                        <div class="stat-number">100%</div>
                        <div class="stat-label">RERA Registered</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="stat-card">
                        <div class="stat-number">24/7</div>
                        <div class="stat-label">Security</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="400">
                    <div class="stat-card">
                        <div class="stat-number">100%</div>
                        <div class="stat-label">Customer Satisfaction</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include_once __DIR__ . '/includes/templates/footer.php'; ?>

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

        // Filter functionality
        const filterOptions = document.querySelectorAll('.filter-option');
        const propertyItems = document.querySelectorAll('.property-item');

        filterOptions.forEach(option => {
            option.addEventListener('click', function() {
                // Remove active class from siblings
                const siblings = this.parentElement.querySelectorAll('.filter-option');
                siblings.forEach(sibling => sibling.classList.remove('active'));
                
                // Add active class to clicked option
                this.classList.add('active');
                
                // Apply filters
                applyFilters();
            });
        });

        function applyFilters() {
            const activeTypeFilter = document.querySelector('[data-filter].active')?.dataset.filter || 'all';
            const activeFeatureFilter = document.querySelector('[data-feature].active')?.dataset.feature || 'all';
            const activeStatusFilter = document.querySelector('[data-status].active')?.dataset.status || 'all';

            propertyItems.forEach(item => {
                let show = true;

                // Type filter
                if (activeTypeFilter !== 'all' && item.dataset.type !== activeTypeFilter) {
                    show = false;
                }

                // Status filter
                if (activeStatusFilter !== 'all' && item.dataset.status !== activeStatusFilter) {
                    show = false;
                }

                // Feature filter
                if (activeFeatureFilter !== 'all' && !item.dataset.features.includes(activeFeatureFilter)) {
                    show = false;
                }

                item.style.display = show ? 'block' : 'none';
            });

            // Update visible count
            const visibleItems = Array.from(propertyItems).filter(item => item.style.display !== 'none');
            console.log(`Showing ${visibleItems.length} properties`);
        }

        // Property details function
        function showPropertyDetails(propertyId) {
            const properties = <?php echo json_encode($enhanced_properties); ?>;
            const property = properties.find(p => p.id === propertyId);
            
            if (property) {
                alert(`Property Details:\n\nName: ${property.name}\nType: ${property.type}\nLocation: ${property.location}\nSize: ${property.size}\nPrice: ${property.price}\nStatus: ${property.status}\nRERA: ${property.rera}\n\nAmenities: ${property.amenities.join(', ')}\n\nDescription: ${property.description}\n\nContact us for more details and site visit!`);
            }
        }

        // Add hover effects to property cards
        document.querySelectorAll('.property-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-15px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });

        // Animate stats on scroll
        const statsObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const statNumbers = entry.target.querySelectorAll('.stat-number');
                    statNumbers.forEach(stat => {
                        const finalValue = stat.textContent;
                        const numericValue = parseInt(finalValue);
                        
                        if (!isNaN(numericValue)) {
                            let currentValue = 0;
                            const increment = numericValue / 50;
                            const timer = setInterval(() => {
                                currentValue += increment;
                                if (currentValue >= numericValue) {
                                    currentValue = numericValue;
                                    clearInterval(timer);
                                }
                                stat.textContent = Math.round(currentValue) + (finalValue.includes('%') ? '%' : '+');
                            }, 30);
                        }
                    });
                    statsObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        // Observe stats section
        const statsSection = document.querySelector('.stats-section');
        if (statsSection) {
            statsObserver.observe(statsSection);
        }

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Track property interactions
        document.querySelectorAll('.btn-property').forEach(button => {
            button.addEventListener('click', function(e) {
                if (!this.getAttribute('href') || this.getAttribute('href') === '#') {
                    e.preventDefault();
                }
                
                const action = this.textContent.trim();
                const propertyName = this.closest('.property-card').querySelector('.property-title').textContent;
                console.log(`${action} clicked for: ${propertyName}`);
                
                // Add visual feedback
                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 200);
            });
        });

        // Initialize filters on page load
        applyFilters();
    </script>
</body>
</html>
