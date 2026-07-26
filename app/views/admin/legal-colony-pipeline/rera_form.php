<?php
$colony = $colony ?? null;
?>
<div class="container-fluid py-4">
  <a href="/admin/legal-colony-pipeline/detail/<?= $colony['id'] ?? 0 ?>" class="text-decoration-none text-muted small"><i class="fas fa-arrow-left me-1"></i> Back to Pipeline</a>
  <h2 class="mb-4"><i class="fas fa-stamp me-2 text-danger"></i>Phase 4: RERA Registration</h2>

  <?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-triangle me-1"></i> <?= $_SESSION['flash_error'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php unset($_SESSION['flash_error']); ?>
  <?php endif; ?>

  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <form method="POST" action="/admin/legal-colony-pipeline/store-rera">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
        <input type="hidden" name="colony_id" value="<?= $colony['id'] ?? 0 ?>">

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label text-white">RERA Number *</label>
            <input type="text" name="rera_number" class="form-control bg-dark text-white border-secondary" required placeholder="e.g. UPRERAPRJ12345">
          </div>
          <div class="col-md-6">
            <label class="form-label text-white">State Code</label>
            <input type="text" name="state_code" class="form-control bg-dark text-white border-secondary" value="UP">
          </div>
          <div class="col-md-6">
            <label class="form-label text-white">Builder Name</label>
            <input type="text" name="builder_name" class="form-control bg-dark text-white border-secondary" value="APS Dream Homes Pvt. Ltd.">
          </div>
          <div class="col-md-6">
            <label class="form-label text-white">Builder License</label>
            <input type="text" name="builder_license" class="form-control bg-dark text-white border-secondary" placeholder="License number">
          </div>
          <div class="col-md-6">
            <label class="form-label text-white">Project Type</label>
            <select name="project_type" class="form-select bg-dark text-white border-secondary">
              <option value="Residential Plotted">Residential Plotted</option>
              <option value="Residential Apartments">Residential Apartments</option>
              <option value="Commercial">Commercial</option>
              <option value="Mixed Use">Mixed Use</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label text-white">Registration Date</label>
            <input type="date" name="registration_date" class="form-control bg-dark text-white border-secondary" value="<?= date('Y-m-d') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label text-white">Validity Date</label>
            <input type="date" name="validity_date" class="form-control bg-dark text-white border-secondary" value="<?= date('Y-m-d', strtotime('+5 years')) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label text-white">City</label>
            <input type="text" name="city" class="form-control bg-dark text-white border-secondary" value="Gorakhpur">
          </div>
          <div class="col-md-6">
            <label class="form-label text-white">District</label>
            <input type="text" name="district" class="form-control bg-dark text-white border-secondary" value="Gorakhpur">
          </div>
        </div>

        <hr class="border-secondary">
        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-danger"><i class="fas fa-stamp me-1"></i> Register RERA</button>
          <a href="/admin/legal-colony-pipeline/detail/<?= $colony['id'] ?? 0 ?>" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
