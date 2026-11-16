<?php
/**
 * Employee Department Overview / Fallback View
 * APS Dream Home — Employee Portal
 */
$page_title = $page_title ?? 'Department Dashboard';
$dept_title = $dept_title ?? 'Department Overview';
$dept_icon  = $dept_icon ?? 'fas fa-briefcase';
$dept_desc  = $dept_desc ?? 'Department operational overview and activities.';
$dept_color = $dept_color ?? '#1e293b';
$dept_slug  = $dept_slug ?? 'department';
$employee_id = $employee_id ?? ($_SESSION['employee_id'] ?? 0);
$employee_name = $employee_name ?? ($_SESSION['employee_name'] ?? $_SESSION['user_name'] ?? 'Employee');
$base = defined('BASE_URL') ? BASE_URL : '';
?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="<?= $base ?>/employee/dashboard">Employee Portal</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($dept_title) ?></li>
                </ol>
            </nav>
            <h2 class="h3 mb-0 text-gray-800">
                <i class="<?= htmlspecialchars($dept_icon) ?> me-2" style="color: <?= htmlspecialchars($dept_color) ?>;"></i>
                <?= htmlspecialchars($dept_title) ?>
            </h2>
        </div>
        <div>
            <span class="badge bg-secondary px-3 py-2">
                <i class="fas fa-user me-1"></i> <?= htmlspecialchars($employee_name) ?>
            </span>
        </div>
    </div>

    <!-- Overview Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-left: 5px solid <?= htmlspecialchars($dept_color) ?> !important;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle p-3 text-white me-3" style="background-color: <?= htmlspecialchars($dept_color) ?>;">
                            <i class="<?= htmlspecialchars($dept_icon) ?> fa-2x"></i>
                        </div>
                        <div>
                            <h4 class="card-title mb-1"><?= htmlspecialchars($dept_title) ?> Operations</h4>
                            <p class="text-muted mb-0"><?= htmlspecialchars($dept_desc) ?></p>
                        </div>
                    </div>
                    <hr>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded text-center">
                                <h6 class="text-muted mb-1">Portal Access</h6>
                                <span class="badge bg-success">Active Operational</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded text-center">
                                <h6 class="text-muted mb-1">Assigned Employee</h6>
                                <span class="fw-bold"><?= htmlspecialchars($employee_name) ?></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded text-center">
                                <h6 class="text-muted mb-1">Quick Link</h6>
                                <a href="<?= $base ?>/employee/dashboard" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-home me-1"></i> Main Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
