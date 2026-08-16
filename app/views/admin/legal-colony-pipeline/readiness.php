<?php
$colony    = $colony ?? null;
$readiness = $readiness ?? ['checks' => [], 'passed_count' => 0, 'total_checks' => 0, 'readiness_pct' => 0, 'is_ready' => false];
?>
<div class="container-fluid py-4">
  <a href="/admin/legal-colony-pipeline/detail/<?= $colony['id'] ?? 0 ?>" class="text-decoration-none text-muted small"><i class="fas fa-arrow-left me-1"></i> Back to Pipeline</a>
  <h2 class="mb-4"><i class="fas fa-clipboard-check me-2 text-warning"></i>Phase 7: Sales Readiness — <?= htmlspecialchars($colony['name'] ?? '') ?></h2>

  <div class="row g-4">
    <div class="col-lg-8">
      <!-- Readiness Score -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-body text-center py-5">
          <div class="display-1 fw-bold <?= ($readiness['is_ready'] ?? false) ? 'text-success' : 'text-warning' ?>">
            <?= (int)($readiness['readiness_pct'] ?? 0) ?>%
          </div>
          <h4 class="<?= ($readiness['is_ready'] ?? false) ? 'text-success' : 'text-warning' ?>">
            <?= ($readiness['is_ready'] ?? false) ? 'READY FOR SALES LAUNCH!' : 'Not Yet Ready' ?>
          </h4>
          <p class="text-muted"><?= (int)($readiness['passed_count'] ?? 0) ?> / <?= (int)($readiness['total_checks'] ?? 0) ?> checks passed</p>
          <div class="progress mt-3" class="style-38853">
            <div class="progress-bar <?= ($readiness['is_ready'] ?? false) ? 'bg-success' : 'bg-warning' ?>" class="style-91943"></div>
          </div>
        </div>
      </div>

      <!-- Checklist -->
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-dark text-white"><strong><i class="fas fa-list-check me-1"></i> Readiness Checklist</strong></div>
        <div class="list-group list-group-flush">
          <?php foreach (($readiness['checks'] ?? []) as $i => $chk): ?>
            <div class="list-group-item bg-dark text-white border-secondary d-flex align-items-center justify-content-between py-3">
              <div>
                <span class="me-3 fs-5">
                  <?php if ($chk['passed'] ?? false): ?>
                    <i class="fas fa-check-circle text-success"></i>
                  <?php else: ?>
                    <i class="fas fa-times-circle text-danger"></i>
                  <?php endif; ?>
                </span>
                <strong><?= htmlspecialchars($chk['label'] ?? '') ?></strong>
                <?php if (!empty($chk['detail'])): ?>
                  <span class="badge bg-secondary ms-2"><?= htmlspecialchars($chk['detail']) ?></span>
                <?php endif; ?>
              </div>
              <div>
                <?php if ($chk['passed'] ?? false): ?>
                  <span class="badge bg-success">PASSED</span>
                <?php else: ?>
                  <span class="badge bg-danger"><?= strtoupper($chk['status'] ?? 'PENDING') ?></span>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-dark text-white"><strong><i class="fas fa-info-circle me-1"></i> Colony Summary</strong></div>
        <div class="card-body">
          <table class="table table-dark table-sm mb-0">
            <tr><td class="text-muted">Name</td><td><?= htmlspecialchars($colony['name'] ?? '') ?></td></tr>
            <tr><td class="text-muted">Location</td><td><?= htmlspecialchars($colony['location'] ?? '') ?></td></tr>
            <tr><td class="text-muted">Total Plots</td><td><?= (int)($colony['total_plots'] ?? 0) ?></td></tr>
            <tr><td class="text-muted">Available</td><td><?= (int)($colony['available_plots'] ?? 0) ?></td></tr>
            <tr><td class="text-muted">Starting Price</td><td>₹<?= number_format(floatval($colony['starting_price'] ?? 0)) ?></td></tr>
            <tr><td class="text-muted">Land Cost</td><td>₹<?= number_format(floatval($colony['estimated_land_cost'] ?? 0)) ?></td></tr>
            <tr><td class="text-muted">Pipeline Stage</td><td><span class="badge bg-warning"><?= ucfirst(str_replace('_', ' ', $colony['pipeline_stage'] ?? '')) ?></span></td></tr>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
