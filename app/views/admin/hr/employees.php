<?php
$page_title = $page_title ?? 'users';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-users me-2"></i>users</h4>
    <a href="<?= BASE_URL ?>/admin/hr/users/create" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add Employee</a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body aps-cp-card-body">
        <form method="GET" class="row g-2 align-items-end">
    <?php echo CSRFProtection::csrfField(); ?>
            <div class="col-md-4">
                <label class="form-label small">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Name, email, phone..." value="<?= htmlspecialchars($search ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Department</label>
                <select name="department" class="form-select">
                    <option value="">All Departments</option>
                    <?php foreach ($departments ?? [] as $d): ?>
                        <option value="<?= htmlspecialchars($d['department'] ?? '') ?>" <?= ($department ?? '') === ($d['department'] ?? '') ? 'selected' : '' ?>><?= htmlspecialchars($d['department'] ?? '') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($status ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-2"></i>Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Name</th><th>Email</th><th>Phone</th><th>Department</th><th>Designation</th><th>Salary</th><th>Status</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4"><i class="fas fa-users fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No users found</td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $e): ?>
                            <tr>
                                <td><a href="<?= BASE_URL ?>/admin/hr/users/view/<?= $e['id'] ?>" class="text-decoration-none fw-medium"><?= htmlspecialchars($e['name'] ?? '') ?></a></td>
                                <td><?= htmlspecialchars($e['email'] ?? '') ?></td>
                                <td><?= htmlspecialchars($e['phone'] ?? '') ?></td>
                                <td><?= htmlspecialchars($e['department'] ?? '') ?></td>
                                <td><?= htmlspecialchars($e['designation'] ?? '') ?></td>
                                <td><?= $e['salary'] ? '₹' . number_format($e['salary'], 2) : '-' ?></td>
                                <td>
                                    <?php if (($e['status'] ?? '') === 'active'): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php elseif (($e['status'] ?? '') === 'inactive'): ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Deleted</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="<?= BASE_URL ?>/admin/hr/users/view/<?= $e['id'] ?>" class="btn btn-sm btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                                    <a href="<?= BASE_URL ?>/admin/hr/users/edit/<?= $e['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                    <a href="<?= BASE_URL ?>/admin/hr/users/delete/<?= $e['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Delete this employee?')"><i class="fas fa-trash"></i></a>
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
                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search ?? '') ?>&department=<?= urlencode($department ?? '') ?>&status=<?= urlencode($status ?? '') ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul></nav>
        </div>
    <?php endif; ?>
</div>
