<?php include APP_PATH . '/views/admin/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4"><i class="fas fa-cogs me-2"></i>Operations Dashboard (COO)</h2>
        </div>
    </div>

    <!-- Operations Overview -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-start border-primary border-4 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small">Project Performance</h6>
                    <h3><?php echo $project_performance['overall'] ?? '94%'; ?></h3>
                    <p class="text-success mb-0"><i class="fas fa-arrow-up me-1"></i>2% increase</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-start border-info border-4 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small">Resource Utilization</h6>
                    <h3><?php echo $project_performance['resources'] ?? '88%'; ?></h3>
                    <p class="text-muted mb-0">Optimal range</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-start border-warning border-4 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small">Active Tasks</h6>
                    <h3><?php echo $project_performance['active_tasks'] ?? '156'; ?></h3>
                    <p class="text-warning mb-0"><i class="fas fa-clock me-1"></i>12 overdue</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Project Progress -->
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Ongoing Projects Progress</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small">Raghunath Nagri Phase 1</span>
                            <span class="small fw-bold">85%</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 85%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small">APS Green Valley</span>
                            <span class="small fw-bold">60%</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: 60%"></div>
                        </div>
                    </div>
                    <div class="mb-0">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small">Gorakhpur Enclave</span>
                            <span class="small fw-bold">35%</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: 35%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Alerts -->
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Operational Alerts</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex align-items-center gap-3">
                            <i class="fas fa-exclamation-triangle text-warning"></i>
                            <span>Material shortage at APS Green Valley</span>
                        </li>
                        <li class="list-group-item d-flex align-items-center gap-3">
                            <i class="fas fa-info-circle text-info"></i>
                            <span>New booking policy update published</span>
                        </li>
                        <li class="list-group-item d-flex align-items-center gap-3">
                            <i class="fas fa-check-circle text-success"></i>
                            <span>Phase 1 layout approved by Legal</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/admin/layouts/footer.php'; ?>
