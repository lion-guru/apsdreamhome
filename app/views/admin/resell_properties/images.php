<?php
$p = $property ?? [];
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Property Images</h1>
        <p class="text-muted mb-0">Manage images for this property</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/admin/resell-properties/edit/<?= $id ?>" class="btn btn-outline-warning btn-sm"><i class="fas fa-edit me-1"></i>Edit</a>
        <a href="<?= BASE_URL ?>/admin/resell-properties" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="card aps-cp-card mb-4">
    <div class="card-header aps-cp-card-header">
        <span><i class="fas fa-upload me-2"></i>Upload New Image</span>
    </div>
    <div class="card-body aps-cp-card-body">
        <form method="POST" action="<?= BASE_URL ?>/admin/resell-properties/images/<?= $id ?>" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <div class="mb-3">
                <label class="form-label small fw-semibold">Select Image</label>
                <input type="file" class="form-control" name="property_image" accept="image/*">
                <div class="form-text">Supported formats: JPG, PNG, WebP. Max 5MB.</div>
            </div>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-upload me-1"></i>Upload Image</button>
        </form>
    </div>
</div>

<div class="card aps-cp-card">
    <div class="card-header aps-cp-card-header">
        <span><i class="fas fa-images me-2"></i>Current Images</span>
    </div>
    <div class="card-body aps-cp-card-body">
        <?php
        $image = $p['image'] ?? '';
        $images = [];
        if (!empty($image)) {
            if (strpos($image, '[') === 0) {
                $images = json_decode($image, true) ?: [];
            } else {
                $images = array_filter(explode(',', $image));
            }
        }
        ?>
        <?php if (empty($images)): ?>
            <div class="text-center py-5">
                <i class="fas fa-image fa-3x mb-3" class="style-39608"></i>
                <h5 class="text-muted">No images uploaded</h5>
                <p class="text-muted mb-0">Upload property images using the form above.</p>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($images as $idx => $img): ?>
                    <?php $src = (strpos(trim($img), 'http') === 0) ? trim($img) : BASE_URL . trim($img); ?>
                    <div class="col-md-3">
                        <div class="card border position-relative" class="style-94398">
                            <img src="<?= htmlspecialchars($src ?? '') ?>" alt="Property Image <?= $idx + 1 ?>" class="card-img-top" class="style-58348" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                            <div class="d-none align-items-center justify-content-center bg-light" class="style-59965"><i class="fas fa-broken-image fa-2x text-muted"></i></div>
                            <div class="card-body p-2 text-center d-flex justify-content-between align-items-center">
                                <small class="text-muted">Image #<?= $idx + 1 ?></small>
                                <form method="POST" action="<?= BASE_URL ?>/admin/resell-properties/images/<?= $id ?>/delete" class="d-inline" onsubmit="return confirm('Remove this image?')">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="image_index" value="<?= $idx ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
