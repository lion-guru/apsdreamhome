<?php $report = $report ?? []; $result = $result ?? null; $params = json_decode($report['parameters'] ?? '{}', true) ?: []; ?>
<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Run Report: <?= htmlspecialchars($report['report_name'] ?? '') ?></h1>
    <a href="<?= BASE_URL ?>/admin/backoffice/reports" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
  </div>

  <div class="card aps-cp-card mb-4">
    <div class="card-header"><strong>Parameters</strong></div>
    <div class="card-body">
      <form method="post" class="row g-3 align-items-end">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
        <?php foreach ($params as $key => $label): ?>
          <div class="col-md-3">
            <label class="form-label"><?= htmlspecialchars($label) ?></label>
            <input type="text" name="<?= htmlspecialchars($key) ?>" class="form-control" required>
          </div>
        <?php endforeach; ?>
        <div class="col-auto"><button class="btn btn-primary"><i class="fas fa-play me-1"></i>Execute</button></div>
      </form>
    </div>
  </div>

  <?php if ($result): ?>
    <?php if (isset($result['error'])): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($result['error']) ?></div>
    <?php else: ?>
      <div class="card aps-cp-card">
        <div class="card-header"><strong>Results</strong> — <?= $result['row_count'] ?? 0 ?> rows</div>
        <div class="table-responsive">
          <?php if (!empty($result['rows'])): ?>
            <table class="table table-hover mb-0">
              <thead><tr><?php foreach (array_keys($result['rows'][0] ?? []) as $col): ?><th><?= htmlspecialchars($col) ?></th><?php endforeach; ?></tr></thead>
              <tbody>
                <?php foreach ($result['rows'] as $row): ?>
                  <tr><?php foreach ($row as $val): ?><td><?= htmlspecialchars($val ?? '') ?></td><?php endforeach; ?></tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php else: ?>
            <div class="p-4 text-center text-muted">No data returned</div>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>
