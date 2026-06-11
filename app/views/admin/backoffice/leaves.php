<?php $leaves = $leaves ?? []; ?>
<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3"><?= __('admin_pending_leave_requests') ?></h1>
    <a href="<?= BASE_URL ?>/admin/backoffice/leaves/history" class="btn btn-outline-primary btn-sm"><i class="fas fa-history me-1"></i><?= __('admin_history_button') ?></a>
  </div>
  <div class="card aps-cp-card">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead><tr><th><?= __('admin_employee_label') ?></th><th><?= __('admin_type_label') ?></th><th><?= __('admin_from_label') ?></th><th><?= __('admin_to_label') ?></th><th><?= __('admin_days_label') ?></th><th><?= __('admin_reason_label') ?></th><th><?= __('admin_actions_label') ?></th></tr></thead>
        <tbody>
          <?php if (empty($leaves)): ?>
            <tr><td colspan="7" class="text-center text-muted py-4"><?= __('admin_no_pending_leaves') ?></td></tr>
          <?php else: ?>
            <?php foreach ($leaves as $l): ?>
              <tr>
                <td><?= htmlspecialchars($l['employee_name'] ?? '') ?></td>
                <td><span class="badge bg-info"><?= ucfirst($l['leave_type'] ?? '') ?></span></td>
                <td><?= $l['start_date'] ?? '' ?></td>
                <td><?= $l['end_date'] ?? '' ?></td>
                <td><?= $l['total_days'] ?? '' ?></td>
                <td><?= htmlspecialchars($l['reason'] ?? '') ?></td>
                <td>
                  <form method="post" action="<?= BASE_URL ?>/admin/backoffice/leaves/<?= $l['id'] ?>/approve" class="d-inline">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                    <button class="btn btn-success btn-sm"><i class="fas fa-check"></i></button>
                  </form>
                  <form method="post" action="<?= BASE_URL ?>/admin/backoffice/leaves/<?= $l['id'] ?>/reject" class="d-inline">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                    <input type="text" name="remarks" placeholder="<?= __('admin_reason_label') ?>" class="form-control form-control-sm d-inline-block" style="width:120px">
                    <button class="btn btn-danger btn-sm"><i class="fas fa-times"></i></button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
