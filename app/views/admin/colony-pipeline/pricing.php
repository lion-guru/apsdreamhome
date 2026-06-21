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
      <h1 class="h3 mb-1"><?= __('cp_pricing') ?></h1>
      <span class="text-muted"><?= htmlspecialchars($colony['name'] ?? '') ?></span>
    </div>
    <div>
      <a href="<?= BASE_URL ?>/admin/colony-feasibility/<?= (int)($colony['id'] ?? 0) ?>" class="btn btn-outline-primary btn-sm me-2">
        <i class="fas fa-calculator me-1"></i><?= __('cp_feasibility_calc') ?>
      </a>
      <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= (int)($colony['id'] ?? 0) ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i><?= __('cp_back_to_colony') ?>
      </a>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-md-2">
      <div class="card aps-cp-card">
        <div class="card-body text-center">
          <div class="fs-3 text-primary"><?= number_format((int)($plotStats['total'] ?? 0)) ?></div>
          <div class="text-muted small"><?= __('cp_total_plots') ?></div>
        </div>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card aps-cp-card">
        <div class="card-body text-center">
          <div class="fs-3 text-info">₹<?= number_format((float)($plotStats['avg_ppsf'] ?? 0), 0) ?></div>
          <div class="text-muted small"><?= __('cp_avg_per_sqft') ?></div>
        </div>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card aps-cp-card">
        <div class="card-body text-center">
          <div class="fs-3 text-success">₹<?= number_format((float)($plotStats['min_price'] ?? 0) / 100000, 1) ?>L</div>
          <div class="text-muted small"><?= __('cp_min_price') ?></div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card aps-cp-card">
        <div class="card-body text-center">
          <div class="fs-3 text-danger">₹<?= number_format((float)($plotStats['max_price'] ?? 0) / 100000, 1) ?>L</div>
          <div class="text-muted small"><?= __('cp_max_price') ?></div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card aps-cp-card">
        <div class="card-body text-center">
          <div class="fs-3 text-warning">₹<?= number_format((float)($plotStats['total_value'] ?? 0) / 10000000, 2) ?> Cr</div>
          <div class="text-muted small"><?= __('cp_total_value') ?></div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-md-6">
      <div class="card aps-cp-card mb-4">
        <div class="card-header aps-cp-card-header"><strong><i class="fas fa-tools me-2"></i><?= __('cp_dev_costs') ?></strong></div>
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead><tr><th><?= __('cp_cost_type') ?></th><th><?= __('cp_amount') ?></th><th><?= __('cp_gst') ?></th><th><?= __('cp_total') ?></th></tr></thead>
            <tbody>
              <?php if (empty($devCosts)): ?>
                <tr><td colspan="4" class="text-center text-muted py-3"><?= __('cp_no_dev_costs') ?></td></tr>
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
                  <td><strong><?= __('cp_total') ?></strong></td>
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
        <div class="card-header aps-cp-card-header"><strong><i class="fas fa-chart-bar me-2"></i><?= __('cp_price_band_distribution') ?></strong></div>
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
                <span class="small fw-bold"><?= $bc ?> <?= __('cp_plots') ?> (<?= $pct ?>%)</span>
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
        <div class="card-header aps-cp-card-header"><strong><i class="fas fa-calculator me-2"></i><?= __('cp_calculate_pricing') ?></strong></div>
        <div class="card-body aps-cp-card-body text-center">
          <p class="text-muted mb-3"><?= __('cp_auto_calculate') ?></p>
          <form method="post" action="<?= BASE_URL ?>/admin/colony-pipeline/<?= (int)($colony['id'] ?? 0) ?>/pricing/calculate" class="d-inline">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
            <button type="submit" class="btn btn-info">
              <i class="fas fa-calculator me-1"></i><?= __('cp_calculate_pricing') ?>
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="card aps-cp-card">
    <div class="card-header aps-cp-card-header"><strong><i class="fas fa-tag me-2"></i><?= __('cp_apply_pricing') ?></strong></div>
    <div class="card-body aps-cp-card-body">
      <form method="post" action="<?= BASE_URL ?>/admin/colony-pipeline/<?= (int)($colony['id'] ?? 0) ?>/pricing/apply">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
        <div class="row g-3 align-items-end">
          <div class="col-md-3">
            <label class="form-label"><?= __('cp_base_price_per_sqft') ?></label>
            <input type="number" name="base_price_per_sqft" class="form-control" value="2500" min="500" max="100000" step="50" required>
          </div>
          <div class="col-md-3">
            <label class="form-label"><?= __('cp_corner_premium') ?></label>
            <input type="number" name="corner_premium_pct" class="form-control" value="10" min="0" max="50" step="1">
          </div>
          <div class="col-md-3">
            <label class="form-label"><?= __('cp_park_facing_premium') ?></label>
            <input type="number" name="park_facing_premium_pct" class="form-control" value="5" min="0" max="30" step="1">
          </div>
          <div class="col-md-3">
            <button type="submit" class="btn btn-primary w-100" onclick="return confirm('<?= __('cp_confirm_apply') ?>');">
              <i class="fas fa-check me-1"></i><?= __('cp_apply_pricing') ?>
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
