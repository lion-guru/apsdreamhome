<?php
$current_page = $current_page ?? 'plot-detail';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
?>

<div class="container py-4">

    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= $baseUrl ?>"><?= __('home') ?></a></li>
            <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/plots/browse"><?= __('browse_browse_plots') ?></a></li>
            <li class="breadcrumb-item active"><?= __('browse_plot') ?> <?= htmlspecialchars($plot['plot_number']) ?></li>
        </ol>
    </nav>

    <div class="row g-4">

        <!-- Left: Plot Detail -->
        <div class="col-lg-8">

            <!-- Header Card -->
            <div class="aps-cp-card mb-4">
                <div class="aps-cp-card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h3 class="fw-bold mb-1"><?= __('browse_plot') ?> <?= htmlspecialchars($plot['plot_number']) ?></h3>
                            <p class="text-muted mb-0">
                                <i class="fas fa-building me-1"></i><?= htmlspecialchars($plot['colony_name']) ?>
                                <?php if (!empty($plot['block'])): ?>
                                    &middot; <?= __('browse_block') ?> <?= htmlspecialchars($plot['block']) ?>
                                <?php endif; ?>
                                &middot; <?= htmlspecialchars($plot['district_name'] ?? '') ?>, <?= htmlspecialchars($plot['state_name'] ?? '') ?>
                            </p>
                        </div>
                        <span class="badge bg-success fs-6"><?= __('browse_available') ?></span>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-sm-3 col-6">
                            <div class="bg-light rounded p-3 text-center">
                                <small class="text-muted d-block mb-1"><?= __('detail_area') ?></small>
                                <strong class="fs-5"><?= number_format($plot['area_sqft']) ?></strong>
                                <small class="text-muted"> <?= __('sqft') ?></small>
                            </div>
                        </div>
                        <div class="col-sm-3 col-6">
                            <div class="bg-light rounded p-3 text-center">
                                <small class="text-muted d-block mb-1"><?= __('detail_dimensions') ?></small>
                                <strong class="fs-5"><?= htmlspecialchars($plot['dimension_label'] ?? '—') ?></strong>
                            </div>
                        </div>
                        <div class="col-sm-3 col-6">
                            <div class="bg-light rounded p-3 text-center">
                                <small class="text-muted d-block mb-1"><?= __('detail_price_sqft') ?></small>
                                <strong class="fs-5">₹<?= number_format($pricePerSqft) ?></strong>
                            </div>
                        </div>
                        <div class="col-sm-3 col-6">
                            <div class="bg-light rounded p-3 text-center">
                                <small class="text-muted d-block mb-1"><?= __('detail_total_price') ?></small>
                                <strong class="fs-5 text-primary">₹<?= number_format($plot['total_price']) ?></strong>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($plot['corner_plot']) || !empty($plot['park_facing'])): ?>
                    <div class="mb-2">
                        <?php if (!empty($plot['corner_plot'])): ?>
                            <span class="badge bg-warning-subtle text-warning me-1"><i class="fas fa-star me-1"></i><?= __('browse_corner_plot') ?></span>
                        <?php endif; ?>
                        <?php if (!empty($plot['park_facing'])): ?>
                            <span class="badge bg-success-subtle text-success"><i class="fas fa-tree me-1"></i><?= __('browse_park_facing') ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Map Placeholder -->
            <div class="aps-cp-card mb-4">
                <div class="aps-cp-card-header">
                    <span><i class="fas fa-map-marked-alt me-2"></i><?= __('detail_location_map') ?></span>
                </div>
                <div class="aps-cp-card-body">
                    <div class="bg-light rounded d-flex align-items-center justify-content-center" class="style-26992">
                        <div class="text-center text-muted">
                            <i class="fas fa-map fa-3x mb-2 opacity-25"></i>
                            <p class="mb-0"><?= __('detail_plot_location_map') ?></p>
                            <?php if (!empty($plot['map_link'])): ?>
                            <a href="<?= htmlspecialchars($plot['map_link']) ?>" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                                <i class="fas fa-external-link-alt me-1"></i><?= __('detail_open_google_maps') ?>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Nearby Plots -->
            <?php if (!empty($nearbyPlots)): ?>
            <div class="aps-cp-card mb-4">
                <div class="aps-cp-card-header">
                    <span><i class="fas fa-th-large me-2"></i><?= __('detail_nearby_plots') ?></span>
                </div>
                <div class="aps-cp-card-body">
                    <div class="row g-3">
                        <?php foreach ($nearbyPlots as $np): ?>
                        <div class="col-sm-6 col-lg-3">
                            <a href="<?= $baseUrl ?>/plots/<?= $np['id'] ?>/detail" class="text-decoration-none">
                                <div class="bg-light rounded p-3 text-center h-100">
                                    <strong class="d-block"><?= __('browse_plot') ?> <?= htmlspecialchars($np['plot_number']) ?></strong>
                                    <small class="text-muted"><?= number_format($np['area_sqft']) ?> <?= __('sqft') ?></small>
                                    <div class="text-primary fw-bold mt-1">₹<?= number_format($np['total_price']) ?></div>
                                </div>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Right: Price Breakdown + CTA -->
        <div class="col-lg-4">

            <!-- Price Breakdown -->
            <div class="aps-cp-card mb-4">
                <div class="aps-cp-card-header">
                    <span><i class="fas fa-receipt me-2"></i><?= __('detail_price_breakdown') ?></span>
                </div>
                <div class="aps-cp-card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span><?= __('detail_plot_price') ?></span>
                        <strong>₹<?= number_format($plot['total_price']) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span><?= __('detail_token_amount') ?></span>
                        <strong>₹<?= number_format($tokenAmount) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span><?= __('detail_stamp_duty') ?></span>
                        <span class="text-muted">~₹<?= number_format($stampDuty) ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span><?= __('detail_balance_after_token') ?></span>
                        <strong>₹<?= number_format($plot['total_price'] - $tokenAmount) ?></strong>
                    </div>
                </div>
            </div>

            <!-- CTA -->
            <div class="aps-cp-card mb-4">
                <div class="aps-cp-card-body text-center">
                    <h5 class="fw-bold mb-2"><?= __('detail_interested_plot') ?></h5>
                    <p class="text-muted small mb-3"><?= __('detail_book_token_desc') ?></p>
                    <a href="<?= $baseUrl ?>/plots/<?= $plot['id'] ?>/book" class="btn btn-primary btn-lg w-100 mb-2">
                        <i class="fas fa-check-circle me-2"></i><?= __('detail_book_this_plot') ?>
                    </a>
                    <a href="<?= $baseUrl ?>/plots/browse" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="fas fa-arrow-left me-1"></i><?= __('detail_back_to_browse') ?>
                    </a>
                </div>
            </div>

            <!-- Colony Info -->
            <div class="aps-cp-card">
                <div class="aps-cp-card-header">
                    <span><i class="fas fa-info-circle me-2"></i><?= sprintf(__('detail_about_colony'), htmlspecialchars($plot['colony_name'])) ?></span>
                </div>
                <div class="aps-cp-card-body">
                    <p class="small text-muted mb-2"><?= htmlspecialchars($plot['colony_description'] ?? __('detail_colony_default_desc')) ?></p>
                    <?php if (!empty($plot['district_name'])): ?>
                    <div class="small">
                        <i class="fas fa-map-marker-alt text-primary me-1"></i>
                        <?= htmlspecialchars($plot['district_name']) ?>, <?= htmlspecialchars($plot['state_name'] ?? '') ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
