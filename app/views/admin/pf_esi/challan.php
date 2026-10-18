<?php
$data = $data ?? ['month_year' => date('Y-m'), 'est_code' => '', 'esi_code' => '', 'deductions' => [], 'count' => 0, 'totals' => [], 'grand_total' => 0, 'generated_at' => ''];
$month_year = $month_year ?? date('Y-m');
$month_years = $month_years ?? [];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>PF/ESI Challan</h1>
        <div>
            <form method="GET" class="d-inline">
                <label class="me-2" style="margin-bottom:0;">Month:</label>
                <select name="month_year" class="form-select form-select-sm d-inline w-auto">
                    <?php foreach ($month_years as $my): ?>
                        <option value="<?= $my['month_year'] ?>" <?= $my['month_year'] === $month_year ? 'selected' : '' ?>><?= htmlspecialchars($my['month_year']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary btn-sm ml-2">View</button>
            </form>
            <a href="<?= BASE_URL ?>/admin/pf-esi" class="btn btn-outline-secondary ml-2"><i class="fas fa-arrow-left me-2"></i>Back</a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><h5 class="card-title mb-0">Challan Details</h5></div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <strong>Establishment Code:</strong> <?= htmlspecialchars($data['est_code']) ?><br>
                    <strong>ESI Code:</strong> <?= htmlspecialchars($data['esi_code']) ?><br>
                    <strong>Month:</strong> <?= htmlspecialchars($month_year) ?><br>
                    <strong>Generated:</strong> <?= htmlspecialchars($data['generated_at']) ?>
                </div>
                <div class="col-md-4">
                    <strong>Employees:</strong> <?= (int)$data['count'] ?><br>
                    <strong>Total Deduction:</strong> ₹<?= number_format((float)($data['totals']['total_deduction'] ?? 0), 2) ?><br>
                    <strong>Total Employer:</strong> ₹<?= number_format((float)($data['totals']['total_employer'] ?? 0), 2) ?><br>
                    <strong>Grand Total:</strong> ₹<?= number_format((float)$data['grand_total'], 2) ?>
                </div>
                <div class="col-md-4">
                    <strong>PF Employee:</strong> ₹<?= number_format((float)($data['totals']['pf_employee'] ?? 0), 2) ?><br>
                    <strong>PF Employer:</strong> ₹<?= number_format((float)($data['totals']['pf_employer'] ?? 0), 2) ?><br>
                    <strong>PF Admin:</strong> ₹<?= number_format((float)($data['totals']['pf_admin_charges'] ?? 0), 2) ?><br>
                    <strong>ESI Employee:</strong> ₹<?= number_format((float)($data['totals']['esi_employee'] ?? 0), 2) ?><br>
                    <strong>ESI Employer:</strong> ₹<?= number_format((float)($data['totals']['esi_employer'] ?? 0), 2) ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($data['deductions'])): ?>
    <div class="card">
        <div class="card-header"><h5 class="card-title mb-0">Employee Breakdown</h5></div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Emp ID</th><th>Name</th><th>Department</th><th class="text-end">Basic</th><th class="text-end">Gross</th><th class="text-end">PF Wage</th><th class="text-end">ESI Wage</th><th class="text-end">PF Emp</th><th class="text-end">PF Er</th><th class="text-end">ESI Emp</th><th class="text-end">ESI Er</th><th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($data['deductions'] as $d): ?>
                    <tr>
                        <td><?= (int)$d['employee_id'] ?></td>
                        <td><?= htmlspecialchars($d['employee_name']) ?></td>
                        <td><?= htmlspecialchars($d['department'] ?? '-') ?></td>
                        <td class="text-end"><?= number_format((float)$d['basic_salary'], 2) ?></td>
                        <td class="text-end"><?= number_format((float)$d['gross_salary'], 2) ?></td>
                        <td class="text-end"><?= number_format((float)$d['pf_wage'], 2) ?></td>
                        <td class="text-end"><?= number_format((float)$d['esi_wage'], 2) ?></td>
                        <td class="text-end"><?= number_format((float)$d['pf_employee'], 2) ?></td>
                        <td class="text-end"><?= number_format((float)$d['pf_employer'], 2) ?></td>
                        <td class="text-end"><?= number_format((float)$d['esi_employee'], 2) ?></td>
                        <td class="text-end"><?= number_format((float)$d['esi_employer'], 2) ?></td>
                        <td class="text-end font-weight-bold"><?= number_format((float)$d['total_deduction'] + (float)$d['total_employer'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="table-light font-weight-bold">
                    <td colspan="4" class="text-end">Total</td>
                    <td class="text-end"><?= number_format(array_sum(array_map(fn($d) => (float)$d['basic_salary'], $data['deductions'])), 2) ?></td>
                    <td class="text-end"><?= number_format(array_sum(array_map(fn($d) => (float)$d['gross_salary'], $data['deductions'])), 2) ?></td>
                    <td class="text-end"><?= number_format((float)$data['totals']['pf_wage'] ?? 0, 2) ?></td>
                    <td class="text-end"><?= number_format((float)$data['totals']['esi_wage'] ?? 0, 2) ?></td>
                    <td class="text-end"><?= number_format((float)$data['totals']['pf_employee'] ?? 0, 2) ?></td>
                    <td class="text-end"><?= number_format((float)$data['totals']['pf_employer'] ?? 0, 2) ?></td>
                    <td class="text-end"><?= number_format((float)$data['totals']['esi_employee'] ?? 0, 2) ?></td>
                    <td class="text-end"><?= number_format((float)$data['totals']['esi_employer'] ?? 0, 2) ?></td>
                    <td class="text-end"><?= number_format((float)$data['grand_total'], 2) ?></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <?php else: ?>
    <div class="alert alert-info">No paid deductions found for <?= htmlspecialchars($month_year) ?>. Process the month first.</div>
    <?php endif; ?>
</div>
