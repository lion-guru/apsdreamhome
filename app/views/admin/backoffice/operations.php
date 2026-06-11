<?php $logs = $logs ?? []; $colonies = $colonies ?? []; $filter_date = $filter_date ?? date('Y-m-d'); $filters = $filters ?? []; ?>
<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3"><?= __('admin_daily_operations_log') ?></h1>
    <a href="<?= BASE_URL ?>/admin/backoffice/operations/create" class="btn btn-primary"><i class="fas fa-plus me-1"></i><?= __('admin_new_entry') ?></a>
  </div>
  <form class="row g-2 mb-4" method="get">
    <div class="col-auto"><input type="date" name="date" class="form-control" value="<?= $filter_date ?>"></div>
    <div class="col-auto"><select name="log_type" class="form-select">
      <option value=""><?= __('admin_all_types') ?></option>
      <?php foreach(['site_visit','client_meeting','collection','payment_received','cheque_collected','document_submission','registry','mutation','legal_update','construction_update','other'] as $t): ?>
        <option value="<?= $t ?>" <?= ($filters['log_type'] ?? '') === $t ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$t)) ?></option>
      <?php endforeach; ?>
    </select></div>
    <div class="col-auto"><select name="status" class="form-select">
      <option value=""><?= __('admin_all_status') ?></option>
      <?php foreach(['completed','in_progress','pending','cancelled'] as $s): ?>
        <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
      <?php endforeach; ?>
    </select></div>
    <div class="col-auto"><button class="btn btn-primary"><?= __('admin_filter_btn') ?></button></div>
  </form>
  <div class="card aps-cp-card">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead><tr><th><?= __('admin_type_label') ?></th><th><?= __('admin_colony_label') ?></th><th><?= __('admin_description_label') ?></th><th>Amount</th><th>Party</th><th><?= __('admin_status_label') ?></th><th><?= __('admin_priority_label') ?></th><th><?= __('admin_date_label') ?></th></tr></thead>
        <tbody>
          <?php if (empty($logs)): ?>
            <tr><td colspan="8" class="text-center text-muted py-4"><?= __('admin_no_operations_for_date') ?></td></tr>
          <?php else: ?>
            <?php foreach ($logs as $l): ?>
              <tr>
                <td><span class="badge bg-info"><?= ucfirst(str_replace('_',' ',$l['log_type'] ?? '')) ?></span></td>
                <td><?= htmlspecialchars($l['colony_name'] ?? '') ?></td>
                <td><?= htmlspecialchars($l['description'] ?? '') ?></td>
                <td><?= ($l['amount'] ?? null) !== null ? '&#8377;'.number_format($l['amount'], 2) : '-' ?></td>
                <td><?= htmlspecialchars($l['party_name'] ?? '') ?></td>
                <td><span class="badge bg-<?= ($l['status'] ?? '') === 'completed' ? 'success' : (($l['status'] ?? '') === 'in_progress' ? 'warning' : (($l['status'] ?? '') === 'cancelled' ? 'secondary' : 'danger')) ?>"><?= ucfirst(str_replace('_',' ',$l['status'] ?? '')) ?></span></td>
                <td><span class="badge bg-<?= ($l['priority'] ?? '') === 'high' ? 'danger' : (($l['priority'] ?? '') === 'medium' ? 'warning' : 'secondary') ?>"><?= ucfirst($l['priority'] ?? '') ?></span></td>
                <td><?= $l['log_date'] ?? '' ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
