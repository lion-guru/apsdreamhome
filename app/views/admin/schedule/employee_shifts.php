<?php
$page_title = $page_title ?? 'Employee Shifts - APS Dream Home';
$page_heading = $page_heading ?? 'Employee Shift Assignments';
$assignments = $assignments ?? [];
$users = $users ?? [];
$shift_types = $shift_types ?? [];
$filters = $filters ?? [];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-user-clock me-2"></i><?= htmlspecialchars($page_heading ?? '') ?></h4>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#assignShiftModal"><i class="fas fa-plus"></i> Assign Shift</button>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-auto">
                    <select name="employee_id" class="form-select form-select-sm">
                        <option value="">All users</option>
                        <?php foreach ($users as $emp): ?>
                            <option value="<?= $emp['id'] ?>" <?= ($filters['employee_id'] ?? '') == $emp['id'] ? 'selected' : '' ?>><?= htmlspecialchars($emp['name'] ?? '') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <select name="shift_type_id" class="form-select form-select-sm">
                        <option value="">All Shift Types</option>
                        <?php foreach ($shift_types as $st): ?>
                            <option value="<?= $st['id'] ?>" <?= ($filters['shift_type_id'] ?? '') == $st['id'] ? 'selected' : '' ?>><?= htmlspecialchars($st['name'] ?? '') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="scheduled" <?= ($filters['status'] ?? '') == 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                        <option value="confirmed" <?= ($filters['status'] ?? '') == 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                        <option value="completed" <?= ($filters['status'] ?? '') == 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="cancelled" <?= ($filters['status'] ?? '') == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-auto">
                    <input type="date" name="date_from" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
                </div>
                <div class="col-auto">
                    <input type="date" name="date_to" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>">
                </div>
                <div class="col-auto">
                    <button class="btn btn-sm btn-outline-primary"><i class="fas fa-filter"></i> Filter</button>
                    <a href="<?= BASE_URL ?>/admin/schedule/employee-shifts" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Assignments Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($assignments)): ?>
                <div class="text-center py-5 text-muted"><i class="fas fa-calendar-times fa-3x mb-3"></i><p>No shift assignments found.</p></div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Employee</th><th>Department</th><th>Shift</th><th>Date</th><th>Time</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody>
                        <?php foreach ($assignments as $a): ?>
                            <tr>
                                <td><?= htmlspecialchars($a['employee_name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($a['department'] ?? '') ?></td>
                                <td><span class="badge"><?= htmlspecialchars($a['shift_type_name'] ?? '') ?></span></td>
                                <td><?= htmlspecialchars($a['shift_date'] ?? '') ?></td>
                                <td><?= htmlspecialchars($a['shift_start_time'] ?? '') ?>-<?= htmlspecialchars($a['shift_end_time'] ?? '') ?></td>
                                <td><?= htmlspecialchars($a['status'] ?? '') ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary edit-assignment" data-id="<?= $a['id'] ?>"><i class="fas fa-edit"></i></button>
                                    <form method="POST" action="<?= BASE_URL ?>/admin/schedule/assignments/remove/<?= $a['id'] ?>" class="d-inline" data-aps-confirm="Delete this shift assignment?">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" aria-label="Delete"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Assign Shift Modal -->
<div class="modal fade" id="assignShiftModal" tabindex="-1" aria-labelledby="assignShiftModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignShiftModalLabel"><i class="fas fa-user-clock me-2"></i>Assign Shift</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/admin/schedule/assign-shift">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <div class="mb-3">
                        <label class="form-label" for="asEmployee">Employee *</label>
                        <select class="form-select" id="asEmployee" name="employee_id" required>
                            <option value="">Select employee</option>
                            <?php foreach ($users as $emp): ?>
                                <option value="<?= (int)$emp['id'] ?>"><?= htmlspecialchars($emp['name'] ?? ('ID ' . $emp['id'])) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="asShiftType">Shift Type *</label>
                        <select class="form-select" id="asShiftType" name="shift_type_id" required>
                            <option value="">Select shift type</option>
                            <?php foreach ($shift_types as $st): ?>
                                <option value="<?= (int)$st['id'] ?>" data-start="<?= htmlspecialchars($st['start_time'] ?? '') ?>" data-end="<?= htmlspecialchars($st['end_time'] ?? '') ?>"><?= htmlspecialchars($st['name'] ?? '') ?><?= !empty($st['start_time']) ? ' (' . htmlspecialchars($st['start_time'] . '-' . ($st['end_time'] ?? '')) . ')' : '' ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="asDate">Shift Date *</label>
                        <input type="date" class="form-control" id="asDate" name="shift_date" required value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="asStart">Start Time</label>
                            <input type="time" class="form-control" id="asStart" name="start_time">
                            <div class="form-text">Defaults to shift type hours.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="asEnd">End Time</label>
                            <input type="time" class="form-control" id="asEnd" name="end_time">
                        </div>
                    </div>
                    <div class="mb-3 mt-3">
                        <label class="form-label" for="asNotes">Notes</label>
                        <input type="text" class="form-control" id="asNotes" name="notes" maxlength="255">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Assign Shift</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
(function() {
    var shiftSelect = document.getElementById('asShiftType');
    if (!shiftSelect) return;
    shiftSelect.addEventListener('change', function() {
        var opt = shiftSelect.options[shiftSelect.selectedIndex];
        if (!opt) return;
        if (opt.getAttribute('data-start')) document.getElementById('asStart').value = opt.getAttribute('data-start').substring(0, 5);
        if (opt.getAttribute('data-end')) document.getElementById('asEnd').value = opt.getAttribute('data-end').substring(0, 5);
    });
})();
</script>
