<?php
$gallery = $media['gallery'] ?? [];
$layoutMap = $media['layout_map'] ?? null;
$virtualTour = $media['virtual_tour'] ?? null;
$brochure = $media['brochure'] ?? null;
$video = $media['video'] ?? null;
?>
<section class="microsite-section bg-light py-5" id="microsite-media">
    <div class="container">
        <div class="row mb-4 align-items-center">
            <div class="col">
                <h2 class="section-title">Visual Gallery</h2>
            </div>
            <?php if (!empty($brochure)): ?>
            <div class="col-auto">
                <a href="<?php echo h($brochure); ?>" target="_blank" rel="noopener" class="btn btn-outline-primary">
                    <i class="fa-solid fa-file-arrow-down me-2"></i>Download Brochure
                </a>
            </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($gallery)): ?>
        <div class="microsite-gallery row g-3 mb-4">
            <?php foreach ($gallery as $item): ?>
            <div class="col-md-4 col-sm-6">
                <div class="microsite-gallery__item">
                    <img src="<?php echo h($item['url'] ?? ''); ?>" alt="<?php echo h($item['alt'] ?? 'Project image'); ?>" loading="lazy">
                    <?php if (!empty($item['caption'])): ?>
                    <div class="microsite-gallery__caption"><?php echo h($item['caption']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="row g-4">
            <?php if (!empty($video)): ?>
            <div class="col-lg-6">
                <div class="ratio ratio-16x9 microsite-media-card">
                    <iframe src="<?php echo h($video); ?>" title="Project video" allowfullscreen loading="lazy"></iframe>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($virtualTour)): ?>
            <div class="col-lg-6">
                <div class="ratio ratio-16x9 microsite-media-card">
                    <iframe src="<?php echo h($virtualTour); ?>" title="Virtual tour" allowfullscreen loading="lazy"></iframe>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($layoutMap)): ?>
            <div class="col-lg-6">
                <div class="microsite-media-card">
                    <img src="<?php echo h($layoutMap); ?>" alt="Project layout map" class="img-fluid rounded" loading="lazy">
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
