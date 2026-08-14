<?php $leaves = $leaves ?? []; $status_filter = $status_filter ?? ''; ?>
<div class="container-fluid py-4">
  <h1 class="h3 mb-4"><?= __('bko_leave_history') ?></h1>
  <form class="row g-2 mb-4" method="get">
    <?php echo CSRFProtection::csrfField(); ?>
    <div class="col-auto">
      <select name="status" class="form-select">
        <option value=""><?= __('bko_all_status') ?></option>
        <option value="pending" <?= $status_filter==='pending'?'selected':'' ?>><?= __('bko_pending') ?></option>
        <option value="approved" <?= $status_filter==='approved'?'selected':'' ?>><?= __('bko_approved') ?></option>
        <option value="rejected" <?= $status_filter==='rejected'?'selected':'' ?>><?= __('bko_rejected') ?></option>
        <option value="cancelled" <?= $status_filter==='cancelled'?'selected':'' ?>><?= __('bko_cancelled') ?></option>
      </select>
    </div>
    <div class="col-auto"><button class="btn btn-primary"><?= __('bko_filter') ?></button></div>
  </form>
  <div class="card aps-cp-card">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead><tr><th><?= __('bko_employee') ?></th><th><?= __('bko_type') ?></th><th><?= __('bko_from') ?></th><th><?= __('bko_to') ?></th><th><?= __('bko_days') ?></th><th><?= __('bko_status') ?></th><th><?= __('bko_approved_by') ?></th></tr></thead>
        <tbody>
          <?php if (empty($leaves)): ?>
            <tr><td colspan="7" class="text-center text-muted py-4"><?= __('bko_no_records') ?></td></tr>
          <?php else: ?>
            <?php foreach ($leaves as $l): ?>
              <tr>
                <td><?= htmlspecialchars($l['employee_name'] ?? '') ?></td>
                <td><?= ucfirst($l['leave_type'] ?? '') ?></td>
                <td><?= $l['start_date'] ?? '' ?></td>
                <td><?= $l['end_date'] ?? '' ?></td>
                <td><?= $l['total_days'] ?? '' ?></td>
                <td><span class="badge bg-<?= ($l['status'] ?? '') === 'approved' ? 'success' : (($l['status'] ?? '') === 'rejected' ? 'danger' : (($l['status'] ?? '') === 'cancelled' ? 'secondary' : 'warning')) ?>"><?= ucfirst($l['status'] ?? '') ?></span></td>
                <td><?= htmlspecialchars($l['approver_name'] ?? '-') ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
