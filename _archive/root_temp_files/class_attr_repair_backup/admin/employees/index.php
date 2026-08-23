<?php

/**
 * users Management - APS Dream Home Admin
 */
$page_title = 'users Management';
$page_description = 'Manage users and team members';


?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-2">users Management</h1>
            <p class="text-muted">Manage users and team members</p>
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
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Total users</h6>
                            <h3 class="mb-0">45</h3>
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
                                <i class="fas fa-user-check fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Active</h6>
                            <h3 class="mb-0">38</h3>
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
                                <i class="fas fa-user-clock fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">On Leave</h6>
                            <h3 class="mb-0">4</h3>
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
                                <i class="fas fa-user-plus fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">New Joined</h6>
                            <h3 class="mb-0">3</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <a href="<?php echo BASE_URL; ?>/admin/users" class="btn btn-outline-primary w-100 py-3">
                                <i class="fas fa-users-cog mb-2 d-block" class="style-41417"></i>
                                Manage Users
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="<?php echo BASE_URL; ?>/admin/team" class="btn btn-outline-success w-100 py-3">
                                <i class="fas fa-users mb-2 d-block" class="style-41417"></i>
                                Team Management
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="<?php echo BASE_URL; ?>/admin/hrm" class="btn btn-outline-info w-100 py-3">
                                <i class="fas fa-briefcase mb-2 d-block" class="style-41417"></i>
                                HRM Module
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