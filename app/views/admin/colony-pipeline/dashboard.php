<?php
$colonies = $colonies ?? [];
$stats = $stats ?? [];
$totalColonies = count($colonies);
$totalPlots = (int)($stats['total_colony_plots'] ?? 0);
$totalAvailable = (int)($stats['total_available'] ?? 0);
$totalValue = (float)($stats['total_value_sum'] ?? 0);
?>
<div class="container-fluid py-4">
  <h1 class="h3 mb-4">Colony Development Pipeline</h1>

  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card aps-cp-card">
        <div class="card-body text-center">
          <div class="fs-1 text-primary"><?= $totalColonies ?></div>
          <div class="text-muted">Total Colonies</div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card aps-cp-card">
        <div class="card-body text-center">
          <div class="fs-1 text-info"><?= number_format($totalPlots) ?></div>
          <div class="text-muted">Total Plots</div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card aps-cp-card">
        <div class="card-body text-center">
          <div class="fs-1 text-success"><?= number_format($totalAvailable) ?></div>
          <div class="text-muted">Available Plots</div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card aps-cp-card">
        <div class="card-body text-center">
          <div class="fs-1 text-warning">₹<?= number_format($totalValue / 10000000, 2) ?> Cr</div>
          <div class="text-muted">Total Value</div>
        </div>
      </div>
    </div>
  </div>

  <div class="card aps-cp-card">
    <div class="card-header aps-cp-card-header"><strong>Colony List</strong></div>
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr>
            <th>Colony Name</th>
            <th>District</th>
            <th>Area (Acres)</th>
            <th>Plots (Total / Available / Booked / Sold)</th>
            <th>Dev Cost</th>
            <th>Layout</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($colonies)): ?>
            <tr><td colspan="8" class="text-center text-muted py-4">No colonies found.</td></tr>
          <?php else: ?>
            <?php foreach ($colonies as $c): ?>
              <tr>
                <td>
                  <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= (int)$c['id'] ?>" class="text-decoration-none fw-semibold">
                    <?= htmlspecialchars($c['name'] ?? '') ?>
                  </a>
                  <br><small class="text-muted"><?= htmlspecialchars($c['colony_code'] ?? '') ?></small>
                </td>
                <td><?= htmlspecialchars($c['district_name'] ?? '') ?></td>
                <td><?= number_format((float)($c['total_area_acres'] ?? 0), 2) ?></td>
                <td>
                  <span class="badge bg-primary"><?= (int)($c['total_plots'] ?? 0) ?></span>
                  <span class="badge bg-success"><?= (int)($c['available_plots'] ?? 0) ?></span>
                  <span class="badge bg-warning text-dark"><?= (int)($c['booked_plots'] ?? 0) ?></span>
                  <span class="badge bg-danger"><?= (int)($c['sold_plots'] ?? 0) ?></span>
                </td>
                <td>₹<?= number_format((float)($c['total_dev_cost'] ?? 0), 0) ?></td>
                <td>
                  <?php if (!empty($c['has_layout'])): ?>
                    <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= (int)$c['id'] ?>/layout" class="badge bg-success text-decoration-none">Layout Ready</a>
                  <?php else: ?>
                    <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= (int)$c['id'] ?>/layout" class="badge bg-warning text-dark text-decoration-none">No Layout</a>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="badge bg-<?= !empty($c['has_layout']) ? 'success' : 'secondary' ?>">
                    <?= !empty($c['has_layout']) ? 'Configured' : 'Pending' ?>
                  </span>
                </td>
                <td>
                  <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= (int)$c['id'] ?>" class="btn btn-outline-primary btn-sm" title="View Details">
                    <i class="fas fa-eye"></i>
                  </a>
                  <?php if (empty($c['has_layout'])): ?>
                    <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= (int)$c['id'] ?>/layout" class="btn btn-outline-success btn-sm" title="Generate Layout">
                      <i class="fas fa-drafting-compass"></i>
                    </a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
