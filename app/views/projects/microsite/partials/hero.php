<?php
$heroImage = $media['hero'] ?? '/public/assets/images/projects/placeholder-hero.jpg';
$palette = $theme['palette'] ?? ['primary' => '#4e73df', 'accent' => '#1cc88a'];
?>
<section class="microsite-hero" style="--primary-color: <?php echo h($palette['primary']); ?>; --accent-color: <?php echo h($palette['accent']); ?>;">
    <div class="microsite-hero__bg" style="background-image: url('<?php echo h($heroImage); ?>');"></div>
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <span class="badge bg-light text-dark mb-3"><?php echo h(ucwords($project['type'] ?? '')); ?></span>
                <h1 class="display-4 fw-bold text-white">
                    <?php echo h($project['name'] ?? 'Project'); ?>
                </h1>
                <?php if (!empty($project['summary'])): ?>
                <p class="lead text-white-50 mb-4"><?php echo h($project['summary']); ?></p>
                <?php endif; ?>
                <div class="d-flex flex-wrap gap-3">
                    <?php if (!empty($cta['book_visit']['endpoint'])): ?>
                    <a href="#microsite-enquiry" class="btn btn-light btn-lg">Book a Visit</a>
                    <?php endif; ?>
                    <?php if (!empty($cta['download_brochure'])): ?>
                    <a href="<?php echo h($cta['download_brochure']); ?>" class="btn btn-outline-light btn-lg" target="_blank" rel="noopener">Download Brochure</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="microsite-stats card shadow-lg">
                    <div class="card-body">
                        <div class="row g-3">
                            <?php if (!empty($project['status'])): ?>
                            <div class="col-6">
                                <div class="microsite-stat">
                                    <small class="text-muted">Status</small>
                                    <p class="h5 mb-0"><?php echo h(ucwords($project['status'])); ?></p>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($project['possession_date'])): ?>
                            <div class="col-6">
                                <div class="microsite-stat">
                                    <small class="text-muted">Possession</small>
                                    <p class="h5 mb-0"><?php echo h($project['possession_date']); ?></p>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($project['price_per_sqft'])): ?>
                            <div class="col-6">
                                <div class="microsite-stat">
                                    <small class="text-muted">Price / Sq.Ft.</small>
                                    <p class="h5 mb-0">₹<?php echo number_format((float)$project['price_per_sqft']); ?></p>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($project['available_plots'])): ?>
                            <div class="col-6">
                                <div class="microsite-stat">
                                    <small class="text-muted">Available Units</small>
                                    <p class="h5 mb-0"><?php echo h($project['available_plots']); ?></p>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
