<?php
$colony = $colony ?? [];
$costs = $costs ?? [];
$summary = $summary ?? [];
$byType = $by_type ?? [];
$costTypes = [
  'roads' => 'Roads', 'drainage' => 'Drainage', 'water_supply' => 'Water Supply',
  'electricity' => 'Electricity', 'sewage' => 'Sewage', 'park_development' => 'Park Development',
  'compound_wall' => 'Compound Wall', 'entrance_gate' => 'Entrance Gate', 'street_lights' => 'Street Lights',
  'internal_roads' => 'Internal Roads', 'landscaping' => 'Landscaping', 'community_center' => 'Community Center',
  'parking' => 'Parking', 'fire_safety' => 'Fire Safety', 'rainwater_harvesting' => 'Rainwater Harvesting',
  'stp' => 'STP', 'other' => 'Other'
];
$costStatuses = [
  'planned' => 'secondary', 'in_progress' => 'warning', 'completed' => 'success', 'cancelled' => 'danger'
];
?>
<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-1">Development Costs</h1>
      <span class="text-muted">
        <?= htmlspecialchars($colony['name'] ?? '') ?>
        <?= !empty($colony['district_name']) ? ' &middot; ' . htmlspecialchars($colony['district_name']) : '' ?>
      </span>
    </div>
    <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= (int)($colony['id'] ?? 0) ?>" class="btn btn-outline-secondary btn-sm">
      <i class="fas fa-arrow-left me-1"></i>Back to Colony
    </a>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-md-2">
      <div class="card aps-cp-card">
        <div class="card-body text-center">
          <div class="fs-3 text-primary">₹<?= number_format((float)($summary['total_amount'] ?? 0) / 100000, 1) ?>L</div>
          <div class="text-muted small">Total Cost</div>
        </div>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card aps-cp-card">
        <div class="card-body text-center">
          <div class="fs-3 text-info">₹<?= number_format((float)($summary['total_gst'] ?? 0) / 100000, 1) ?>L</div>
          <div class="text-muted small">Total GST</div>
        </div>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card aps-cp-card">
        <div class="card-body text-center">
          <div class="fs-3 text-success">₹<?= number_format((float)($summary['total_paid'] ?? 0) / 100000, 1) ?>L</div>
          <div class="text-muted small">Total Paid</div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card aps-cp-card">
        <div class="card-body text-center">
          <div class="fs-3 text-danger">₹<?= number_format((float)($summary['total_balance'] ?? 0) / 100000, 1) ?>L</div>
          <div class="text-muted small">Balance Due</div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card aps-cp-card">
        <div class="card-body text-center">
          <div class="fs-3 text-secondary"><?= (int)($summary['cost_count'] ?? 0) ?></div>
          <div class="text-muted small"># Entries</div>
        </div>
      </div>
    </div>
  </div>

  <?php if (!empty($byType)): ?>
  <div class="card aps-cp-card mb-4">
    <div class="card-header aps-cp-card-header"><strong><i class="fas fa-layer-group me-2"></i>Summary by Type</strong></div>
    <div class="table-responsive">
      <table class="table table-sm table-hover mb-0">
        <thead><tr><th>Cost Type</th><th>Count</th><th>Total Amount</th></tr></thead>
        <tbody>
          <?php foreach ($byType as $bt): ?>
            <tr>
              <td><?= htmlspecialchars(ucwords(str_replace('_', ' ', $bt['cost_type'] ?? ''))) ?></td>
              <td><span class="badge bg-primary"><?= (int)($bt['cnt'] ?? 0) ?></span></td>
              <td>₹<?= number_format((float)($bt['amt'] ?? 0), 0) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <div class="card aps-cp-card mb-4">
    <div class="card-header aps-cp-card-header"><strong><i class="fas fa-list me-2"></i>All Costs</strong></div>
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr>
            <th>Type</th>
            <th>Vendor</th>
            <th>Description</th>
            <th>Invoice</th>
            <th>Amount</th>
            <th>GST</th>
            <th>Status</th>
            <th>Paid</th>
            <th>Balance</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($costs)): ?>
            <tr><td colspan="10" class="text-center text-muted py-4">No development costs recorded yet. Add your first cost entry below.</td></tr>
          <?php else: ?>
            <?php foreach ($costs as $cv): ?>
              <?php
                $cvAmount = (float)($cv['amount'] ?? 0);
                $cvGst = (float)($cv['gst_amount'] ?? 0);
                $cvPaid = (float)($cv['paid_amount'] ?? 0);
                $cvBalance = ($cvAmount + $cvGst) - $cvPaid;
                $cvStatus = $cv['status'] ?? 'planned';
              ?>
              <tr>
                <td><span class="badge bg-light text-dark"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $cv['cost_type'] ?? ''))) ?></span></td>
                <td><?= htmlspecialchars($cv['vendor_name'] ?? $cv['vendor_name_lookup'] ?? '-') ?></td>
                <td><small><?= htmlspecialchars($cv['work_description'] ?? '') ?></small></td>
                <td><small class="text-muted"><?= htmlspecialchars($cv['invoice_number'] ?? '-') ?></small></td>
                <td>₹<?= number_format($cvAmount, 0) ?></td>
                <td>₹<?= number_format($cvGst, 0) ?></td>
                <td><span class="badge bg-<?= $costStatuses[$cvStatus] ?? 'secondary' ?>"><?= ucfirst(str_replace('_', ' ', $cvStatus)) ?></span></td>
                <td class="text-success">₹<?= number_format($cvPaid, 0) ?></td>
                <td class="text-danger">₹<?= number_format($cvBalance, 0) ?></td>
                <td><small class="text-muted"><?= htmlspecialchars($cv['invoice_date'] ?? $cv['created_at'] ?? '') ?></small></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="accordion" id="addCostAccordion">
    <div class="accordion-item">
      <h2 class="accordion-header">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#addCostCollapse">
          <i class="fas fa-plus-circle me-2 text-success"></i><strong>Add New Cost Entry</strong>
        </button>
      </h2>
      <div id="addCostCollapse" class="accordion-collapse collapse" data-bs-parent="#addCostAccordion">
        <div class="accordion-body">
          <form method="post" action="<?= BASE_URL ?>/admin/colony-pipeline/<?= (int)($colony['id'] ?? 0) ?>/costs/store">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label">Cost Type <span class="text-danger">*</span></label>
                <select name="cost_type" class="form-select" required>
                  <option value="">Select Type</option>
                  <?php foreach ($costTypes as $key => $label): ?>
                    <option value="<?= $key ?>"><?= htmlspecialchars($label) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Vendor Name <span class="text-danger">*</span></label>
                <input type="text" name="vendor_name" class="form-control" required maxlength="200">
              </div>
              <div class="col-md-4">
                <label class="form-label">Invoice Number</label>
                <input type="text" name="invoice_number" class="form-control" maxlength="100">
              </div>
              <div class="col-md-6">
                <label class="form-label">Work Description</label>
                <textarea name="work_description" class="form-control" rows="2" maxlength="500"></textarea>
              </div>
              <div class="col-md-6">
                <label class="form-label">Invoice Date</label>
                <input type="date" name="invoice_date" class="form-control" value="<?= date('Y-m-d') ?>">
              </div>
              <div class="col-md-3">
                <label class="form-label">Amount (₹) <span class="text-danger">*</span></label>
                <input type="number" name="amount" class="form-control" min="0" step="0.01" required>
              </div>
              <div class="col-md-3">
                <label class="form-label">GST Amount (₹)</label>
                <input type="number" name="gst_amount" class="form-control" value="0" min="0" step="0.01">
              </div>
              <div class="col-md-3">
                <label class="form-label">TDS Section</label>
                <select name="tds_section" class="form-select">
                  <option value="">None</option>
                  <option value="194C">194C (Contractor)</option>
                  <option value="194IA">194IA (Immovable Property)</option>
                  <option value="194IB">194IB (Rent)</option>
                  <option value="194J">194J (Professional)</option>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label">Paid Amount (₹)</label>
                <input type="number" name="paid_amount" class="form-control" value="0" min="0" step="0.01">
              </div>
              <div class="col-md-3">
                <label class="form-label">Status <span class="text-danger">*</span></label>
                <select name="status" class="form-select" required>
                  <option value="planned">Planned</option>
                  <option value="in_progress">In Progress</option>
                  <option value="completed">Completed</option>
                  <option value="cancelled">Cancelled</option>
                </select>
              </div>
              <div class="col-md-12 text-end">
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-save me-1"></i>Save Cost Entry
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
