<?php
$page_title = $page_title ?? 'Employee Report';
$report = $report ?? null;
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-file-alt me-2"></i>Employee Report</h4>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body aps-cp-card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small">Employee</label>
                <select name="employee_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Select Employee</option>
                    <?php foreach ($users ?? [] as $emp): ?>
                        <option value="<?= $emp['id'] ?>" <?= ($emp_id ?? '') == $emp['id'] ? 'selected' : '' ?>><?= htmlspecialchars($emp['name'] ?? '') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>
</div>

<?php if ($report): ?>
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body aps-cp-card-body">
                    <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-2" style="width:60px;height:60px;font-size:1.5rem;">
                        <?= strtoupper(substr($report['name'] ?? '?', 0, 1)) ?>
                    </div>
                    <h5 class="mb-1"><?= htmlspecialchars($report['name'] ?? '') ?></h5>
                    <div class="text-muted small"><?= htmlspecialchars($report['designation'] ?? '') ?> - <?= htmlspecialchars($report['department'] ?? '') ?></div>
                    <div class="mt-2">
                        <span class="badge bg-<?= ($report['status'] ?? '') === 'active' ? 'success' : 'danger' ?>"><?= htmlspecialchars($report['status'] ?? '') ?></span>
                        <span class="badge bg-info ms-1">₹<?= number_format($report['salary'] ?? 0, 2) ?></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-calendar-check me-2 text-success"></i>Attendance (Last 30 Records)</h6>
                </div>
                <div class="card-body p-0" style="max-height:300px;overflow-y:auto;">
                    <div class="table-responsive"><table class="table table-sm mb-0">
                        <thead class="table-light"><tr><th>Date</th><th>Status</th><th>In</th><th>Out</th></tr></thead>
                        <tbody>
                            <?php if (empty($attendances ?? [])): ?>
                                <tr><td colspan="4" class="text-center text-muted py-2">No records</td></tr>
                            <?php else: ?>
                                <?php foreach ($attendances as $a): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($a['attendance_date'] ?? '') ?></td>
                                        <td><span class="badge bg-<?= ($a['attendance_status'] ?? '') === 'present' ? 'success' : (($a['attendance_status'] ?? '') === 'absent' ? 'danger' : 'warning') ?>"><?= htmlspecialchars($a['attendance_status'] ?? '') ?></span></td>
                                        <td><?= htmlspecialchars($a['check_in_time'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($a['check_out_time'] ?? '-') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-umbrella-beach me-2 text-warning"></i>Leave History</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive"><table class="table table-sm mb-0">
                        <thead class="table-light"><tr><th>Type</th><th>From</th><th>To</th><th>Days</th><th>Status</th></tr></thead>
                        <tbody>
                            <?php if (empty($leaves ?? [])): ?>
                                <tr><td colspan="5" class="text-center text-muted py-2">No leaves</td></tr>
                            <?php else: ?>
                                <?php foreach ($leaves as $l): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($l['leave_type_name'] ?? $l['leave_type'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($l['start_date'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($l['end_date'] ?? '') ?></td>
                                        <td><?= $l['total_days'] ?? '' ?></td>
                                        <td><span class="badge bg-<?= ($l['status'] ?? '') === 'approved' ? 'success' : (($l['status'] ?? '') === 'rejected' ? 'danger' : 'warning') ?>"><?= htmlspecialchars($l['status'] ?? '') ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table></div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-gift me-2 text-success"></i>Bonuses</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive"><table class="table table-sm mb-0">
                        <thead class="table-light"><tr><th>Type</th><th>Amount</th><th>Month</th><th>Status</th></tr></thead>
                        <tbody>
                            <?php if (empty($bonuses ?? [])): ?>
                                <tr><td colspan="4" class="text-center text-muted py-2">No bonuses</td></tr>
                            <?php else: ?>
                                <?php foreach ($bonuses as $b): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($b['bonus_type'] ?? '') ?></td>
                                        <td class="text-success fw-bold">₹<?= number_format($b['bonus_amount'] ?? 0, 2) ?></td>
                                        <td><?= htmlspecialchars($b['bonus_month'] ?? '') ?>/<?= htmlspecialchars($b['bonus_year'] ?? '') ?></td>
                                        <td><span class="badge bg-<?= ($b['payment_status'] ?? '') === 'paid' ? 'success' : 'warning' ?>"><?= htmlspecialchars($b['payment_status'] ?? '') ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table></div>
                </div>
            </div>
        </div>
    </div>
<?php elseif ($emp_id ?? 0): ?>
    <div class="alert alert-warning">Employee not found.</div>
<?php endif; ?>
