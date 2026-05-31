<?php $pageTitle = 'Edit Associate'; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><i class="fas fa-home me-1"></i>Home</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/associate">users</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/associate/show/<?= $associate['id'] ?? '' ?>"><?= htmlspecialchars($associate['name'] ?? '') ?></a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-user-edit me-2"></i>Edit Associate</h4>
    </div>
    <?php if (!empty($associate)): ?>
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
                    <?php endif; ?>
                    <form method="POST" action="<?= BASE_URL ?>/associate/edit/<?= $associate['id'] ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($_POST['name'] ?? $associate['name'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? $associate['email'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone <span class="text-danger">*</span></label>
                                <input type="tel" name="phone" class="form-control" required value="<?= htmlspecialchars($_POST['phone'] ?? $associate['phone'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Password <small class="text-muted">(leave blank to keep current)</small></label>
                                <input type="password" name="password" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($_POST['city'] ?? $associate['city'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Commission Rate (%)</label>
                                <input type="number" name="commission_rate" class="form-control" step="0.01" value="<?= htmlspecialchars($_POST['commission_rate'] ?? $associate['commission_rate'] ?? '5.00') ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($_POST['address'] ?? $associate['address'] ?? '') ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="active" <?= ($associate['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                                    <option value="inactive" <?= ($associate['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Update Associate</button>
                            <a href="<?= BASE_URL ?>/associate/show/<?= $associate['id'] ?>" class="btn btn-secondary ms-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
            <h5 class="text-muted">Associate Not Found</h5>
            <a href="<?= BASE_URL ?>/associate" class="btn btn-primary mt-2">Back to users</a>
        </div>
    </div>
    <?php endif; ?>
</div>
