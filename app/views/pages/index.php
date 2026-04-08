<?php
// Homepage content (used within layouts/base.php)
?>
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
                        <?php 
                        $heroStats = $hero_stats ?? [
                            'properties_sold' => '500+',
                            'happy_clients' => '1000+',
                            'years_experience' => '15+',
                            'projects_completed' => '50+'
                        ];
                        ?>
                        <div class="trust-indicators mt-4">
                            <div class="row text-white">
                                <div class="col-4 text-center">
                                    <div class="trust-item">
                                        <i class="fas fa-award fa-2x mb-2"></i>
                                        <h6><?php echo $heroStats['years_experience']; ?></h6>
                                        <small>Experience</small>
                                    </div>
                                </div>
                                <div class="col-4 text-center">
                                    <div class="trust-item">
                                        <i class="fas fa-home fa-2x mb-2"></i>
                                        <h6><?php echo $heroStats['properties_sold']; ?></h6>
                                        <small>Properties Sold</small>
                                    </div>
                                </div>
                                <div class="col-4 text-center">
                                    <div class="trust-item">
                                        <i class="fas fa-smile fa-2x mb-2"></i>
                                        <h6><?php echo $heroStats['happy_clients']; ?></h6>
                                        <small>Happy Clients</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-image animate-slide-in-right">
                        <img src="<?php echo BASE_URL; ?>/assets/images/hero/luxury-home-1.jpg" alt="Dream Property" class="img-fluid rounded-3 shadow-lg">
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

    <!-- Featured Projects Section -->
    <section id="projects" class="featured-projects py-5 bg-light">
        <div class="container">
            <div class="row mb-5">
                <div class="col-12 text-center">
                    <h2 class="display-5 fw-bold mb-4">Our Projects</h2>
                    <p class="lead text-muted">Premium residential colonies across Uttar Pradesh</p>
                </div>
            </div>

            <div class="row" id="featuredProjects">
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card project-card h-100">
                        <img src="<?php echo BASE_URL; ?>/assets/images/projects/gorakhpur/suryoday.jpg" class="card-img-top" alt="Suyoday Colony">
                        <div class="card-body">
                            <h5 class="card-title">Suyoday Colony</h5>
                            <p class="text-muted"><i class="fas fa-map-marker-alt me-1"></i>Gorakhpur</p>
                            <p class="text-primary fw-bold">Starting ₹7.5 Lakhs</p>
                            <p class="small">Premium residential plots with modern infrastructure</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-success">Ongoing</span>
                                <a href="<?php echo BASE_URL; ?>/projects/suyoday-colony" class="btn btn-primary btn-sm">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card project-card h-100">
                        <img src="<?php echo BASE_URL; ?>/assets/images/projects/gorakhpur/raghunath nagri motiram.JPG" class="card-img-top" alt="Raghunat Nagri">
                        <div class="card-body">
                            <h5 class="card-title">Raghunat Nagri</h5>
                            <p class="text-muted"><i class="fas fa-map-marker-alt me-1"></i>Gorakhpur</p>
                            <p class="text-primary fw-bold">Starting ₹8.5 Lakhs</p>
                            <p class="small">Premium residential plots in developing area</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-success">Ongoing</span>
                                <a href="<?php echo BASE_URL; ?>/projects/raghunat-nagri" class="btn btn-primary btn-sm">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card project-card h-100">
                        <img src="<?php echo BASE_URL; ?>/assets/images/projects/gorakhpur/suryoday1.jpeg" class="card-img-top" alt="Braj Radha Nagri">
                        <div class="card-body">
                            <h5 class="card-title">Braj Radha Nagri</h5>
                            <p class="text-muted"><i class="fas fa-map-marker-alt me-1"></i>Gorakhpur</p>
                            <p class="text-primary fw-bold">Starting ₹6.5 Lakhs</p>
                            <p class="small">Affordable residential plots with amenities</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-warning">Planned</span>
                                <a href="<?php echo BASE_URL; ?>/projects/braj-radha-nagri" class="btn btn-primary btn-sm">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card project-card h-100">
                        <img src="<?php echo BASE_URL; ?>/assets/images/placeholder/property.svg" class="card-img-top" alt="Budh Bihar Colony">
                        <div class="card-body">
                            <h5 class="card-title">Budh Bihar Colony</h5>
                            <p class="text-muted"><i class="fas fa-map-marker-alt me-1"></i>Kushinagar</p>
                            <p class="text-primary fw-bold">Starting ₹5.5 Lakhs</p>
                            <p class="small">Integrated township at Premwaliya</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-success">Ongoing</span>
                                <a href="<?php echo BASE_URL; ?>/projects/budh-bihar-colony" class="btn btn-primary btn-sm">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card project-card h-100">
                        <img src="<?php echo BASE_URL; ?>/assets/images/placeholder/property.svg" class="card-img-top" alt="Awadhpuri">
                        <div class="card-body">
                            <h5 class="card-title">Awadhpuri</h5>
                            <p class="text-muted"><i class="fas fa-map-marker-alt me-1"></i>Lucknow</p>
                            <p class="text-primary fw-bold">Starting ₹12 Lakhs</p>
                            <p class="small">20 bigha premium project at Safadarganj</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-info">Coming Soon</span>
                                <a href="<?php echo BASE_URL; ?>/projects/awadhpuri" class="btn btn-primary btn-sm">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12 text-center">
                    <a href="<?php echo BASE_URL; ?>/company/projects" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-building me-2"></i>View All Projects
                    </a>
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
                <!-- Static Featured Properties -->
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card property-card h-100 shadow-sm">
                        <img src="<?php echo BASE_URL; ?>/assets/images/projects/gorakhpur/suryoday.jpg" class="card-img-top" alt="Suyoday Colony">
                        <div class="card-body">
                            <h5 class="card-title">Suyoday Colony</h5>
                            <p class="text-muted"><i class="fas fa-map-marker-alt me-1"></i>Gorakhpur</p>
                            <p class="text-primary fw-bold">₹7.5 Lakhs</p>
                            <p class="small">Premium residential plots with modern infrastructure</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-success">Available</span>
                                <a href="<?php echo BASE_URL; ?>/projects/suyoday-colony" class="btn btn-primary btn-sm">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card property-card h-100 shadow-sm">
                        <img src="<?php echo BASE_URL; ?>/assets/images/projects/gorakhpur/raghunath nagri motiram.JPG" class="card-img-top" alt="Raghunat Nagri">
                        <div class="card-body">
                            <h5 class="card-title">Raghunat Nagri</h5>
                            <p class="text-muted"><i class="fas fa-map-marker-alt me-1"></i>Gorakhpur</p>
                            <p class="text-primary fw-bold">₹8.5 Lakhs</p>
                            <p class="small">Premium residential plots in developing area</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-success">Available</span>
                                <a href="<?php echo BASE_URL; ?>/projects/raghunat-nagri" class="btn btn-primary btn-sm">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card property-card h-100 shadow-sm">
                        <img src="<?php echo BASE_URL; ?>/assets/images/projects/gorakhpur/suryoday1.jpeg" class="card-img-top" alt="Braj Radha Nagri">
                        <div class="card-body">
                            <h5 class="card-title">Braj Radha Nagri</h5>
                            <p class="text-muted"><i class="fas fa-map-marker-alt me-1"></i>Gorakhpur</p>
                            <p class="text-primary fw-bold">₹6.5 Lakhs</p>
                            <p class="small">Affordable residential plots with amenities</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-warning">Planned</span>
                                <a href="<?php echo BASE_URL; ?>/projects/braj-radha-nagri" class="btn btn-primary btn-sm">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12 text-center">
                    <a href="<?php echo BASE_URL; ?>/properties" class="btn btn-outline-primary btn-lg">
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
                        <img src="<?php echo BASE_URL; ?>/assets/images/hero-1.jpg" alt="About APS Dream Home" class="img-fluid rounded-3 shadow-lg">
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
                <?php 
                $stats = $hero_stats ?? [
                    'properties_sold' => '500+',
                    'happy_clients' => '1000+',
                    'years_experience' => '15+',
                    'projects_completed' => '50+'
                ];
                ?>
                <div class="col-md-3 mb-4">
                    <div class="stat-item">
                        <div class="stat-number" data-target="<?php echo (int)$stats['properties_sold']; ?>"><?php echo $stats['properties_sold']; ?></div>
                        <div class="stat-label">Properties Sold</div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stat-item">
                        <div class="stat-number" data-target="<?php echo (int)$stats['happy_clients']; ?>"><?php echo $stats['happy_clients']; ?></div>
                        <div class="stat-label">Happy Clients</div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stat-item">
                        <div class="stat-number" data-target="<?php echo (int)$stats['years_experience']; ?>"><?php echo $stats['years_experience']; ?></div>
                        <div class="stat-label">Years Experience</div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stat-item">
                        <div class="stat-number" data-target="<?php echo (int)$stats['projects_completed']; ?>"><?php echo $stats['projects_completed']; ?></div>
                        <div class="stat-label">Projects Completed</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <?php 
    $testimonialList = $testimonials ?? [];
    if (!empty($testimonialList)): 
    ?>
    <section class="testimonials-section py-5 bg-light">
        <div class="container">
            <div class="row mb-5">
                <div class="col-12 text-center">
                    <h2 class="display-5 fw-bold mb-4">What Our Clients Say</h2>
                    <p class="lead text-muted">Real stories from satisfied customers</p>
                </div>
            </div>
            <div id="testimonialCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-indicators">
                    <?php foreach($testimonialList as $i => $t): ?>
                    <button type="button" data-bs-target="#testimonialCarousel" data-bs-slide-to="<?php echo $i; ?>" <?php echo $i === 0 ? 'class="active" aria-current="true"' : ''; ?> aria-label="Slide <?php echo $i + 1; ?>"></button>
                    <?php endforeach; ?>
                </div>
                <div class="carousel-inner">
                    <?php foreach($testimonialList as $i => $t): ?>
                    <div class="carousel-item <?php echo $i === 0 ? 'active' : ''; ?>">
                        <div class="row justify-content-center">
                            <div class="col-lg-8 text-center">
                                <div class="testimonial-card p-4">
                                    <div class="testimonial-rating mb-3">
                                        <?php for($s = 0; $s < ($t->rating ?? 5); $s++): ?>
                                        <i class="fas fa-star text-warning"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <blockquote class="blockquote">
                                        <p class="mb-4"><em>"<?php echo htmlspecialchars($t->content ?? ''); ?>"</em></p>
                                    </blockquote>
                                    <div class="testimonial-author">
                                        <h5 class="mb-1"><?php echo htmlspecialchars($t->name ?? 'Anonymous'); ?></h5>
                                        <p class="text-muted mb-0"><?php echo htmlspecialchars($t->property ?? ''); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon bg-dark rounded-circle" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon bg-dark rounded-circle" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </div>
    </section>
    <?php endif; ?>

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
<!-- Page-specific scripts -->
<script>
    // Initialize application
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize animations
        if (typeof initAnimations === 'function') {
            initAnimations();
        }

        // Initialize property search
        if (typeof initPropertySearch === 'function') {
            initPropertySearch();
        }

        // Initialize contact form
        if (typeof initContactForm === 'function') {
            initContactForm();
        }

        // Initialize premium header
        if (typeof initPremiumHeader === 'function') {
            initPremiumHeader();
        }

        // Initialize back to top
        if (typeof initBackToTop === 'function') {
            initBackToTop();
        }
    });
</script>