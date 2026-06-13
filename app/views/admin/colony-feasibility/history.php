<?php
$colony  = $colony ?? [];
$history = $history ?? [];
?>
<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-1">Feasibility Audit Log</h1>
      <span class="text-muted"><?= htmlspecialchars($colony['name'] ?? '') ?> — All pricing calculations</span>
    </div>
    <a href="<?= BASE_URL ?>/admin/colony-feasibility/<?= (int)($colony['id'] ?? 0) ?>"
       class="btn btn-outline-secondary btn-sm">
      <i class="fas fa-arrow-left me-1"></i>Back to Calculator
    </a>
  </div>

  <?php if (empty($history)): ?>
    <div class="card aps-cp-card">
      <div class="card-body text-center py-5">
        <i class="fas fa-history fa-3x text-muted mb-3"></i>
        <h5>No Calculations Yet</h5>
        <p class="text-muted">Run a feasibility calculation to create the first audit entry.</p>
      </div>
    </div>
  <?php else: ?>
    <div class="card aps-cp-card">
      <div class="card-body p-0">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>Date</th>
              <th>Calculated By</th>
              <th class="text-end">Land</th>
              <th class="text-end">Registry</th>
              <th class="text-end">Development</th>
              <th class="text-end">Approvals</th>
              <th class="text-end">Cost ₹/sqft</th>
              <th class="text-end">Recommended ₹/sqft</th>
              <th class="text-end">Markup</th>
              <th>Notes</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($history as $h): ?>
              <tr>
                <td><small><?= date('d M Y H:i', strtotime($h['created_at'])) ?></small></td>
                <td><small><?= htmlspecialchars($h['created_by_name'] ?? 'System') ?></small></td>
                <td class="text-end"><small>₹<?= number_format($h['land_cost_total'] ?? 0, 0) ?></small></td>
                <td class="text-end"><small>₹<?= number_format($h['registry_cost_total'] ?? 0, 0) ?></small></td>
                <td class="text-end"><small>₹<?= number_format($h['development_cost_total'] ?? 0, 0) ?></small></td>
                <td class="text-end"><small>₹<?= number_format($h['approvals_cost_total'] ?? 0, 0) ?></small></td>
                <td class="text-end"><small><strong>₹<?= number_format($h['raw_cost_basis_ppsf'] ?? 0, 0) ?></strong></small></td>
                <td class="text-end"><small><strong class="text-primary">₹<?= number_format($h['recommended_price_ppsf'] ?? 0, 0) ?></strong></small></td>
                <td class="text-end"><small><?= $h['markup_factor'] ?? 0 ?>x</small></td>
                <td><small class="text-muted"><?= htmlspecialchars(mb_substr($h['notes'] ?? '', 0, 50)) ?></small></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>
</div>
