<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-heart text-danger me-2"></i><?= __('user_favorites_heading') ?></h4>
            <p class="text-muted mb-0"><?= __('user_favorites_subtitle') ?></p>
        </div>
        <a href="<?= BASE_URL ?>/properties" class="btn btn-primary rounded-pill px-4">
            <i class="fas fa-search me-2"></i><?= __('user_favorites_browse_button') ?>
        </a>
    </div>

    <?php if (!empty($favorites)): ?>
        <div class="row g-3">
            <?php foreach ($favorites as $fav): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                        <?php if (!empty($fav['image'])): ?>
                            <img src="<?= BASE_URL ?>/assets/images/properties/<?= htmlspecialchars($fav['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($fav['title'] ?? __('user_favorites_default_alt')) ?>" class="style-24482" loading="lazy">
                        <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center" class="style-32569">
                                <i class="fas fa-home fa-3x text-muted opacity-25" aria-hidden="true"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-body p-3">
                            <h6 class="fw-bold mb-1"><?= htmlspecialchars($fav['title'] ?? $fav['name'] ?? __('user_favorites_default_alt')) ?></h6>
                            <p class="small text-muted mb-2">
                                <i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($fav['address'] ?? $fav['location'] ?? __('common_not_available')) ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-primary"><?= __('currency_inr') ?><?= number_format($fav['price'] ?? 0) ?></span>
                                <small class="text-muted"><?= __('user_favorites_saved_on', ['date' => !empty($fav['favorited_at']) ? date('d M', strtotime($fav['favorited_at'])) : '']) ?></small>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-0 pt-0 px-3 pb-3">
                            <a href="<?= BASE_URL ?>/property/<?= $fav['id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill w-100">
                                <i class="fas fa-eye me-1"></i><?= __('user_favorites_view_details') ?>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="aps-empty-state" role="status">
            <i class="fas fa-heart aps-empty-state-icon" aria-hidden="true"></i>
            <h5 class="aps-empty-state-title"><?= __('user_favorites_empty_title') ?></h5>
            <p class="aps-empty-state-message text-muted"><?= __('user_favorites_empty_desc') ?></p>
            <a href="<?= BASE_URL ?>/properties" class="btn btn-primary rounded-pill px-4 aps-empty-state-action"><?= __('user_favorites_browse_button') ?></a>
        </div>
    <?php endif; ?>
</div>
