<?php $reports = $reports ?? []; ?>
<div class="container-fluid py-4">
  <h1 class="h3 mb-4">Report Center</h1>
  <div class="row g-3">
    <?php if (empty($reports)): ?>
      <div class="col-12 text-center text-muted py-4">No reports configured</div>
    <?php else: ?>
      <?php foreach ($reports as $r): ?>
        <div class="col-md-4">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
              <h5 class="card-title"><?= htmlspecialchars($r['report_name'] ?? '') ?></h5>
              <p class="text-muted small">Type: <?= ucfirst($r['report_type'] ?? '') ?> | Format: <?= strtoupper($r['format'] ?? 'html') ?></p>
              <p class="small">Last run: <?= $r['last_run_at'] ?? 'Never' ?></p>
              <div class="d-flex gap-2">
                <a href="<?= BASE_URL ?>/admin/backoffice/reports/<?= $r['id'] ?>/run" class="btn btn-primary btn-sm"><i class="fas fa-play me-1"></i>Run</a>
                <a href="<?= BASE_URL ?>/admin/backoffice/reports/<?= $r['id'] ?>/history" class="btn btn-outline-secondary btn-sm"><i class="fas fa-history me-1"></i>History</a>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>
