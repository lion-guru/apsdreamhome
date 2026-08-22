<?php $pageTitle = 'Operations Dashboard'; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><i class="fas fa-home me-1"></i>Home</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/employee/dashboard">Employee</a></li>
            <li class="breadcrumb-item active" aria-current="page">Operations Dashboard</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-cogs me-2"></i>Operations Dashboard</h4>
        <span class="text-muted small"><i class="far fa-calendar-alt me-1"></i><?= date('l, F j, Y') ?></span>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-primary mb-2"><i class="fas fa-project-diagram"></i></div>
                    <h3 class="fw-bold mb-1"><?= e($activeProjects ?? 0) ?></h3>
                    <p class="text-muted mb-0">Active Projects</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-warning mb-2"><i class="fas fa-clipboard-check"></i></div>
                    <h3 class="fw-bold mb-1"><?= e($pendingApprovals ?? 0) ?></h3>
                    <p class="text-muted mb-0">Pending Approvals</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-success mb-2"><i class="fas fa-truck"></i></div>
                    <h3 class="fw-bold mb-1"><?= e($activeVendors ?? 0) ?></h3>
                    <p class="text-muted mb-0">Active Vendors</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-info mb-2"><i class="fas fa-calendar-day"></i></div>
                    <h3 class="fw-bold mb-1"><?= e($siteVisitsToday ?? 0) ?></h3>
                    <p class="text-muted mb-0">Site Visits Today</p>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0"><h6 class="mb-0"><i class="fas fa-tasks me-2"></i>Pending Operations</h6></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($pendingOperations)): ?>
                        <div class="table-responsive"><table class="table table-sm table-hover mb-0">
                            <thead><tr><th>Task</th><th>Assigned To</th><th>Due Date</th><th>Priority</th></tr></thead>
                            <tbody>
                                <?php foreach ($pendingOperations as $op): ?>
                                <tr>
                                    <td class="small"><?= htmlspecialchars($op['title'] ?? '') ?></td>
                                    <td class="small"><?= htmlspecialchars($op['assigned_to'] ?? '') ?></td>
                                    <td class="small"><?= htmlspecialchars($op['due_date'] ?? '') ?></td>
                                    <td><span class="badge bg-<?= ($op['priority'] ?? '') === 'high' ? 'danger' : (($op['priority'] ?? '') === 'medium' ? 'warning' : 'info') ?>"><?= ucfirst($op['priority'] ?? '') ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table></div>
                    <?php else: ?>
                        <div class="text-center py-4"><i class="fas fa-check-circle fa-2x text-success mb-2"></i><p class="text-muted mb-0">No pending operations</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0"><h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Project Status</h6></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($projectStatus)): ?>
                        <?php foreach ($projectStatus as $ps): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span><?= htmlspecialchars($ps['label'] ?? '') ?></span>
                            <span class="badge bg-primary"><?= e($ps['count'] ?? 0) ?></span>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4"><i class="fas fa-chart-pie fa-2x text-muted mb-2"></i><p class="text-muted mb-0">No project data</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
