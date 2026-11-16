<?php
// Featured Properties Page - APS Dream Home
// Uses controller-supplied $featuredProperties. No demo data injected from here.
$featuredProperties = $featuredProperties ?? [];
$realMissing = empty($featuredProperties);
?>

<section class="py-5 bg-primary text-white">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-3">Featured Properties - APS Dream Home</h1>
        <p class="lead">Handpicked featured properties from our live inventory</p>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <?php if ($realMissing): ?>
        <div class="text-center py-5">
            <i class="fas fa-home fa-4x text-muted mb-3"></i>
            <h2 class="h4">No featured properties right now</h2>
            <p class="text-muted">Browse all properties instead.</p>
            <a href="<?= BASE_URL ?>/properties" class="btn btn-primary">See all properties</a>
        </div>
        <?php else: ?>
        <div class="row">
            <?php foreach ($featuredProperties as $property): ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="position-relative">
                        <img src="<?= htmlspecialchars($property['image'] ?? (BASE_URL . '/assets/images/placeholder/property.svg')) ?>" class="card-img-top img-fluid" alt="<?= htmlspecialchars($property['name'] ?? '') ?>" loading="lazy" onerror="this.onerror=null;this.src='<?= BASE_URL ?>/assets/images/placeholder/property.svg'">
                        <span class="badge bg-success position-absolute top-0 start-0 m-3">Featured</span>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <h5 class="card-title"><?= htmlspecialchars($property['name'] ?? 'Featured Property') ?></h5>
                        <p class="text-muted"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($property['location'] ?? '') ?></p>
                        <div class="d-flex gap-3 mb-3">
                            <span><i class="fas fa-ruler-combined text-muted me-1"></i><?= htmlspecialchars($property['area'] ?? '0 sq.ft') ?></span>
                            <span><i class="fas fa-tag text-muted me-1"></i><?= htmlspecialchars($property['type'] ?? 'Property') ?></span>
                        </div>
                        <div class="h4 text-primary"><?= htmlspecialchars($property['price'] ?? '') ?></div>
                    </div>
                    <div class="card-footer bg-white border-0 pb-3">
                        <a href="<?= BASE_URL ?>/properties" class="btn btn-primary w-100">View Details</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="<?= BASE_URL ?>/properties" class="btn btn-outline-primary btn-lg"><i class="fas fa-th-large me-2"></i>See all properties</a>
        </div>
        <?php endif; ?>
    </div>
</section>
