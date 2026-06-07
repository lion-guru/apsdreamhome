<?php $payslips = $payslips ?? []; $employees = $employees ?? []; $filter_month = $filter_month ?? ''; $filter_year = $filter_year ?? ''; ?>
<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Employee Payslips</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generateModal"><i class="fas fa-plus me-1"></i>Generate Payslip</button>
  </div>
  <form class="row g-2 mb-4" method="get">
    <div class="col-auto"><input type="number" name="month" placeholder="Month" class="form-control" value="<?= $filter_month ?>" min="1" max="12"></div>
    <div class="col-auto"><input type="number" name="year" placeholder="Year" class="form-control" value="<?= $filter_year ?>"></div>
    <div class="col-auto"><button class="btn btn-primary">Filter</button></div>
  </form>
  <div class="card border-0 shadow-sm">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead><tr><th>Employee</th><th>Period</th><th>Basic</th><th>HRA</th><th>Gross</th><th>Deductions</th><th>Net</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
          <?php if (empty($payslips)): ?>
            <tr><td colspan="9" class="text-center text-muted py-4">No payslips found</td></tr>
          <?php else: ?>
            <?php foreach ($payslips as $p): ?>
              <?php $gross = ($p['basic_salary'] ?? 0) + ($p['hra'] ?? 0) + ($p['allowances'] ?? 0); ?>
              <tr>
                <td><?= htmlspecialchars($p['employee_name'] ?? '') ?></td>
                <td><?= str_pad($p['period_month'] ?? 0, 2, '0', STR_PAD_LEFT) ?>/<?= $p['period_year'] ?? '' ?></td>
                <td>&#8377;<?= number_format($p['basic_salary'] ?? 0, 2) ?></td>
                <td>&#8377;<?= number_format($p['hra'] ?? 0, 2) ?></td>
                <td>&#8377;<?= number_format($gross, 2) ?></td>
                <td>&#8377;<?= number_format(($p['deductions'] ?? 0) + ($p['tds'] ?? 0), 2) ?></td>
                <td><strong>&#8377;<?= number_format($p['net_salary'] ?? 0, 2) ?></strong></td>
                <td><span class="badge bg-<?= ($p['status'] ?? '') === 'paid' ? 'success' : (($p['status'] ?? '') === 'approved' ? 'primary' : 'warning') ?>"><?= ucfirst($p['status'] ?? '') ?></span></td>
                <td><a href="<?= BASE_URL ?>/admin/backoffice/payslips/<?= $p['id'] ?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-eye"></i></a></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<div class="modal fade" id="generateModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
  <form method="post" action="<?= BASE_URL ?>/admin/backoffice/payslips/generate">
    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
    <div class="modal-header"><h5 class="modal-title">Generate Payslip</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <div class="mb-3"><label class="form-label">Employee</label><select name="employee_id" class="form-select" required>
        <option value="">Select...</option>
        <?php foreach ($employees as $e): ?><option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['name'] ?? '') ?></option><?php endforeach; ?>
      </select></div>
      <div class="row"><div class="col-6 mb-3"><label class="form-label">Month</label><input type="number" name="period_month" class="form-control" value="<?= date('n') ?>" min="1" max="12" required></div>
      <div class="col-6 mb-3"><label class="form-label">Year</label><input type="number" name="period_year" class="form-control" value="<?= date('Y') ?>" required></div></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Generate</button></div>
  </form>
</div></div></div>
