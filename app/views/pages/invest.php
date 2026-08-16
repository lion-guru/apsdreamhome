<?php if (!isset($sc)) { $sc = function($k, $d='') { return $GLOBALS['_site_settings_cache'][$k] ?? $d; }; }$phoneRaw = preg_replace('/[^0-9]/', '', $sc('contact_whatsapp', '919277121112')); $phoneDisplay = $sc('contact_phone', '<?= $phoneDisplay ?>'); ?>
<!-- Hero Section -->
<section class="py-5 text-white" class="style-22627">
    <div class="container text-center py-5">
        <h1 class="display-4 fw-bold mb-3"><i class="fas fa-chart-line me-3"></i><?= __('invest_hero_title') ?></h1>
        <p class="lead"><?= __('invest_hero_desc') ?></p>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto text-center">
                <h3><?= __('invest_why_title') ?></h3>
                <p class="text-muted"><?= __('invest_why_desc') ?></p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-trending-up fa-3x text-success mb-3"></i>
                        <h5><?= __('invest_high_returns') ?></h5>
                        <p class="text-muted"><?= __('invest_high_returns_desc') ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                        <h5><?= __('invest_safe_secure') ?></h5>
                        <p class="text-muted"><?= __('invest_safe_secure_desc') ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-hand-holding-usd fa-3x text-warning mb-3"></i>
                        <h5><?= __('invest_easy_financing') ?></h5>
                        <p class="text-muted"><?= __('invest_easy_financing_desc') ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-5">
            <div class="col-12 text-center">
                <h4><?= __('invest_featured_title') ?></h4>
            </div>
            <?php if (!empty($featured_properties)): ?>
                <?php foreach (array_slice($featured_properties, 0, 3) as $project): 
                    $slug = $project['slug'] ?? strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $project['title']));
                ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <h5><?php echo htmlspecialchars($project['title'] ?? ''); ?></h5>
                            <p class="text-muted small"><?php echo htmlspecialchars($project['location'] ?? ''); ?></p>
                            <p class="h4 text-success"><?php echo $project['price']; ?></p>
                            <a href="<?php echo BASE_URL; ?>/projects/<?php echo $slug; ?>" class="btn btn-outline-success"><?= __('featured_view_details') ?></a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="text-center mt-5">
            <a href="tel:<?= $phoneRaw ?>" class="btn btn-warning btn-lg">
                <i class="fas fa-phone me-2"></i><?= __('invest_call_advice') ?>
            </a>
        </div>
    </div>
</section>
