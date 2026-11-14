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
            <th scope="col">#</th><th scope="col">Name</th><th scope="col">Role</th><th scope="col">Leads Assigned</th>
            <th scope="col">Conversions</th><th scope="col">Conversion Rate</th><th scope="col">Avg Score</th><th scope="col">Last Activity</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($telecallers as $i => $t): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td><?= htmlspecialchars($t['name'] ?? '') ?></td>
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
