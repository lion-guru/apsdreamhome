<?php
// Enhanced Property Listings with Database Integration
require_once __DIR__ . '/init.php';

// ORM is already handled by config.php or App::database()

// Set page metadata
$page_title = 'Property Listings - APS Dream Homes';
$page_description = 'Browse premium properties and real estate listings in Gorakhpur';
$page_keywords = 'properties, real estate, Gorakhpur, APS Dream Homes, residential plots';

// Fetch properties from database
$properties = [];
try {
    $db = \App\Core\App::database();
    $properties = $db->fetchAll("SELECT * FROM properties WHERE status = 'available' ORDER BY featured DESC, created_at DESC");
} catch (Exception $e) {
    error_log("Property fetch error: " . $e->getMessage());
    // Fallback to sample data if database fails
    $properties = [
        [
            'id' => 1,
            'name' => 'APS Anant City',
            'type' => 'residential',
            'location' => 'Gorakhpur - NH-28',
            'size' => '1000-5000 Sq.Ft',
            'price' => '₹2,500/Sq.Ft',
            'description' => 'Premium residential plots with modern amenities',
            'amenities' => json_encode(['24/7 Security', 'Wide Roads', 'Underground Drainage', 'Central Park']),
            'image_url' => getPlaceholderUrl(400, 300, 'APS Anant City'),
            'status' => 'available',
            'featured' => 1,
            'rera_number' => 'UPRERAAGT12345'
        ]
    ];
}
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
        
        /* Search Section */
        .search-section {
            padding: 60px 0;
            background: white;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-top: -50px;
            position: relative;
            z-index: 10;
            border-radius: 20px 20px 0 0;
        }
        
        .search-card {
            background: #f8f9fa;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        
        .search-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            align-items: end;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-label {
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--dark-color);
        }
        
        .form-control, .form-select {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 15px 20px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.2);
            outline: none;
        }
        
        .btn-search {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 15px 35px;
            border-radius: 50px;
            border: none;
            font-weight: 700;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            height: fit-content;
        }
        
        .btn-search:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }
        
        /* Properties Section */
        .properties-section {
            padding: 100px 0;
            background: #f8f9fa;
        }
        
        .property-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .property-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
        }
        
        .property-image {
            height: 250px;
            position: relative;
            overflow: hidden;
        }
        
        .property-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .property-card:hover .property-image img {
            transform: scale(1.05);
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
        
        .property-badge.featured {
            background: var(--warning-color);
        }
        
        .property-badge.new {
            background: var(--info-color);
        }
        
        .property-favorite {
            position: absolute;
            top: 15px;
            right: 15px;
            background: white;
            color: var(--primary-color);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .property-favorite:hover {
            background: var(--primary-color);
            color: white;
            transform: scale(1.1);
        }
        
        .property-content {
            padding: 25px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        
        .property-price {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .property-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 15px;
            line-height: 1.3;
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
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .property-feature {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #666;
            font-size: 0.9rem;
        }
        
        .property-feature i {
            color: var(--primary-color);
        }
        
        .property-description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 25px;
            flex-grow: 1;
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
        
        /* Filter Sidebar */
        .filter-sidebar {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .filter-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 25px;
        }
        
        .filter-group {
            margin-bottom: 30px;
        }
        
        .filter-group h4 {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 15px;
        }
        
        .filter-option {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            cursor: pointer;
        }
        
        .filter-option input {
            margin-right: 10px;
        }
        
        .price-range {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .price-range input {
            flex: 1;
        }
        
        /* Stats Section */
        .stats-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            padding: 80px 0;
            color: white;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            color: #ffd700;
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
            
            .search-section {
                padding: 40px 20px;
            }
            
            .search-card {
                padding: 30px 20px;
            }
            
            .search-form {
                grid-template-columns: 1fr;
            }
            
            .property-actions {
                flex-direction: column;
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
                        <i class="fas fa-home me-2"></i>Property Listings
                    </div>
                    <h1 class="display-3 fw-bold mb-4">Find Your Dream Property</h1>
                    <p class="lead mb-4">
                        Discover premium properties for sale and investment in Gorakhpur. 
                        From residential homes to commercial spaces, find the perfect property for your needs.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Search Section -->
    <section class="search-section">
        <div class="container">
            <div class="search-card" data-aos="fade-up">
                <form class="search-form" onsubmit="searchProperties(event)">
                    <div class="form-group">
                        <label class="form-label">Property Type</label>
                        <select class="form-select">
                            <option value="">All Types</option>
                            <option value="residential">Residential</option>
                            <option value="commercial">Commercial</option>
                            <option value="investment">Investment</option>
                            <option value="plot">Land/Plot</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Location</label>
                        <select class="form-select">
                            <option value="">All Locations</option>
                            <option value="civil-lines">Civil Lines</option>
                            <option value="gol-ghar">Gol Ghar</option>
                            <option value="rajendra-nagar">Rajendra Nagar</option>
                            <option value="mahewa">Mahewa</option>
                            <option value="budh-vihar">Budh Vihar</option>
                            <option value="karim-nagar">Karim Nagar</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Price Range</label>
                        <select class="form-select">
                            <option value="">All Prices</option>
                            <option value="0-25">Under ₹25 Lakhs</option>
                            <option value="25-50">₹25-50 Lakhs</option>
                            <option value="50-75">₹50-75 Lakhs</option>
                            <option value="75-100">₹75 Lakhs - ₹1 Crore</option>
                            <option value="100+">Above ₹1 Crore</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Bedrooms</label>
                        <select class="form-select">
                            <option value="">Any</option>
                            <option value="1">1 BHK</option>
                            <option value="2">2 BHK</option>
                            <option value="3">3 BHK</option>
                            <option value="4">4 BHK</option>
                            <option value="5+">5+ BHK</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn-search">
                        <i class="fas fa-search me-2"></i>Search Properties
                    </button>
                </form>
            </div>
        </div>
    </section>

    <!-- Properties Section -->
    <section class="properties-section">
        <div class="container">
            <div class="row">
                <!-- Filter Sidebar -->
                <div class="col-lg-3">
                    <div class="filter-sidebar" data-aos="fade-right">
                        <h3 class="filter-title">
                            <i class="fas fa-filter me-2"></i>Filters
                        </h3>
                        
                        <div class="filter-group">
                            <h4>Property Type</h4>
                            <div class="filter-option">
                                <input type="checkbox" id="filter-residential">
                                <label for="filter-residential">Residential (45)</label>
                            </div>
                            <div class="filter-option">
                                <input type="checkbox" id="filter-commercial">
                                <label for="filter-commercial">Commercial (12)</label>
                            </div>
                            <div class="filter-option">
                                <input type="checkbox" id="filter-investment">
                                <label for="filter-investment">Investment (8)</label>
                            </div>
                            <div class="filter-option">
                                <input type="checkbox" id="filter-plot">
                                <label for="filter-plot">Land/Plot (15)</label>
                            </div>
                        </div>
                        
                        <div class="filter-group">
                            <h4>Price Range</h4>
                            <div class="price-range">
                                <input type="number" class="form-control" placeholder="Min" value="0">
                                <input type="number" class="form-control" placeholder="Max" value="50000000">
                            </div>
                        </div>
                        
                        <div class="filter-group">
                            <h4>Bedrooms</h4>
                            <div class="filter-option">
                                <input type="checkbox" id="filter-1bhk">
                                <label for="filter-1bhk">1 BHK</label>
                            </div>
                            <div class="filter-option">
                                <input type="checkbox" id="filter-2bhk">
                                <label for="filter-2bhk">2 BHK</label>
                            </div>
                            <div class="filter-option">
                                <input type="checkbox" id="filter-3bhk">
                                <label for="filter-3bhk">3 BHK</label>
                            </div>
                            <div class="filter-option">
                                <input type="checkbox" id="filter-4bhk">
                                <label for="filter-4bhk">4 BHK+</label>
                            </div>
                        </div>
                        
                        <div class="filter-group">
                            <h4>Features</h4>
                            <div class="filter-option">
                                <input type="checkbox" id="filter-parking">
                                <label for="filter-parking">Parking</label>
                            </div>
                            <div class="filter-option">
                                <input type="checkbox" id="filter-garden">
                                <label for="filter-garden">Garden</label>
                            </div>
                            <div class="filter-option">
                                <input type="checkbox" id="filter-security">
                                <label for="filter-security">Security</label>
                            </div>
                            <div class="filter-option">
                                <input type="checkbox" id="filter-gym">
                                <label for="filter-gym">Gym</label>
                            </div>
                        </div>
                        
                        <button class="btn btn-primary w-100">
                            <i class="fas fa-filter me-2"></i>Apply Filters
                        </button>
                    </div>
                </div>
                
                <!-- Properties Grid -->
                <div class="col-lg-9">
                    <div class="text-center mb-5" data-aos="fade-up">
                        <h2 class="display-4 fw-bold mb-3">Featured Properties</h2>
                        <p class="lead text-muted">Handpicked selection of premium properties in Gorakhpur</p>
                    </div>
                    
                    <div class="row">
                        <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                            <div class="property-card">
                                <div class="property-image">
                                    <img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" 
                                         alt="Luxury Villa">
                                    <span class="property-badge featured">Featured</span>
                                    <div class="property-favorite">
                                        <i class="far fa-heart"></i>
                                    </div>
                                </div>
                                <div class="property-content">
                                    <div class="property-price">₹85,00,000</div>
                                    <h3 class="property-title">Luxury Villa in Civil Lines</h3>
                                    <div class="property-location">
                                        <i class="fas fa-map-marker-alt"></i>
                                        Civil Lines, Gorakhpur
                                    </div>
                                    <div class="property-features">
                                        <div class="property-feature">
                                            <i class="fas fa-bed"></i>
                                            <span>4 BHK</span>
                                        </div>
                                        <div class="property-feature">
                                            <i class="fas fa-bath"></i>
                                            <span>3 Baths</span>
                                        </div>
                                        <div class="property-feature">
                                            <i class="fas fa-ruler-combined"></i>
                                            <span>2500 sqft</span>
                                        </div>
                                    </div>
                                    <p class="property-description">
                                        Premium luxury villa in prime location with modern amenities, garden area, and 24/7 security.
                                    </p>
                                    <div class="property-actions">
                                        <a href="#" class="btn-property btn-primary-property">
                                            <i class="fas fa-eye me-2"></i>View Details
                                        </a>
                                        <a href="#" class="btn-property btn-outline-property">
                                            <i class="fas fa-phone me-2"></i>Contact
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                            <div class="property-card">
                                <div class="property-image">
                                    <img src="https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" 
                                         alt="Modern Apartment">
                                    <span class="property-badge new">New</span>
                                    <div class="property-favorite">
                                        <i class="far fa-heart"></i>
                                    </div>
                                </div>
                                <div class="property-content">
                                    <div class="property-price">₹45,00,000</div>
                                    <h3 class="property-title">Modern Apartment in Gol Ghar</h3>
                                    <div class="property-location">
                                        <i class="fas fa-map-marker-alt"></i>
                                        Gol Ghar, Gorakhpur
                                    </div>
                                    <div class="property-features">
                                        <div class="property-feature">
                                            <i class="fas fa-bed"></i>
                                            <span>3 BHK</span>
                                        </div>
                                        <div class="property-feature">
                                            <i class="fas fa-bath"></i>
                                            <span>2 Baths</span>
                                        </div>
                                        <div class="property-feature">
                                            <i class="fas fa-ruler-combined"></i>
                                            <span>1800 sqft</span>
                                        </div>
                                    </div>
                                    <p class="property-description">
                                        Modern apartment with contemporary design, parking facility, and community amenities.
                                    </p>
                                    <div class="property-actions">
                                        <a href="#" class="btn-property btn-primary-property">
                                            <i class="fas fa-eye me-2"></i>View Details
                                        </a>
                                        <a href="#" class="btn-property btn-outline-property">
                                            <i class="fas fa-phone me-2"></i>Contact
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                            <div class="property-card">
                                <div class="property-image">
                                    <img src="https://images.unsplash.com/photo-1600566753376-12c8ac7fc5bd?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" 
                                         alt="Independent House">
                                    <span class="property-badge">For Sale</span>
                                    <div class="property-favorite">
                                        <i class="far fa-heart"></i>
                                    </div>
                                </div>
                                <div class="property-content">
                                    <div class="property-price">₹65,00,000</div>
                                    <h3 class="property-title">Independent House in Rajendra Nagar</h3>
                                    <div class="property-location">
                                        <i class="fas fa-map-marker-alt"></i>
                                        Rajendra Nagar, Gorakhpur
                                    </div>
                                    <div class="property-features">
                                        <div class="property-feature">
                                            <i class="fas fa-bed"></i>
                                            <span>3 BHK</span>
                                    </div>
                                </div>
                                
                                <div class="property-price">
                                    <?php echo h($property['price']); ?>
                                </div>
                                
                                <div class="property-actions">
                                    <button class="btn-detail" onclick="showPropertyDetails(<?php echo $property['id']; ?>)">
                                        <i class="fas fa-info-circle"></i> Details
                                    </button>
                                    <button class="btn-contact" onclick="contactProperty(<?php echo $property['id']; ?>)">
                                        <i class="fas fa-phone"></i> Contact
                                    </button>
                                </div>
                            </div>
                        </div>
                                    <div class="property-favorite">
                                        <i class="far fa-heart"></i>
                                    </div>
                                </div>
                                <div class="property-content">
                                    <div class="property-price">₹35,00,000</div>
                                    <h3 class="property-title">Commercial Space in Budh Vihar</h3>
                                    <div class="property-location">
                                        <i class="fas fa-map-marker-alt"></i>
                                        Budh Vihar, Gorakhpur
                                    </div>
                                    <div class="property-features">
                                        <div class="property-feature">
                                            <i class="fas fa-store"></i>
                                            <span>Shop</span>
                                        </div>
                                        <div class="property-feature">
                                            <i class="fas fa-ruler-combined"></i>
                                            <span>800 sqft</span>
                                        </div>
                                        <div class="property-feature">
                                            <i class="fas fa-parking"></i>
                                            <span>Parking</span>
                                        </div>
                                    </div>
                                    <p class="property-description">
                                        Prime commercial space on main road with high footfall and excellent business potential.
                                    </p>
                                    <div class="property-actions">
                                        <a href="#" class="btn-property btn-primary-property">
                                            <i class="fas fa-eye me-2"></i>View Details
                                        </a>
                                        <a href="#" class="btn-property btn-outline-property">
                                            <i class="fas fa-phone me-2"></i>Contact
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="600">
                            <div class="property-card">
                                <div class="property-image">
                                    <img src="https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" 
                                         alt="Investment Plot">
                                    <span class="property-badge">Investment</span>
                                    <div class="property-favorite">
                                        <i class="far fa-heart"></i>
                                    </div>
                                </div>
                                <div class="property-content">
                                    <div class="property-price">₹12,00,000</div>
                                    <h3 class="property-title">Investment Plot in Karim Nagar</h3>
                                    <div class="property-location">
                                        <i class="fas fa-map-marker-alt"></i>
                                        Karim Nagar, Gorakhpur
                                    </div>
                                    <div class="property-features">
                                        <div class="property-feature">
                                            <i class="fas fa-ruler-combined"></i>
                                            <span>1200 sqft</span>
                                        </div>
                                        <div class="property-feature">
                                            <i class="fas fa-chart-line"></i>
                                            <span>High ROI</span>
                                        </div>
                                        <div class="property-feature">
                                            <i class="fas fa-road"></i>
                                            <span>Road Front</span>
                                        </div>
                                    </div>
                                    <p class="property-description">
                                        Excellent investment plot with road frontage and high appreciation potential in developing area.
                                    </p>
                                    <div class="property-actions">
                                        <a href="#" class="btn-property btn-primary-property">
                                            <i class="fas fa-eye me-2"></i>View Details
                                        </a>
                                        <a href="#" class="btn-property btn-outline-property">
                                            <i class="fas fa-phone me-2"></i>Contact
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="text-center mt-5" data-aos="fade-up">
                        <nav>
                            <ul class="pagination justify-content-center">
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" tabindex="-1">Previous</a>
                                </li>
                                <li class="page-item active">
                                    <a class="page-link" href="#">1</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">2</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">3</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="stat-item">
                        <div class="stat-number">80+</div>
                        <div class="stat-label">Properties Available</div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="stat-item">
                        <div class="stat-number">15+</div>
                        <div class="stat-label">Prime Locations</div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="stat-item">
                        <div class="stat-number">98%</div>
                        <div class="stat-label">Customer Satisfaction</div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="stat-item">
                        <div class="stat-number">24/7</div>
                        <div class="stat-label">Support Available</div>
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

        // Search Properties
        function searchProperties(event) {
            event.preventDefault();
            
            // Get search criteria
            const formData = new FormData(event.target);
            const searchCriteria = Object.fromEntries(formData);
            
            // Show loading state
            alert('Searching properties based on your criteria...\n\n' + 
                  'Property Type: ' + (searchCriteria.propertyType || 'All') + '\n' +
                  'Location: ' + (searchCriteria.location || 'All') + '\n' +
                  'Price Range: ' + (searchCriteria.priceRange || 'All') + '\n' +
                  'Bedrooms: ' + (searchCriteria.bedrooms || 'Any'));
            
            // In a real application, this would filter the properties
            console.log('Search criteria:', searchCriteria);
        }

        // Favorite Properties
        document.querySelectorAll('.property-favorite').forEach(favorite => {
            favorite.addEventListener('click', function(e) {
                e.preventDefault();
                const icon = this.querySelector('i');
                
                if (icon.classList.contains('far')) {
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                    this.style.background = 'var(--primary-color)';
                    this.style.color = 'white';
                } else {
                    icon.classList.remove('fas');
                    icon.classList.add('far');
                    this.style.background = 'white';
                    this.style.color = 'var(--primary-color)';
                }
            });
        });

        // Filter Checkboxes
        document.querySelectorAll('.filter-option input').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                // In a real application, this would filter the properties
                console.log('Filter changed:', this.id, this.checked);
            });
        });

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

        // Property card hover effect enhancement
        document.querySelectorAll('.property-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-15px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
    </script>
</body>
</html>

