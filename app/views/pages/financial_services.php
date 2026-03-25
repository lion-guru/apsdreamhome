<?php

/**
 * Financial Services Page - APS Dream Homes
 * Professional financial services for real estate
 */

try {
    // These variables should be passed from the controller
    // Mock data for now
    $services = $services ?? [];
    $advisors = $advisors ?? [];
    $faqs = $faqs ?? [];

} catch (Exception $e) {
    error_log('Financial services page database error: ' . $e->getMessage());
    $services = [];
    $advisors = [];
    $faqs = [];
}
?>

<!-- Hero Section -->
<section class="financial-hero">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-4" data-aos="fade-up">Financial Services</h1>
                <p class="lead mb-4" data-aos="fade-up" data-aos-delay="100">
                    Expert financial solutions for your real estate investments with comprehensive mortgage and financing options.
                </p>
                <div class="d-flex gap-3 justify-content-center flex-wrap" data-aos="fade-up" data-aos-delay="200">
                    <a href="#contact-form" class="btn btn-light btn-lg">
                        <i class="fas fa-coins me-2"></i>Get Financial Advice
                    </a>
                    <a href="#services" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-list me-2"></i>Our Services
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Breadcrumb -->
<nav class="bg-light border-bottom py-2" aria-label="breadcrumb">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="services.php">Services</a></li>
            <li class="breadcrumb-item active" aria-current="page">Financial Services</li>
        </ol>
    </div>
</nav>

<!-- Services Section -->
<section id="services" class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="display-5 fw-bold mb-3" data-aos="fade-up">Our Financial Services</h2>
                <p class="lead text-muted" data-aos="fade-up" data-aos-delay="100">
                    Comprehensive financial solutions for real estate investments and property financing
                </p>
            </div>
        </div>

        <?php if (!empty($services)): ?>
            <div class="row g-4">
                <?php foreach ($services as $service): ?>
                    <div class="col-lg-4 col-md-6" data-aos="fade-up">
                        <div class="service-card">
                            <div class="service-icon">
                                <i class="<?php echo htmlspecialchars($service['icon'] ?? 'fas fa-coins'); ?>"></i>
                            </div>
                            <h3 class="h4 fw-bold mb-3"><?php echo htmlspecialchars($service['title'] ?? 'Financial Service'); ?></h3>
                            <p class="text-muted mb-4"><?php echo htmlspecialchars($service['description'] ?? 'Professional financial assistance for your real estate needs.'); ?></p>

                            <?php if (!empty($service['features'])): ?>
                                <?php $features = json_decode($service['features'], true); ?>
                                <?php if (is_array($features) && !empty($features)): ?>
                                    <ul class="service-features">
                                        <?php foreach ($features as $feature): ?>
                                            <li>
                                                <i class="fas fa-check text-success"></i>
                                                <?php echo htmlspecialchars($feature); ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <div class="empty-state">
                    <i class="fas fa-coins fa-4x text-muted mb-4"></i>
                    <h3 class="text-muted">Financial Services Coming Soon</h3>
                    <p class="text-muted mb-4">
                        We're currently expanding our financial services. Please contact us for immediate financial assistance.
                    </p>
                    <a href="contact.php" class="btn btn-primary">Contact Us</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
