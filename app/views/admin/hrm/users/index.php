<?php $users = $users ?? []; $roles = $roles ?? []; $departments = $departments ?? []; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><?= __('hr_employees') ?></h4>
    <a href="<?= BASE_URL ?>admin/hrm/users/create" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> <?= __('hr_add_employee') ?></a>
</div>
<div class="card mb-3">
    <div class="card-body aps-cp-card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label"><?= __('hr_designation') ?></label>
                <select name="role" class="form-select">
                    <option value=""><?= __('hr_filter_employees') ?></option>
                    <?php foreach ($roles as $r): ?>
                        <option value="<?= $r ?>" <?= ($_GET['role'] ?? '') === $r ? 'selected' : '' ?>><?= ucfirst($r) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label"><?= __('hr_employee_status') ?></label>
                <select name="status" class="form-select">
                    <option value=""><?= __('hr_filter_employees') ?></option>
                    <option value="active" <?= ($_GET['status'] ?? '') === 'active' ? 'selected' : '' ?>><?= __('hr_active') ?></option>
                    <option value="inactive" <?= ($_GET['status'] ?? '') === 'inactive' ? 'selected' : '' ?>><?= __('hr_inactive') ?></option>
                    <option value="suspended" <?= ($_GET['status'] ?? '') === 'suspended' ? 'selected' : '' ?>><?= __('hr_inactive') ?></option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label"><?= __('hr_department') ?></label>
                <select name="department" class="form-select">
                    <option value=""><?= __('hr_departments') ?></option>
                    <?php foreach ($departments as $d): ?>
                        <option value="<?= $d['id'] ?? $d ?>" <?= (($_GET['department'] ?? '') == ($d['id'] ?? $d)) ? 'selected' : '' ?>><?= $d['name'] ?? $d ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-outline-primary w-100"><i class="fas fa-filter"></i> <?= __('hr_filter_employees') ?></button>
            </div>
        </form>
    </div>
</div>
<div class="card aps-cp-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th><?= __('hr_employee_name') ?></th>
                        <th><?= __('hr_email') ?></th>
                        <th><?= __('hr_designation') ?></th>
                        <th><?= __('hr_department') ?></th>
                        <th><?= __('hr_employee_status') ?></th>
                        <th><?= __('hr_check_in') ?></th>
                        <th><?= __('hr_actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4"><i class="fas fa-users fa-2x d-block mb-2 text-muted" aria-hidden="true"></i><?= __('hr_no_employees') ?></td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?= htmlspecialchars($u['name'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($u['email'] ?? '-') ?></td>
                                <td><span class="badge bg-info"><?= htmlspecialchars($u['role'] ?? $u['user_type'] ?? '-') ?></span></td>
                                <td><?= htmlspecialchars($u['department'] ?? $u['department_name'] ?? '-') ?></td>
                                <td><?php $s = $u['status'] ?? 'active'; ?>
                                    <span class="badge bg-<?= $s === 'active' ? 'success' : ($s === 'inactive' ? 'secondary' : 'warning') ?>">
                                        <?= ucfirst($s) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($u['last_login'] ?? $u['last_activity'] ?? 'Never') ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>admin/hrm/users/<?= $u['id'] ?? 0 ?>" class="btn btn-sm btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                                    <a href="<?= BASE_URL ?>admin/hrm/users/edit/<?= $u['id'] ?? 0 ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
