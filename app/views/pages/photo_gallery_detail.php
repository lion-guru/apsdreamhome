<div class="container mt-4">
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>"><?= __('breadcrumb_home') ?></a></li>
        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/photo-gallery"><?= __('nav_photo_gallery') ?></a></li>
        <li class="breadcrumb-item active"><?= __('gallery_image_detail') ?></li>
    </ol></nav>
    <div class="row">
        <div class="col-md-8">
            <img src="<?= BASE_URL ?>/assets/images/placeholder/property.svg" class="img-fluid" alt="<?php echo htmlspecialchars($image['caption'] ?? ''); ?>">
        </div>
        <div class="col-md-4">
            <h3><?php echo htmlspecialchars($image['caption'] ?? __('gallery_image_default')); ?></h3>
            <p class="text-muted"><?php echo htmlspecialchars($image['category'] ?? ''); ?></p>
        </div>
    </div>
    <a href="<?php echo BASE_URL; ?>/photo-gallery" class="btn btn-secondary mt-3">&larr; <?= __('back_to_gallery') ?></a>
</div>
