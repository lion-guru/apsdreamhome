<?php
$categories = $categories ?? [];
$current_category = $current_category ?? '';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Gallery Management</h1>
        <p class="text-muted mb-0">Manage your photo gallery (<?= count($images) ?> images)</p>
    </div>
    <a href="<?= BASE_URL ?>/admin/gallery/create" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Add Image
    </a>
</div>

<?php if (!empty($success)): ?>
<div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?= $success ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
<div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle me-2"></i><?= $error ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<!-- Category Filter -->
<div class="mb-4">
    <a href="<?= BASE_URL ?>/admin/gallery" class="btn btn-sm <?= empty($current_category) ? 'btn-primary' : 'btn-outline-primary' ?> me-1">All</a>
    <?php foreach ($categories as $cat): ?>
    <a href="<?= BASE_URL ?>/admin/gallery?category=<?= urlencode($cat['category']) ?>" class="btn btn-sm <?= $current_category === $cat['category'] ? 'btn-primary' : 'btn-outline-primary' ?> me-1">
        <?= ucfirst($cat['category']) ?> <span class="badge bg-light text-dark ms-1"><?= $cat['cnt'] ?></span>
    </a>
    <?php endforeach; ?>
</div>

<?php if (!empty($images)): ?>
<div class="row g-4">
    <?php foreach ($images as $img): ?>
    <div class="col-md-4 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="position-relative" class="style-16984">
                <?php if (!empty($img['image_path'])): ?>
                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($img['image_path']) ?>" alt="<?= htmlspecialchars($img['title'] ?? $img['caption'] ?? '') ?>" class="style-83369" loading="lazy">
                <?php else: ?>
                <div class="bg-light d-flex align-items-center justify-content-center h-100"><i class="fas fa-image fa-3x text-muted"></i></div>
                <?php endif; ?>
                <span class="badge bg-<?= $img['status'] === 'active' ? 'success' : 'secondary' ?> position-absolute top-0 end-0 m-2"><?= ucfirst($img['status']) ?></span>
                <?php if (!empty($img['category'])): ?>
                <span class="badge bg-dark position-absolute top-0 start-0 m-2"><?= ucfirst($img['category']) ?></span>
                <?php endif; ?>
            </div>
            <div class="card-body p-3">
                <?php if (!empty($img['title'])): ?>
                <h6 class="card-title mb-1"><?= htmlspecialchars($img['title']) ?></h6>
                <?php endif; ?>
                <p class="card-text small text-muted mb-0"><?= htmlspecialchars($img['caption'] ?? 'No caption') ?></p>
                <?php if (($img['sort_order'] ?? 0) > 0): ?>
                <small class="text-muted">Order: <?= (int)$img['sort_order'] ?></small>
                <?php endif; ?>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0">
                <div class="btn-group w-100">
                    <a href="<?= BASE_URL ?>/admin/gallery/<?= $img['id'] ?>/edit" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i> Edit</a>
                    <a href="<?= BASE_URL ?>/admin/gallery/<?= $img['id'] ?>/destroy" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this image?');"><i class="fas fa-trash"></i> Delete</a>
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
    <a href="<?= BASE_URL ?>/admin/gallery/create" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add First Image</a>
</div>
<?php endif; ?>
