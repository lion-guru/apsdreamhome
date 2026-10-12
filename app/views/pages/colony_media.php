<?php
/** Public colony media gallery + 1-click WhatsApp share. */
/** @var array $colony */
/** @var array $rows */
$colony = $colony ?? [];
$rows = $rows ?? [];
$base = defined('BASE_URL') ? BASE_URL : '';
$e = function ($v) { return htmlspecialchars((string)($v ?? '')); };
$shareText = 'See ' . ($colony['name'] ?? 'our township') . ' live: ' . $base . '/colony/' . (int)($colony['id'] ?? 0) . '/media-hub';
?>
<div class="container py-4" style="max-width:960px">
    <h3 class="mb-1"><i class="fas fa-photo-video me-2"></i><?= $e($colony['name'] ?? '') ?> — Media Gallery</h3>
    <p class="text-muted">Drone walkthroughs, site photos &amp; layouts
        <a class="btn btn-sm btn-success ms-2" target="_blank" rel="noopener" href="https://wa.me/?text=<?= urlencode($shareText) ?>"><i class="fab fa-whatsapp me-1"></i>Share Gallery on WhatsApp</a>
    </p>
    <div class="row g-3">
    <?php if (empty($rows)): ?>
        <div class="col-12"><p class="text-muted">Fresh site media is on its way — check back soon.</p></div>
    <?php else: foreach ($rows as $r):
        $isPhoto = ($r['media_type'] ?? '') === 'site_photo';
        $isYt = ($r['media_type'] ?? '') === 'youtube_embed';
        $waOne = 'See ' . ($colony['name'] ?? '') . ' — ' . ($r['title'] ?? '') . ': ' . (($isPhoto ? $base . '/' : '') . ($r['media_url'] ?? ''));
    ?>
        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <?php if ($isPhoto): ?>
                    <img src="<?= $e(strpos($r['media_url'], 'http') === 0 ? $r['media_url'] : $base . '/' . $r['media_url']) ?>" class="card-img-top" alt="<?= $e($r['title']) ?>">
                <?php endif; ?>
                <div class="card-body">
                    <h6><?= $e($r['title']) ?> <?= !empty($r['is_featured']) ? '★' : '' ?></h6>
                    <div class="small text-muted mb-2"><?= $e($r['media_type']) ?><?= $e($r['tags'] ?? '') !== '' ? ' · ' . $e($r['tags']) : '' ?></div>
                    <div class="d-flex gap-2">
                        <?php if (!$isPhoto): ?><a class="btn btn-sm btn-outline-primary" target="_blank" href="<?= $e($r['media_url']) ?>">Open</a><?php endif; ?>
                        <a class="btn btn-sm btn-outline-success" target="_blank" rel="noopener" href="https://wa.me/?text=<?= urlencode($waOne) ?>"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; endif; ?>
    </div>
</div>
