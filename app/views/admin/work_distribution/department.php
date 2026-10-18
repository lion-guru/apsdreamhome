<?php
/**
 * Department Work Distribution Detail
*/

$config = $config ?? [];
$workload = $workload ?? [];
$pending_tasks = $pending_tasks ?? [];
$skills = $skills ?? [];
$department = $department ?? '';
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.wd-emp-card { border: 1px solid #e2e8f0; border-radius: 10px; transition: all 0.2s; }
.wd-emp-card:hover { border-color: #7c2d12; box-shadow: 0 4px 15px rgba(124,45,18,0.1); transform: translateY(-1px); }
.wd-emp-overloaded { border-left: 3px solid #ef4444; }
.wd-emp-underloaded { border-left: 3px solid #f59e0b; }
.wd-emp-healthy { border-left: 3px solid #22c55e; }
.wd-task-row { transition: background 0.15s; }
.wd-task-row:hover { background: #fef3e2; }
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-tasks me-2 text-primary"></i>Work Distribution - <?= htmlspecialchars($department) ?></h4>
            <p class="text-muted mb-0 small">Manage task assignments and workload for this department</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary" onclick="showAssignModal()">
                <i class="fas fa-plus me-1"></i> Assign Task
            </button>
            <a href="<?= BASE_URL ?>/admin/work-distribution/<?= urlencode($department) ?>/config" class="btn btn-outline-secondary">
                <i class="fas fa-cog me-1"></i> Config
            </a>
            <a href="<?= BASE_URL ?>/admin/work-distribution" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <!-- Config Summary -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fw-bold fs-4 text-primary"><?= $config['assignment_algorithm'] ?? 'hybrid' ?></div>
                    <div class="small text-muted">Assignment Algorithm</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fw-bold fs-4 text-<?= $config['auto_assign_enabled'] ? 'success' : 'danger' ?>">
                        <?= $config['auto_assign_enabled'] ? 'Enabled' : 'Disabled' ?>
                    </div>
                    <div class="small text-muted">Auto Assignment</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fw-bold fs-4"><?= $config['max_tasks_per_employee'] ?? 10 ?></div>
                    <div class="small text-muted">Max Tasks/Employee</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fw-bold fs-4 text-<?= $config['rebalance_threshold'] >= 80 ? 'warning' : 'success' ?>">
                        <?= $config['rebalance_threshold'] ?? 80 ?>%
                    </div>
                    <div class="small text-muted">Rebalance Threshold</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Workload Grid -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <h5 class="fw-semibold mb-3"><i class="fas fa-users me-2"></i>Team Workload</h5>
        </div>
        <?php foreach ($workload as $w): 
            $cardClass = 'wd-emp-card';
            if ($w['is_overloaded']) $cardClass .= ' wd-emp-overloaded';
            elseif ($w['is_underloaded']) $cardClass .= ' wd-emp-underloaded';
            else $cardClass .= ' wd-emp-healthy';
        ?>
            <div class="col-md-6 col-lg-4">
                <div class="card <?= $cardClass ?> shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="mb-1 fw-semibold"><?= htmlspecialchars($w['name']) ?></h6>
                            <div class="d-flex gap-1">
                                <?php if ($w['is_overloaded']): ?>
                                    <span class="badge bg-danger"><i class="fas fa-exclamation-triangle me-1"></i> Overloaded</span>
                                <?php elseif ($w['is_underloaded']): ?>
                                    <span class="badge bg-warning"><i class="fas fa-clock me-1"></i> Underloaded</span>
                                <?php else: ?>
                                    <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Healthy</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="text-muted small mb-2"><?= htmlspecialchars($w['role'] ?? 'Employee') ?></div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="small text-muted">Active Tasks</div>
                                <div class="fw-bold fs-4"><?= $w['active_tasks'] ?> / <?= $w['max_tasks'] ?></div>
                            </div>
                            <div class="col-6">
                                <div class="small text-muted">Workload</div>
                                <div class="fw-bold fs-4 text-<?= $w['is_overloaded'] ? 'danger' : ($w['is_underloaded'] ? 'warning' : 'success') ?>">
                                    <?= round($w['workload_percentage']) ?>%
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="<?= BASE_URL ?>/admin/work-distribution/employee/<?= $w['employee_id'] ?>" class="btn btn-sm btn-outline-primary flex-fill">
                                <i class="fas fa-chart-bar me-1"></i> Details
                            </a>
                            <button class="btn btn-sm btn-outline-primary flex-fill" onclick="assignToEmployee(<?= $w['employee_id'] ?>, '<?= addslashes($w['name']) ?>')">
                                <i class="fas fa-plus me-1"></i> Assign
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Pending Tasks Table -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Pending Tasks</h5>
            <button class="btn btn-sm btn-primary" onclick="showAssignModal()">
                <i class="fas fa-plus me-1"></i> Create Task
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="tasksTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 35%">Task</th>
                            <th style="width: 10%">Priority</th>
                            <th style="width: 10%">Status</th>
                            <th style="width: 12%">Due Date</th>
                            <th style="width: 15%">Assigned To</th>
                            <th style="width: 18%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_tasks as $t): 
                            $priority = $t['priority'] ?? 'medium';
                            $status = $t['status'] ?? 'pending';
                            $dueDate = !empty($t['due_date']) ? date('M d, Y', strtotime($t['due_date'])) : '—';
                            $isOverdue = !empty($t['due_date']) && $t['due_date'] < date('Y-m-d') && $status !== 'completed';
                            $dueClass = $isOverdue ? 'text-danger fw-bold' : '';
                            $priorityBadge = [
                                'high' => 'bg-danger',
                                'urgent' => 'bg-danger',
                                'medium' => 'bg-warning',
                                'low' => 'bg-info'
                            ][$priority] ?? 'bg-secondary';
                            $statusBadge = [
                                'pending' => 'bg-warning',
                                'in_progress' => 'bg-primary',
                                'completed' => 'bg-success'
                            ][$status] ?? 'bg-secondary';
                        ?>
                        <tr class="wd-task-row">
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($t['title'] ?? 'Untitled') ?></div>
                                <?php if (!empty($t['description'])): ?>
                                    <div class="text-muted small" style="max-width: 300px;">
                                        <?= htmlspecialchars(substr($t['description'], 0, 80)) ?>
                                        <?= strlen($t['description']) > 80 ? '...' : '' ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge <?= $priorityBadge ?>"><?= ucfirst($priority) ?></span></td>
                            <td><span class="badge <?= $statusBadge ?>"><?= ucfirst($status) ?></span></td>
                            <td class="<?= $dueClass ?>"><?= $dueDate ?><?= $isOverdue ? ' <i class="fas fa-exclamation-circle ms-1"></i>' : '' ?></td>
                            <td>
                                <?php if (!empty($t['assigned_to_name'])): ?>
                                    <div class="fw-medium"><?= htmlspecialchars($t['assigned_to_name']) ?></div>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Unassigned</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <?php if (empty($t['assigned_to'])): ?>
                                        <button class="btn btn-outline-primary" onclick="assignTask(<?= $t['id'] ?>)" title="Assign">
                                            <i class="fas fa-user-plus"></i>
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-outline-success" onclick="reassignTask(<?= $t['id'] ?>)" title="Reassign">
                                            <i class="fas fa-user-edit"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn btn-outline-info" onclick="viewTask(<?= $t['id'] ?>)" title="View">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($pending_tasks)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="fas fa-clipboard-check fa-2x mb-2 d-block opacity-25"></i>
                                    No pending tasks in this department
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row g-3 mt-4">
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fw-bold fs-4 text-primary"><?= count($pending_tasks) ?></div>
                    <div class="small text-muted">Total Pending</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fw-bold fs-4 text-danger"><?= count(array_filter($pending_tasks, fn($t) => !empty($t['due_date']) && $t['due_date'] < date('Y-m-d') && $t['status'] !== 'completed')) ?></div>
                    <div class="small text-muted">Overdue</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fw-bold fs-4 text-warning"><?= count(array_filter($pending_tasks, fn($t) => empty($t['assigned_to']))) ?></div>
                    <div class="small text-muted">Unassigned</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fw-bold fs-4 text-info"><?= count(array_filter($pending_tasks, fn($t) => in_array($t['priority'], ['high', 'urgent']))) ?></div>
                    <div class="small text-muted">High Priority</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assign Task Modal -->
<div class="modal fade" id="assignTaskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="assignTaskForm" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="department" value="<?= htmlspecialchars($department) ?>">
                <input type="hidden" name="assigned_by" value="<?= htmlspecialchars($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 1) ?>">
                <input type="hidden" name="task_id" id="modalTaskId" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="assignModalTitle"><i class="fas fa-user-plus me-2"></i>Assign Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Employee <span class="text-danger">*</span></label>
                            <select name="employee_id" id="modalEmployeeId" class="form-select" required>
                                <option value="">Select employee</option>
                                <?php foreach ($workload as $w): ?>
                                    <option value="<?= $w['employee_id'] ?>" data-name="<?= htmlspecialchars($w['name']) ?>" data-workload="<?= round($w['workload_percentage']) ?>">
                                        <?= htmlspecialchars($w['name']) ?> (<?= $w['active_tasks'] ?>/<?= $w['max_tasks'] ?> - <?= round($w['workload_percentage']) ?>%)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Priority <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select" required>
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" required placeholder="Task title">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Task details..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Deadline <span class="text-danger">*</span></label>
                            <input type="date" name="deadline" class="form-control" required min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d', strtotime('+7 days')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Required Skills (comma separated)</label>
                            <input type="text" name="required_skills" class="form-control" placeholder="e.g., CRM & Lead Management, Negotiation, Property Sales">
                            <div class="form-text">Skills will be used for smart assignment scoring</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Required Role</label>
                            <select name="required_role" class="form-select">
                                <option value="">Any role</option>
                                <option value="telecalling_executive">Telecaller</option>
                                <option value="sales_manager">Sales Manager</option>
                                <option value="hr_manager">HR Manager</option>
                                <option value="accountant">Accountant</option>
                                <option value="legal_advisor">Legal Advisor</option>
                                <option value="property_manager">Property Manager</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Algorithm</label>
                            <select name="algorithm" class="form-select">
                                <option value="hybrid">Hybrid (Default)</option>
                                <option value="least_loaded">Least Loaded</option>
                                <option value="skill_based">Skill Based</option>
                                <option value="performance_based">Performance Based</option>
                                <option value="round_robin">Round Robin</option>
                            </select>
                        </div>
                        <div class="col-12 form-check">
                            <input class="form-check-input" type="checkbox" name="force_assign" id="forceAssign" value="1">
                            <label class="form-check-label" for="forceAssign">Force assign even if employee is overloaded</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="assignSubmitBtn">
                        <i class="fas fa-user-plus me-1"></i> Assign Task
                    </button>
                </div>
            </form>
        </div>
    </div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
const workloadData = <?= json_encode($workload) ?>;

function showAssignModal(taskId = null) {
    const modal = new bootstrap.Modal(document.getElementById('assignTaskModal'));
    const form = document.getElementById('assignTaskForm');
    form.reset();
    document.getElementById('modalTaskId').value = taskId || '';
    
    if (taskId) {
        document.getElementById('assignModalTitle').innerHTML = '<i class="fas fa-user-edit me-2"></i>Reassign Task';
        document.getElementById('assignSubmitBtn').innerHTML = '<i class="fas fa-user-edit me-1"></i> Reassign Task';
    } else {
        document.getElementById('assignModalTitle').innerHTML = '<i class="fas fa-user-plus me-2"></i>Assign Task';
        document.getElementById('assignSubmitBtn').innerHTML = '<i class="fas fa-user-plus me-1"></i> Assign Task';
    }
    modal.show();
}

function assignToEmployee(empId, empName) {
    showAssignModal();
    document.getElementById('modalEmployeeId').value = empId;
}

function assignTask(taskId) {
    showAssignModal(taskId);
}

function reassignTask(taskId) {
    showAssignModal(taskId);
}

function viewTask(taskId) {
    window.location.href = '<?= BASE_URL ?>/admin/tasks/view/' + taskId;
}

document.getElementById('assignTaskForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('assignSubmitBtn');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Assigning...';
    
    const formData = new FormData(this);
    const taskId = document.getElementById('modalTaskId').value;
    const url = taskId ? '<?= BASE_URL ?>/admin/work-distribution/<?= urlencode($department) ?>/assign-manual' : '<?= BASE_URL ?>/admin/work-distribution/<?= urlencode($department) ?>/assign';
    
    try {
        const response = await fetch(url, {
            method: 'POST',
            body: new URLSearchParams(formData)
        });
        const data = await response.json();
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('assignTaskModal')).hide();
            location.reload();
        } else {
            alert(data.message || 'Assignment failed');
        }
    } catch (err) {
        alert('Error: ' + err.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
});

function suggestAssignment() {
    const form = document.getElementById('assignTaskForm');
    const formData = new FormData(form);
    formData.delete('employee_id');
    formData.delete('algorithm');
    
    fetch('<?= BASE_URL ?>/admin/work-distribution/<?= urlencode($department) ?>/suggest', {
        method: 'POST',
        body: new URLSearchParams(formData)
    }).then(r => r.json()).then(data => {
        if (data.suggestions && data.suggestions.length > 0) {
            let msg = 'Top suggestions:\n\n';
            data.suggestions.forEach((s, i) => {
                msg += `${i+1}. ${s.name} (${s.role})\n   Score: ${s.score} | Workload: ${s.workload}% | Skill: ${s.skill_match}% | Perf: ${s.performance}%\n   ${s.reasons.join(', ')}\n\n`;
            });
            alert(msg);
        } else {
            alert(data.message || 'No suggestions available');
        }
    });
}

// Add suggest button handler
document.addEventListener('DOMContentLoaded', function() {
    const suggestBtn = document.createElement('button');
    suggestBtn.type = 'button';
    suggestBtn.className = 'btn btn-outline-info btn-sm';
    suggestBtn.innerHTML = '<i class="fas fa-lightbulb me-1"></i> Get Suggestions';
    suggestBtn.onclick = suggestAssignment;
    
    const submitBtn = document.getElementById('assignSubmitBtn');
    submitBtn.parentNode.insertBefore(suggestBtn, submitBtn);
});
</script>