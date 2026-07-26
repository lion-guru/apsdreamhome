<?php
$colony = $colony ?? null;
?>
<div class="container-fluid py-4">
  <a href="/admin/legal-colony-pipeline/detail/<?= $colony['id'] ?? 0 ?>" class="text-decoration-none text-muted small"><i class="fas fa-arrow-left me-1"></i> Back to Pipeline</a>
  <h2 class="mb-4"><i class="fas fa-vector-square me-2 text-primary"></i>Phase 3: Legal Plot Cutting</h2>

  <?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-triangle me-1"></i> <?= $_SESSION['flash_error'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php unset($_SESSION['flash_error']); ?>
  <?php endif; ?>

  <div class="row g-4">
    <div class="col-lg-8">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-dark text-white"><strong><i class="fas fa-cut me-1"></i> Plot Configuration</strong></div>
        <div class="card-body">
          <form method="POST" action="/admin/legal-colony-pipeline/generate-plots">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <input type="hidden" name="colony_id" value="<?= $colony['id'] ?? 0 ?>">

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label text-white">Total Land Area (sqft) *</label>
                <input type="number" name="total_land_sqft" class="form-control bg-dark text-white border-secondary" required value="<?= number_format(floatval($colony['total_area_sqft'] ?? 0), 0, '.', '') ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label text-white">Road Width (ft) *</label>
                <input type="number" name="road_width_ft" class="form-control bg-dark text-white border-secondary" value="30" min="20">
                <small class="text-warning">Legal minimum: 20 ft</small>
              </div>
              <div class="col-md-4">
                <label class="form-label text-white">Plot Width (ft)</label>
                <input type="number" name="plot_width_ft" class="form-control bg-dark text-white border-secondary" value="30">
              </div>
              <div class="col-md-4">
                <label class="form-label text-white">Plot Length (ft)</label>
                <input type="number" name="plot_length_ft" class="form-control bg-dark text-white border-secondary" value="40">
              </div>
              <div class="col-md-4">
                <label class="form-label text-white">Plots Per Block</label>
                <input type="number" name="plots_per_block" class="form-control bg-dark text-white border-secondary" value="20">
              </div>
              <div class="col-md-4">
                <label class="form-label text-white">Road Area %</label>
                <input type="number" name="road_area_pct" class="form-control bg-dark text-white border-secondary" value="15" min="15">
              </div>
              <div class="col-md-4">
                <label class="form-label text-white">Park Area %</label>
                <input type="number" name="park_area_pct" class="form-control bg-dark text-white border-secondary" value="10">
              </div>
              <div class="col-md-4">
                <label class="form-label text-white">Amenity Area %</label>
                <input type="number" name="amenity_area_pct" class="form-control bg-dark text-white border-secondary" value="5">
              </div>
              <div class="col-md-12">
                <label class="form-label text-white">Block Names (comma-separated)</label>
                <input type="text" name="blocks" class="form-control bg-dark text-white border-secondary" value="A, B, C" placeholder="A, B, C">
              </div>
            </div>

            <hr class="border-secondary">
            <button type="submit" class="btn btn-primary"><i class="fas fa-magic me-1"></i> Generate Plots Legally</button>
          </form>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card border-0 shadow-sm border-warning">
        <div class="card-header bg-warning text-dark"><strong><i class="fas fa-exclamation-triangle me-1"></i> Legal Compliance</strong></div>
        <div class="card-body">
          <ul class="list-unstyled mb-0 small">
            <li class="mb-2"><i class="fas fa-check-circle text-success me-1"></i> RERA minimum 30% open space</li>
            <li class="mb-2"><i class="fas fa-check-circle text-success me-1"></i> Road width ≥ 20 ft</li>
            <li class="mb-2"><i class="fas fa-check-circle text-success me-1"></i> Plot depth ≤ 3× width</li>
            <li class="mb-2"><i class="fas fa-check-circle text-success me-1"></i> Minimum plot size 120 sqft</li>
            <li class="mb-2"><i class="fas fa-info-circle text-info me-1"></i> Compliance checks run before generation</li>
            <li><i class="fas fa-shield-alt text-primary me-1"></i> Non-compliant plots blocked automatically</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>
