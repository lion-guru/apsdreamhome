<?php
/**
 * Home - APS Dream Home
 * Integrated with enhanced universal template system
 */

require_once 'includes/config.php';
require_once 'includes/enhanced_universal_template.php';

// Create template instance
$template = new EnhancedUniversalTemplate();
$template->setTitle('APS Dream Homes | Premium Real Estate')
          ->setDescription('APS Dream Homes - Premium real estate properties in Gorakhpur, Lucknow, Kanpur. Find your dream home, villa, apartment, or commercial space.')
          ->addCSS('assets/css/modern-homepage-enhancements.css')
          ->addJS('assets/js/home-slider.js');

// Render header (includes HTML structure)
$template->renderHeader();
?>

<!-- Main Content -->

<!-- Custom CSS for Homepage -->
<style>
:root {
    --primary-color: #2c3e50;
    --secondary-color: #3498db;
    --accent-color: #e74c3c;
    --light-bg: #f8f9fa;
    --dark-bg: #2c3e50;
}

/* Hero Section */
.hero-section {
    background: linear-gradient(135deg, rgba(44, 62, 80, 0.9), rgba(52, 152, 219, 0.9)), url('assets/images/hero-bg.jpg');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    color: white;
    padding: 6rem 0 4rem;
    text-align: center;
    margin-top: 76px;
}

.hero-title {
    font-size: 3.5rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
}

.hero-subtitle {
    font-size: 1.5rem;
    margin-bottom: 2rem;
    opacity: 0.9;
}

.search-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

/* Stats Section */
.stats-section {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    padding: 4rem 0;
}

.stat-card {
    text-align: center;
    padding: 2rem;
    border-radius: 15px;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-10px);
}

.stat-number {
    font-size: 3rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

/* Property Cards */
.property-card {
    border: none;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    height: 100%;
}

.property-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

.property-image {
    height: 250px;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.property-card:hover .property-image {
    transform: scale(1.1);
}

.price-tag {
    background: var(--accent-color);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 25px;
    font-weight: 600;
    position: absolute;
    top: 1rem;
    right: 1rem;
}

/* Features Section */
.features-section {
    padding: 5rem 0;
    background: var(--light-bg);
}

.feature-card {
    text-align: center;
    padding: 3rem 2rem;
    border-radius: 20px;
    background: white;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    height: 100%;
}

.feature-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

.feature-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 1.5rem;
    background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2rem;
}

/* CTA Section */
.cta-section {
    background: linear-gradient(135deg, var(--accent-color), var(--secondary-color));
    color: white;
    padding: 5rem 0;
    text-align: center;
}

/* Buttons */
.btn-primary-custom {
    background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
    color: white;
    border: none;
    padding: 0.75rem 2rem;
    border-radius: 25px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-primary-custom:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
}

.btn-outline-custom {
    background: transparent;
    color: white;
    border: 2px solid white;
    padding: 0.75rem 2rem;
    border-radius: 25px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-outline-custom:hover {
    background: white;
    color: var(--primary-color);
}

/* Responsive */
@media (max-width: 768px) {
    .hero-title {
        font-size: 2.5rem;
    }
    
    .hero-subtitle {
        font-size: 1.2rem;
    }
    
    .stat-number {
        font-size: 2rem;
    }
}
</style>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Font Awesome -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<!-- AOS Animation -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center min-vh-100">
            <div class="col-lg-6">
                <div class="hero-content" data-aos="fade-right">
                    <h1 class="hero-title">Find Your Dream Home</h1>
                    <p class="hero-subtitle">Premium properties in Gorakhpur, Lucknow, Kanpur & beyond</p>
                    <div class="d-flex flex-column flex-sm-row gap-3">
                        <a href="properties.php" class="btn btn-primary-custom btn-lg">Browse Properties</a>
                        <a href="contact.php" class="btn btn-outline-custom btn-lg">Talk to Advisor</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="search-card" data-aos="fade-left">
                    <h3 class="text-center mb-4">Smart Property Search</h3>
                    <form action="properties.php" method="GET">
                        <?php 
                        if (class_exists('CSRFProtection')) {
                            echo CSRFProtection::hiddenField();
                        }
                        ?>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Location</label>
                                <input type="text" class="form-control" name="location" placeholder="City or Project">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Property Type</label>
                                <select class="form-select" name="type">
                                    <option value="">Any Type</option>
                                    <option value="residential">Residential</option>
                                    <option value="commercial">Commercial</option>
                                    <option value="land">Land/Plot</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Min Price (₹)</label>
                                <input type="number" class="form-control" name="min_price" placeholder="25,00,000">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Max Price (₹)</label>
                                <input type="number" class="form-control" name="max_price" placeholder="90,00,000">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary-custom w-100 btn-lg">
                                    <i class="fas fa-search me-2"></i>Search Properties
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-section">
    <div class="container">
        <div class="row">
            <div class="col-md-3 col-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($stats['total_properties']); ?>+</div>
                    <div class="stat-label">Properties</div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($stats['available_properties']); ?>+</div>
                    <div class="stat-label">Available</div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($stats['residential_properties']); ?>+</div>
                    <div class="stat-label">Residential</div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($stats['commercial_properties']); ?>+</div>
                    <div class="stat-label">Commercial</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Properties -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-5 fw-bold mb-3">Featured Properties</h2>
            <p class="lead text-muted">Premium listings handpicked by our real estate experts</p>
        </div>
        
        <?php if ($featured_properties && $featured_properties->num_rows > 0): ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php while ($property = $featured_properties->fetch_assoc()): ?>
                    <div class="col" data-aos="fade-up" data-aos-delay="<?php echo (int)$property['id'] * 100; ?>">
                        <div class="property-card">
                            <div class="position-relative overflow-hidden">
                                <img src="uploads/properties/default.jpg" class="property-image w-100" alt="<?php echo $property['title']; ?>">
                                <div class="price-tag">₹<?php echo number_format($property['price'], 2); ?></div>
                            </div>
                            <div class="card-body p-4">
                                <h4 class="fw-bold mb-2"><?php echo $property['title']; ?></h4>
                                <p class="text-muted mb-3">
                                    <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                    <?php echo $property['location']; ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="badge bg-primary rounded-pill"><?php echo ucfirst($property['type']); ?></span>
                                    <span class="badge bg-success rounded-pill">Available</span>
                                </div>
                                <a href="property_details.php?id=<?php echo $property['id']; ?>" class="btn btn-primary-custom w-100">
                                    View Details <i class="fas fa-arrow-right ms-2"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center" role="alert">
                <i class="fas fa-info-circle me-2"></i>
                No featured properties available at the moment. Check back soon!
            </div>
        <?php endif; ?>
        
        <div class="text-center mt-5">
            <a href="properties.php" class="btn btn-outline-primary btn-lg">
                View All Properties <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-5 fw-bold mb-3">Why Choose APS Dream Homes</h2>
            <p class="lead text-muted">15+ years of excellence in real estate services</p>
        </div>
        
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
            <div class="col" data-aos="fade-up" data-aos-delay="100">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-award"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Trusted Since 2009</h4>
                    <p class="text-muted">15+ years of delivering excellence in real estate services across Uttar Pradesh.</p>
                </div>
            </div>
            <div class="col" data-aos="fade-up" data-aos-delay="200">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Premium Properties</h4>
                    <p class="text-muted">Handpicked properties with verified documents and clear titles for your peace of mind.</p>
                </div>
            </div>
            <div class="col" data-aos="fade-up" data-aos-delay="300">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Expert Guidance</h4>
                    <p class="text-muted">Dedicated property advisors to help you make informed decisions every step of the way.</p>
                </div>
            </div>
            <div class="col" data-aos="fade-up" data-aos-delay="400">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Secure Transactions</h4>
                    <p class="text-muted">End-to-end support with legal assistance and secure documentation processes.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Latest Projects -->
<?php if ($projects && $projects->num_rows > 0): ?>
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-5 fw-bold mb-3">Latest Projects</h2>
            <p class="lead text-muted">Explore our ongoing and upcoming developments</p>
        </div>
        
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php while ($project = $projects->fetch_assoc()): ?>
                <div class="col" data-aos="fade-up" data-aos-delay="<?php echo (int)$project['id'] * 100; ?>">
                    <div class="card h-100 border-0 shadow-sm">
                        <img src="uploads/projects/default.jpg" class="card-img-top" style="height: 200px; object-fit: cover;" alt="<?php echo $project['name']; ?>">
                        <div class="card-body">
                            <h5 class="fw-bold"><?php echo $project['name']; ?></h5>
                            <p class="text-muted"><?php echo $project['location']; ?></p>
                            <a href="project_details.php?id=<?php echo $project['id']; ?>" class="btn btn-outline-primary w-100">
                                Learn More
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8" data-aos="fade-right">
                <h2 class="display-5 fw-bold mb-3">Ready to Find Your Dream Home?</h2>
                <p class="lead mb-4">Let our expert team help you discover the perfect property that matches your needs and budget.</p>
                <div class="d-flex flex-column flex-sm-row gap-3">
                    <a href="contact.php" class="btn btn-outline-custom btn-lg">
                        <i class="fas fa-phone me-2"></i>Contact Us
                    </a>
                    <a href="register.php" class="btn btn-outline-custom btn-lg">
                        <i class="fas fa-user-plus me-2"></i>Register Now
                    </a>
                </div>
            </div>
            <div class="col-lg-4" data-aos="fade-left">
                <div class="text-center">
                    <i class="fas fa-home" style="font-size: 8rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- AOS Animation JS -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<!-- Custom JS -->
<script>
    // Initialize AOS
    AOS.init({
        duration: 1000,
        once: true,
        offset: 100
    });
    
    // Navbar scroll effect
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar');
        if (window.scrollY > 50) {
            navbar.style.background = 'rgba(255, 255, 255, 0.98)';
            navbar.style.boxShadow = '0 5px 30px rgba(0, 0, 0, 0.15)';
        } else {
            navbar.style.background = 'rgba(255, 255, 255, 0.95)';
            navbar.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.1)';
        }
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
</script>

<?php
// Render footer using enhanced universal template
$template->renderFooter();
?>
</body>
</html>