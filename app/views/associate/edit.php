<?php $pageTitle = __('assoc_edit_title'); ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><i class="fas fa-home me-1"></i><?= __('assoc_home') ?></a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/associate/manage/list"><?= __('assoc_users') ?></a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/associate/manage/show/<?= $associate['id'] ?? '' ?>"><?= htmlspecialchars($associate['name'] ?? '') ?></a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= __('assoc_edit_title') ?></li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-user-edit me-2"></i><?= __('assoc_edit_title') ?></h4>
    </div>
    <?php if (!empty($associate)): ?>
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
                    <?php endif; ?>
                    <form method="POST" action="<?= BASE_URL ?>/admin/associate/manage/update/<?= $associate['id'] ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label"><?= __('assoc_edit_full_name') ?> <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($_POST['name'] ?? $associate['name'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= __('assoc_edit_email') ?> <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? $associate['email'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= __('assoc_edit_phone') ?> <span class="text-danger">*</span></label>
                                <input type="tel" name="phone" class="form-control" required value="<?= htmlspecialchars($_POST['phone'] ?? $associate['phone'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= __('assoc_edit_password') ?> <small class="text-muted"><?= __('assoc_edit_password_help') ?></small></label>
                                <input type="password" name="password" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= __('assoc_edit_city') ?></label>
                                <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($_POST['city'] ?? $associate['city'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= __('assoc_edit_commission_rate') ?></label>
                                <input type="number" name="commission_rate" class="form-control" step="0.01" value="<?= htmlspecialchars($_POST['commission_rate'] ?? $associate['commission_rate'] ?? '5.00') ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label"><?= __('assoc_edit_address') ?></label>
                                <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($_POST['address'] ?? $associate['address'] ?? '') ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= __('assoc_edit_status') ?></label>
                                <select name="status" class="form-select">
                                    <option value="active" <?= ($associate['status'] ?? '') === 'active' ? 'selected' : '' ?>><?= __('assoc_edit_active') ?></option>
                                    <option value="inactive" <?= ($associate['status'] ?? '') === 'inactive' ? 'selected' : '' ?>><?= __('assoc_edit_inactive') ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i><?= __('assoc_edit_update') ?></button>
                            <a href="<?= BASE_URL ?>/admin/associate/manage/show/<?= $associate['id'] ?>" class="btn btn-secondary ms-2"><?= __('assoc_edit_cancel') ?></a>
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
            <h5 class="text-muted"><?= __('assoc_edit_not_found') ?></h5>
            <a href="<?= BASE_URL ?>/admin/associate/manage/list" class="btn btn-primary mt-2"><?= __('assoc_edit_back') ?></a>
        </div>
    </div>
    <?php endif; ?>
</div>
