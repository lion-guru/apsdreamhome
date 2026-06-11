<?php
$page_title = $page_title ?? 'Edit Employee';
$e = $employee ?? [];
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-user-edit me-2"></i>Edit Employee</h4>
    <a href="<?= BASE_URL ?>/admin/hr/users" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body aps-cp-card-body">
        <?php if (!$e): ?>
            <div class="alert alert-danger">Employee not found</div>
        <?php else: ?>
        <form method="POST" action="<?= BASE_URL ?>/admin/hr/users/update/<?= $e['id'] ?>">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? $_SESSION['csrf_token'] ?? ''; ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($e['name'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($e['email'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($e['phone'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Department</label>
                    <input type="text" name="department" class="form-control" value="<?= htmlspecialchars($e['department'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Designation</label>
                    <input type="text" name="designation" class="form-control" value="<?= htmlspecialchars($e['designation'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Salary (₹)</label>
                    <input type="number" name="salary" class="form-control" step="0.01" value="<?= htmlspecialchars($e['salary'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Joining Date</label>
                    <input type="date" name="join_date" class="form-control" value="<?= htmlspecialchars($e['join_date'] ?? date('Y-m-d')) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" <?= ($e['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= ($e['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        <option value="deleted" <?= ($e['status'] ?? '') === 'deleted' ? 'selected' : '' ?>>Deleted</option>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update Employee</button>
                <a href="<?= BASE_URL ?>/admin/hr/users" class="btn btn-secondary ms-2">Cancel</a>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>
