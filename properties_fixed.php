<?php
/**
 * Properties Page - APS Dream Homes Pvt Ltd
 * Professional property listings with database integration
 */

// Include database connection
require_once 'includes/db_connection.php';

try {
    $conn = getDbConnection();

    // Get all available properties
    $sql = "SELECT * FROM properties WHERE status = 'available' ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get property counts by type
    $type_sql = "SELECT type, COUNT(*) as count FROM properties WHERE status = 'available' GROUP BY type";
    $type_stmt = $conn->prepare($type_sql);
    $type_stmt->execute();
    $property_types = $type_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $properties = [];
    $property_types = [];
    $error_message = "Database connection issue. Please try again later.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Properties - APS Dream Homes Pvt Ltd</title>
    <meta name="description" content="Browse our premium property portfolio including apartments, villas, commercial spaces, and plots in Gorakhpur and surrounding areas.">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        .property-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 15px;
            overflow: hidden;
        }

        .property-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
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
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 8px 15px;
            border-radius: 25px;
            font-weight: bold;
            font-size: 1.1rem;
        }

        .property-type-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: rgba(255,255,255,0.9);
            color: #333;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            margin-bottom: 30px;
        }

        .filter-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
        }

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

        .search-box {
            max-width: 500px;
            margin: 0 auto;
        }

        .feature-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin: 0 auto 15px;
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
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
                    <a href="customer_login.php" class="btn btn-outline-light me-2">Login</a>
                    <a href="customer_registration.php" class="btn btn-primary">Register</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h1 class="section-title">🏠 Find Your Perfect Property</h1>
                    <p class="lead mb-4">Browse through our extensive collection of premium properties in Gorakhpur and surrounding areas</p>

                    <!-- Search Box -->
                    <div class="search-box">
                        <div class="input-group">
                            <input type="text" class="form-control form-control-lg" placeholder="Search properties by location, type, or price..." id="propertySearch">
                            <button class="btn btn-light btn-lg" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="stats-card">
                        <h2 class="mb-2"><?php echo count($properties); ?></h2>
                        <p class="mb-0">Total Properties</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <h2 class="mb-2"><?php echo count($property_types); ?></h2>
                        <p class="mb-0">Property Types</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <h2 class="mb-2">
                            <?php
                            $total_value = 0;
                            foreach ($properties as $property) {
                                $total_value += $property['price'];
                            }
                            echo '₹' . number_format($total_value / 10000000, 1) . 'Cr';
                            ?>
                        </h2>
                        <p class="mb-0">Portfolio Value</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <h2 class="mb-2">100%</h2>
                        <p class="mb-0">Satisfaction</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Properties Section -->
    <section class="py-5">
        <div class="container">
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <?php if (empty($properties)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-home fa-4x text-muted mb-4"></i>
                    <h3 class="text-muted">No Properties Available</h3>
                    <p class="text-muted">We're working on adding new properties. Please check back soon!</p>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($properties as $property): ?>
                        <div class="col-lg-4 col-md-6" data-aos="fade-up">
                            <div class="card property-card h-100">
                                <div class="position-relative">
                                    <img src="<?php echo htmlspecialchars($property['image_url']); ?>"
                                         class="card-img-top property-image"
                                         alt="<?php echo htmlspecialchars($property['title']); ?>"
                                         onerror="this.src='https://via.placeholder.com/400x250/e9ecef/6c757d?text=Property+Image'">
                                    <div class="property-type-badge">
                                        <?php echo ucfirst($property['type']); ?>
                                    </div>
                                    <div class="price-tag">
                                        ₹<?php echo number_format($property['price'] / 100000, 1); ?>L
                                    </div>
                                </div>

                                <div class="card-body">
                                    <h5 class="card-title fw-bold mb-2">
                                        <a href="#" class="text-decoration-none text-dark">
                                            <?php echo htmlspecialchars($property['title']); ?>
                                        </a>
                                    </h5>

                                    <p class="card-text text-muted mb-3">
                                        <?php echo htmlspecialchars(substr($property['description'], 0, 100)); ?>...
                                    </p>

                                    <div class="row mb-3">
                                        <div class="col-4">
                                            <small class="text-muted d-block">Bedrooms</small>
                                            <span class="fw-bold">
                                                <i class="fas fa-bed me-1"></i>
                                                <?php echo $property['bedrooms'] ?: 'N/A'; ?>
                                            </span>
                                        </div>
                                        <div class="col-4">
                                            <small class="text-muted d-block">Bathrooms</small>
                                            <span class="fw-bold">
                                                <i class="fas fa-bath me-1"></i>
                                                <?php echo $property['bathrooms'] ?: 'N/A'; ?>
                                            </span>
                                        </div>
                                        <div class="col-4">
                                            <small class="text-muted d-block">Area</small>
                                            <span class="fw-bold">
                                                <i class="fas fa-ruler-combined me-1"></i>
                                                <?php echo number_format($property['area_sqft']); ?> sq ft
                                            </span>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold text-primary fs-5">
                                            ₹<?php echo number_format($property['price'] / 100000, 1); ?>L
                                        </span>
                                        <button class="btn btn-primary">
                                            <i class="fas fa-eye me-1"></i>View Details
                                        </button>
                                    </div>
                                </div>

                                <div class="card-footer bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            <?php echo htmlspecialchars($property['location']); ?>
                                        </small>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            Recently Added
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row text-center">
                <div class="col-lg-4 mb-4" data-aos="fade-up">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h5>Verified Properties</h5>
                    <p class="text-muted">All properties are thoroughly verified and legally cleared</p>
                </div>

                <div class="col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h5>24/7 Support</h5>
                    <p class="text-muted">Round-the-clock customer support for all your queries</p>
                </div>

                <div class="col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h5>Best Deals</h5>
                    <p class="text-muted">Competitive pricing and exclusive deals for our clients</p>
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
                    <p>Your trusted partner in real estate solutions. We provide comprehensive property services with modern technology and personalized approach.</p>
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
                            123, Kunraghat Main Road, Near Railway Station<br>
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
                    <form class="mb-3">
                        <div class="input-group">
                            <input type="email" class="form-control" placeholder="Your Email" required>
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

        // Property search functionality
        document.getElementById('propertySearch').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const propertyCards = document.querySelectorAll('.property-card');

            propertyCards.forEach(card => {
                const title = card.querySelector('.card-title').textContent.toLowerCase();
                const description = card.querySelector('.card-text').textContent.toLowerCase();
                const location = card.querySelector('.card-footer small').textContent.toLowerCase();

                if (title.includes(searchTerm) || description.includes(searchTerm) || location.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = searchTerm === '' ? 'block' : 'none';
                }
            });
        });

        // Property card click handler
        document.querySelectorAll('.property-card .btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                // Add property detail view logic here
                alert('Property detail view will be implemented here');
            });
        });

        // Newsletter subscription
        document.querySelector('footer form').addEventListener('submit', function(e) {
            e.preventDefault();
            const email = this.querySelector('input[type="email"]').value;
            if (email) {
                alert('Thank you for subscribing to our newsletter!');
                this.reset();
            }
        });
    </script>
</body>
</html>
