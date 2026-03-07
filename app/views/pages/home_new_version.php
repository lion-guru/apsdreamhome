<?php
// Home Page - APS Dream Home
// Main landing page with hero section, featured properties, etc.
?>

<!-- Hero Section -->
<section class="hero-section bg-gradient-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">Find Your Dream Home in Gorakhpur</h1>
                <p class="lead mb-4">Discover premium residential and commercial properties with APS Dream Home. Your trusted real estate partner since 2014.</p>
                <div class="d-flex gap-3">
                    <a href="#properties" class="btn btn-light btn-lg">View Properties</a>
                    <a href="#contact" class="btn btn-outline-light btn-lg">Contact Us</a>
                </div>
            </div>
            <div class="col-lg-6">
                <img src="<?php echo BASE_URL; ?>/assets/images/hero-image.jpg" alt="APS Dream Home" class="img-fluid rounded shadow" style="max-width: 100%;">
            </div>
        </div>
    </div>
</section>

<!-- Featured Properties -->
<section class="py-5 bg-light" id="properties">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="display-5 fw-bold">Featured Properties</h2>
                <p class="lead text-muted">Explore our premium property collection</p>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <img src="<?php echo BASE_URL; ?>/assets/images/property1.jpg" class="card-img-top" alt="Property 1" style="height: 250px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title">APS Dream City</h5>
                        <p class="card-text text-muted">Premium residential plots in prime location</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="h5 text-primary mb-0">₹25L - ₹50L</span>
                            <a href="#" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <img src="<?php echo BASE_URL; ?>/assets/images/property2.jpg" class="card-img-top" alt="Property 2" style="height: 250px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title">APS Heights</h5>
                        <p class="card-text text-muted">Modern commercial spaces</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="h5 text-primary mb-0">₹1.5Cr - ₹3Cr</span>
                            <a href="#" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <img src="<?php echo BASE_URL; ?>/assets/images/property3.jpg" class="card-img-top" alt="Property 3" style="height: 250px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title">APS Greens</h5>
                        <p class="card-text text-muted">Eco-friendly residential project</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="h5 text-primary mb-0">₹35L - ₹75L</span>
                            <a href="#" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="display-5 fw-bold">Our Services</h2>
                <p class="lead text-muted">Comprehensive real estate solutions</p>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-md-4 text-center">
                <div class="service-card p-4">
                    <i class="fas fa-home fa-3x text-primary mb-3"></i>
                    <h4>Property Sales</h4>
                    <p>Buy premium residential and commercial properties</p>
                </div>
            </div>
            <div class="col-md-4 text-center">
                <div class="service-card p-4">
                    <i class="fas fa-building fa-3x text-primary mb-3"></i>
                    <h4>Property Development</h4>
                    <p>Expert construction and development services</p>
                </div>
            </div>
            <div class="col-md-4 text-center">
                <div class="service-card p-4">
                    <i class="fas fa-handshake fa-3x text-primary mb-3"></i>
                    <h4>Consultation</h4>
                    <p>Professional real estate consultation</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-6">
                <h2 class="display-5 fw-bold mb-4">Why Choose APS Dream Home?</h2>
                <div class="row g-3">
                    <div class="col-12">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle text-success me-3 fa-lg"></i>
                            <span>8+ years of real estate experience</span>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle text-success me-3 fa-lg"></i>
                            <span>Prime locations across Gorakhpur</span>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle text-success me-3 fa-lg"></i>
                            <span>Modern amenities and infrastructure</span>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle text-success me-3 fa-lg"></i>
                            <span>Transparent pricing and documentation</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <img src="<?php echo BASE_URL; ?>/assets/images/why-choose-us.jpg" alt="Why Choose APS Dream Home" class="img-fluid rounded shadow">
            </div>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="py-5" id="contact">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="display-5 fw-bold">Get In Touch</h2>
                <p class="lead text-muted">Ready to find your dream property? Contact us today.</p>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <form>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <input type="text" class="form-control" placeholder="Your Name" required>
                                </div>
                                <div class="col-md-6">
                                    <input type="email" class="form-control" placeholder="Your Email" required>
                                </div>
                                <div class="col-md-6">
                                    <input type="tel" class="form-control" placeholder="Phone Number" required>
                                </div>
                                <div class="col-md-6">
                                    <select class="form-select" required>
                                        <option value="">Select Service</option>
                                        <option value="buy">Buy Property</option>
                                        <option value="sell">Sell Property</option>
                                        <option value="consultation">Consultation</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <textarea class="form-control" rows="4" placeholder="Your Message" required></textarea>
                                </div>
                                <div class="col-12 text-center">
                                    <button type="submit" class="btn btn-primary btn-lg px-5">Send Message</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
