<!-- Hero Section -->
<section class="services-hero py-5" style="background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('<?= get_asset_url('assets/images/hero-1.jpg') ?>'); background-size: cover; background-position: center;">
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

<!-- Breadcrumb -->
<div class="bg-light py-2">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <?php if (isset($breadcrumbs)): ?>
                    <?php foreach ($breadcrumbs as $crumb): ?>
                        <?php if (empty($crumb['url']) || $crumb === end($breadcrumbs)): ?>
                            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($crumb['title']) ?></li>
                        <?php else: ?>
                            <li class="breadcrumb-item"><a href="<?= $crumb['url'] ?>"><?= htmlspecialchars($crumb['title']) ?></a></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Services</li>
                <?php endif; ?>
            </ol>
        </nav>
    </div>
</div>

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
            <?php if (!empty($services)): ?>
                <?php foreach ($services as $service): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="service-card">
                            <div class="service-icon mb-4">
                                <i class="<?php echo htmlspecialchars($service->icon ?? 'fas fa-check'); ?> fa-3x text-<?php echo htmlspecialchars($service->color ?? 'primary'); ?>"></i>
                            </div>
                            <h4 class="service-title mb-3"><?php echo htmlspecialchars($service->title); ?></h4>
                            <p class="service-description mb-4">
                                <?php echo htmlspecialchars($service->description ?? ''); ?>
                            </p>
                            <?php if (isset($service->features) && !empty($service->features)): ?>
                                <div class="service-features">
                                    <ul class="list-unstyled">
                                        <?php foreach (explode(',', $service->features) as $feature): ?>
                                            <li><i class="fas fa-check text-success me-2"></i><?php echo htmlspecialchars(trim($feature)); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            <a href="<?php echo BASE_URL; ?>/contact" class="btn btn-<?php echo htmlspecialchars($service->color ?? 'primary'); ?> mt-3">
                                <i class="fas fa-phone me-2"></i>Contact Us
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Fallback or static services if no dynamic data -->
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
                        <a href="<?php echo BASE_URL; ?>/properties" class="btn btn-primary mt-3">
                            <i class="fas fa-search me-2"></i>Browse Properties
                        </a>
                    </div>
                </div>
            <?php endif; ?>
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

<!-- Service Inquiry Form -->
<section class="py-5 bg-light" id="service-inquiry">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow">
                    <div class="card-body p-5">
                        <h3 class="text-center mb-4">
                            <i class="fas fa-paper-plane text-primary me-2"></i>
                            Enquire About Our Services
                        </h3>
                        <form id="serviceInterestForm">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Your Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Phone Number *</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="service_type" class="form-label">Service Required *</label>
                                    <select class="form-select" id="service_type" name="service_type" required>
                                        <option value="">Select a service</option>
                                        <option value="home_loan">Home Loan Assistance</option>
                                        <option value="legal">Legal Services</option>
                                        <option value="registry">Registry / Transfer</option>
                                        <option value="mutation">Mutation</option>
                                        <option value="interior">Interior Design</option>
                                        <option value="rental_agreement">Rental Agreement</option>
                                        <option value="property_tax">Property Tax</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label for="message" class="form-label">Additional Details</label>
                                    <textarea class="form-control" id="message" name="message" rows="3" placeholder="Tell us more about your requirements..."></textarea>
                                </div>
                                <div class="col-12">
                                    <div id="serviceFormMessage" class="alert d-none"></div>
                                    <button type="submit" class="btn btn-primary w-100" id="submitBtn">
                                        <i class="fas fa-paper-plane me-2"></i>Submit Inquiry
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.getElementById('serviceInterestForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const submitBtn = document.getElementById('submitBtn');
    const messageDiv = document.getElementById('serviceFormMessage');
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';
    
    const formData = new FormData(form);
    
    fetch('<?php echo BASE_URL; ?>/service-interest', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        messageDiv.classList.remove('d-none', 'alert-danger', 'alert-success');
        messageDiv.classList.add(data.success ? 'alert-success' : 'alert-danger');
        messageDiv.textContent = data.message;
        
        if (data.success) {
            form.reset();
        }
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Submit Inquiry';
        messageDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
    })
    .catch(error => {
        messageDiv.classList.remove('d-none');
        messageDiv.classList.remove('alert-success');
        messageDiv.classList.add('alert-danger');
        messageDiv.textContent = 'Something went wrong. Please try again.';
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Submit Inquiry';
    });
});
</script>

<!-- Call to Action Section -->
<section class="cta-section py-5 cta-section-success">
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
                    <a href="<?php echo BASE_URL; ?>/contact" class="btn btn-light btn-lg me-3">
                        <i class="fas fa-phone me-2"></i>Contact Us
                    </a>
                    <a href="<?php echo BASE_URL; ?>/properties" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-search me-2"></i>Browse Properties
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>