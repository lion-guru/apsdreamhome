<?php
$page_title = $page_title ?? 'Shift Schedule';
$isAssign = ($mode ?? '') === 'assign';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-calendar-alt me-2"></i><?= $isAssign ? 'Assign Shift' : 'Shift Schedule' ?></h4>
    <div>
        <?php if (!$isAssign): ?>
            <a href="<?= BASE_URL ?>/admin/hr/shifts/assign" class="btn btn-primary me-2"><i class="fas fa-plus me-2"></i>Assign Shift</a>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>/admin/hr/shifts" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Shifts</a>
    </div>
</div>

<?php if ($isAssign): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body aps-cp-card-body">
            <form method="POST" action="<?= BASE_URL ?>/admin/hr/shifts/assign">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? $_SESSION['csrf_token'] ?? ''; ?>">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Employee <span class="text-danger">*</span></label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">Select Employee</option>
                            <?php foreach ($users ?? [] as $emp): ?>
                                <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['name'] ?? '') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Shift Type <span class="text-danger">*</span></label>
                        <select name="shift_type_id" class="form-select" required>
                            <option value="">Select Shift</option>
                            <?php foreach ($shift_types ?? [] as $st): ?>
                                <option value="<?= $st['id'] ?>"><?= htmlspecialchars($st['name'] ?? '') ?> (<?= htmlspecialchars($st['start_time'] ?? '') ?> - <?= htmlspecialchars($st['end_time'] ?? '') ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date</label>
                        <input type="date" name="shift_date" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Start Time</label>
                        <input type="time" name="start_time" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">End Time</label>
                        <input type="time" name="end_time" class="form-control">
                    </div>
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Assign Shift</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php else: ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body aps-cp-card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small">Date</label>
                    <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($date ?? date('Y-m-d')) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-2"></i>View</button>
                </div>
            </form>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Employee</th><th>Shift</th><th>Date</th><th>Start</th><th>End</th><th>Duration</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($schedule ?? [])): ?>
                            <tr><td colspan="7" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No shifts scheduled for this date</td></tr>
                        <?php else: ?>
                            <?php foreach ($schedule as $s): ?>
                                <tr>
                                    <td class="fw-medium"><?= htmlspecialchars($s['employee_name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($s['shift_name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($s['shift_date'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($s['start_time'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($s['end_time'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($s['duration_hours'] ?? '') ?>h</td>
                                    <td>
                                        <span class="badge bg-<?= ($s['status'] ?? '') === 'scheduled' ? 'info' : (($s['status'] ?? '') === 'confirmed' ? 'primary' : (($s['status'] ?? '') === 'completed' ? 'success' : 'secondary')) ?>">
                                            <?= htmlspecialchars($s['status'] ?? '') ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>
