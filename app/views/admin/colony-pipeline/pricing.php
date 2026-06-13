<?php
$colony = $colony ?? [];
$plotStats = $plot_stats ?? [];
$devCosts = $dev_costs ?? [];
$totalDevCost = (float)($total_dev_cost ?? 0);
$priceBands = $price_bands ?? [];
?>
<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-1">Pricing</h1>
      <span class="text-muted"><?= htmlspecialchars($colony['name'] ?? '') ?></span>
    </div>
    <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= (int)($colony['id'] ?? 0) ?>" class="btn btn-outline-secondary btn-sm">
      <i class="fas fa-arrow-left me-1"></i>Back to Colony
    </a>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-md-2">
      <div class="card aps-cp-card">
        <div class="card-body text-center">
          <div class="fs-3 text-primary"><?= number_format((int)($plotStats['total'] ?? 0)) ?></div>
          <div class="text-muted small">Total Plots</div>
        </div>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card aps-cp-card">
        <div class="card-body text-center">
          <div class="fs-3 text-info">₹<?= number_format((float)($plotStats['avg_ppsf'] ?? 0), 0) ?></div>
          <div class="text-muted small">Avg ₹/sqft</div>
        </div>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card aps-cp-card">
        <div class="card-body text-center">
          <div class="fs-3 text-success">₹<?= number_format((float)($plotStats['min_price'] ?? 0) / 100000, 1) ?>L</div>
          <div class="text-muted small">Min Price</div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card aps-cp-card">
        <div class="card-body text-center">
          <div class="fs-3 text-danger">₹<?= number_format((float)($plotStats['max_price'] ?? 0) / 100000, 1) ?>L</div>
          <div class="text-muted small">Max Price</div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card aps-cp-card">
        <div class="card-body text-center">
          <div class="fs-3 text-warning">₹<?= number_format((float)($plotStats['total_value'] ?? 0) / 10000000, 2) ?> Cr</div>
          <div class="text-muted small">Total Value</div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-md-6">
      <div class="card aps-cp-card mb-4">
        <div class="card-header aps-cp-card-header"><strong><i class="fas fa-tools me-2"></i>Development Costs</strong></div>
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead><tr><th>Cost Type</th><th>Amount</th><th>GST</th><th>Total</th></tr></thead>
            <tbody>
              <?php if (empty($devCosts)): ?>
                <tr><td colspan="4" class="text-center text-muted py-3">No development costs recorded.</td></tr>
              <?php else: ?>
                <?php foreach ($devCosts as $dc): ?>
                  <?php $dcTotal = (float)($dc['total_amount'] ?? 0) + (float)($dc['total_gst'] ?? 0); ?>
                  <tr>
                    <td><?= htmlspecialchars(ucwords(str_replace('_', ' ', $dc['cost_type'] ?? ''))) ?></td>
                    <td>₹<?= number_format((float)($dc['total_amount'] ?? 0), 0) ?></td>
                    <td>₹<?= number_format((float)($dc['total_gst'] ?? 0), 0) ?></td>
                    <td><strong>₹<?= number_format($dcTotal, 0) ?></strong></td>
                  </tr>
                <?php endforeach; ?>
                <tr class="table-active">
                  <td><strong>Total</strong></td>
                  <td colspan="2"></td>
                  <td><strong>₹<?= number_format($totalDevCost, 0) ?></strong></td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <?php if (!empty($priceBands)): ?>
      <div class="card aps-cp-card mb-4">
        <div class="card-header aps-cp-card-header"><strong><i class="fas fa-chart-bar me-2"></i>Price Band Distribution</strong></div>
        <div class="card-body aps-cp-card-body">
          <?php foreach ($priceBands as $band): ?>
            <?php
              $bc = (int)($band['plot_count'] ?? 0);
              $totalPlots = (int)($plotStats['total'] ?? 1);
              $pct = $totalPlots > 0 ? round(($bc / $totalPlots) * 100) : 0;
            ?>
            <div class="mb-2">
              <div class="d-flex justify-content-between mb-1">
                <span class="small"><?= htmlspecialchars($band['price_band'] ?? '') ?></span>
                <span class="small fw-bold"><?= $bc ?> plots (<?= $pct ?>%)</span>
              </div>
              <div class="progress" style="height: 18px;">
                <div class="progress-bar bg-info" style="width: <?= $pct ?>%"></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <div class="card aps-cp-card mb-4">
        <div class="card-header aps-cp-card-header"><strong><i class="fas fa-calculator me-2"></i>Calculate Pricing</strong></div>
        <div class="card-body aps-cp-card-body text-center">
          <p class="text-muted mb-3">Auto-calculate plot prices based on development costs, location, and market rates.</p>
          <form method="post" action="<?= BASE_URL ?>/admin/colony-pipeline/<?= (int)($colony['id'] ?? 0) ?>/pricing/calculate" class="d-inline">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
            <button type="submit" class="btn btn-info">
              <i class="fas fa-calculator me-1"></i>Calculate Pricing
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="card aps-cp-card">
    <div class="card-header aps-cp-card-header"><strong><i class="fas fa-tag me-2"></i>Apply Pricing</strong></div>
    <div class="card-body aps-cp-card-body">
      <form method="post" action="<?= BASE_URL ?>/admin/colony-pipeline/<?= (int)($colony['id'] ?? 0) ?>/pricing/apply">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
        <div class="row g-3 align-items-end">
          <div class="col-md-3">
            <label class="form-label">Base Price Per Sqft (₹)</label>
            <input type="number" name="base_price_per_sqft" class="form-control" value="2500" min="500" max="100000" step="50" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Corner Plot Premium %</label>
            <input type="number" name="corner_premium_pct" class="form-control" value="10" min="0" max="50" step="1">
          </div>
          <div class="col-md-3">
            <label class="form-label">Park Facing Premium %</label>
            <input type="number" name="park_facing_premium_pct" class="form-control" value="5" min="0" max="30" step="1">
          </div>
          <div class="col-md-3">
            <button type="submit" class="btn btn-primary w-100" onclick="return confirm('Apply this pricing to all plots?');">
              <i class="fas fa-check me-1"></i>Apply Pricing
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
