<?php
/**
 * Budha City Kushinagar Project View - APS Dream Homes
 */
?>

<!-- Page Header -->
<section class="project-hero-section section-padding bg-primary text-white text-center rounded-bottom-4 py-5" data-aos="fade-down">
    <div class="container py-4">
                        <h1 class="display-5 fw-bold mb-2"><?= __('budha_city_title') ?></h1>
                        <p class="lead mb-0"><?= __('budha_city_subtitle') ?></p>
    </div>
</section>

<!-- Breadcrumb -->
<nav class="bg-light border-bottom py-2">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <?php foreach ($breadcrumbs as $crumb): ?>
                <?php if (isset($crumb['url'])): ?>
                    <li class="breadcrumb-item"><a href="<?= $crumb['url'] ?>"><?= $crumb['title'] ?></a></li>
                <?php else: ?>
                    <li class="breadcrumb-item active"><?= $crumb['title'] ?></li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ol>
    </div>
</nav>

<div class="full-row bg-white py-5">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6 mb-4 mb-md-0" data-aos="fade-right">
                        <img src="<?= BASE_URL ?>/assets/images/placeholder/property.svg" class="img-fluid rounded-4 shadow-lg" alt="Budha City Overview" />
                    </div>
                    <div class="col-md-6" data-aos="fade-left">
                        <h2 class="text-secondary double-down-line mb-4"><?= __('budha_city_overview_heading') ?></h2>
                        <p class="lead"><b><?= __('budha_city_location_tag') ?></b></p>
                        <p><?= __('budha_city_overview_p1') ?></p>
                        <p class="fw-bold text-dark"><?= __('budha_city_overview_p2') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Amenities Section -->
        <div class="full-row bg-light py-5">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12 text-center mb-5">
                        <h2 class="text-secondary"><?= __('top_amenities') ?></h2>
                        <p class="text-muted"><?= __('top_amenities_desc') ?></p>
                    </div>
                </div>
                <div class="row g-4">
                    <?php if (!empty($amenities)): ?>
                        <?php foreach ($amenities as $amenity): ?>
                            <div class="col-md-3 col-sm-6" data-aos="zoom-in">
                                <div class="amenity-card bg-white p-4 rounded-4 shadow-sm text-center h-100 transition-hover">
                                    <img src="<?= BASE_URL ?>/assets/images/placeholder/property.svg" class="img-fluid mb-3 rounded-3" alt="<?= $amenity['alt_text'] ?>">
                                    <h5 class="mb-0"><?= $amenity['title'] ?></h5>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .project-hero-section {
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%) !important;
    }
    .transition-hover {
        transition: all 0.3s ease;
    }
    .transition-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
</style>
