<?php
/**
 * APS Dream Home - Main Index Page
 * Modern, responsive, and feature-rich homepage
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="APS Dream Home - Premium real estate properties in Gorakhpur, Lucknow, and Uttar Pradesh. Find your dream home with us.">
    <meta name="keywords" content="real estate, properties, gorakhpur, lucknow, uttar pradesh, dream home, buy property, sell property">
    <meta name="author" content="APS Dream Home">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="APS Dream Home - Find Your Dream Property">
    <meta property="og:description" content="Premium real estate properties in Gorakhpur, Lucknow, and Uttar Pradesh">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo BASE_URL; ?>">
    <meta property="og:image" content="<?php echo BASE_URL; ?>/public/assets/images/og-image.jpg">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="APS Dream Home - Find Your Dream Property">
    <meta name="twitter:description" content="Premium real estate properties in Gorakhpur, Lucknow, and Uttar Pradesh">
    <meta name="twitter:image" content="<?php echo BASE_URL; ?>/public/assets/images/og-image.jpg">
    
    <title>APS Dream Home - Premium Real Estate Properties</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo BASE_URL; ?>/public/assets/images/favicon.ico">
    <link rel="apple-touch-icon" href="<?php echo BASE_URL; ?>/public/assets/images/apple-touch-icon.png">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/responsive.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/animations.css">
    
    <!-- Preload Critical Resources -->
    <link rel="preload" href="<?php echo BASE_URL; ?>/public/assets/fonts/inter-var.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?php echo BASE_URL; ?>/public/assets/css/critical.css" as="style">
    
    <!-- Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "RealEstateAgent",
        "name": "APS Dream Home",
        "description": "Premium real estate properties in Gorakhpur, Lucknow, and Uttar Pradesh",
        "url": "<?php echo BASE_URL; ?>",
        "logo": "<?php echo BASE_URL; ?>/public/assets/images/logo.png",
        "image": "<?php echo BASE_URL; ?>/public/assets/images/og-image.jpg",
        "address": {
            "@type": "PostalAddress",
            "addressLocality": "Gorakhpur",
            "addressRegion": "Uttar Pradesh",
            "addressCountry": "India"
        },
        "contactPoint": {
            "@type": "ContactPoint",
            "telephone": "+91-XXXXXXXXXX",
            "contactType": "customer service"
        }
    }
    </script>
</head>
<body>
    <!-- Premium Header -->
    <header class="premium-header fixed-top">
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center" href="<?php echo BASE_URL; ?>">
                    <img src="<?php echo BASE_URL; ?>/public/assets/images/logo.png" alt="APS Dream Home" class="logo me-2">
                    <span class="brand-text">APS Dream Home</span>
                </a>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="#home">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#properties">Properties</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#about">About</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#services">Services</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#contact">Contact</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-primary text-white px-3" href="#contact">Get Started</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main>
        <!-- Hero Section -->
        <section id="home" class="hero-section">
            <div class="hero-overlay"></div>
            <div class="container">
                <div class="row align-items-center min-vh-100">
                    <div class="col-lg-6">
                        <div class="hero-content">
                            <h1 class="display-2 fw-bold text-white mb-4 animate-fade-in">
                                Find Your <span class="text-primary">Dream Home</span>
                            </h1>
                            <p class="lead text-white mb-4 animate-fade-in-delay">
                                Discover premium residential and commercial properties in Gorakhpur, Lucknow, and across Uttar Pradesh with APS Dream Home.
                            </p>
                            <div class="hero-buttons animate-fade-in-delay-2">
                                <a href="#properties" class="btn btn-primary btn-lg me-3 animate-bounce">
                                    <i class="fas fa-search me-2"></i>Explore Properties
                                </a>
                                <a href="#contact" class="btn btn-outline-light btn-lg animate-bounce-delay">
                                    <i class="fas fa-phone me-2"></i>Contact Us
                                </a>
                            </div>
                            
                            <!-- Trust Indicators -->
                            <div class="trust-indicators mt-4">
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
                    <div class="col-lg-6">
                        <div class="hero-image animate-slide-in-right">
                            <img src="<?php echo BASE_URL; ?>/public/assets/images/hero-property.jpg" alt="Dream Property" class="img-fluid rounded-3 shadow-lg">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Quick Search Section -->
        <section class="quick-search py-5 bg-light">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="search-card shadow-lg">
                            <h3 class="text-center mb-4">Quick Property Search</h3>
                            <form id="quickSearchForm" class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Property Type</label>
                                    <select class="form-select" name="property_type">
                                        <option value="">All Types</option>
                                        <option value="residential">Residential</option>
                                        <option value="commercial">Commercial</option>
                                        <option value="land">Land</option>
                                        <option value="villa">Villa</option>
                                        <option value="apartment">Apartment</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Location</label>
                                    <select class="form-select" name="location">
                                        <option value="">All Locations</option>
                                        <option value="gorakhpur">Gorakhpur</option>
                                        <option value="lucknow">Lucknow</option>
                                        <option value="kanpur">Kanpur</option>
                                        <option value="varanasi">Varanasi</option>
                                        <option value="allahabad">Allahabad</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Price Range</label>
                                    <select class="form-select" name="price_range">
                                        <option value="">Any Price</option>
                                        <option value="0-1000000">Under ₹10L</option>
                                        <option value="1000000-5000000">₹10L - ₹50L</option>
                                        <option value="5000000-10000000">₹50L - ₹1Cr</option>
                                        <option value="10000000-50000000">₹1Cr - ₹5Cr</option>
                                        <option value="50000000+">Above ₹5Cr</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Bedrooms</label>
                                    <select class="form-select" name="bedrooms">
                                        <option value="">Any</option>
                                        <option value="1">1 BHK</option>
                                        <option value="2">2 BHK</option>
                                        <option value="3">3 BHK</option>
                                        <option value="4">4 BHK</option>
                                        <option value="5+">5+ BHK</option>
                                    </select>
                                </div>
                                <div class="col-12 text-center mt-3">
                                    <button type="submit" class="btn btn-primary btn-lg px-5">
                                        <i class="fas fa-search me-2"></i>Search Properties
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Featured Properties Section -->
        <section id="properties" class="featured-properties py-5">
            <div class="container">
                <div class="row mb-5">
                    <div class="col-12 text-center">
                        <h2 class="display-5 fw-bold mb-4">Featured Properties</h2>
                        <p class="lead text-muted">Handpicked premium properties for your consideration</p>
                    </div>
                </div>
                
                <div class="row" id="featuredProperties">
                    <!-- Properties will be loaded here via AJAX -->
                    <div class="col-12 text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-12 text-center">
                        <a href="#" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-th me-2"></i>View All Properties
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- About Section -->
        <section id="about" class="about-section py-5 bg-light">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <div class="about-content">
                            <h2 class="display-5 fw-bold mb-4">About APS Dream Home</h2>
                            <p class="lead mb-4">
                                With over 8 years of excellence in real estate, APS Dream Home has been helping families and businesses find their perfect properties across Gorakhpur, Lucknow, and Uttar Pradesh.
                            </p>
                            <div class="about-features">
                                <div class="feature-item mb-3">
                                    <i class="fas fa-check-circle text-success me-3"></i>
                                    <span><strong>8+ Years of Experience</strong> in real estate</span>
                                </div>
                                <div class="feature-item mb-3">
                                    <i class="fas fa-check-circle text-success me-3"></i>
                                    <span><strong>500+ Properties Sold</strong> with satisfied clients</span>
                                </div>
                                <div class="feature-item mb-3">
                                    <i class="fas fa-check-circle text-success me-3"></i>
                                    <span><strong>Premium Locations</strong> across Uttar Pradesh</span>
                                </div>
                                <div class="feature-item mb-3">
                                    <i class="fas fa-check-circle text-success me-3"></i>
                                    <span><strong>Transparent Pricing</strong> with no hidden charges</span>
                                </div>
                                <div class="feature-item mb-3">
                                    <i class="fas fa-check-circle text-success me-3"></i>
                                    <span><strong>Expert Guidance</strong> throughout the process</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="about-image">
                            <img src="<?php echo BASE_URL; ?>/public/assets/images/about-us.jpg" alt="About APS Dream Home" class="img-fluid rounded-3 shadow-lg">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Services Section -->
        <section id="services" class="services-section py-5">
            <div class="container">
                <div class="row mb-5">
                    <div class="col-12 text-center mb-5">
                        <h2 class="display-5 fw-bold">Our Services</h2>
                        <p class="lead text-muted">Comprehensive real estate solutions</p>
                    </div>
                </div>
                <div class="row g-4">
                    <div class="col-md-4 text-center">
                        <div class="service-card p-4 h-100">
                            <div class="service-icon mb-3">
                                <i class="fas fa-home fa-3x text-primary"></i>
                            </div>
                            <h4 class="mb-3">Property Sales</h4>
                            <p class="text-muted">Buy premium residential and commercial properties with expert guidance and transparent pricing.</p>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="service-card p-4 h-100">
                            <div class="service-icon mb-3">
                                <i class="fas fa-building fa-3x text-primary"></i>
                            </div>
                            <h4 class="mb-3">Property Development</h4>
                            <p class="text-muted">Expert construction and development services for residential and commercial projects.</p>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="service-card p-4 h-100">
                            <div class="service-icon mb-3">
                                <i class="fas fa-handshake fa-3x text-primary"></i>
                            </div>
                            <h4 class="mb-3">Consultation</h4>
                            <p class="text-muted">Professional real estate consultation and investment advisory services.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Statistics Section -->
        <section class="statistics-section py-5 bg-primary text-white">
            <div class="container">
                <div class="row text-center">
                    <div class="col-md-3 mb-4">
                        <div class="stat-item">
                            <div class="stat-number animate-counter" data-target="500">0</div>
                            <div class="stat-label">Properties Sold</div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="stat-item">
                            <div class="stat-number animate-counter" data-target="1000">0</div>
                            <div class="stat-label">Happy Clients</div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="stat-item">
                            <div class="stat-number animate-counter" data-target="8">0</div>
                            <div class="stat-label">Years Experience</div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="stat-item">
                            <div class="stat-number animate-counter" data-target="15">0</div>
                            <div class="stat-label">Cities Covered</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section id="contact" class="contact-section py-5 bg-light">
            <div class="container">
                <div class="row mb-5">
                    <div class="col-12 text-center mb-5">
                        <h2 class="display-5 fw-bold">Get In Touch</h2>
                        <p class="lead text-muted">Ready to find your dream property? Contact us today.</p>
                    </div>
                </div>
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="contact-form-card shadow-lg">
                            <div class="card-body p-5">
                                <form id="contactForm" class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Your Name *</label>
                                        <input type="text" class="form-control" name="name" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Your Email *</label>
                                        <input type="email" class="form-control" name="email" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Phone Number *</label>
                                        <input type="tel" class="form-control" name="phone" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Service Type *</label>
                                        <select class="form-select" name="service" required>
                                            <option value="">Select Service</option>
                                            <option value="buy">Buy Property</option>
                                            <option value="sell">Sell Property</option>
                                            <option value="rent">Rent Property</option>
                                            <option value="consultation">Consultation</option>
                                            <option value="investment">Investment</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Your Message *</label>
                                        <textarea class="form-control" name="message" rows="4" required></textarea>
                                    </div>
                                    <div class="col-12 text-center">
                                        <button type="submit" class="btn btn-primary btn-lg px-5">
                                            <i class="fas fa-paper-plane me-2"></i>Send Message
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Premium Footer -->
    <footer class="premium-footer bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="footer-about">
                        <img src="<?php echo BASE_URL; ?>/public/assets/images/logo-white.png" alt="APS Dream Home" class="footer-logo mb-3">
                        <p class="footer-description">
                            APS Dream Home is your trusted partner in finding premium real estate properties across Uttar Pradesh with 8+ years of excellence.
                        </p>
                        <div class="social-links">
                            <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-whatsapp"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="footer-links">
                        <h5 class="footer-title">Quick Links</h5>
                        <ul class="footer-nav">
                            <li><a href="#home">Home</a></li>
                            <li><a href="#properties">Properties</a></li>
                            <li><a href="#about">About Us</a></li>
                            <li><a href="#services">Services</a></li>
                            <li><a href="#contact">Contact</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="footer-contact">
                        <h5 class="footer-title">Contact Info</h5>
                        <div class="contact-info">
                            <div class="contact-item">
                                <i class="fas fa-map-marker-alt me-3"></i>
                                <span>Gorakhpur, Uttar Pradesh, India</span>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-phone me-3"></i>
                                <span>+91-XXXXXXXXXX</span>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-envelope me-3"></i>
                                <span>info@apsdreamhome.com</span>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-clock me-3"></i>
                                <span>Mon-Sat: 9:00 AM - 7:00 PM</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-bottom mt-4 pt-4 border-top border-secondary">
                <div class="row">
                    <div class="col-12 text-center">
                        <p class="mb-0">&copy; 2026 APS Dream Home. All rights reserved.</p>
                        <div class="footer-links-bottom mt-2">
                            <a href="#" class="text-white me-2">Privacy Policy</a>
                            <a href="#" class="text-white me-2">Terms of Service</a>
                            <a href="#" class="text-white">Sitemap</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button id="backToTop" class="back-to-top">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- WhatsApp Floating Button -->
    <div class="whatsapp-float">
        <a href="https://wa.me/919XXXXXXXXXX" target="_blank" class="whatsapp-link">
            <i class="fab fa-whatsapp"></i>
        </a>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="<?php echo BASE_URL; ?>/public/assets/js/layout.js"></script>
    <script src="<?php echo BASE_URL; ?>/public/assets/js/premium-header.js"></script>
    <script src="<?php echo BASE_URL; ?>/public/assets/js/lead-capture.js"></script>
    <script src="<?php echo BASE_URL; ?>/public/assets/js/property-search.js"></script>
    <script src="<?php echo BASE_URL; ?>/public/assets/js/animations.js"></script>
    <script src="<?php echo BASE_URL; ?>/public/assets/js/contact-form.js"></script>
    
    <!-- Initialize App -->
    <script>
        // Initialize application
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize animations
            initAnimations();
            
            // Initialize property search
            initPropertySearch();
            
            // Initialize contact form
            initContactForm();
            
            // Initialize premium header
            initPremiumHeader();
            
            // Initialize back to top
            initBackToTop();
            
            // Load featured properties
            loadFeaturedProperties();
        });
        
        // Load featured properties function
        function loadFeaturedProperties() {
            fetch('<?php echo BASE_URL; ?>/api/properties/featured')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('featuredProperties');
                    if (data.properties && data.properties.length > 0) {
                        container.innerHTML = data.properties.map(property => `
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card property-card h-100">
                                    ${property.featured ? '<div class="featured-badge">Featured</div>' : ''}
                                    <img src="${property.image}" class="card-img-top" alt="${property.title}">
                                    <div class="card-body">
                                        <h5 class="card-title">${property.title}</h5>
                                        <p class="text-muted">${property.location}</p>
                                        <p class="text-primary fw-bold">₹${formatPrice(property.price)}</p>
                                        <p class="small">${property.bedrooms} BHK • ${property.area} sq.ft.</p>
                                        <p>${property.description}</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="badge bg-${property.status === 'ready-to-move' ? 'success' : 'warning'}">
                                                ${property.status}
                                            </span>
                                            <a href="#" class="btn btn-primary btn-sm">View Details</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `).join('');
                    } else {
                        container.innerHTML = '<div class="col-12 text-center"><p>No featured properties available at the moment.</p></div>';
                    }
                })
                .catch(error => {
                    console.error('Error loading featured properties:', error);
                    document.getElementById('featuredProperties').innerHTML = '<div class="col-12 text-center"><p>Error loading properties. Please try again later.</p></div>';
                });
        }
        
        // Format price function
        function formatPrice(price) {
            return new Intl.NumberFormat('en-IN').format(price);
        }
    </script>
</body>
</html>
