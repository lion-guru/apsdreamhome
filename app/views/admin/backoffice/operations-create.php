<?php $colonies = $colonies ?? []; $employees = $employees ?? []; ?>
<div class="container-fluid py-4">
  <h1 class="h3 mb-4">New Operations Entry</h1>
  <div class="card aps-cp-card">
    <div class="card-body aps-cp-card-body">
      <form method="post" action="<?= BASE_URL ?>/admin/backoffice/operations/store">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
        <div class="row g-3">
          <div class="col-md-4"><label class="form-label">Date</label><input type="date" name="log_date" class="form-control" value="<?= date('Y-m-d') ?>"></div>
          <div class="col-md-4"><label class="form-label">Type</label><select name="log_type" class="form-select">
            <?php foreach(['site_visit','client_meeting','collection','payment_received','cheque_collected','document_submission','registry','mutation','legal_update','construction_update','other'] as $t): ?>
              <option value="<?= $t ?>"><?= ucfirst(str_replace('_',' ',$t)) ?></option>
            <?php endforeach; ?>
          </select></div>
          <div class="col-md-4"><label class="form-label">Colony</label><select name="colony_id" class="form-select">
            <option value="">Select...</option>
            <?php foreach ($colonies as $c): ?><option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name'] ?? '') ?></option><?php endforeach; ?>
          </select></div>
          <div class="col-md-4"><label class="form-label">Description</label><input type="text" name="description" class="form-control"></div>
          <div class="col-md-4"><label class="form-label">Amount</label><input type="number" name="amount" class="form-control" step="0.01"></div>
          <div class="col-md-4"><label class="form-label">Party Name</label><input type="text" name="party_name" class="form-control"></div>
          <div class="col-md-4"><label class="form-label">Party Type</label><select name="party_type" class="form-select">
            <option value="customer">Customer</option><option value="vendor">Vendor</option><option value="land_owner">Land Owner</option><option value="employee">Employee</option><option value="government">Government</option><option value="other">Other</option>
          </select></div>
          <div class="col-md-4"><label class="form-label">Status</label><select name="status" class="form-select">
            <option value="pending">Pending</option><option value="in_progress">In Progress</option><option value="completed">Completed</option><option value="cancelled">Cancelled</option>
          </select></div>
          <div class="col-md-4"><label class="form-label">Priority</label><select name="priority" class="form-select">
            <option value="medium">Medium</option><option value="high">High</option><option value="low">Low</option>
          </select></div>
          <div class="col-md-4"><label class="form-label">Assigned To</label><select name="assigned_to" class="form-select">
            <option value="">Select...</option>
            <?php foreach ($employees as $e): ?><option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['name'] ?? '') ?></option><?php endforeach; ?>
          </select></div>
          <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="3"></textarea></div>
          <div class="col-12"><button class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Entry</button></div>
        </div>
      </form>
    </div>
  </div>
</div>
