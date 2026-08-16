<?php
$current_page = $current_page ?? 'browse-plots';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
?>

<div class="container py-4">

    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= $baseUrl ?>"><?= __('home') ?></a></li>
            <li class="breadcrumb-item active"><?= __('browse_browse_plots') ?></li>
        </ol>
    </nav>

    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h1 class="display-6 fw-bold text-primary mb-1">
                <i class="fas fa-vector-square me-2"></i><?= __('browse_hero_title') ?>
            </h1>
            <p class="text-muted mb-0">
                <strong><?= number_format($total ?? 0) ?></strong> <?= __('browse_plots_available') ?>
            </p>
        </div>
    </div>

    <!-- Filters -->
    <div class="aps-cp-card mb-4">
        <div class="aps-cp-card-header">
            <span><i class="fas fa-filter me-2"></i><?= __('browse_filters') ?></span>
        </div>
        <div class="aps-cp-card-body">
            <form method="GET" action="<?= $baseUrl ?>/plots/browse" id="plotFilters">
    <?php echo CSRFProtection::csrfField(); ?>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold"><?= __('browse_colony') ?></label>
                        <select name="colony" class="form-select form-select-sm">
                            <option value=""><?= __('browse_all_colonies') ?></option>
                            <?php foreach ($colonies as $col): ?>
                            <option value="<?= (int)$col['id'] ?>" <?= ($current_colony == $col['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($col['name'] ?? '') ?> (<?= (int)$col['available_count'] ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold"><?= __('browse_min_price') ?></label>
                        <input type="number" name="min_price" class="form-control form-control-sm" placeholder="e.g. 500000" value="<?= (int)$current_min_price ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold"><?= __('browse_max_price') ?></label>
                        <input type="number" name="max_price" class="form-control form-control-sm" placeholder="e.g. 5000000" value="<?= (int)$current_max_price ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold"><?= __('browse_min_area') ?></label>
                        <input type="number" name="min_area" class="form-control form-control-sm" placeholder="e.g. 1000" value="<?= (int)$current_min_area ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold"><?= __('browse_max_area') ?></label>
                        <input type="number" name="max_area" class="form-control form-control-sm" placeholder="e.g. 5000" value="<?= (int)$current_max_area ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold"><?= __('browse_sort_by') ?></label>
                        <select name="sort" class="form-select form-select-sm">
                            <option value="plot_number" <?= $current_sort === 'plot_number' ? 'selected' : '' ?>><?= __('browse_sort_plot_number') ?></option>
                            <option value="price_asc" <?= $current_sort === 'price_asc' ? 'selected' : '' ?>><?= __('browse_sort_price_low') ?></option>
                            <option value="price_desc" <?= $current_sort === 'price_desc' ? 'selected' : '' ?>><?= __('browse_sort_price_high') ?></option>
                            <option value="area_asc" <?= $current_sort === 'area_asc' ? 'selected' : '' ?>><?= __('browse_sort_area_small') ?></option>
                            <option value="area_desc" <?= $current_sort === 'area_desc' ? 'selected' : '' ?>><?= __('browse_sort_area_large') ?></option>
                        </select>
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search me-1"></i><?= __('browse_apply_filters') ?></button>
                    <a href="<?= $baseUrl ?>/plots/browse" class="btn btn-outline-secondary btn-sm"><i class="fas fa-times me-1"></i><?= __('browse_clear') ?></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Plot Grid -->
    <?php if (empty($plots)): ?>
    <div class="aps-cp-card text-center py-5">
        <i class="fas fa-search fa-3x text-muted mb-3"></i>
        <h5 class="text-muted"><?= __('browse_no_plots_found') ?></h5>
        <a href="<?= $baseUrl ?>/plots/browse" class="btn btn-primary mt-2"><?= __('browse_clear_filters') ?></a>
    </div>
    <?php else: ?>
    <div class="row g-4">
        <?php foreach ($plots as $plot): ?>
        <div class="col-md-6 col-lg-4">
            <div class="aps-cp-card h-100 d-flex flex-column" class="style-2133">
                <div class="aps-cp-card-body flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="fw-bold mb-0">
                                <?= __('browse_plot') ?> <?= htmlspecialchars($plot['plot_number'] ?? '') ?>
                            </h6>
                            <small class="text-muted">
                                <i class="fas fa-building me-1"></i><?= htmlspecialchars($plot['colony_name'] ?? '') ?>
                                <?php if (!empty($plot['block'])): ?>
                                    &middot; <?= __('browse_block') ?> <?= htmlspecialchars($plot['block'] ?? '') ?>
                                <?php endif; ?>
                            </small>
                        </div>
                        <span class="badge bg-success"><?= __('browse_available') ?></span>
                    </div>

                    <div class="my-3">
                        <div class="d-flex align-items-baseline gap-2 mb-1">
                            <span class="fs-5 fw-bold text-primary">₹<?= number_format($plot['total_price']) ?></span>
                        </div>
                        <?php
                        $psf = $plot['area_sqft'] > 0 ? number_format(round($plot['total_price'] / $plot['area_sqft'])) : '—';
                        ?>
                        <small class="text-muted">₹<?= $psf ?> / <?= __('sqft') ?></small>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="bg-light rounded p-2 text-center">
                                <small class="text-muted d-block"><?= __('browse_area') ?></small>
                                <strong><?= number_format($plot['area_sqft']) ?> <?= __('sqft') ?></strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light rounded p-2 text-center">
                                <small class="text-muted d-block"><?= __('browse_dimensions') ?></small>
                                <strong><?= htmlspecialchars($plot['dimension_label'] ?? '—') ?></strong>
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
                <div class="border-top px-3 py-2 d-flex gap-2">
                    <a href="<?= $baseUrl ?>/plots/<?= $plot['id'] ?>/detail" class="btn btn-sm btn-outline-primary flex-grow-1">
                        <i class="fas fa-eye me-1"></i><?= __('browse_view_details') ?>
                    </a>
                    <a href="<?= $baseUrl ?>/plots/<?= $plot['id'] ?>/book" class="btn btn-sm btn-primary flex-grow-1">
                        <i class="fas fa-check-circle me-1"></i><?= __('browse_book_now') ?>
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
