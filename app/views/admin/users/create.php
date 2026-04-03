<!-- Page Header -->
<div class="mb-4">
    <a href="<?php echo BASE_URL; ?>/admin/users" class="text-decoration-none text-muted">
        <i class="fas fa-arrow-left me-2"></i>Back to Users
    </a>
    <h1 class="h3 mt-2 mb-1">Add New User</h1>
    <p class="text-muted">Create a new user account</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="<?php echo BASE_URL; ?>/admin/users">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">

                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label fw-semibold">Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone">
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label fw-semibold">Role</label>
                        <select class="form-select" id="role" name="role">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                            <option value="employee">Employee</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="status" class="form-label fw-semibold">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Create User</button>
                        <a href="<?php echo BASE_URL; ?>/admin/users" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
