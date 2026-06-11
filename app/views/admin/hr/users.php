<?php $employees = $employees ?? []; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">HR Users</h4>
    <a href="<?= BASE_URL ?>admin/hr/users/create" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add Employee</a>
</div>
<div class="card aps-cp-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Employee Name</th>
                        <th>Department</th>
                        <th>Designation</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($employees)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No employees found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($employees as $e): ?>
                            <tr>
                                <td><?= htmlspecialchars($e['name'] ?? $e['full_name'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($e['department'] ?? $e['department_name'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($e['designation'] ?? $e['job_title'] ?? '-') ?></td>
                                <td><?php $s = $e['status'] ?? 'active'; ?>
                                    <span class="badge bg-<?= $s === 'active' ? 'success' : ($s === 'inactive' ? 'secondary' : 'warning') ?>">
                                        <?= ucfirst($s) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= BASE_URL ?>admin/hr/users/<?= $e['id'] ?? 0 ?>" class="btn btn-sm btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                                    <a href="<?= BASE_URL ?>admin/hr/users/edit/<?= $e['id'] ?? 0 ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
