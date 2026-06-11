<?php $pageTitle = $pageTitle ?? $page_title ?? "Events Dashboard"; $events = $events ?? []; $base = $base ?? BASE_URL; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-calendar-alt me-2"></i>Events & Webinars</h4>
        <a href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>events/create" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Create Event</a>
    </div>
    <?php if (empty($events)): ?>
    <div class="card aps-cp-card"><div class="card-body text-center py-5"><i class="fas fa-calendar fa-3x text-muted mb-3"></i><p class="text-muted">No upcoming events</p></div></div>
    <?php else: ?>
    <div class="row"><?php foreach ($events as $e): ?>
        <div class="col-md-4 mb-3"><div class="card aps-cp-card"><div class="card-body aps-cp-card-body"><h6><?= h($e["title"] ?? "") ?></h6><p class="small text-muted"><?= h($e["date"] ?? "") ?></p><span class="badge bg-primary"><?= h($e["status"] ?? "upcoming") ?></span></div></div></div>
    <?php endforeach; ?></div>
    <?php endif; ?>
</div>