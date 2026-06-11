<?php
$page_title = $page_title ?? 'Analytics & KPIs';
$page_heading = $page_heading ?? 'Analytics & KPIs';
$content = $content ?? '';
ob_start();
?>
<div class="container-fluid py-4">
  <h1 class="h3 mb-4"><i class="fas fa-chart-line me-2"></i>Analytics & KPIs</h1>

  <div class="row mb-3">
    <div class="col-md-2"><div class="card border-left-primary shadow-sm"><div class="card-body aps-cp-card-body"><h6 class="text-muted small">Customers</h6><h4 class="mb-0"><?= $comprehensive['customers'] ?? 0 ?></h4></div></div></div>
    <div class="col-md-2"><div class="card border-left-info shadow-sm"><div class="card-body aps-cp-card-body"><h6 class="text-muted small">Agents</h6><h4 class="mb-0"><?= $comprehensive['agents'] ?? 0 ?></h4></div></div></div>
    <div class="col-md-2"><div class="card border-left-success shadow-sm"><div class="card-body aps-cp-card-body"><h6 class="text-muted small">Associates</h6><h4 class="mb-0"><?= $comprehensive['associates'] ?? 0 ?></h4></div></div></div>
    <div class="col-md-2"><div class="card border-left-warning shadow-sm"><div class="card-body aps-cp-card-body"><h6 class="text-muted small">Leads (30d)</h6><h4 class="mb-0"><?= $comprehensive['leads_30d'] ?? 0 ?></h4></div></div></div>
    <div class="col-md-2"><div class="card border-left-danger shadow-sm"><div class="card-body aps-cp-card-body"><h6 class="text-muted small">Bookings (30d)</h6><h4 class="mb-0"><?= $comprehensive['bookings_30d'] ?? 0 ?></h4></div></div></div>
    <div class="col-md-2"><div class="card border-left-secondary shadow-sm"><div class="card-body aps-cp-card-body"><h6 class="text-muted small">Revenue (30d)</h6><h4 class="mb-0">₹<?= number_format(($comprehensive['revenue_30d'] ?? 0)/1000, 0) ?>k</h4></div></div></div>
  </div>

  <ul class="nav nav-tabs mb-3">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#kpi">KPIs</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#fc">Forecasts</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#dash">Dashboards</button></li>
  </ul>

  <div class="tab-content">
    <div class="tab-pane fade show active" id="kpi">
      <div class="card shadow-sm"><div class="card-body p-0">
        <div class="table-responsive"><table class="table mb-0">
          <thead class="table-light"><tr><th>Code</th><th>Name</th><th>Category</th><th>Unit</th><th>Target</th><th>Frequency</th></tr></thead>
          <tbody>
            <?php if (empty($kpis)): ?>
              <tr><td colspan="6" class="text-center py-3 text-muted">No KPIs</td></tr>
            <?php else: foreach ($kpis as $k): ?>
              <tr>
                <td><code><?= htmlspecialchars($k['kpi_code'] ?? '') ?></code></td>
                <td><?= htmlspecialchars($k['name'] ?? '') ?></td>
                <td><span class="badge bg-info"><?= htmlspecialchars($k['category'] ?? '') ?></span></td>
                <td><?= htmlspecialchars($k['unit'] ?? '') ?></td>
                <td><?= htmlspecialchars($k['target_value'] ?? '') ?></td>
                <td><?= htmlspecialchars($k['frequency'] ?? '') ?></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table></div>
      </div></div>
    </div>

    <div class="tab-pane fade" id="fc">
      <div class="card shadow-sm"><div class="card-body p-0">
        <div class="table-responsive"><table class="table mb-0">
          <thead class="table-light"><tr><th>Metric</th><th>Method</th><th>Periods</th><th>R²</th><th>Generated</th></tr></thead>
          <tbody>
            <?php if (empty($forecasts)): ?>
              <tr><td colspan="5" class="text-center py-3 text-muted">No forecasts yet</td></tr>
            <?php else: foreach ($forecasts as $f): ?>
              <tr>
                <td><strong><?= htmlspecialchars($f['metric_name'] ?? '') ?></strong></td>
                <td><span class="badge bg-secondary"><?= htmlspecialchars($f['method'] ?? '') ?></span></td>
                <td><?= htmlspecialchars($f['periods'] ?? '') ?></td>
                <td><?= htmlspecialchars($f['r_squared'] ?? '') ?></td>
                <td><small><?= htmlspecialchars($f['generated_at'] ?? '') ?></small></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table></div>
      </div></div>
    </div>

    <div class="tab-pane fade" id="dash">
      <div class="row">
        <?php if (empty($dashboards)): ?>
          <div class="col-12"><div class="alert alert-info">No custom dashboards created yet</div></div>
        <?php else: foreach ($dashboards as $d): ?>
          <div class="col-md-4 mb-3">
            <div class="card shadow-sm">
              <div class="card-body aps-cp-card-body">
                <h5><?= htmlspecialchars($d['name'] ?? '') ?></h5>
                <p class="text-muted small">Updated: <?= htmlspecialchars($d['updated_at'] ?? '') ?></p>
                <span class="badge bg-<?= ($d['is_public'] ?? 0) ? 'success' : 'secondary' ?>"><?= ($d['is_public'] ?? 0) ? 'Public' : 'Private' ?></span>
              </div>
            </div>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/admin/layouts/unified.php';
