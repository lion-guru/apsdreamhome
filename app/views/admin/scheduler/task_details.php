<?php
$pageTitle = $pageTitle ?? 'Task Details';
$base = $base ?? (defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/'));
$task = $task ?? ['id' => 0, 'name' => '', 'type' => '', 'status' => '', 'last_run' => '', 'next_run' => '', 'schedule' => '', 'enabled' => false];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-tasks me-2 text-primary"></i>Task Details</h1>
        <div>
            <a href="<?= $base ?>/admin/scheduler" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
            <a href="<?= $base ?>/admin/scheduler/task/<?= $task['id'] ?>/edit" class="btn btn-primary"><i class="fas fa-edit me-1"></i>Edit</a>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><?= htmlspecialchars($task['name'] ?? '') ?></h6></div>
                <div class="card-body aps-cp-card-body">
                    <table class="table table-bordered">
                        <tr><th class="style-47085">Task Name</th><td><?= htmlspecialchars($task['name'] ?? '') ?></td></tr>
                        <tr><th>Type</th><td><span class="badge bg-info"><?= htmlspecialchars($task['type'] ?? '') ?></span></td></tr>
                        <tr><th>Status</th>
                            <td>
                                <?php if ($task['enabled'] ?? false): ?>
                                    <span class="badge bg-success">Enabled</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Disabled</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr><th>Schedule</th><td><code><?= htmlspecialchars($task['schedule'] ?? '') ?></code></td></tr>
                        <tr><th>Last Run</th><td><?= !empty($task['last_run']) ? htmlspecialchars($task['last_run']) : '<span class="text-muted">Never</span>' ?></td></tr>
                        <tr><th>Next Run</th><td><?= !empty($task['next_run']) ? htmlspecialchars($task['next_run']) : '<span class="text-muted">Not scheduled</span>' ?></td></tr>
                    </table>
                </div>
            </div>
            <div class="card shadow">
                <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Execution Logs</h6></div>
                <div class="card-body aps-cp-card-body">
                    <?php $logs = $task['logs'] ?? []; ?>
                    <?php if (empty($logs)): ?>
                        <p class="text-muted text-center py-3"><i class="fas fa-history fa-2x d-block mb-2"></i>No execution logs yet.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead><tr><th>Start Time</th><th>End Time</th><th>Status</th><th>Output</th></tr></thead>
                                <tbody>
                                    <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($log['start_time'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($log['end_time'] ?? '') ?></td>
                                        <td><?php $s = $log['status'] ?? ''; ?>
                                            <span class="badge bg-<?= $s === 'success' ? 'success' : ($s === 'failed' ? 'danger' : 'secondary') ?>"><?= $s ?></span>
                                        </td>
                                        <td><code><?= htmlspecialchars(substr($log['output'] ?? '', 0, 100)) ?></code></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Actions</h6></div>
                <div class="card-body aps-cp-card-body">
                    <form method="POST" action="<?= $base ?>/admin/scheduler/task/<?= $task['id'] ?>/run" class="mb-2">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <button type="submit" class="btn btn-success w-100" onclick="return confirm('Run this task now?')"><i class="fas fa-play me-1"></i>Run Now</button>
                    </form>
                    <form method="POST" action="<?= $base ?>/admin/scheduler/task/<?= $task['id'] ?>/toggle">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <button type="submit" class="btn btn-warning w-100 mb-2"><i class="fas fa-power-off me-1"></i><?= ($task['enabled'] ?? false) ? 'Disable' : 'Enable' ?></button>
                    </form>
                    <form method="POST" action="<?= $base ?>/admin/scheduler/task/<?= $task['id'] ?>/delete" onsubmit="return confirm('Delete this task?')">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <button type="submit" class="btn btn-danger w-100"><i class="fas fa-trash me-1"></i>Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
