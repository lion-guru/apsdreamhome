<?php
$colony      = $colony ?? [];
$feasibility = $feasibility ?? [];
$pricing     = $pricing ?? [];
$history     = $history ?? [];
$isSuccess   = $feasibility['success'] ?? false;
?>
<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-1">Feasibility Calculator</h1>
      <span class="text-muted"><?= htmlspecialchars($colony['name'] ?? '') ?></span>
    </div>
    <div>
      <a href="<?= BASE_URL ?>/admin/colony-feasibility/<?= (int)($colony['id'] ?? 0) ?>/history"
         class="btn btn-outline-secondary btn-sm me-2">
        <i class="fas fa-history me-1"></i>Audit Log
      </a>
      <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= (int)($colony['id'] ?? 0) ?>/pricing"
         class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Back to Pricing
      </a>
    </div>
  </div>

  <?php if ($isSuccess): ?>
  <!-- ─── RESULT CARDS ──────────────────────────────────────── -->
  <div class="row g-3 mb-4">
    <div class="col-md-2">
      <div class="card aps-cp-card">
        <div class="card-body text-center">
          <div class="fs-3 text-primary">₹<?= number_format($feasibility['recommended_price_ppsf'] ?? 0, 0) ?></div>
          <div class="text-muted small">Recommended ₹/sqft</div>
        </div>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card aps-cp-card">
        <div class="card-body text-center">
          <div class="fs-3 text-info">₹<?= number_format($feasibility['raw_cost_per_sqft'] ?? 0, 0) ?></div>
          <div class="text-muted small">Cost Basis ₹/sqft</div>
        </div>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card aps-cp-card">
        <div class="card-body text-center">
          <div class="fs-3 text-success"><?= $feasibility['markup_factor'] ?? 0 ?>x</div>
          <div class="text-muted small">Markup Factor</div>
        </div>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card aps-cp-card">
        <div class="card-body text-center">
          <div class="fs-3 text-warning">₹<?= number_format(($feasibility['total_revenue_projected'] ?? 0) / 10000000, 2) ?>Cr</div>
          <div class="text-muted small">Revenue Projection</div>
        </div>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card aps-cp-card">
        <div class="card-body text-center">
          <div class="fs-3 text-danger"><?= number_format($feasibility['yield_pct'] ?? 60, 0) ?>%</div>
          <div class="text-muted small">Saleable Yield</div>
        </div>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card aps-cp-card">
        <div class="card-body text-center">
          <div class="fs-3 text-secondary"><?= number_format($feasibility['total_overhead_pct'] ?? 50, 0) ?>%</div>
          <div class="text-muted small">Total Overhead</div>
        </div>
      </div>
    </div>
  </div>

  <!-- ─── COST BREAKDOWN + OVERRIDE FORM ──────────────────── -->
  <div class="row g-3 mb-4">
    <!-- Cost Breakdown -->
    <div class="col-md-6">
      <div class="card aps-cp-card">
        <div class="card-header"><strong>Cost Component Breakdown</strong></div>
        <div class="card-body">
          <div class="table-responsive"><table class="table table-sm mb-0">
            <thead><tr><th>Component</th><th class="text-end">Amount</th><th class="text-end">₹/sqft</th></tr></thead>
            <tbody>
              <tr>
                <td><i class="fas fa-map text-primary me-2"></i>Land (L)</td>
                <td class="text-end">₹<?= number_format($feasibility['land_cost'] ?? 0, 0) ?></td>
                <td class="text-end">
                  <?php
                    $saleable = $feasibility['saleable_area_sqft'] ?? 1;
                    echo '₹' . number_format(($feasibility['land_cost'] ?? 0) / max($saleable, 1), 0);
                  ?>
                </td>
              </tr>
              <tr>
                <td><i class="fas fa-stamp text-warning me-2"></i>Registry (R)</td>
                <td class="text-end">₹<?= number_format($feasibility['registry_cost'] ?? 0, 0) ?></td>
                <td class="text-end">₹<?= number_format(($feasibility['registry_cost'] ?? 0) / max($saleable, 1), 0) ?></td>
              </tr>
              <tr>
                <td><i class="fas fa-hard-hat text-info me-2"></i>Development (D)</td>
                <td class="text-end">₹<?= number_format($feasibility['development_cost'] ?? 0, 0) ?></td>
                <td class="text-end">₹<?= number_format(($feasibility['development_cost'] ?? 0) / max($saleable, 1), 0) ?></td>
              </tr>
              <tr>
                <td><i class="fas fa-check-circle text-success me-2"></i>Approvals (A)</td>
                <td class="text-end">₹<?= number_format($feasibility['approval_cost'] ?? 0, 0) ?></td>
                <td class="text-end">₹<?= number_format(($feasibility['approval_cost'] ?? 0) / max($saleable, 1), 0) ?></td>
              </tr>
              <tr class="table-warning fw-bold">
                <td><i class="fas fa-equals me-2"></i>Total Cost Basis (C)</td>
                <td class="text-end">₹<?= number_format($feasibility['total_cost_basis'] ?? 0, 0) ?></td>
                <td class="text-end">₹<?= number_format($feasibility['raw_cost_per_sqft'] ?? 0, 0) ?></td>
              </tr>
              <tr class="border-top">
                <td><i class="fas fa-building text-secondary me-2"></i>G&A Overhead (O)</td>
                <td class="text-end">₹<?= number_format($feasibility['ga_cost'] ?? 0, 0) ?></td>
                <td class="text-end">₹<?= number_format($feasibility['ga_per_sqft'] ?? 0, 0) ?></td>
              </tr>
            </tbody>
          </table></div>

          <hr>
          <div class="row text-center">
            <div class="col-4">
              <div class="small text-muted">Raw Area</div>
              <strong><?= number_format($feasibility['total_raw_area_sqft'] ?? 0, 0) ?> sqft</strong>
            </div>
            <div class="col-4">
              <div class="small text-muted">Yield</div>
              <strong><?= number_format($feasibility['yield_pct'] ?? 60, 0) ?>%</strong>
            </div>
            <div class="col-4">
              <div class="small text-muted">Saleable Area</div>
              <strong><?= number_format($feasibility['saleable_area_sqft'] ?? 0, 0) ?> sqft</strong>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Override Form -->
    <div class="col-md-6">
      <div class="card aps-cp-card">
        <div class="card-header"><strong>Adjust Parameters</strong></div>
        <div class="card-body">
          <form method="POST" action="<?= BASE_URL ?>/admin/colony-feasibility/<?= (int)($colony['id'] ?? 0) ?>/calculate">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

            <div class="mb-3">
              <label class="form-label">Total Raw Land Area (sqft)</label>
              <input type="number" name="total_raw_area_sqft" class="form-control"
                     value="<?= number_format($feasibility['total_raw_area_sqft'] ?? 0, 0, '.', '') ?>"
                     placeholder="Leave blank to auto-detect from DB">
              <small class="text-muted">Auto-detected from land_acquisitions if left empty</small>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Saleable Yield (%)</label>
                <input type="number" name="yield_pct" class="form-control" step="0.1" min="10" max="100"
                       value="<?= $feasibility['yield_pct'] ?? 60 ?>">
                <small class="text-muted">% of raw area that becomes plots</small>
              </div>
              <div class="col-md-6">
                <label class="form-label">MLM Budget (%)</label>
                <input type="number" name="mlm_budget_pct" class="form-control" step="0.1" min="0" max="50"
                       value="<?= $feasibility['mlm_budget_pct'] ?? 25 ?>">
                <small class="text-muted">Commissions (worst-case 26.4%)</small>
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">G&A Overhead (%)</label>
                <input type="number" name="office_overhead_pct" class="form-control" step="0.1" min="0" max="20"
                       value="<?= $feasibility['office_overhead_pct'] ?? 5 ?>">
                <small class="text-muted">Office + staff + marketing</small>
              </div>
              <div class="col-md-6">
                <label class="form-label">Target Profit Margin (%)</label>
                <input type="number" name="target_profit_pct" class="form-control" step="0.1" min="0" max="50"
                       value="<?= $feasibility['profit_margin_pct'] ?? 20 ?>">
                <small class="text-muted">Desired profit on selling price</small>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label">Notes (optional)</label>
              <textarea name="notes" class="form-control" rows="2" placeholder="Reason for this recalculation..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary">
              <i class="fas fa-calculator me-1"></i>Recalculate Feasibility
            </button>
            <button type="button" class="btn btn-outline-info ms-2" id="previewBtn">
              <i class="fas fa-eye me-1"></i>Live Preview
            </button>
          </form>

          <hr>
          <div class="text-center">
            <h5 class="mb-1">
              Recommended Price:
              <span class="text-primary">₹<?= number_format($feasibility['recommended_price_ppsf'] ?? 0, 0) ?>/sqft</span>
            </h5>
            <small class="text-muted">
              Formula: P = C/(1 - <?= number_format(($feasibility['total_overhead_pct'] ?? 50) / 100, 2) ?>) =
              ₹<?= number_format($feasibility['raw_cost_per_sqft'] ?? 0, 0) ?> × <?= $feasibility['markup_factor'] ?? 0 ?>
            </small>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php if (!empty($history)): ?>
  <!-- ─── AUDIT LOG (last 10) ──────────────────────────────── -->
  <div class="card aps-cp-card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <strong>Recent Calculations</strong>
      <a href="<?= BASE_URL ?>/admin/colony-feasibility/<?= (int)($colony['id'] ?? 0) ?>/history"
         class="btn btn-sm btn-outline-secondary">View All</a>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive"><table class="table table-sm table-hover mb-0">
        <thead class="table-light">
          <tr><th>Date</th><th>By</th><th class="text-end">Cost ₹/sqft</th><th class="text-end">Recommended ₹/sqft</th><th class="text-end">Markup</th></tr>
        </thead>
        <tbody>
          <?php foreach (array_slice($history, 0, 5) as $h): ?>
            <tr>
              <td><small><?= date('d M Y H:i', strtotime($h['created_at'])) ?></small></td>
              <td><small><?= htmlspecialchars($h['created_by_name'] ?? 'System') ?></small></td>
              <td class="text-end"><small>₹<?= number_format($h['raw_cost_basis_ppsf'] ?? 0, 0) ?></small></td>
              <td class="text-end"><small><strong>₹<?= number_format($h['recommended_price_ppsf'] ?? 0, 0) ?></strong></small></td>
              <td class="text-end"><small><?= $h['markup_factor'] ?? 0 ?>x</small></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table></div>
    </div>
  </div>
  <?php endif; ?>

  <?php else: ?>
  <!-- ─── NO DATA / ERROR STATE ────────────────────────────── -->
  <div class="card aps-cp-card">
    <div class="card-body text-center py-5">
      <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
      <h5>Could Not Calculate Feasibility</h5>
      <p class="text-muted"><?= htmlspecialchars($feasibility['error'] ?? 'No plot data or land costs found for this colony.') ?></p>
      <p class="text-muted">Ensure the colony has land acquisition records and development costs entered.</p>
      <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= (int)($colony['id'] ?? 0) ?>/costs"
         class="btn btn-primary mt-2">
        <i class="fas fa-plus me-1"></i>Add Development Costs
      </a>
    </div>
  </div>
  <?php endif; ?>
</div>

<script>
document.getElementById('previewBtn')?.addEventListener('click', function() {
  const params = new URLSearchParams();
  const form = document.querySelector('form');
  const data = new FormData(form);
  for (const [k, v] of data.entries()) {
    if (v && k !== 'csrf_token' && k !== 'notes') params.set(k.replace('_pct', '_pct'), v);
  }
  params.set('yield_pct', data.get('yield_pct') || 60);
  params.set('profit_pct', data.get('target_profit_pct') || 20);
  params.set('ga_pct', data.get('office_overhead_pct') || 5);
  params.set('mlm_pct', data.get('mlm_budget_pct') || 25);

  fetch('<?= BASE_URL ?>/admin/colony-feasibility/<?= (int)($colony['id'] ?? 0) ?>/preview?' + params.toString())
    .then(r => r.json())
    .then(d => {
      if (d.success) {
        showToast('Preview:\nPrice: ₹' + d.recommended_price_ppsf + '/sqft\nCost: ₹' + d.total_cost_basis + ' total\nRevenue: ₹' + d.total_revenue?.toLocaleString(), 'info');
      } else {
        showToast('Preview failed: ' + d.error, 'danger');
      }
    })
    .catch(e => showToast('Network error', 'danger'));
});
</script>
