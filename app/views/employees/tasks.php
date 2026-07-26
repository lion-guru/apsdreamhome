<?php
$tasks = $tasks ?? [];
$stats = $stats ?? ['total' => 0, 'pending' => 0, 'in_progress' => 0, 'completed' => 0, 'overdue' => 0];
$filter = $_GET['status'] ?? '';
$sort = $_GET['sort'] ?? 'priority';

function taskPriorityBadge($p) {
    $map = ['High' => 'danger', 'Medium' => 'warning', 'Low' => 'success'];
    return '<span class="badge bg-' . ($map[$p] ?? 'secondary') . ' bg-opacity-10 text-' . ($map[$p] ?? 'secondary') . '">' . htmlspecialchars($p) . '</span>';
}
function taskStatusBadge($s) {
    $map = ['completed' => 'success', 'in progress' => 'info', 'pending' => 'secondary', 'on hold' => 'warning'];
    $cls = $map[strtolower($s)] ?? 'secondary';
    return '<span class="badge bg-' . $cls . '">' . htmlspecialchars($s) . '</span>';
}
function taskPriorityIcon($p) {
    $map = ['High' => 'arrow-up text-danger', 'Medium' => 'minus text-warning', 'Low' => 'arrow-down text-success'];
    return $map[$p] ?? 'circle text-muted';
}
function taskProgress($t) {
    $s = strtolower($t['status'] ?? '');
    if ($s === 'completed') return 100;
    if ($s === 'in progress') return 50;
    return 10;
}
function taskProgressColor($t) {
    $s = strtolower($t['status'] ?? '');
    if ($s === 'completed') return 'success';
    if ($s === 'in progress') return 'info';
    return 'secondary';
}
function timeAgo($date) {
    if (!$date) return '';
    $diff = time() - strtotime($date);
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    return floor($diff / 86400) . 'd ago';
}

$filtered = $tasks;
if ($filter && $filter !== 'all') {
    $filtered = array_filter($tasks, function($t) use ($filter) {
        return strtolower($t['status'] ?? '') === $filter;
    });
}
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.emp-task-stat { border: none; border-radius: 12px; cursor: pointer; transition: all 0.2s; border: 2px solid transparent; }
.emp-task-stat:hover { transform: translateY(-2px); }
.emp-task-stat.active { border-color: var(--bs-primary); }
.emp-task-stat .stat-num { font-size: 1.5rem; font-weight: 700; }
.emp-task-card { border: 1px solid #e2e8f0; border-radius: 10px; transition: all 0.2s; }
.emp-task-card:hover { border-color: #7c2d12; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
.emp-task-card.overdue { border-left: 3px solid #ef4444; }
.emp-task-progress { height: 6px; border-radius: 3px; background: #e2e8f0; overflow: hidden; }
.emp-task-progress-fill { height: 100%; border-radius: 3px; transition: width 0.3s; }
.emp-task-filter-btn { border: 1px solid #e2e8f0; border-radius: 20px; padding: 4px 14px; font-size: 0.8rem; transition: all 0.2s; }
.emp-task-filter-btn:hover, .emp-task-filter-btn.active { background: #7c2d12; color: #fff; border-color: #7c2d12; }
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-tasks me-2 text-primary"></i>My Tasks</h4>
            <p class="text-muted mb-0 small"><?= $stats['total'] ?> tasks assigned to you</p>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row g-2 mb-3">
        <div class="col">
            <a href="/employee/tasks" class="card emp-task-stat <?= !$filter || $filter === 'all' ? 'active shadow-sm' : 'shadow-none' ?> text-decoration-none">
                <div class="card-body py-2 px-3 text-center">
                    <div class="stat-num text-dark"><?= $stats['total'] ?></div>
                    <div class="text-muted small">All</div>
                </div>
            </a>
        </div>
        <div class="col">
            <a href="/employee/tasks?status=pending" class="card emp-task-stat <?= $filter === 'pending' ? 'active shadow-sm' : 'shadow-none' ?> text-decoration-none">
                <div class="card-body py-2 px-3 text-center">
                    <div class="stat-num text-warning"><?= $stats['pending'] ?></div>
                    <div class="text-muted small">Pending</div>
                </div>
            </a>
        </div>
        <div class="col">
            <a href="/employee/tasks?status=in+progress" class="card emp-task-stat <?= $filter === 'in progress' ? 'active shadow-sm' : 'shadow-none' ?> text-decoration-none">
                <div class="card-body py-2 px-3 text-center">
                    <div class="stat-num text-info"><?= $stats['in_progress'] ?></div>
                    <div class="text-muted small">In Progress</div>
                </div>
            </a>
        </div>
        <div class="col">
            <a href="/employee/tasks?status=completed" class="card emp-task-stat <?= $filter === 'completed' ? 'active shadow-sm' : 'shadow-none' ?> text-decoration-none">
                <div class="card-body py-2 px-3 text-center">
                    <div class="stat-num text-success"><?= $stats['completed'] ?></div>
                    <div class="text-muted small">Done</div>
                </div>
            </a>
        </div>
        <div class="col">
            <a href="/employee/tasks?status=overdue" class="card emp-task-stat <?= $filter === 'overdue' ? 'active shadow-sm' : 'shadow-none' ?> text-decoration-none">
                <div class="card-body py-2 px-3 text-center">
                    <div class="stat-num text-danger"><?= $stats['overdue'] ?></div>
                    <div class="text-muted small">Overdue</div>
                </div>
            </a>
        </div>
    </div>

    <!-- Task List -->
    <?php if (empty($filtered)): ?>
        <div class="card shadow-sm border-0">
            <div class="card-body text-center py-5">
                <div class="mb-3"><i class="fas fa-clipboard-check fa-4x text-muted opacity-25"></i></div>
                <h5 class="text-muted"><?= $filter ? 'No tasks with this status' : 'No Tasks Assigned' ?></h5>
                <p class="text-muted small">You're all caught up!</p>
            </div>
        </div>
    <?php else: ?>
        <div class="d-flex flex-column gap-2">
            <?php foreach ($filtered as $t):
                $isOverdue = !empty($t['due_date']) && $t['due_date'] < date('Y-m-d') && strtolower($t['status'] ?? '') !== 'completed';
                $progress = taskProgress($t);
                $progColor = taskProgressColor($t);
            ?>
                <div class="card emp-task-card <?= $isOverdue ? 'overdue' : '' ?> shadow-sm" id="task-<?= $t['id'] ?>">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-start gap-3">
                            <div class="mt-1">
                                <input type="checkbox" class="form-check-input task-check" data-task-id="<?= $t['id'] ?>"
                                    <?= strtolower($t['status'] ?? '') === 'completed' ? 'checked disabled' : '' ?>
                                    onchange="toggleTask(<?= $t['id'] ?>, this.checked)">
                            </div>
                            <div class="flex-grow-1 min-width-0">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <h6 class="mb-0 fw-semibold <?= strtolower($t['status'] ?? '') === 'completed' ? 'text-decoration-line-through text-muted' : '' ?>">
                                        <?= htmlspecialchars($t['title'] ?? '') ?>
                                    </h6>
                                    <div class="d-flex gap-1 align-items-center">
                                        <?= taskPriorityBadge($t['priority'] ?? 'Medium') ?>
                                        <?= taskStatusBadge($t['status'] ?? 'pending') ?>
                                    </div>
                                </div>
                                <?php if (!empty($t['description'])): ?>
                                    <p class="text-muted small mb-2"><?= htmlspecialchars(mb_strimwidth($t['description'], 0, 120, '...')) ?></p>
                                <?php endif; ?>
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <?php if (!empty($t['due_date'])): ?>
                                        <span class="small <?= $isOverdue ? 'text-danger fw-semibold' : 'text-muted' ?>">
                                            <i class="fas fa-calendar me-1"></i><?= date('d M', strtotime($t['due_date'])) ?>
                                            <?php if ($isOverdue): ?>
                                                <span class="badge bg-danger bg-opacity-10 text-danger ms-1">Overdue</span>
                                            <?php endif; ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($t['assigned_by_name'])): ?>
                                        <span class="small text-muted"><i class="fas fa-user me-1"></i><?= htmlspecialchars($t['assigned_by_name']) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($t['created_at'])): ?>
                                        <span class="small text-muted"><i class="fas fa-clock me-1"></i><?= timeAgo($t['created_at']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="emp-task-progress">
                                    <div class="emp-task-progress-fill bg-<?= $progColor ?>" style="width: <?= $progress ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
function toggleTask(taskId, completed) {
    const status = completed ? 'completed' : 'pending';
    fetch('<?= BASE_URL ?>/employee/api/update-task', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ task_id: taskId, status: status })
    }).then(r => r.json()).then(d => {
        if (d.success) location.reload();
    }).catch(() => { location.reload(); });
}
</script>
