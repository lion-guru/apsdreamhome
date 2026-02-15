<?php include APP_PATH . '/views/admin/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4"><i class="fas fa-tasks me-2"></i>Operations Management Dashboard</h2>
        </div>
    </div>

    <!-- Operations Overview -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-start border-primary border-4 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small">Inventory Items</h6>
                    <h3><?php echo $inventory_stats['total_items'] ?? '240'; ?></h3>
                    <p class="text-muted mb-0">Across 3 warehouses</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-start border-warning border-4 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small">Low Stock Alerts</h6>
                    <h3><?php echo $inventory_stats['low_stock'] ?? '5'; ?></h3>
                    <p class="text-danger mb-0"><i class="fas fa-exclamation-circle me-1"></i>Action required</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-start border-success border-4 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small">Completed Tasks (Today)</h6>
                    <h3><?php echo $task_status['completed_today'] ?? '18'; ?></h3>
                    <p class="text-success mb-0"><i class="fas fa-check-circle me-1"></i>On track</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Task Status -->
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Ongoing Operational Tasks</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold">Site Survey - Raghunath Nagri</span>
                                <span class="badge bg-primary">In Progress</span>
                            </div>
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar" style="width: 45%"></div>
                            </div>
                        </div>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold">Material Delivery - Phase 2</span>
                                <span class="badge bg-warning text-dark">Pending</span>
                            </div>
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar bg-warning" style="width: 10%"></div>
                            </div>
                        </div>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold">Client Site Visit Scheduling</span>
                                <span class="badge bg-success">Completed</span>
                            </div>
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar bg-success" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inventory Summary -->
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Inventory Summary</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Cement (Bags)</span>
                            <span class="fw-bold">1,200</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Steel (Tons)</span>
                            <span class="fw-bold">15</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between text-danger">
                            <span>Bricks (Units)</span>
                            <span class="fw-bold">5,000 <i class="fas fa-arrow-down ms-1"></i></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Electrical Fittings</span>
                            <span class="fw-bold">OK</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/admin/layouts/footer.php'; ?>
