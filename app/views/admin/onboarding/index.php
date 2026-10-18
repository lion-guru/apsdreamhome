<?php
/**
 * Onboarding Dashboard
*/

$page_title = $page_title ?? 'Employee Onboarding';
$templates = $templates ?? [];
$stats = $stats ?? [];
$employees = $employees ?? [];
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.onb-card { border: 1px solid #e2e8f0; border-radius: 12px; transition: all 0.2s; }
.onb-card:hover { border-color: #7c2d12; box-shadow: 0 4px 15px rgba(124,45,18,0.1); transform: translateY(-2px); }
.onb-progress { height: 8px; border-radius: 4px; background: #e2e8f0; overflow: hidden; }
.onb-progress-fill { height: 100%; border-radius: 4px; background: linear-gradient(90deg, #7c2d12, #b45309); transition: width 0.5s ease; }
.onb-template-card { border: 1px solid #e2e8f0; border-radius: 10px; transition: all 0.2s; cursor: pointer; }
.onb-template-card:hover { border-color: #7c2d12; box-shadow: 0 4px 15px rgba(124,45,18,0.1); }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-user-plus me-2 text-primary"></i>Employee Onboarding</h4>
            <p class="text-muted mb-0 small">Manage employee onboarding workflows and track progress</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/onboarding/create-template" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Create Template
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-primary text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= $stats['total_items'] ?? 0 ?></div>
                    <div class="text-white-50 small">Total Checklist Items</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-success text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-4"><?= $stats['completed'] ?? 0 ?></div>
                    <div class="text-white-50 small">Completed</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-warning text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-4"><?= $stats['in_progress'] ?? 0 ?></div>
                    <div class="text-white-50 small">In Progress</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-info text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-4"><?= $stats['pending'] ?? 0 ?></div>
                    <div class="text-white-50 small">Pending</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Templates -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold"><i class="fas fa-file-alt me-2"></i>Onboarding Templates</h5>
            <a href="<?= BASE_URL ?>/admin/onboarding/create-template" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> New Template
            </a>
        </div>
        <div class="card-body">
            <?php if (empty($templates)): ?>
            <div class="text-center py-5">
                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Templates Yet</h5>
                <p class="text-muted small mb-3">Create your first onboarding template to get started</p>
                <a href="<?= BASE_URL ?>/admin/onboarding/create-template" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Create Template
                </a>
            </div>
            <?php else: ?>
            <div class="row g-3">
                <?php foreach ($templates as $t): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="onb-template-card card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="mb-1 fw-semibold"><?= htmlspecialchars($t['name']) ?></div>
                                <span class="badge bg-primary"><?= $t['item_count'] ?? 0 ?> items</span>
                            </div>
                            <p class="text-muted small mb-3"><?= htmlspecialchars($t['description'] ?? 'No description') ?></p>
                            <div class="d-flex gap-2">
                                <a href="<?= BASE_URL ?>/admin/onboarding/template/<?= $t['id'] ?>" class="btn btn-sm btn-outline-primary flex-fill">
                                    <i class="fas fa-edit me-1"></i> Edit
                                </a>
                                <form method="POST" action="<?= BASE_URL ?>/admin/onboarding/template/<?= $t['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Delete this template?')">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger flex-fill" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Employee Onboarding Status -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold"><i class="fas fa-users me-2"></i>Employee Onboarding Status</h5>
            <a href="<?= BASE_URL ?>/admin/onboarding/assign" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-plus me-1"></i> Assign Template
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="onboardingTable">
                    <thead class="table-light">
                        <tr>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Progress</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($employees as $emp): 
                            $progress = $emp['total_items'] > 0 ? round(($emp['completed_items'] / $emp['total_items']) * 100) : 0;
                            $statusClass = $emp['completed_items'] == $emp['total_items'] && $emp['total_items'] > 0 ? 'success' : ($emp['total_items'] > 0 ? 'warning' : 'secondary');
                        ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($emp['name']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($emp['employee_code']) ?></small>
                            </td>
                            <td><?= htmlspecialchars($emp['department'] ?? 'N/A') ?></td>
                            <td>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-<?= $progress === 100 ? 'success' : 'primary' ?>" style="width: <?= $progress ?>%"></div>
                                </div>
                                <small class="text-muted"><?= $progress ?>% (<?= $emp['completed_items'] ?>/<?= $emp['total_items'] ?>)</small>
                            </td>
                            <td>
                                <span class="badge bg-<?= $statusClass ?>"><?= 
                                    $progress === 100 ? 'Complete' : ($progress > 0 ? 'In Progress' : 'Not Started') 
                                ?></span>
                            </td>
                            <td>
                                <a href="<?= BASE_URL ?>/admin/onboarding/<?= $emp['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye me-1"></i> View
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($employees)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fas fa-user-plus fa-2x mb-2"></i>
                                <div>No active employees found</div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
// DataTables initialization if needed
$(document).ready(function() {
    if ($.fn.DataTable && $('#onboardingTable').length) {
        $('#onboardingTable').DataTable({
            pageLength: 10,
            order: [[3, 'asc']],
            columnDefs: [{ orderable: false, targets: [4] }]
        });
    }
});
</script>