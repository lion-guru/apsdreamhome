<?php
$page_title = $page_title ?? 'Tasks - APS Dream Home';
$page_heading = $page_heading ?? 'Tasks';
$tasks = $tasks ?? [];
$filters = $filters ?? [];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-list me-2"></i><?= htmlspecialchars($page_heading ?? '') ?></h2>
        <a href="<?= BASE_URL ?>/async/create" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Create Task</a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
    <?php echo CSRFProtection::csrfField(); ?>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="running" <?= ($filters['status'] ?? '') === 'running' ? 'selected' : '' ?>>Running</option>
                        <option value="completed" <?= ($filters['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="failed" <?= ($filters['status'] ?? '') === 'failed' ? 'selected' : '' ?>>Failed</option>
                        <option value="cancelled" <?= ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Task Type</label>
                    <select name="task_type" class="form-select">
                        <option value="">All</option>
                        <option value="email" <?= ($filters['task_type'] ?? '') === 'email' ? 'selected' : '' ?>>Email</option>
                        <option value="image_processing" <?= ($filters['task_type'] ?? '') === 'image_processing' ? 'selected' : '' ?>>Image Processing</option>
                        <option value="report_generation" <?= ($filters['task_type'] ?? '') === 'report_generation' ? 'selected' : '' ?>>Report Generation</option>
                        <option value="data_export" <?= ($filters['task_type'] ?? '') === 'data_export' ? 'selected' : '' ?>>Data Export</option>
                        <option value="backup" <?= ($filters['task_type'] ?? '') === 'backup' ? 'selected' : '' ?>>Backup</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Priority</label>
                    <select name="priority" class="form-select">
                        <option value="">All</option>
                        <option value="1" <?= ($filters['priority'] ?? '') === '1' ? 'selected' : '' ?>>Low</option>
                        <option value="2" <?= ($filters['priority'] ?? '') === '2' ? 'selected' : '' ?>>Normal</option>
                        <option value="3" <?= ($filters['priority'] ?? '') === '3' ? 'selected' : '' ?>>High</option>
                        <option value="4" <?= ($filters['priority'] ?? '') === '4' ? 'selected' : '' ?>>Critical</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100 me-2"><i class="fas fa-filter me-2"></i>Filter</button>
                    <a href="<?= BASE_URL ?>/async/tasks" class="btn btn-outline-secondary w-100">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Task Name</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Progress</th>
                            <th>Worker</th>
                            <th>Retries</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($tasks)): ?>
                            <?php foreach ($tasks as $task): ?>
                            <tr>
                                <td><?= $task['id'] ?? '' ?></td>
                                <td><?= htmlspecialchars($task['task_name'] ?? '') ?></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($task['task_type'] ?? '') ?></span></td>
                                <td>
                                    <?php
                                    $status = $task['status'] ?? 'pending';
                                    $badgeClass = match($status) {
                                        'completed' => 'bg-success',
                                        'running' => 'bg-primary',
                                        'failed' => 'bg-danger',
                                        'cancelled' => 'bg-secondary',
                                        default => 'bg-warning text-dark'
                                    };
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= ucfirst($status) ?></span>
                                </td>
                                <td><?= htmlspecialchars($task['priority'] ?? '') ?></td>
                                <td>
                                    <div class="progress" class="style-54927">
                                        <div class="progress-bar" role="progressbar" class="style-98943" aria-valuenow="<?= (int)($task['progress_percentage'] ?? 0) ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($task['assigned_worker'] ?? '-') ?></td>
                                <td><?= (int)($task['retry_count'] ?? 0) ?>/<?= (int)($task['max_retries'] ?? 3) ?></td>
                                <td><?= date('d M Y H:i', strtotime($task['created_at'] ?? 'now')) ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/async/task/<?= $task['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="11" class="text-center text-muted py-4">No tasks found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>