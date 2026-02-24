<?php
$page_title = $data['title'] ?? 'Welcome to APS Dream Home';
$page_description = $data['description'] ?? 'Discover premium properties and find your dream home with APS Dream Home - Your trusted real estate partner in UP';
?>

<section class="py-5 bg-gradient" style="background: linear-gradient(135deg,#0f2b66 0%,#1b5fd0 50%,#0f2b66 100%);">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7 text-white">
                <h1 class="display-4 fw-bold mb-3">APS Dream Home</h1>
                <p class="lead opacity-90 mb-4">Trusted Real Estate Partner in Gorakhpur, Lucknow & across Uttar Pradesh</p>
                <form action="<?php echo BASE_URL; ?>properties" method="GET" class="row g-2 bg-white p-2 rounded-3 shadow-sm">
                    <div class="col-md-4">
                        <select name="type" class="form-select form-select-lg bg-light">
                            <option value="">Property Type</option>
                            <option value="apartment">Apartments</option>
                            <option value="villa">Villas</option>
                            <option value="commercial">Commercial</option>
                            <option value="plot">Plots / Land</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <input type="text" name="location" class="form-control form-control-lg bg-light" placeholder="City or Area">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary btn-lg w-100">Search</button>
                    </div>
                </form>
                <div class="d-flex gap-3 mt-3">
                    <a href="<?php echo BASE_URL; ?>properties" class="btn btn-outline-light btn-lg">Explore Properties</a>
                    <a href="<?php echo BASE_URL; ?>contact" class="btn btn-light btn-lg text-primary">Contact Us</a>
                </div>
            </div>
            <div class="col-lg-5 text-white mt-4 mt-lg-0">
                <div class="p-4 bg-white bg-opacity-10 border border-white border-opacity-25 rounded-4">
                    <div class="row g-3">
                        <div class="col-6">
                            <a class="btn btn-outline-light w-100 py-3" href="<?php echo BASE_URL; ?>properties?type=apartment">Apartments</a>
                        </div>
                        <div class="col-6">
                            <a class="btn btn-outline-light w-100 py-3" href="<?php echo BASE_URL; ?>properties?type=villa">Villas</a>
                        </div>
                        <div class="col-6">
                            <a class="btn btn-outline-light w-100 py-3" href="<?php echo BASE_URL; ?>properties?type=commercial">Commercial</a>
                        </div>
                        <div class="col-6">
                            <a class="btn btn-outline-light w-100 py-3" href="<?php echo BASE_URL; ?>properties?type=plot">Plots / Land</a>
                        </div>
                    </div>
                    <div class="mt-4 p-3 bg-white bg-opacity-10 rounded-3">
                        <div class="d-flex justify-content-between">
                            <span>Featured Listings</span>
                            <a class="text-white" href="<?php echo BASE_URL; ?>properties?featured=1">View</a>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <span>Resale Properties</span>
                            <a class="text-white" href="<?php echo BASE_URL; ?>resell">View</a>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <span>Ready to Move</span>
                            <a class="text-white" href="<?php echo BASE_URL; ?>properties?ready=1">View</a>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <span>₹50L - ₹1Cr Homes</span>
                            <a class="text-white" href="<?php echo BASE_URL; ?>properties?min_price=5000000&max_price=10000000">View</a>
                        </div>
                    </div>
                    <div class="mt-3 small">
                        <div>500+ Properties</div>
                        <div>2k+ Happy Families</div>
                        <div><a class="text-white" href="<?php echo BASE_URL; ?>properties">Explore All</a></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-4 bg-white">
    <div class="container">
        <div class="row text-center g-4">
            <div class="col-md-3 col-6">
                <div class="display-6 fw-bold text-primary">500+</div>
                <div class="text-muted">Properties</div>
            </div>
            <div class="col-md-3 col-6">
                <div class="display-6 fw-bold text-primary">2k+</div>
                <div class="text-muted">Happy Families</div>
            </div>
            <div class="col-md-3 col-6">
                <div class="display-6 fw-bold text-primary">50+</div>
                <div class="text-muted">Expert Agents</div>
            </div>
            <div class="col-md-3 col-6">
                <div class="display-6 fw-bold text-primary">10+</div>
                <div class="text-muted">Years</div>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h6 class="text-uppercase text-primary fw-bold">Exclusive Offers</h6>
            <h2 class="display-5 fw-bold">Featured Properties</h2>
            <div class="mx-auto bg-primary mt-3" style="height:4px;width:80px;border-radius:2px;"></div>
        </div>

        <div class="row g-4">
            <?php if (!empty($data['properties'])): ?>
                <?php foreach ($data['properties'] as $property): ?>
                    <div class="col-md-4" data-aos="fade-up">
                        <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
                            <div class="position-relative">
                                <img src="<?php echo !empty($property->image_path) ? htmlspecialchars($property->image_path) : BASE_URL . '/assets/images/property-placeholder.jpg'; ?>"
                                    class="card-img-top property-card-img"
                                    alt="<?php echo htmlspecialchars($property->title); ?>">
                                <span class="badge bg-primary position-absolute top-0 start-0 m-3 px-3 py-2">Featured</span>
                                <span class="badge bg-dark position-absolute bottom-0 end-0 m-3 px-3 py-2">
                                    ₹<?php echo number_format($property->price); ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title fw-bold mb-2">
                                    <a href="<?php echo BASE_URL; ?>properties/<?php echo $property->id; ?>" class="text-dark text-decoration-none stretched-link">
                                        <?php echo htmlspecialchars($property->title); ?>
                                    </a>
                                </h5>
                                <p class="text-muted small mb-3">
                                    <i class="fas fa-map-marker-alt text-primary me-2"></i><?php echo htmlspecialchars($property->location ?? $property->address ?? ''); ?>
                                </p>
                                <div class="d-flex justify-content-between border-top pt-3 mt-3">
                                    <span class="small"><i class="fas fa-bed me-1"></i> <?php echo $property->bedrooms ?? 0; ?> Beds</span>
                                    <span class="small"><i class="fas fa-bath me-1"></i> <?php echo $property->bathrooms ?? 0; ?> Baths</span>
                                    <span class="small"><i class="fas fa-ruler-combined me-1"></i> <?php echo $property->area ?? 0; ?> Sq.ft</span>
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

<section class="py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-right">
                <img src="<?php echo BASE_URL; ?>/assets/images/hero-2.jpg" alt="Why Choose Us" class="img-fluid rounded-3 shadow-lg">
            </div>
            <div class="col-lg-6 ps-lg-5" data-aos="fade-left">
                <h6 class="text-uppercase text-primary fw-bold">Why Choose Us</h6>
                <h2 class="display-5 fw-bold mb-4">We Help You Find Your Dream Home</h2>
                <p class="lead text-muted mb-4">With over a decade of experience in the real estate market, we provide transparent and hassle-free services.</p>

                <div class="d-flex mb-4">
                    <div class="flex-shrink-0">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center feature-icon-circle">
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
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center feature-icon-circle">
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
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center feature-icon-circle">
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