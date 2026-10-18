<?php
/**
 * Work Distribution Dashboard
 * Overview of task distribution across all departments
*/

$stats = $stats ?? [];
$departments = $departments ?? [];
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.wd-stat-card { border: none; border-radius: 14px; overflow: hidden; position: relative; }
.wd-stat-card .stat-body { padding: 1.25rem; position: relative; z-index: 1; }
.wd-stat-card .stat-bg { position: absolute; top: 0; right: 0; width: 100px; height: 100%; opacity: 0.08; font-size: 4rem; display: flex; align-items: center; justify-content: center; }
.wd-stat-card .stat-val { font-size: 2rem; font-weight: 700; }
.wd-dept-card { border: 1px solid #e2e8f0; border-radius: 12px; transition: all 0.2s; }
.wd-dept-card:hover { border-color: #7c2d12; box-shadow: 0 4px 15px rgba(124,45,18,0.1); transform: translateY(-1px); }
.wd-dept-overloaded { border-left: 4px solid #ef4444; }
.wd-dept-underloaded { border-left: 4px solid #f59e0b; }
.wd-dept-healthy { border-left: 4px solid #22c55e; }
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-tasks me-2 text-primary"></i>Work Distribution Dashboard</h4>
            <p class="text-muted mb-0 small">Monitor and manage task distribution across departments</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/admin/work-distribution/workload" class="btn btn-outline-primary">
                <i class="fas fa-chart-bar me-1"></i> Workload Overview
            </a>
            <a href="<?= BASE_URL ?>/admin/work-distribution/analytics" class="btn btn-primary">
                <i class="fas fa-chart-line me-1"></i> Analytics
            </a>
        </div>
    </div>

    <!-- Top Stats -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card wd-stat-card bg-primary text-white">
                <div class="stat-body">
                    <div class="text-white-50 small mb-1"><i class="fas fa-building me-1"></i> Active Departments</div>
                    <div class="stat-val"><?= count($departments) ?></div>
                    <div class="small text-white-50">with active config</div>
                </div>
                <div class="stat-bg"><i class="fas fa-building"></i></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card wd-stat-card bg-warning text-white">
                <div class="stat-body">
                    <div class="text-white-50 small mb-1"><i class="fas fa-exclamation-triangle me-1"></i> Overloaded Departments</div>
                    <div class="stat-val"><?= array_sum(array_column($stats, 'overloaded')) ?></div>
                    <div class="small text-white-50">need attention</div>
                </div>
                <div class="stat-bg"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card wd-stat-card bg-info text-white">
                <div class="stat-body">
                    <div class="text-white-50 small mb-1"><i class="fas fa-clock me-1"></i> Underloaded Departments</div>
                    <div class="stat-val"><?= array_sum(array_column($stats, 'underloaded')) ?></div>
                    <div class="small text-white-50">available capacity</div>
                </div>
                <div class="stat-bg"><i class="fas fa-clock"></i></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card wd-stat-card bg-success text-white">
                <div class="stat-body">
                    <div class="text-white-50 small mb-1"><i class="fas fa-tasks me-1"></i> Pending Tasks</div>
                    <div class="stat-val"><?= array_sum(array_column($stats, 'total_tasks')) ?></div>
                    <div class="small text-white-50">awaiting assignment</div>
                </div>
                <div class="stat-bg"><i class="fas fa-tasks"></i></div>
            </div>
        </div>
    </div>

    <!-- Department Grid -->
    <div class="row g-3">
        <?php foreach ($departments as $dept): 
            $deptCode = $dept['code'];
            $deptStat = $stats[$deptCode] ?? ['total_employees' => 0, 'overloaded' => 0, 'underloaded' => 0, 'total_tasks' => 0];
            $totalEmp = $deptStat['total_employees'];
            $overloaded = $deptStat['overloaded'];
            $underloaded = $deptStat['underloaded'];
            $totalTasks = $deptStat['total_tasks'];
            
            $cardClass = 'wd-dept-card';
            if ($overloaded > 0) $cardClass .= ' wd-dept-overloaded';
            elseif ($underloaded > 0) $cardClass .= ' wd-dept-underloaded';
            else $cardClass .= ' wd-dept-healthy';
        ?>
            <div class="col-md-6 col-lg-4">
                <div class="card <?= $cardClass ?> shadow-sm h-100">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-semibold"><?= htmlspecialchars($dept['name']) ?></h5>
                        <span class="badge bg-primary bg-opacity-10 text-primary"><?= htmlspecialchars($deptCode) ?></span>
                    </div>
                    <div class="card-body">
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="text-center p-2 bg-light rounded">
                                    <div class="fw-bold fs-4 text-primary"><?= $totalEmp ?></div>
                                    <div class="small text-muted">Employees</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center p-2 bg-light rounded">
                                    <div class="fw-bold fs-4 text-info"><?= $deptStat['total_tasks'] ?></div>
                                    <div class="small text-muted">Pending Tasks</div>
                                </div>
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="text-center p-2 <?= $overloaded > 0 ? 'bg-danger bg-opacity-10' : 'bg-light' ?> rounded">
                                    <div class="fw-bold fs-5 text-danger"><?= $overloaded ?></div>
                                    <div class="small text-muted">Overloaded</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center p-2 <?= $underloaded > 0 ? 'bg-warning bg-opacity-10' : 'bg-light' ?> rounded">
                                    <div class="fw-bold fs-5 text-warning"><?= $underloaded ?></div>
                                    <div class="small text-muted">Underloaded</div>
                                </div>
                            </div>
                        </div>
                        <hr class="my-2">
                        <div class="d-grid gap-2">
                            <a href="<?= BASE_URL ?>/admin/work-distribution/<?= urlencode($deptCode) ?>" class="btn btn-outline-primary">
                                <i class="fas fa-eye me-1"></i> View Department
                            </a>
                            <a href="<?= BASE_URL ?>/admin/work-distribution/<?= urlencode($deptCode) ?>/config" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-cog me-1"></i> Configure
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
                <div class="mb-3"><i class="fas fa-building fa-4x text-muted opacity-25"></i></div>
                <h5 class="text-muted">No Departments Configured</h5>
                <p class="text-muted small mb-3">Create departments to start distributing work</p>
                <a href="<?= BASE_URL ?>/admin/departments/create" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> Create Department
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>