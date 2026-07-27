<?php
$page_title = $page_title ?? 'Payroll';
$page_heading = $page_heading ?? 'Payroll Management';
$content = $content ?? '';
ob_start();
?>
<div class="container-fluid py-4">
  <h1 class="h3 mb-4"><i class="fas fa-money-check-alt me-2"></i>Payroll Management</h1>

  <ul class="nav nav-tabs mb-3" id="payrollTabs" role="tablist">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#adv">Advances</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#bon">Bonuses</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#ent"><?= $currentMonth ?>/<?= $currentYear ?> Payroll</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#set">Settings</button></li>
  </ul>

  <div class="tab-content">
    <div class="tab-pane fade show active" id="adv">
      <div class="card shadow-sm">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light"><tr><th>Employee</th><th>Amount</th><th>Reason</th><th>Status</th><th>Requested</th></tr></thead>
              <tbody>
                <?php if (empty($advances)): ?>
                  <tr><td colspan="5" class="text-center py-4 text-muted">No advances</td></tr>
                <?php else: foreach ($advances as $a): ?>
                  <tr>
                    <td><?= htmlspecialchars($a['employee_name'] ?? 'N/A') ?></td>
                    <td>₹<?= number_format((float)($a['amount'] ?? 0), 2) ?></td>
                    <td><small><?= htmlspecialchars($a['reason'] ?? '') ?></small></td>
                    <td><span class="badge bg-<?= ($a['status'] ?? '') === 'approved' ? 'success' : 'warning' ?>"><?= htmlspecialchars($a['status'] ?? '') ?></span></td>
                    <td><small><?= htmlspecialchars($a['requested_at'] ?? '') ?></small></td>
                  </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="tab-pane fade" id="bon">
      <div class="card shadow-sm">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light"><tr><th>Employee</th><th>Amount</th><th>Type</th><th>Reason</th><th>Date</th></tr></thead>
              <tbody>
                <?php if (empty($bonuses)): ?>
                  <tr><td colspan="5" class="text-center py-4 text-muted">No bonuses</td></tr>
                <?php else: foreach ($bonuses as $b): ?>
                  <tr>
                    <td><?= htmlspecialchars($b['employee_name'] ?? 'N/A') ?></td>
                    <td>₹<?= number_format((float)($b['amount'] ?? 0), 2) ?></td>
                    <td><span class="badge bg-info"><?= htmlspecialchars($b['bonus_type'] ?? '') ?></span></td>
                    <td><small><?= htmlspecialchars($b['reason'] ?? '') ?></small></td>
                    <td><small><?= htmlspecialchars($b['given_at'] ?? '') ?></small></td>
                  </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="tab-pane fade" id="ent">
      <form method="POST" action="<?= BASE_URL ?>/api/v2/payroll/generate" class="card card-body mb-3">
                          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <div class="row g-2">
          <div class="col-md-3"><input name="month" type="number" class="form-control" value="<?= $currentMonth ?>" min="1" max="12" required></div>
          <div class="col-md-3"><input name="year" type="number" class="form-control" value="<?= $currentYear ?>" required></div>
          <div class="col-md-3"><button class="btn btn-primary">Generate</button></div>
        </div>
      </form>
      <div class="card shadow-sm">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light"><tr><th>Employee</th><th>Base</th><th>HRA</th><th>Allowances</th><th>Deductions</th><th>Net</th><th>Status</th></tr></thead>
              <tbody>
                <?php if (empty($entries)): ?>
                  <tr><td colspan="7" class="text-center py-4 text-muted">No payroll entries</td></tr>
                <?php else: foreach ($entries as $e): ?>
                  <tr>
                    <td><?= htmlspecialchars($e['employee_name'] ?? '') ?></td>
                    <td>₹<?= number_format((float)($e['base_salary'] ?? 0), 0) ?></td>
                    <td>₹<?= number_format((float)($e['hra'] ?? 0), 0) ?></td>
                    <td>₹<?= number_format((float)($e['allowances'] ?? 0), 0) ?></td>
                    <td>₹<?= number_format((float)($e['deductions'] ?? 0), 0) ?></td>
                    <td><strong>₹<?= number_format((float)($e['net_salary'] ?? 0), 0) ?></strong></td>
                    <td><span class="badge bg-<?= ($e['status'] ?? '') === 'paid' ? 'success' : 'secondary' ?>"><?= htmlspecialchars($e['status'] ?? '') ?></span></td>
                  </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="tab-pane fade" id="set">
      <div class="card card-body">
        <h5>Attendance Settings</h5>
        <pre class="bg-light p-3"><?= htmlspecialchars(json_encode($settings ?? [], JSON_PRETTY_PRINT)) ?></pre>
      </div>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';
