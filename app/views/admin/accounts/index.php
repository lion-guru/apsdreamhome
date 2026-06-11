<?php

/**
 * Accounts/Financial Management - APS Dream Home Admin
 */
$page_title = 'Accounts & Finance';
$page_description = 'Financial management and accounting';


?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-2">Accounts & Finance</h1>
            <p class="text-muted">Financial management and accounting overview</p>
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
                            <div class="bg-success bg-opacity-10 text-success rounded p-3">
                                <i class="fas fa-rupee-sign fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Total Revenue</h6>
                            <h3 class="mb-0">₹2.5Cr</h3>
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
                            <div class="bg-danger bg-opacity-10 text-danger rounded p-3">
                                <i class="fas fa-money-bill-wave fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Expenses</h6>
                            <h3 class="mb-0">₹85L</h3>
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
                            <div class="bg-primary bg-opacity-10 text-primary rounded p-3">
                                <i class="fas fa-piggy-bank fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Net Profit</h6>
                            <h3 class="mb-0">₹1.65Cr</h3>
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
                                <i class="fas fa-chart-pie fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Margin</h6>
                            <h3 class="mb-0">66%</h3>
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
                    <h5 class="mb-0">Financial Tools</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="<?php echo BASE_URL; ?>/admin/accounting" class="btn btn-outline-primary w-100 py-3">
                                <i class="fas fa-book mb-2 d-block" style="font-size: 1.5rem;"></i>
                                Accounting
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?php echo BASE_URL; ?>/admin/accounting/income" class="btn btn-outline-success w-100 py-3">
                                <i class="fas fa-arrow-up mb-2 d-block" style="font-size: 1.5rem;"></i>
                                Income
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?php echo BASE_URL; ?>/admin/accounting/expenses" class="btn btn-outline-danger w-100 py-3">
                                <i class="fas fa-arrow-down mb-2 d-block" style="font-size: 1.5rem;"></i>
                                Expenses
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?php echo BASE_URL; ?>/admin/analytics/financial" class="btn btn-outline-info w-100 py-3">
                                <i class="fas fa-chart-line mb-2 d-block" style="font-size: 1.5rem;"></i>
                                Analytics
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