ï»¿<?php
// Initialize default values if not set
$health = $health ?? [
    'healthy' => true,
    'total_tasks' => 0,
    'active_tasks' => 0,
    'executions_24h' => 0,
    'issues' => []
];
$tasks = $tasks ?? [];
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">â�° Task Scheduler</h1>
        <div>
            <a href="<?= BASE_URL ?>/admin/scheduler/create" class="btn btn-primary me-2">
                <i class="fas fa-plus"></i> New Task
            </a>
            <a href="<?= BASE_URL ?>/admin/scheduler/health" class="btn btn-info me-2">
                <i class="fas fa-heartbeat"></i> Health
            </a>
            <a href="<?= BASE_URL ?>/admin/scheduler/logs" class="btn btn-secondary">
                <i class="fas fa-list"></i> Logs
            </a>
        </div>
    </div>

    <!-- Health Status -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card <?= $health['healthy'] ? 'border-success' : 'border-danger' ?>">
                <div class="card-body text-center">
                    <h5 class="card-title">Scheduler Health</h5>
                    <div class="h2 <?= $health['healthy'] ? 'text-success' : 'text-danger' ?>">
                        <i class="fas fa-<?= $health['healthy'] ? 'check-circle' : 'exclamation-triangle' ?>"></i>
                    </div>
                    <p class="card-text">
                        <?= $health['healthy'] ? 'Healthy' : 'Issues Detected' ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card aps-cp-card">
                <div class="card-body text-center">
                    <h5 class="card-title">Total Tasks</h5>
                    <div class="h2 text-primary"><?= $health['total_tasks'] ?? 0 ?></div>
                    <p class="card-text">Configured</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card aps-cp-card">
                <div class="card-body text-center">
                    <h5 class="card-title">Active Tasks</h5>
                    <div class="h2 text-success"><?= $health['active_tasks'] ?? 0 ?></div>
                    <p class="card-text">Running</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card aps-cp-card">
                <div class="card-body text-center">
                    <h5 class="card-title">24h Executions</h5>
                    <div class="h2 text-info"><?= $health['executions_24h'] ?? 0 ?></div>
                    <p class="card-text">Completed</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tasks Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Scheduled Tasks</h6>
        </div>
        <div class="card-body aps-cp-card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="tasksTable">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Name</th>
                            <th>Schedule</th>
                            <th>Last Run</th>
                            <th>Next Run</th>
                            <th>Runs</th>
                            <th>Last Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tasks)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-clock fa-3x text-muted mb-3 style-82835"></i>
                                <h5 class="text-muted">No scheduled tasks</h5>
                                <p class="text-muted mb-3">Create your first cron task to automate recurring operations like lead follow-ups, commission calculations, and report generation.</p>
                                <a href="<?= BASE_URL ?>/admin/scheduler/create" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i> Create Task
                                </a>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($tasks as $task): ?>
                        <tr>
                            <td>
                                <?php if ($task['is_active'] ?? false): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($task['name'] ?? 'Untitled') ?></strong>
                                <br><small class="text-muted"><?= htmlspecialchars($task['description'] ?? '') ?></small>
                            </td>
                            <td><code><?= htmlspecialchars($task['schedule'] ?? 'N/A') ?></code></td>
                            <td>
                                <?= ($task['last_run_at'] ?? null) ? date('Y-m-d H:i', strtotime($task['last_run_at'])) : 'Never' ?>
                            </td>
                            <td>
                                <?= ($task['next_run_at'] ?? null) ? date('Y-m-d H:i', strtotime($task['next_run_at'])) : 'Not scheduled' ?>
                            </td>
                            <td><?= $task['run_count'] ?? 0 ?></td>
                            <td>
                                <?php if (($task['last_status'] ?? '') === 'success'): ?>
                                    <span class="badge bg-success">Success</span>
                                <?php elseif (($task['last_status'] ?? '') === 'failed'): ?>
                                    <span class="badge bg-danger">Failed</span>
                                <?php elseif (($task['last_status'] ?? '') === 'running'): ?>
                                    <span class="badge bg-warning">Running</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= BASE_URL ?>/admin/scheduler/tasks/<?= $task['id'] ?? 0 ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?= BASE_URL ?>/admin/scheduler/tasks/edit/<?= $task['id'] ?? 0 ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="<?= BASE_URL ?>/admin/scheduler/tasks/run/<?= $task['id'] ?? 0 ?>" method="POST" class="style-26772">
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    <button type="submit" class="btn btn-sm btn-success" data-aps-confirm="Run this task now?" aria-label="Play"><i class="fas fa-play"></i></button>
                                </form>
                                <?php if (!($task['is_system'] ?? true)): ?>
                                <form action="<?= BASE_URL ?>/admin/scheduler/tasks/delete/<?= $task['id'] ?>" method="POST" class="style-26772">
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" data-aps-confirm="Delete this task?" aria-label="Delete"><i class="fas fa-trash"></i></button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Cron Setup Guide -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Cron Setup</h6>
        </div>
        <div class="card-body aps-cp-card-body">
            <p>Add this to your crontab to run the scheduler:</p>
            <div class="bg-dark text-light p-3 rounded">
                <code>* * * * * cd /var/www/html/apsdreamhome && php scripts/scheduler.php >> /dev/null 2>&1</code>
            </div>
            <p class="mt-3 text-muted">Or on Windows Task Scheduler, run every minute:</p>
            <div class="bg-dark text-light p-3 rounded">
                <code>php C:\xampp\htdocs\apsdreamhome\scripts\scheduler.php</code>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#tasksTable').DataTable({
        order: [[3, 'desc']],
        pageLength: 25
    });
});
</script>


