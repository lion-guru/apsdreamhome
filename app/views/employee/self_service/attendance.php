<?php
/**
 * My Attendance
*/

$page_title = $page_title ?? 'My Attendance';
$attendance = $attendance ?? [];
$stats = $stats ?? [];
$month = $month ?? date('Y-m');
$prev_month = $prev_month ?? date('Y-m', strtotime($month . ' -1 month'));
$next_month = $next_month ?? date('Y-m', strtotime($month . ' +1 month'));
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-clock me-2"></i>My Attendance</h4>
                    <div class="d-flex align-items-center gap-2">
                        <a href="<?= BASE_URL ?>/employee/self-service/attendance?month=<?= $prev_month ?>" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <span class="fw-bold fs-5"><?= date('F Y', strtotime($month . '-01')) ?></span>
                        <a href="<?= BASE_URL ?>/employee/self-service/attendance?month=<?= $next_month ?>" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="stat-icon bg-success text-white mx-auto mb-2">
                                        <i class="fas fa-check-circle fa-2x"></i>
                                    </div>
                                    <h3 class="mb-0"><?= $stats['present'] ?? 0 ?></h3>
                                    <small class="text-muted">Present</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="stat-icon bg-danger text-white mx-auto mb-2">
                                        <i class="fas fa-times-circle fa-2x"></i>
                                    </div>
                                    <h3 class="mb-0"><?= $stats['absent'] ?? 0 ?></h3>
                                    <small class="text-muted">Absent</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="stat-icon bg-warning text-dark mx-auto mb-2">
                                        <i class="fas fa-clock fa-2x"></i>
                                    </div>
                                    <h3 class="mb-0"><?= $stats['late'] ?? 0 ?></h3>
                                    <small class="text-muted">Late</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="stat-icon bg-info text-white mx-auto mb-2">
                                        <i class="fas fa-hourglass-half fa-2x"></i>
                                    </div>
                                    <h3 class="mb-0"><?= $stats['half_day'] ?? 0 ?></h3>
                                    <small class="text-muted">Half Day</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="stat-icon bg-primary text-white mx-auto mb-2">
                                        <i class="fas fa-hourglass-start fa-2x"></i>
                                    </div>
                                    <h3 class="mb-0"><?= number_format($stats['total_hours'] ?? 0, 1) ?></h3>
                                    <small class="text-muted">Total Hours</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <div class="stat-icon bg-secondary text-white mx-auto mb-2">
                                        <i class="fas fa-bolt fa-2x"></i>
                                    </div>
                                    <h3 class="mb-0"><?= number_format($stats['total_overtime'] ?? 0, 1) ?></h3>
                                    <small class="text-muted">Overtime Hours</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Attendance Table -->
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Daily Attendance - <?= date('F Y', strtotime($month . '-01')) ?></h5>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($attendance)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No Attendance Records</h5>
                                <p class="text-muted">Records for this month will appear here</p>
                            </div>
                            <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Day</th>
                                            <th>Check In</th>
                                            <th>Check Out</th>
                                            <th>Hours</th>
                                            <th>Overtime</th>
                                            <th>Status</th>
                                            <th>Late</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($attendance as $a): ?>
                                        <tr>
                                            <td><strong><?= date('d M', strtotime($a['date'])) ?></strong></td>
                                            <td><?= date('D', strtotime($a['date'])) ?></td>
                                            <td><?= $a['check_in'] ? date('H:i', strtotime($a['check_in'])) : '-' ?></td>
                                            <td><?= $a['check_out'] ? date('H:i', strtotime($a['check_out'])) : '-' ?></td>
                                            <td><?= number_format($a['hours'] ?? 0, 2) ?></td>
                                            <td><?= number_format($a['overtime'] ?? 0, 2) ?></td>
                                            <td>
                                                <?php $st = strtolower($a['status'] ?? 'present'); ?>
                                                <span class="badge bg-<?= 
                                                    $st === 'present' ? 'success' : 
                                                    ($st === 'absent' ? 'danger' : 
                                                    ($st === 'half_day' ? 'warning' : 
                                                    ($st === 'late' ? 'info' : 'secondary'))) ?>">
                                                    <?= ucfirst(str_replace('_', ' ', $st)) ?>
                                                </span>
                                            </td>
                                            <td><?= $a['late_minutes'] ? $a['late_minutes'] . ' min' : '-' ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Monthly Summary -->
                    <div class="card mt-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Monthly Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="text-center p-3 bg-light rounded">
                                        <h4 class="text-primary"><?= $stats['present'] ?? 0 ?></h4>
                                        <small class="text-muted">Working Days</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center p-3 bg-light rounded">
                                        <h4 class="text-success"><?= number_format($stats['total_hours'] ?? 0, 1) ?></h4>
                                        <small class="text-muted">Total Hours</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center p-3 bg-light rounded">
                                        <h4 class="text-warning"><?= number_format($stats['total_overtime'] ?? 0, 1) ?></h4>
                                        <small class="text-muted">Overtime Hours</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center p-3 bg-light rounded">
                                        <?php 
                                        $workingDays = ($stats['present'] ?? 0) + ($stats['half_day'] ?? 0) * 0.5;
                                        $avgHours = $workingDays > 0 ? ($stats['total_hours'] ?? 0) / $workingDays : 0; 
                                        ?>
                                        <h4 class="text-info"><?= number_format($avgHours, 1) ?></h4>
                                        <small class="text-muted">Avg Hours/Day</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.stat-card { border: none; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
.stat-icon { width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
.card { border-radius: 0.75rem; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
.table th { border-top: none; font-weight: 600; color: #495057; }
.badge { font-size: 0.75rem; }
</style>