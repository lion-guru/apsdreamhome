<?php $this->layout = 'layouts/admin'; ?>

<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Contracts & AMC Tracker</h1>
    <a href="/admin/contracts-amc/create" class="btn btn-tenant-primary">
      <i class="fas fa-plus me-1"></i> New Contract/AMC
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

  <!-- Stats Cards -->
  <div class="row g-3 mb-4">
    <div class="col-xl-2 col-md-4 col-6">
      <div class="aps-cp-stat aps-cp-stat--blue">
        <div class="aps-cp-stat-icon"><i class="fas fa-file-contract"></i></div>
        <div class="aps-cp-stat-body">
          <div class="aps-cp-stat-label">Total</div>
          <div class="aps-cp-stat-value"><?= (int)$stats['total'] ?></div>
        </div>
      </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
      <div class="aps-cp-stat aps-cp-stat--green">
        <div class="aps-cp-stat-icon"><i class="fas fa-check-circle"></i></div>
        <div class="aps-cp-stat-body">
          <div class="aps-cp-stat-label">Active</div>
          <div class="aps-cp-stat-value"><?= (int)$stats['active'] ?></div>
        </div>
      </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
      <div class="aps-cp-stat aps-cp-stat--orange">
        <div class="aps-cp-stat-icon"><i class="fas fa-clock"></i></div>
        <div class="aps-cp-stat-body">
          <div class="aps-cp-stat-label">Expiring Soon</div>
          <div class="aps-cp-stat-value"><?= (int)$stats['expiring_soon'] ?></div>
        </div>
      </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
      <div class="aps-cp-stat aps-cp-stat--red">
        <div class="aps-cp-stat-icon"><i class="fas fa-times-circle"></i></div>
        <div class="aps-cp-stat-body">
          <div class="aps-cp-stat-label">Expired</div>
          <div class="aps-cp-stat-value"><?= (int)$stats['expired'] ?></div>
        </div>
      </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
      <div class="aps-cp-stat aps-cp-stat--purple">
        <div class="aps-cp-stat-icon"><i class="fas fa-rupee-sign"></i></div>
        <div class="aps-cp-stat-body">
          <div class="aps-cp-stat-label">Renewal Value</div>
          <div class="aps-cp-stat-value"><?= number_format((float)$stats['renewal_value'], 0) ?></div>
        </div>
      </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
      <div class="aps-cp-stat aps-cp-stat--indigo">
        <div class="aps-cp-stat-icon"><i class="fas fa-sync"></i></div>
        <div class="aps-cp-stat-body">
          <div class="aps-cp-stat-label">Auto-Renew</div>
          <div class="aps-cp-stat-value"><?= (int)$this->db->fetchOne("SELECT COUNT(*) FROM contracts_amc WHERE auto_renew=1" . ($this->tenantWhere()[0] ?? ''), $this->tenantWhere()[1] ?? [])['c'] ?></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Filters -->
  <div class="card aps-cp-card mb-4">
    <div class="card-body">
      <form method="GET" class="row g-3">
        <div class="col-md-3">
          <label class="form-label">Search</label>
          <input type="text" name="search" class="form-control" placeholder="Contract #, Party, Description..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
        </div>
        <div class="col-md-2">
          <label class="form-label">Status</label>
          <select name="status" class="form-select">
            <option value="">All Status</option>
            <?php foreach ($statuses as $s): ?>
              <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $s)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">Type</label>
          <select name="type" class="form-select">
            <option value="">All Types</option>
            <?php foreach ($types as $t): ?>
              <option value="<?= $t ?>" <?= ($filters['type'] ?? '') === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
          <button type="submit" class="btn btn-tenant-primary w-100"><i class="fas fa-filter me-1"></i> Filter</button>
        </div>
        <div class="col-md-1 d-flex align-items-end">
          <a href="/admin/contracts-amc" class="btn btn-outline-secondary w-100"><i class="fas fa-times"></i></a>
        </div>
      </form>
    </div>
  </div>

  <!-- Contracts Table -->
  <div class="card aps-cp-card">
    <div class="card-header aps-cp-card-header">
      <h5 class="mb-0">All Contracts / AMCs</h5>
    </div>
    <div class="card-body p-0">
      <?php if (empty($contracts)): ?>
        <div class="aps-cp-empty text-center py-5">
          <i class="aps-cp-empty-icon fas fa-file-contract"></i>
          <h5>No contracts found</h5>
          <p class="text-muted">Click "New Contract/AMC" to create your first contract</p>
          <a href="/admin/contracts-amc/create" class="btn btn-tenant-primary mt-2">Create Contract</a>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover aps-cp-table mb-0">
            <thead class="tenant-header">
              <tr>
                <th>#</th>
                <th>Contract #</th>
                <th>Type</th>
                <th>Party</th>
                <th>Period</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Days Left</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($contracts as $c): ?>
                <tr>
                  <td><?= (int)$c['id'] ?></td>
                  <td>
                    <a href="/admin/contracts-amc/<?= (int)$c['id'] ?>" class="text-decoration-none fw-bold">
                      <?= htmlspecialchars($c['contract_number'] ?? '') ?>
                    </a>
                  </td>
                  <td>
                    <span class="aps-cp-pill aps-cp-pill-<?= $c['type'] === 'amc' ? 'primary' : ($c['type'] === 'rental' ? 'success' : ($c['type'] === 'service' ? 'info' : ($c['type'] === 'maintenance' ? 'warning' : 'secondary'))) ?>">
                      <?= ucfirst($c['type']) ?>
                    </span>
                  </td>
                  <td>
                    <div class="fw-medium"><?= htmlspecialchars($c['party_name']) ?></div>
                    <small class="text-muted"><?= ucfirst($c['party_type']) ?><?= $c['party_id'] ? " (ID: {$c['party_id']})" : '' ?></small>
                  </td>
                  <td>
                    <small><?= date('d M Y', strtotime($c['start_date'])) ?> - <?= date('d M Y', strtotime($c['end_date'])) ?></small>
                    <?php if ($c['billing_cycle']): ?>
                      <br><span class="badge bg-light text-dark"><?= ucfirst(str_replace('_', ' ', $c['billing_cycle'])) ?></span>
                    <?php endif; ?>
                  </td>
                  <td class="fw-medium"><?= $c['currency'] === 'INR' ? '₹' : '$' ?><?= number_format((float)$c['amount'], 2) ?></td>
                  <td>
                    <?php
                      $statusClass = match($c['status']) {
                        'active' => 'aps-cp-stat--green',
                        'expiring_soon' => 'aps-cp-stat--orange',
                        'expired' => 'aps-cp-stat--red',
                        'renewed' => 'aps-cp-stat--blue',
                        'cancelled' => 'aps-cp-stat--purple',
                        default => 'aps-cp-stat--indigo'
                      };
                    ?>
                    <span class="aps-cp-badge aps-cp-badge-<?= $c['status'] === 'active' ? 'green' : ($c['status'] === 'expiring_soon' ? 'orange' : ($c['status'] === 'expired' ? 'red' : ($c['status'] === 'renewed' ? 'blue' : ($c['status'] === 'cancelled' ? 'purple' : 'indigo')))) ?>">
                      <?= ucfirst(str_replace('_', ' ', $c['status'])) ?>
                    </span>
                  </td>
                  <td>
                    <?php if ($c['is_expired']): ?>
                      <span class="text-danger fw-bold"><i class="fas fa-exclamation-triangle me-1"></i> Overdue <?= abs($c['days_to_expiry']) ?> days</span>
                    <?php elseif ($c['is_expiring']): ?>
                      <span class="text-warning fw-bold"><i class="fas fa-clock me-1"></i> <?= $c['days_to_expiry'] ?> days</span>
                    <?php else: ?>
                      <span class="text-success"><?= $c['days_to_expiry'] ?> days</span>
                    <?php endif; ?>
                    <?php if ($c['auto_renew']): ?>
                      <span class="badge bg-info ms-1"><i class="fas fa-sync me-1"></i> Auto</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <div class="btn-group btn-group-sm">
                      <a href="/admin/contracts-amc/<?= (int)$c['id'] ?>" class="btn btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                      <a href="/admin/contracts-amc/<?= (int)$c['id'] ?>/edit" class="btn btn-outline-secondary" title="Edit"><i class="fas fa-edit"></i></a>
                      <?php if ($c['status'] === 'active' || $c['status'] === 'expiring_soon' || $c['status'] === 'expired'): ?>
                        <form method="POST" action="/admin/contracts-amc/<?= (int)$c['id'] ?>/renew" class="d-inline" onsubmit="return confirm('Renew this contract?')">
                          <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                          <button type="submit" class="btn btn-outline-success" title="Renew"><i class="fas fa-sync"></i></button>
                        </form>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <?php if ($pagination['total_pages'] > 1): ?>
          <div class="card-footer">
            <nav aria-label="Contracts pagination">
              <ul class="pagination justify-content-center mb-0">
                <?php
                  $page = $pagination['page'];
                  $total = $pagination['total_pages'];
                  $base = '/admin/contracts-amc?' . http_build_query(array_filter($filters));
                ?>
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                  <a class="page-link" href="<?= $page > 1 ? $base . '&page=' . ($page - 1) : '#' ?>">&laquo;</a>
                </li>
                <?php for ($i = max(1, $page - 2); $i <= min($total, $page + 2); $i++): ?>
                  <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a class="page-link" href="<?= $base . '&page=' . $i ?>"><?= $i ?></a>
                  </li>
                <?php endfor; ?>
                <li class="page-item <?= $page >= $total ? 'disabled' : '' ?>">
                  <a class="page-link" href="<?= $page < $total ? $base . '&page=' . ($page + 1) : '#' ?>">&raquo;</a>
                </li>
              </ul>
            </nav>
          </div>
        <?php endif; ?>
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