<?php
/** Colony media hub (admin). */
/** @var array $colony */
/** @var array $rows */
$colony = $colony ?? [];
$rows = $rows ?? [];
$base = defined('BASE_URL') ? BASE_URL : '';
$csrf = htmlspecialchars($_SESSION['csrf_token'] ?? '');
$e = function ($v) { return htmlspecialchars((string)($v ?? '')); };
$typeBadge = ['drone_video' => 'danger', 'site_photo' => 'success', 'layout_3d' => 'primary', 'youtube_embed' => 'dark'];
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1"><i class="fas fa-photo-video me-2"></i>Media Hub — <?= $e($colony['name'] ?? '') ?></h4>
        <p class="text-muted mb-0"><?= count($rows) ?> item(s) · <a target="_blank" href="<?= $base ?>/colony/<?= (int)($colony['id'] ?? 0) ?>/media-hub">public gallery ↗</a></p>
    </div>
    <a href="<?= $base ?>/admin/plots/map" class="btn btn-outline-secondary btn-sm">← Map</a>
</div>
<div class="aps-cp-card mb-3"><div class="aps-cp-card-body">
    <h6 class="fw-bold mb-3">Add Media</h6>
    <form method="POST" action="<?= $base ?>/admin/colonies/<?= (int)($colony['id'] ?? 0) ?>/media-hub/upload" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <div class="row g-2">
            <div class="col-md-3"><label class="form-label small">Title *</label><input type="text" name="title" class="form-control" required maxlength="150" placeholder="e.g. Drone flyover — north block"></div>
            <div class="col-md-2"><label class="form-label small">Type *</label>
                <select name="media_type" class="form-select"><option value="site_photo">Site Photo</option><option value="drone_video">Drone Video (URL)</option><option value="layout_3d">3D Layout (URL)</option><option value="youtube_embed">YouTube Embed</option></select>
            </div>
            <div class="col-md-3"><label class="form-label small">Media URL (for video/embed)</label><input type="text" name="media_url" class="form-control" maxlength="255" placeholder="https://…"></div>
            <div class="col-md-2"><label class="form-label small">Photo upload (≤8MB)</label><input type="file" name="media_file" class="form-control" accept=".jpg,.jpeg,.png,.gif,.webp"></div>
            <div class="col-md-2"><label class="form-label small">Tags</label><input type="text" name="tags" class="form-control" maxlength="100" placeholder="Road Work"></div>
            <div class="col-md-2 pt-4"><div class="form-check"><input type="checkbox" name="is_featured" value="1" class="form-check-input" id="featBox"><label class="form-check-label small" for="featBox">Featured</label></div></div>
            <div class="col-md-2 pt-4"><button type="submit" class="btn btn-primary w-100">Add</button></div>
        </div>
    </form>
</div></div>
<div class="aps-cp-card"><div class="aps-cp-card-body">
    <div class="row g-3">
    <?php if (empty($rows)): ?>
        <div class="col-12 text-center text-muted py-4">No media yet — add the first drone video or site photo above.</div>
    <?php else: foreach ($rows as $r): ?>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <span class="badge bg-<?= $typeBadge[$r['media_type']] ?? 'secondary' ?>"><?= $e($r['media_type']) ?></span>
                    <?= !empty($r['is_featured']) ? '<span class="badge bg-warning text-dark">★ featured</span>' : '' ?>
                    <h6 class="mt-2"><?= $e($r['title']) ?></h6>
                    <div class="small text-muted mb-2"><?= $e($r['tags'] ?? '') ?></div>
                    <?php if (($r['media_type'] ?? '') === 'site_photo'): ?>
                        <img src="<?= $base ?>/<?= $e($r['media_url']) ?>" class="img-fluid rounded mb-2" alt="<?= $e($r['title']) ?>">
                    <?php else: ?>
                        <div class="small mb-2"><a target="_blank" href="<?= $e($r['media_url']) ?>">open link ↗</a></div>
                    <?php endif; ?>
                    <form method="POST" action="<?= $base ?>/admin/colonies/media/<?= (int)$r['id'] ?>/delete">
                        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                        <input type="hidden" name="colony_id" value="<?= (int)($colony['id'] ?? 0) ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger" data-aps-confirm="Delete this media?"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; endif; ?>
    </div>
</div></div>
