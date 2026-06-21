<?php $history = $history ?? []; $report_id = $report_id ?? 0; ?>
<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3"><?= __('bko_report_history') ?></h1>
    <div>
      <a href="<?= BASE_URL ?>/admin/backoffice/reports/<?= $report_id ?>/run" class="btn btn-primary btn-sm"><i class="fas fa-play me-1"></i><?= __('bko_run_report') ?></a>
      <a href="<?= BASE_URL ?>/admin/backoffice/reports" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i><?= __('bko_back') ?></a>
    </div>
  </div>
  <div class="card aps-cp-card">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead><tr><th>#</th><th><?= __('bko_executed_by') ?></th><th><?= __('bko_start') ?></th><th><?= __('bko_end') ?></th><th><?= __('bko_rows') ?></th><th><?= __('bko_status') ?></th><th><?= __('bko_error') ?></th></tr></thead>
        <tbody>
          <?php if (empty($history)): ?>
            <tr><td colspan="7" class="text-center text-muted py-4"><?= __('bko_no_history') ?></td></tr>
          <?php else: ?>
            <?php foreach ($history as $h): ?>
              <tr>
                <td><?= $h['id'] ?? '' ?></td>
                <td><?= htmlspecialchars($h['executed_by_name'] ?? '') ?></td>
                <td><?= $h['start_time'] ?? '' ?></td>
                <td><?= $h['end_time'] ?? '-' ?></td>
                <td><?= $h['row_count'] ?? 0 ?></td>
                <td><span class="badge bg-<?= ($h['status'] ?? '') === 'completed' ? 'success' : (($h['status'] ?? '') === 'failed' ? 'danger' : 'warning') ?>"><?= ucfirst($h['status'] ?? '') ?></span></td>
                <td class="text-danger small"><?= htmlspecialchars($h['error_message'] ?? '') ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
