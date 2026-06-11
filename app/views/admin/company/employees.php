<?php

$page_title = 'Company users';
$users = $users ?? [];
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-2"><i class="fas fa-users me-2"></i>Company users</h1>
            <p class="text-muted">Manage users linked to your organization</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
            <i class="fas fa-plus me-1"></i>Add Employee
        </button>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= htmlspecialchars($_SESSION['success'] ?? '') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success']); endif; ?>

    <div class="card aps-cp-card">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Employee List</h5>
        </div>
        <div class="card-body aps-cp-card-body">
            <?php if (empty($users)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-user-plus fa-4x text-muted mb-3"></i>
                    <p class="text-muted">No users added yet. Click "Add Employee" to get started.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Position</th>
                                <th>Salary</th>
                                <th>Join Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $emp): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($emp['user_name'] ?? 'N/A') ?></strong></td>
                                <td><?= htmlspecialchars($emp['user_email'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($emp['user_phone'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($emp['position'] ?? 'N/A') ?></td>
                                <td>₹<?= number_format(floatval($emp['salary'] ?? 0), 2) ?></td>
                                <td><?= htmlspecialchars($emp['join_date'] ?? 'N/A') ?></td>
                                <td>
                                    <span class="badge bg-<?= ($emp['status'] ?? '') === 'active' ? 'success' : 'secondary' ?>">
                                        <?= ucfirst(htmlspecialchars($emp['status'] ?? 'unknown')) ?>
                                    </span>
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

<!-- Add Employee Modal -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/admin/company/users/add">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Add Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">User <span class="text-danger">*</span></label>
                        <select name="user_id" class="form-select" required>
                            <option value="">Select User</option>
                            <?php
                            $users = \App\Core\Database\Database::getInstance()->fetchAll("SELECT id, name, email FROM users WHERE role = 'employee' ORDER BY name");
                            foreach (($users ?: []) as $u):
                            ?>
                            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?> (<?= htmlspecialchars($u['email']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Position <span class="text-danger">*</span></label>
                        <input type="text" name="position" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Salary</label>
                        <input type="number" step="0.01" name="salary" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Join Date</label>
                        <input type="date" name="join_date" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Add</button>
                </div>
            </form>
        </div>
    </div>
</div>
