<div class="container mt-4">
    <h1 class="mb-4"><?php echo $page_title ?? 'Photo Gallery'; ?></h1>
    <?php if (!empty($images)): ?>
        <div class="row">
            <?php foreach ($images as $image): ?>
                <div class="col-md-4 col-lg-3 mb-4">
                    <div class="card">
                        <img src="<?= BASE_URL ?>/assets/images/placeholder/property.svg" class="card-img-top" alt="<?php echo htmlspecialchars($image['caption'] ?? ''); ?>" style="height:200px;object-fit:cover;">
                        <?php if (!empty($image['caption'])): ?>
                            <div class="card-body p-2">
                                <p class="card-text small"><?php echo htmlspecialchars($image['caption']); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">No gallery images available at this time.</div>
    <?php endif; ?>
</div>
