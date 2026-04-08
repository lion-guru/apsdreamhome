<!-- Hero Section -->
<section class="hero" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 80px 0 60px;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7 text-white">
                <h1 class="display-4 fw-bold mb-3">Find Your <span class="text-warning">Dream Home</span></h1>
                <p class="lead mb-4">Premium residential & commercial properties across India. Buy, Sell, Rent - All in one platform.</p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="<?php echo BASE_URL; ?>/company/projects" class="btn btn-warning btn-lg">View Projects</a>
                    <a href="<?php echo BASE_URL; ?>/list-property" class="btn btn-outline-light btn-lg">Post Property FREE</a>
                </div>
            </div>
            <div class="col-lg-5 mt-4 mt-lg-0">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-primary text-white text-center py-3">
                        <h5 class="mb-0"><i class="fas fa-search me-2"></i>Search Properties</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="<?php echo BASE_URL; ?>/properties" method="GET">
                            <div class="mb-3">
                                <select name="listing" class="form-select">
                                    <option value="">Buy / Rent</option>
                                    <option value="sell">Buy</option>
                                    <option value="rent">Rent</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <select name="type" class="form-select">
                                    <option value="">Property Type</option>
                                    <option value="residential">Residential</option>
                                    <option value="commercial">Commercial</option>
                                    <option value="plot">Plot/Land</option>
                                    <option value="house">House/Villa</option>
                                    <option value="flat">Flat/Apartment</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <select name="location" class="form-select">
                                    <option value="">Select Location</option>
                                    <option value="Gorakhpur">Gorakhpur</option>
                                    <option value="Lucknow">Lucknow</option>
                                    <option value="Kushinagar">Kushinagar</option>
                                    <option value="Varanasi">Varanasi</option>
                                    <option value="Ayodhya">Ayodhya</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <select name="budget" class="form-select">
                                    <option value="">Budget</option>
                                    <option value="under_5l">Under ₹5 Lakh</option>
                                    <option value="5_10l">₹5-10 Lakh</option>
                                    <option value="10_20l">₹10-20 Lakh</option>
                                    <option value="20_50l">₹20-50 Lakh</option>
                                    <option value="above_50l">Above ₹50 Lakh</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 btn-lg">
                                <i class="fas fa-search me-2"></i>Search
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Quick Links -->
<section class="py-4 bg-light">
    <div class="container">
        <div class="row text-center">
            <div class="col-6 col-md-3 mb-3">
                <a href="<?php echo BASE_URL; ?>/properties?type=residential" class="text-decoration-none">
                    <div class="p-3 bg-white rounded shadow-sm">
                        <i class="fas fa-home fa-2x text-primary mb-2"></i>
                        <h6>Residential</h6>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <a href="<?php echo BASE_URL; ?>/properties?type=commercial" class="text-decoration-none">
                    <div class="p-3 bg-white rounded shadow-sm">
                        <i class="fas fa-store fa-2x text-success mb-2"></i>
                        <h6>Commercial</h6>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <a href="<?php echo BASE_URL; ?>/properties?type=plot" class="text-decoration-none">
                    <div class="p-3 bg-white rounded shadow-sm">
                        <i class="fas fa-vector-square fa-2x text-warning mb-2"></i>
                        <h6>Plots</h6>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <a href="<?php echo BASE_URL; ?>/list-property" class="text-decoration-none">
                    <div class="p-3 bg-white rounded shadow-sm">
                        <i class="fas fa-plus-circle fa-2x text-info mb-2"></i>
                        <h6>Post FREE</h6>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Stats -->
<section class="py-5">
    <div class="container">
        <div class="row text-center">
            <div class="col-6 col-md-3 mb-4">
                <div class="h2 text-primary mb-1"><?php echo $hero_stats['years_experience']; ?>+</div>
                <p class="text-muted mb-0">Years Experience</p>
            </div>
            <div class="col-6 col-md-3 mb-4">
                <div class="h2 text-success mb-1"><?php echo $hero_stats['projects_completed']; ?>+</div>
                <p class="text-muted mb-0">Projects Completed</p>
            </div>
            <div class="col-6 col-md-3 mb-4">
                <div class="h2 text-info mb-1"><?php echo $hero_stats['happy_customers']; ?>+</div>
                <p class="text-muted mb-0">Happy Customers</p>
            </div>
            <div class="col-6 col-md-3 mb-4">
                <div class="h2 text-warning mb-1"><?php echo $hero_stats['awards_won']; ?></div>
                <p class="text-muted mb-0">Awards Won</p>
            </div>
        </div>
    </div>
</section>

<!-- Our Projects -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-6 fw-bold">Our Projects</h2>
            <p class="text-muted">Explore our premium projects across Uttar Pradesh</p>
        </div>
        <div class="row">
            <?php if (!empty($featured_properties)): ?>
                <?php foreach (array_slice($featured_properties, 0, 4) as $project): 
                    $slug = $project['slug'] ?? strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $project['title']));
                    $imgPath = '/assets/images/projects/';
                    if (stripos($project['title'], 'Suryoday') !== false) {
                        $imgPath .= 'gorakhpur/suryoday.jpg';
                    } elseif (stripos($project['title'], 'Raghunath') !== false) {
                        $imgPath .= 'gorakhpur/raghunath nagri motiram.JPG';
                    } elseif (stripos($project['title'], 'Braj') !== false) {
                        $imgPath .= 'gorakhpur/suryoday1.jpeg';
                    } elseif (stripos($project['title'], 'Budh') !== false) {
                        $imgPath .= 'kushinagar/budh-bihar.jpg';
                    } else {
                        $imgPath .= 'placeholder/property.svg';
                    }
                ?>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="position-relative" style="height: 180px;">
                            <img src="<?php echo BASE_URL . $imgPath; ?>" class="w-100 h-100" style="object-fit: cover;" alt="<?php echo htmlspecialchars($project['title']); ?>" onerror="this.src='<?php echo BASE_URL; ?>/assets/images/placeholder/property.svg'">
                            <span class="badge bg-success position-absolute top-0 end-0 m-2"><?php echo $project['status']; ?></span>
                        </div>
                        <div class="card-body text-center">
                            <h5 class="card-title fw-bold"><?php echo htmlspecialchars($project['title']); ?></h5>
                            <p class="text-muted small mb-2"><i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($project['city']); ?></p>
                            <p class="h5 text-primary mb-3"><?php echo $project['price']; ?></p>
                            <a href="<?php echo BASE_URL; ?>/projects/<?php echo $slug; ?>" class="btn btn-outline-primary btn-sm">View Details</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="text-center mt-4">
            <a href="<?php echo BASE_URL; ?>/company/projects" class="btn btn-primary btn-lg">View All Projects</a>
        </div>
    </div>
</section>

<!-- Services -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-6 fw-bold">Our Services</h2>
            <p class="text-muted">Complete real estate solutions under one roof</p>
        </div>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm text-center p-4">
                    <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-hand-holding-usd fa-2x text-success"></i>
                    </div>
                    <h5>Home Loan</h5>
                    <p class="text-muted">SBI, HDFC, ICICI - Best rates & easy processing</p>
                    <a href="<?php echo BASE_URL; ?>/financial-services" class="btn btn-outline-success btn-sm">Learn More</a>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm text-center p-4">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-gavel fa-2x text-primary"></i>
                    </div>
                    <h5>Legal Services</h5>
                    <p class="text-muted">Registry, Mutation, Agreement - Complete documentation</p>
                    <a href="<?php echo BASE_URL; ?>/legal-services" class="btn btn-outline-primary btn-sm">Learn More</a>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm text-center p-4">
                    <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-couch fa-2x text-warning"></i>
                    </div>
                    <h5>Interior Design</h5>
                    <p class="text-muted">Modular kitchen, wardrobe, complete furnishing</p>
                    <a href="<?php echo BASE_URL; ?>/interior-design" class="btn btn-outline-warning btn-sm">Learn More</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h2 class="display-6 fw-bold mb-4">Why Choose APS Dream Home?</h2>
                <div class="d-flex mb-3">
                    <div class="flex-shrink-0">
                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="fas fa-check"></i>
                        </div>
                    </div>
                    <div class="ms-3">
                        <h6 class="mb-1">15+ Years Experience</h6>
                        <p class="text-muted mb-0 small">Trusted name in UP real estate since 2010</p>
                    </div>
                </div>
                <div class="d-flex mb-3">
                    <div class="flex-shrink-0">
                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="fas fa-check"></i>
                        </div>
                    </div>
                    <div class="ms-3">
                        <h6 class="mb-1">RERA Verified Projects</h6>
                        <p class="text-muted mb-0 small">All properties legally approved</p>
                    </div>
                </div>
                <div class="d-flex mb-3">
                    <div class="flex-shrink-0">
                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="fas fa-check"></i>
                        </div>
                    </div>
                    <div class="ms-3">
                        <h6 class="mb-1">Transparent Dealings</h6>
                        <p class="text-muted mb-0 small">No hidden charges, clear documentation</p>
                    </div>
                </div>
                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="fas fa-check"></i>
                        </div>
                    </div>
                    <div class="ms-3">
                        <h6 class="mb-1">24/7 Support</h6>
                        <p class="text-muted mb-0 small">Always here to help you</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card border-0 shadow-lg">
                    <div class="card-body p-4 text-center">
                        <i class="fas fa-headset fa-4x text-primary mb-4"></i>
                        <h4>Need Help?</h4>
                        <p class="text-muted mb-4">Our team is ready to assist you</p>
                        <div class="d-grid gap-3">
                            <a href="tel:+919277121112" class="btn btn-success btn-lg">
                                <i class="fas fa-phone me-2"></i>Call: +91 92771 21112
                            </a>
                            <a href="https://wa.me/919277121112" target="_blank" class="btn btn-outline-success btn-lg">
                                <i class="fab fa-whatsapp me-2"></i>WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-6 fw-bold">What Customers Say</h2>
        </div>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm p-4">
                    <div class="text-warning mb-3">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="card-text">"Bought a plot in Suryoday Colony. Best decision! Process was smooth and team was very helpful."</p>
                    <div class="d-flex align-items-center">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Rajesh Kumar</h6>
                            <small class="text-muted">Gorakhpur</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm p-4">
                    <div class="text-warning mb-3">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="card-text">"Excellent service! Got home loan easily through their assistance. Highly recommended."</p>
                    <div class="d-flex align-items-center">
                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Priya Singh</h6>
                            <small class="text-muted">Kushinagar</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm p-4">
                    <div class="text-warning mb-3">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                    </div>
                    <p class="card-text">"Great investment opportunity! The team guided me at every step. Thank you APS Dream Home!"</p>
                    <div class="d-flex align-items-center">
                        <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Amit Verma</h6>
                            <small class="text-muted">Lucknow</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-5 text-white text-center" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container">
        <h2 class="display-6 fw-bold mb-3">Ready to Find Your Dream Home?</h2>
        <p class="lead mb-4">Contact us today and let us help you find the perfect property</p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="tel:+919277121112" class="btn btn-warning btn-lg">
                <i class="fas fa-phone me-2"></i>Call Now
            </a>
            <a href="https://wa.me/919277121112" target="_blank" class="btn btn-success btn-lg">
                <i class="fab fa-whatsapp me-2"></i>WhatsApp
            </a>
        </div>
    </div>
</section>

<!-- WhatsApp Float -->
<a href="https://wa.me/919277121112" target="_blank" class="whatsapp-float">
    <i class="fab fa-whatsapp"></i>
</a>

<style>
.whatsapp-float {
    position: fixed;
    width: 60px;
    height: 60px;
    bottom: 20px;
    right: 20px;
    background: #25D366;
    color: white;
    border-radius: 50%;
    font-size: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 15px rgba(37, 211, 102, 0.4);
    z-index: 999;
    text-decoration: none;
    transition: all 0.3s;
}
.whatsapp-float:hover {
    background: #128C7E;
    transform: scale(1.1);
    color: white;
}
</style>
