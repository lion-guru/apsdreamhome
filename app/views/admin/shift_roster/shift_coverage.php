<?php
/**
 * Shift Coverage Report View
*/

$page_title = $page_title ?? 'Shift Coverage Report';
$coverage = $coverage ?? [];
$start_date = $start_date ?? date('Y-m-01');
$end_date = $end_date ?? date('Y-m-t');
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-users me-2"></i>Shift Coverage Report</h1>
    </div>

    <!-- Date Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> Generate Report</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Coverage Table -->
    <div class="card">
        <div class="card-body p-0">
            <?php if (empty($coverage)): ?>
            <div class="text-center py-5">
                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Shift Coverage Data</h5>
                <p class="text-muted">No shifts scheduled for the selected date range</p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Shift</th>
                            <th class="text-center">Assigned</th>
                            <th>Employees</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($coverage as $c): ?>
                        <tr>
                            <td>
                                <strong><?= date('d M Y', strtotime($c['shift_date'])) ?></strong>
                                <br><small class="text-muted"><?= date('D', strtotime($c['shift_date'])) ?></small>
                            </td>
                            <td>
                                <span class="badge me-1" style="background: <?= $c['color'] ?>"><?= htmlspecialchars($c['shift_name']) ?></span>
                            </td>
                            <td class="text-center">
                                <strong><?= (int)$c['assigned_count'] ?></strong>
                            </td>
                            <td>
                                <small><?= htmlspecialchars($c['employee_names']) ?></small>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Summary by Shift -->
    <?php if (!empty($coverage)): ?>
    <div class="card mt-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Coverage Summary by Shift</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Shift</th>
                            <th class="text-center">Total Assignments</th>
                            <th class="text-center">Days Covered</th>
                            <th class="text-center">Avg. Employees/Day</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $shiftSummary = [];
                        foreach ($coverage as $c) {
                            $key = $c['shift_name'];
                            if (!isset($shiftSummary[$key])) {
                                $shiftSummary[$key] = ['color' => $c['color'], 'assignments' => 0, 'days' => 0];
                            }
                            $shiftSummary[$key]['assignments'] += (int)$c['assigned_count'];
                            $shiftSummary[$key]['days']++;
                        }
                        foreach ($shiftSummary as $name => $data):
                        ?>
                        <tr>
                            <td>
                                <span class="badge me-1" style="background: <?= $data['color'] ?>"><?= htmlspecialchars($name) ?></span>
                            </td>
                            <td class="text-center"><strong><?= $data['assignments'] ?></strong></td>
                            <td class="text-center"><?= $data['days'] ?></td>
                            <td class="text-center"><?= $data['days'] > 0 ? round($data['assignments'] / $data['days'], 1) : 0 ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.card { border-radius: 0.75rem; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
</style>