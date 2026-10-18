<?php
$data = $data ?? ['employee' => null, 'ledger' => [], 'count' => 0];
$employees = $employees ?? [];
$employee_id = (int)($employee_id ?? 0);
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>PF Ledger</h1>
        <div>
            <form method="GET" class="d-inline">
                <label class="me-2" style="margin-bottom:0;">Employee:</label>
                <select name="employee_id" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()">
                    <option value="0">Select employee</option>
                    <?php foreach ($employees as $e): ?>
                        <option value="<?= (int)$e['id'] ?>" <?= $employee_id === (int)$e['id'] ? 'selected' : '' ?>><?= htmlspecialchars($e['name']) ?> (<?= htmlspecialchars($e['employee_code'] ?? $e['id']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </form>
            <a href="<?= BASE_URL ?>/admin/pf-esi" class="btn btn-outline-secondary ml-2"><i class="fas fa-arrow-left me-2"></i>Back</a>
        </div>
    </div>

    <?php if ($data['employee']): ?>
    <div class="card mb-4">
        <div class="card-header"><h5 class="card-title mb-0">Employee: <?= htmlspecialchars($data['employee']['name']) ?></h5></div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3"><strong>Employee ID:</strong> <?= (int)$data['employee']['id'] ?></div>
                <div class="col-md-3"><strong>Department:</strong> <?= htmlspecialchars($data['employee']['department'] ?? '-') ?></div>
                <div class="col-md-3"><strong>Designation:</strong> <?= htmlspecialchars($data['employee']['designation'] ?? '-') ?></div>
                <div class="col-md-3"><strong>Records:</strong> <?= (int)$data['count'] ?></div>
            </div>
        </div>
    </div>

    <?php if (!empty($data['ledger'])): ?>
    <div class="card">
        <div class="card-header"><h5 class="card-title mb-0">Monthly Ledger</h5></div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Month</th><th class="text-end">Basic</th><th class="text-end">Gross</th><th class="text-end">PF Wage</th><th class="text-end">PF Emp</th><th class="text-end">PF Er</th><th class="text-end">ESI Emp</th><th class="text-end">ESI Er</th><th class="text-end">Total Deduct</th><th class="text-end">Running PF</th><th class="text-end">Running Employer</th><th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($data['ledger'] as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['month_year']) ?></td>
                        <td class="text-end"><?= number_format((float)$r['basic_salary'], 2) ?></td>
                        <td class="text-end"><?= number_format((float)$r['gross_salary'], 2) ?></td>
                        <td class="text-end"><?= number_format((float)$r['pf_wage'], 2) ?></td>
                        <td class="text-end"><?= number_format((float)$r['pf_employee'], 2) ?></td>
                        <td class="text-end"><?= number_format((float)$r['pf_employer'], 2) ?></td>
                        <td class="text-end"><?= number_format((float)$r['esi_employee'], 2) ?></td>
                        <td class="text-end"><?= number_format((float)$r['esi_employer'], 2) ?></td>
                        <td class="text-end"><?= number_format((float)$r['total_deduction'], 2) ?></td>
                        <td class="text-end"><?= number_format((float)($r['running_pf_deduction'] ?? 0), 2) ?></td>
                        <td class="text-end"><?= number_format((float)($r['running_employer_cost'] ?? 0), 2) ?></td>
                        <td><span class="badge bg-<?= $r['status'] === 'paid' ? 'success' : ($r['status'] === 'processed' ? 'warning' : 'secondary') ?>"><?= ucfirst($r['status']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php else: ?>
    <div class="alert alert-info">No PF/ESI records found for this employee.</div>
    <?php endif; ?>
    <?php else: ?>
    <div class="alert alert-info">Please select an employee to view their PF ledger.</div>
    <?php endif; ?>
</div>
