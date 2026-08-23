<?php

// TODO: Add proper error handling with try-catch blocks

/**
 * Virtual Tour View
 */
?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <?= h($error) ?>
                </div>
            <?php elseif (isset($tour)): ?>
                <h1 class="mb-4"><?= h($tour['property_title'] ?? __('virtual_tour_default_title')) ?></h1>
                
                <div id="viewer-container" class="style-43831">
                    <?php if (!empty($tour['assets'])): ?>
                        <!-- 360 Viewer Implementation -->
                        <div id="viewer" class="style-80928"></div>
                        
                        <script src="https://cdn.jsdelivr.net/npm/photo-sphere-viewer@4/dist/photo-sphere-viewer.min.js"></script>
                        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/photo-sphere-viewer@4/dist/photo-sphere-viewer.min.css">
                        
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const viewer = new PhotoSphereViewer.Viewer({
                                    container: document.querySelector('#viewer'),
                                    panorama: '<?= BASE_URL . $tour['assets'][0]['file_path'] ?>',
                                    caption: '<?= h($tour['assets'][0]['title'] ?? '') ?>',
                                    navbar: [
                                        'autorotate',
                                        'zoom',
                                        'download',
                                        'fullscreen',
                                        'caption'
                                    ]
                                });
                            });
                        </script>
                    <?php else: ?>
                        <div class="d-flex align-items-center justify-content-center h-100 text-white">
                            <p><?= __('virtual_tour_no_assets') ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mt-4">
                    <h3><?= __('virtual_tour_about') ?></h3>
                    <p><?= nl2br(h($tour['description'] ?? __('virtual_tour_about_default'))) ?></p>
                </div>

                <?php if (!empty($tour['assets']) && count($tour['assets']) > 1): ?>
                    <div class="mt-4">
                        <h4><?= __('virtual_tour_scenes') ?></h4>
                        <div class="row g-3">
                            <?php foreach ($tour['assets'] as $asset): ?>
                                <div class="col-md-3">
                                    <div class="card h-100 tour-scene-card style-75920">
                                        <img src="<?= BASE_URL ?>/assets/images/placeholder/property.svg" class="card-img-top img-fluid" alt="<?= h($asset['title']) ?>">
                                        <div class="card-body p-2">
                                            <p class="card-text small mb-0"><?= h($asset['title']) ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.tour-scene-card:hover {
    transform: translateY(-5px);
    transition: transform 0.3s ease;
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}
#viewer-container {
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 15px 35px rgba(0,0,0,0.2);
}
</style>
