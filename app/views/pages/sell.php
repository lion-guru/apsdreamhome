<?php if (!isset($sc)) { $sc = function($k, $d='') { return $GLOBALS['_site_settings_cache'][$k] ?? $d; }; }$phoneRaw = preg_replace('/[^0-9]/', '', $sc('contact_whatsapp', '919277121112')); $phoneDisplay = $sc('contact_phone', '<?= $phoneDisplay ?>'); ?>
<?php
/**
 * Sell Property Page - Redirect to List Property
 */
?>
<!-- Hero Section -->
<section class="py-5 text-white style-88128">
    <div class="container text-center py-5">
        <h1 class="display-4 fw-bold mb-3"><i class="fas fa-tag me-3"></i><?= __('sell_hero_title') ?></h1>
        <p class="lead"><?= __('sell_hero_desc') ?></p>
    </div>
</section>

<!-- Info Section -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-5">
                        <i class="fas fa-home fa-4x text-success mb-4"></i>
                        <h3><?= __('sell_post_free_title') ?></h3>
                        <p class="text-muted mb-4"><?= __('sell_post_free_desc') ?></p>
                        
                        <div class="row g-4 mb-4">
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded">
                                    <i class="fas fa-check-circle text-success fa-2x mb-2"></i>
                                    <p class="mb-0 small fw-bold"><?= __('sell_free_label') ?></p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded">
                                    <i class="fas fa-clock text-primary fa-2x mb-2"></i>
                                    <p class="mb-0 small fw-bold"><?= __('sell_quick_listing') ?></p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded">
                                    <i class="fas fa-users text-warning fa-2x mb-2"></i>
                                    <p class="mb-0 small fw-bold"><?= __('sell_more_buyers') ?></p>
                                </div>
                            </div>
                        </div>

                        <a href="<?php echo BASE_URL; ?>/list-property" class="btn btn-success btn-lg px-5">
                            <i class="fas fa-plus-circle me-2"></i><?= __('sell_post_btn') ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="py-5 bg-light">
    <div class="container">
        <h3 class="text-center mb-4"><?= __('sell_how_it_works') ?></h3>
        <div class="row text-center">
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3 style-71716">
                            <span class="h4 mb-0">1</span>
                        </div>
                        <h5><?= __('sell_step1_title') ?></h5>
                        <p class="text-muted"><?= __('sell_step1_desc') ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="bg-warning text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3 style-71716">
                            <span class="h4 mb-0">2</span>
                        </div>
                        <h5><?= __('sell_step2_title') ?></h5>
                        <p class="text-muted"><?= __('sell_step2_desc') ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3 style-71716">
                            <span class="h4 mb-0">3</span>
                        </div>
                        <h5><?= __('sell_step3_title') ?></h5>
                        <p class="text-muted"><?= __('sell_step3_desc') ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-5 text-center text-white style-88128">
    <div class="container">
        <h3><?= __('sell_need_help') ?></h3>
        <p class="mb-4"><?= __('sell_contact_us') ?></p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="tel:<?= $phoneRaw ?>" class="btn btn-light btn-lg">
                <i class="fas fa-phone me-2"></i><?= __('sell_call_now') ?>
            </a>
            <a href="https://wa.me/<?= $phoneRaw ?>?text=<?= urlencode(__('sell_whatsapp_msg')) ?>" target="_blank" class="btn btn-outline-light btn-lg">
                <i class="fab fa-whatsapp me-2"></i><?= __('sell_whatsapp') ?>
            </a>
        </div>
    </div>
</section>
