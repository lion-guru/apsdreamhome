<?php
/**
 * Offboarding Dashboard
*/

$page_title = $page_title ?? 'Employee Offboarding';
$stats = $stats ?? [];
$employees = $employees ?? [];
$templates = $templates ?? [];
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.offb-card { border: 1px solid #e2e8f0; border-radius: 12px; transition: all 0.2s; }
.offb-card:hover { border-color: #7c2d12; box-shadow: 0 4px 15px rgba(124,45,18,0.1); }
.offb-in_progress { border-left: 4px solid #3b82f6; }
.offb-completed { border-left: 4px solid #22c55e; }
.offb-not_started { border-left: 4px solid #94a3b8; }
.offb-terminated { border-left: 4px solid #ef4444; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-sign-out-alt me-2 text-primary"></i>Employee Offboarding</h4>
            <p class="text-muted mb-0 small">Manage employee offboarding workflows and track completion</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#initiateOffboardingModal">
            <i class="fas fa-plus me-1"></i> Initiate Offboarding
        </button>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-primary text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= $stats['total_items'] ?? 0 ?></div>
                    <div class="text-white-50 small">Total Items</div>
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
                    <div class="fw-bold fs-4"><?= $stats['pending'] ?? 0 ?></div>
                    <div class="text-white-50 small">Pending</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-info text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-4"><?= $stats['completion_rate'] ?? 0 ?>%</div>
                    <div class="text-white-50 small">Completion Rate</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Templates -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold"><i class="fas fa-file-alt me-2"></i>Offboarding Templates</h5>
            <a href="<?= BASE_URL ?>/admin/offboarding/create-template" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> New Template
            </a>
        </div>
        <div class="card-body">
            <?php if (empty($templates)): ?>
            <div class="text-center py-4">
                <i class="fas fa-file-alt fa-2x text-muted mb-2"></i>
                <h6 class="text-muted">No Offboarding Templates</h6>
                <p class="text-muted small mb-2">Create your first offboarding template</p>
                <a href="<?= BASE_URL ?>/admin/offboarding/create-template" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Create Template
                </a>
            </div>
            <?php else: ?>
            <div class="row g-3">
                <?php foreach ($templates as $t): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card offb-template-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="mb-1 fw-semibold"><?= htmlspecialchars($t['name']) ?></h6>
                                <span class="badge bg-primary"><?= $t['item_count'] ?? 0 ?> items</span>
                            </div>
                            <p class="text-muted small mb-3"><?= htmlspecialchars($t['description'] ?? 'No description') ?></p>
                            <div class="d-flex gap-2">
                                <a href="<?= BASE_URL ?>/admin/offboarding/template/<?= $t['id'] ?>" class="btn btn-sm btn-outline-primary flex-fill">
                                    <i class="fas fa-edit me-1"></i> Edit
                                </a>
                                <form method="POST" action="<?= BASE_URL ?>/admin/offboarding/template/<?= $t['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Delete this template?')">
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

    <!-- Employee Offboarding Status -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold"><i class="fas fa-users me-2"></i>Employee Offboarding Status</h5>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#initiateOffboardingModal">
                <i class="fas fa-plus me-1"></i> Initiate Offboarding
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="offboardingTable">
                    <thead class="table-light">
                        <tr>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Progress</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($employees as $emp): 
                            $statusClass = [
                                'in_progress' => 'primary',
                                'completed' => 'success',
                                'not_started' => 'secondary',
                                'terminated' => 'danger'
                            ][$emp['offboarding_status']] ?? 'secondary';
                            $progress = $emp['total_items'] > 0 ? round(($emp['completed_items'] / $emp['total_items']) * 100) : 0;
                        ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($emp['name']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($emp['employee_code']) ?></small>
                            </td>
                            <td><?= htmlspecialchars($emp['department'] ?? 'N/A') ?></td>
                            <td>
                                <span class="badge bg-<?= $statusClass ?>">
                                    <?= ucfirst(str_replace('_', ' ', $emp['offboarding_status'])) ?>
                                </span>
                            </td>
                            <td>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-<?= $progress === 100 ? 'success' : 'primary' ?>" style="width: <?= $progress ?>%"></div>
                                </div>
                                <small class="text-muted"><?= $progress ?>% (<?= $emp['completed_items'] ?>/<?= $emp['total_items'] ?>)</small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= BASE_URL ?>/admin/offboarding/<?= $emp['id'] ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if ($emp['offboarding_status'] === 'not_started'): ?>
                                        <button class="btn btn-primary btn-sm" onclick="initiateOffboarding(<?= $emp['id'] ?>)">
                                            <i class="fas fa-rocket me-1"></i> Initiate
                                        </button>
                                    <?php elseif ($emp['offboarding_status'] === 'in_progress'): ?>
                                        <a href="<?= BASE_URL ?>/admin/offboarding/<?= $emp['id'] ?>" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-tasks me-1"></i> Manage
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($employees)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fas fa-user-slash fa-2x mb-2"></i>
                                <div>No employees found</div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Initiate Offboarding Modal -->
<div class="modal fade" id="initiateOffboardingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/admin/offboarding/initiate">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-rocket me-2"></i>Initiate Offboarding</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Employee <span class="text-danger">*</span></label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">Select employee</option>
                            <?php 
                            $activeEmployees = $this->db->fetchAll("SELECT e.id, u.name, e.employee_code FROM employees e JOIN users u ON e.user_id = u.id WHERE e.status = 'active' ORDER BY u.name");
                            foreach ($activeEmployees as $e): 
                                $hasOffboarding = $this->db->fetchOne("SELECT id FROM offboarding_checklists WHERE employee_id = ? AND status != 'completed'", [$e['id']]);
                                if (!$hasOffboarding):
                            ?>
                            <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['name']) ?> (<?= htmlspecialchars($e['employee_code']) ?>)</option>
                            <?php endif; endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-rocket me-1"></i> Initiate Offboarding
                </button>
            </div>
        </div>
    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
function initiateOffboarding(employeeId) {
    if (!confirm('Are you sure you want to initiate offboarding for this employee?')) return;
    
    fetch('<?= BASE_URL ?>/admin/offboarding/initiate', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'employee_id=' + employeeId + '&csrf_token=' + encodeURIComponent('<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>')
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.reload();
        else alert(data.message || 'Failed to initiate offboarding');
    });
}
</script>