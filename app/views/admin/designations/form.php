<!-- Designation Form - Create/Edit -->
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-user-tag mr-2"></i> <?= $page_title ?? 'Designation Form' ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/designations">Designations</a></li>
                        <li class="breadcrumb-item active"><?= $designation ? 'Edit' : 'Create' ?></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <?php if (!empty($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    <?= $_SESSION['error'] ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="<?= $designation ? '/admin/designations/' . $designation['id'] . '/update' : '/admin/designations/store' ?>">
    <?php echo CSRFProtection::csrfField(); ?>
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label>Designation Name *</label>
                                    <input type="text" name="name" class="form-control" required
                                           value="<?= htmlspecialchars($designation['name'] ?? '') ?>"
                                           placeholder="e.g. Finance Manager">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Department *</label>
                                    <select name="department_id" class="form-control" required>
                                        <option value="">— Select Department —</option>
                                        <?php foreach ($departments as $d): ?>
                                            <option value="<?= $d['id'] ?>" <?= ($designation['department_id'] ?? '') == $d['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($d['code'] . ' — ' . $d['name'] ?? '') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Level *</label>
                                    <select name="level" class="form-control" required>
                                        <option value="1" <?= ($designation['level'] ?? 1) == 1 ? 'selected' : '' ?>>1 — Junior</option>
                                        <option value="2" <?= ($designation['level'] ?? 1) == 2 ? 'selected' : '' ?>>2 — Executive</option>
                                        <option value="3" <?= ($designation['level'] ?? 1) == 3 ? 'selected' : '' ?>>3 — Senior</option>
                                        <option value="4" <?= ($designation['level'] ?? 1) == 4 ? 'selected' : '' ?>>4 — Manager</option>
                                        <option value="5" <?= ($designation['level'] ?? 1) == 5 ? 'selected' : '' ?>>5 — Director</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Min Salary (?/month)</label>
                                    <input type="number" name="min_salary" class="form-control" min="0" step="1000"
                                           value="<?= $designation['min_salary'] ?? 0 ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Max Salary (?/month)</label>
                                    <input type="number" name="max_salary" class="form-control" min="0" step="1000"
                                           value="<?= $designation['max_salary'] ?? 0 ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" class="form-control">
                                        <option value="active" <?= ($designation['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                                        <option value="inactive" <?= ($designation['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Sub-Role (RBAC)</label>
                                    <input type="text" name="sub_role" class="form-control"
                                           value="<?= htmlspecialchars($designation['sub_role'] ?? '') ?>"
                                           placeholder="e.g. employee_finance_manager">
                                    <small class="text-muted">RBAC role string for permissions</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Dashboard View</label>
                                    <input type="text" name="dashboard_view" class="form-control"
                                           value="<?= htmlspecialchars($designation['dashboard_view'] ?? '') ?>"
                                           placeholder="e.g. employee/finance-dashboard">
                                    <small class="text-muted">Route for role-specific dashboard</small>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> <?= $designation ? 'Update Designation' : 'Create Designation' ?>
                            </button>
                            <a href="<?= BASE_URL ?>/admin/designations" class="btn btn-secondary ml-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
