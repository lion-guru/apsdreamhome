<?php $roles = $roles ?? ['admin', 'manager', 'employee', 'agent']; $departments = $departments ?? []; $designations = $designations ?? []; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><?= __('hr_add_employee') ?></h4>
    <a href="<?= BASE_URL ?>admin/hrm/users" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> <?= __('admin_btn_back') ?></a>
</div>
<div class="card aps-cp-card">
    <div class="card-body aps-cp-card-body">
        <form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label"><?= __('hr_full_name') ?> <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label"><?= __('hr_email') ?> <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Password <span class="text-danger">*</span></label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label"><?= __('hr_designation') ?></label>
                    <select name="role" class="form-select">
                        <?php foreach ($roles as $r): ?>
                            <option value="<?= $r ?>"><?= ucfirst($r) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label"><?= __('hr_department') ?></label>
                    <select name="department_id" class="form-select">
                        <option value=""><?= __('hr_department') ?></option>
                        <?php foreach ($departments as $d): ?>
                            <option value="<?= $d['id'] ?? $d ?>"><?= $d['name'] ?? $d ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label"><?= __('hr_designation') ?></label>
                    <select name="designation_id" class="form-select">
                        <option value=""><?= __('hr_designation') ?></option>
                        <?php foreach ($designations as $d): ?>
                            <option value="<?= $d['id'] ?? $d ?>"><?= $d['name'] ?? $d ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label"><?= __('hr_joining_date') ?></label>
                    <input type="date" name="joining_date" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label"><?= __('hr_employee_status') ?></label>
                    <select name="status" class="form-select">
                        <option value="active"><?= __('hr_active') ?></option>
                        <option value="inactive"><?= __('hr_inactive') ?></option>
                        <option value="probation"><?= __('hr_probation') ?></option>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= __('hr_add_employee') ?></button>
                <a href="<?= BASE_URL ?>admin/hrm/users" class="btn btn-secondary"><?= __('admin_btn_back') ?></a>
            </div>
        </form>
    </div>
</div>
