<?php
$page_title = $page_title ?? 'Attendance Report';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Attendance Report</h4>
    <a href="<?= BASE_URL ?>/admin/hr/attendance" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body aps-cp-card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small">Month</label>
                <select name="month" class="form-select">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= str_pad($m, 2, '0', STR_PAD_LEFT) ?>" <?= ($month ?? date('m')) === str_pad($m, 2, '0', STR_PAD_LEFT) ? 'selected' : '' ?>><?= date('F', mktime(0, 0, 0, $m, 1)) ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Year</label>
                <select name="year" class="form-select">
                    <?php for ($y = date('Y') - 2; $y <= date('Y') + 1; $y++): ?>
                        <option value="<?= $y ?>" <?= ($year ?? date('Y')) == $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-2"></i>Generate</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold"><?= date('F Y', mktime(0, 0, 0, $month ?? 1, 1, $year ?? date('Y'))) ?></h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Employee</th><th>Present</th><th>Absent</th><th>Half Day</th><th>Leave</th><th>Holiday</th><th>Total</th><th>Rate</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($report ?? [])): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No data for this month</td></tr>
                    <?php else: ?>
                        <?php foreach ($report as $r): ?>
                            <?php $totalPresent = ($r['present'] ?? 0) + ($r['half_day'] ?? 0) * 0.5; $rate = ($r['total_days'] ?? 0) > 0 ? round(($totalPresent / ($r['total_days'] ?? 1)) * 100, 1) : 0; ?>
                            <tr>
                                <td class="fw-medium"><?= htmlspecialchars($r['name'] ?? '') ?></td>
                                <td><span class="badge bg-success"><?= (int)($r['present'] ?? 0) ?></span></td>
                                <td><span class="badge bg-danger"><?= (int)($r['absent'] ?? 0) ?></span></td>
                                <td><span class="badge bg-warning"><?= (int)($r['half_day'] ?? 0) ?></span></td>
                                <td><span class="badge bg-info"><?= (int)($r['leave_count'] ?? 0) ?></span></td>
                                <td><span class="badge bg-secondary"><?= (int)($r['holiday'] ?? 0) ?></span></td>
                                <td><?= (int)($r['total_days'] ?? 0) ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 me-2" style="height:6px;max-width:80px;">
                                            <div class="progress-bar bg-<?= $rate >= 90 ? 'success' : ($rate >= 75 ? 'warning' : 'danger') ?>" style="width:<?= $rate ?>%"></div>
                                        </div>
                                        <small><?= $rate ?>%</small>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
