<?php
/**
 * Leaves Dashboard - Standardized Version
 */

require_once __DIR__ . '/core/init.php';

// Leaves stats
$total_leaves = $db->fetch("SELECT COUNT(*) AS c FROM leaves")['c'] ?? 0;
$approved_leaves = $db->fetch("SELECT COUNT(*) AS c FROM leaves WHERE status='approved'")['c'] ?? 0;
$pending_leaves = $db->fetch("SELECT COUNT(*) AS c FROM leaves WHERE status='pending'")['c'] ?? 0;
$rejected_leaves = $db->fetch("SELECT COUNT(*) AS c FROM leaves WHERE status='rejected'")['c'] ?? 0;

$page_title = "Leaves Dashboard";
include 'admin_header.php';
include 'admin_sidebar.php';
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Leaves Dashboard</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Leaves</li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto">
                    <a href="apply_leave.php" class="btn btn-primary add-btn"><i class="fa fa-plus"></i> Apply Leave</a>
                    <a href="leaves.php" class="btn btn-info add-btn"><i class="fa fa-list"></i> View Leaves</a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 col-sm-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <span class="dash-widget-icon text-primary border-primary">
                                <i class="fas fa-calendar-alt"></i>
                            </span>
                            <div class="dash-count">
                                <h3><?= number_format($total_leaves) ?></h3>
                            </div>
                        </div>
                        <div class="dash-widget-info">
                            <h6 class="text-muted">Total Leaves</h6>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-primary w-50"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <span class="dash-widget-icon text-success">
                                <i class="fas fa-check-circle"></i>
                            </span>
                            <div class="dash-count">
                                <h3><?= number_format($approved_leaves) ?></h3>
                            </div>
                        </div>
                        <div class="dash-widget-info">
                            <h6 class="text-muted">Approved</h6>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-success w-50"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <span class="dash-widget-icon text-warning border-warning">
                                <i class="fas fa-clock"></i>
                            </span>
                            <div class="dash-count">
                                <h3><?= number_format($pending_leaves) ?></h3>
                            </div>
                        </div>
                        <div class="dash-widget-info">
                            <h6 class="text-muted">Pending</h6>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-warning w-50"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <span class="dash-widget-icon text-danger border-danger">
                                <i class="fas fa-times-circle"></i>
                            </span>
                            <div class="dash-count">
                                <h3><?= number_format($rejected_leaves) ?></h3>
                            </div>
                        </div>
                        <div class="dash-widget-info">
                            <h6 class="text-muted">Rejected</h6>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-danger w-50"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'admin_footer.php'; ?>


