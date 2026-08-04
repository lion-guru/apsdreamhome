<?php $this->layout = 'layouts/admin'; ?>

<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Telecaller Performance</h1>
  </div>

  <div class="row mb-3">
    <div class="col-12">
      <a href="/admin/leads" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Back to Leads
      </a>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-header"><h5 class="mb-0">All Telecallers</h5></div>
    <div class="card-body">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>#</th><th>Name</th><th>Role</th><th>Leads Assigned</th>
            <th>Conversions</th><th>Conversion Rate</th><th>Avg Score</th><th>Last Activity</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($telecallers as $i => $t): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td><?= htmlspecialchars($t['name']) ?></td>
            <td><?= htmlspecialchars(ucfirst($t['role'])) ?></td>
            <td><?= $t['leads_assigned'] ?: 0 ?></td>
            <td><?= $t['conversions'] ?: 0 ?></td>
            <td><?= $t['leads_assigned'] ? round(($t['conversions'] / $t['leads_assigned']) * 100, 1) . '%' : '-' ?></td>
            <td><?= $t['avg_score'] ?? '-' ?></td>
            <td><?= $t['last_activity'] ?? '-' ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
