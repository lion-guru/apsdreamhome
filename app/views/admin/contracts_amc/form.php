<?php $this->layout = 'layouts/admin'; ?>
<?php $isEdit = $contract !== null; ?>

<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><?= $isEdit ? 'Edit' : 'Create' ?> Contract / AMC</h1>
    <a href="/admin/contracts-amc" class="btn btn-outline-secondary">
      <i class="fas fa-arrow-left me-1"></i> Back
    </a>
  </div>

  <?php if ($this->getFlash('success')): ?>
    <div class="alert alert-success alert-dismissible fade show">
      <?= $this->getFlash('success') ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
  <?php if ($this->getFlash('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show">
      <?= $this->getFlash('error') ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <div class="row justify-content-center">
    <div class="col-xl-8">
      <div class="card aps-cp-card">
        <div class="aps-cp-card-header">
          <h5 class="mb-0">Contract Details</h5>
        </div>
        <div class="card-body">
          <form method="POST" action="<?= $isEdit ? '/admin/contracts-amc/' . (int)$contract['id'] . '/update' : '/admin/contracts-amc/store' ?>">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Contract Number *</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($contract['contract_number'] ?? 'Auto-generated') ?>" <?= $isEdit ? 'readonly' : 'disabled' ?>>
                <?php if (!$isEdit): ?><small class="text-muted">Auto-generated on save</small><?php endif; ?>
              </div>

              <div class="col-md-6">
                <label class="form-label">Type *</label>
                <select name="type" class="form-select" required>
                  <?php foreach (['amc','rental','service','maintenance','warranty','other'] as $t): ?>
                    <option value="<?= $t ?>" <?= ($contract['type'] ?? '') === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label">Party Name *</label>
                <input type="text" name="party_name" class="form-control" value="<?= htmlspecialchars($contract['party_name'] ?? '') ?>" required>
              </div>

              <div class="col-md-6">
                <label class="form-label">Party Type</label>
                <select name="party_type" class="form-select">
                  <?php foreach (['customer','vendor','supplier','partner'] as $pt): ?>
                    <option value="<?= $pt ?>" <?= ($contract['party_type'] ?? 'customer') === $pt ? 'selected' : '' ?>><?= ucfirst($pt) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label">Link to User/Lead (optional)</label>
                <select name="party_id" class="form-select">
                  <option value="">-- None --</option>
                  <?php foreach ($users as $u): ?>
                    <option value="<?= (int)$u['id'] ?>" <?= ($contract['party_id'] ?? 0) === (int)$u['id'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($u['name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label">Start Date *</label>
                <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($contract['start_date'] ?? date('Y-m-d')) ?>" required>
              </div>

              <div class="col-md-6">
                <label class="form-label">End Date *</label>
                <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($contract['end_date'] ?? date('Y-m-d', strtotime('+1 year'))) ?>" required>
              </div>

              <div class="col-md-4">
                <label class="form-label">Amount</label>
                <div class="input-group">
                  <span class="input-group-text">₹</span>
                  <input type="number" name="amount" step="0.01" class="form-control" value="<?= htmlspecialchars($contract['amount'] ?? 0) ?>">
                </div>
              </div>

              <div class="col-md-4">
                <label class="form-label">Currency</label>
                <select name="currency" class="form-select">
                  <option value="INR" <?= ($contract['currency'] ?? 'INR') === 'INR' ? 'selected' : '' ?>>INR</option>
                  <option value="USD" <?= ($contract['currency'] ?? '') === 'USD' ? 'selected' : '' ?>>USD</option>
                </select>
              </div>

              <div class="col-md-4">
                <label class="form-label">Billing Cycle</label>
                <select name="billing_cycle" class="form-select">
                  <?php foreach (['monthly','quarterly','half_yearly','yearly','one_time'] as $bc): ?>
                    <option value="<?= $bc ?>" <?= ($contract['billing_cycle'] ?? 'yearly') === $bc ? 'selected' : '' ?><?= ucfirst(str_replace('_', ' ', $bc)) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-md-4">
                <label class="form-label">Renewal Notice (days before)</label>
                <input type="number" name="renewal_notice_days" class="form-control" value="<?= htmlspecialchars($contract['renewal_notice_days'] ?? 30) ?>" min="1" max="365">
              </div>

              <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" <?= !$isEdit ? 'disabled' : '' ?>>
                  <?php foreach (['draft','active','expiring_soon','expired','renewed','cancelled'] as $s): ?>
                    <option value="<?= $s ?>" <?= ($contract['status'] ?? 'active') === $s ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $s)) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-md-4">
                <div class="form-check mt-4">
                  <input type="checkbox" name="auto_renew" class="form-check-input" id="auto_renew" value="1" <?= ($contract['auto_renew'] ?? 0) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="auto_renew">Auto-renew on expiry</label>
                </div>
              </div>

              <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($contract['description'] ?? '') ?></textarea>
              </div>

              <div class="col-12">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="2"><?= htmlspecialchars($contract['notes'] ?? '') ?></textarea>
              </div>
            </div>

            <div class="d-flex gap-2 mt-4 pt-3 border-top">
              <button type="submit" class="btn btn-tenant-primary">
                <i class="fas fa-save me-1"></i> <?= $isEdit ? 'Update Contract' : 'Create Contract' ?>
              </button>
              <a href="/admin/contracts-amc" class="btn btn-outline-secondary">Cancel</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// Auto-calculate renewal date when end_date or notice_days changes
document.addEventListener('DOMContentLoaded', function() {
  const endDate = document.querySelector('input[name="end_date"]');
  const noticeDays = document.querySelector('input[name="renewal_notice_days"]');
  if (endDate && noticeDays) {
    function updateRenewal() {
      if (endDate.value && noticeDays.value) {
        const end = new Date(endDate.value);
        const notice = parseInt(noticeDays.value) || 0;
        const renewal = new Date(end);
        renewal.setDate(renewal.getDate() - notice);
        // Could show calculated renewal date as hint
        console.log('Renewal date would be:', renewal.toISOString().split('T')[0]);
      }
    }
    endDate.addEventListener('change', updateRenewal);
    noticeDays.addEventListener('input', updateRenewal);
  }
});
</script>