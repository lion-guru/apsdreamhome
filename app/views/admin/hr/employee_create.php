<?php
$page_title = $page_title ?? 'Add Employee';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-user-plus me-2"></i>Add Employee</h4>
    <a href="<?= BASE_URL ?>/admin/hr/users" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body aps-cp-card-body">
        <form method="POST" action="<?= BASE_URL ?>/admin/hr/users/store">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? $_SESSION['csrf_token'] ?? ''; ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Department</label>
                    <input type="text" name="department" class="form-control" value="General">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Designation</label>
                    <input type="text" name="designation" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Salary (₹)</label>
                    <input type="number" name="salary" class="form-control" step="0.01">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Joining Date</label>
                    <input type="date" name="join_date" class="form-control" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Password</label>
                    <input type="text" name="password" class="form-control" value="employee@123">
                    <div class="form-text">Default: employee@123</div>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Create Employee</button>
                <a href="<?= BASE_URL ?>/admin/hr/users" class="btn btn-secondary ms-2">Cancel</a>
            </div>
        </form>
    </div>
</div>
