<?php
/**
 * Workload Overview - Cross-department view
*/

$all_workload = $all_workload ?? [];
$departments = $departments ?? [];
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.wl-dept-card { border: 1px solid #e2e8f0; border-radius: 12px; transition: all 0.2s; }
.wl-dept-card:hover { border-color: #7c2d12; box-shadow: 0 4px 15px rgba(124,45,18,0.1); transform: translateY(-1px); }
.wl-emp-card { border: 1px solid #e2e8f0; border-radius: 10px; transition: all 0.2s; }
.wl-emp-card:hover { border-color: #7c2d12; box-shadow: 0 4px 15px rgba(124,45,18,0.1); }
.wl-emp-overloaded { border-left: 3px solid #ef4444; }
.wl-emp-underloaded { border-left: 3px solid #f59e0b; }
.wl-emp-healthy { border-left: 3px solid #22c55e; }
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-chart-bar me-2 text-primary"></i>Workload Overview</h4>
            <p class="text-muted mb-0 small">Cross-department workload monitoring and management</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/admin/work-distribution" class="btn btn-outline-secondary">
                <i class="fas fa-tasks me-1"></i> Distribution Dashboard
            </a>
            <a href="<?= BASE_URL ?>/admin/work-distribution/analytics" class="btn btn-primary">
                <i class="fas fa-chart-line me-1"></i> Analytics
            </a>
        </div>
    </div>

    <!-- Global Summary -->
    <?php 
    $totalEmp = 0;
    $totalOverloaded = 0;
    $totalUnderloaded = 0;
    $totalTasks = 0;
    foreach ($all_workload as $deptCode => $workload) {
        foreach ($workload as $w) {
            $totalEmp++;
            $totalTasks += $w['active_tasks'];
            if ($w['is_overloaded']) $totalOverloaded++;
            if ($w['is_underloaded']) $totalUnderloaded++;
        }
    }
    $healthy = $totalEmp - $totalOverloaded - $totalUnderloaded;
    ?>
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card stat-card border-0 shadow-sm bg-primary text-white">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-white bg-opacity-10"><i class="fas fa-users"></i></div>
                    <div><div class="stat-val fs-3"><?= $totalEmp ?></div><div class="small text-white-50">Total Employees</div></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card stat-card border-0 shadow-sm bg-success text-white">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-white bg-opacity-10"><i class="fas fa-check-circle"></i></div>
                    <div><div class="stat-val fs-3"><?= $healthy ?></div><div class="small text-white-50">Healthy</div></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card stat-card border-0 shadow-sm bg-warning text-white">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-white bg-opacity-10"><i class="fas fa-exclamation-triangle"></i></div>
                    <div><div class="stat-val fs-3"><?= $totalOverloaded ?></div><div class="small text-white-50">Overloaded</div></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card stat-card border-0 shadow-sm bg-info text-white">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-white bg-opacity-10"><i class="fas fa-clock"></i></div>
                    <div><div class="stat-val fs-3"><?= $totalUnderloaded ?></div><div class="small text-white-50">Underloaded</div></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Department Cards -->
    <div class="row g-3 mb-4">
        <?php foreach ($departments as $dept): 
            $deptCode = $dept['code'];
            $workload = $all_workload[$deptCode] ?? [];
            $deptOverloaded = count(array_filter($workload, fn($w) => $w['is_overloaded']));
            $deptUnderloaded = count(array_filter($workload, fn($w) => $w['is_underloaded']));
            $deptHealthy = count($workload) - $deptOverloaded - $deptUnderloaded;
            $deptTasks = array_sum(array_column($workload, 'active_tasks'));
        ?>
            <div class="col-md-6 col-lg-4">
                <div class="card wl-dept-card shadow-sm h-100">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-semibold"><?= htmlspecialchars($dept['name']) ?></h5>
                        <span class="badge bg-primary bg-opacity-10 text-primary"><?= htmlspecialchars($deptCode) ?></span>
                    </div>
                    <div class="card-body">
                        <div class="row g-2 mb-3">
                            <div class="col-4 text-center">
                                <div class="fw-bold fs-4 text-primary"><?= count($all_workload[$deptCode] ?? []) ?></div>
                                <div class="small text-muted">Employees</div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="fw-bold fs-4 text-info"><?= $deptTasks ?></div>
                                <div class="small text-muted">Active Tasks</div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="fw-bold fs-4 text-<?= $deptOverloaded > 0 ? 'danger' : 'success' ?>"><?= $deptOverloaded ?></div>
                                <div class="small text-muted">Overloaded</div>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="<?= BASE_URL ?>/admin/work-distribution/<?= urlencode($deptCode) ?>" class="btn btn-outline-primary flex-fill">
                                <i class="fas fa-eye me-1"></i> Manage
                            </a>
                            <a href="<?= BASE_URL ?>/admin/work-distribution/<?= urlencode($deptCode) ?>/workload" class="btn btn-outline-secondary flex-fill">
                                <i class="fas fa-chart-bar me-1"></i> Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (empty($departments)): ?>
        <div class="card shadow-sm border-0">
            <div class="card-body text-center py-5">
                <div class="mb-3"><i class="fas fa-chart-bar fa-4x text-muted opacity-25"></i></div>
                <h5 class="text-muted">No Departments</h5>
            </div>
        </div>
    <?php endif; ?>
</div>