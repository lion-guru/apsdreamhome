<?php
$tasks = $tasks ?? [];
$stats = $stats ?? ['total' => 0, 'pending' => 0, 'in_progress' => 0, 'completed' => 0, 'overdue' => 0];
$activeFilter = $_GET['filter'] ?? 'all';
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.emp-stat-card { border: none; border-radius: 12px; transition: transform 0.2s; }
.emp-stat-card:hover { transform: translateY(-2px); }
.emp-stat-card .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; }
.task-card { border: 1px solid #e2e8f0; border-radius: 12px; transition: all 0.2s; }
.task-card:hover { border-color: #3b82f6; box-shadow: 0 4px 15px rgba(59,130,246,0.1); }
.task-card .task-title { font-weight: 600; color: #1e293b; }
.task-card .task-meta { font-size: 0.8rem; color: #64748b; }
.filter-btn { border-radius: 20px; font-size: 0.85rem; padding: 6px 16px; }
.filter-btn.active { background: #3b82f6; color: #fff; border-color: #3b82f6; }
.progress-thin { height: 6px; border-radius: 3px; }
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-tasks me-2 text-primary"></i>My Tasks</h4>
            <p class="text-muted mb-0 small">View and manage your assigned tasks</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md">
            <div class="card emp-stat-card shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="fas fa-list-check"></i></div>
                    <div><div class="fw-bold fs-4"><?= $stats['total'] ?></div><div class="text-muted small">Total</div></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="card emp-stat-card shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="fas fa-clock"></i></div>
                    <div><div class="fw-bold fs-4 text-warning"><?= $stats['pending'] ?></div><div class="text-muted small">Pending</div></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="card emp-stat-card shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-info bg-opacity-10 text-info"><i class="fas fa-spinner"></i></div>
                    <div><div class="fw-bold fs-4 text-info"><?= $stats['in_progress'] ?></div><div class="text-muted small">In Progress</div></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="card emp-stat-card shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="fas fa-check-circle"></i></div>
                    <div><div class="fw-bold fs-4 text-success"><?= $stats['completed'] ?></div><div class="text-muted small">Completed</div></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="card emp-stat-card shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger"><i class="fas fa-exclamation-triangle"></i></div>
                    <div><div class="fw-bold fs-4 text-danger"><?= $stats['overdue'] ?></div><div class="text-muted small">Overdue</div></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Bar -->
    <?php if ($stats['total'] > 0): ?>
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="fw-semibold small">Overall Progress</span>
                <span class="fw-bold text-primary"><?= $stats['total'] > 0 ? round(($stats['completed'] / $stats['total']) * 100) : 0 ?>%</span>
            </div>
            <div class="progress progress-thin">
                <div class="progress-bar bg-success" style="width: <?= $stats['total'] > 0 ? round(($stats['completed'] / $stats['total']) * 100) : 0 ?>%"></div>
            </div>
            <div class="d-flex gap-3 mt-2">
                <small class="text-muted"><span class="text-success">●</span> Completed <?= $stats['completed'] ?></small>
                <small class="text-muted"><span class="text-info">●</span> In Progress <?= $stats['in_progress'] ?></small>
                <small class="text-muted"><span class="text-warning">●</span> Pending <?= $stats['pending'] ?></small>
                <small class="text-muted"><span class="text-danger">●</span> Overdue <?= $stats['overdue'] ?></small>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Filter Tabs -->
    <div class="d-flex gap-2 mb-4 flex-wrap">
        <?php
        $filters = [
            'all' => ['label' => 'All', 'count' => $stats['total']],
            'pending' => ['label' => 'Pending', 'count' => $stats['pending']],
            'in_progress' => ['label' => 'In Progress', 'count' => $stats['in_progress']],
            'completed' => ['label' => 'Completed', 'count' => $stats['completed']],
        ];
        foreach ($filters as $key => $f):
            $isActive = $activeFilter === $key ? 'active' : '';
        ?>
            <a href="?filter=<?= $key ?>" class="btn btn-sm btn-outline-primary filter-btn <?= $isActive ?>"><?= $f['label'] ?> (<?= $f['count'] ?>)</a>
        <?php endforeach; ?>
    </div>

    <!-- Tasks List -->
    <?php if (empty($tasks)): ?>
        <div class="card shadow-sm border-0">
            <div class="card-body text-center py-5">
                <div class="mb-3"><i class="fas fa-clipboard-check fa-4x text-muted opacity-25"></i></div>
                <h5 class="text-muted">No Tasks Found</h5>
                <p class="text-muted small">You don't have any tasks assigned yet.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($tasks as $t):
                $status = strtolower($t['status'] ?? 'pending');
                $priority = $t['priority'] ?? 'Medium';
                $isOverdue = !empty($t['due_date']) && $t['due_date'] < date('Y-m-d') && $status !== 'completed';
                $statusColor = match($status) { 'completed' => 'success', 'in progress' => 'info', default => 'warning' };
                $priorityColor = match($priority) { 'High' => 'danger', 'Low' => 'success', default => 'warning' };
            ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card task-card shadow-sm h-100 <?= $isOverdue ? 'border-danger' : '' ?>">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-<?= $priorityColor ?>"><?= $priority ?></span>
                                <?php if ($isOverdue): ?>
                                    <span class="badge bg-danger"><i class="fas fa-exclamation-circle me-1"></i>Overdue</span>
                                <?php else: ?>
                                    <span class="badge bg-<?= $statusColor ?>"><?= ucfirst(htmlspecialchars($status)) ?></span>
                                <?php endif; ?>
                            </div>
                            <h6 class="task-title mb-2"><?= htmlspecialchars($t['title'] ?? 'Untitled Task') ?></h6>
                            <?php if (!empty($t['description'])): ?>
                                <p class="text-muted small mb-2" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;"><?= htmlspecialchars($t['description']) ?></p>
                            <?php endif; ?>
                            <div class="task-meta d-flex flex-wrap gap-2">
                                <?php if (!empty($t['due_date'])): ?>
                                    <span><i class="fas fa-calendar me-1"></i><?= date('d M Y', strtotime($t['due_date'])) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($t['assigned_by_name'])): ?>
                                    <span><i class="fas fa-user me-1"></i><?= htmlspecialchars($t['assigned_by_name']) ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if ($status === 'completed' && !empty($t['completed_at'])): ?>
                                <div class="mt-2"><small class="text-success"><i class="fas fa-check me-1"></i>Completed <?= date('d M Y', strtotime($t['completed_at'])) ?></small></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
