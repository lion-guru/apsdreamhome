<?php $pageTitle = $pageTitle ?? $page_title ?? "Performance Dashboard"; $metrics = $metrics ?? []; $base = $base ?? BASE_URL; ?>
<div class="container-fluid py-4">
    <h4><i class="fas fa-chart-line me-2"></i>Performance Metrics</h4>
    <div class="row g-3 mt-2">
        <div class="col-md-3"><div class="card aps-cp-card"><div class="card-body text-center"><h5><?= number_format($metrics["page_load"] ?? 0, 2) ?>s</h5><small class="text-muted">Avg Page Load</small></div></div></div>
        <div class="col-md-3"><div class="card aps-cp-card"><div class="card-body text-center"><h5><?= number_format($metrics["queries"] ?? 0) ?></h5><small class="text-muted">DB Queries/sec</small></div></div></div>
        <div class="col-md-3"><div class="card aps-cp-card"><div class="card-body text-center"><h5><?= number_format($metrics["cache_hits"] ?? 0, 1) ?>%</h5><small class="text-muted">Cache Hit Rate</small></div></div></div>
        <div class="col-md-3"><div class="card aps-cp-card"><div class="card-body text-center"><h5><?= number_format($metrics["error_rate"] ?? 0, 2) ?>%</h5><small class="text-muted">Error Rate</small></div></div></div>
    </div>
</div>