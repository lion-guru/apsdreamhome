<!-- Hero Section -->
<section class="hero-section py-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 100px 0;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">Find Your <span class="text-warning">Dream Home</span></h1>
                <p class="lead mb-4">Discover premium residential and commercial properties in Gorakhpur, Lucknow, and across Uttar Pradesh with APS Dream Home.</p>
                <div class="d-flex gap-3">
                    <a href="<?php echo BASE_URL; ?>/properties" class="btn btn-warning btn-lg">Browse Properties</a>
                    <a href="<?php echo BASE_URL; ?>/contact" class="btn btn-outline-light btn-lg">Contact Us</a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="position-relative">
                    <div class="bg-white rounded-3 shadow-lg p-4 text-dark">
                        <h4 class="mb-3"><i class="fas fa-search text-primary me-2"></i>Quick Search</h4>
                        <form action="<?php echo BASE_URL; ?>/properties" method="GET">
                            <div class="mb-3">
                                <select class="form-select" name="location">
                                    <option value="">Select Location</option>
                                    <option value="gorakhpur">Gorakhpur</option>
                                    <option value="lucknow">Lucknow</option>
                                    <option value="kushinagar">Kushinagar</option>
                                    <option value="varanasi">Varanasi</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <select class="form-select" name="type">
                                    <option value="">Property Type</option>
                                    <option value="residential">Residential</option>
                                    <option value="commercial">Commercial</option>
                                    <option value="land">Land/Plot</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>Search Properties
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <i class="fas fa-home fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">Premium Properties</h5>
                        <p class="card-text text-muted">Handpicked selection of luxury homes and commercial spaces in prime locations</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <i class="fas fa-map-marked-alt fa-3x text-success mb-3"></i>
                        <h5 class="card-title">Prime Locations</h5>
                        <p class="card-text text-muted">Strategic locations in Gorakhpur, Lucknow, Kushinagar, and across UP</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <i class="fas fa-user-tie fa-3x text-info mb-3"></i>
                        <h5 class="card-title">Expert Guidance</h5>
                        <p class="card-text text-muted">Professional assistance throughout your property buying journey</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Statistics Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3 col-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body py-4">
                        <h2 class="text-primary mb-1"><?php echo $hero_stats['years_experience']; ?>+</h2>
                        <p class="text-muted mb-0">Years Experience</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body py-4">
                        <h2 class="text-success mb-1"><?php echo $hero_stats['projects_completed']; ?>+</h2>
                        <p class="text-muted mb-0">Projects Completed</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body py-4">
                        <h2 class="text-info mb-1"><?php echo $hero_stats['happy_customers']; ?>+</h2>
                        <p class="text-muted mb-0">Happy Customers</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body py-4">
                        <h2 class="text-warning mb-1"><?php echo $hero_stats['awards_won']; ?></h2>
                        <p class="text-muted mb-0">Awards Won</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Properties Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Featured Properties</h2>
            <p class="text-muted">Explore our handpicked premium properties</p>
        </div>
        <div class="row">
            <?php foreach ($featured_properties as $property): ?>
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card property-card border-0 shadow-sm h-100">
                    <div class="position-relative">
                        <div class="bg-primary text-white text-center py-5" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                            <i class="fas fa-building fa-3x mb-2"></i>
                            <p class="mb-0 small"><?php echo $property['type']; ?></p>
                        </div>
                        <span class="badge bg-success position-absolute top-0 end-0 m-2"><?php echo $property['status']; ?></span>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($property['title']); ?></h5>
                        <p class="text-muted mb-2"><i class="fas fa-map-marker-alt text-primary me-1"></i> <?php echo htmlspecialchars($property['location']); ?></p>
                        <h4 class="text-primary mb-3"><?php echo $property['price']; ?></h4>
                        <a href="<?php echo BASE_URL; ?>/properties/<?php echo $property['id']; ?>" class="btn btn-outline-primary btn-sm w-100">View Details</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="<?php echo BASE_URL; ?>/properties" class="btn btn-primary btn-lg">View All Properties <i class="fas fa-arrow-right ms-2"></i></a>
        </div>
    </div>
</section>

<!-- About Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h2 class="display-5 fw-bold mb-4">About APS Dream Home</h2>
                <p class="lead text-muted">With over <?php echo $hero_stats['years_experience']; ?> years of excellence in real estate, APS Dream Home has been helping families and businesses find their perfect properties across Gorakhpur, Lucknow, and Uttar Pradesh.</p>
                <p class="text-muted">Our commitment to quality, transparency, and customer satisfaction has made us a trusted name in the real estate industry. We specialize in residential plots, commercial spaces, and integrated townships.</p>
                <div class="d-flex gap-3 mt-4">
                    <a href="<?php echo BASE_URL; ?>/about" class="btn btn-primary">Learn More</a>
                    <a href="<?php echo BASE_URL; ?>/services" class="btn btn-outline-primary">Our Services</a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="card border-0 shadow-sm text-center p-4">
                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                            <h6>Verified Properties</h6>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card border-0 shadow-sm text-center p-4">
                            <i class="fas fa-shield-alt fa-2x text-primary mb-2"></i>
                            <h6>Legal Support</h6>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card border-0 shadow-sm text-center p-4">
                            <i class="fas fa-hand-holding-usd fa-2x text-warning mb-2"></i>
                            <h6>Easy Financing</h6>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card border-0 shadow-sm text-center p-4">
                            <i class="fas fa-headset fa-2x text-info mb-2"></i>
                            <h6>24/7 Support</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Our Projects Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Our Projects</h2>
            <p class="text-muted">Explore our ongoing and completed projects across Uttar Pradesh</p>
        </div>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center p-4">
                        <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-city fa-lg"></i>
                        </div>
                        <h5>Suyoday Colony</h5>
                        <p class="text-muted">Premium residential plots in Gorakhpur with modern infrastructure</p>
                        <a href="<?php echo BASE_URL; ?>/projects/suyoday-colony" class="btn btn-sm btn-outline-primary">View Project</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center p-4">
                        <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-building fa-lg"></i>
                        </div>
                        <h5>Raghunat Nagri</h5>
                        <p class="text-muted">Integrated township in developing area with all modern amenities</p>
                        <a href="<?php echo BASE_URL; ?>/projects/raghunat-nagri" class="btn btn-sm btn-outline-success">View Project</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center p-4">
                        <div class="rounded-circle bg-warning text-white d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-landmark fa-lg"></i>
                        </div>
                        <h5>Budh Bihar Colony</h5>
                        <p class="text-muted">Affordable residential plots in Kushinagar with basic facilities</p>
                        <a href="<?php echo BASE_URL; ?>/projects/budh-bihar-colony" class="btn btn-sm btn-outline-warning">View Project</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-center mt-3">
            <a href="<?php echo BASE_URL; ?>/company/projects" class="btn btn-primary btn-lg">View All Projects <i class="fas fa-arrow-right ms-2"></i></a>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
    <div class="container text-center">
        <h2 class="display-5 fw-bold mb-4">Ready to Find Your Dream Home?</h2>
        <p class="lead mb-4">Join thousands of happy customers who found their perfect property with APS Dream Home</p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="<?php echo BASE_URL; ?>/properties" class="btn btn-warning btn-lg">
                <i class="fas fa-search me-2"></i>Browse Properties
            </a>
            <a href="<?php echo BASE_URL; ?>/contact" class="btn btn-outline-light btn-lg">
                <i class="fas fa-phone me-2"></i>Contact Us
            </a>
            <a href="<?php echo BASE_URL; ?>/register" class="btn btn-light btn-lg">
                <i class="fas fa-user-plus me-2"></i>Register Now
            </a>
        </div>
    </div>
</section>

<!-- WhatsApp Floating Button -->
<a href="https://wa.me/919277121112" target="_blank" class="position-fixed bottom-0 end-0 m-4 rounded-circle bg-success text-white d-flex align-items-center justify-content-center shadow-lg" style="width: 60px; height: 60px; z-index: 1000;">
    <i class="fab fa-whatsapp fa-2x"></i>
</a>
