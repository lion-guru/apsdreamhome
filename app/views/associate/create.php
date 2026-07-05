<?php $pageTitle = __('assoc_create_title'); ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><i class="fas fa-home me-1"></i><?= __('assoc_home') ?></a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/associate"><?= __('assoc_users') ?></a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= __('assoc_create_title') ?></li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-user-plus me-2"></i><?= __('assoc_create_title') ?></h4>
    </div>
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
                    <?php endif; ?>
                    <form method="POST" action="<?= BASE_URL ?>/associate/create">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label"><?= __('assoc_create_full_name') ?> <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= __('assoc_create_email') ?> <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= __('assoc_create_phone') ?> <span class="text-danger">*</span></label>
                                <input type="tel" name="phone" class="form-control" required value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= __('assoc_create_password') ?> <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= __('assoc_create_state') ?></label>
                                <select name="state_id" class="form-select"><option value=""><?= __('assoc_create_select_state') ?></option></select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= __('assoc_create_city') ?></label>
                                <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($_POST['city'] ?? '') ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label"><?= __('assoc_create_address') ?></label>
                                <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= __('assoc_create_commission_rate') ?></label>
                                <input type="number" name="commission_rate" class="form-control" step="0.01" value="<?= htmlspecialchars($_POST['commission_rate'] ?? '5.00') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= __('assoc_create_status') ?></label>
                                <select name="status" class="form-select">
                                    <option value="active"><?= __('assoc_create_active') ?></option>
                                    <option value="inactive"><?= __('assoc_create_inactive') ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i><?= __('assoc_create_save') ?></button>
                            <a href="<?= BASE_URL ?>/associate" class="btn btn-secondary ms-2"><?= __('assoc_create_cancel') ?></a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
