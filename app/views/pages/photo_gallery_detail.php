<div class="container mt-4">
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>">Home</a></li>
        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/photo-gallery">Gallery</a></li>
        <li class="breadcrumb-item active">Image Detail</li>
    </ol></nav>
    <div class="row">
        <div class="col-md-8">
            <img src="<?= BASE_URL ?>/assets/images/placeholder/property.svg" class="img-fluid" alt="<?php echo htmlspecialchars($image['caption'] ?? ''); ?>">
        </div>
        <div class="col-md-4">
            <h3><?php echo htmlspecialchars($image['caption'] ?? 'Gallery Image'); ?></h3>
            <p class="text-muted"><?php echo htmlspecialchars($image['category'] ?? ''); ?></p>
        </div>
    </div>
    <a href="<?php echo BASE_URL; ?>/photo-gallery" class="btn btn-secondary mt-3">&larr; Back to Gallery</a>
</div>
