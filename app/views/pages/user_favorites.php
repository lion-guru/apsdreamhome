<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-heart text-danger me-2"></i>My Favorites</h4>
            <p class="text-muted mb-0">Properties you've saved for quick access.</p>
        </div>
        <a href="<?= BASE_URL ?>/properties" class="btn btn-primary rounded-pill px-4">
            <i class="fas fa-search me-2"></i>Browse Properties
        </a>
    </div>

    <?php if (!empty($favorites)): ?>
        <div class="row g-3">
            <?php foreach ($favorites as $fav): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                        <?php if (!empty($fav['image'])): ?>
                            <img loading="lazy" src="<?= BASE_URL ?>/assets/images/properties/<?= htmlspecialchars($fav['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($fav['title'] ?? 'Property') ?>" style="height: 180px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center" style="height: 180px;">
                                <i class="fas fa-home fa-3x text-muted opacity-25"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-body p-3">
                            <h6 class="fw-bold mb-1"><?= htmlspecialchars($fav['title'] ?? $fav['name'] ?? 'Property') ?></h6>
                            <p class="small text-muted mb-2">
                                <i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($fav['address'] ?? $fav['location'] ?? 'N/A') ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-primary">₹<?= number_format($fav['price'] ?? 0) ?></span>
                                <small class="text-muted">Saved <?= !empty($fav['favorited_at']) ? date('d M', strtotime($fav['favorited_at'])) : '' ?></small>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-0 pt-0 px-3 pb-3">
                            <a href="<?= BASE_URL ?>/property/<?= $fav['id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill w-100">
                                <i class="fas fa-eye me-1"></i>View Details
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 100px; height: 100px;">
                <i class="fas fa-heart fa-3x text-muted opacity-25"></i>
            </div>
            <h5 class="fw-bold">No favorites yet</h5>
            <p class="text-muted mx-auto" style="max-width: 400px;">Browse properties and click the heart icon to save your favorites.</p>
            <a href="<?= BASE_URL ?>/properties" class="btn btn-primary rounded-pill px-4 mt-2">Browse Properties</a>
        </div>
    <?php endif; ?>
</div>
