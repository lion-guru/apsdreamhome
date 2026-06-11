<?php
$page_title = $page_title ?? 'Leave Applications';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-umbrella-beach me-2"></i>Leave Applications</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#applyModal"><i class="fas fa-plus me-2"></i>Apply Leave</button>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body aps-cp-card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">Status</label>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">All</option>
                    <option value="pending" <?= ($status_filter ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="approved" <?= ($status_filter ?? '') === 'approved' ? 'selected' : '' ?>>Approved</option>
                    <option value="rejected" <?= ($status_filter ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">&nbsp;</label>
                <a href="<?= BASE_URL ?>/admin/hr/leave-types" class="btn btn-outline-info w-100"><i class="fas fa-tags me-2"></i>Leave Types</a>
            </div>
            <div class="col-md-3">
                <label class="form-label small">&nbsp;</label>
                <a href="<?= BASE_URL ?>/admin/hr/leave-balances" class="btn btn-outline-warning w-100"><i class="fas fa-balance-scale me-2"></i>Balances</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Employee</th><th>Type</th><th>From</th><th>To</th><th>Days</th><th>Reason</th><th>Status</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($leaves)): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No leave applications</td></tr>
                    <?php else: ?>
                        <?php foreach ($leaves as $l): ?>
                            <tr>
                                <td class="fw-medium"><?= htmlspecialchars($l['employee_name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($l['leave_type_name'] ?? $l['leave_type'] ?? '') ?></td>
                                <td><?= htmlspecialchars($l['start_date'] ?? '') ?></td>
                                <td><?= htmlspecialchars($l['end_date'] ?? '') ?></td>
                                <td><?= $l['total_days'] ?? '' ?></td>
                                <td><span title="<?= htmlspecialchars($l['reason'] ?? '') ?>"><?= mb_strimwidth(htmlspecialchars($l['reason'] ?? ''), 0, 30, '...') ?></span></td>
                                <td>
                                    <span class="badge bg-<?= ($l['status'] ?? '') === 'approved' ? 'success' : (($l['status'] ?? '') === 'rejected' ? 'danger' : 'warning') ?>">
                                        <?= htmlspecialchars($l['status'] ?? '') ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <?php if (($l['status'] ?? '') === 'pending'): ?>
                                        <a href="<?= BASE_URL ?>/admin/hr/leaves/approve/<?= $l['id'] ?>" class="btn btn-sm btn-outline-success" title="Approve" onclick="return confirm('Approve this leave?')"><i class="fas fa-check"></i></a>
                                        <a href="<?= BASE_URL ?>/admin/hr/leaves/reject/<?= $l['id'] ?>" class="btn btn-sm btn-outline-danger" title="Reject" onclick="return confirm('Reject this leave?')"><i class="fas fa-times"></i></a>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
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
                        <a class="page-link" href="?status=<?= urlencode($status_filter ?? '') ?>&page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul></nav>
        </div>
    <?php endif; ?>
</div>

<!-- Apply Leave Modal -->
<div class="modal fade" id="applyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/admin/hr/leaves/store">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? $_SESSION['csrf_token'] ?? ''; ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Apply Leave</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Employee</label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">Select</option>
                            <?php if (isset($users)): ?>
                                <?php foreach ($users as $emp): ?>
                                    <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['name'] ?? '') ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Leave Type</label>
                        <select name="leave_type_id" class="form-select">
                            <option value="0">General</option>
                            <?php if (isset($leave_types)): ?>
                                <?php foreach ($leave_types as $lt): ?>
                                    <option value="<?= $lt['id'] ?>"><?= htmlspecialchars($lt['name'] ?? '') ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6"><label class="form-label">Start Date</label><input type="date" name="start_date" class="form-control" required></div>
                        <div class="col-6"><label class="form-label">End Date</label><input type="date" name="end_date" class="form-control" required></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason</label>
                        <textarea name="reason" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-2"></i>Submit</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Pass users and leave_types for modal - fetch from controller if needed
try {
    $db = \App\Core\Database\Database::getInstance();
    $GLOBALS['_hr_employees'] = $GLOBALS['_hr_employees'] ?? $db->fetchAll("SELECT e.id, u.name FROM users e JOIN users u ON e.user_id=u.id WHERE e.status='active' ORDER BY u.name");
    $GLOBALS['_hr_leave_types'] = $GLOBALS['_hr_leave_types'] ?? $db->fetchAll("SELECT id, name FROM leave_types WHERE status='active' ORDER BY name");
} catch (\Exception $e) { $GLOBALS['_hr_employees'] = []; $GLOBALS['_hr_leave_types'] = []; }
$users = $GLOBALS['_hr_employees'];
$leave_types = $GLOBALS['_hr_leave_types'];
?>
