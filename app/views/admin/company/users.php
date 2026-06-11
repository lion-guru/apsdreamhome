<?php $users = $users ?? []; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Company Users</h4>
    <a href="<?= BASE_URL ?>admin/company/users/create" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add User</a>
</div>
<div class="card aps-cp-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Department</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4"><i class="fas fa-users fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No company users found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?= htmlspecialchars($u['name'] ?? $u['full_name'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($u['email'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($u['phone'] ?? $u['mobile'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($u['department'] ?? $u['department_name'] ?? '-') ?></td>
                                <td><span class="badge bg-info"><?= htmlspecialchars($u['role'] ?? $u['user_type'] ?? '-') ?></span></td>
                                <td><?php $s = $u['status'] ?? 'active'; ?>
                                    <span class="badge bg-<?= $s === 'active' ? 'success' : ($s === 'inactive' ? 'secondary' : 'warning') ?>">
                                        <?= ucfirst($s) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= BASE_URL ?>admin/company/users/<?= $u['id'] ?? 0 ?>" class="btn btn-sm btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                                    <a href="<?= BASE_URL ?>admin/company/users/edit/<?= $u['id'] ?? 0 ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
