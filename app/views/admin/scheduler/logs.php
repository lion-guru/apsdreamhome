<?php
$pageTitle = $pageTitle ?? 'Scheduler Logs';
$base = $base ?? (defined('BASE_URL') ? BASE_URL : '/apsdreamhome');
$logs = $logs ?? [];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-history me-2 text-info"></i>Execution Logs</h1>
        <a href="<?= $base ?>/admin/scheduler" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">Task Execution History</h6>
        </div>
        <div class="card-body aps-cp-card-body">
            <?php if (empty($logs)): ?>
                <p class="text-muted text-center py-4"><i class="fas fa-inbox fa-2x d-block mb-2"></i>No execution logs found.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Task Name</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Duration</th>
                                <th>Status</th>
                                <th>Output</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= htmlspecialchars($log['task_name'] ?? $log['name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($log['start_time'] ?? '') ?></td>
                                <td><?= htmlspecialchars($log['end_time'] ?? '') ?></td>
                                <td>
                                    <?php
                                    $s = strtotime($log['start_time'] ?? '');
                                    $e = strtotime($log['end_time'] ?? '');
                                    echo $s && $e ? ($e - $s) . 's' : '-';
                                    ?>
                                </td>
                                <td>
                                    <?php $status = $log['status'] ?? ''; ?>
                                    <?php if ($status === 'success'): ?>
                                        <span class="badge bg-success">Success</span>
                                    <?php elseif ($status === 'failed'): ?>
                                        <span class="badge bg-danger">Failed</span>
                                    <?php elseif ($status === 'running'): ?>
                                        <span class="badge bg-warning">Running</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($status) ?: 'Unknown' ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><code><?= htmlspecialchars(substr($log['output'] ?? '', 0, 120)) ?></code></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
