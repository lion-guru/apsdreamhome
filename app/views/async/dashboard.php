<?php
$page_title = $page_title ?? 'Async Task Dashboard - APS Dream Home';
$page_heading = $page_heading ?? 'Async Task Dashboard';
$stats = $stats ?? [];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-tasks me-2"></i><?= htmlspecialchars($page_heading) ?></h2>
        <a href="<?= BASE_URL ?>/async/create" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Create Task</a>
    </div>

    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Tasks</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($stats['total_tasks'] ?? 0) ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-tasks fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Completed</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($stats['completed_tasks'] ?? 0) ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-check-circle fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Failed</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($stats['failed_tasks'] ?? 0) ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-times-circle fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Avg Time (s)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($stats['avg_completion_time'] ?? 0, 1) ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-clock fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Tasks</h6>
                    <a href="<?= BASE_URL ?>/async/tasks" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Task Name</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Priority</th>
                                    <th>Progress</th>
                                    <th>Worker</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($stats['recent'])): ?>
                                    <?php foreach ($stats['recent'] as $task): ?>
                                    <tr>
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
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar" role="progressbar" style="width: <?= (int)($task['progress_percentage'] ?? 0) ?>%" aria-valuenow="<?= (int)($task['progress_percentage'] ?? 0) ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($task['assigned_worker'] ?? '-') ?></td>
                                        <td><?= date('d M Y H:i', strtotime($task['created_at'] ?? 'now')) ?></td>
                                        <td>
                                            <a href="<?= BASE_URL ?>/async/task/<?= $task['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="8" class="text-center text-muted py-4">No tasks found</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Tasks by Status</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="statusChart" height="100"></canvas>
                    </div>
                </div>
            </div>
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Tasks by Type</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="typeChart" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusData = <?= json_encode($stats['by_status'] ?? []) ?>;
    const typeData = <?= json_encode($stats['by_type'] ?? []) ?>;

    if (typeof Chart !== 'undefined') {
        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: statusData.map(d => d.status?.charAt(0).toUpperCase() + d.status?.slice(1) ?? 'Unknown'),
                datasets: [{
                    data: statusData.map(d => parseInt(d.count) || 0),
                    backgroundColor: ['#4e73df', '#1cc88a', '#e74a3b', '#858796', '#f6c23e']
                }]
            }
        });

        new Chart(document.getElementById('typeChart'), {
            type: 'doughnut',
            data: {
                labels: typeData.map(d => d.task_type ?? 'Unknown'),
                datasets: [{
                    data: typeData.map(d => parseInt(d.count) || 0),
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796']
                }]
            }
        });
    }
});
</script>