<?php
$page_title = $page_title ?? 'Employee Profile';
$e = $employee ?? [];
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-id-card me-2"></i>Employee Profile</h4>
    <div>
        <a href="<?= BASE_URL ?>/admin/hr/users/edit/<?= $e['id'] ?? 0 ?>" class="btn btn-primary"><i class="fas fa-edit me-2"></i>Edit</a>
        <a href="<?= BASE_URL ?>/admin/hr/users" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-4">
                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3" class="style-93790">
                    <?= strtoupper(substr($e['name'] ?? '?', 0, 1)) ?>
                </div>
                <h5 class="mb-1"><?= htmlspecialchars($e['name'] ?? '') ?></h5>
                <div class="text-muted small"><?= htmlspecialchars($e['designation'] ?? '') ?></div>
                <div class="mt-2">
                    <?php if (($e['status'] ?? '') === 'active'): ?>
                        <span class="badge bg-success">Active</span>
                    <?php else: ?>
                        <span class="badge bg-danger"><?= htmlspecialchars($e['status'] ?? '') ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-body aps-cp-card-body">
                <h6 class="fw-bold mb-3"><i class="fas fa-info-circle me-2"></i>Details</h6>
                <table class="table table-sm mb-0">
                    <tr><td class="text-muted">Email</td><td><?= htmlspecialchars($e['email'] ?? '-') ?></td></tr>
                    <tr><td class="text-muted">Phone</td><td><?= htmlspecialchars($e['phone'] ?? '-') ?></td></tr>
                    <tr><td class="text-muted">Department</td><td><?= htmlspecialchars($e['department'] ?? '-') ?></td></tr>
                    <tr><td class="text-muted">Salary</td><td><?= ($e['salary'] ?? 0) ? '₹' . number_format($e['salary'], 2) : '-' ?></td></tr>
                    <tr><td class="text-muted">Joined</td><td><?= htmlspecialchars($e['join_date'] ?? '-') ?></td></tr>
                    <tr><td class="text-muted">User Since</td><td><?= htmlspecialchars($e['user_since'] ?? '-') ?></td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="fas fa-calendar-check me-2 text-success"></i>Recent Attendance</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light"><tr><th>Date</th><th>Status</th><th>Check In</th><th>Check Out</th></tr></thead>
                        <tbody>
                            <?php if (empty($attendance ?? [])): ?>
                                <tr><td colspan="4" class="text-center text-muted py-3">No records</td></tr>
                            <?php else: ?>
                                <?php foreach ($attendance as $a): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($a['attendance_date'] ?? '') ?></td>
                                        <td>
                                            <span class="badge bg-<?= ($a['attendance_status'] ?? '') === 'present' ? 'success' : (($a['attendance_status'] ?? '') === 'absent' ? 'danger' : (($a['attendance_status'] ?? '') === 'half_day' ? 'warning' : 'info')) ?>">
                                                <?= htmlspecialchars($a['attendance_status'] ?? '') ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($a['check_in_time'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($a['check_out_time'] ?? '-') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="fas fa-umbrella-beach me-2 text-warning"></i>Recent Leaves</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light"><tr><th>Type</th><th>From</th><th>To</th><th>Days</th><th>Status</th></tr></thead>
                        <tbody>
                            <?php if (empty($leaves ?? [])): ?>
                                <tr><td colspan="5" class="text-center text-muted py-3">No leaves</td></tr>
                            <?php else: ?>
                                <?php foreach ($leaves as $l): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($l['leave_type_name'] ?? $l['leave_type'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($l['start_date'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($l['end_date'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($l['total_days'] ?? '') ?></td>
                                        <td>
                                            <span class="badge bg-<?= ($l['status'] ?? '') === 'approved' ? 'success' : (($l['status'] ?? '') === 'rejected' ? 'danger' : 'warning') ?>">
                                                <?= htmlspecialchars($l['status'] ?? '') ?>
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
    </div>
</div>
