<?php
$deductions = $deductions ?? [];
$month_year = $month_year ?? date('Y-m');
$status = $status ?? '';
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>PF/ESI Register - <?= $month_year ?></h1>
        <a href="<?= BASE_URL ?>/admin/pf-esi" class="btn btn-secondary">Back to Dashboard</a>
    </div>

    <div class="card mb-4"><div class="card-body">
        <form method="GET" class="row">
            <div class="col-md-3"><label>Month</label><input type="month" name="month_year" class="form-control" value="<?= $month_year ?>"></div>
            <div class="col-md-2"><label>Status</label><select name="status" class="form-select"><option value="">All</option><option value="processed" <?= $status==='processed'?'selected':'' ?>>Processed</option><option value="paid" <?= $status==='paid'?'selected':'' ?>>Paid</option></select></div>
            <div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary w-100">Filter</button></div>
            <div class="col-md-2 d-flex align-items-end"><a href="<?= BASE_URL ?>/admin/pf-esi/register" class="btn btn-outline-secondary w-100">Clear</a></div>
        </form>
    </div></div>

    <?php if (!empty($deductions)) { ?>
    <div class="card mb-4"><div class="card-header"><strong>Mark as Paid</strong></div><div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/admin/pf-esi/mark-paid" class="row" onsubmit="return confirm('Mark selected records as paid?');">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <div class="col-md-4"><label>Payment Reference</label><input type="text" name="payment_ref" class="form-control" placeholder="e.g., PAY-20260101"></div>
            <div class="col-md-2 d-flex align-items-end"><button type="submit" class="btn btn-success w-100"><i class="fas fa-check me-2"></i>Mark Paid</button></div>
        </form>
    </div></div>
    <?php } ?>

    <div class="card"><div class="card-body p-0"><table class="table mb-0">
        <thead><tr><th></th><th>Employee</th><th>Department</th><th>Basic</th><th>Gross</th><th>PF Wage</th><th>PF Emp</th><th>PF Empr</th><th>ESI Wage</th><th>ESI Emp</th><th>ESI Empr</th><th>Total Ded</th><th>Status</th></tr></thead>
        <tbody>
        <?php if (empty($deductions)) { ?><tr><td colspan="13" class="text-center text-muted py-4">No records found</td></tr>
        <?php } else { foreach ($deductions as $d) { ?>
            <tr>
                <td><?php if ($d['status'] === 'processed') { ?><input type="checkbox" name="ids[]" value="<?= $d['id'] ?>" class="form-check-input"><?php } ?></td>
                <td><?= htmlspecialchars($d['employee_name']) ?></td>
                <td><?= htmlspecialchars($d['department'] ?? '—') ?></td>
                <td>₹<?= number_format($d['basic_salary'], 2) ?></td>
                <td>₹<?= number_format($d['gross_salary'], 2) ?></td>
                <td>₹<?= number_format($d['pf_wage'], 2) ?></td>
                <td>₹<?= number_format($d['pf_employee'], 2) ?></td>
                <td>₹<?= number_format($d['pf_employer'], 2) ?></td>
                <td>₹<?= number_format($d['esi_wage'], 2) ?></td>
                <td>₹<?= number_format($d['esi_employee'], 2) ?></td>
                <td>₹<?= number_format($d['esi_employer'], 2) ?></td>
                <td class="fw-bold">₹<?= number_format($d['total_deduction'], 2) ?></td>
                <td><?php if ($d['status'] === 'paid') { ?><span class="badge bg-success">Paid</span><?php } else { ?><span class="badge bg-warning">Processed</span><?php } ?></td>
            </tr>
        <?php } } ?>
        </tbody>
    </table></div></div>
</div>