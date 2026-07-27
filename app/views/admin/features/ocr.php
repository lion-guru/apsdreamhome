<?php
$page_title = $page_title ?? 'OCR & Documents';
$page_heading = $page_heading ?? 'OCR & Document Classification';
$content = $content ?? '';
ob_start();
?>
<div class="container-fluid py-4">
  <h1 class="h3 mb-4"><i class="fas fa-file-alt me-2"></i>OCR & Document Classification</h1>

  <form method="POST" action="<?= BASE_URL ?>/api/v2/ocr/report" class="card card-body mb-4">
                      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    <h5>Generate Report</h5>
    <div class="row g-2">
      <div class="col-md-3">
        <select name="report_type" class="form-select">
          <option value="leads">Leads</option>
          <option value="sales">Sales</option>
          <option value="plots">Plots</option>
          <option value="agents">Agents</option>
          <option value="associates">Associates</option>
          <option value="colonies">Colonies</option>
          <option value="financial">Financial</option>
        </select>
      </div>
      <div class="col-md-3"><button class="btn btn-primary">Generate</button></div>
    </div>
  </form>

  <ul class="nav nav-tabs mb-3">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#docs">Documents</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#cls">Classifications</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tmpl">Templates</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#exec">Report Runs</button></li>
  </ul>

  <div class="tab-content">
    <div class="tab-pane fade show active" id="docs">
      <div class="card shadow-sm"><div class="card-body p-0">
        <div class="table-responsive"><table class="table mb-0">
          <thead class="table-light"><tr><th>ID</th><th>Status</th><th>Text Length</th><th>Processed</th></tr></thead>
          <tbody>
            <?php if (empty($documents)): ?>
              <tr><td colspan="4" class="text-center py-3 text-muted">No OCR documents</td></tr>
            <?php else: foreach ($documents as $d): ?>
              <tr>
                <td>#<?= htmlspecialchars($d['id'] ?? '') ?></td>
                <td><span class="badge bg-success"><?= htmlspecialchars($d['status'] ?? '') ?></span></td>
                <td><?= htmlspecialchars(strlen($d['extracted_text'] ?? '')) ?> chars</td>
                <td><small><?= htmlspecialchars($d['processed_at'] ?? '') ?></small></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table></div>
      </div></div>
    </div>

    <div class="tab-pane fade" id="cls">
      <div class="card shadow-sm"><div class="card-body p-0">
        <div class="table-responsive"><table class="table mb-0">
          <thead class="table-light"><tr><th>Doc ID</th><th>Category</th><th>Confidence</th><th>Classified</th></tr></thead>
          <tbody>
            <?php if (empty($classifications)): ?>
              <tr><td colspan="4" class="text-center py-3 text-muted">No classifications</td></tr>
            <?php else: foreach ($classifications as $c): ?>
              <tr>
                <td>#<?= htmlspecialchars($c['document_id'] ?? '') ?></td>
                <td><span class="badge bg-info"><?= htmlspecialchars($c['category'] ?? '') ?></span></td>
                <td><?= htmlspecialchars($c['confidence'] ?? '') ?></td>
                <td><small><?= htmlspecialchars($c['classified_at'] ?? '') ?></small></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table></div>
      </div></div>
    </div>

    <div class="tab-pane fade" id="tmpl">
      <div class="card shadow-sm"><div class="card-body p-0">
        <div class="table-responsive"><table class="table mb-0">
          <thead class="table-light"><tr><th>Name</th><th>Category</th><th>Active</th></tr></thead>
          <tbody>
            <?php if (empty($templates)): ?>
              <tr><td colspan="3" class="text-center py-3 text-muted">No templates</td></tr>
            <?php else: foreach ($templates as $t): ?>
              <tr>
                <td><?= htmlspecialchars($t['name'] ?? '') ?></td>
                <td><span class="badge bg-info"><?= htmlspecialchars($t['category'] ?? '') ?></span></td>
                <td><?= ($t['active'] ?? 0) ? '✓' : '✗' ?></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table></div>
      </div></div>
    </div>

    <div class="tab-pane fade" id="exec">
      <div class="card shadow-sm"><div class="card-body p-0">
        <div class="table-responsive"><table class="table mb-0">
          <thead class="table-light"><tr><th>ID</th><th>Type</th><th>Status</th><th>Duration</th><th>Started</th></tr></thead>
          <tbody>
            <?php if (empty($executions)): ?>
              <tr><td colspan="5" class="text-center py-3 text-muted">No report runs</td></tr>
            <?php else: foreach ($executions as $e): ?>
              <tr>
                <td>#<?= htmlspecialchars($e['id'] ?? '') ?></td>
                <td><code><?= htmlspecialchars($e['report_type'] ?? '') ?></code></td>
                <td><span class="badge bg-<?= ($e['status'] ?? '') === 'completed' ? 'success' : (($e['status'] ?? '') === 'failed' ? 'danger' : 'warning') ?>"><?= htmlspecialchars($e['status'] ?? '') ?></span></td>
                <td><?= htmlspecialchars($e['duration_ms'] ?? '') ?>ms</td>
                <td><small><?= htmlspecialchars($e['started_at'] ?? '') ?></small></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table></div>
      </div></div>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';
