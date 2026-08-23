<?php
$page_title = $page_title ?? __('assoc_fu_title', [], 'Follow-ups');
$current_page = 'followups';
$followups = $followups ?? [];
$stats = $stats ?? ['total' => 0, 'pending' => 0, 'completed' => 0, 'overdue' => 0];
$filter = $_GET['filter'] ?? 'all';
$today = date('Y-m-d');
?>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <a href="?filter=all" class="card border-0 shadow-sm text-decoration-none <?= $filter === 'all' ? 'border-primary' : '' ?>" class="style-19672">
            <div class="card-body p-3 text-center">
                <div class="fs-2 fw-bold"><?= e($stats['total']) ?></div>
                <div class="small opacity-75"><?= __('assoc_fu_total_tasks', [], 'Total Tasks') ?></div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="?filter=pending" class="card border-0 shadow-sm text-decoration-none <?= $filter === 'pending' ? 'border-warning' : '' ?>">
            <div class="card-body p-3 text-center">
                <div class="fs-2 fw-bold text-warning"><?= e($stats['pending']) ?></div>
                <div class="small text-muted"><?= __('assoc_fu_pending', [], 'Pending') ?></div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="?filter=overdue" class="card border-0 shadow-sm text-decoration-none <?= $filter === 'overdue' ? 'border-danger' : '' ?>">
            <div class="card-body p-3 text-center">
                <div class="fs-2 fw-bold text-danger"><?= e($stats['overdue']) ?></div>
                <div class="small text-muted"><?= __('assoc_fu_overdue', [], 'Overdue') ?></div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="?filter=completed" class="card border-0 shadow-sm text-decoration-none <?= $filter === 'completed' ? 'border-success' : '' ?>">
            <div class="card-body p-3 text-center">
                <div class="fs-2 fw-bold text-success"><?= e($stats['completed']) ?></div>
                <div class="small text-muted"><?= __('assoc_fu_completed', [], 'Completed') ?></div>
            </div>
        </a>
    </div>
</div>

<div class="d-flex gap-2 mb-4 flex-wrap">
    <?php
    $tabs = ['all'=>__('assoc_fu_all_tasks', [], 'All Tasks'),'today'=>__('assoc_fu_today', [], 'Today'),'week'=>__('assoc_fu_week', [], 'This Week'),'overdue'=>__('assoc_fu_overdue', [], 'Overdue'),'completed'=>__('assoc_fu_completed', [], 'Completed')];
    foreach ($tabs as $tKey => $tLabel): ?>
        <a href="?filter=<?= $tKey ?>" class="tab-pill <?= $filter === $tKey ? 'active' : '' ?>"><?= $tLabel ?></a>
    <?php endforeach; ?>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0"><i class="fas fa-calendar-check me-2 text-primary"></i><?= __('assoc_fu_title', [], 'Follow-ups & Tasks') ?></h5>
        <a href="<?= BASE_URL ?>/associate/crm" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> <?= __('assoc_fu_add_task', [], 'Add Task') ?>
        </a>
    </div>
    <div class="card-body p-0">
        <?php
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
                <i class="fas fa-check-circle fa-3x text-success mb-3 style-56312"></i>
                <h5 class="text-muted"><?= $filter === 'completed' ? __('assoc_fu_no_completed', [], 'No completed tasks yet') : __('assoc_fu_no_pending', [], 'No pending follow-ups!') ?></h5>
                <p class="text-muted"><?= $filter === 'completed' ? __('assoc_fu_no_completed_desc', [], 'Complete tasks to see them here.') : __('assoc_fu_all_caught_up', [], 'You\'re all caught up. Great job!') ?></p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th><?= __('assoc_fu_th_task', [], 'Task') ?></th>
                            <th><?= __('assoc_fu_th_lead', [], 'Lead') ?></th>
                            <th><?= __('assoc_fu_th_due', [], 'Due Date') ?></th>
                            <th><?= __('assoc_fu_th_type', [], 'Type') ?></th>
                            <th><?= __('assoc_fu_th_priority', [], 'Priority') ?></th>
                            <th><?= __('assoc_fu_th_status', [], 'Status') ?></th>
                            <th><?= __('assoc_fu_th_action', [], 'Action') ?></th>
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
                                    <strong><?= htmlspecialchars($task['title'] ?? __('assoc_fu_untitled', [], 'Untitled')) ?></strong>
                                    <?php if (!empty($task['description'])): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars(mb_substr($task['description'] ?? '', 0, 60)) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($task['lead_name'])): ?>
                                        <a href="<?= BASE_URL ?>/associate/leads/<?= $task['lead_id'] ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($task['lead_name'] ?? '') ?>
                                        </a>
                                        <?php if (!empty($task['lead_phone'])): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($task['lead_phone'] ?? '') ?></small>
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
                                            <?php if ($isTodayTask): ?><span class="badge bg-primary ms-1"><?= __('assoc_fu_today_badge', [], 'Today') ?></span><?php endif; ?>
                                            <?php if ($isOverdue): ?><span class="badge bg-danger ms-1"><?= __('assoc_fu_overdue_badge', [], 'Overdue') ?></span><?php endif; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted"><?= __('assoc_fu_no_date', [], 'No date') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark"><i class="fas <?= e($typeIcon) ?> me-1"></i><?= e(ucfirst(str_replace('_', ' ', $task['task_type'] ?? __('assoc_fu_task', [], 'task')))) ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= e($priorityClass) ?>"><?= e(ucfirst($task['priority'] ?? 'medium')) ?></span>
                                </td>
                                <td>
                                    <?php if ($task['status'] === 'completed'): ?>
                                        <span class="badge bg-success"><i class="fas fa-check me-1"></i><?= __('assoc_fu_done', [], 'Done') ?></span>
                                    <?php elseif ($isOverdue): ?>
                                        <span class="badge bg-danger"><i class="fas fa-exclamation me-1"></i><?= __('assoc_fu_overdue_badge', [], 'Overdue') ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark"><i class="fas fa-hourglass me-1"></i><?= __('assoc_fu_pending', [], 'Pending') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($task['status'] !== 'completed'): ?>
                                        <form method="POST" action="<?= BASE_URL ?>/associate/followups/update/<?= $task['id'] ?>" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                            <input type="hidden" name="status" value="completed">
                                            <button type="submit" class="btn btn-success btn-sm" title="<?= __('assoc_fu_mark_done', [], 'Mark Complete') ?>"><i class="fas fa-check"></i></button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if (!empty($task['lead_id'])): ?>
                                    <a href="<?= BASE_URL ?>/associate/leads/<?= $task['lead_id'] ?>" class="btn btn-outline-primary btn-sm" title="<?= __('assoc_fu_view_lead', [], 'View Lead') ?>"><i class="fas fa-eye"></i></a>
                                    <?php endif; ?>
                                    <?php if (!empty($task['lead_phone'])): ?>
                                    <a href="tel:<?= htmlspecialchars($task['lead_phone'] ?? '') ?>" class="btn btn-outline-success btn-sm" title="<?= __('assoc_fu_call', [], 'Call') ?>"><i class="fas fa-phone"></i></a>
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
