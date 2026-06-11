<?php $leads = $leads ?? []; $summary = $summary ?? []; $filters = $filters ?? []; $total = $total ?? 0; ?>
<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3"><?= __('admin_lead_pipeline') ?></h1>
    <a href="<?= BASE_URL ?>/admin/backoffice/leads/create" class="btn btn-primary"><i class="fas fa-plus me-1"></i><?= __('admin_new_lead') ?></a>
  </div>
  <?php if (!empty($summary['stages'])): ?>
  <div class="row g-2 mb-4">
    <?php foreach ($summary['stages'] as $s): ?>
      <div class="col">
        <div class="card aps-cp-card text-center p-2">
          <div class="fs-5 fw-bold"><?= (int)($s['count'] ?? 0) ?></div>
          <div class="text-muted small"><?= ucfirst($s['status'] ?? '') ?></div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  <div class="mb-3"><?= __('admin_total_colon') ?> <strong><?= $total ?></strong> | <?= __('admin_conversion_label') ?> <strong><?= $summary['conversion_rate'] ?? 0 ?>%</strong> | <?= __('admin_won_label') ?> <strong><?= $summary['won'] ?? 0 ?></strong></div>
  <?php endif; ?>

  <form class="row g-2 mb-3" method="get">
    <div class="col-auto"><select name="status" class="form-select form-select-sm"><option value=""><?= __('admin_all_status') ?></option>
      <?php foreach(['new','contacted','qualified','viewing','negotiation','closed_won','closed_lost','on_hold'] as $s): ?>
        <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
      <?php endforeach; ?>
    </select></div>
    <div class="col-auto"><select name="priority" class="form-select form-select-sm"><option value=""><?= __('admin_all_priority') ?></option>
      <?php foreach(['hot','warm','cold','dead'] as $p): ?>
        <option value="<?= $p ?>" <?= ($filters['priority'] ?? '') === $p ? 'selected' : '' ?>><?= ucfirst($p) ?></option>
      <?php endforeach; ?>
    </select></div>
    <div class="col-auto"><input type="text" name="search" class="form-control form-control-sm" placeholder="<?= __('admin_search_placeholder') ?>" value="<?= htmlspecialchars($filters['search'] ?? '') ?>"></div>
    <div class="col-auto"><button class="btn btn-primary btn-sm"><?= __('admin_filter_btn') ?></button></div>
  </form>

  <div class="card aps-cp-card">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead><tr><th><?= __('admin_hash_label') ?></th><th><?= __('admin_lead_label') ?></th><th><?= __('admin_contact_label') ?></th><th><?= __('admin_source_label') ?></th><th><?= __('admin_type_label') ?></th><th><?= __('admin_priority_label') ?></th><th><?= __('admin_score_label') ?></th><th><?= __('admin_status_label') ?></th><th><?= __('admin_follow_up') ?></th><th></th></tr></thead>
        <tbody>
          <?php if (empty($leads)): ?>
            <tr><td colspan="10" class="text-center text-muted py-4"><?= __('admin_no_leads') ?></td></tr>
          <?php else: ?>
            <?php foreach ($leads as $l): ?>
              <tr>
                <td><?= htmlspecialchars($l['lead_number'] ?? '') ?></td>
                <td><?= htmlspecialchars($l['lead_name'] ?? '') ?></td>
                <td><?= htmlspecialchars($l['contact_phone'] ?? '') ?></td>
                <td><span class="badge bg-secondary"><?= $l['lead_source'] ?? '' ?></span></td>
                <td><?= ucfirst($l['lead_type'] ?? '') ?></td>
                <td><span class="badge bg-<?= ($l['priority'] ?? '') === 'hot' ? 'danger' : (($l['priority'] ?? '') === 'warm' ? 'warning' : (($l['priority'] ?? '') === 'cold' ? 'info' : 'dark')) ?>"><?= ucfirst($l['priority'] ?? '') ?></span></td>
                <td><span class="badge bg-primary"><?= $l['score'] ?? 0 ?></span></td>
                <td><span class="badge bg-<?= ($l['status'] ?? '') === 'closed_won' ? 'success' : (($l['status'] ?? '') === 'closed_lost' ? 'danger' : 'warning') ?>"><?= str_replace('_',' ', ucfirst($l['status'] ?? '')) ?></span></td>
                <td><?= $l['follow_up_date'] ?? '-' ?></td>
                <td><a href="<?= BASE_URL ?>/admin/backoffice/leads/<?= $l['id'] ?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-eye"></i></a></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
