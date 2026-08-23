<?php
// Available Colonies / Plots Page
?>
<div class="container py-5">
    <div class="row mb-5 text-center">
        <div class="col-lg-8 mx-auto">
            <h1 class="display-4 fw-bold text-dark mb-3"><?= __('plots_title', [], 'Explore Our Premium Plots') ?></h1>
            <p class="lead text-muted"><?= __('plots_subtitle', [], 'Discover fully developed, verified residential and commercial plots across Uttar Pradesh\'s prime hubs.') ?></p>
            <div class="d-flex justify-content-center gap-2 mt-4">
                <span class="badge bg-light text-dark border px-3 py-2"><i class="fas fa-map-marker-alt text-danger me-1"></i> <?= __('plots_lucknow') ?></span>
                <span class="badge bg-light text-dark border px-3 py-2"><i class="fas fa-map-marker-alt text-danger me-1"></i> <?= __('plots_gorakhpur') ?></span>
                <span class="badge bg-light text-dark border px-3 py-2"><i class="fas fa-map-marker-alt text-danger me-1"></i> <?= __('plots_varanasi') ?></span>
                <span class="badge bg-light text-dark border px-3 py-2"><i class="fas fa-map-marker-alt text-danger me-1"></i> <?= __('plots_kushinagar') ?></span>
            </div>
        </div>
    </div>

    <!-- Cross-link Section: Also Explore -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-wrap gap-2 align-items-center style-1563">
                <span class="fw-semibold text-success me-2"><i class="fas fa-compass me-1"></i><?= __('also_explore') ?></span>
                <a href="<?= BASE_URL ?>/properties" class="btn btn-sm px-3 style-6722">
                    <i class="fas fa-building me-1"></i>Properties
                </a>
                <a href="<?= BASE_URL ?>/projects" class="btn btn-sm px-3 style-97522">
                    <i class="fas fa-project-diagram me-1"></i>Projects
                </a>
                <a href="<?= BASE_URL ?>/colonies" class="btn btn-sm px-3 style-66828">
                    <i class="fas fa-city me-1"></i><?= __('colonies') ?>
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <?php if (!empty($colonies)): ?>
            <?php foreach ($colonies as $colony): ?>
                <?php
                $imgRaw = $colony['image_path'] ?? '';
                $img = !empty($imgRaw) && (str_starts_with($imgRaw, 'http://') || str_starts_with($imgRaw, 'https://'))
                    ? $imgRaw
                    : (!empty($imgRaw) ? BASE_URL . '/' . ltrim($imgRaw, '/') : BASE_URL . '/assets/images/placeholder/property.svg');
                $startingPrice = !empty($colony['starting_price']) ? '₹' . number_format($colony['starting_price']) : '₹5.5 Lakh';
                ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden position-relative aps-colony-card style-42942">
                        <?php if (!empty($colony['is_featured'])): ?>
                            <span class="position-absolute top-0 start-0 m-3 badge bg-warning text-dark fw-bold px-3 py-2 shadow-sm style-82516">
                                <i class="fas fa-star me-1"></i> <?= __('plots_featured') ?>
                            </span>
                        <?php endif; ?>
                        
                        <div class="position-relative overflow-hidden style-59431">
                            <img src="<?= htmlspecialchars($img ?? '') ?>" class="w-100 h-100 object-fit-cover" alt="<?= htmlspecialchars($colony['name'] ?? '') ?>" onerror="this.src='<?= BASE_URL ?>/assets/images/placeholder/property.svg'">
                            <div class="position-absolute bottom-0 start-0 w-100 p-3 text-white d-flex align-items-end style-78528">
                                <span class="badge bg-primary px-2 py-1"><i class="fas fa-map-marker-alt me-1"></i> <?= htmlspecialchars($colony['district_name'] . ', ' . $colony['state_name']) ?></span>
                            </div>
                        </div>

                        <div class="card-body p-4 d-flex flex-column">
                            <h4 class="card-title fw-bold text-dark mb-2"><?= htmlspecialchars($colony['name'] ?? '') ?></h4>
                            <p class="card-text text-muted small flex-grow-1"><?= htmlspecialchars(substr(strip_tags($colony['description'] ?? ''), 0, 120)) ?>...</p>
                            
                            <hr class="text-muted opacity-25 my-3">
                            
                            <div class="row g-0 align-items-center mb-3">
                                <div class="col-6">
                                    <span class="text-muted d-block small"><?= __('plots_starting_from') ?></span>
                                    <span class="fs-5 fw-bold text-primary"><?= $startingPrice ?></span>
                                </div>
                                <div class="col-6 text-end">
                                    <span class="text-muted d-block small"><?= __('plots_available') ?></span>
                                    <span class="fs-5 fw-bold text-success"><?= (int)($colony['available_plots'] ?? 0) ?></span>
                                </div>
                            </div>
                            
                            <a href="<?= BASE_URL ?>/colony/<?= htmlspecialchars($colony['slug'] ?? '') ?>/plots" class="btn btn-primary w-100 rounded-3 py-2 fw-semibold">
                                View Layout & Plots <i class="fas fa-arrow-right ms-1 small"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <div class="empty-state p-5 bg-white rounded-4 shadow-sm border">
                    <i class="fas fa-map-marked-alt text-muted fa-4x mb-3 opacity-25"></i>
                    <h3 class="fw-bold"><?= __('plots_no_colonies') ?></h3>
                    <p class="text-muted"><?= __('plots_no_colonies_desc') ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.aps-colony-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 24px rgba(0,0,0,0.1) !important;
}
.object-fit-cover {
    object-fit: cover;
}
</style>
