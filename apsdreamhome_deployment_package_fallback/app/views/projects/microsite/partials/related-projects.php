<?php
if (empty($related)) {
    return;
}

$cityLabel = $location['city'] ?? 'our portfolio';
$items = array_slice($related, 0, 3);
?>
<section class="microsite-section bg-light py-5" id="microsite-related">
    <div class="container">
        <div class="row mb-4 align-items-center">
            <div class="col-lg-8">
                <h2 class="section-title">Discover More Projects</h2>
                <p class="section-subtitle">Explore other developments in <?php echo h($cityLabel); ?>.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="/projects" class="btn btn-outline-primary">View All Projects</a>
            </div>
        </div>
        <div class="row g-4">
            <?php foreach ($items as $item): ?>
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 microsite-related-card">
                    <?php if (!empty($item['hero_image'])): ?>
                    <img src="<?php echo h($item['hero_image']); ?>" class="card-img-top" alt="<?php echo h($item['project_name'] ?? 'Related project'); ?>" loading="lazy">
                    <?php endif; ?>
                    <div class="card-body">
                        <h3 class="h5 card-title"><?php echo h($item['project_name'] ?? 'Project'); ?></h3>
                        <p class="card-text text-muted mb-3"><?php echo h($item['short_description'] ?? $item['description'] ?? ''); ?></p>
                        <ul class="list-inline microsite-related-meta">
                            <?php if (!empty($item['project_status'])): ?>
                            <li class="list-inline-item"><i class="fa-solid fa-signal me-1"></i><?php echo h(ucwords($item['project_status'])); ?></li>
                            <?php endif; ?>
                            <?php if (!empty($item['price_per_sqft'])): ?>
                            <li class="list-inline-item"><i class="fa-solid fa-indian-rupee-sign me-1"></i><?php echo number_format((float)$item['price_per_sqft']); ?>/sq.ft</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <div class="card-footer bg-transparent border-0">
                        <?php if (!empty($item['project_code'])): ?>
                        <a href="/projects/<?php echo urlencode($item['project_code']); ?>/microsite" class="btn btn-sm btn-primary">View Microsite</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
