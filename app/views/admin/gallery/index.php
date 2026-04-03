<!-- Gallery Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Gallery Management</h1>
        <p class="text-muted mb-0">Manage your photo gallery</p>
    </div>
    <a href="<?php echo BASE_URL; ?>/admin/gallery/create" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Add Image
    </a>
</div>

<?php if ($success): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Gallery Grid -->
<?php if (!empty($images)): ?>
<div class="row g-4">
    <?php foreach ($images as $image): ?>
    <div class="col-md-4 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="position-relative" style="height: 200px; overflow: hidden;">
                <?php if (!empty($image['image_path'])): ?>
                <img src="<?php echo BASE_URL . '/' . $image['image_path']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($image['caption'] ?? ''); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                <div class="bg-light d-flex align-items-center justify-content-center h-100">
                    <i class="fas fa-image fa-3x text-muted"></i>
                </div>
                <?php endif; ?>
                <span class="badge bg-<?php echo ($image['status'] ?? 'active') === 'active' ? 'success' : 'secondary'; ?> position-absolute top-0 end-0 m-2">
                    <?php echo ucfirst($image['status'] ?? 'active'); ?>
                </span>
            </div>
            <div class="card-body">
                <span class="badge bg-primary mb-2"><?php echo ucfirst($image['category'] ?? 'general'); ?></span>
                <p class="card-text small"><?php echo htmlspecialchars($image['caption'] ?? 'No caption'); ?></p>
            </div>
            <div class="card-footer bg-transparent border-0">
                <div class="btn-group w-100">
                    <a href="<?php echo BASE_URL; ?>/admin/gallery/<?php echo $image['id']; ?>/edit" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="<?php echo BASE_URL; ?>/admin/gallery/<?php echo $image['id']; ?>/destroy" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this image?');">
                        <i class="fas fa-trash"></i> Delete
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="text-center py-5">
    <i class="fas fa-images fa-4x text-muted mb-3"></i>
    <h5 class="text-muted">No images in gallery</h5>
    <p class="text-muted">Start by adding your first image</p>
    <a href="<?php echo BASE_URL; ?>/admin/gallery/create" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Add First Image
    </a>
</div>
<?php endif; ?>
