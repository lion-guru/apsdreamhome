<?php
$page_title = $page_title ?? 'HR Dashboard';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-users-cog me-2"></i>HR Dashboard</h4>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-4">
                <i class="fas fa-user-tie fa-3x text-primary mb-3"></i>
                <h2 class="mb-1 fw-bold"><?= $total_employees ?></h2>
                <div class="text-muted">Total users</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-4">
                <i class="fas fa-user-check fa-3x text-success mb-3"></i>
                <h2 class="mb-1 fw-bold"><?= $present_today ?></h2>
                <div class="text-muted">Present Today</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-4">
                <i class="fas fa-chart-line fa-3x text-info mb-3"></i>
                <h2 class="mb-1 fw-bold"><?= $attendance_rate ?>%</h2>
                <div class="text-muted">Attendance Rate</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-4">
                <i class="fas fa-clock fa-3x text-warning mb-3"></i>
                <h2 class="mb-1 fw-bold"><?= $pending_leaves ?></h2>
                <div class="text-muted">Pending Leaves</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="fas fa-user-tie me-2 text-primary"></i>Active users</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light"><tr><th>Name</th><th>Department</th><th>Designation</th><th>Status</th></tr></thead>
                        <tbody>
                            <?php if (empty($active_employees)): ?>
                                <tr><td colspan="4" class="text-center text-muted py-3">No active users</td></tr>
                            <?php else: ?>
                                <?php foreach ($active_employees as $e): ?>
                                    <tr>
                                        <td><a href="<?= BASE_URL ?>/admin/hr/users/view/<?= $e['id'] ?>"><?= htmlspecialchars($e['name'] ?? '') ?></a></td>
                                        <td><?= htmlspecialchars($e['department'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($e['designation'] ?? '') ?></td>
                                        <td><span class="badge bg-success">Active</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="fas fa-link me-2 text-success"></i>Quick Actions</h6>
            </div>
            <div class="card-body aps-cp-card-body">
                <div class="d-grid gap-2">
                    <a href="<?= BASE_URL ?>/admin/hr/users" class="btn btn-outline-primary text-start"><i class="fas fa-list me-2"></i>Manage users</a>
                    <a href="<?= BASE_URL ?>/admin/hr/users/create" class="btn btn-outline-success text-start"><i class="fas fa-plus me-2"></i>Add New Employee</a>
                    <a href="<?= BASE_URL ?>/admin/hr/attendance" class="btn btn-outline-info text-start"><i class="fas fa-calendar-check me-2"></i>Mark Attendance</a>
                    <a href="<?= BASE_URL ?>/admin/hr/leaves" class="btn btn-outline-warning text-start"><i class="fas fa-umbrella-beach me-2"></i>Manage Leaves</a>
                    <a href="<?= BASE_URL ?>/admin/hr/salary-structure" class="btn btn-outline-secondary text-start"><i class="fas fa-money-bill-wave me-2"></i>Salary Structures</a>
                </div>
            </div>
        </div>
    </div>
</div>
