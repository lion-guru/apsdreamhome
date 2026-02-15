<?php
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
                <h1 class="mb-4"><?= h($tour['property_title'] ?? 'Virtual Property Tour') ?></h1>
                
                <div id="viewer-container" style="width: 100%; height: 600px; background: #000; position: relative;">
                    <?php if (!empty($tour['assets'])): ?>
                        <!-- 360 Viewer Implementation -->
                        <div id="viewer" style="width: 100%; height: 100%;"></div>
                        
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
                            <p>No assets found for this virtual tour.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mt-4">
                    <h3>About this Tour</h3>
                    <p><?= nl2br(h($tour['description'] ?? 'Experience this property in immersive 360°.')) ?></p>
                </div>

                <?php if (!empty($tour['assets']) && count($tour['assets']) > 1): ?>
                    <div class="mt-4">
                        <h4>Tour Scenes</h4>
                        <div class="row g-3">
                            <?php foreach ($tour['assets'] as $asset): ?>
                                <div class="col-md-3">
                                    <div class="card h-100 tour-scene-card" style="cursor: pointer;">
                                        <img src="<?= BASE_URL . ($asset['thumbnail_path'] ?: $asset['file_path']) ?>" class="card-img-top" alt="<?= h($asset['title']) ?>">
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
