<?php
$stats = $stats ?? [];
$virtualTours = (int)($stats['virtual_tours'] ?? 0);
$properties = (int)($stats['properties'] ?? 0);
$recentActivities = $stats['recent_activities'] ?? [];
?>
<div class="container-fluid py-4">
  <h1 class="h3 mb-4">Custom Features Dashboard</h1>

  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card aps-cp-card">
        <div class="card-body text-center">
          <div class="fs-1 text-primary"><?= $virtualTours ?></div>
          <div class="text-muted">Virtual Tours</div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card aps-cp-card">
        <div class="card-body text-center">
          <div class="fs-1 text-info"><?= number_format($properties) ?></div>
          <div class="text-muted">Active Properties</div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card aps-cp-card">
        <div class="card-body text-center">
          <div class="fs-1 text-success"><?= count($recentActivities) ?></div>
          <div class="text-muted">Activities (24h)</div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card aps-cp-card">
        <div class="card-body text-center">
          <div class="fs-1 text-warning">2</div>
          <div class="text-muted">Active Features</div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-md-6">
      <div class="card border-0 shadow-sm">
        <div class="card-body text-center p-5">
          <i class="fas fa-map-marked-alt fa-3x text-primary mb-3"></i>
          <h5>Neighborhood Analytics</h5>
          <p class="text-muted">Analyze property neighborhood data including nearby amenities, price trends, and market analysis</p>
          <a href="<?= $base ?? BASE_URL ?>/admin/custom-features/neighborhood" class="btn btn-primary">
            <i class="fas fa-arrow-right me-1"></i> Open
          </a>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card border-0 shadow-sm">
        <div class="card-body text-center p-5">
          <i class="fas fa-calculator fa-3x text-success mb-3"></i>
          <h5>Investment Calculator</h5>
          <p class="text-muted">Calculate property investment returns including EMI, ROI, break-even analysis, and more</p>
          <a href="<?= $base ?? BASE_URL ?>/admin/custom-features/investment-calculator" class="btn btn-success">
            <i class="fas fa-arrow-right me-1"></i> Open
          </a>
        </div>
      </div>
    </div>
  </div>

  <?php if (!empty($recentActivities)): ?>
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom">
      <h5 class="mb-0">Recent Activities</h5>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Action</th>
              <th>Description</th>
              <th>Time</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recentActivities as $act): ?>
            <tr>
              <td><span class="badge bg-info"><?= htmlspecialchars($act['action'] ?? '') ?></span></td>
              <td><?= htmlspecialchars($act['description'] ?? '') ?></td>
              <td><?= htmlspecialchars($act['created_at'] ?? '') ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>
