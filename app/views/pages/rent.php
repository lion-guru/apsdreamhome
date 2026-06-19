<?php if (!isset($sc)) { $sc = function($k, $d='') { return $GLOBALS['_site_settings_cache'][$k] ?? $d; }; }$phoneRaw = preg_replace('/[^0-9]/', '', $sc('contact_whatsapp', '919277121112')); $phoneDisplay = $sc('contact_phone', '<?= $phoneDisplay ?>'); ?>
<!-- Hero Section -->
<section class="py-5 text-white" style="background: linear-gradient(135deg, #17a2b8 0%, #6610f2 100%);">
    <div class="container text-center py-5">
        <h1 class="display-4 fw-bold mb-3"><i class="fas fa-key me-3"></i><?= __('rent_hero_title') ?></h1>
        <p class="lead"><?= __('rent_hero_desc') ?></p>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h3><?= __('rent_coming_soon') ?></h3>
            <p class="text-muted"><?= __('rent_coming_soon_desc') ?></p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center">
                <div class="card shadow-sm">
                    <div class="card-body p-5">
                        <i class="fas fa-home fa-4x text-info mb-4"></i>
                        <h4><?= __('rent_looking_title') ?></h4>
                        <p class="text-muted mb-4"><?= __('rent_looking_desc') ?></p>
                        <div class="d-flex gap-2 justify-content-center">
                            <a href="tel:<?= $phoneRaw ?>" class="btn btn-success">
                                <i class="fas fa-phone me-2"></i><?= __('rent_call_us') ?>
                            </a>
                            <a href="https://wa.me/919277121112" target="_blank" class="btn btn-primary">
                                <i class="fab fa-whatsapp me-2"></i><?= __('rent_whatsapp') ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
