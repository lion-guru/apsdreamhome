<div class="container py-4">
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error ?? '') ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?>"><?= htmlspecialchars($_SESSION['flash_message'] ?? '') ?><?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?></div>
    <?php endif; ?>

    <!-- Hero -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto text-center">
            <h1 class="display-5 fw-bold mb-3">Real Estate Services Directory</h1>
            <p class="lead text-muted mb-4">Find trusted masons, plumbers, electricians, architects, material suppliers and more — all for your dream home</p>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="#categories" class="btn btn-primary btn-lg"><i class="fas fa-search me-2"></i>Browse Services</a>
                <a href="<?= BASE_URL ?>/services/jobs" class="btn btn-outline-success btn-lg"><i class="fas fa-briefcase me-2"></i>Jobs</a>
                <a href="<?= BASE_URL ?>/services/materials" class="btn btn-outline-warning btn-lg"><i class="fas fa-cubes me-2"></i>Material Prices</a>
                <a href="<?= BASE_URL ?>/services/submit" class="btn btn-outline-info btn-lg"><i class="fas fa-plus me-2"></i>Add Your Business</a>
            </div>
            <p class="text-muted small mt-4 mb-0">
                <i class="fas fa-shield-alt text-success me-1"></i><?= (int)($stats['approved_listings'] ?? 0) ?> listed service providers
                <span class="mx-2">|</span>
                <i class="fas fa-star text-warning me-1"></i><?= (int)($stats['total_reviews'] ?? 0) ?> customer reviews
                <span class="mx-2">|</span>
                <i class="fas fa-check-circle text-primary me-1"></i>Verified local businesses
            </p>
        </div>
    </div>

    <!-- Stats -->
    <div class="row mb-5 text-center">
        <div class="col-md-3 mb-3"><div class="card border-0 shadow-sm bg-primary text-white"><div class="card-body"><h2><?= (int)($stats['approved_listings'] ?? 0) ?></h2><p class="mb-0">Service Providers</p></div></div></div>
        <div class="col-md-3 mb-3"><div class="card border-0 shadow-sm bg-success text-white"><div class="card-body"><h2><?= (int)($stats['total_categories'] ?? 0) ?></h2><p class="mb-0">Categories</p></div></div></div>
        <div class="col-md-3 mb-3"><div class="card border-0 shadow-sm bg-warning text-dark"><div class="card-body"><h2><?= (int)($stats['active_jobs'] ?? 0) ?></h2><p class="mb-0">Active Jobs</p></div></div></div>
        <div class="col-md-3 mb-3"><div class="card border-0 shadow-sm bg-info text-white"><div class="card-body"><h2><?= (int)($stats['total_reviews'] ?? 0) ?></h2><p class="mb-0">Reviews</p></div></div></div>
    </div>

    <!-- Featured Listings -->
    <?php if (!empty($featured)): ?>
        <h2 class="mb-4"><i class="fas fa-crown text-warning me-2"></i>Featured Service Providers</h2>
        <div class="row mb-5">
            <?php foreach ($featured as $f): ?>
                <div class="col-md-4 col-lg-3 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <?php if ($f['photo']): ?>
                            <img src="<?= htmlspecialchars($f['photo'] ?? '') ?>" loading="lazy" class="card-img-top" alt="<?= htmlspecialchars($f['business_name'] ?? '') ?>" class="style-66292">
                        <?php else: ?>
                            <div class="bg-light text-center py-5"><i class="<?= htmlspecialchars($f['category_icon'] ?? 'fas fa-building') ?> fa-3x text-muted"></i></div>
                        <?php endif; ?>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="card-title mb-0"><?= htmlspecialchars($f['business_name'] ?? '') ?></h6>
                                <?php if ($f['is_verified']): ?><i class="fas fa-check-circle text-info" title="Verified"></i><?php endif; ?>
                            </div>
                            <p class="text-muted small mb-1"><i class="<?= htmlspecialchars($f['category_icon'] ?? 'fas fa-tag') ?> me-1"></i><?= htmlspecialchars($f['category_name'] ?? '') ?></p>
                            <?php if ($f['city']): ?><p class="text-muted small mb-1"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($f['city'] ?? '') ?></p><?php endif; ?>
                            <?php if ($f['rating'] > 0): ?>
                                <p class="mb-1"><span class="text-warning"><?= str_repeat('â˜…', (int)$f['rating']) ?></span><span class="text-muted"> (<?= $f['review_count'] ?>)</span></p>
                            <?php endif; ?>
                            <a href="<?= BASE_URL ?>/services/listing/<?= $f['id'] ?>" class="btn btn-sm btn-outline-primary mt-2">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- All Categories -->
    <h2 class="mb-4" id="categories"><i class="fas fa-th-large me-2"></i>Browse by Category</h2>
    <div class="row">
        <?php foreach ($categories as $cat): ?>
            <div class="col-md-4 col-lg-3 mb-3">
                <a href="<?= BASE_URL ?>/services/<?= htmlspecialchars($cat['slug'] ?? '') ?>" class="text-decoration-none">
                    <div class="card h-100 border-0 shadow-sm category-card">
                        <div class="card-body text-center py-4">
                            <i class="<?= htmlspecialchars($cat['icon'] ?? 'fas fa-building') ?> fa-3x mb-3 text-primary"></i>
                            <h5 class="card-title"><?= htmlspecialchars($cat['name'] ?? '') ?></h5>
                            <p class="text-muted small mb-0"><?= htmlspecialchars($cat['description'] ?? '') ?></p>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Quick Links -->
    <div class="row mt-5">
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-5">
                    <i class="fas fa-briefcase fa-3x mb-3 text-success"></i>
                    <h4>Jobs & Employment</h4>
                    <p class="text-muted">Looking for work in real estate? Hiring for your project? Post or find jobs.</p>
                    <a href="<?= BASE_URL ?>/services/jobs" class="btn btn-outline-success">Browse Jobs</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-5">
                    <i class="fas fa-cubes fa-3x mb-3 text-warning"></i>
                    <h4>Material Price Comparison</h4>
                    <p class="text-muted">Compare prices of cement, steel, bricks, sand and more from multiple suppliers.</p>
                    <a href="<?= BASE_URL ?>/services/materials" class="btn btn-outline-warning">Compare Prices</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-5">
                    <i class="fas fa-plus-circle fa-3x mb-3 text-info"></i>
                    <h4>List Your Business</h4>
                    <p class="text-muted">Are you a service provider? List your business free and get more customers.</p>
                    <a href="<?= BASE_URL ?>/services/submit" class="btn btn-outline-info">Submit Now</a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.category-card:hover { transform: translateY(-3px); transition: 0.2s; border-color: var(--bs-primary) !important; }
.category-card:hover i { transform: scale(1.1); transition: 0.2s; }
</style>
