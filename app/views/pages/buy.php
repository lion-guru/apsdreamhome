<!-- Hero Section -->
<section class="py-5 text-white" style="background: linear-gradient(135deg, #0a192f 0%, #1e3a5f 100%)">
    <div class="container text-center py-5">
        <h1 class="display-4 fw-bold mb-3"><i class="fas fa-search me-3"></i><?= __('buy_hero_title') ?></h1>
        <p class="lead"><?= __('buy_hero_desc') ?></p>
    </div>
</section>

<!-- Search Section -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-4">
                        <h4 class="mb-4 text-center"><?= __('buy_search_title') ?></h4>
                        <form action="<?php echo BASE_URL; ?>/properties" method="GET">
    <?php echo CSRFProtection::csrfField(); ?>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <select name="type" class="form-select">
                                        <option value=""><?= __('buy_type_placeholder') ?></option>
                                        <option value="residential"><?= __('buy_type_residential') ?></option>
                                        <option value="house"><?= __('buy_type_house') ?></option>
                                        <option value="flat"><?= __('buy_type_flat') ?></option>
                                        <option value="commercial"><?= __('buy_type_commercial') ?></option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <select name="location" class="form-select">
                                        <option value=""><?= __('buy_location_placeholder') ?></option>
                                        <option value="Gorakhpur"><?= __('location_gorakhpur') ?></option>
                                        <option value="Lucknow"><?= __('location_lucknow') ?></option>
                                        <option value="Kushinagar"><?= __('location_kushinagar') ?></option>
                                        <option value="Varanasi"><?= __('location_varanasi') ?></option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <select name="budget" class="form-select">
                                        <option value=""><?= __('buy_budget_placeholder') ?></option>
                                        <option value="under_5l"><?= __('buy_budget_under_5l') ?></option>
                                        <option value="5_10l"><?= __('buy_budget_5_10l') ?></option>
                                        <option value="10_20l"><?= __('buy_budget_10_20l') ?></option>
                                        <option value="20_50l"><?= __('buy_budget_20_50l') ?></option>
                                        <option value="above_50l"><?= __('buy_budget_above_50l') ?></option>
                                    </select>
                                </div>
                                <div class="col-12 text-center mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg px-5">
                                        <i class="fas fa-search me-2"></i><?= __('buy_search_btn') ?>
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

<!-- Featured Listings -->
<section class="py-5 bg-light">
    <div class="container">
        <h3 class="text-center mb-4"><?= __('buy_featured_title') ?></h3>
        <div class="row">
            <?php if (!empty($featured_properties)): ?>
                <?php foreach (array_slice($featured_properties, 0, 3) as $project): 
                    $slug = $project['slug'] ?? strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $project['title']));
                ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <i class="fas fa-home fa-3x text-primary mb-3"></i>
                            <h5><?php echo htmlspecialchars($project['title'] ?? ''); ?></h5>
                            <p class="text-muted"><?php echo htmlspecialchars($project['location'] ?? ''); ?></p>
                            <p class="h5 text-primary"><?php echo e($project['price']); ?></p>
                            <a href="<?php echo BASE_URL; ?>/projects/<?php echo e($slug); ?>" class="btn btn-outline-primary mt-2"><?= __('buy_view_details') ?></a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <p class="text-muted"><?= __('buy_no_properties') ?> <a href="<?php echo BASE_URL; ?>/list-property"><?= __('buy_post_your_property') ?></a> <?= __('buy_post_free') ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-5 text-center text-white style-68644">
    <div class="container">
        <h3><?= __('buy_sell_cta_title') ?></h3>
        <p class="mb-4"><?= __('buy_sell_cta_desc') ?></p>
        <a href="<?php echo BASE_URL; ?>/sell" class="btn btn-warning btn-lg"><?= __('buy_sell_cta_btn') ?></a>
    </div>
</section>
