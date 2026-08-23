<!-- Hero Section -->
<section class="page-hero" class="style-88494">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
            <h1 class="display-4 fw-bold text-white mb-4">
                <i class="fas fa-handshake me-3"></i>
                <?php echo __('our_services'); ?>
            </h1>
            <p class="lead text-white-50 mb-4">
                <?php echo __('services_hero_subtitle'); ?>
            </p>
                <?php if (!empty($pageContent)): ?>
                <div class="cms-content text-white mt-4 mb-4 p-4 bg-white bg-opacity-10 rounded"><?php echo e($pageContent); ?></div>
                <?php endif; ?>
                <div class="hero-stats">
                    <div class="row g-4">
                        <div class="col-md-3">
                            <div class="stat-item">
                                <div class="stat-number">500+</div>
                                <div class="stat-label"><?php echo __('properties_sold'); ?></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-item">
                                <div class="stat-number">1000+</div>
                                <div class="stat-label"><?php echo __('happy_clients'); ?></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-item">
                                <div class="stat-number">15+</div>
                                <div class="stat-label"><?php echo __('years_experience'); ?></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-item">
                                <div class="stat-number">24/7</div>
                                <div class="stat-label"><?php echo __('support'); ?></div>
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
                            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($crumb['title'] ?? '') ?></li>
                        <?php else: ?>
                            <li class="breadcrumb-item"><a href="<?= $crumb['url'] ?>"><?= htmlspecialchars($crumb['title'] ?? '') ?></a></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><?php echo __('home'); ?></a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo __('services'); ?></li>
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
                    <?php echo __('what_we_offer'); ?>
                </h2>
                <p class="section-subtitle">
                    <?php echo __('what_we_offer_subtitle'); ?>
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
                                <i class="fas fa-phone me-2"></i><?php echo __('contact_us'); ?>
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
                        <h4 class="service-title mb-3"><?php echo __('property_sales'); ?></h4>
                        <p class="service-description mb-4">
                            <?php echo __('property_sales_desc'); ?>
                        </p>
                        <div class="service-features">
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i><?php echo __('residential_properties'); ?></li>
                                <li><i class="fas fa-check text-success me-2"></i><?php echo __('commercial_properties'); ?></li>
                                <li><i class="fas fa-check text-success me-2"></i><?php echo __('investment_properties'); ?></li>
                                <li><i class="fas fa-check text-success me-2"></i><?php echo __('expert_negotiation'); ?></li>
                            </ul>
                        </div>
                        <a href="<?php echo BASE_URL; ?>/properties" class="btn btn-primary mt-3">
                            <i class="fas fa-search me-2"></i><?php echo __('browse_properties'); ?>
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
                    <?php echo __('our_process'); ?>
                </h3>
                <p class="section-subtitle">
                    <?php echo __('our_process_subtitle'); ?>
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
                                <h5 class="step-title"><?php echo __('process_consultation'); ?></h5>
                                <p class="step-description">
                                    <?php echo __('process_consultation_desc'); ?>
                                </p>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="process-step text-center">
                                <div class="step-number mb-3">
                                    <span class="badge bg-primary rounded-circle p-3 fs-5">2</span>
                                </div>
                                <h5 class="step-title"><?php echo __('process_search'); ?></h5>
                                <p class="step-description">
                                    <?php echo __('process_search_desc'); ?>
                                </p>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="process-step text-center">
                                <div class="step-number mb-3">
                                    <span class="badge bg-primary rounded-circle p-3 fs-5">3</span>
                                </div>
                                <h5 class="step-title"><?php echo __('process_viewings'); ?></h5>
                                <p class="step-description">
                                    <?php echo __('process_viewings_desc'); ?>
                                </p>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="process-step text-center">
                                <div class="step-number mb-3">
                                    <span class="badge bg-primary rounded-circle p-3 fs-5">4</span>
                                </div>
                                <h5 class="step-title"><?php echo __('process_closing'); ?></h5>
                                <p class="step-description">
                                    <?php echo __('process_closing_desc'); ?>
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
                    <?php echo __('why_choose_us'); ?>
                </h3>
                <p class="section-subtitle">
                    <?php echo __('why_choose_us_subtitle'); ?>
                </p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4 text-center">
                <div class="feature-card">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-shield-alt fa-3x text-primary"></i>
                    </div>
                    <h5><?php echo __('trusted_reliable'); ?></h5>
                    <p class="text-muted">
                        <?php echo __('trusted_reliable_desc'); ?>
                    </p>
                </div>
            </div>

            <div class="col-lg-4 text-center">
                <div class="feature-card">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-search fa-3x text-success"></i>
                    </div>
                    <h5><?php echo __('extensive_network'); ?></h5>
                    <p class="text-muted">
                        <?php echo __('extensive_network_desc'); ?>
                    </p>
                </div>
            </div>

            <div class="col-lg-4 text-center">
                <div class="feature-card">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-headset fa-3x text-warning"></i>
                    </div>
                    <h5><?php echo __('support_247'); ?></h5>
                    <p class="text-muted">
                        <?php echo __('support_247_desc'); ?>
                    </p>
                </div>
            </div>

            <div class="col-lg-4 text-center">
                <div class="feature-card">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-hand-holding-heart fa-3x text-info"></i>
                    </div>
                    <h5><?php echo __('personalized_service'); ?></h5>
                    <p class="text-muted">
                        <?php echo __('personalized_service_desc'); ?>
                    </p>
                </div>
            </div>

            <div class="col-lg-4 text-center">
                <div class="feature-card">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-chart-line fa-3x text-danger"></i>
                    </div>
                    <h5><?php echo __('market_expertise'); ?></h5>
                    <p class="text-muted">
                        <?php echo __('market_expertise_desc'); ?>
                    </p>
                </div>
            </div>

            <div class="col-lg-4 text-center">
                <div class="feature-card">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-thumbs-up fa-3x text-secondary"></i>
                    </div>
                    <h5><?php echo __('proven_results'); ?></h5>
                    <p class="text-muted">
                        <?php echo __('proven_results_desc'); ?>
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
                        <h3 class="text-center mb-2">
                            <i class="fas fa-paper-plane text-primary me-2"></i>
                            <?php echo __('enquire_services'); ?>
                        </h3>
                        <p class="text-center text-muted small mb-4">
                            <i class="fas fa-shield-alt text-success me-1"></i>500+ Properties Sold
                            <span class="mx-2">|</span>
                            <i class="fas fa-users text-primary me-1"></i>1000+ Happy Clients
                            <span class="mx-2">|</span>
                            <i class="fas fa-clock text-warning me-1"></i>We respond within 24 hours
                        </p>
                        <form id="serviceInterestForm">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                            <input type="hidden" name="utm_source" value="<?= htmlspecialchars($_GET['utm_source'] ?? $_SESSION['utm_source'] ?? '') ?>">
                            <input type="hidden" name="utm_medium" value="<?= htmlspecialchars($_GET['utm_medium'] ?? $_SESSION['utm_medium'] ?? '') ?>">
                            <input type="hidden" name="utm_campaign" value="<?= htmlspecialchars($_GET['utm_campaign'] ?? $_SESSION['utm_campaign'] ?? '') ?>">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label"><?php echo __('your_name'); ?> *</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="phone" class="form-label"><?php echo __('phone'); ?> *</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label"><?php echo __('email'); ?> *</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="service_type" class="form-label"><?php echo __('service_required'); ?> *</label>
                                    <select class="form-select" id="service_type" name="service_type" required>
                                        <option value=""><?php echo __('select_service'); ?></option>
                                        <option value="home_loan"><?php echo __('service_home_loan'); ?></option>
                                        <option value="legal"><?php echo __('subject_legal'); ?></option>
                                        <option value="registry"><?php echo __('service_registry'); ?></option>
                                        <option value="mutation"><?php echo __('service_mutation'); ?></option>
                                        <option value="interior"><?php echo __('subject_interior'); ?></option>
                                        <option value="construction"><?php echo __('service_construction'); ?></option>
                                        <option value="rental_agreement"><?php echo __('service_rental_agreement'); ?></option>
                                        <option value="property_tax"><?php echo __('service_property_tax'); ?></option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label for="message" class="form-label"><?php echo __('additional_details'); ?></label>
                                    <textarea class="form-control" id="message" name="message" rows="3" placeholder="<?php echo __('service_message_placeholder'); ?>"></textarea>
                                </div>
                                <div class="col-12">
                                    <div id="serviceFormMessage" class="alert d-none"></div>
                                    <button type="submit" class="btn btn-primary w-100" id="submitBtn">
                                        <i class="fas fa-paper-plane me-2"></i><?php echo __('submit_inquiry'); ?>
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
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span><?php echo __('services_sending'); ?>';
    
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
        submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>' + '<?php echo __('submit_inquiry'); ?>';
        messageDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
    })
    .catch(error => {
        messageDiv.classList.remove('d-none');
        messageDiv.classList.remove('alert-success');
        messageDiv.classList.add('alert-danger');
        messageDiv.textContent = '<?php echo __('something_wrong'); ?>';
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>' + '<?php echo __('submit_inquiry'); ?>';
    });
});
</script>

<script>
(function() {
    const params = ['utm_source','utm_medium','utm_campaign','utm_term','utm_content'];
    params.forEach(p => {
        const val = new URLSearchParams(window.location.search).get(p);
        if (val) {
            document.querySelectorAll(`input[name="${p}"]`).forEach(el => el.value = val);
            try { sessionStorage.setItem(p, val); } catch (e) { console.error("Error:", e); }
        }
    });
})();
</script>

<!-- Call to Action Section -->
<section class="cta-section py-5 cta-section-success">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h3 class="text-white mb-4">
                    <i class="fas fa-rocket me-2"></i>
                    <?php echo __('cta_title'); ?>
                </h3>
                <p class="text-white-50 mb-4">
                    <?php echo __('cta_services_subtitle'); ?>
                </p>
                <div class="cta-buttons">
                    <a href="<?php echo BASE_URL; ?>/contact" class="btn btn-light btn-lg me-3">
                        <i class="fas fa-phone me-2"></i><?php echo __('contact_us'); ?>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/properties" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-search me-2"></i><?php echo __('browse_properties'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>