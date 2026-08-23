<?php
$page_title = $page_title ?? 'Task Details - APS Dream Home';
$page_heading = $page_heading ?? 'Task Details';
$task = $task ?? [];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-info-circle me-2"></i><?= htmlspecialchars($page_heading ?? '') ?></h2>
        <div>
            <a href="<?= BASE_URL ?>/async/tasks" class="btn btn-outline-secondary me-2"><i class="fas fa-arrow-left me-2"></i>Back to Tasks</a>
            <?php if (($task['status'] ?? '') === 'pending' || ($task['status'] ?? '') === 'running'): ?>
                <a href="<?= BASE_URL ?>/async/task/<?= $task['id'] ?>/cancel" class="btn btn-outline-danger me-2" onclick="return confirm('Cancel this task?')"><i class="fas fa-ban me-2"></i>Cancel</a>
            <?php endif; ?>
            <?php if (($task['status'] ?? '') === 'failed'): ?>
                <a href="<?= BASE_URL ?>/async/task/<?= $task['id'] ?>/retry" class="btn btn-outline-warning me-2"><i class="fas fa-redo me-2"></i>Retry</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Task ID</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">#<?= $task['id'] ?? 'N/A' ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-hashtag fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Status</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php
                                $status = $task['status'] ?? 'pending';
                                $badgeClass = match($status) {
                                    'completed' => 'text-success',
                                    'running' => 'text-primary',
                                    'failed' => 'text-danger',
                                    'cancelled' => 'text-secondary',
                                    default => 'text-warning'
                                };
                                ?>
                                <span class="<?= $badgeClass ?>"><?= ucfirst($status) ?></span>
                            </div>
                        </div>
                        <div class="col-auto"><i class="fas fa-info-circle fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Type</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= ucfirst(str_replace('_', ' ', $task['task_type'] ?? 'N/A')) ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-cog fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Priority</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php
                                $priorityLabels = [1 => 'Low', 2 => 'Normal', 3 => 'High', 4 => 'Critical'];
                                $priorityColors = [1 => 'text-info', 2 => 'text-secondary', 3 => 'text-warning', 4 => 'text-danger'];
                                $p = (int)($task['priority'] ?? 2);
                                ?>
                                <span class="<?= $priorityColors[$p] ?? '' ?>"><?= $priorityLabels[$p] ?? 'Normal' ?></span>
                            </div>
                        </div>
                        <div class="col-auto"><i class="fas fa-flag fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Progress</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-bold">Progress</span>
                            <span class="fw-bold"><?= (int)($task['progress_percentage'] ?? 0) ?>%</span>
                        </div>
                        <div class="progress" class="style-51309">
                            <div class="progress-bar bg-primary" role="progressbar" class="style-98943" aria-valuenow="<?= (int)($task['progress_percentage'] ?? 0) ?>" aria-valuemin="0" aria-valuemax="100">
                                <?= (int)($task['progress_percentage'] ?? 0) ?>%
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($task['result'])): ?>
                    <div class="alert alert-info">
                        <h6 class="alert-heading"><i class="fas fa-check-circle me-2"></i>Result</h6>
                        <pre class="mb-0"><?= json_encode($task['result'], JSON_PRETTY_PRINT) ?></pre>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($task['error_message'])): ?>
                    <div class="alert alert-danger">
                        <h6 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Error</h6>
                        <pre class="mb-0"><?= htmlspecialchars($task['error_message'] ?? '') ?></pre>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Parameters</h6>
                </div>
                <div class="card-body">
                    <pre class="mb-0"><?= json_encode($task['parameters'] ?? [], JSON_PRETTY_PRINT) ?></pre>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Task Info</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tbody>
                            <tr><th scope="row" class="text-muted">Name</th><td><?= htmlspecialchars($task['task_name'] ?? 'N/A') ?></td></tr>
                            <tr><th scope="row" class="text-muted">Worker</th><td><?= htmlspecialchars($task['assigned_worker'] ?? 'Not assigned') ?></td></tr>
                            <tr><th scope="row" class="text-muted">Queue</th><td><?= htmlspecialchars($task['queue_name'] ?? 'default') ?></td></tr>
                            <tr><th scope="row" class="text-muted">Max Retries</th><td><?= (int)($task['max_retries'] ?? 3) ?></td></tr>
                            <tr><th scope="row" class="text-muted">Retry Count</th><td><?= (int)($task['retry_count'] ?? 0) ?></td></tr>
                            <tr><th scope="row" class="text-muted">Created</th><td><?= date('d M Y H:i:s', strtotime($task['created_at'] ?? 'now')) ?></td></tr>
                            <tr><th scope="row" class="text-muted">Started</th><td><?= $task['started_at'] ? date('d M Y H:i:s', strtotime($task['started_at'])) : 'Not started' ?></td></tr>
                            <tr><th scope="row" class="text-muted">Completed</th><td><?= $task['completed_at'] ? date('d M Y H:i:s', strtotime($task['completed_at'])) : 'Not completed' ?></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
<?php if (($task['status'] ?? '') === 'pending' || ($task['status'] ?? '') === 'running'): ?>
                        <a href="<?= BASE_URL ?>/async/task/<?= $task['id'] ?>/cancel" class="btn btn-outline-danger" onclick="return confirm('Cancel this task?')"><i class="fas fa-ban me-2"></i>Cancel Task</a>
                        <?php endif; ?>
                        <?php if (($task['status'] ?? '') === 'failed'): ?>
                            <a href="<?= BASE_URL ?>/async/task/<?= $task['id'] ?>/retry" class="btn btn-outline-warning"><i class="fas fa-redo me-2"></i>Retry Task</a>
                        <?php endif; ?>
                        <button class="btn btn-outline-primary" onclick="window.location.reload()"><i class="fas fa-sync-alt me-2"></i>Refresh Status</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>