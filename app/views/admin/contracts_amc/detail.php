<?php $this->layout = 'layouts/admin'; ?>

<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-0">Contract Details</h1>
      <small class="text-muted">#<?= (int)$contract['id'] ?> · <?= htmlspecialchars($contract['contract_number']) ?></small>
    </div>
    <div class="btn-group">
      <a href="/admin/contracts-amc/<?= (int)$contract['id'] ?>/edit" class="btn btn-outline-secondary"><i class="fas fa-edit me-1"></i> Edit</a>
      <?php if (in_array($contract['status'], ['active','expiring_soon','expired'])): ?>
        <form method="POST" action="/admin/contracts-amc/<?= (int)$contract['id'] ?>/renew" class="d-inline" onsubmit="return confirm('Renew this contract?')">
          <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
          <button type="submit" class="btn btn-outline-success"><i class="fas fa-sync me-1"></i> Renew</button>
        </form>
      <?php endif; ?>
      <a href="/admin/contracts-amc" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
    </div>
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

  <div class="row g-4 mb-4">
    <!-- Main Info Card -->
    <div class="col-xl-8">
      <div class="card aps-cp-card h-100">
        <div class="aps-cp-card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Contract Information</h5>
          <span class="aps-cp-badge aps-cp-badge-<?= $contract['type'] === 'amc' ? 'primary' : ($contract['type'] === 'rental' ? 'success' : ($contract['type'] === 'service' ? 'info' : ($contract['type'] === 'maintenance' ? 'warning' : 'secondary'))) ?>">
            <?= ucfirst($contract['type']) ?>
          </span>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label text-muted small">Status</label>
              <div class="fw-bold">
                <?php
                  $statusClass = match($contract['status']) {
                    'active' => 'aps-cp-stat--green',
                    'expiring_soon' => 'aps-cp-stat--orange',
                    'expired' => 'aps-cp-stat--red',
                    'renewed' => 'aps-cp-stat--blue',
                    'cancelled' => 'aps-cp-stat--purple',
                    default => 'aps-cp-stat--indigo'
                  };
                ?>
                <span class="aps-cp-badge aps-cp-badge-<?= $contract['status'] === 'active' ? 'green' : ($contract['status'] === 'expiring_soon' ? 'orange' : ($contract['status'] === 'expired' ? 'red' : ($contract['status'] === 'renewed' ? 'blue' : ($contract['status'] === 'cancelled' ? 'purple' : 'indigo')))) ?>">
                  <?= ucfirst(str_replace('_', ' ', $contract['status'])) ?>
                </span>
                <?php if ($contract['auto_renew']): ?>
                  <span class="badge bg-info ms-2"><i class="fas fa-sync me-1"></i> Auto-Renew</span>
                <?php endif; ?>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label text-muted small">Party</label>
              <div class="fw-bold"><?= htmlspecialchars($contract['party_name']) ?></div>
              <small class="text-muted"><?= ucfirst($contract['party_type']) ?><?= $contract['party_id'] ? " (ID: {$contract['party_id']})" : '' ?></small>
            </div>
            <div class="col-md-4">
              <label class="form-label text-muted small">Period</label>
              <div><?= date('d M Y', strtotime($contract['start_date'])) ?> - <?= date('d M Y', strtotime($contract['end_date'])) ?></div>
            </div>
            <div class="col-md-4">
              <label class="form-label text-muted small">Billing Cycle</label>
              <div><span class="badge bg-light text-dark"><?= ucfirst(str_replace('_', ' ', $contract['billing_cycle'])) ?></span></div>
            </div>
            <div class="col-md-4">
              <label class="form-label text-muted small">Renewal Notice</label>
              <div><?= (int)$contract['renewal_notice_days'] ?> days before expiry</div>
            </div>
            <div class="col-md-6">
              <label class="form-label text-muted small">Amount</label>
              <div class="fw-bold h5 mb-0"><?= $contract['currency'] === 'INR' ? '₹' : '$' ?><?= number_format((float)$contract['amount'], 2) ?></div>
            </div>
            <div class="col-md-6">
              <label class="form-label text-muted small">Days to Expiry</label>
              <div class="fw-bold h5 mb-0
                <?php if ($contract['is_expired']): ?> text-danger
                <?php elseif ($contract['is_expiring']): ?> text-warning
                <?php else: ?> text-success <?php endif; ?>">
                <?php if ($contract['is_expired']): ?>
                  Overdue <?= abs($contract['days_to_expiry']) ?> days
                <?php else: ?>
                  <?= $contract['days_to_expiry'] ?> days
                <?php endif; ?>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label text-muted small">Renewal Date</label>
              <div><?= $contract['renewal_date'] ? date('d M Y', strtotime($contract['renewal_date'])) : 'N/A' ?></div>
            </div>
            <div class="col-md-6">
              <label class="form-label text-muted small">Created By</label>
              <div><?= htmlspecialchars($contract['creator_name'] ?? 'Unknown') ?></div>
            </div>
            <div class="col-md-6">
              <label class="form-label text-muted small">Created</label>
              <div><?= $contract['created_at'] ? date('d M Y H:i', strtotime($contract['created_at'])) : 'N/A' ?></div>
            </div>
          </div>

          <?php if (!empty($contract['description'])): ?>
            <hr>
            <h6>Description</h6>
            <p class="text-muted"><?= nl2br(htmlspecialchars($contract['description'])) ?></p>
          <?php endif; ?>
          <?php if (!empty($contract['notes'])): ?>
            <h6>Notes</h6>
            <p class="text-muted"><?= nl2br(htmlspecialchars($contract['notes'])) ?></p>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Quick Actions / Summary -->
    <div class="col-xl-4">
      <div class="card aps-cp-card h-100">
        <div class="aps-cp-card-header">
          <h5 class="mb-0">Quick Actions</h5>
        </div>
        <div class="card-body">
          <div class="d-grid gap-2">
            <a href="/admin/contracts-amc/<?= (int)$contract['id'] ?>/edit" class="btn btn-outline-primary">
              <i class="fas fa-edit me-2"></i> Edit Contract
            </a>
            <?php if (in_array($contract['status'], ['active','expiring_soon','expired'])): ?>
              <form method="POST" action="/admin/contracts-amc/<?= (int)$contract['id'] ?>/renew" class="d-inline" onsubmit="return confirm('Renew this contract?')">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <button type="submit" class="btn btn-success w-100">
                  <i class="fas fa-sync me-2"></i> Renew Contract
                </button>
              </form>
            <?php endif; ?>
            <?php if ($contract['party_id']): ?>
              <a href="/admin/users/<?= (int)$contract['party_id'] ?>" class="btn btn-outline-info" target="_blank">
                <i class="fas fa-user me-2"></i> View Party Profile
              </a>
            <?php endif; ?>
            <a href="/admin/contracts-amc" class="btn btn-outline-secondary">
              <i class="fas fa-list me-2"></i> All Contracts
            </a>
          </div>

          <hr>

          <h6 class="text-muted small">Status Indicators</h6>
          <div class="aps-cp-pill-group">
            <span class="aps-cp-pill aps-cp-pill-<?= $contract['is_expired'] ? 'red' : ($contract['is_expiring'] ? 'orange' : 'green') ?>">
              <i class="fas fa-<?= $contract['is_expired'] ? 'exclamation-triangle' : ($contract['is_expiring'] ? 'clock' : 'check-circle') ?> me-1"></i>
              <?= $contract['is_expired'] ? 'Overdue' : ($contract['is_expiring'] ? 'Expiring Soon' : 'Active') ?>
            </span>
            <?php if ($contract['auto_renew']): ?>
              <span class="aps-cp-pill aps-cp-pill-info"><i class="fas fa-sync me-1"></i> Auto-Renew</span>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Activity Timeline -->
  <div class="card aps-cp-card">
    <div class="aps-cp-card-header">
      <h5 class="mb-0">Activity Timeline</h5>
    </div>
    <div class="card-body p-0">
      <?php if (empty($activities)): ?>
        <div class="aps-cp-empty text-center py-4">
          <i class="aps-cp-empty-icon fas fa-history"></i>
          <h6>No activities yet</h6>
          <p class="text-muted">Contract activities will appear here</p>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover aps-cp-table mb-0">
            <thead class="tenant-header">
              <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Description</th>
                <th>By</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($activities as $a): ?>
                <tr>
                  <td><?= date('d M Y H:i', strtotime($a['created_at'])) ?></td>
                  <td>
                    <span class="aps-cp-pill aps-cp-pill-secondary">
                      <?= htmlspecialchars(str_replace('_', ' ', $a['activity_type'])) ?>
                    </span>
                  </td>
                  <td><?= htmlspecialchars($a['description']) ?></td>
                  <td><?= htmlspecialchars($a['user_name'] ?? 'System') ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<style>
.aps-cp-badge-green { background:#dcfce7; color:#166534; }
.aps-cp-badge-orange { background:#ffedd5; color:#9a3412; }
.aps-cp-badge-red { background:#fee2e2; color:#991b1b; }
.aps-cp-badge-blue { background:#dbeafe; color:#1e40af; }
.aps-cp-badge-purple { background:#f3e8ff; color:#6b21a8; }
.aps-cp-badge-indigo { background:#e0e7ff; color:#3730a3; }
</style>