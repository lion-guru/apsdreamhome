<?php
$colony = $colony ?? null;
?>
<div class="container-fluid py-4">
  <a href="/admin/legal-colony-pipeline/detail/<?= $colony['id'] ?? 0 ?>" class="text-decoration-none text-muted small"><i class="fas fa-arrow-left me-1"></i> Back to Pipeline</a>
  <h2 class="mb-4"><i class="fas fa-tag me-2 text-success"></i>Phase 6: Apply Pricing</h2>

  <?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-triangle me-1"></i> <?= $_SESSION['flash_error'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php unset($_SESSION['flash_error']); ?>
  <?php endif; ?>

  <div class="row g-4">
    <div class="col-lg-8">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-dark text-white"><strong><i class="fas fa-calculator me-1"></i> Pricing Configuration</strong></div>
        <div class="card-body">
          <form method="POST" action="/admin/legal-colony-pipeline/calculate-pricing">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <input type="hidden" name="colony_id" value="<?= $colony['id'] ?? 0 ?>">

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label text-white">Override Price per sqft (₹)</label>
                <input type="number" name="override_price_per_sqft" class="form-control bg-dark text-white border-secondary" value="0" min="0" step="0.01">
                <small class="text-info">Leave 0 to use calculated price from ColonyPricingService</small>
              </div>
              <div class="col-md-6">
                <label class="form-label text-white">Corner Plot Premium %</label>
                <input type="number" name="corner_premium_pct" class="form-control bg-dark text-white border-secondary" value="10" min="0" step="0.5">
              </div>
              <div class="col-md-6">
                <label class="form-label text-white">Park Facing Premium %</label>
                <input type="number" name="park_facing_premium_pct" class="form-control bg-dark text-white border-secondary" value="15" min="0" step="0.5">
              </div>
              <div class="col-md-6">
                <label class="form-label text-white">Wide Road Premium %</label>
                <input type="number" name="wide_road_premium_pct" class="form-control bg-dark text-white border-secondary" value="8" min="0" step="0.5">
              </div>
              <div class="col-md-6">
                <label class="form-label text-white">Wide Road Threshold (ft)</label>
                <input type="number" name="wide_road_threshold" class="form-control bg-dark text-white border-secondary" value="40">
              </div>
              <div class="col-md-6">
                <div class="form-check mt-4">
                  <input type="checkbox" name="force_below_cost" class="form-check-input" id="forceBelow">
                  <label class="form-check-label text-white" for="forceBelow">Force pricing below cost floor</label>
                </div>
              </div>
            </div>

            <hr class="border-secondary">
            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-success"><i class="fas fa-calculator me-1"></i> Calculate & Apply</button>
              <a href="/admin/legal-colony-pipeline/detail/<?= $colony['id'] ?? 0 ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card border-0 shadow-sm border-success">
        <div class="card-header bg-success text-white"><strong><i class="fas fa-info-circle me-1"></i> Legal Pricing Guard</strong></div>
        <div class="card-body">
          <ul class="list-unstyled mb-0 small text-dark">
            <li class="mb-2"><i class="fas fa-shield-alt me-1"></i> Price cannot go below land + development cost</li>
            <li class="mb-2"><i class="fas fa-info-circle me-1"></i> ColonyPricingService auto-calculates from DB</li>
            <li class="mb-2"><i class="fas fa-percent me-1"></i> Corner +10%, Park-facing +15%, Wide road +8%</li>
            <li class="mb-2"><i class="fas fa-calculator me-1"></i> Cost floor stored in colony.min_price_per_sqft</li>
            <li><i class="fas fa-exclamation-triangle me-1"></i> Below-cost override requires explicit flag</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>
