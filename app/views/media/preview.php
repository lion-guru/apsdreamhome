<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-eye me-2 text-secondary"></i>Media Preview</h4>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <?php if (!empty($media['url'])): ?>
                    <?php $ext = strtolower(pathinfo($media['url'], PATHINFO_EXTENSION)); if (in_array($ext, ['jpg','jpeg','png','gif','webp'])): ?>
                        <img src="<?= htmlspecialchars($media['url']) ?>" class="card-img-top" alt="Preview">
                    <?php else: ?>
                        <div class="card-body text-center py-5">
                            <i class="fas fa-file display-1 text-muted"></i>
                            <p class="mt-3"><?= htmlspecialchars($media['filename'] ?? '') ?></p>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="card-body text-center py-5"><p class="text-muted">No media found.</p></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>