<?php
/**
 * Services Page Template
 * Beautiful services page showcasing real estate services
 */

// Set page title and description for layout
$page_title = 'Our Services - APS Dream Home';
$page_description = 'Discover our comprehensive range of real estate services designed to help you find your perfect property';

?>

<!-- Hero Section -->
<section class="services-hero py-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h1 class="display-4 fw-bold text-white mb-4">
                    <i class="fas fa-handshake me-3"></i>
                    Our Services
                </h1>
                <p class="lead text-white-50 mb-4">
                    Discover our comprehensive range of real estate services designed to help you find your perfect property or sell your current one with ease.
                </p>
                <div class="hero-stats">
                    <div class="row g-4">
                        <div class="col-md-3">
                            <div class="stat-item">
                                <div class="stat-number">500+</div>
                                <div class="stat-label">Properties Sold</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-item">
                                <div class="stat-number">1000+</div>
                                <div class="stat-label">Happy Clients</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-item">
                                <div class="stat-number">15+</div>
                                <div class="stat-label">Years Experience</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-item">
                                <div class="stat-number">24/7</div>
                                <div class="stat-label">Support</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="services-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="section-title">
                    <i class="fas fa-star text-primary me-2"></i>
                    What We Offer
                </h2>
                <p class="section-subtitle">
                    Professional real estate services tailored to your needs
                </p>
            </div>
        </div>

        <div class="row g-4">
            <!-- Property Sales -->
            <div class="col-lg-4 col-md-6">
                <div class="service-card">
                    <div class="service-icon mb-4">
                        <i class="fas fa-home fa-3x text-primary"></i>
                    </div>
                    <h4 class="service-title mb-3">Property Sales</h4>
                    <p class="service-description mb-4">
                        Find your dream home from our extensive collection of residential properties. We help you navigate the buying process with expert guidance every step of the way.
                    </p>
                    <div class="service-features">
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i>Residential Properties</li>
                            <li><i class="fas fa-check text-success me-2"></i>Commercial Properties</li>
                            <li><i class="fas fa-check text-success me-2"></i>Investment Properties</li>
                            <li><i class="fas fa-check text-success me-2"></i>Expert Negotiation</li>
                        </ul>
                    </div>
                    <a href="<?php echo BASE_URL; ?>properties" class="btn btn-primary mt-3">
                        <i class="fas fa-search me-2"></i>Browse Properties
                    </a>
                </div>
            </div>

            <!-- Property Rentals -->
            <div class="col-lg-4 col-md-6">
                <div class="service-card">
                    <div class="service-icon mb-4">
                        <i class="fas fa-key fa-3x text-success"></i>
                    </div>
                    <h4 class="service-title mb-3">Property Rentals</h4>
                    <p class="service-description mb-4">
                        Discover rental properties that match your lifestyle and budget. From apartments to luxury villas, we have options for every need.
                    </p>
                    <div class="service-features">
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i>Short-term Rentals</li>
                            <li><i class="fas fa-check text-success me-2"></i>Long-term Rentals</li>
                            <li><i class="fas fa-check text-success me-2"></i>Furnished Options</li>
                            <li><i class="fas fa-check text-success me-2"></i>Lease Management</li>
                        </ul>
                    </div>
                    <a href="<?php echo BASE_URL; ?>properties?type=rental" class="btn btn-success mt-3">
                        <i class="fas fa-key me-2"></i>Find Rentals
                    </a>
                </div>
            </div>

            <!-- Property Management -->
            <div class="col-lg-4 col-md-6">
                <div class="service-card">
                    <div class="service-icon mb-4">
                        <i class="fas fa-building fa-3x text-warning"></i>
                    </div>
                    <h4 class="service-title mb-3">Property Management</h4>
                    <p class="service-description mb-4">
                        Let us handle the day-to-day management of your rental properties. We take care of everything from tenant screening to maintenance.
                    </p>
                    <div class="service-features">
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i>Tenant Screening</li>
                            <li><i class="fas fa-check text-success me-2"></i>Rent Collection</li>
                            <li><i class="fas fa-check text-success me-2"></i>Maintenance Coordination</li>
                            <li><i class="fas fa-check text-success me-2"></i>Legal Compliance</li>
                        </ul>
                    </div>
                    <a href="<?php echo BASE_URL; ?>contact" class="btn btn-warning mt-3">
                        <i class="fas fa-phone me-2"></i>Contact Us
                    </a>
                </div>
            </div>

            <!-- Real Estate Investment -->
            <div class="col-lg-4 col-md-6">
                <div class="service-card">
                    <div class="service-icon mb-4">
                        <i class="fas fa-chart-line fa-3x text-info"></i>
                    </div>
                    <h4 class="service-title mb-3">Investment Advisory</h4>
                    <p class="service-description mb-4">
                        Get expert advice on real estate investments. We help you identify profitable opportunities and maximize your returns.
                    </p>
                    <div class="service-features">
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i>Market Analysis</li>
                            <li><i class="fas fa-check text-success me-2"></i>ROI Calculations</li>
                            <li><i class="fas fa-check text-success me-2"></i>Portfolio Management</li>
                            <li><i class="fas fa-check text-success me-2"></i>Risk Assessment</li>
                        </ul>
                    </div>
                    <a href="<?php echo BASE_URL; ?>contact" class="btn btn-info mt-3">
                        <i class="fas fa-chart-bar me-2"></i>Get Advice
                    </a>
                </div>
            </div>

            <!-- Legal Services -->
            <div class="col-lg-4 col-md-6">
                <div class="service-card">
                    <div class="service-icon mb-4">
                        <i class="fas fa-gavel fa-3x text-danger"></i>
                    </div>
                    <h4 class="service-title mb-3">Legal Services</h4>
                    <p class="service-description mb-4">
                        Comprehensive legal support for all your real estate transactions. We ensure everything is handled legally and efficiently.
                    </p>
                    <div class="service-features">
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i>Contract Review</li>
                            <li><i class="fas fa-check text-success me-2"></i>Title Verification</li>
                            <li><i class="fas fa-check text-success me-2"></i>Registration Assistance</li>
                            <li><i class="fas fa-check text-success me-2"></i>Dispute Resolution</li>
                        </ul>
                    </div>
                    <a href="<?php echo BASE_URL; ?>contact" class="btn btn-danger mt-3">
                        <i class="fas fa-balance-scale me-2"></i>Legal Help
                    </a>
                </div>
            </div>

            <!-- Interior Design -->
            <div class="col-lg-4 col-md-6">
                <div class="service-card">
                    <div class="service-icon mb-4">
                        <i class="fas fa-palette fa-3x text-secondary"></i>
                    </div>
                    <h4 class="service-title mb-3">Interior Design</h4>
                    <p class="service-description mb-4">
                        Transform your property with our expert interior design services. We create beautiful, functional spaces that reflect your style.
                    </p>
                    <div class="service-features">
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i>Space Planning</li>
                            <li><i class="fas fa-check text-success me-2"></i>Color Consultation</li>
                            <li><i class="fas fa-check text-success me-2"></i>Furniture Selection</li>
                            <li><i class="fas fa-check text-success me-2"></i>3D Visualization</li>
                        </ul>
                    </div>
                    <a href="<?php echo BASE_URL; ?>contact" class="btn btn-secondary mt-3">
                        <i class="fas fa-magic me-2"></i>Design Help
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Process Section -->
<section class="process-section py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h3 class="section-title">
                    <i class="fas fa-cogs text-primary me-2"></i>
                    Our Process
                </h3>
                <p class="section-subtitle">
                    Simple, transparent process from start to finish
                </p>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="process-timeline">
                    <div class="row g-4">
                        <div class="col-md-3">
                            <div class="process-step text-center">
                                <div class="step-number mb-3">
                                    <span class="badge bg-primary rounded-circle p-3 fs-5">1</span>
                                </div>
                                <h5 class="step-title">Consultation</h5>
                                <p class="step-description">
                                    We discuss your requirements and preferences to understand exactly what you're looking for.
                                </p>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="process-step text-center">
                                <div class="step-number mb-3">
                                    <span class="badge bg-primary rounded-circle p-3 fs-5">2</span>
                                </div>
                                <h5 class="step-title">Property Search</h5>
                                <p class="step-description">
                                    We search our extensive database and network to find properties that match your criteria.
                                </p>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="process-step text-center">
                                <div class="step-number mb-3">
                                    <span class="badge bg-primary rounded-circle p-3 fs-5">3</span>
                                </div>
                                <h5 class="step-title">Viewings & Selection</h5>
                                <p class="step-description">
                                    Schedule viewings and help you evaluate properties to make the best choice.
                                </p>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="process-step text-center">
                                <div class="step-number mb-3">
                                    <span class="badge bg-primary rounded-circle p-3 fs-5">4</span>
                                </div>
                                <h5 class="step-title">Closing</h5>
                                <p class="step-description">
                                    Handle all paperwork, negotiations, and legal processes to complete your transaction.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="why-choose-us py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h3 class="section-title">
                    <i class="fas fa-award text-primary me-2"></i>
                    Why Choose APS Dream Home?
                </h3>
                <p class="section-subtitle">
                    Trusted by thousands of clients for exceptional service
                </p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4 text-center">
                <div class="feature-card">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-shield-alt fa-3x text-primary"></i>
                    </div>
                    <h5>Trusted & Reliable</h5>
                    <p class="text-muted">
                        Licensed and regulated real estate professionals with years of experience in the market.
                    </p>
                </div>
            </div>

            <div class="col-lg-4 text-center">
                <div class="feature-card">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-search fa-3x text-success"></i>
                    </div>
                    <h5>Extensive Network</h5>
                    <p class="text-muted">
                        Access to exclusive properties and off-market deals that you won't find elsewhere.
                    </p>
                </div>
            </div>

            <div class="col-lg-4 text-center">
                <div class="feature-card">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-headset fa-3x text-warning"></i>
                    </div>
                    <h5>24/7 Support</h5>
                    <p class="text-muted">
                        Our dedicated support team is available round the clock to assist you at every step.
                    </p>
                </div>
            </div>

            <div class="col-lg-4 text-center">
                <div class="feature-card">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-hand-holding-heart fa-3x text-info"></i>
                    </div>
                    <h5>Personalized Service</h5>
                    <p class="text-muted">
                        We understand that every client is unique and provide personalized solutions for your needs.
                    </p>
                </div>
            </div>

            <div class="col-lg-4 text-center">
                <div class="feature-card">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-chart-line fa-3x text-danger"></i>
                    </div>
                    <h5>Market Expertise</h5>
                    <p class="text-muted">
                        Deep understanding of local markets and trends to help you make informed decisions.
                    </p>
                </div>
            </div>

            <div class="col-lg-4 text-center">
                <div class="feature-card">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-thumbs-up fa-3x text-secondary"></i>
                    </div>
                    <h5>Proven Results</h5>
                    <p class="text-muted">
                        Track record of successful transactions and satisfied clients speaks for our quality.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Section -->
<section class="cta-section py-5" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h3 class="text-white mb-4">
                    <i class="fas fa-rocket me-2"></i>
                    Ready to Find Your Dream Property?
                </h3>
                <p class="text-white-50 mb-4">
                    Contact us today and let our expert team help you find the perfect property or sell your current one.
                </p>
                <div class="cta-buttons">
                    <a href="<?php echo BASE_URL; ?>contact" class="btn btn-light btn-lg me-3">
                        <i class="fas fa-phone me-2"></i>Contact Us
                    </a>
                    <a href="<?php echo BASE_URL; ?>properties" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-search me-2"></i>Browse Properties
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.service-card {
    background: white;
    border-radius: 15px;
    padding: 2rem;
    text-align: center;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    height: 100%;
}

.service-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}

.service-icon {
    margin-bottom: 1.5rem;
}

.service-title {
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 1rem;
}

.service-description {
    color: #6c757d;
    line-height: 1.6;
    margin-bottom: 1.5rem;
}

.service-features ul li {
    padding: 0.25rem 0;
    color: #495057;
}

.stat-item {
    text-align: center;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: bold;
    color: white;
    margin-bottom: 0.5rem;
}

.stat-label {
    color: rgba(255, 255, 255, 0.8);
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.process-step {
    padding: 1.5rem;
}

.step-number {
    display: inline-block;
}

.step-title {
    color: #2c3e50;
    margin-bottom: 1rem;
}

.step-description {
    color: #6c757d;
    font-size: 0.95rem;
}

.process-timeline {
    position: relative;
}

.process-timeline::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 2px;
    background: #e9ecef;
    z-index: 1;
}

.process-step {
    position: relative;
    z-index: 2;
}

.feature-card {
    padding: 2rem 1rem;
    height: 100%;
}

.feature-icon {
    margin-bottom: 1rem;
}

.feature-card h5 {
    color: #2c3e50;
    margin-bottom: 1rem;
}

.cta-buttons .btn {
    padding: 0.75rem 2rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

@media (max-width: 768px) {
    .stat-number {
        font-size: 2rem;
    }

    .service-card {
        padding: 1.5rem;
    }

    .process-timeline::before {
        display: none;
    }
}
</style>
