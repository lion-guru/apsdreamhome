<?php

/**
 * Commissions Management - APS Dream Home Admin
 */
$page_title = 'Commissions Management';
$page_description = 'Manage MLM commissions and payouts';


?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-2">Commissions Management</h1>
            <p class="text-muted">Manage MLM commissions, plans and payouts</p>
        </div>
    </div>

    <!-- Search and Export -->
    <?php require __DIR__ . '/../partials/search_bar.php'; ?>
    <?php require __DIR__ . '/../partials/export_buttons.php'; ?>
    <?php require __DIR__ . '/../partials/mobile_optimization.php'; ?>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-primary bg-opacity-10 text-primary rounded p-3">
                                <i class="fas fa-coins fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Total Commission</h6>
                            <h3 class="mb-0">₹12.5L</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-success bg-opacity-10 text-success rounded p-3">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Paid Out</h6>
                            <h3 class="mb-0">₹8.2L</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-warning bg-opacity-10 text-warning rounded p-3">
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Pending</h6>
                            <h3 class="mb-0">₹4.3L</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-info bg-opacity-10 text-info rounded p-3">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">users</h6>
                            <h3 class="mb-0">234</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Commission Tools</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <a href="<?php echo BASE_URL; ?>/admin/payouts" class="btn btn-outline-primary w-100 py-3">
                                <i class="fas fa-wallet mb-2 d-block" style="font-size: 1.5rem;"></i>
                                Process Payouts
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="<?php echo BASE_URL; ?>/admin/commission/calculator" class="btn btn-outline-success w-100 py-3">
                                <i class="fas fa-calculator mb-2 d-block" style="font-size: 1.5rem;"></i>
                                Calculator
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="<?php echo BASE_URL; ?>/admin/mlm" class="btn btn-outline-info w-100 py-3">
                                <i class="fas fa-sitemap mb-2 d-block" style="font-size: 1.5rem;"></i>
                                MLM Network
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php


?>