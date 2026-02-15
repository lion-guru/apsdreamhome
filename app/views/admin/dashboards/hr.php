<?php include APP_PATH . '/views/admin/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4"><i class="fas fa-users me-2"></i>Human Resources Dashboard</h2>
        </div>
    </div>

    <!-- HR Metrics -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-start border-primary border-4 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small">Total Employees</h6>
                    <h3><?php echo $employee_stats['total'] ?? '45'; ?></h3>
                    <p class="text-muted mb-0">Across all branches</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-start border-warning border-4 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small">Pending Leave Requests</h6>
                    <h3><?php echo $pending_leaves['count'] ?? '3'; ?></h3>
                    <p class="text-warning mb-0"><i class="fas fa-clock me-1"></i>Awaiting approval</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-start border-success border-4 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small">New Hires (This Month)</h6>
                    <h3><?php echo $employee_stats['new_hires'] ?? '2'; ?></h3>
                    <p class="text-success mb-0"><i class="fas fa-plus-circle me-1"></i>Onboarding in progress</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Employee Attendance/Performance -->
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Employee Attendance Summary</h5>
                    <button class="btn btn-sm btn-outline-primary">View Full Report</button>
                </div>
                <div class="card-body" style="height: 300px; display: flex; align-items: center; justify-content: center; background: #f8f9fa;">
                    <div class="text-center text-muted">
                        <i class="fas fa-calendar-alt fa-3x mb-3"></i>
                        <p>Attendance Visualization (Integration Pending)</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Leave Requests -->
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Recent Leave Requests</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php if (empty($pending_leaves['list'])): ?>
                            <li class="list-group-item text-center py-4 text-muted small">No pending requests</li>
                        <?php else: ?>
                            <?php foreach ($pending_leaves['list'] as $leave): ?>
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong><?php echo $leave['name']; ?></strong>
                                        <span class="badge bg-warning"><?php echo $leave['days']; ?> days</span>
                                    </div>
                                    <small class="text-muted"><?php echo $leave['reason']; ?></small>
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
