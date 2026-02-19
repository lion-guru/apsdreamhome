<?php
$page_title = $data['title'] ?? 'Welcome to APS Dream Home';
$page_description = $data['description'] ?? 'Discover premium properties and find your dream home with APS Dream Home - Your trusted real estate partner in UP';
?>

<!-- Hero Section -->
<section class="hero-section text-white d-flex align-items-center position-relative" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('<?php echo BASE_URL; ?>assets/images/hero-bg.jpg') no-repeat center center/cover; min-height: 80vh;">
    <div class="container position-relative z-index-2">
        <div class="row justify-content-center text-center">
            <div class="col-lg-10">
                <h1 class="display-3 fw-bold mb-4 animate__animated animate__fadeInDown">Find Your Perfect Dream Home</h1>
                <p class="lead mb-5 animate__animated animate__fadeInUp animate__delay-1s">Premium Properties in Gorakhpur, Lucknow & Across Uttar Pradesh</p>
                
                <div class="search-box bg-white p-4 rounded-3 shadow-lg animate__animated animate__fadeInUp animate__delay-2s mx-auto" style="max-width: 800px;">
                    <form action="<?php echo BASE_URL; ?>properties" method="GET" class="row g-3">
                        <div class="col-md-4">
                            <select name="type" class="form-select form-select-lg border-0 bg-light">
                                <option value="">Property Type</option>
                                <option value="plot">Plot / Land</option>
                                <option value="house">House / Villa</option>
                                <option value="apartment">Apartment</option>
                                <option value="commercial">Commercial</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <input type="text" name="location" class="form-control form-control-lg border-0 bg-light" placeholder="Search Location (e.g. Kunraghat)">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold">Search</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row text-center g-4">
            <div class="col-md-3 col-6" data-aos="fade-up">
                <div class="display-4 fw-bold text-primary mb-2">500+</div>
                <p class="text-muted mb-0">Properties Sold</p>
            </div>
            <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="100">
                <div class="display-4 fw-bold text-primary mb-2">1200+</div>
                <p class="text-muted mb-0">Happy Customers</p>
            </div>
            <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="200">
                <div class="display-4 fw-bold text-primary mb-2">50+</div>
                <p class="text-muted mb-0">Expert Agents</p>
            </div>
            <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="300">
                <div class="display-4 fw-bold text-primary mb-2">10+</div>
                <p class="text-muted mb-0">Years Experience</p>
            </div>
        </div>
    </div>
</section>

<!-- Featured Properties -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h6 class="text-uppercase text-primary fw-bold letter-spacing-2">Exclusive Offers</h6>
            <h2 class="display-5 fw-bold">Featured Properties</h2>
            <div class="divider mx-auto bg-primary mt-3" style="width: 80px; height: 4px;"></div>
        </div>

        <div class="row g-4">
            <?php if (!empty($data['properties'])): ?>
                <?php foreach ($data['properties'] as $property): ?>
                    <div class="col-md-4" data-aos="fade-up">
                        <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
                            <div class="position-relative">
                                <img src="<?php echo !empty($property['image_url']) ? htmlspecialchars($property['image_url']) : BASE_URL . 'assets/images/property-placeholder.jpg'; ?>" 
                                     class="card-img-top" 
                                     alt="<?php echo htmlspecialchars($property['title']); ?>"
                                     style="height: 250px; object-fit: cover;">
                                <span class="badge bg-primary position-absolute top-0 start-0 m-3 px-3 py-2">Featured</span>
                                <span class="badge bg-dark position-absolute bottom-0 end-0 m-3 px-3 py-2">
                                    ₹<?php echo number_format($property['price']); ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title fw-bold mb-2">
                                    <a href="<?php echo BASE_URL; ?>properties/<?php echo $property['id']; ?>" class="text-dark text-decoration-none stretched-link">
                                        <?php echo htmlspecialchars($property['title']); ?>
                                    </a>
                                </h5>
                                <p class="text-muted small mb-3">
                                    <i class="fas fa-map-marker-alt text-primary me-2"></i><?php echo htmlspecialchars($property['location']); ?>
                                </p>
                                <div class="d-flex justify-content-between border-top pt-3 mt-3">
                                    <span class="small"><i class="fas fa-bed me-1"></i> <?php echo $property['bedrooms'] ?? 0; ?> Beds</span>
                                    <span class="small"><i class="fas fa-bath me-1"></i> <?php echo $property['bathrooms'] ?? 0; ?> Baths</span>
                                    <span class="small"><i class="fas fa-ruler-combined me-1"></i> <?php echo $property['area'] ?? 0; ?> Sq.ft</span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <p class="text-muted lead">No featured properties available at the moment.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="text-center mt-5">
            <a href="<?php echo BASE_URL; ?>properties" class="btn btn-outline-primary btn-lg px-5 rounded-pill">View All Properties</a>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-right">
                <img src="<?php echo BASE_URL; ?>assets/images/about-us.jpg" alt="Why Choose Us" class="img-fluid rounded-3 shadow-lg">
            </div>
            <div class="col-lg-6 ps-lg-5" data-aos="fade-left">
                <h6 class="text-uppercase text-primary fw-bold letter-spacing-2">Why Choose Us</h6>
                <h2 class="display-5 fw-bold mb-4">We Help You Find Your Dream Home</h2>
                <p class="lead text-muted mb-4">With over a decade of experience in the real estate market, we provide transparent and hassle-free services.</p>
                
                <div class="d-flex mb-4">
                    <div class="flex-shrink-0">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="fas fa-check"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="fw-bold">Legal Verification</h5>
                        <p class="text-muted">All our properties are legally verified and free from disputes.</p>
                    </div>
                </div>
                
                <div class="d-flex mb-4">
                    <div class="flex-shrink-0">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="fw-bold">Best Market Price</h5>
                        <p class="text-muted">We ensure you get the best deal with transparent pricing.</p>
                    </div>
                </div>

                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="fas fa-headset"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="fw-bold">Expert Support</h5>
                        <p class="text-muted">Our team of experts guides you through every step of the process.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-5 bg-primary text-white text-center position-relative overflow-hidden">
    <div class="container position-relative z-index-2">
        <h2 class="display-5 fw-bold mb-4">Ready to Find Your Dream Home?</h2>
        <p class="lead mb-5 opacity-75">Join thousands of happy families who found their perfect home with APS Dream Home.</p>
        <div class="d-flex justify-content-center gap-3">
            <a href="<?php echo BASE_URL; ?>contact" class="btn btn-light btn-lg px-5 rounded-pill text-primary fw-bold">Contact Us</a>
            <a href="<?php echo BASE_URL; ?>properties" class="btn btn-outline-light btn-lg px-5 rounded-pill">Browse Properties</a>
        </div>
    </div>
</section>

<style>
    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    }
    .transition-all {
        transition: all 0.3s ease;
    }
    .letter-spacing-2 {
        letter-spacing: 2px;
    }
</style>
