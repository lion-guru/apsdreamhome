<?php
$page_title = $page_title ?? 'Property Maintenance';
$page_heading = $page_heading ?? 'Property Maintenance';
$content = $content ?? '';
ob_start();
?>
<div class="container-fluid py-4">
  <h1 class="h3 mb-4"><i class="fas fa-tools me-2"></i>Property Maintenance</h1>

  <div class="row">
    <div class="col-md-3"><div class="card border-left-primary shadow-sm"><div class="card-body aps-cp-card-body"><h6 class="text-muted">Active</h6><h2 class="mb-0"><?= count(array_filter($maintenance ?? [], fn($m) => ($m['status'] ?? '') === 'scheduled')) ?></h2></div></div></div>
    <div class="col-md-3"><div class="card border-left-success shadow-sm"><div class="card-body aps-cp-card-body"><h6 class="text-muted">Completed</h6><h2 class="mb-0"><?= count(array_filter($maintenance ?? [], fn($m) => ($m['status'] ?? '') === 'completed')) ?></h2></div></div></div>
    <div class="col-md-3"><div class="card border-left-warning shadow-sm"><div class="card-body aps-cp-card-body"><h6 class="text-muted">Total Cost</h6><h2 class="mb-0">₹<?= number_format(array_sum(array_column($maintenance ?? [], 'estimated_cost')), 0) ?></h2></div></div></div>
    <div class="col-md-3"><div class="card border-left-info shadow-sm"><div class="card-body aps-cp-card-body"><h6 class="text-muted">Market Records</h6><h2 class="mb-0"><?= count($marketData ?? []) ?></h2></div></div></div>
  </div>

  <ul class="nav nav-tabs mb-3 mt-3">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#mn">Maintenance Schedule</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#mkt">Market Data</button></li>
  </ul>

  <div class="tab-content">
    <div class="tab-pane fade show active" id="mn">
      <div class="card shadow-sm"><div class="card-body p-0">
        <div class="table-responsive"><table class="table mb-0">
          <thead class="table-light"><tr><th>Property</th><th>Type</th><th>Description</th><th>Scheduled</th><th>Est. Cost</th><th>Status</th></tr></thead>
          <tbody>
            <?php if (empty($maintenance)): ?>
              <tr><td colspan="6" class="text-center py-3 text-muted">No maintenance scheduled</td></tr>
            <?php else: foreach ($maintenance as $m): ?>
              <tr>
                <td><?= htmlspecialchars($m['property_title'] ?? 'N/A') ?></td>
                <td><span class="badge bg-info"><?= htmlspecialchars($m['maintenance_type'] ?? '') ?></span></td>
                <td><small><?= htmlspecialchars($m['description'] ?? '') ?></small></td>
                <td><small><?= htmlspecialchars($m['scheduled_date'] ?? '') ?></small></td>
                <td>₹<?= number_format((float)($m['estimated_cost'] ?? 0), 0) ?></td>
                <td><span class="badge bg-<?= ($m['status'] ?? '') === 'completed' ? 'success' : 'warning' ?>"><?= htmlspecialchars($m['status'] ?? '') ?></span></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table></div>
      </div></div>
    </div>

    <div class="tab-pane fade" id="mkt">
      <div class="card shadow-sm"><div class="card-body p-0">
        <div class="table-responsive"><table class="table mb-0">
          <thead class="table-light"><tr><th>Date</th><th>District</th><th>Type</th><th>Avg Price</th><th>Listings</th><th>Sold</th><th>% Change</th></tr></thead>
          <tbody>
            <?php if (empty($marketData)): ?>
              <tr><td colspan="7" class="text-center py-3 text-muted">No market data</td></tr>
            <?php else: foreach ($marketData as $md): ?>
              <tr>
                <td><small><?= htmlspecialchars($md['created_at'] ?? '') ?></small></td>
                <td><?= htmlspecialchars($md['district_id'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($md['property_type'] ?? '') ?></td>
                <td>₹<?= number_format((float)($md['avg_price'] ?? 0), 0) ?></td>
                <td><?= htmlspecialchars($md['total_listings'] ?? 0) ?></td>
                <td><?= htmlspecialchars($md['total_sold'] ?? 0) ?></td>
                <td><?= htmlspecialchars($md['price_change_pct'] ?? 0) ?>%</td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table></div>
      </div></div>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/admin/layouts/admin.php';
