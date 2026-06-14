<?php
$page_title = $page_title ?? 'Finance';
$page_heading = $page_heading ?? 'Finance Management';
$content = $content ?? '';
ob_start();
?>
<div class="container-fluid py-4">
  <h1 class="h3 mb-4"><i class="fas fa-rupee-sign me-2"></i>Finance Management — FY <?= $currentYear ?></h1>

  <div class="row mb-3">
    <div class="col-md-3"><div class="card border-left-primary shadow-sm"><div class="card-body aps-cp-card-body"><h6 class="text-muted">Budget Allocated</h6><h3 class="mb-0">₹<?= number_format($summary['budgets'] ?? 0, 0) ?></h3></div></div></div>
    <div class="col-md-3"><div class="card border-left-danger shadow-sm"><div class="card-body aps-cp-card-body"><h6 class="text-muted">Spent</h6><h3 class="mb-0">₹<?= number_format($summary['spent'] ?? 0, 0) ?></h3></div></div></div>
    <div class="col-md-3"><div class="card border-left-success shadow-sm"><div class="card-body aps-cp-card-body"><h6 class="text-muted">Remaining</h6><h3 class="mb-0">₹<?= number_format($summary['remaining'] ?? 0, 0) ?></h3></div></div></div>
    <div class="col-md-3"><div class="card border-left-info shadow-sm"><div class="card-body aps-cp-card-body"><h6 class="text-muted">Utilization</h6><h3 class="mb-0"><?= $summary['utilization'] ?? 0 ?>%</h3></div></div></div>
  </div>

  <form method="POST" action="<?= BASE_URL ?>/api/v2/finance/gst" class="card card-body mb-4">
                      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    <h5>GST Calculator</h5>
    <div class="row g-2">
      <div class="col-md-3"><label>Amount</label><input name="amount" type="number" step="0.01" class="form-control" required></div>
      <div class="col-md-3"><label>State</label><input name="state_code" class="form-control" placeholder="UP/MH"></div>
      <div class="col-md-3"><label>Interstate?</label>
        <select name="interstate" class="form-select"><option value="0">No (Intra-state)</option><option value="1">Yes (Inter-state)</option></select>
      </div>
      <div class="col-md-3 align-self-end"><button class="btn btn-primary">Calculate</button></div>
    </div>
  </form>

  <ul class="nav nav-tabs mb-3">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#bud">Budgets</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#exp">Expenses</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tax">Tax Slabs</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#gst">GST Returns</button></li>
  </ul>

  <div class="tab-content">
    <div class="tab-pane fade show active" id="bud">
      <div class="card shadow-sm"><div class="card-body p-0">
        <div class="table-responsive"><table class="table mb-0">
          <thead class="table-light"><tr><th>Category</th><th>Department</th><th>Allocated</th><th>Spent</th><th>Period</th></tr></thead>
          <tbody>
            <?php if (empty($budgets)): ?>
              <tr><td colspan="5" class="text-center py-3 text-muted">No budgets</td></tr>
            <?php else: foreach ($budgets as $b): ?>
              <tr>
                <td><?= htmlspecialchars($b['category'] ?? '') ?></td>
                <td><?= htmlspecialchars($b['department_name'] ?? 'N/A') ?></td>
                <td>₹<?= number_format((float)($b['allocated_amount'] ?? 0), 0) ?></td>
                <td>₹<?= number_format((float)($b['spent_amount'] ?? 0), 0) ?></td>
                <td><small><?= htmlspecialchars($b['period'] ?? '') ?></small></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table></div>
      </div></div>
    </div>

    <div class="tab-pane fade" id="exp">
      <div class="card shadow-sm"><div class="card-body p-0">
        <div class="table-responsive"><table class="table mb-0">
          <thead class="table-light"><tr><th>Description</th><th>Amount</th><th>Date</th><th>Status</th><th>Category</th></tr></thead>
          <tbody>
            <?php if (empty($expenses)): ?>
              <tr><td colspan="5" class="text-center py-3 text-muted">No expenses</td></tr>
            <?php else: foreach ($expenses as $e): ?>
              <tr>
                <td><?= htmlspecialchars($e['description'] ?? '') ?></td>
                <td>₹<?= number_format((float)($e['amount'] ?? 0), 0) ?></td>
                <td><small><?= htmlspecialchars($e['expense_date'] ?? '') ?></small></td>
                <td><span class="badge bg-<?= ($e['status'] ?? '') === 'approved' ? 'success' : 'warning' ?>"><?= htmlspecialchars($e['status'] ?? '') ?></span></td>
                <td><?= htmlspecialchars($e['category'] ?? '') ?></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table></div>
      </div></div>
    </div>

    <div class="tab-pane fade" id="tax">
      <div class="row">
        <div class="col-md-6">
          <h6>Tax Slabs</h6>
          <div class="card shadow-sm"><div class="card-body p-0">
            <div class="table-responsive"><table class="table mb-0">
              <thead class="table-light"><tr><th>Type</th><th>State</th><th>Min</th><th>Max</th><th>Rate</th></tr></thead>
              <tbody>
                <?php if (empty($taxSlabs)): ?>
                  <tr><td colspan="5" class="text-center py-3 text-muted">No tax slabs</td></tr>
                <?php else: foreach ($taxSlabs as $t): ?>
                  <tr>
                    <td><?= htmlspecialchars($t['tax_type'] ?? '') ?></td>
                    <td><?= htmlspecialchars($t['state_code'] ?? 'ALL') ?></td>
                    <td>₹<?= number_format((float)($t['min_amount'] ?? 0), 0) ?></td>
                    <td>₹<?= number_format((float)($t['max_amount'] ?? 0), 0) ?></td>
                    <td><?= htmlspecialchars($t['tax_rate'] ?? '') ?>%</td>
                  </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table></div>
          </div></div>
        </div>
        <div class="col-md-6">
          <h6>GST Returns</h6>
          <div class="card shadow-sm"><div class="card-body p-0">
            <div class="table-responsive"><table class="table mb-0">
              <thead class="table-light"><tr><th>Period</th><th>Sales</th><th>Tax</th><th>Filed</th></tr></thead>
              <tbody>
                <?php if (empty($gstReturns)): ?>
                  <tr><td colspan="4" class="text-center py-3 text-muted">No returns</td></tr>
                <?php else: foreach ($gstReturns as $g): ?>
                  <tr>
                    <td><?= htmlspecialchars($g['return_period'] ?? '') ?></td>
                    <td>₹<?= number_format((float)($g['total_sales'] ?? 0), 0) ?></td>
                    <td>₹<?= number_format((float)($g['tax_liability'] ?? 0), 0) ?></td>
                    <td><small><?= htmlspecialchars($g['filed_at'] ?? '') ?></small></td>
                  </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table></div>
          </div></div>
        </div>
      </div>
    </div>

    <div class="tab-pane fade" id="gst">
      <p>GST returns list above</p>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/admin/layouts/admin.php';
