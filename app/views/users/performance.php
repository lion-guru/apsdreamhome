<?php
$metrics = $metrics ?? [];
$overall = $overall ?? [
    'tasks_completed' => 0,
    'on_time_rate' => 0,
    'rating' => 0,
    'attendance_percent' => 0,
];
$weeks = $weeks ?? [];
?>

<div class="container-fluid">
    <h1 class="h3 mb-4">Performance Overview</h1>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-primary text-white">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <h5 class="card-title">Tasks Completed</h5>
                    <h2 class="mb-0"><?= (int)($overall['tasks_completed'] ?? 0) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-success text-white">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-2x mb-2"></i>
                    <h5 class="card-title">On-Time Rate</h5>
                    <h2 class="mb-0"><?= (int)($overall['on_time_rate'] ?? 0) ?>%</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-warning text-dark">
                <div class="card-body text-center">
                    <i class="fas fa-star fa-2x mb-2"></i>
                    <h5 class="card-title">Rating</h5>
                    <h2 class="mb-0"><?= htmlspecialchars(number_format((float)($overall['rating'] ?? 0), 1)) ?> / 5</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-info text-white">
                <div class="card-body text-center">
                    <i class="fas fa-user-check fa-2x mb-2"></i>
                    <h5 class="card-title">Attendance</h5>
                    <h2 class="mb-0"><?= (int)($overall['attendance_percent'] ?? 0) ?>%</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header aps-cp-card-header">
            <h5 class="mb-0">Monthly Breakdown</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Month</th>
                            <th>Tasks Completed</th>
                            <th>On-Time Rate</th>
                            <th>Rating</th>
                            <th>Attendance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($metrics)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No performance data available.</td></tr>
                        <?php else: ?>
                            <?php foreach ($metrics as $m): ?>
                                <tr>
                                    <td><?= htmlspecialchars($m['month'] ?? '') ?></td>
                                    <td><?= (int)($m['tasks_completed'] ?? 0) ?></td>
                                    <td><?= (int)($m['on_time_rate'] ?? 0) ?>%</td>
                                    <td><?= htmlspecialchars(number_format((float)($m['rating'] ?? 0), 1)) ?></td>
                                    <td><?= (int)($m['attendance'] ?? 0) ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
