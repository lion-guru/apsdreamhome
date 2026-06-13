<section class="page-hero">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h1 class="display-4 fw-bold mb-3"><?= __('gallery_hero_title') ?></h1>
                <p class="lead mb-0"><?= __('gallery_hero_subtitle') ?></p>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($pageContent)): ?>
<section class="py-4 bg-white">
    <div class="container"><div class="row justify-content-center"><div class="col-lg-10"><div class="cms-content"><?php echo $pageContent; ?></div></div></div></div>
</section>
<?php endif; ?>

<?php if (!empty($galleryImages)): ?>
<section class="py-5">
    <div class="container">
        <!-- Category Filter -->
        <div class="text-center mb-5">
            <div class="btn-group flex-wrap" role="group">
                <button type="button" class="btn btn-primary active" data-filter="all"><?= __('gallery_filter_all') ?></button>
                <?php foreach ($galleryCategories as $cat): ?>
                <button type="button" class="btn btn-outline-primary" data-filter="<?= htmlspecialchars($cat) ?>"><?= ucfirst(htmlspecialchars($cat)) ?></button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Gallery Grid -->
        <div class="row g-4" id="galleryGrid">
            <?php foreach ($galleryImages as $img): ?>
            <div class="col-md-4 col-lg-3 gallery-item" data-category="<?= htmlspecialchars($img['category'] ?? 'all') ?>">
                <div class="card border-0 shadow-sm overflow-hidden h-100 gallery-card">
                    <div class="position-relative" style="height: 250px;">
                        <?php if (!empty($img['image_path'])): ?>
                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($img['image_path']) ?>" alt="<?= htmlspecialchars($img['title'] ?? $img['caption'] ?? '') ?>" class="w-100 h-100" style="object-fit:cover;" loading="lazy">
                        <?php else: ?>
                        <div class="bg-light d-flex align-items-center justify-content-center h-100"><i class="fas fa-image fa-3x text-muted"></i></div>
                        <?php endif; ?>
                        <?php if (!empty($img['category'])): ?>
                        <span class="badge bg-dark position-absolute top-0 start-0 m-2"><?= ucfirst($img['category']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body p-3">
                        <?php if (!empty($img['title'])): ?>
                        <h6 class="card-title mb-1"><?= htmlspecialchars($img['title']) ?></h6>
                        <?php endif; ?>
                        <?php if (!empty($img['caption'])): ?>
                        <p class="text-muted small mb-0"><?= htmlspecialchars($img['caption']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php else: ?>
<section class="py-5">
    <div class="container text-center">
        <i class="fas fa-images fa-4x text-muted mb-3"></i>
        <h4 class="text-muted">Gallery images coming soon</h4>
        <p class="text-muted">We're preparing stunning visuals of our projects for you.</p>
    </div>
</section>
<?php endif; ?>

<section class="py-5 bg-light">
    <div class="container text-center">
        <h3 class="mb-4">Want to See More?</h3>
        <p class="text-muted mb-4">Contact us for site visits and detailed project galleries</p>
        <a href="<?= BASE_URL ?>/contact" class="btn btn-primary btn-lg"><i class="fas fa-phone me-2"></i>Contact Us</a>
    </div>
</section>

<script>
document.querySelectorAll('[data-filter]').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('[data-filter]').forEach(b => { b.classList.remove('active', 'btn-primary'); b.classList.add('btn-outline-primary'); });
        this.classList.add('active', 'btn-primary');
        this.classList.remove('btn-outline-primary');
        const filter = this.dataset.filter;
        document.querySelectorAll('.gallery-item').forEach(item => {
            item.style.display = (filter === 'all' || item.dataset.category === filter) ? '' : 'none';
        });
    });
});
</script>
