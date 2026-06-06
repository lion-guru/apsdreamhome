<?php $roles = $roles ?? ['admin', 'manager', 'employee', 'agent']; $departments = $departments ?? []; $designations = $designations ?? []; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Add HRM User</h4>
    <a href="<?= BASE_URL ?>admin/hrm/users" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
</div>
<div class="card">
    <div class="card-body">
        <form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Password <span class="text-danger">*</span></label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select">
                        <?php foreach ($roles as $r): ?>
                            <option value="<?= $r ?>"><?= ucfirst($r) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Department</label>
                    <select name="department_id" class="form-select">
                        <option value="">Select Department</option>
                        <?php foreach ($departments as $d): ?>
                            <option value="<?= $d['id'] ?? $d ?>"><?= $d['name'] ?? $d ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Designation</label>
                    <select name="designation_id" class="form-select">
                        <option value="">Select Designation</option>
                        <?php foreach ($designations as $d): ?>
                            <option value="<?= $d['id'] ?? $d ?>"><?= $d['name'] ?? $d ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Joining Date</label>
                    <input type="date" name="joining_date" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="probation">Probation</option>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Create User</button>
                <a href="<?= BASE_URL ?>admin/hrm/users" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
