<?php

/**
 * Interior Design Services Page - APS Dream Homes
 * Professional interior design services for real estate
 */

try {
    // These variables should be passed from the controller
    // Mock data for now
    $services = $services ?? [];
    $portfolio = $portfolio ?? [];
    $team_members = $team_members ?? [];
    $testimonials = $testimonials ?? [];
    $faqs = $faqs ?? [];

} catch (Exception $e) {
    error_log('Interior design page database error: ' . $e->getMessage());
    $services = [];
    $portfolio = [];
    $team_members = [];
    $testimonials = [];
    $faqs = [];
}
?>

<!-- Hero Section -->
<section class="interior-hero">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-4" data-aos="fade-up">Transform Your Space</h1>
                <p class="lead mb-4" data-aos="fade-up" data-aos-delay="100">
                    Professional interior design services tailored to your style and needs, creating beautiful and functional environments.
                </p>
                <div class="d-flex gap-3 justify-content-center flex-wrap" data-aos="fade-up" data-aos-delay="200">
                    <a href="#contact-form" class="btn btn-light btn-lg">
                        <i class="fas fa-palette me-2"></i>Get Started
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
            <li class="breadcrumb-item active" aria-current="page">Interior Design</li>
        </ol>
    </div>
</nav>

<!-- Services Section -->
<section id="services" class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="display-5 fw-bold mb-3" data-aos="fade-up">Our Interior Design Services</h2>
                <p class="lead text-muted" data-aos="fade-up" data-aos-delay="100">
                    Comprehensive interior design solutions for every space and style
                </p>
            </div>
        </div>

        <?php if (!empty($services)): ?>
            <div class="row g-4">
                <?php foreach ($services as $service): ?>
                    <div class="col-lg-4 col-md-6" data-aos="fade-up">
                        <div class="service-card">
                            <div class="service-icon">
                                <i class="<?php echo htmlspecialchars($service['icon'] ?? 'fas fa-palette'); ?>"></i>
                            </div>
                            <h3 class="h4 fw-bold mb-3"><?php echo htmlspecialchars($service['title'] ?? 'Design Service'); ?></h3>
                            <p class="text-muted mb-4"><?php echo htmlspecialchars($service['description'] ?? 'Professional interior design service tailored to your needs.'); ?></p>

                            <?php if (!empty($service['features'])): ?>
                                <?php $features = json_decode($service['features'], true); ?>
                                <?php if (is_array($features) && !empty($features)): ?>
                                    <ul class="list-unstyled">
                                        <?php foreach ($features as $feature): ?>
                                            <li class="mb-2">
                                                <i class="fas fa-check text-success me-2"></i>
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
                    <i class="fas fa-palette fa-4x text-muted mb-4"></i>
                    <h3 class="text-muted">Interior Design Services Coming Soon</h3>
                    <p class="text-muted mb-4">
                        We're currently expanding our interior design services. Please contact us for immediate design consultation.
                    </p>
                    <a href="contact.php" class="btn btn-primary">Contact Us</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
