<?php
$extraHead = '<style>
.investment-card-hover { transition: transform 0.3s ease, shadow 0.3s ease; }
.investment-card-hover:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
</style>';

$investments = $investments ?? [];
?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="h3 mb-0"><i class="fas fa-chart-line me-2 text-primary"></i><?= __('user_inv2_title', null, 'My Investments') ?></h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>user/dashboard"><?= __('user_inv2_dashboard', null, 'Dashboard') ?></a></li>
                        <li class="breadcrumb-item active"><?= __('user_inv2_investments', null, 'Investments') ?></li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-primary text-white">
                <div class="card-body aps-cp-card-body">
                    <h6 class="text-white-50 small fw-bold text-uppercase mb-2"><?= __('user_inv2_total_plots', null, 'Total Active Plots') ?></h6>
                    <h3 class="mb-0 fw-bold"><?= count($investments) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <?php if (empty($investments)): ?>
            <div class="col-12">
                <div class="card border-0 shadow-sm text-center py-5">
                    <div class="card-body aps-cp-card-body">
                        <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                        <h5><?= __('user_inv2_empty_title', null, 'No active investments found') ?></h5>
                        <p class="text-muted"><?= __('user_inv2_empty_desc', null, "You haven't purchased any plots yet.") ?></p>
                        <a href="<?= BASE_URL ?>properties" class="btn btn-primary px-4"><?= __('user_inv2_browse', null, 'Browse Properties') ?></a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($investments as $inv): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm investment-card-hover overflow-hidden">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="bg-primary-subtle text-primary p-2 rounded">
                                    <i class="fas fa-map-marked-alt fa-lg"></i>
                                </div>
                                <span class="badge bg-success rounded-pill px-3"><?= __('user_inv2_active', null, 'ACTIVE') ?></span>
                            </div>
                            <h5 class="card-title fw-bold mb-1"><?= htmlspecialchars($inv['site_name'] ?? 'N/A') ?></h5>
                            <p class="text-muted small mb-3">
                                <i class="fas fa-map-marker-alt me-1 text-danger"></i>
                                <?= htmlspecialchars($inv['site_location'] ?? 'N/A') ?>
                            </p>
                            <hr class="my-3 opacity-10">
                            <div class="row g-3">
                                <div class="col-6">
                                    <label class="text-muted small d-block mb-1"><?= __('user_inv2_plot_number', null, 'Plot Number') ?></label>
                                    <span class="fw-bold"><?= htmlspecialchars($inv['plot_number'] ?? 'N/A') ?></span>
                                </div>
                                <div class="col-6">
                                    <label class="text-muted small d-block mb-1"><?= __('user_inv2_sector', null, 'Sector') ?></label>
                                    <span class="fw-bold"><?= htmlspecialchars($inv['sector'] ?? 'N/A') ?></span>
                                </div>
                                <div class="col-6">
                                    <label class="text-muted small d-block mb-1"><?= __('user_inv2_size', null, 'Size') ?></label>
                                    <span class="fw-bold"><?= number_format($inv['area_sqft'] ?? 0) ?> <?= __('unit_sqft', null, 'sq.ft') ?></span>
                                </div>
                                <div class="col-6">
                                    <label class="text-muted small d-block mb-1"><?= __('user_inv2_price', null, 'Price') ?></label>
                                    <span class="fw-bold">&#8377;<?= number_format($inv['total_price'] ?? 0) ?></span>
                                </div>
                            </div>
                            <div class="d-grid mt-4">
                                <a href="<?= BASE_URL ?>properties/<?= $inv['id'] ?? '' ?>" class="btn btn-outline-primary"><?= __('user_inv2_view_details', null, 'View Details') ?></a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
