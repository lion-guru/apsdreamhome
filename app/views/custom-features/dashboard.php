<?php $pageTitle = $pageTitle ?? $page_title ?? "Custom Features"; $features = $features ?? []; $base = $base ?? BASE_URL; ?>
<div class="container-fluid py-4">
    <h4><i class="fas fa-puzzle-piece me-2"></i>Custom Features Dashboard</h4>
    <p class="text-muted">Manage custom feature modules for your platform.</p>
    <div class="row mt-3">
        <?php foreach ($features as $f): ?>
        <div class="col-md-4 mb-3"><div class="card aps-cp-card"><div class="card-body aps-cp-card-body"><h6><?= h($f["name"] ?? "Feature") ?></h6><p class="small text-muted"><?= h($f["description"] ?? "") ?></p><span class="badge bg-<?= ($f["enabled"] ?? false) ? "success" : "secondary" ?>"><?= ($f["enabled"] ?? false) ? "Active" : "Inactive" ?></span></div></div></div>
        <?php endforeach; ?>
    </div>
</div>