<?php
$page_title = $page_title ?? 'Attendance';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Attendance</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#markModal"><i class="fas fa-plus me-2"></i>Mark Attendance</button>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body aps-cp-card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small">Date</label>
                <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($date ?? date('Y-m-d')) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <option value="present" <?= ($status_filter ?? '') === 'present' ? 'selected' : '' ?>>Present</option>
                    <option value="absent" <?= ($status_filter ?? '') === 'absent' ? 'selected' : '' ?>>Absent</option>
                    <option value="late" <?= ($status_filter ?? '') === 'late' ? 'selected' : '' ?>>Late</option>
                    <option value="half_day" <?= ($status_filter ?? '') === 'half_day' ? 'selected' : '' ?>>Half Day</option>
                    <option value="leave" <?= ($status_filter ?? '') === 'leave' ? 'selected' : '' ?>>Leave</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-2"></i>Filter</button>
            </div>
            <div class="col-md-2">
                <label class="form-label small">&nbsp;</label>
                <a href="<?= BASE_URL ?>/admin/hr/attendance/report?month=<?= date('m') ?>&year=<?= date('Y') ?>" class="btn btn-outline-info w-100"><i class="fas fa-file-alt me-2"></i>Report</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Employee</th><th>Date</th><th>Status</th><th>Check In</th><th>Check Out</th><th>Notes</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($records)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No attendance records for this date</td></tr>
                    <?php else: ?>
                        <?php foreach ($records as $r): ?>
                            <tr>
                                <td><?= htmlspecialchars($r['employee_name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($r['attendance_date'] ?? '') ?></td>
                                <td>
                                    <span class="badge bg-<?= ($r['attendance_status'] ?? '') === 'present' ? 'success' : (($r['attendance_status'] ?? '') === 'absent' ? 'danger' : (($r['attendance_status'] ?? '') === 'half_day' ? 'warning' : 'info')) ?>">
                                        <?= htmlspecialchars($r['attendance_status'] ?? '') ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($r['check_in_time'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($r['check_out_time'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($r['remarks'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if (($total_pages ?? 1) > 1): ?>
        <div class="card-footer bg-white">
            <nav><ul class="pagination pagination-sm mb-0 justify-content-center">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i === ($page ?? 1) ? 'active' : '' ?>">
                        <a class="page-link" href="?date=<?= urlencode($date ?? date('Y-m-d')) ?>&status=<?= urlencode($status_filter ?? '') ?>&page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul></nav>
        </div>
    <?php endif; ?>
</div>

<!-- Mark Attendance Modal -->
<div class="modal fade" id="markModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/admin/hr/attendance/mark">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? $_SESSION['csrf_token'] ?? ''; ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-pen me-2"></i>Mark Attendance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Employee</label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">Select Employee</option>
                            <?php foreach ($users ?? [] as $emp): ?>
                                <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['name'] ?? '') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="present">Present</option>
                            <option value="absent">Absent</option>
                            <option value="half_day">Half Day</option>
                            <option value="late">Late</option>
                            <option value="leave">Leave</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Check In Time</label>
                        <input type="time" name="check_in" class="form-control" value="<?= date('H:i') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
