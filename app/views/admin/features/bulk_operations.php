<?php
$page_title = $page_title ?? 'Bulk Import/Export';
$page_heading = $page_heading ?? 'Bulk Import/Export';
$content = $content ?? '';
ob_start();
$result = $_SESSION['bulk_result'] ?? null;
unset($_SESSION['bulk_result']);
?>
<div class="container-fluid py-4">
  <h1 class="h3 mb-4"><i class="fas fa-file-csv me-2"></i>Bulk Import/Export</h1>

  <?php if ($result): ?>
    <?php if ($result['ok']): ?>
      <div class="alert alert-success alert-dismissible fade show">
        <strong>Imported <?= $result['imported'] ?> rows.</strong>
        <?php if ($result['failed'] > 0): ?>
          <strong class="text-warning"><?= $result['failed'] ?> failed.</strong>
          <ul class="mb-0 mt-2 small"><?php foreach ($result['errors'] as $err): ?><li><?= htmlspecialchars($err ?? '') ?></li><?php endforeach; ?></ul>
        <?php endif; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php else: ?>
      <div class="alert alert-danger alert-dismissible fade show">
        <strong>Import failed:</strong> <?= htmlspecialchars($result['error'] ?? '') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>
  <?php endif; ?>

  <div class="row mb-4">
    <div class="col-lg-6">
      <div class="card shadow-sm h-100">
        <div class="card-header bg-primary text-white"><h5 class="mb-0"><i class="fas fa-upload me-2"></i>Import CSV</h5></div>
        <div class="card-body aps-cp-card-body">
          <form method="post" action="<?= BASE_URL ?>/admin/bulk-operations/import" enctype="multipart/form-data">
                              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            <div class="mb-3">
              <label class="form-label">Target Table</label>
              <select name="table" class="form-select" required>
                <option value="">-- Select table --</option>
                <?php foreach ($tables as $t): ?>
                  <option value="<?= $t ?>"><?= htmlspecialchars($t ?? '') ?> (<?= $row_counts[$t] ?? 0 ?> rows)</option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">CSV File</label>
              <input type="file" name="csv" accept=".csv,text/csv" class="form-control" required>
              <small class="text-muted">First row must be column headers. <a href="<?= BASE_URL ?>/admin/bulk-operations/template/leads">Download template</a></small>
            </div>
            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-upload"></i> Upload & Import</button>
          </form>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card shadow-sm h-100">
        <div class="card-header bg-success text-white"><h5 class="mb-0"><i class="fas fa-download me-2"></i>Export CSV</h5></div>
        <div class="card-body aps-cp-card-body">
          <p class="text-muted">Download table data as CSV. Each table has a "View" link to filter and export.</p>
          <div class="list-group">
            <?php foreach ($tables as $t): ?>
              <div class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                  <strong><?= htmlspecialchars($t ?? '') ?></strong>
                  <span class="badge bg-secondary ms-2"><?= number_format($row_counts[$t] ?? 0) ?> rows</span>
                </div>
                <div>
                  <a href="<?= BASE_URL ?>/admin/bulk-operations/template/<?= $t ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-file"></i> Template</a>
                  <a href="<?= BASE_URL ?>/admin/bulk-operations/export/<?= $t ?>?limit=1000" class="btn btn-sm btn-success"><i class="fas fa-download"></i> Export</a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-header bg-white"><h5 class="mb-0">Allowed Tables Schema</h5></div>
    <div class="card-body aps-cp-card-body">
      <p class="text-muted small">For security, only these tables are allowed for bulk operations. Column lists are enforced on import.</p>
      <div class="row">
        <?php foreach ($tables as $t): ?>
          <div class="col-md-4 mb-3">
            <h6><code><?= htmlspecialchars($t ?? '') ?></code></h6>
            <div class="small text-muted">
              <?php
              $svc = new \App\Services\BulkOperationsService($this->db ?? \App\Core\Database::getInstance());
              $template = $svc->getTemplate($t);
              $cols = explode(',', explode("\n", $template)[0]);
              echo implode(' &middot; ', array_map('htmlspecialchars', $cols));
              ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';
