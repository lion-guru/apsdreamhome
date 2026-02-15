<!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-overlay"></div>
        <div class="container hero-content">
            <div class="row align-items-center">
                <div class="col-lg-7" data-aos="fade-right">
                    <h1 class="display-3 fw-bold mb-4 animate__animated animate__fadeInDown">
                        Your Journey to the <span class="text-warning">Perfect Home</span> Starts Here
                    </h1>
                    <p class="lead mb-5 opacity-90 animate__animated animate__fadeInUp">
                        Discover premium villas, apartments, and commercial spaces in Gorakhpur's most sought-after locations.
                    </p>
                    <div class="d-flex gap-3 mb-5 animate__animated animate__fadeInUp">
                        <a href="<?= BASE_URL ?>properties" class="btn btn-premium btn-lg px-4">Explore Properties</a>
                        <a href="<?= BASE_URL ?>about" class="btn btn-outline-premium btn-lg px-4">Learn More</a>
                    </div>
                </div>

                <div class="col-lg-5" data-aos="fade-left">
                    <div class="search-card p-4 p-md-5 rounded-5 shadow-lg">
                        <h3 class="text-dark fw-bold mb-4">Find Property</h3>
                        <form action="<?= BASE_URL ?>properties" method="GET">
                            <div class="mb-3">
                                <label class="form-label text-dark fw-semibold">Location</label>
                                <select name="location" class="form-select form-select-lg border-0 bg-light">
                                    <option value="">Any Location</option>
                                    <option value="Gorakhpur">Gorakhpur</option>
                                    <option value="Lucknow">Lucknow</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-dark fw-semibold">Property Type</label>
                                <select name="type" class="form-select form-select-lg border-0 bg-light">
                                    <option value="">Any Type</option>
                                    <option value="Villa">Villa</option>
                                    <option value="Apartment">Apartment</option>
                                    <option value="Plot">Plot</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-premium btn-lg w-100 py-3 mt-3">
                                <i class="fas fa-search me-2"></i> Search Now
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Stats -->
    <section class="py-5 bg-white">
        <div class="container mt-n5 position-relative z-index-2">
            <div class="row g-4">
                <?php
                $stats = [
                    ['icon' => 'fa-building', 'count' => $counts['total'] ?? '59', 'label' => 'Total Properties', 'color' => 'primary'],
                    ['icon' => 'fa-users', 'count' => $counts['agents'] ?? '1', 'label' => 'Expert Agents', 'color' => 'success'],
                    ['icon' => 'fa-smile', 'count' => '2000+', 'label' => 'Happy Clients', 'color' => 'info'],
                    ['icon' => 'fa-award', 'count' => '15+', 'label' => 'Years Experience', 'color' => 'warning']
                ];
                foreach ($stats as $index => $stat):
                ?>
                <div class="col-lg-3 col-6" data-aos="zoom-in" data-aos-delay="<?= $index * 100 ?>">
                    <div class="card stat-card h-100 p-4 shadow-sm text-center rounded-4">
                        <div class="mb-3 text-<?= $stat['color'] ?>">
                            <i class="fas <?= $stat['icon'] ?> fa-2x"></i>
                        </div>
                        <h3 class="fw-bold mb-1"><?= $stat['count'] ?></h3>
                        <p class="text-muted small mb-0"><?= $stat['label'] ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Featured Properties -->
    <section class="section-padding bg-light">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h6 class="text-primary fw-bold text-uppercase ls-2">Our Collection</h6>
                <h2 class="display-5 fw-bold mb-3">Featured Properties</h2>
                <p class="text-muted mx-auto" style="max-width: 600px;">Handpicked premium properties that offer the perfect blend of luxury, comfort, and value.</p>
            </div>

            <div class="row g-4">
                <?php if (!empty($featured_properties)): ?>
                    <?php foreach ($featured_properties as $index => $prop): ?>
                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
                        <div class="card property-card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                            <div class="position-relative">
                                <?php
                                $image_filename = $prop['main_image'] ?? '';
                                $image_url = (!empty($prop['main_image']))
                                    ? BASE_URL . 'public/uploads/property/' . $image_filename
                                    : 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=800&q=80';
                                ?>
                                <img src="<?= $image_url ?>" class="card-img-top" alt="<?= $prop['title'] ?>" style="height: 260px; object-fit: cover;">
                                <div class="property-badge">
                                    <span class="badge bg-primary rounded-pill px-3 py-2 shadow-sm">
                                        <?= $prop['property_type'] ?? 'Property' ?>
                                    </span>
                                </div>
                            </div>
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h5 class="fw-bold mb-0"><?= h($prop['title']) ?></h5>
                                    <span class="text-primary fw-bold">₹<?= number_format($prop['price'] ?? 0) ?></span>
                                </div>
                                <p class="text-muted small mb-3"><i class="fas fa-map-marker-alt me-2 text-primary"></i> <?= h($prop['city'] . ', ' . $prop['state']) ?></p>
                                <div class="d-flex gap-3 text-muted small pt-3 border-top">
                                    <span><i class="fas fa-bed me-1 text-primary"></i> <?= $prop['bedrooms'] ?? '3' ?> Beds</span>
                                    <span><i class="fas fa-bath me-1 text-primary"></i> <?= $prop['bathrooms'] ?? '2' ?> Baths</span>
                                    <span><i class="fas fa-ruler-combined me-1 text-primary"></i> <?= $prop['area_sqft'] ?? '1200' ?> sqft</span>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-0 p-4 pt-0">
                                <div class="d-flex gap-2">
                                    <a href="<?= BASE_URL ?>property/<?= $prop['id'] ?>" class="btn btn-outline-premium flex-grow-1">View Details</a>
                                    <a href="<?= BASE_URL ?>book-property/<?= $prop['id'] ?>" class="btn btn-premium px-3" title="Book Now">
                                        <i class="fas fa-calendar-alt"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <div class="alert alert-info rounded-4 border-0 shadow-sm p-5">
                            <i class="fas fa-info-circle fa-3x mb-3 opacity-50"></i>
                            <h4>No properties found at the moment</h4>
                            <p class="mb-0">Please check back later or contact our agents for assistance.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="text-center mt-5" data-aos="fade-up">
                <a href="<?= BASE_URL ?>properties" class="btn btn-premium btn-lg px-5">View All Properties</a>
            </div>
        </div>
    </section>

    <!-- Why Choose Us -->
    <section class="section-padding bg-white">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0" data-aos="fade-right">
                    <h6 class="text-primary fw-bold text-uppercase ls-2">Why APS Dream Home</h6>
                    <h2 class="display-5 fw-bold mb-4">We Help You Find Your <br>Dream Property</h2>
                    <p class="text-muted mb-5">With over 15 years of experience in the real estate market, we have helped thousands of families find their perfect home. Our expertise and commitment to quality ensure a seamless property buying experience.</p>

                    <div class="row g-4">
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="feature-icon bg-primary bg-opacity-10 text-primary me-3">
                                    <i class="fas fa-check-circle fa-lg"></i>
                                </div>
                                <h5 class="fw-bold mb-0">Verified Listings</h5>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="feature-icon bg-success bg-opacity-10 text-success me-3">
                                    <i class="fas fa-handshake fa-lg"></i>
                                </div>
                                <h5 class="fw-bold mb-0">Expert Guidance</h5>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="feature-icon bg-info bg-opacity-10 text-info me-3">
                                    <i class="fas fa-shield-alt fa-lg"></i>
                                </div>
                                <h5 class="fw-bold mb-0">Secure Deals</h5>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="feature-icon bg-warning bg-opacity-10 text-warning me-3">
                                    <i class="fas fa-headset fa-lg"></i>
                                </div>
                                <h5 class="fw-bold mb-0">24/7 Support</h5>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="position-relative ps-lg-5">
                        <img src="https://images.unsplash.com/photo-1582408921715-18e7806365c1?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" class="img-fluid rounded-5 shadow-lg" alt="About Us">
                        <div class="position-absolute bottom-0 start-0 translate-middle-x mb-n4 d-none d-md-block">
                            <div class="bg-white p-4 rounded-4 shadow-lg text-center" style="width: 200px;">
                                <h2 class="fw-bold text-primary mb-0">15+</h2>
                                <p class="text-muted small fw-bold mb-0">Years Experience</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section text-center">
        <div class="container" data-aos="zoom-in">
            <h2 class="display-4 fw-bold mb-4 text-white">Ready to Find Your Dream Home?</h2>
            <p class="lead mb-5 text-white-50 mx-auto" style="max-width: 700px;">Our experts are here to help you navigate the real estate market and find the perfect property that matches your needs and budget.</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="<?= BASE_URL ?>contact" class="btn btn-premium btn-lg px-5">Contact Us Now</a>
                <a href="<?= BASE_URL ?>properties" class="btn btn-outline-premium btn-lg px-5">Browse Properties</a>
            </div>
        </div>
    </section>
