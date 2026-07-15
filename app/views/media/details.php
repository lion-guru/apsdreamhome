<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-info-circle me-2 text-info"></i>Media Details</h4>
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <?php if (!empty($media['url'])): ?>
                    <img src="<?= htmlspecialchars($media['url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($media['title'] ?? '') ?>">
                <?php endif; ?>
                <div class="card-body">
                    <h5><?= htmlspecialchars($media['title'] ?? 'Untitled') ?></h5>
                    <p class="mb-1"><strong>File:</strong> <?= htmlspecialchars($media['filename'] ?? '') ?></p>
                    <p class="mb-1"><strong>Size:</strong> <?= number_format((int)($media['size'] ?? 0) / 1024, 1) ?> KB</p>
                    <p class="mb-0"><strong>Uploaded:</strong> <?= htmlspecialchars($media['created_at'] ?? '') ?></p>
                </div>
            </div>
        </div>
    </div>
</div>