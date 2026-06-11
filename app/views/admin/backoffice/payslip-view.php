<?php $payslip = $payslip ?? []; $gross = ($payslip['basic_salary']??0) + ($payslip['hra']??0) + ($payslip['allowances']??0); ?>
<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Payslip #<?= $payslip['id'] ?? '' ?></h1>
    <a href="<?= BASE_URL ?>/admin/backoffice/payslips" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
  </div>
  <div class="card aps-cp-card">
    <div class="card-body">
      <div class="row mb-4">
        <div class="col-md-6">
          <h5>Employee Details</h5>
          <p><strong>Name:</strong> <?= htmlspecialchars($payslip['employee_name'] ?? '') ?></p>
          <p><strong>Email:</strong> <?= htmlspecialchars($payslip['employee_email'] ?? '') ?></p>
          <p><strong>Period:</strong> <?= str_pad($payslip['period_month']??0, 2, '0', STR_PAD_LEFT) ?>/<?= $payslip['period_year'] ?? '' ?></p>
          <p><strong>Status:</strong> <span class="badge bg-<?= ($payslip['status'] ?? '') === 'paid' ? 'success' : 'warning' ?>"><?= ucfirst($payslip['status'] ?? '') ?></span></p>
        </div>
        <div class="col-md-6 text-md-end">
          <p><strong>Days Present:</strong> <?= $payslip['days_present'] ?? 0 ?></p>
          <p><strong>LOP Days:</strong> <?= $payslip['lop_days'] ?? 0 ?></p>
        </div>
      </div>
      <div class="row">
        <div class="col-md-6">
          <h6 class="text-success">Earnings</h6>
          <table class="table table-sm">
            <tr><td>Basic Salary</td><td class="text-end">&#8377;<?= number_format($payslip['basic_salary']??0, 2) ?></td></tr>
            <tr><td>HRA</td><td class="text-end">&#8377;<?= number_format($payslip['hra']??0, 2) ?></td></tr>
            <tr><td>Allowances</td><td class="text-end">&#8377;<?= number_format($payslip['allowances']??0, 2) ?></td></tr>
            <tr class="table-success"><td><strong>Gross</strong></td><td class="text-end"><strong>&#8377;<?= number_format($gross, 2) ?></strong></td></tr>
          </table>
        </div>
        <div class="col-md-6">
          <h6 class="text-danger">Deductions</h6>
          <table class="table table-sm">
            <tr><td>PF</td><td class="text-end">&#8377;<?= number_format($payslip['pf']??0, 2) ?></td></tr>
            <tr><td>ESI</td><td class="text-end">&#8377;<?= number_format($payslip['esi']??0, 2) ?></td></tr>
            <tr><td>TDS</td><td class="text-end">&#8377;<?= number_format($payslip['tds']??0, 2) ?></td></tr>
            <tr><td>Professional Tax</td><td class="text-end">&#8377;<?= number_format($payslip['professional_tax']??0, 2) ?></td></tr>
            <tr><td>Other Deductions</td><td class="text-end">&#8377;<?= number_format($payslip['deductions']??0, 2) ?></td></tr>
            <tr class="table-danger"><td><strong>Total Deductions</strong></td><td class="text-end"><strong>&#8377;<?= number_format(($payslip['deductions']??0)+($payslip['tds']??0)+($payslip['pf']??0)+($payslip['esi']??0)+($payslip['professional_tax']??0), 2) ?></strong></td></tr>
          </table>
        </div>
      </div>
      <div class="text-center mt-3 p-3 bg-light rounded">
        <h4>Net Salary: <span class="text-primary">&#8377;<?= number_format($payslip['net_salary']??0, 2) ?></span></h4>
      </div>
    </div>
  </div>
</div>
