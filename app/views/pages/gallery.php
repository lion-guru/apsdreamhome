<section class="page-hero">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h1 class="display-4 fw-bold mb-3"><?= __('gallery_hero_title') ?></h1>
                <p class="lead mb-0"><?= __('gallery_hero_subtitle') ?></p>
                <?php if (!empty($galleryImages)): ?>
                    <p class="mt-2 text-muted"><i class="fas fa-images me-1"></i> <?= count($galleryImages) ?> photos</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($pageContent)): ?>
<section class="py-4 bg-white">
    <div class="container"><div class="row justify-content-center"><div class="col-lg-10"><div class="cms-content"><?php echo e($pageContent); ?></div></div></div></div>
</section>
<?php endif; ?>

<?php if (!empty($galleryImages)): ?>
<section class="py-5">
    <div class="container">
        <!-- Category Filter -->
        <div class="text-center mb-5">
            <div class="btn-group flex-wrap" role="group">
                <button type="button" class="btn btn-primary active" data-filter="all">
                    <?= __('gallery_filter_all') ?> <span class="badge bg-white text-primary ms-1"><?= count($galleryImages) ?></span>
                </button>
                <?php foreach ($galleryCategories as $cat): ?>
                <button type="button" class="btn btn-outline-primary" data-filter="<?= htmlspecialchars($cat ?? '') ?>"><?= ucfirst(htmlspecialchars($cat ?? '')) ?></button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Gallery Grid -->
        <div class="row g-4" id="galleryGrid">
            <?php foreach ($galleryImages as $idx => $img): ?>
            <div class="col-md-4 col-lg-3 gallery-item" data-category="<?= htmlspecialchars($img['category'] ?? 'all') ?>">
                <div class="card border-0 shadow-sm overflow-hidden h-100 gallery-card" class="style-10432" onclick="openLightbox(<?= $idx ?>)">
                    <div class="position-relative" class="style-44237">
                        <?php if (!empty($img['image_path'])): ?>
                        <?php $imgRaw = $img['image_path'] ?? '';
                              $imgSrc = (str_starts_with($imgRaw, 'http://') || str_starts_with($imgRaw, 'https://')) ? $imgRaw : BASE_URL . '/' . $imgRaw; ?>
                        <img src="<?= htmlspecialchars($imgSrc ?? '') ?>" alt="<?= htmlspecialchars($img['title'] ?? $img['caption'] ?? '') ?>" class="w-100 h-100" class="style-44820" loading="lazy">
                        <?php else: ?>
                        <div class="bg-light d-flex align-items-center justify-content-center h-100"><i class="fas fa-image fa-3x text-muted"></i></div>
                        <?php endif; ?>
                        <?php if (!empty($img['category'])): ?>
                        <span class="badge bg-dark position-absolute top-0 start-0 m-2"><?= ucfirst($img['category']) ?></span>
                        <?php endif; ?>
                        <div class="position-absolute bottom-0 end-0 m-2">
                            <span class="badge bg-dark bg-opacity-75"><i class="fas fa-expand"></i></span>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <?php if (!empty($img['title'])): ?>
                        <h6 class="card-title mb-1"><?= htmlspecialchars($img['title'] ?? '') ?></h6>
                        <?php endif; ?>
                        <?php if (!empty($img['caption'])): ?>
                        <p class="text-muted small mb-0"><?= htmlspecialchars($img['caption'] ?? '') ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Lightbox Modal -->
<div class="modal fade" id="galleryLightbox" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-dark border-0">
            <div class="modal-header border-0 py-2">
                <span class="text-white small" id="lightboxCounter"></span>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-0">
                <img id="lightboxImage" src="" class="img-fluid" class="style-25219" alt="">
                <div class="py-3">
                    <h6 class="text-white mb-1" id="lightboxTitle"></h6>
                    <p class="text-white-50 small mb-0" id="lightboxCaption"></p>
                </div>
            </div>
            <div class="modal-footer border-0 justify-content-center py-2">
                <button class="btn btn-outline-light btn-sm me-2" onclick="navigateLightbox(-1)"><i class="fas fa-chevron-left"></i></button>
                <button class="btn btn-outline-light btn-sm" onclick="navigateLightbox(1)"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
    </div>
</div>

<script>
var galleryData = <?= json_encode(array_values($galleryImages)) ?>;
var currentLightboxIndex = 0;

function openLightbox(index) {
    currentLightboxIndex = index;
    updateLightbox();
    new bootstrap.Modal(document.getElementById('galleryLightbox')).show();
}

function navigateLightbox(dir) {
    currentLightboxIndex = (currentLightboxIndex + dir + galleryData.length) % galleryData.length;
    updateLightbox();
}

function updateLightbox() {
    var img = galleryData[currentLightboxIndex];
    var src = img.image_path ? (img.image_path.startsWith('http') ? img.image_path : '<?= BASE_URL ?>/' + img.image_path) : '';
    document.getElementById('lightboxImage').src = src;
    document.getElementById('lightboxTitle').textContent = img.title || '';
    document.getElementById('lightboxCaption').textContent = img.caption || '';
    document.getElementById('lightboxCounter').textContent = (currentLightboxIndex + 1) + ' / ' + galleryData.length;
}

document.querySelectorAll('[data-filter]').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('[data-filter]').forEach(function(b) { b.classList.remove('active', 'btn-primary'); b.classList.add('btn-outline-primary'); });
        this.classList.add('active', 'btn-primary');
        this.classList.remove('btn-outline-primary');
        var filter = this.dataset.filter;
        document.querySelectorAll('.gallery-item').forEach(function(item) {
            item.style.display = (filter === 'all' || item.dataset.category === filter) ? '' : 'none';
        });
    });
});
</script>

<?php else: ?>
<section class="py-5">
    <div class="container text-center">
        <i class="fas fa-images fa-4x text-muted mb-3"></i>
        <h4 class="text-muted"><?= __('gallery_coming_soon') ?></h4>
        <p class="text-muted"><?= __('gallery_coming_soon_desc') ?></p>
    </div>
</section>
<?php endif; ?>

<section class="py-5 bg-light">
    <div class="container text-center">
        <h3 class="mb-4"><?= __('gallery_want_more') ?></h3>
        <p class="text-muted mb-4"><?= __('gallery_want_more_desc') ?></p>
        <a href="<?= BASE_URL ?>/contact" class="btn btn-primary btn-lg"><i class="fas fa-phone me-2"></i><?= __('gallery_contact_us') ?></a>
    </div>
</section>
