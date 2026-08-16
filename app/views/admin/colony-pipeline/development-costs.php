<?php
$colony = $colony ?? [];
$costs = $costs ?? [];
$summary = $summary ?? [];
$byType = $by_type ?? [];
$costTypes = [
  'roads' => __('cp_roads'), 'drainage' => __('cp_drainage'), 'water_supply' => __('cp_water_supply'),
  'electricity' => __('cp_electricity'), 'sewage' => __('cp_sewage'), 'park_development' => __('cp_park_development'),
  'compound_wall' => __('cp_compound_wall'), 'entrance_gate' => __('cp_entrance_gate'), 'street_lights' => __('cp_street_lights'),
  'internal_roads' => __('cp_internal_roads'), 'landscaping' => __('cp_landscaping'), 'community_center' => __('cp_community_center'),
  'parking' => __('cp_parking'), 'fire_safety' => __('cp_fire_safety'), 'rainwater_harvesting' => __('cp_rainwater_harvesting'),
  'stp' => __('cp_stp'), 'other' => __('cp_other')
];
$costStatuses = [
  'planned' => 'secondary', 'in_progress' => 'warning', 'completed' => 'success', 'cancelled' => 'danger'
];
?>
<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-1"><?= __('cp_dev_costs') ?></h1>
      <span class="text-muted">
        <?= htmlspecialchars($colony['name'] ?? '') ?>
        <?= !empty($colony['district_name']) ? ' &middot; ' . htmlspecialchars($colony['district_name'] ?? '') : '' ?>
      </span>
    </div>
    <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= (int)($colony['id'] ?? 0) ?>" class="btn btn-outline-secondary btn-sm">
      <i class="fas fa-arrow-left me-1"></i><?= __('cp_back_to_colony') ?>
    </a>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-md-2">
      <div class="card aps-cp-card">
        <div class="card-body text-center">
          <div class="fs-3 text-primary">₹<?= number_format((float)($summary['total_amount'] ?? 0) / 100000, 1) ?>L</div>
          <div class="text-muted small"><?= __('cp_total_cost') ?></div>
        </div>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card aps-cp-card">
        <div class="card-body text-center">
          <div class="fs-3 text-info">₹<?= number_format((float)($summary['total_gst'] ?? 0) / 100000, 1) ?>L</div>
          <div class="text-muted small"><?= __('cp_total_gst') ?></div>
        </div>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card aps-cp-card">
        <div class="card-body text-center">
          <div class="fs-3 text-success">₹<?= number_format((float)($summary['total_paid'] ?? 0) / 100000, 1) ?>L</div>
          <div class="text-muted small"><?= __('cp_paid') ?></div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card aps-cp-card">
        <div class="card-body text-center">
          <div class="fs-3 text-danger">₹<?= number_format((float)($summary['total_balance'] ?? 0) / 100000, 1) ?>L</div>
          <div class="text-muted small"><?= __('cp_balance_due') ?></div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card aps-cp-card">
        <div class="card-body text-center">
          <div class="fs-3 text-secondary"><?= (int)($summary['cost_count'] ?? 0) ?></div>
          <div class="text-muted small"><?= __('cp_entries') ?></div>
        </div>
      </div>
    </div>
  </div>

  <?php if (!empty($byType)): ?>
  <div class="card aps-cp-card mb-4">
    <div class="card-header aps-cp-card-header"><strong><i class="fas fa-layer-group me-2"></i><?= __('cp_summary_by_type') ?></strong></div>
    <div class="table-responsive">
      <table class="table table-sm table-hover mb-0">
        <thead><tr><th><?= __('cp_cost_type') ?></th><th><?= __('cp_count') ?></th><th><?= __('cp_total_amount') ?></th></tr></thead>
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
    <div class="card-header aps-cp-card-header"><strong><i class="fas fa-list me-2"></i><?= __('cp_all_costs') ?></strong></div>
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr>
            <th><?= __('cp_cost_type') ?></th>
            <th><?= __('cp_vendor') ?></th>
            <th><?= __('cp_description') ?></th>
            <th><?= __('cp_invoice') ?></th>
            <th><?= __('cp_amount') ?></th>
            <th><?= __('cp_gst') ?></th>
            <th><?= __('cp_status') ?></th>
            <th><?= __('cp_paid') ?></th>
            <th><?= __('cp_balance') ?></th>
            <th><?= __('cp_date') ?></th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($costs)): ?>
            <tr><td colspan="10" class="text-center text-muted py-4"><?= __('cp_no_costs_recorded') ?></td></tr>
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
          <i class="fas fa-plus-circle me-2 text-success"></i><strong><?= __('cp_add_cost') ?></strong>
        </button>
      </h2>
      <div id="addCostCollapse" class="accordion-collapse collapse" data-bs-parent="#addCostAccordion">
        <div class="accordion-body">
          <form method="post" action="<?= BASE_URL ?>/admin/colony-pipeline/<?= (int)($colony['id'] ?? 0) ?>/costs/store">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label"><?= __('cp_cost_type') ?> <span class="text-danger">*</span></label>
                <select name="cost_type" class="form-select" required>
                  <option value=""><?= __('cp_select_type') ?></option>
                  <?php foreach ($costTypes as $key => $label): ?>
                    <option value="<?= $key ?>"><?= htmlspecialchars($label ?? '') ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label"><?= __('cp_vendor_name') ?> <span class="text-danger">*</span></label>
                <input type="text" name="vendor_name" class="form-control" required maxlength="200">
              </div>
              <div class="col-md-4">
                <label class="form-label"><?= __('cp_invoice_number') ?></label>
                <input type="text" name="invoice_number" class="form-control" maxlength="100">
              </div>
              <div class="col-md-6">
                <label class="form-label"><?= __('cp_work_description') ?></label>
                <textarea name="work_description" class="form-control" rows="2" maxlength="500"></textarea>
              </div>
              <div class="col-md-6">
                <label class="form-label"><?= __('cp_invoice_date') ?></label>
                <input type="date" name="invoice_date" class="form-control" value="<?= date('Y-m-d') ?>">
              </div>
              <div class="col-md-3">
                <label class="form-label"><?= __('cp_amount') ?> (₹) <span class="text-danger">*</span></label>
                <input type="number" name="amount" class="form-control" min="0" step="0.01" required>
              </div>
              <div class="col-md-3">
                <label class="form-label"><?= __('cp_gst_amount') ?> (₹)</label>
                <input type="number" name="gst_amount" class="form-control" value="0" min="0" step="0.01">
              </div>
              <div class="col-md-3">
                <label class="form-label"><?= __('cp_tds_section') ?></label>
                <select name="tds_section" class="form-select">
                  <option value=""><?= __('cp_none') ?></option>
                  <option value="194C"><?= __('cp_194c') ?></option>
                  <option value="194IA"><?= __('cp_194ia') ?></option>
                  <option value="194IB"><?= __('cp_194ib') ?></option>
                  <option value="194J"><?= __('cp_194j') ?></option>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label"><?= __('cp_paid_amount') ?> (₹)</label>
                <input type="number" name="paid_amount" class="form-control" value="0" min="0" step="0.01">
              </div>
              <div class="col-md-3">
                <label class="form-label"><?= __('cp_status') ?> <span class="text-danger">*</span></label>
                <select name="status" class="form-select" required>
                  <option value="planned"><?= __('cp_planned') ?></option>
                  <option value="in_progress"><?= __('cp_in_progress') ?></option>
                  <option value="completed"><?= __('cp_completed') ?></option>
                  <option value="cancelled"><?= __('cp_cancelled') ?></option>
                </select>
              </div>
              <div class="col-md-12 text-end">
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-save me-1"></i><?= __('cp_save_cost_entry') ?>
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
