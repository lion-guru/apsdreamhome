<?php

/**
 * CRM Dashboard - APS Dream Home Admin
 */

@@session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: " . BASE_URL . "/admin/login");
    exit();
}

$page_title = 'CRM Dashboard';
$page_description = 'Customer Relationship Management';

// Start output buffering
ob_start();
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-2">CRM Dashboard</h1>
            <p class="text-muted">Customer Relationship Management System</p>
        </div>
    </div>

    <!-- Search and Export -->
    <?php require __DIR__ . '/../partials/search_bar.php'; ?>
    <?php require __DIR__ . '/../partials/export_buttons.php'; ?>
    <?php require __DIR__ . '/../partials/mobile_optimization.php'; ?>
    <?php require __DIR__ . '/../partials/realtime_updates.php'; ?>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="stats-icon bg-primary bg-opacity-10 text-primary">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Total Leads</h6>
                            <h3 class="mb-0">1,234</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="stats-icon bg-success bg-opacity-10 text-success">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Converted</h6>
                            <h3 class="mb-0">567</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="stats-icon bg-warning bg-opacity-10 text-warning">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Pending</h6>
                            <h3 class="mb-0">89</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="stats-icon bg-info bg-opacity-10 text-info">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Conversion Rate</h6>
                            <h3 class="mb-0">45.9%</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="<?php echo BASE_URL; ?>/admin/leads" class="btn btn-outline-primary w-100 py-3">
                                <i class="fas fa-list mb-2 d-block" style="font-size: 1.5rem;"></i>
                                View All Leads
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?php echo BASE_URL; ?>/admin/customers" class="btn btn-outline-success w-100 py-3">
                                <i class="fas fa-user-check mb-2 d-block" style="font-size: 1.5rem;"></i>
                                Manage Customers
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?php echo BASE_URL; ?>/admin/bookings" class="btn btn-outline-info w-100 py-3">
                                <i class="fas fa-calendar-check mb-2 d-block" style="font-size: 1.5rem;"></i>
                                View Bookings
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?php echo BASE_URL; ?>/admin/reports" class="btn btn-outline-warning w-100 py-3">
                                <i class="fas fa-chart-bar mb-2 d-block" style="font-size: 1.5rem;"></i>
                                Reports
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Recent CRM Activity</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Lead/Customer</th>
                                    <th>Action</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>2026-05-11</td>
                                    <td>Rajesh Kumar</td>
                                    <td>New lead created</td>
                                    <td><span class="badge bg-primary">New</span></td>
                                </tr>
                                <tr>
                                    <td>2026-05-10</td>
                                    <td>Priya Singh</td>
                                    <td>Follow-up completed</td>
                                    <td><span class="badge bg-success">Converted</span></td>
                                </tr>
                                <tr>
                                    <td>2026-05-10</td>
                                    <td>Amit Sharma</td>
                                    <td>Site visit scheduled</td>
                                    <td><span class="badge bg-warning">Pending</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../../layouts/admin.php';
?>