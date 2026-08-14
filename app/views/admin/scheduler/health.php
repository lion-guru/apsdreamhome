<?php
$pageTitle = $pageTitle ?? 'Scheduler Health';
$base = $base ?? (defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/'));
$health = $health ?? ['worker_status' => 'unknown', 'queue_size' => 0, 'last_heartbeat' => '', 'memory_usage' => 0, 'uptime' => 0, 'tasks_processed' => 0, 'failed_tasks' => 0];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-heartbeat me-2 text-danger"></i>Scheduler Health</h1>
        <a href="<?= $base ?>/admin/scheduler" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-<?= ($health['worker_status'] ?? '') === 'running' ? 'success' : 'danger' ?> shadow h-100 py-2">
                <div class="card-body aps-cp-card-body">
                    <div class="row align-items-center">
                        <div class="col"><div class="text-xs fw-bold text-uppercase mb-1">Worker Status</div><div class="h5 mb-0 fw-bold"><?= ucfirst($health['worker_status'] ?? 'Unknown') ?></div></div>
                        <div class="col-auto"><i class="fas fa-cog fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body aps-cp-card-body">
                    <div class="row align-items-center">
                        <div class="col"><div class="text-xs fw-bold text-info text-uppercase mb-1">Queue Size</div><div class="h5 mb-0 fw-bold"><?= number_format($health['queue_size'] ?? 0) ?></div></div>
                        <div class="col-auto"><i class="fas fa-tasks fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body aps-cp-card-body">
                    <div class="row align-items-center">
                        <div class="col"><div class="text-xs fw-bold text-warning text-uppercase mb-1">Memory Usage</div><div class="h5 mb-0 fw-bold"><?= number_format($health['memory_usage'] ?? 0, 1) ?> MB</div></div>
                        <div class="col-auto"><i class="fas fa-memory fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body aps-cp-card-body">
                    <div class="row align-items-center">
                        <div class="col"><div class="text-xs fw-bold text-primary text-uppercase mb-1">Tasks Processed</div><div class="h5 mb-0 fw-bold"><?= number_format($health['tasks_processed'] ?? 0) ?></div></div>
                        <div class="col-auto"><i class="fas fa-check-double fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Health Details</h6></div>
        <div class="card-body aps-cp-card-body">
            <div class="table-responsive"><table class="table table-bordered">
                <tr><th class="style-47085">Last Heartbeat</th><td><?= !empty($health['last_heartbeat']) ? htmlspecialchars($health['last_heartbeat']) : '<span class="text-danger">No heartbeat detected</span>' ?></td></tr>
                <tr><th>Uptime</th><td><?= htmlspecialchars($health['uptime'] ?? '0') ?> seconds</td></tr>
                <tr><th>Failed Tasks</th><td><span class="badge bg-<?= ($health['failed_tasks'] ?? 0) > 0 ? 'danger' : 'success' ?>"><?= number_format($health['failed_tasks'] ?? 0) ?></span></td></tr>
                <tr><th>Worker PID</th><td><?= htmlspecialchars($health['pid'] ?? 'N/A') ?></td></tr>
            </table></div>
        </div>
    </div>
</div>
