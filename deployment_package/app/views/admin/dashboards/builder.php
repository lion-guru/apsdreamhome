<?php include APP_PATH . '/views/admin/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4"><i class="fas fa-hard-hat me-2"></i>Construction & Builder Dashboard</h2>
        </div>
    </div>

    <!-- Construction Overview -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-start border-primary border-4 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small">Construction Progress</h6>
                    <h3><?php echo $construction_progress['overall'] ?? '65%'; ?></h3>
                    <p class="text-success mb-0"><i class="fas fa-arrow-up me-1"></i>5% from last month</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-start border-warning border-4 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small">Material Requests</h6>
                    <h3><?php echo $material_requests['pending'] ?? '4'; ?></h3>
                    <p class="text-warning mb-0"><i class="fas fa-clock me-1"></i>Awaiting approval</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-start border-success border-4 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small">Safety Compliance</h6>
                    <h3>100%</h3>
                    <p class="text-success mb-0"><i class="fas fa-check-circle me-1"></i>No incidents reported</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Site Progress Photos/Updates -->
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Latest Site Updates</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="site-update-card p-3 border rounded bg-light">
                                <h6 class="mb-1">Phase 1 Foundation Done</h6>
                                <p class="small text-muted mb-2">Raghunath Nagri - Feb 10, 2026</p>
                                <span class="badge bg-success">Verified</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="site-update-card p-3 border rounded bg-light">
                                <h6 class="mb-1">Internal Road Levelling</h6>
                                <p class="small text-muted mb-2">APS Green Valley - Feb 14, 2026</p>
                                <span class="badge bg-primary">In Progress</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Material Request List -->
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Pending Material Requests</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush small">
                        <?php if (empty($material_requests['list'])): ?>
                            <li class="list-group-item text-center py-4 text-muted">No pending requests</li>
                        <?php else: ?>
                            <?php foreach ($material_requests['list'] as $req): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><?php echo $req['item']; ?> (<?php echo $req['qty']; ?>)</span>
                                    <span class="badge bg-warning"><?php echo $req['status']; ?></span>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/admin/layouts/footer.php'; ?>
