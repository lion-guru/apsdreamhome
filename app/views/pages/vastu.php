<?php
$page_title = __('vastu_title') . ' - APS Dream Home';
$page_description = __('vastu_meta_desc');
?>
<!-- Hero Section -->
<section class="hero-section text-white text-center py-5 style-2141">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-4" data-aos="fade-up"><?= __('vastu_hero_title') ?></h1>
                <p class="lead mb-4" data-aos="fade-up" data-aos-delay="100">
                    <?= __('vastu_hero_desc') ?>
                </p>
                <div class="d-flex gap-3 mt-4 justify-content-center">
                    <a href="<?= BASE_URL ?>/contact" class="btn btn-light btn-lg px-4 py-3">
                        <i class="fas fa-phone me-2"></i><?= __('vastu_cta_consult') ?>
                    </a>
                    <a href="<?= BASE_URL ?>/colonies" class="btn btn-outline-light btn-lg px-4 py-3">
                        <i class="fas fa-building me-2"></i><?= __('vastu_cta_properties') ?>
                    </a>
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
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><?= __('nav.menu.home') ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= __('vastu_title') ?></li>
            </ol>
        </nav>
    </div>
</div>

<!-- Introduction -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6" data-aos="fade-right">
                <img src="<?= get_asset_url('assets/images/vastu.jpg') ?>" alt="<?= __('vastu_title') ?>" class="img-fluid rounded-4 shadow">
            </div>
            <div class="col-lg-6" data-aos="fade-left" data-aos-delay="100">
                <h2 class="mb-4"><?= __('vastu_intro_title') ?></h2>
                <p class="lead text-muted mb-4"><?= __('vastu_intro_desc') ?></p>
                <p><?= __('vastu_intro_text') ?></p>
            </div>
        </div>
    </div>
</section>

<!-- Vastu Principles -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="section-title mb-3"><?= __('vastu_principles_title') ?></h2>
            <p class="section-subtitle"><?= __('vastu_principles_desc') ?></p>
        </div>
        <div class="row g-4">
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="icon-circle bg-primary bg-opacity-10 text-primary mx-auto mb-3 style-84502">
                            <i class="fas fa-compass display-5"></i>
                        </div>
                        <h4><?= __('vastu_direction_title') ?></h4>
                        <p class="text-muted"><?= __('vastu_direction_desc') ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="icon-circle bg-primary bg-opacity-10 text-primary mx-auto mb-3 style-84502">
                            <i class="fas fa-home display-5"></i>
                        </div>
                        <h4><?= __('vastu_plot_title') ?></h4>
                        <p class="text-muted"><?= __('vastu_plot_desc') ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="icon-circle bg-primary bg-opacity-10 text-primary mx-auto mb-3 style-84502">
                            <i class="fas fa-balance-scale display-5"></i>
                        </div>
                        <h4><?= __('vastu_5_elements_title') ?></h4>
                        <p class="text-muted"><?= __('vastu_5_elements_desc') ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Colony Applications -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="section-title mb-3"><?= __('vastu_colonies_title') ?></h2>
            <p class="section-subtitle"><?= __('vastu_colonies_desc') ?></p>
        </div>
        <div class="row g-4">
            <?php if (isset($colonies) && !empty($colonies)): ?>
                <?php foreach ($colonies as $colony): ?>
                <div class="col-md-6 col-lg-3" data-aos="fade-up">
                    <div class="card h-100 border-0 shadow-sm overflow-hidden">
                        <div class="position-relative">
                            <?php $imgRaw = $colony['image_path'] ?? 'assets/images/default-banner.jpg';
                                  $imgSrc = (str_starts_with($imgRaw, 'http://') || str_starts_with($imgRaw, 'https://')) ? $imgRaw : BASE_URL . '/' . $imgRaw; ?>
                            <img src="<?= htmlspecialchars($imgSrc ?? '') ?>" alt="<?= htmlspecialchars($colony['name'] ?? '') ?>" class="card-img-top style-58348">
                            <span class="badge bg-warning text-dark position-absolute top-0 end-0 m-2"><?= $colony['completion_status'] ?? '' ?></span>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($colony['name'] ?? '') ?></h5>
                            <p class="text-muted small mb-2"><?= htmlspecialchars($colony['location'] ?? '') ?></p>
                            <p class="fw-bold text-primary mb-0"><?= !empty($colony['starting_price']) ? '₹' . number_format($colony['starting_price']) : 'Contact Us' ?></p>
                        </div>
                        <div class="card-footer bg-white border-0 text-center">
                            <a href="<?= BASE_URL ?>/colony/<?= htmlspecialchars($colony['slug'] ?? $colony['name'] ?? '') ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <i class="fas fa-project-diagram fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Our vastu-compliant colonies are coming soon.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-primary text-white text-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h2 class="fw-bold mb-3">Need Vastu Consultation?</h2>
                <p class="lead mb-4">Our Vastu experts can help you choose the perfect plot in our vastu-compliant colonies.</p>
                <a href="<?= BASE_URL ?>/contact" class="btn btn-light btn-lg px-5 py-3">
                    <i class="fas fa-phone me-2"></i>Schedule Consultation
                </a>
            </div>
        </div>
    </div>
</section>

<!-- FAQ -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title mb-3">Vastu FAQs</h2>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="vastuAccordion">
                    <div class="accordion-item border-0 mb-3 shadow-sm">
                        <h2 class="accordion-header" id="vastuHeading1">
                            <button class="accordion-button collapsed bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#vastuCollapse1">
                                Is Vastu necessary for property purchase?
                            </button>
                        </h2>
                        <div id="vastuCollapse1" class="accordion-collapse collapse" data-bs-parent="#vastuAccordion">
                            <div class="accordion-body">
                                While not mandatory, Vastu compliance brings peace, prosperity, and positive energy to your home and life.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item border-0 mb-3 shadow-sm">
                        <h2 class="accordion-header" id="vastuHeading2">
                            <button class="accordion-button collapsed bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#vastuCollapse2">
                                Can you modify an existing property for Vastu?
                            </button>
                        </h2>
                        <div id="vastuCollapse2" class="accordion-collapse collapse" data-bs-parent="#vastuAccordion">
                            <div class="accordion-body">
                                Yes, simple remedies like adjusting furniture placement, colors, and element balance can correct Vastu issues.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>