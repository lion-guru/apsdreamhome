<?php
$tasks = $tasks ?? [];
$stats = $stats ?? ['total' => 0, 'pending' => 0, 'in_progress' => 0, 'completed' => 0, 'overdue' => 0];

function taskPriorityBadge($priority) {
    $map = ['High' => 'danger', 'Medium' => 'warning', 'Low' => 'info'];
    $cls = $map[$priority] ?? 'secondary';
    return '<span class="badge bg-' . $cls . '">' . htmlspecialchars($priority ?? 'Medium') . '</span>';
}
function taskStatusBadge($status) {
    $map = ['completed' => 'success', 'in progress' => 'primary', 'pending' => 'warning'];
    $cls = $map[strtolower($status ?? '')] ?? 'secondary';
    return '<span class="badge bg-' . $cls . '">' . ucfirst(htmlspecialchars($status ?? 'Pending')) . '</span>';
}
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.emp-task-stat { border: none; border-radius: 12px; transition: transform 0.2s; }
.emp-task-stat:hover { transform: translateY(-2px); }
.emp-task-stat .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; }
.emp-task-card { border: 1px solid #e2e8f0; border-radius: 12px; transition: all 0.2s; position: relative; }
.emp-task-card:hover { border-color: #7c2d12; box-shadow: 0 4px 15px rgba(124,45,18,0.1); transform: translateY(-1px); }
.emp-task-overdue { border-left: 3px solid #ef4444 !important; }
.emp-task-completed { opacity: 0.7; background: #f8fafc; }
.emp-task-mini { border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 12px; font-size: 0.85rem; }
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-tasks me-2 text-primary"></i>My Tasks</h4>
            <p class="text-muted mb-0 small">View and manage your assigned tasks</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card emp-task-stat shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="fas fa-tasks"></i></div>
                    <div><div class="fw-bold fs-4"><?= $stats['total'] ?></div><div class="text-muted small">Total</div></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card emp-task-stat shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="fas fa-clock"></i></div>
                    <div><div class="fw-bold fs-4 text-warning"><?= $stats['pending'] + $stats['in_progress'] ?></div><div class="text-muted small">Active</div></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card emp-task-stat shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="fas fa-check-circle"></i></div>
                    <div><div class="fw-bold fs-4 text-success"><?= $stats['completed'] ?></div><div class="text-muted small">Completed</div></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card emp-task-stat shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger"><i class="fas fa-exclamation-triangle"></i></div>
                    <div><div class="fw-bold fs-4 text-danger"><?= $stats['overdue'] ?></div><div class="text-muted small">Overdue</div></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <select id="statusFilter" class="form-select form-select-sm" onchange="filterTasks()">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="in progress">In Progress</option>
                <option value="completed">Completed</option>
            </select>
        </div>
        <div class="col-md-4">
            <select id="priorityFilter" class="form-select form-select-sm" onchange="filterTasks()">
                <option value="">All Priorities</option>
                <option value="High">High</option>
                <option value="Medium">Medium</option>
                <option value="Low">Low</option>
            </select>
        </div>
        <div class="col-md-4 text-md-end">
            <input type="text" id="searchTasks" class="form-control form-control-sm" placeholder="Search tasks..." onkeyup="filterTasks()">
        </div>
    </div>

    <?php if (empty($tasks)): ?>
        <div class="card shadow-sm border-0">
            <div class="card-body text-center py-5">
                <div class="mb-3"><i class="fas fa-clipboard-list fa-4x text-muted opacity-25"></i></div>
                <h5 class="text-muted">No Tasks Assigned</h5>
                <p class="text-muted small mb-0">You have no tasks at the moment. Check back later!</p>
            </div>
        </div>
    <?php else: ?>
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="tasksTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40%">Task</th>
                                <th style="width: 12%">Priority</th>
                                <th style="width: 12%">Status</th>
                                <th style="width: 12%">Due Date</th>
                                <th style="width: 12%">Assigned By</th>
                                <th style="width: 12%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tasks as $t): 
                                $status = strtolower($t['status'] ?? 'pending');
                                $isOverdue = !empty($t['due_date']) && $t['due_date'] < date('Y-m-d') && $status !== 'completed';
                                $isCompleted = $status === 'completed';
                                $rowClass = $isOverdue ? 'emp-task-overdue' : ($isCompleted ? 'emp-task-completed' : '');
                                $dueDate = !empty($t['due_date']) ? date('M d, Y', strtotime($t['due_date'])) : '—';
                                $dueClass = $isOverdue ? 'text-danger fw-bold' : '';
                            ?>
                            <tr class="emp-task-card <?= $rowClass ?>" data-status="<?= $status ?>" data-priority="<?= htmlspecialchars($t['priority'] ?? 'Medium') ?>" data-search="<?= strtolower(htmlspecialchars($t['title'] ?? '')) ?>">
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($t['title'] ?? 'Untitled Task') ?></div>
                                    <?php if (!empty($t['description'])): ?>
                                        <div class="text-muted small" style="max-width: 300px;"><?= htmlspecialchars(substr($t['description'], 0, 80)) ?><?= strlen($t['description']) > 80 ? '...' : '' ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><?= taskPriorityBadge($t['priority'] ?? 'Medium') ?></td>
                                <td><?= taskStatusBadge($status) ?></td>
                                <td class="<?= $dueClass ?>"><?= $dueDate ?><?= $isOverdue ? ' <i class="fas fa-exclamation-circle ms-1"></i>' : '' ?></td>
                                <td class="text-muted small"><?= htmlspecialchars($t['assigned_by_name'] ?? 'System') ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <?php if ($status !== 'completed'): ?>
                                            <button class="btn btn-outline-success" onclick="updateTaskStatus(<?= $t['id'] ?>, 'in progress')" title="Start Task">
                                                <i class="fas fa-play"></i>
                                            </button>
                                            <button class="btn btn-outline-primary" onclick="updateTaskStatus(<?= $t['id'] ?>, 'completed')" title="Mark Complete">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        <?php else: ?>
                                            <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Done</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
function filterTasks() {
    const status = document.getElementById('statusFilter').value;
    const priority = document.getElementById('priorityFilter').value;
    const search = document.getElementById('searchTasks').value.toLowerCase();
    const rows = document.querySelectorAll('#tasksTable tbody tr');
    
    rows.forEach(row => {
        const rowStatus = row.dataset.status;
        const rowPriority = row.dataset.priority;
        const rowSearch = row.dataset.search;
        
        let show = true;
        if (status && rowStatus !== status) show = false;
        if (priority && rowPriority !== priority) show = false;
        if (search && !rowSearch.includes(search)) show = false;
        
        row.style.display = show ? '' : 'none';
    });
}

function updateTaskStatus(taskId, newStatus) {
    if (!confirm('Update task status to ' + newStatus + '?')) return;
    
    fetch('<?= BASE_URL ?>/employee/api/update-task', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'task_id=' + taskId + '&status=' + encodeURIComponent(newStatus) + '&csrf_token=' + encodeURIComponent('<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>')
    }).then(r => r.json()).then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Update failed');
        }
    });
}
</script>