<!-- Department Form - Create/Edit -->
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-building mr-2"></i> <?= $page_title ?? 'Department Form' ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="/admin/departments">Departments</a></li>
                        <li class="breadcrumb-item active"><?= $department ? 'Edit' : 'Create' ?></li>
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
                    <form method="POST" action="<?= $department ? '/admin/departments/' . $department['id'] . '/update' : '/admin/departments/store' ?>">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Department Name *</label>
                                    <input type="text" name="name" class="form-control" required
                                           value="<?= htmlspecialchars($department['name'] ?? '') ?>"
                                           placeholder="e.g. Finance & Accounts">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Code *</label>
                                    <input type="text" name="code" class="form-control" required
                                           value="<?= htmlspecialchars($department['code'] ?? '') ?>"
                                           placeholder="e.g. FIN" maxlength="20"
                                           style="text-transform:uppercase">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" class="form-control">
                                        <option value="active" <?= ($department['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                                        <option value="inactive" <?= ($department['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="3"
                                      placeholder="Brief description of department responsibilities"><?= htmlspecialchars($department['description'] ?? '') ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Department Head</label>
                                    <select name="head_user_id" class="form-control">
                                        <option value="">— Select Head —</option>
                                        <?php foreach ($users as $u): ?>
                                            <option value="<?= $u['id'] ?>" <?= ($department['head_user_id'] ?? '') == $u['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($u['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Parent Department</label>
                                    <select name="parent_dept_id" class="form-control">
                                        <option value="">— None (Root) —</option>
                                        <?php foreach ($departments as $d): ?>
                                            <?php if (!$department || $d['id'] != $department['id']): ?>
                                                <option value="<?= $d['id'] ?>" <?= ($department['parent_dept_id'] ?? '') == $d['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($d['code'] . ' — ' . $d['name']) ?>
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Annual Budget (₹)</label>
                                    <input type="number" name="dept_budget" class="form-control" min="0" step="100000"
                                           value="<?= $department['dept_budget'] ?? 0 ?>">
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> <?= $department ? 'Update Department' : 'Create Department' ?>
                            </button>
                            <a href="/admin/departments" class="btn btn-secondary ml-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
