<?php $isEdit = !empty($member); ?>
<?php if (isset($_SESSION['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas <?php echo $isEdit ? 'fa-edit' : 'fa-plus-circle'; ?> me-2"></i><?php echo $isEdit ? 'Edit Team Member' : 'Add Team Member'; ?></h5>
        <a href="<?php echo BASE_URL; ?>/admin/team" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="card-body">
        <form method="POST" action="<?php echo BASE_URL; ?>/admin/team/<?php echo $isEdit ? 'update/' . $member['id'] : 'store'; ?>" enctype="multipart/form-data" class="needs-validation">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($member['name'] ?? ''); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Position / Title <span class="text-danger">*</span></label>
                    <input type="text" name="position" class="form-control" value="<?php echo htmlspecialchars($member['position'] ?? ''); ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Bio / Description</label>
                    <textarea name="bio" class="form-control" rows="4"><?php echo htmlspecialchars($member['bio'] ?? ''); ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($member['email'] ?? ''); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($member['phone'] ?? ''); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">LinkedIn URL</label>
                    <input type="url" name="linkedin" class="form-control" value="<?php echo htmlspecialchars($member['linkedin'] ?? ''); ?>" placeholder="https://linkedin.com/in/...">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Expertise / Department</label>
                    <input type="text" name="expertise" class="form-control" value="<?php echo htmlspecialchars($member['expertise'] ?? ''); ?>" placeholder="e.g. Sales, Legal, Marketing">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Experience</label>
                    <input type="text" name="experience" class="form-control" value="<?php echo htmlspecialchars($member['experience'] ?? ''); ?>" placeholder="e.g. 10+ years">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="<?php echo (int)($member['sort_order'] ?? 0); ?>" min="0">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" <?php echo (($member['status'] ?? 'active') === 'active') ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo (($member['status'] ?? '') === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Photo</label>
                    <?php if ($isEdit && !empty($member['photo'])): ?>
                    <div class="mb-2">
                        <img src="<?= BASE_URL ?>/assets/images/<?php echo htmlspecialchars($member['photo']); ?>" alt="" style="width:80px;height:80px;object-fit:cover;border-radius:8px;">
                    </div>
                    <?php endif; ?>
                    <input type="file" name="photo" class="form-control" accept="image/jpeg,image/png,image/webp">
                    <small class="text-muted">Allowed: JPG, PNG, WEBP. Max 2MB.</small>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save me-1"></i><?php echo $isEdit ? 'Update' : 'Save'; ?> Member
                </button>
                <a href="<?php echo BASE_URL; ?>/admin/team" class="btn btn-outline-secondary ms-2">Cancel</a>
            </div>
        </form>
    </div>
</div>
