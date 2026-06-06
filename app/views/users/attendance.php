<?php
$attendance = $attendance ?? [];
$month = $month ?? date('Y-m');
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Attendance Records</h1>
        <form method="get" class="row g-2 align-items-center">
            <div class="col-auto">
                <input type="month" name="month" class="form-control form-control-sm" value="<?= htmlspecialchars($month) ?>">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter"></i> Filter</button>
            </div>
        </form>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Hours</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($attendance)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No attendance records found for this month.</td></tr>
                        <?php else: ?>
                            <?php foreach ($attendance as $a): ?>
                                <tr>
                                    <td><?= htmlspecialchars($a['date'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($a['check_in'] ?? '--') ?></td>
                                    <td><?= htmlspecialchars($a['check_out'] ?? '--') ?></td>
                                    <td><?= htmlspecialchars($a['hours'] ?? '--') ?></td>
                                    <td>
                                        <?php $s = $a['status'] ?? ''; ?>
                                        <?php if ($s === 'Present'): ?>
                                            <span class="badge bg-success">Present</span>
                                        <?php elseif ($s === 'Absent'): ?>
                                            <span class="badge bg-danger">Absent</span>
                                        <?php elseif ($s === 'Late'): ?>
                                            <span class="badge bg-warning text-dark">Late</span>
                                        <?php elseif ($s === 'Half Day'): ?>
                                            <span class="badge bg-info">Half Day</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?= htmlspecialchars($s) ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
