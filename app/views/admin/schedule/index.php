<?php
$page_title = $page_title ?? 'Work Schedule - APS Dream Home';
$page_heading = $page_heading ?? 'Work Schedule Management';

$today_shifts = $today_shifts ?? [];
$total_employees = $total_employees ?? 0;
$on_shift_today = $on_shift_today ?? 0;
$shift_types_count = $shift_types_count ?? 0;
$coverage_rate = $coverage_rate ?? 0;
$status_breakdown = $status_breakdown ?? [];
$upcoming_week = $upcoming_week ?? [];
$dept_coverage = $dept_coverage ?? [];
$recent_changes = $recent_changes ?? [];
$today = $today ?? date('Y-m-d');
$day_of_week = $day_of_week ?? date('w');
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-calendar-alt me-2"></i><?= htmlspecialchars($page_heading) ?></h4>
        <div class="btn-group">
            <a href="<?= BASE_URL ?>/admin/schedule/shift-types" class="btn btn-outline-primary btn-sm"><i class="fas fa-clock"></i> Shift Types</a>
            <a href="<?= BASE_URL ?>/admin/schedule/employee-shifts" class="btn btn-outline-primary btn-sm"><i class="fas fa-user-clock"></i> Employee Shifts</a>
            <a href="<?= BASE_URL ?>/admin/schedule/shift-schedule" class="btn btn-outline-primary btn-sm"><i class="fas fa-table"></i> Schedule</a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body text-center py-3">
                    <h5 class="mb-0"><?= $total_employees ?></h5>
                    <small>Total users</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body text-center py-3">
                    <h5 class="mb-0"><?= $on_shift_today ?></h5>
                    <small>On Shift Today</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body text-center py-3">
                    <h5 class="mb-0"><?= $shift_types_count ?></h5>
                    <small>Shift Types</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-white">
                <div class="card-body text-center py-3">
                    <h5 class="mb-0"><?= $coverage_rate ?>%</h5>
                    <small>Coverage Rate</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- Today's Shifts -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-2"><strong><i class="fas fa-users me-2"></i>Today's Shifts</strong></div>
                <div class="card-body p-0">
                    <?php if (empty($today_shifts)): ?>
                        <div class="text-center py-4 text-muted">No shifts scheduled for today</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead><tr><th>Employee</th><th>Shift</th><th>Time</th><th>Status</th></tr></thead>
                                <tbody>
                                <?php foreach ($today_shifts as $shift): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($shift['employee_name'] ?? '') ?></td>
                                        <td><span class="badge" class="style-22927"><?= htmlspecialchars($shift['shift_type_name'] ?? '') ?></span></td>
                                        <td><?= htmlspecialchars($shift['shift_start_time'] ?? '') ?>-<?= htmlspecialchars($shift['shift_end_time'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($shift['status'] ?? '') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Status Breakdown -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-2"><strong><i class="fas fa-chart-pie me-2"></i>Status Today</strong></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (empty($status_breakdown)): ?>
                        <div class="text-center py-4 text-muted">No data</div>
                    <?php else: ?>
                        <?php foreach ($status_breakdown as $sb): ?>
                            <div class="d-flex justify-content-between mb-1">
                                <span><?= htmlspecialchars($sb['status'] ?? '') ?></span>
                                <strong><?= $sb['count'] ?? 0 ?></strong>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Upcoming Week -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-2"><strong><i class="fas fa-calendar-week me-2"></i>Upcoming Week</strong></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (empty($upcoming_week)): ?>
                        <div class="text-center py-4 text-muted">No upcoming shifts</div>
                    <?php else: ?>
                        <?php foreach ($upcoming_week as $uw): ?>
                            <div class="d-flex justify-content-between mb-1">
                                <span><?= htmlspecialchars($uw['shift_date'] ?? '') ?></span>
                                <strong><?= $uw['count'] ?? 0 ?></strong>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
