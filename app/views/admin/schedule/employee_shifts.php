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
                                <td><span class="badge" class="style-36083"><?= htmlspecialchars($a['shift_type_name'] ?? '') ?></span></td>
                                <td><?= htmlspecialchars($a['shift_date'] ?? '') ?></td>
                                <td><?= htmlspecialchars($a['shift_start_time'] ?? '') ?>-<?= htmlspecialchars($a['shift_end_time'] ?? '') ?></td>
                                <td><?= htmlspecialchars($a['status'] ?? '') ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary edit-assignment" data-id="<?= $a['id'] ?>"><i class="fas fa-edit"></i></button>
                                    <form method="POST" action="<?= BASE_URL ?>/admin/schedule/assignments/<?= $a['id'] ?>/delete" class="d-inline" data-aps-confirm="Delete this shift assignment?">
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
