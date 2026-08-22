<?php
$extraHead = '<style>
    .property-card { border: none; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); transition: transform 0.2s; }
    .property-card:hover { transform: translateY(-3px); }
</style>';
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <h3><i class="fas fa-building me-2 text-primary"></i><?= __('user_properties_heading') ?></h3>
        <a href="<?php echo BASE_URL; ?>/list-property" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i><?= __('user_properties_button_post') ?>
        </a>
    </div>

    <?php if (empty($properties)): ?>
        <div class="card aps-cp-card">
            <div class="card-body text-center py-5">
                <i class="fas fa-home fa-4x text-muted mb-3"></i>
                <h5 class="text-muted"><?= __('user_properties_empty_title') ?></h5>
                <p class="text-muted"><?= __('user_properties_empty_desc') ?></p>
                <a href="<?php echo BASE_URL; ?>/list-property" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i><?= __('user_properties_button_post_property') ?>
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($properties as $p): ?>
                <div class="col-lg-6 mb-4">
                    <div class="card property-card h-100">
                        <div class="card-body aps-cp-card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="mb-1"><?php echo htmlspecialchars($p['name'] ?? ''); ?></h5>
                                    <p class="text-muted mb-0 small">
                                        <i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($p['address'] ?? __('user_properties_location_unspecified')); ?>
                                    </p>
                                </div>
                                <?php
                                $statusClass = match($p['status'] ?? 'pending') {
                                    'pending' => 'warning',
                                    'verified' => 'info',
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    'sold' => 'dark',
                                    default => 'secondary'
                                };
                                ?>
                                <span class="badge bg-<?php echo e($statusClass); ?>"><?php echo e(ucfirst(__('status_' . ($p['status'] ?? 'pending')))); ?></span>
                            </div>

                            <?php if (!empty($p['image'])): ?>
                                <?php $imgRaw = $p['image'] ?? '';
                                      $imgSrc = (str_starts_with($imgRaw, 'http://') || str_starts_with($imgRaw, 'https://')) ? $imgRaw : BASE_URL . '/' . $imgRaw; ?>
                                <img src="<?= htmlspecialchars($imgSrc ?? '') ?>" class="img-fluid rounded mb-3" class="style-9014" alt="<?php echo htmlspecialchars($p['name'] ?? ''); ?>" loading="lazy">
                            <?php endif; ?>

                            <div class="row mb-3">
                                <div class="col-4">
                                    <small class="text-muted"><?= __('user_properties_label_type') ?></small>
                                    <p class="mb-0 fw-bold"><?php echo ucfirst(__('ptype_' . ($p['property_type'] ?? 'other'))); ?></p>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted"><?= __('user_properties_label_for') ?></small>
                                    <p class="mb-0 fw-bold"><?php echo ucfirst(__('listing_' . ($p['listing_type'] ?? 'sale'))); ?></p>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted"><?= __('user_properties_label_price') ?></small>
                                    <p class="mb-0 fw-bold text-success"><?= __('currency_inr') ?><?php echo number_format($p['price']); ?></p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-4">
                                    <small class="text-muted"><?= __('user_properties_label_area') ?></small>
                                    <p class="mb-0"><?php echo number_format($p['area_sqft'] ?? 0); ?> <?= __('unit_sqft') ?></p>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted"><?= __('user_properties_label_views') ?></small>
                                    <p class="mb-0"><i class="fas fa-eye me-1"></i><?php echo e($p['views'] ?? 0); ?></p>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted"><?= __('user_properties_label_inquiries') ?></small>
                                    <p class="mb-0"><i class="fas fa-envelope me-1"></i><?php echo e($p['inquiries'] ?? 0); ?></p>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <small class="text-muted">
                                    <?= __('user_properties_posted_on', ['date' => date('d M Y', strtotime($p['created_at']))]) ?>
                                </small>
                                <?php if ($p['status'] === 'pending'): ?>
                                    <span class="badge bg-warning"><?= __('user_properties_under_review') ?></span>
                                <?php elseif ($p['status'] === 'approved'): ?>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?php echo BASE_URL; ?>/listing/<?php echo (int)$p['id']; ?>" class="btn btn-outline-primary" target="_blank"><?= __('user_properties_view_listing') ?> <i class="fas fa-external-link-alt ms-1"></i></a>
                                        <a href="<?php echo BASE_URL; ?>/user/boost-property/<?php echo (int)$p['id']; ?>" class="btn btn-outline-warning">
                                            <i class="fas fa-crown"></i> Boost
                                        </a>
                                    </div>
                                <?php elseif ($p['status'] === 'rejected'): ?>
                                    <span class="badge bg-danger"><?= __('status_rejected') ?></span>
                                <?php elseif ($p['status'] === 'verified'): ?>
                                    <span class="badge bg-info"><?= __('status_verified') ?></span>
                                <?php elseif ($p['status'] === 'sold'): ?>
                                    <span class="badge bg-dark"><?= __('status_sold') ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if ($p['status'] === 'approved' && (!empty($p['is_featured']) || !empty($p['is_urgent']) || !empty($p['is_premium']))): ?>
                            <div class="mt-2 d-flex gap-1">
                                <?php if (!empty($p['is_featured'])): ?><span class="badge bg-warning text-dark"><i class="fas fa-star"></i> Featured</span><?php endif; ?>
                                <?php if (!empty($p['is_urgent'])): ?><span class="badge bg-danger"><i class="fas fa-bolt"></i> Urgent</span><?php endif; ?>
                                <?php if (!empty($p['is_premium'])): ?><span class="badge bg-primary"><i class="fas fa-gem"></i> Premium</span><?php endif; ?>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($p['admin_notes'])): ?>
                            <div class="mt-3 p-3 bg-light rounded">
                                <small class="text-muted d-block mb-1"><i class="fas fa-sticky-note me-1"></i><?= __('user_properties_admin_note') ?>:</small>
                                <p class="mb-0 small"><?php echo nl2br(htmlspecialchars($p['admin_notes'] ?? '')); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
