<?php $pageTitle = 'HR Dashboard'; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><i class="fas fa-home me-1"></i>Home</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/employee/dashboard">Employee</a></li>
            <li class="breadcrumb-item active" aria-current="page">HR Dashboard</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-users me-2"></i>HR Dashboard</h4>
        <a href="<?= BASE_URL ?>/employee/payroll" class="btn btn-sm btn-outline-primary"><i class="fas fa-print me-1"></i>Payroll</a>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-primary mb-2"><i class="fas fa-user-friends"></i></div>
                    <h3 class="fw-bold mb-1"><?= $totalEmployees ?? 0 ?></h3>
                    <p class="text-muted mb-0">Total users</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-success mb-2"><i class="fas fa-user-check"></i></div>
                    <h3 class="fw-bold mb-1"><?= $presentToday ?? 0 ?></h3>
                    <p class="text-muted mb-0">Present Today</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-warning mb-2"><i class="fas fa-user-clock"></i></div>
                    <h3 class="fw-bold mb-1"><?= $onLeave ?? 0 ?></h3>
                    <p class="text-muted mb-0">On Leave</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-info mb-2"><i class="fas fa-briefcase"></i></div>
                    <h3 class="fw-bold mb-1"><?= $openPositions ?? 0 ?></h3>
                    <p class="text-muted mb-0">Open Positions</p>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0"><h6 class="mb-0"><i class="fas fa-clock me-2"></i>Recent Attendance</h6></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($attendance)): ?>
                        <div class="table-responsive"><div class="table-responsive"><table class="table table-sm table-hover mb-0 table-responsive">
                            <thead><tr><th>Employee</th><th>Date</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php foreach ($attendance as $a): ?>
                                <tr>
                                    <td class="small"><?= htmlspecialchars($a['employee_name'] ?? '') ?></td>
                                    <td class="small"><?= htmlspecialchars($a['date'] ?? '') ?></td>
                                    <td><span class="badge bg-<?= ($a['status'] ?? '') === 'present' ? 'success' : 'secondary' ?>"><?= ucfirst($a['status'] ?? '') ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table></div></div>
                    <?php else: ?>
                        <div class="text-center py-4"><i class="fas fa-clipboard-list fa-2x text-muted mb-2"></i><p class="text-muted mb-0">No attendance records</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0"><h6 class="mb-0"><i class="fas fa-file-invoice me-2"></i>Payroll Summary</h6></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($payrollSummary)): ?>
                        <div class="d-flex justify-content-between mb-2"><span>This Month</span><strong>₹<?= number_format($payrollSummary['this_month'] ?? 0) ?></strong></div>
                        <div class="d-flex justify-content-between mb-2"><span>Last Month</span><strong>₹<?= number_format($payrollSummary['last_month'] ?? 0) ?></strong></div>
                        <div class="d-flex justify-content-between"><span>Pending</span><strong class="text-danger">₹<?= number_format($payrollSummary['pending'] ?? 0) ?></strong></div>
                    <?php else: ?>
                        <div class="text-center py-4"><i class="fas fa-file-invoice fa-2x text-muted mb-2"></i><p class="text-muted mb-0">No payroll data</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
