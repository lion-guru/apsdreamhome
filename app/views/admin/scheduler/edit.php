<?php
$pageTitle = $pageTitle ?? 'Edit Task';
$base = $base ?? (defined('BASE_URL') ? BASE_URL : '/apsdreamhome');
$task = $task ?? ['id' => 0, 'name' => '', 'type' => '', 'schedule' => '', 'description' => '', 'command' => '', 'enabled' => false];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-edit me-2 text-primary"></i>Edit Scheduled Task</h1>
        <a href="<?= $base ?>/admin/scheduler" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Task Details</h6></div>
                <div class="card-body aps-cp-card-body">
                    <form method="POST" action="<?= $base ?>/admin/scheduler/task/<?= $task['id'] ?>/update">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-3">
                            <label class="form-label">Task Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($task['name'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($task['description'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Task Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="email" <?= ($task['type'] ?? '') === 'email' ? 'selected' : '' ?>>Email Notification</option>
                                <option value="report" <?= ($task['type'] ?? '') === 'report' ? 'selected' : '' ?>>Report Generation</option>
                                <option value="cleanup" <?= ($task['type'] ?? '') === 'cleanup' ? 'selected' : '' ?>>Database Cleanup</option>
                                <option value="sync" <?= ($task['type'] ?? '') === 'sync' ? 'selected' : '' ?>>Data Sync</option>
                                <option value="backup" <?= ($task['type'] ?? '') === 'backup' ? 'selected' : '' ?>>Backup</option>
                                <option value="custom" <?= ($task['type'] ?? '') === 'custom' ? 'selected' : '' ?>>Custom Command</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Schedule Expression <span class="text-danger">*</span></label>
                            <input type="text" name="schedule" class="form-control" value="<?= htmlspecialchars($task['schedule'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Command / Action</label>
                            <input type="text" name="command" class="form-control" value="<?= htmlspecialchars($task['command'] ?? '') ?>">
                        </div>
                        <div class="mb-3 form-check form-switch">
                            <input type="checkbox" name="enabled" class="form-check-input" id="enabledToggle" value="1" <?= ($task['enabled'] ?? false) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="enabledToggle">Task enabled</label>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Update Task</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
