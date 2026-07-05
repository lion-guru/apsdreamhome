<?php
$colony = $colony ?? [];
$plotStats = $plot_stats ?? [];
$devCosts = $dev_costs ?? [];
$totalDevCost = (float)($total_dev_cost ?? 0);
$priceBands = $price_bands ?? [];
$blockList = $block_list ?? [];
$phaseList = $phase_list ?? [];
$pendingApprovals = $pending_approvals ?? [];
$approvalHistory = $approval_history ?? [];
$recentlyOverridden = $recently_overridden ?? [];
$minPpsf = (float)($colony['min_price_per_sqft'] ?? 0);
$colonyId = (int)($colony['id'] ?? 0);
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$postUrl = $baseUrl . '/admin/colony-pipeline/' . $colonyId . '/pricing/apply';
$calcUrl = $baseUrl . '/admin/colony-pipeline/' . $colonyId . '/pricing/calculate';
?>
<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-1"><?= __('cp_pricing') ?></h1>
      <span class="text-muted"><?= htmlspecialchars($colony['name'] ?? '') ?></span>
      <?php if ($minPpsf > 0): ?>
        <span class="badge bg-info ms-2"><?= __('cp_min_price_guard') ?>: ₹<?= number_format($minPpsf, 2) ?>/sqft</span>
      <?php endif; ?>
    </div>
    <div>
      <a href="<?= $baseUrl ?>/admin/colony-feasibility/<?= $colonyId ?>" class="btn btn-outline-primary btn-sm me-2">
        <i class="fas fa-calculator me-1"></i><?= __('cp_feasibility_calc') ?>
      </a>
      <a href="<?= $baseUrl ?>/admin/colony-pipeline/<?= $colonyId ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i><?= __('cp_back_to_colony') ?>
      </a>
    </div>
  </div>

  <!-- ── Stats Cards ────────────────────────────────────── -->
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
    <!-- ── Left Column ──────────────────────────────────── -->
    <div class="col-md-6">
      <!-- Dev Costs -->
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

      <!-- Calculate Pricing -->
      <div class="card aps-cp-card mb-4">
        <div class="card-header aps-cp-card-header"><strong><i class="fas fa-calculator me-2"></i><?= __('cp_calculate_pricing') ?></strong></div>
        <div class="card-body aps-cp-card-body text-center">
          <p class="text-muted mb-3"><?= __('cp_auto_calculate') ?></p>
          <form method="post" action="<?= $calcUrl ?>" class="d-inline">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
            <button type="submit" class="btn btn-info">
              <i class="fas fa-calculator me-1"></i><?= __('cp_calculate_pricing') ?>
            </button>
          </form>
          <?php if ($minPpsf > 0): ?>
            <div class="alert alert-info mt-3 mb-0 py-2 small">
              <i class="fas fa-shield-alt me-1"></i>
              <?= __('cp_min_price_guard') ?>: <strong>₹<?= number_format($minPpsf, 2) ?>/sqft</strong>
              — <?= __('cp_breakeven_explain') ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- ── Pending Approvals ─────────────────────────────── -->
      <?php if (!empty($pendingApprovals)): ?>
      <div class="card aps-cp-card mb-4 border-warning">
        <div class="card-header aps-cp-card-header bg-warning text-dark">
          <strong><i class="fas fa-clock me-2"></i><?= __('cp_pending_approvals') ?> (<?= count($pendingApprovals) ?>)</strong>
        </div>
        <div class="card-body aps-cp-card-body p-0">
          <?php foreach ($pendingApprovals as $pa): ?>
            <div class="border-bottom p-3">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <strong><?= __('cp_plot') ?> #<?= htmlspecialchars($pa['plot_number'] ?? '') ?></strong>
                  (<?= htmlspecialchars($pa['block'] ?? '') ?>) — <?= number_format((float)($pa['area_sqft'] ?? 0)) ?> sqft
                  <br>
                  <span class="text-muted small">
                    <?= __('cp_current') ?>: ₹<?= number_format((float)($pa['current_price_per_sqft'] ?? 0), 2) ?>/sqft →
                    <span class="text-danger"><strong>₹<?= number_format((float)($pa['requested_price_per_sqft'] ?? 0), 2) ?>/sqft</strong></span>
                    (<?= round(((float)($pa['current_price_per_sqft'] ?? 0) - (float)($pa['requested_price_per_sqft'] ?? 0)) / max((float)($pa['current_price_per_sqft'] ?? 1), 1) * 100, 1) ?>% <?= __('cp_discount') ?>)
                  </span>
                  <?php if (!empty($pa['reason'])): ?>
                    <br><em class="small text-muted">"<?= htmlspecialchars($pa['reason']) ?>"</em>
                  <?php endif; ?>
                  <br><span class="small text-muted"><?= __('cp_by') ?>: <?= htmlspecialchars($pa['requested_by_name'] ?? '') ?> — <?= htmlspecialchars($pa['requested_at'] ?? '') ?></span>
                </div>
                <div class="d-flex gap-1">
                  <form method="post" action="<?= $postUrl ?>" class="d-inline">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                    <input type="hidden" name="sub_action" value="approve_discount">
                    <input type="hidden" name="approval_id" value="<?= (int)($pa['id'] ?? 0) ?>">
                    <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('<?= __('cp_confirm_approve') ?>');">
                      <i class="fas fa-check"></i>
                    </button>
                  </form>
                  <form method="post" action="<?= $postUrl ?>" class="d-inline">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                    <input type="hidden" name="sub_action" value="reject_discount">
                    <input type="hidden" name="approval_id" value="<?= (int)($pa['id'] ?? 0) ?>">
                    <div class="input-group input-group-sm">
                      <input type="text" name="approval_notes" class="form-control form-control-sm" placeholder="<?= __('cp_reason') ?>" style="width:100px">
                      <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('<?= __('cp_confirm_reject') ?>');">
                        <i class="fas fa-times"></i>
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <!-- ── Right Column ─────────────────────────────────── -->
    <div class="col-md-6">
      <!-- Price Band Distribution -->
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

      <!-- ── Apply Pricing Form ───────────────────────────── -->
      <div class="card aps-cp-card mb-4">
        <div class="card-header aps-cp-card-header"><strong><i class="fas fa-tag me-2"></i><?= __('cp_apply_pricing') ?></strong></div>
        <div class="card-body aps-cp-card-body">
          <form method="post" action="<?= $postUrl ?>">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
            <input type="hidden" name="sub_action" value="apply_all">

            <div class="row g-3 align-items-end">
              <div class="col-md-4">
                <label class="form-label"><?= __('cp_base_price_per_sqft') ?> <i class="fas fa-info-circle text-muted small" title="<?= __('cp_base_price_help') ?>"></i></label>
                <input type="number" name="base_price_per_sqft" class="form-control" value="2500" min="500" max="100000" step="50" required>
                <?php if ($minPpsf > 0): ?>
                  <small class="text-muted"><?= __('cp_min_floor') ?>: ₹<?= number_format($minPpsf, 2) ?></small>
                <?php endif; ?>
              </div>
              <div class="col-md-2">
                <label class="form-label"><?= __('cp_corner_plot') ?> %</label>
                <input type="number" name="corner_premium_pct" class="form-control" value="10" min="0" max="50" step="1">
              </div>
              <div class="col-md-2">
                <label class="form-label"><?= __('cp_park_facing') ?> %</label>
                <input type="number" name="park_facing_premium_pct" class="form-control" value="15" min="0" max="50" step="1">
              </div>
              <div class="col-md-2">
                <label class="form-label"><?= __('cp_wide_road') ?> %</label>
                <input type="number" name="wide_road_premium_pct" class="form-control" value="8" min="0" max="50" step="1">
              </div>
              <div class="col-md-2">
                <label class="form-label"><?= __('cp_road_threshold') ?> ft</label>
                <input type="number" name="wide_road_threshold" class="form-control" value="40" min="20" max="100" step="5">
              </div>
            </div>

            <!-- Block Premiums -->
            <?php if (!empty($blockList)): ?>
            <div class="mt-3">
              <label class="form-label fw-bold small"><?= __('cp_block_premiums') ?></label>
              <div class="row g-2">
                <?php foreach ($blockList as $blk): ?>
                  <div class="col-md-3">
                    <label class="small"><?= __('cp_block') ?> <?= htmlspecialchars($blk['block'] ?? '') ?> (%)</label>
                    <input type="number" name="block_premium_<?= htmlspecialchars($blk['block'] ?? '') ?>" class="form-control form-control-sm" value="0" min="0" max="50" step="1">
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
            <?php endif; ?>

            <!-- Phase Premiums -->
            <?php if (!empty($phaseList)): ?>
            <div class="mt-3">
              <label class="form-label fw-bold small"><?= __('cp_phase_premiums') ?></label>
              <div class="row g-2">
                <?php foreach ($phaseList as $ph): ?>
                  <div class="col-md-3">
                    <label class="small"><?= __('cp_phase') ?> <?= htmlspecialchars($ph['phase'] ?? '') ?> (%)</label>
                    <input type="number" name="phase_premium_<?= htmlspecialchars($ph['phase'] ?? '') ?>" class="form-control form-control-sm" value="0" min="0" max="50" step="1">
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
            <?php endif; ?>

            <div class="mt-3">
              <button type="submit" class="btn btn-primary w-100" onclick="return confirm('<?= __('cp_confirm_apply') ?>');">
                <i class="fas fa-check me-1"></i><?= __('cp_apply_pricing') ?>
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- ── Single Plot Price Override ────────────────────── -->
      <div class="card aps-cp-card mb-4">
        <div class="card-header aps-cp-card-header"><strong><i class="fas fa-edit me-2"></i><?= __('cp_single_plot_override') ?></strong></div>
        <div class="card-body aps-cp-card-body">
          <form method="post" action="<?= $postUrl ?>" class="row g-2 align-items-end">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
            <input type="hidden" name="sub_action" value="update_single_plot">
            <div class="col-md-3">
              <label class="form-label small"><?= __('cp_plot_id') ?></label>
              <input type="number" name="plot_id" class="form-control" placeholder="ID" required min="1">
            </div>
            <div class="col-md-3">
              <label class="form-label small"><?= __('cp_new_price_psf') ?></label>
              <input type="number" name="new_price_per_sqft" class="form-control" placeholder="₹/sqft" required min="1" step="0.01">
            </div>
            <div class="col-md-4">
              <label class="form-label small"><?= __('cp_reason') ?></label>
              <input type="text" name="price_reason" class="form-control" placeholder="<?= __('cp_override_reason_placeholder') ?>">
            </div>
            <div class="col-md-2">
              <button type="submit" class="btn btn-warning w-100 btn-sm"><?= __('cp_update') ?></button>
            </div>
          </form>
        </div>
      </div>

      <!-- ── Request Discount (Below Min Price) ───────────── -->
      <div class="card aps-cp-card mb-4">
        <div class="card-header aps-cp-card-header"><strong><i class="fas fa-percent me-2"></i><?= __('cp_request_discount') ?></strong></div>
        <div class="card-body aps-cp-card-body">
          <form method="post" action="<?= $postUrl ?>" class="row g-2 align-items-end">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
            <input type="hidden" name="sub_action" value="request_discount">
            <div class="col-md-3">
              <label class="form-label small"><?= __('cp_plot_id') ?></label>
              <input type="number" name="plot_id" class="form-control" placeholder="ID" required min="1">
            </div>
            <div class="col-md-3">
              <label class="form-label small"><?= __('cp_requested_price_psf') ?></label>
              <input type="number" name="requested_price_per_sqft" class="form-control" placeholder="₹/sqft" required min="1" step="0.01">
            </div>
            <div class="col-md-4">
              <label class="form-label small"><?= __('cp_reason') ?></label>
              <input type="text" name="discount_reason" class="form-control" placeholder="<?= __('cp_discount_reason_placeholder') ?>">
            </div>
            <div class="col-md-2">
              <button type="submit" class="btn btn-info w-100 btn-sm"><?= __('cp_request') ?></button>
            </div>
          </form>
        </div>
      </div>

      <!-- ── Approval History ─────────────────────────────── -->
      <?php if (!empty($approvalHistory)): ?>
      <div class="card aps-cp-card mb-4">
        <div class="card-header aps-cp-card-header"><strong><i class="fas fa-history me-2"></i><?= __('cp_approval_history') ?></strong></div>
        <div class="table-responsive">
          <table class="table table-sm mb-0">
            <thead><tr><th><?= __('cp_plot') ?></th><th><?= __('cp_requested') ?></th><th><?= __('cp_status') ?></th><th><?= __('cp_by') ?></th><th><?= __('cp_date') ?></th></tr></thead>
            <tbody>
              <?php foreach ($approvalHistory as $ah): ?>
                <tr>
                  <td><?= htmlspecialchars($ah['plot_number'] ?? '') ?> (<?= htmlspecialchars($ah['block'] ?? '') ?>)</td>
                  <td>₹<?= number_format((float)($ah['requested_price_per_sqft'] ?? 0), 0) ?>/sf</td>
                  <td>
                    <?php if ($ah['status'] === 'approved'): ?>
                      <span class="badge bg-success"><?= __('cp_approved') ?></span>
                    <?php elseif ($ah['status'] === 'rejected'): ?>
                      <span class="badge bg-danger"><?= __('cp_rejected') ?></span>
                    <?php else: ?>
                      <span class="badge bg-warning text-dark"><?= __('cp_pending') ?></span>
                    <?php endif; ?>
                  </td>
                  <td class="small"><?= htmlspecialchars($ah['approved_by_name'] ?? $ah['requested_by_name'] ?? '') ?></td>
                  <td class="small"><?= htmlspecialchars($ah['approved_at'] ?? $ah['requested_at'] ?? '') ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endif; ?>

      <!-- ── Recently Overridden Plots ────────────────────── -->
      <?php if (!empty($recentlyOverridden)): ?>
      <div class="card aps-cp-card mb-4">
        <div class="card-header aps-cp-card-header"><strong><i class="fas fa-exclamation-triangle me-2"></i><?= __('cp_overridden_plots') ?></strong></div>
        <div class="table-responsive">
          <table class="table table-sm mb-0">
            <thead><tr><th><?= __('cp_plot') ?></th><th><?= __('cp_price') ?></th><th><?= __('cp_negotiated') ?></th><th><?= __('cp_reason') ?></th><th><?= __('cp_approved') ?></th></tr></thead>
            <tbody>
              <?php foreach ($recentlyOverridden as $ro): ?>
                <tr>
                  <td><?= htmlspecialchars($ro['plot_number'] ?? '') ?> (<?= htmlspecialchars($ro['block'] ?? '') ?>)</td>
                  <td>₹<?= number_format((float)($ro['price_per_sqft'] ?? 0), 0) ?></td>
                  <td>₹<?= number_format((float)($ro['negotiated_price'] ?? 0), 0) ?></td>
                  <td class="small"><?= htmlspecialchars(mb_substr($ro['price_override_reason'] ?? '', 0, 40)) ?></td>
                  <td><?= !empty($ro['negotiated_price_approved']) ? '<span class="badge bg-success">'.__('cp_yes').'</span>' : '<span class="badge bg-secondary">'.__('cp_no').'</span>' ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
