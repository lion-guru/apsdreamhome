<?php
$config = $config ?? [];
$stats = $stats ?? ['total_employees'=>0,'total_pf_employee'=>0,'total_pf_employer'=>0,'total_esi_employee'=>0,'total_esi_employer'=>0,'total_deduction'=>0,'total_employer'=>0,'processed'=>0,'paid'=>0];
$month_year = $month_year ?? date('Y-m');
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>PF/ESI Compliance</h1>
        <a href="<?= BASE_URL ?>/admin/pf-esi/register?month_year=<?= $month_year ?>" class="btn btn-outline-secondary"><i class="fas fa-list me-2"></i>View Register</a>
    </div>

    <div class="card mb-4"><div class="card-body">
        <form method="GET" class="row">
            <div class="col-md-3"><label>Month</label><input type="month" name="month_year" class="form-control" value="<?= $month_year ?>"></div>
            <div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary w-100">Filter</button></div>
            <div class="col-md-2 d-flex align-items-end"><a href="<?= BASE_URL ?>/admin/pf-esi" class="btn btn-outline-secondary w-100">Clear</a></div>
        </form>
    </div></div>

    <div class="row mb-4">
        <div class="col-md-3"><div class="card bg-primary text-white"><div class="card-body"><h3><?= $stats['total_employees'] ?></h3><small>Employees</small></div></div></div>
        <div class="col-md-3"><div class="card bg-success text-white"><div class="card-body"><h3>₹<?= number_format($stats['total_pf_employee'] ?? 0) ?></h3><small>PF Employee (<?= $config['pf_employee_rate'] ?? 12 ?>%)</small></div></div></div>
        <div class="col-md-3"><div class="card bg-info text-white"><div class="card-body"><h3>₹<?= number_format($stats['total_pf_employer'] ?? 0) ?></h3><small>PF Employer (<?= $config['pf_employer_rate'] ?? 12 ?>%)</small></div></div></div>
        <div class="col-md-3"><div class="card bg-warning text-dark"><div class="card-body"><h3>₹<?= number_format($stats['total_esi_employee'] ?? 0) ?></h3><small>ESI Employee (<?= $config['esi_employee_rate'] ?? 0.75 ?>%)</small></div></div></div>
        <div class="col-md-3"><div class="card bg-danger text-white"><div class="card-body"><h3>₹<?= number_format($stats['total_esi_employer'] ?? 0) ?></h3><small>ESI Employer (<?= $config['esi_employer_rate'] ?? 3.25 ?>%)</small></div></div></div>
        <div class="col-md-3"><div class="card bg-secondary text-white"><div class="card-body"><h3>₹<?= number_format($stats['total_deduction'] ?? 0) ?></h3><small>Total Deduction</small></div></div></div>
        <div class="col-md-3"><div class="card bg-dark text-white"><div class="card-body"><h3>₹<?= number_format($stats['total_employer'] ?? 0) ?></h3><small>Total Employer Cost</small></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><h3><?= $stats['processed'] ?> / <?= $stats['paid'] ?></h3><small>Processed / Paid</small></div></div></div>
    </div>

    <div class="card mb-4"><div class="card-header"><strong>Configuration</strong></div><div class="card-body p-0"><table class="table mb-0">
        <thead><tr><th>Parameter</th><th>Value</th><th>Description</th></tr></thead>
        <tbody>
            <tr><td>PF Employee Rate</td><td><?= $config['pf_employee_rate'] ?? 12 ?>%</td><td>Employee contribution on PF wage</td></tr>
            <tr><td>PF Employer Rate</td><td><?= $config['pf_employer_rate'] ?? 12 ?>%</td><td>Employer contribution on PF wage</td></tr>
            <tr><td>PF Admin Charges</td><td><?= $config['pf_admin_rate'] ?? 1.61 ?>%</td><td>Admin charges on PF wage</td></tr>
            <tr><td>PF Wage Limit</td><td>₹<?= number_format($config['pf_wage_limit'] ?? 15000) ?></td><td>Monthly PF wage ceiling</td></tr>
            <tr><td>ESI Employee Rate</td><td><?= $config['esi_employee_rate'] ?? 0.75 ?>%</td><td>Employee contribution on gross</td></tr>
            <tr><td>ESI Employer Rate</td><td><?= $config['esi_employer_rate'] ?? 3.25 ?>%</td><td>Employer contribution on gross</td></tr>
            <tr><td>ESI Wage Limit</td><td>₹<?= number_format($config['esi_wage_limit'] ?? 21000) ?></td><td>Monthly ESI wage ceiling</td></tr>
        </tbody>
    </table></div></div>

    <div class="card"><div class="card-header"><strong>Process Month</strong></div><div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/admin/pf-esi/process" class="row" onsubmit="return confirm('Process PF/ESI for ' + document.getElementById('processMonth').value + '?');">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <div class="col-md-3"><label>Month to Process</label><input type="month" id="processMonth" name="month_year" class="form-control" value="<?= $month_year ?>" required></div>
            <div class="col-md-2 d-flex align-items-end"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-cogs me-2"></i>Process</button></div>
        </form>
    </div></div>
</div>