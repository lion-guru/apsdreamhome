<?php
/**
 * Employee Performance View
 * Shows employee performance metrics and analytics
 */
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-chart-line me-2"></i>My Performance</h2>
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="fas fa-calendar me-2"></i>Select Period
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="?period=month">This Month</a></li>
                <li><a class="dropdown-item" href="?period=quarter">This Quarter</a></li>
                <li><a class="dropdown-item" href="?period=year">This Year</a></li>
            </ul>
        </div>
    </div>

    <!-- Performance Overview -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stats-card card text-white" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">
                                <?php
                                $overallScore = ($monthly_performance['overall_score'] ?? 0);
                                echo number_format($overallScore, 1);
                                ?>%
                            </h4>
                            <small>Overall Score</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-star fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card card text-white" style="background: linear-gradient(135deg, #007bff 0%, #6610f2 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">
                                <?php
                                $productivityScore = ($monthly_performance['productivity_score'] ?? 0);
                                echo number_format($productivityScore, 1);
                                ?>%
                            </h4>
                            <small>Productivity</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-rocket fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card card text-white" style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">
                                <?php
                                $qualityScore = ($monthly_performance['quality_score'] ?? 0);
                                echo number_format($qualityScore, 1);
                                ?>%
                            </h4>
                            <small>Quality</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-gem fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card card text-white" style="background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">
                                <?php
                                $attendanceScore = ($monthly_performance['attendance_score'] ?? 0);
                                echo number_format($attendanceScore, 1);
                                ?>%
                            </h4>
                            <small>Attendance</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calendar-check fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Charts -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-bar me-2"></i>Performance Trend</h5>
                </div>
                <div class="card-body">
                    <canvas id="performanceChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-trophy me-2"></i>Achievements</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($monthly_performance['achievements'] ?? [])): ?>
                        <p class="text-muted">No achievements yet.</p>
                    <?php else: ?>
                        <?php foreach (($monthly_performance['achievements'] ?? []) as $achievement): ?>
                            <div class="achievement-item mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="achievement-icon me-3">
                                        <i class="fas fa-<?= $achievement['icon'] ?? 'trophy' ?> text-warning"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">
                                            <?= htmlspecialchars($achievement['title'] ?? 'Achievement') ?>
                                        </h6>
                                        <small class="text-muted">
                                            <?= htmlspecialchars($achievement['description'] ?? '') ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Metrics -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-tasks me-2"></i>Task Performance</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="metric-item text-center">
                                <h3 class="text-primary mb-1">
                                    <?= $monthly_performance['tasks_completed'] ?? 0 ?>
                                </h3>
                                <small class="text-muted">Tasks Completed</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="metric-item text-center">
                                <h3 class="text-success mb-1">
                                    <?= number_format($monthly_performance['avg_completion_time'] ?? 0, 1) ?>h
                                </h3>
                                <small class="text-muted">Avg. Completion Time</small>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-6">
                            <div class="metric-item text-center">
                                <h3 class="text-info mb-1">
                                    <?= $monthly_performance['on_time_delivery'] ?? 0 ?>%
                                </h3>
                                <small class="text-muted">On-Time Delivery</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="metric-item text-center">
                                <h3 class="text-warning mb-1">
                                    <?= $monthly_performance['revision_rate'] ?? 0 ?>%
                                </h3>
                                <small class="text-muted">Revision Rate</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-pie me-2"></i>Performance Breakdown</h5>
                </div>
                <div class="card-body">
                    <canvas id="performanceBreakdownChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Goals and Targets -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-bullseye me-2"></i>Goals & Targets</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach (($monthly_performance['goals'] ?? []) as $goal): ?>
                            <div class="col-md-4 mb-3">
                                <div class="goal-card">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0">
                                            <?= htmlspecialchars($goal['title'] ?? 'Goal') ?>
                                        </h6>
                                        <span class="badge bg-<?= $goal['status'] === 'achieved' ? 'success' : 'warning' ?>">
                                            <?= ucfirst($goal['status'] ?? 'pending') ?>
                                        </span>
                                    </div>
                                    <div class="progress mb-2">
                                        <div class="progress-bar bg-<?= $goal['status'] === 'achieved' ? 'success' : 'warning' ?>"
                                             style="width: <?= min(($goal['progress'] ?? 0), 100) ?>%">
                                            <?= $goal['progress'] ?? 0 ?>%
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        Target: <?= htmlspecialchars($goal['target'] ?? 'N/A') ?> |
                                        Current: <?= htmlspecialchars($goal['current'] ?? '0') ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance History -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-history me-2"></i>Performance History</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Period</th>
                                    <th>Overall Score</th>
                                    <th>Productivity</th>
                                    <th>Quality</th>
                                    <th>Attendance</th>
                                    <th>Tasks Completed</th>
                                    <th>Grade</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (($monthly_performance['history'] ?? []) as $period): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($period['period'] ?? 'N/A') ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $this->getScoreBadgeClass($period['overall_score'] ?? 0) ?>">
                                                <?= number_format($period['overall_score'] ?? 0, 1) ?>%
                                            </span>
                                        </td>
                                        <td>
                                            <?= number_format($period['productivity'] ?? 0, 1) ?>%
                                        </td>
                                        <td>
                                            <?= number_format($period['quality'] ?? 0, 1) ?>%
                                        </td>
                                        <td>
                                            <?= number_format($period['attendance'] ?? 0, 1) ?>%
                                        </td>
                                        <td>
                                            <?= $period['tasks_completed'] ?? 0 ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $this->getGradeBadgeClass($period['grade'] ?? 'N/A') ?>">
                                                <?= htmlspecialchars($period['grade'] ?? 'N/A') ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Performance Trend Chart
const performanceCtx = document.getElementById('performanceChart');
if (performanceCtx) {
    new Chart(performanceCtx, {
        type: 'line',
        data: {
            labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
            datasets: [{
                label: 'Overall Performance',
                data: [
                    <?= $monthly_performance['week1_score'] ?? 75 ?>,
                    <?= $monthly_performance['week2_score'] ?? 80 ?>,
                    <?= $monthly_performance['week3_score'] ?? 85 ?>,
                    <?= $monthly_performance['week4_score'] ?? 90 ?>
                ],
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
}

// Performance Breakdown Chart
const breakdownCtx = document.getElementById('performanceBreakdownChart');
if (breakdownCtx) {
    new Chart(breakdownCtx, {
        type: 'doughnut',
        data: {
            labels: ['Productivity', 'Quality', 'Attendance', 'Other'],
            datasets: [{
                data: [
                    <?= $monthly_performance['productivity_score'] ?? 80 ?>,
                    <?= $monthly_performance['quality_score'] ?? 85 ?>,
                    <?= $monthly_performance['attendance_score'] ?? 90 ?>,
                    <?= 100 - (($monthly_performance['productivity_score'] ?? 80) + ($monthly_performance['quality_score'] ?? 85) + ($monthly_performance['attendance_score'] ?? 90)) ?>
                ],
                backgroundColor: [
                    '#007bff',
                    '#28a745',
                    '#ffc107',
                    '#6c757d'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

// Auto-refresh performance data every 5 minutes
setInterval(function() {
    if (!document.hidden) {
        location.reload();
    }
}, 300000);
</script>

<style>
.stats-card {
    border: none;
    border-radius: 10px;
    transition: transform 0.2s;
}

.stats-card:hover {
    transform: translateY(-2px);
}

.metric-item {
    padding: 15px;
    border-radius: 8px;
    background: #f8f9fa;
    margin-bottom: 10px;
}

.goal-card {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    transition: box-shadow 0.2s;
}

.goal-card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.achievement-item {
    padding: 10px;
    border-radius: 8px;
    background: #f8f9fa;
    border-left: 4px solid #ffc107;
}

.achievement-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #ffc107;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
}

.table th {
    border-top: none;
    font-weight: 600;
}

.badge {
    font-size: 0.8em;
}
</style>
