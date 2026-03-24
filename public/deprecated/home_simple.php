<?php
/**
 * APS Dream Home - Simple Homepage
 * Clean, working homepage without dependencies
 */

// Define constants
define('APS_ROOT', dirname(__DIR__));
define('APS_APP', APS_ROOT . '/app');
define('APP_PATH', APS_APP);
define('APS_PUBLIC', __DIR__);
define('APS_CONFIG', APS_ROOT . '/config');
define('APS_STORAGE', APS_ROOT . '/storage');
define('APS_LOGS', APS_ROOT . '/logs');
define('BASE_URL', 'http://localhost/apsdreamhome/public');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APS Dream Home - Premium Real Estate in Gorakhpur</title>
    <meta name="description" content="Discover premium residential and commercial properties in Gorakhpur, Lucknow, and across Uttar Pradesh with APS Dream Home.">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --accent-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
            --white-color: #ffffff;
            --text-color: #333333;
            --text-muted: #6c757d;
            --border-color: #dee2e6;
            --shadow-color: rgba(0, 0, 0, 0.1);
            --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-secondary: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --border-radius: 8px;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background-color: var(--white-color);
            overflow-x: hidden;
        }

        /* Hero Section */
        .hero-section {
            position: relative;
            min-height: 100vh;
            background: linear-gradient(135deg, rgba(44, 62, 80, 0.9), rgba(52, 73, 94, 0.9)),
                        url('https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') center/cover;
            display: flex;
            align-items: center;
            color: white;
        }

        .hero-content h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .hero-content .lead {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .trust-indicators {
            margin-top: 3rem;
        }

        .trust-item {
            text-align: center;
            padding: 1rem;
        }

        .trust-item i {
            color: var(--accent-color);
            margin-bottom: 0.5rem;
        }

        .trust-item h6 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        /* Navigation */
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color) !important;
        }

        .navbar-nav .nav-link {
            font-weight: 500;
            color: var(--text-color) !important;
            margin: 0 0.5rem;
            transition: var(--transition);
        }

        .navbar-nav .nav-link:hover {
            color: var(--accent-color) !important;
        }

        /* Buttons */
        .btn-primary {
            background: var(--gradient-primary);
            border: none;
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: var(--border-radius);
            transition: var(--transition);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .btn-outline-light {
            border: 2px solid rgba(255, 255, 255, 0.8);
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: var(--border-radius);
            transition: var(--transition);
        }

        .btn-outline-light:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: white;
        }

        /* Sections */
        .section-padding {
            padding: 5rem 0;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .section-subtitle {
            font-size: 1.125rem;
            color: var(--text-muted);
            margin-bottom: 3rem;
        }

        /* Search Section */
        .search-section {
            background: var(--light-color);
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .form-control, .form-select {
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            padding: 0.75rem 1rem;
            font-weight: 500;
            transition: var(--transition);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        /* Feature Cards */
        .feature-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: var(--transition);
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: white;
        }

        /* Stats Section */
        .stats-section {
            background: var(--gradient-primary);
            color: white;
            padding: 4rem 0;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1.125rem;
            opacity: 0.9;
        }

        /* Contact Section */
        .contact-section {
            background: var(--light-color);
        }

        /* Footer */
        footer {
            background: var(--dark-color);
            color: white;
            padding: 3rem 0 1rem;
        }

        footer a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: var(--transition);
        }

        footer a:hover {
            color: white;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2.5rem;
            }
            
            .section-padding {
                padding: 3rem 0;
            }
            
            .trust-indicators .col-4 {
                margin-bottom: 1rem;
            }
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

        .animate-fade-in {
            animation: fadeInUp 0.8s ease-out;
        }

        .animate-fade-in-delay {
            animation: fadeInUp 0.8s ease-out 0.2s both;
        }

        .animate-fade-in-delay-2 {
            animation: fadeInUp 0.8s ease-out 0.4s both;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <i class="fas fa-home me-2 text-primary"></i>
                APS Dream Home
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#properties">Properties</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-section">
        <div class="container">
            <div class="row align-items-center min-vh-100">
                <div class="col-lg-6">
                    <div class="hero-content animate-fade-in">
                        <h1>Find Your <span class="text-warning">Dream Home</span></h1>
                        <p class="lead animate-fade-in-delay">
                            Discover premium residential and commercial properties in Gorakhpur, Lucknow, and across Uttar Pradesh with APS Dream Home.
                        </p>
                        <div class="hero-buttons animate-fade-in-delay-2">
                            <a href="#properties" class="btn btn-primary btn-lg me-3">
                                <i class="fas fa-search me-2"></i>Explore Properties
                            </a>
                            <a href="#contact" class="btn btn-outline-light btn-lg">
                                <i class="fas fa-phone me-2"></i>Contact Us
                            </a>
                        </div>
                        
                        <!-- Trust Indicators -->
                        <div class="trust-indicators">
                            <div class="row text-white">
                                <div class="col-4 text-center">
                                    <div class="trust-item">
                                        <i class="fas fa-award fa-2x mb-2"></i>
                                        <h6>8+ Years</h6>
                                        <small>Experience</small>
                                    </div>
                                </div>
                                <div class="col-4 text-center">
                                    <div class="trust-item">
                                        <i class="fas fa-home fa-2x mb-2"></i>
                                        <h6>500+</h6>
                                        <small>Properties Sold</small>
                                    </div>
                                </div>
                                <div class="col-4 text-center">
                                    <div class="trust-item">
                                        <i class="fas fa-smile fa-2x mb-2"></i>
                                        <h6>1000+</h6>
                                        <small>Happy Clients</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Search Section -->
    <section id="search" class="section-padding">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Quick Property Search</h2>
                <p class="section-subtitle">Find your perfect property with our advanced search</p>
            </div>
            
            <div class="search-section">
                <form>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Property Type</label>
                            <select class="form-select">
                                <option>All Types</option>
                                <option>Residential</option>
                                <option>Commercial</option>
                                <option>Land</option>
                                <option>Villa</option>
                                <option>Apartment</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Location</label>
                            <select class="form-select">
                                <option>All Locations</option>
                                <option>Gorakhpur</option>
                                <option>Lucknow</option>
                                <option>Kanpur</option>
                                <option>Varanasi</option>
                                <option>Allahabad</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Price Range</label>
                            <select class="form-select">
                                <option>Any Price</option>
                                <option>Under ₹10L</option>
                                <option>₹10L - ₹50L</option>
                                <option>₹50L - ₹1Cr</option>
                                <option>₹1Cr - ₹5Cr</option>
                                <option>Above ₹5Cr</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Bedrooms</label>
                            <select class="form-select">
                                <option>Any</option>
                                <option>1 BHK</option>
                                <option>2 BHK</option>
                                <option>3 BHK</option>
                                <option>4 BHK</option>
                                <option>5+ BHK</option>
                            </select>
                        </div>
                        <div class="col-12 text-center">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-search me-2"></i>Search Properties
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Featured Properties -->
    <section id="properties" class="section-padding">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Featured Properties</h2>
                <p class="section-subtitle">Handpicked premium properties for your consideration</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card h-100 shadow-sm">
                        <img src="https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" class="card-img-top" alt="Luxury Apartment">
                        <div class="card-body">
                            <h5 class="card-title">Luxury Apartment in Gomti Nagar</h5>
                            <p class="text-muted mb-2"><i class="fas fa-map-marker-alt me-2"></i>Gomti Nagar, Lucknow</p>
                            <h4 class="text-primary mb-2">₹7,500,000</h4>
                            <p class="text-muted mb-3">3 BHK • 1500 Sq.ft</p>
                            <p class="card-text">Spacious 3BHK luxury apartment with modern amenities and prime location in Gomti Nagar.</p>
                            <span class="badge bg-success me-2">Ready-to-move</span>
                            <a href="#" class="btn btn-outline-primary btn-sm float-end">View Details</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100 shadow-sm">
                        <img src="https://images.unsplash.com/photo-1570129477492-45c003edd2be?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" class="card-img-top" alt="Modern Villa">
                        <div class="card-body">
                            <h5 class="card-title">Modern Villa in Hazratganj</h5>
                            <p class="text-muted mb-2"><i class="fas fa-map-marker-alt me-2"></i>Hazratganj, Lucknow</p>
                            <h4 class="text-primary mb-2">₹12,000,000</h4>
                            <p class="text-muted mb-3">4 BHK • 2000 Sq.ft</p>
                            <p class="card-text">Elegant 4BHK villa with private garden and premium finishing in heart of Hazratganj.</p>
                            <span class="badge bg-success me-2">Ready-to-move</span>
                            <a href="#" class="btn btn-outline-primary btn-sm float-end">View Details</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <a href="#" class="btn btn-primary">
                    <i class="fas fa-th me-2"></i>View All Properties
                </a>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row">
                <div class="col-md-3 col-6">
                    <div class="stat-item">
                        <div class="stat-number" data-target="500">0</div>
                        <div class="stat-label">Properties Sold</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-item">
                        <div class="stat-number" data-target="25">0</div>
                        <div class="stat-label">Featured Properties</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-item">
                        <div class="stat-number" data-target="10">0</div>
                        <div class="stat-label">New Listings</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-item">
                        <div class="stat-number" data-target="4500">0</div>
                        <div class="stat-label">Avg Price/sq.ft</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="section-padding">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2 class="section-title">About APS Dream Home</h2>
                    <p class="section-subtitle">With over 8 years of excellence in real estate, APS Dream Home has been helping families and businesses find their perfect properties across Gorakhpur, Lucknow, and Uttar Pradesh.</p>
                    
                    <div class="row g-4 mb-4">
                        <div class="col-6">
                            <div class="d-flex">
                                <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                                <div>
                                    <h6>8+ Years of Experience</h6>
                                    <small class="text-muted">in real estate</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex">
                                <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                                <div>
                                    <h6>500+ Properties Sold</h6>
                                    <small class="text-muted">with satisfied clients</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex">
                                <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                                <div>
                                    <h6>Premium Locations</h6>
                                    <small class="text-muted">across Uttar Pradesh</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex">
                                <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                                <div>
                                    <h6>Transparent Pricing</h6>
                                    <small class="text-muted">with no hidden charges</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <img src="https://images.unsplash.com/photo-1560518883-cea944ad6494?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" class="img-fluid rounded shadow" alt="About APS Dream Home">
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="section-padding bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Our Services</h2>
                <p class="section-subtitle">Comprehensive real estate solutions</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-home"></i>
                        </div>
                        <h4>Property Sales</h4>
                        <p>Buy premium residential and commercial properties with expert guidance and transparent pricing.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <h4>Property Development</h4>
                        <p>Expert construction and development services for residential and commercial projects.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h4>Consultation</h4>
                        <p>Professional real estate consultation and investment advisory services.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="section-padding contact-section">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Get In Touch</h2>
                <p class="section-subtitle">Ready to find your dream property? Contact us today.</p>
            </div>
            
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="card shadow">
                        <div class="card-body p-4">
                            <form>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Your Name *</label>
                                        <input type="text" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Your Email *</label>
                                        <input type="email" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Phone Number *</label>
                                        <input type="tel" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Service Type *</label>
                                        <select class="form-select" required>
                                            <option value="">Select Service</option>
                                            <option>Buy Property</option>
                                            <option>Sell Property</option>
                                            <option>Rent Property</option>
                                            <option>Consultation</option>
                                            <option>Investment</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Your Message *</label>
                                        <textarea class="form-control" rows="5" required></textarea>
                                    </div>
                                    <div class="col-12 text-center">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-paper-plane me-2"></i>Send Message
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5 class="mb-3">APS Dream Home</h5>
                    <p>Your trusted real estate partner in Uttar Pradesh, helping you find your dream property with transparency and expertise.</p>
                </div>
                <div class="col-md-4">
                    <h5 class="mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#home">Home</a></li>
                        <li class="mb-2"><a href="#properties">Properties</a></li>
                        <li class="mb-2"><a href="#about">About Us</a></li>
                        <li class="mb-2"><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5 class="mb-3">Contact Info</h5>
                    <p><i class="fas fa-phone me-2"></i>+91 98765 43210</p>
                    <p><i class="fas fa-envelope me-2"></i>info@apsdreamhome.com</p>
                    <p><i class="fas fa-map-marker-alt me-2"></i>Gorakhpur, Uttar Pradesh</p>
                </div>
            </div>
            <hr class="my-4 bg-white">
            <div class="text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> APS Dream Home. All rights reserved.</p>
                <div class="mt-2">
                    <a href="#" class="me-3">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        // Smooth scrolling for navigation links
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

        // Animated counter for stats
        const counters = document.querySelectorAll('.stat-number');
        const speed = 200;

        counters.forEach(counter => {
            const animate = () => {
                const value = +counter.getAttribute('data-target');
                const data = +counter.innerText;
                const time = value / speed;
                
                if (data < value) {
                    counter.innerText = Math.ceil(data + time);
                    setTimeout(animate, 1);
                } else {
                    counter.innerText = value;
                }
            }
            
            // Start animation when element is in viewport
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        animate();
                        observer.unobserve(entry.target);
                    }
                });
            });
            
            observer.observe(counter);
        });

        // Form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Thank you for your message! We will get back to you soon.');
            this.reset();
        });
    </script>
</body>
</html>
