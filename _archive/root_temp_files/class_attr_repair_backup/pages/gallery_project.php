<?php
$breadcrumbs = $breadcrumbs ?? [['title' => 'Home', 'url' => BASE_URL], ['title' => 'Gallery', 'url' => BASE_URL . '/gallery'], ['title' => 'Project Gallery', 'url' => '']];
$images = $images ?? [];
?>
<section class="section-padding py-5 bg-light">
    <div class="container">
        <h1 class="display-6 fw-bold text-primary mb-2">Project Gallery</h1>
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <?php foreach ($breadcrumbs as $crumb): ?>
                    <?php if (isset($crumb['url']) && $crumb['url']): ?>
                        <li class="breadcrumb-item"><a href="<?= $crumb['url'] ?>"><?= $crumb['title'] ?></a></li>
                    <?php else: ?>
                        <li class="breadcrumb-item active" aria-current="page"><?= $crumb['title'] ?></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ol>
        </nav>

        <?php if (empty($images)): ?>
            <div class="text-center py-5">
                <i class="fas fa-images fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">No gallery images found</h4>
                <p class="text-muted">Check back later for project photos and updates.</p>
                <a href="<?= BASE_URL ?>/gallery" class="btn btn-primary rounded-pill px-4">View Full Gallery</a>
            </div>
        <?php else: ?>
            <div class="row g-3" id="galleryGrid">
                <?php foreach ($images as $i => $img): ?>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="card border-0 shadow-sm rounded-3 overflow-hidden h-100 gallery-item" class="style-10432" onclick="openLightbox(<?= $i ?>)">
                            <img src="<?= htmlspecialchars($img['image_path'] ?? $img['image_url'] ?? '/assets/images/placeholder.jpg') ?>"
                                 class="card-img-top" alt="<?= htmlspecialchars($img['title'] ?? 'Gallery Image') ?>"
                                 class="style-31036" loading="lazy">
                            <?php if (!empty($img['title'])): ?>
                                <div class="card-body p-2">
                                    <p class="card-text small text-muted mb-0"><?= htmlspecialchars($img['title'] ?? '') ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="text-center mt-5">
            <a href="<?= BASE_URL ?>/gallery" class="btn btn-outline-primary rounded-pill px-4 me-2">
                <i class="fas fa-arrow-left me-1"></i> Back to Gallery
            </a>
            <a href="<?= BASE_URL ?>" class="btn btn-primary rounded-pill px-4">
                <i class="fas fa-home me-1"></i> Home
            </a>
        </div>
    </div>
</section>

<?php if (!empty($images)): ?>
<div class="modal fade" id="lightboxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content bg-dark">
            <div class="modal-header border-0">
                <h6 class="text-white mb-0" id="lightboxTitle"></h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-0">
                <img id="lightboxImage" src="" class="img-fluid" class="style-17121">
            </div>
        </div>
    </div>
</div>
<script>
const galleryImages = <?= json_encode(array_map(fn($img) => [
    'src' => $img['image_path'] ?? $img['image_url'] ?? '/assets/images/placeholder.jpg',
    'title' => $img['title'] ?? ''
], $images)) ?>;
let currentIndex = 0;
function openLightbox(index) {
    currentIndex = index;
    const img = galleryImages[index];
    document.getElementById('lightboxImage').src = img.src;
    document.getElementById('lightboxTitle').textContent = img.title;
    new bootstrap.Modal(document.getElementById('lightboxModal')).show();
}
</script>
<?php endif; ?>
