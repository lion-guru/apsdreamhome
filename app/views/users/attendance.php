<?php
$attendance = $attendance ?? [];
$stats = $stats ?? ['present' => 0, 'absent' => 0, 'late' => 0, 'half_day' => 0, 'total_hours' => 0];
$month = $month ?? date('Y-m');

function attStatusBadge($status) {
    $map = ['present' => 'success', 'full day' => 'success', 'absent' => 'danger', 'late' => 'warning', 'half day' => 'info', 'holiday' => 'secondary', 'week off' => 'secondary', 'leave' => 'purple'];
    $cls = $map[strtolower($status)] ?? 'secondary';
    return '<span class="badge bg-' . $cls . '">' . htmlspecialchars(ucfirst($status)) . '</span>';
}
function attStatusColor($status) {
    $map = ['present' => 'success', 'full day' => 'success', 'absent' => 'danger', 'late' => 'warning', 'half day' => 'info'];
    return $map[strtolower($status)] ?? 'secondary';
}
$workingDays = $stats['present'] + $stats['late'] + $stats['half_day'];
$totalDays = $workingDays + $stats['absent'];
$attendanceRate = $totalDays > 0 ? round(($workingDays / $totalDays) * 100) : 0;
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.emp-att-stat { border: none; border-radius: 12px; transition: transform 0.2s; }
.emp-att-stat:hover { transform: translateY(-2px); }
.emp-att-stat .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; }
.emp-att-meter { height: 10px; border-radius: 5px; background: #e2e8f0; overflow: hidden; }
.emp-att-meter-fill { height: 100%; border-radius: 5px; transition: width 0.5s; }
.emp-att-row { transition: background 0.15s; }
.emp-att-row:hover { background: #f8fafc; }
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-calendar-check me-2 text-primary"></i>Attendance</h4>
            <p class="text-muted mb-0 small">Month of <?= date('F Y', strtotime($month . '-01')) ?></p>
        </div>
        <form method="get" class="d-flex gap-2">
    <?php echo CSRFProtection::csrfField(); ?>
            <input type="month" name="month" class="form-control form-control-sm" value="<?= htmlspecialchars($month) ?>" style="max-width:170px;">
            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter me-1"></i>Filter</button>
        </form>
    </div>

    <!-- Attendance Rate Bar -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="fw-semibold">Attendance Rate</span>
                <span class="fw-bold fs-5 text-<?= $attendanceRate >= 90 ? 'success' : ($attendanceRate >= 75 ? 'warning' : 'danger') ?>"><?= $attendanceRate ?>%</span>
            </div>
            <div class="emp-att-meter">
                <div class="emp-att-meter-fill bg-<?= $attendanceRate >= 90 ? 'success' : ($attendanceRate >= 75 ? 'warning' : 'danger') ?>" style="width:<?= $attendanceRate ?>%"></div>
            </div>
            <div class="d-flex justify-content-between mt-2">
                <small class="text-muted"><?= $workingDays ?> days worked</small>
                <small class="text-muted"><?= $totalDays ?> total working days</small>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card emp-att-stat shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="fas fa-check-circle"></i></div>
                    <div><div class="fw-bold fs-4 text-success"><?= $stats['present'] ?></div><div class="text-muted small">Present</div></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card emp-att-stat shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger"><i class="fas fa-times-circle"></i></div>
                    <div><div class="fw-bold fs-4 text-danger"><?= $stats['absent'] ?></div><div class="text-muted small">Absent</div></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card emp-att-stat shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="fas fa-clock"></i></div>
                    <div><div class="fw-bold fs-4 text-warning"><?= $stats['late'] ?></div><div class="text-muted small">Late</div></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card emp-att-stat shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-info bg-opacity-10 text-info"><i class="fas fa-hourglass-half"></i></div>
                    <div><div class="fw-bold fs-4 text-info"><?= $stats['total_hours'] ?>h</div><div class="text-muted small">Hours Worked</div></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Table -->
    <?php if (empty($attendance)): ?>
        <div class="card shadow-sm border-0">
            <div class="card-body text-center py-5">
                <div class="mb-3"><i class="fas fa-calendar fa-4x text-muted opacity-25"></i></div>
                <h5 class="text-muted">No Records for <?= date('F Y', strtotime($month . '-01')) ?></h5>
                <p class="text-muted small">No attendance data found for this month</p>
            </div>
        </div>
    <?php else: ?>
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Day</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th class="text-end">Hours</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attendance as $a):
                                $date = $a['date'] ?? '';
                                $dayOfWeek = $date ? date('l', strtotime($date)) : '';
                            ?>
                                <tr class="emp-att-row">
                                    <td>
                                        <div class="fw-semibold"><?= $date ? date('d M Y', strtotime($date)) : '—' ?></div>
                                    </td>
                                    <td class="text-muted small"><?= $dayOfWeek ?></td>
                                    <td>
                                        <?php if (!empty($a['check_in'])): ?>
                                            <span class="fw-semibold"><?= date('h:i A', strtotime($a['check_in'])) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($a['check_out'])): ?>
                                            <span class="fw-semibold"><?= date('h:i A', strtotime($a['check_out'])) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if (!empty($a['hours'])): ?>
                                            <span class="fw-semibold"><?= number_format((float)$a['hours'], 1) ?>h</span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= attStatusBadge($a['status'] ?? 'absent') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
