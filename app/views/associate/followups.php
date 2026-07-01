<?php
$page_title = $page_title ?? 'Follow-ups';
$current_page = 'followups';
$followups = $followups ?? [];
$stats = $stats ?? ['total' => 0, 'pending' => 0, 'completed' => 0, 'overdue' => 0];
$filter = $_GET['filter'] ?? 'all';
$today = date('Y-m-d');
?>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <a href="?filter=all" class="card border-0 shadow-sm text-decoration-none <?= $filter === 'all' ? 'border-primary' : '' ?>" style="background: linear-gradient(135deg, #6366f1 0%, #14b8a6 100%); color: #fff;">
            <div class="card-body p-3 text-center">
                <div class="fs-2 fw-bold"><?= $stats['total'] ?></div>
                <div class="small opacity-75">Total Tasks</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="?filter=pending" class="card border-0 shadow-sm text-decoration-none <?= $filter === 'pending' ? 'border-warning' : '' ?>">
            <div class="card-body p-3 text-center">
                <div class="fs-2 fw-bold text-warning"><?= $stats['pending'] ?></div>
                <div class="small text-muted">Pending</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="?filter=overdue" class="card border-0 shadow-sm text-decoration-none <?= $filter === 'overdue' ? 'border-danger' : '' ?>">
            <div class="card-body p-3 text-center">
                <div class="fs-2 fw-bold text-danger"><?= $stats['overdue'] ?></div>
                <div class="small text-muted">Overdue</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="?filter=completed" class="card border-0 shadow-sm text-decoration-none <?= $filter === 'completed' ? 'border-success' : '' ?>">
            <div class="card-body p-3 text-center">
                <div class="fs-2 fw-bold text-success"><?= $stats['completed'] ?></div>
                <div class="small text-muted">Completed</div>
            </div>
        </a>
    </div>
</div>

<!-- Filter Tabs -->
<div class="d-flex gap-2 mb-4 flex-wrap">
    <?php
    $tabs = ['all'=>'All Tasks','today'=>'Today','week'=>'This Week','overdue'=>'Overdue','completed'=>'Completed'];
    foreach ($tabs as $tKey => $tLabel): ?>
        <a href="?filter=<?= $tKey ?>" class="tab-pill <?= $filter === $tKey ? 'active' : '' ?>"><?= $tLabel ?></a>
    <?php endforeach; ?>
</div>

<!-- Follow-ups List -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0"><i class="fas fa-calendar-check me-2 text-primary"></i>Follow-ups & Tasks</h5>
        <a href="<?= BASE_URL ?>/associate/crm" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Add Task
        </a>
    </div>
    <div class="card-body p-0">
        <?php
        // Filter logic
        $filtered = $followups;
        if ($filter === 'today') {
            $filtered = array_filter($followups, fn($t) => ($t['due_date'] ?? '') === $today && $t['status'] !== 'completed');
        } elseif ($filter === 'week') {
            $weekEnd = date('Y-m-d', strtotime('+7 days'));
            $filtered = array_filter($followups, fn($t) => ($t['due_date'] ?? '') >= $today && ($t['due_date'] ?? '') <= $weekEnd && $t['status'] !== 'completed');
        } elseif ($filter === 'overdue') {
            $filtered = array_filter($followups, fn($t) => $t['status'] !== 'completed' && strtotime($t['due_date'] ?? '') < time());
        } elseif ($filter === 'completed') {
            $filtered = array_filter($followups, fn($t) => $t['status'] === 'completed');
        }

        if (empty($filtered)): ?>
            <div class="text-center py-5">
                <i class="fas fa-check-circle fa-3x text-success mb-3" style="opacity:0.3"></i>
                <h5 class="text-muted"><?= $filter === 'completed' ? 'No completed tasks yet' : 'No pending follow-ups!' ?></h5>
                <p class="text-muted"><?= $filter === 'completed' ? 'Complete tasks to see them here.' : 'You\'re all caught up. Great job!' ?></p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Task</th>
                            <th>Lead</th>
                            <th>Due Date</th>
                            <th>Type</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($filtered as $task):
                            $isOverdue = ($task['status'] !== 'completed' && strtotime($task['due_date'] ?? '') < time());
                            $isTodayTask = (($task['due_date'] ?? '') === $today);
                            $priorityClass = match($task['priority'] ?? 'medium') {
                                'high' => 'danger',
                                'medium' => 'warning',
                                'low' => 'info',
                                default => 'secondary'
                            };
                            $typeIcons = [
                                'follow_up' => 'fa-phone', 'visit' => 'fa-map-marker-alt',
                                'email' => 'fa-envelope', 'meeting' => 'fa-users',
                                'whatsapp' => 'fab fa-whatsapp', 'call' => 'fa-phone',
                            ];
                            $typeIcon = $typeIcons[$task['task_type'] ?? 'follow_up'] ?? 'fa-tasks';
                        ?>
                            <tr class="<?= $isOverdue ? 'table-danger' : ($isTodayTask ? 'table-light' : '') ?>">
                                <td>
                                    <strong><?= htmlspecialchars($task['title'] ?? 'Untitled') ?></strong>
                                    <?php if (!empty($task['description'])): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars(mb_substr($task['description'], 0, 60)) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($task['lead_name'])): ?>
                                        <a href="<?= BASE_URL ?>/associate/leads/<?= $task['lead_id'] ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($task['lead_name']) ?>
                                        </a>
                                        <?php if (!empty($task['lead_phone'])): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($task['lead_phone']) ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($task['due_date'])): ?>
                                        <span class="<?= $isOverdue ? 'text-danger fw-bold' : ($isTodayTask ? 'text-primary fw-bold' : '') ?>">
                                            <i class="fas fa-clock me-1"></i>
                                            <?= date('d M Y', strtotime($task['due_date'])) ?>
                                            <?php if ($isTodayTask): ?><span class="badge bg-primary ms-1">Today</span><?php endif; ?>
                                            <?php if ($isOverdue): ?><span class="badge bg-danger ms-1">Overdue</span><?php endif; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">No date</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark"><i class="fas <?= $typeIcon ?> me-1"></i><?= ucfirst(str_replace('_', ' ', $task['task_type'] ?? 'task')) ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $priorityClass ?>"><?= ucfirst($task['priority'] ?? 'medium') ?></span>
                                </td>
                                <td>
                                    <?php if ($task['status'] === 'completed'): ?>
                                        <span class="badge bg-success"><i class="fas fa-check me-1"></i>Done</span>
                                    <?php elseif ($isOverdue): ?>
                                        <span class="badge bg-danger"><i class="fas fa-exclamation me-1"></i>Overdue</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark"><i class="fas fa-hourglass me-1"></i>Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($task['status'] !== 'completed'): ?>
                                        <form method="POST" action="<?= BASE_URL ?>/associate/followups/update/<?= $task['id'] ?>" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                            <input type="hidden" name="status" value="completed">
                                            <button type="submit" class="btn btn-success btn-sm" title="Mark Complete"><i class="fas fa-check"></i></button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if (!empty($task['lead_id'])): ?>
                                    <a href="<?= BASE_URL ?>/associate/leads/<?= $task['lead_id'] ?>" class="btn btn-outline-primary btn-sm" title="View Lead"><i class="fas fa-eye"></i></a>
                                    <?php endif; ?>
                                    <?php if (!empty($task['lead_phone'])): ?>
                                    <a href="tel:<?= htmlspecialchars($task['lead_phone']) ?>" class="btn btn-outline-success btn-sm" title="Call"><i class="fas fa-phone"></i></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.tab-pill { padding: 8px 20px; border-radius: 25px; font-size: 0.82rem; font-weight: 600; cursor: pointer; transition: all 0.2s; border: 2px solid transparent; text-decoration: none; display: inline-block; }
.tab-pill:hover { transform: translateY(-1px); }
.tab-pill.active { background: #0d9488; color: #fff; border-color: #0d9488; }
.tab-pill:not(.active) { background: #f1f5f9; color: #64748b; border-color: #e2e8f0; }
</style>
