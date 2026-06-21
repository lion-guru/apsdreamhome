<?php $colonies = $colonies ?? []; $employees = $employees ?? []; ?>
<div class="container-fluid py-4">
  <h1 class="h3 mb-4"><?= __('bko_new_ops_entry') ?></h1>
  <div class="card aps-cp-card">
    <div class="card-body aps-cp-card-body">
      <form method="post" action="<?= BASE_URL ?>/admin/backoffice/operations/store">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
        <div class="row g-3">
          <div class="col-md-4"><label class="form-label"><?= __('bko_date') ?></label><input type="date" name="log_date" class="form-control" value="<?= date('Y-m-d') ?>"></div>
          <div class="col-md-4"><label class="form-label"><?= __('bko_type') ?></label><select name="log_type" class="form-select">
            <?php foreach(['site_visit','client_meeting','collection','payment_received','cheque_collected','document_submission','registry','mutation','legal_update','construction_update','other'] as $t): ?>
              <option value="<?= $t ?>"><?= ucfirst(str_replace('_',' ',$t)) ?></option>
            <?php endforeach; ?>
          </select></div>
          <div class="col-md-4"><label class="form-label"><?= __('bko_colony') ?></label><select name="colony_id" class="form-select">
            <option value=""><?= __('bko_select') ?></option>
            <?php foreach ($colonies as $c): ?><option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name'] ?? '') ?></option><?php endforeach; ?>
          </select></div>
          <div class="col-md-4"><label class="form-label"><?= __('bko_description') ?></label><input type="text" name="description" class="form-control"></div>
          <div class="col-md-4"><label class="form-label"><?= __('bko_amount') ?></label><input type="number" name="amount" class="form-control" step="0.01"></div>
          <div class="col-md-4"><label class="form-label"><?= __('bko_party_name') ?></label><input type="text" name="party_name" class="form-control"></div>
          <div class="col-md-4"><label class="form-label"><?= __('bko_party_type') ?></label><select name="party_type" class="form-select">
            <option value="customer"><?= __('bko_customer') ?></option><option value="vendor"><?= __('bko_vendor') ?></option><option value="land_owner"><?= __('bko_land_owner') ?></option><option value="employee"><?= __('bko_employee') ?></option><option value="government"><?= __('bko_government') ?></option><option value="other"><?= __('bko_other') ?></option>
          </select></div>
          <div class="col-md-4"><label class="form-label"><?= __('bko_status') ?></label><select name="status" class="form-select">
            <option value="pending"><?= __('bko_pending') ?></option><option value="in_progress"><?= __('bko_in_progress') ?></option><option value="completed"><?= __('bko_completed') ?></option><option value="cancelled"><?= __('bko_cancelled') ?></option>
          </select></div>
          <div class="col-md-4"><label class="form-label"><?= __('bko_priority') ?></label><select name="priority" class="form-select">
            <option value="medium"><?= __('bko_medium') ?></option><option value="high"><?= __('bko_high') ?></option><option value="low"><?= __('bko_low') ?></option>
          </select></div>
          <div class="col-md-4"><label class="form-label"><?= __('bko_assigned_to') ?></label><select name="assigned_to" class="form-select">
            <option value=""><?= __('bko_select') ?></option>
            <?php foreach ($employees as $e): ?><option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['name'] ?? '') ?></option><?php endforeach; ?>
          </select></div>
          <div class="col-12"><label class="form-label"><?= __('bko_notes') ?></label><textarea name="notes" class="form-control" rows="3"></textarea></div>
          <div class="col-12"><button class="btn btn-primary"><i class="fas fa-save me-1"></i><?= __('bko_save_entry') ?></button></div>
        </div>
      </form>
    </div>
  </div>
</div>
