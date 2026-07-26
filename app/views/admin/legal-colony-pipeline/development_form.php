<?php
$colony = $colony ?? null;
$costs  = $costs ?? [];
?>
<div class="container-fluid py-4">
  <a href="/admin/legal-colony-pipeline/detail/<?= $colony['id'] ?? 0 ?>" class="text-decoration-none text-muted small"><i class="fas fa-arrow-left me-1"></i> Back to Pipeline</a>
  <h2 class="mb-4"><i class="fas fa-hard-hat me-2 text-secondary"></i>Phase 5: Development Costs</h2>

  <?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-1"></i> <?= $_SESSION['flash_success'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php unset($_SESSION['flash_success']); ?>
  <?php endif; ?>
  <?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-triangle me-1"></i> <?= $_SESSION['flash_error'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php unset($_SESSION['flash_error']); ?>
  <?php endif; ?>

  <div class="row g-4">
    <!-- Add Cost Form -->
    <div class="col-lg-6">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-dark text-white"><strong><i class="fas fa-plus me-1"></i> Record Development Cost</strong></div>
        <div class="card-body">
          <form method="POST" action="/admin/legal-colony-pipeline/store-cost">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <input type="hidden" name="colony_id" value="<?= $colony['id'] ?? 0 ?>">

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label text-white">Cost Type *</label>
                <select name="cost_type" class="form-select bg-dark text-white border-secondary" required>
                  <option value="">Select type...</option>
                  <option value="road">Road Construction</option>
                  <option value="drainage">Drainage</option>
                  <option value="water_supply">Water Supply</option>
                  <option value="electricity">Electricity</option>
                  <option value="sewerage">Sewerage</option>
                  <option value="park_landscaping">Park & Landscaping</option>
                  <option value="boundary_wall">Boundary Wall</option>
                  <option value="gate">Entry Gate</option>
                  <option value="common_area">Common Area</option>
                  <option value="stamping_registration">Stamping & Registration</option>
                  <option value="legal_fees">Legal Fees</option>
                  <option value="brokerage">Brokerage</option>
                  <option value="architecture">Architecture</option>
                  <option value="survey">Survey</option>
                  <option value="soil_testing">Soil Testing</option>
                  <option value="other">Other</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label text-white">Amount (₹) *</label>
                <input type="number" name="amount" class="form-control bg-dark text-white border-secondary" min="1" required>
              </div>
              <div class="col-md-4">
                <label class="form-label text-white">GST Rate %</label>
                <input type="number" name="gst_rate" class="form-control bg-dark text-white border-secondary" value="18" step="0.5">
              </div>
              <div class="col-md-4">
                <label class="form-label text-white">TDS Section</label>
                <input type="text" name="tds_section" class="form-control bg-dark text-white border-secondary" placeholder="e.g. 194C">
              </div>
              <div class="col-md-4">
                <label class="form-label text-white">TDS Rate %</label>
                <input type="number" name="tds_rate" class="form-control bg-dark text-white border-secondary" value="0" step="0.5">
              </div>
              <div class="col-md-6">
                <label class="form-label text-white">Vendor Name</label>
                <input type="text" name="vendor_name" class="form-control bg-dark text-white border-secondary">
              </div>
              <div class="col-md-6">
                <label class="form-label text-white">Invoice Number</label>
                <input type="text" name="invoice_number" class="form-control bg-dark text-white border-secondary">
              </div>
              <div class="col-md-6">
                <label class="form-label text-white">Invoice Date</label>
                <input type="date" name="invoice_date" class="form-control bg-dark text-white border-secondary" value="<?= date('Y-m-d') ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label text-white">Paid Amount (₹)</label>
                <input type="number" name="paid_amount" class="form-control bg-dark text-white border-secondary" value="0" min="0">
              </div>
              <div class="col-md-12">
                <label class="form-label text-white">Work Description</label>
                <textarea name="work_description" class="form-control bg-dark text-white border-secondary" rows="2"></textarea>
              </div>
            </div>

            <hr class="border-secondary">
            <button type="submit" class="btn btn-secondary"><i class="fas fa-save me-1"></i> Record Cost</button>
          </form>
        </div>
      </div>
    </div>

    <!-- Existing Costs -->
    <div class="col-lg-6">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-dark text-white"><strong><i class="fas fa-list me-1"></i> Recorded Costs</strong></div>
        <div class="table-responsive">
          <table class="table table-dark table-sm mb-0">
            <thead><tr><th>Type</th><th>Amount</th><th>GST</th><th>Paid</th><th>Status</th></tr></thead>
            <tbody>
              <?php if (empty($costs)): ?>
                <tr><td colspan="5" class="text-center text-muted">No costs recorded yet</td></tr>
              <?php else: ?>
                <?php foreach ($costs as $c): ?>
                  <tr>
                    <td><span class="badge bg-secondary"><?= $c['cost_type'] ?></span></td>
                    <td>₹<?= number_format(floatval($c['amount'])) ?></td>
                    <td>₹<?= number_format(floatval($c['gst_amount'])) ?></td>
                    <td>₹<?= number_format(floatval($c['paid_amount'])) ?></td>
                    <td><span class="badge bg-<?= $c['payment_status'] === 'paid' ? 'success' : 'warning' ?>"><?= ucfirst($c['payment_status']) ?></span></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
