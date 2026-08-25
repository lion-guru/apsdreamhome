<!-- Hero Section -->
<section class="hero-section text-white text-center py-5 style-26625">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-4" data-aos="fade-up">
                    <?= sprintf(__('colonies_hero_title'), '<span class="text-warning">' . __('colonies_word') . '</span>') ?>
                </h1>
                <p class="lead mb-4" data-aos="fade-up" data-aos-delay="100">
                    <?= __('colonies_hero_desc') ?>
                </p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="#colonies-container" class="btn btn-light btn-lg px-4 py-3">
                        <i class="fas fa-building me-2"></i><?= __('colonies_explore') ?>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/contact" class="btn btn-outline-light btn-lg px-4 py-3">
                        <i class="fas fa-phone me-2"></i><?= __('colonies_get_quote') ?>
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
                <?php if (isset($breadcrumbs)): ?>
                    <?php foreach ($breadcrumbs as $crumb): ?>
                        <?php if (empty($crumb['url']) || $crumb === end($breadcrumbs)): ?>
                            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($crumb['title'] ?? '') ?></li>
                        <?php else: ?>
                            <li class="breadcrumb-item"><a href="<?= $crumb['url'] ?>"><?= htmlspecialchars($crumb['title'] ?? '') ?></a></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><?= __('nav.menu.home') ?></a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= __('colonies_filter_all') ?></li>
                <?php endif; ?>
            </ol>
        </nav>
    </div>
</div>

<!-- Statistics Section -->
<section class="stats-section">
    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <div class="stat-counter" data-aos="fade-up">
                    <span class="stat-number" data-target="<?php echo e($colony_stats['total_colonies']); ?>"><?php echo e($colony_stats['total_colonies']); ?></span>
                    <span class="stat-label"><?= __('colonies_stat_active') ?></span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-counter" data-aos="fade-up" data-aos-delay="100">
                    <span class="stat-number" data-target="<?php echo (int)$colony_stats['total_area']; ?>"><?php echo e($colony_stats['total_area']); ?></span>
                    <span class="stat-label"><?= __('colonies_stat_area') ?></span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-counter" data-aos="fade-up" data-aos-delay="200">
                    <span class="stat-number" data-target="<?php echo (int)$colony_stats['total_plots']; ?>"><?php echo number_format($colony_stats['total_plots']); ?></span>
                    <span class="stat-label"><?= __('colonies_stat_plots') ?></span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-counter" data-aos="fade-up" data-aos-delay="300">
                    <span class="stat-number" data-target="<?php echo (int)$colony_stats['cities_covered']; ?>"><?php echo e($colony_stats['cities_covered']); ?></span>
                    <span class="stat-label"><?= __('colonies_stat_cities') ?></span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Filter Section -->
<section class="py-5">
    <div class="container">
        <div class="filter-buttons" data-aos="fade-up">
            <button class="filter-btn active" data-filter="all"><?= __('colonies_filter_all') ?></button>
            <button class="filter-btn" data-filter="gorakhpur"><?= __('location_gorakhpur') ?></button>
            <button class="filter-btn" data-filter="lucknow"><?= __('location_lucknow') ?></button>
            <button class="filter-btn" data-filter="residential"><?= __('colonies_filter_residential') ?></button>
            <button class="filter-btn" data-filter="commercial"><?= __('colonies_filter_commercial') ?></button>
        </div>

        <!-- Colonies Grid -->
        <div class="row" id="colonies-container">
            <?php foreach ($colonies as $index => $colony): ?>
                <div class="col-lg-4 col-md-6 colony-item" data-location="<?php echo e(strtolower(explode(',', $colony['location'])[0])); ?>">
                    <div class="colony-card">
                        <div class="colony-image">
                            <?php
                            $imagePath = $colony['image'] ?? null;
                            if ($imagePath && strpos((string)$imagePath, 'http') !== 0) {
                                $imagePath = get_asset_url((string)$imagePath);
                            }
                            ?>
                            <img src="<?php echo htmlspecialchars($imagePath ?? ''); ?>" alt="<?php echo htmlspecialchars($colony['name'] ?? ''); ?>" class="img-fluid" loading="lazy">
                            <div class="colony-placeholder style-57012">
                                <i class="fas fa-city fa-3x mb-2"></i>
                                <p class="mb-0 text-center px-2"><?php echo e($colony['name']); ?></p>
                            </div>
                        </div>
                        <div class="colony-overlay">
                            <span class="status-badge"><?php echo e($colony['status'] ?? 'active'); ?></span>
                        </div>
                    </div>

                    <div class="colony-content">
                        <h3 class="colony-title"><?php echo e($colony['name']); ?></h3>

                        <div class="colony-location">
                            <i class="fas fa-map-marker-alt"></i>
                            <?php echo e($colony['location']); ?>
                        </div>

                        <p class="colony-description"><?php echo e($colony['description'] ?? ''); ?></p>

                        <div class="colony-highlights">
                            <?php if (!empty($colony['amenities'])): ?>
                                <?php foreach (array_slice($colony['amenities'], 0, 3) as $highlight): ?>
                                    <span class="highlight-tag"><?php echo e($highlight); ?></span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <div class="colony-specs">
                            <div class="spec-item">
                                <span class="spec-value"><?php echo e($colony['total_plots'] ?? 0); ?></span>
                                <span class="spec-label"><?= __('colonies_spec_plots') ?></span>
                            </div>
                            <div class="spec-item">
                                <span class="spec-value"><?php echo e($colony['available_plots']); ?></span>
                                <span class="spec-label"><?= __('colonies_spec_available') ?></span>
                            </div>
                            <div class="spec-item">
                                <span class="spec-value"><?php echo e($colony['starting_price']); ?></span>
                                <span class="spec-label"><?= __('colonies_spec_price') ?></span>
                            </div>
                        </div>

                        <div class="colony-amenities">
                            <h6><i class="fas fa-star me-2"></i><?= __('colonies_amenities_title') ?></h6>
                            <?php foreach (array_slice($colony['amenities'], 0, 4) as $amenity): ?>
                                <div class="amenity-item">
                                    <i class="fas fa-check"></i>
                                    <?php echo e($amenity); ?>
                                </div>
                            <?php endforeach; ?>
                            <?php if (count($colony['amenities']) > 4): ?>
                                <small class="text-muted"><?= sprintf(__('colonies_more_amenities'), count($colony['amenities']) - 4) ?></small>
                            <?php endif; ?>
                        </div>

                        <div class="colony-actions">
                            <button class="btn btn-view-plots flex-fill">
                                <i class="fas fa-eye me-2"></i><?= __('colonies_view_plots') ?>
                            </button>
                            <button class="btn btn-outline-primary" onclick="showInterest('<?php echo (int)$colony['id']; ?>')">
                                <i class="fas fa-heart me-2"></i><?= __('colonies_interested') ?>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="cta-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h2 class="mb-4"><?= __('colonies_cta_title') ?></h2>
                <p class="lead mb-4">
                    <?= __('colonies_cta_desc') ?>
                </p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="<?php echo BASE_URL; ?>/contact" class="btn btn-warning btn-lg px-5 py-3">
                        <i class="fas fa-calendar me-2"></i><?= __('colonies_schedule_visit') ?>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/become-associate" class="btn btn-outline-light btn-lg px-5 py-3">
                        <i class="fas fa-handshake me-2"></i><?= __('colonies_become_associate') ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Interest Modal -->
<div class="modal fade" id="interestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= __('colonies_modal_title') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="interestForm">
    <?php echo CSRFProtection::csrfField(); ?>
                    <input type="hidden" id="colony_id" name="colony_id">
                    <div class="mb-3">
                        <label class="form-label"><?= __('colonies_form_name') ?></label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= __('colonies_form_phone') ?></label>
                        <input type="tel" class="form-control" name="phone" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= __('colonies_form_email') ?></label>
                        <input type="email" class="form-control" name="email">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= __('colonies_form_message') ?></label>
                        <textarea class="form-control" name="message" rows="3" placeholder="<?= htmlspecialchars(__('colonies_form_placeholder')) ?>"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-paper-plane me-2"></i><?= __('colonies_form_submit') ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
