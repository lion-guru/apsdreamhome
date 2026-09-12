<?php
// Executive P&L financial statement (printable). Var: $statement.
$st = $statement ?? ['income' => [], 'expenses' => [], 'ebitda' => 0, 'margin' => 0, 'start_date' => '', 'end_date' => ''];
$inc = $st['income'] ?? [];
$exp = $st['expenses'] ?? [];
function inr($v) { return '₹' . number_format((float)$v, 2); }
?>
<style>
@media print {
    .no-print { display: none !important; }
    .card { border: 1px solid #000 !important; box-shadow: none !important; }
}
.pl-table td, .pl-table th { padding: 10px 16px; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <h4 class="mb-0"><i class="fas fa-balance-scale me-2"></i>Profit &amp; Loss Statement</h4>
    <div class="d-flex gap-2">
        <a href="<?php echo BASE_URL; ?>/admin/reports/financial" class="btn btn-outline-secondary btn-sm"><i class="fas fa-chart-bar me-1"></i>Financial Reports</a>
        <button class="btn btn-primary btn-sm" onclick="window.print()"><i class="fas fa-print me-1"></i>Print</button>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="<?php echo BASE_URL; ?>/admin/reports/profit-loss" class="row g-3 align-items-end no-print">
            <div class="col-md-3">
                <label class="form-label">From</label>
                <input type="date" name="start_date" class="form-control" value="<?php echo e($st['start_date'] ?? ''); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">To</label>
                <input type="date" name="end_date" class="form-control" value="<?php echo e($st['end_date'] ?? ''); ?>">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter me-1"></i>Apply</button>
            </div>
        </form>
        <div class="text-center mt-2">
            <h5 class="mb-0">APS DREAM HOME</h5>
            <div class="text-muted">Profit &amp; Loss Statement · <?php echo e($st['start_date'] ?? ''); ?> to <?php echo e($st['end_date'] ?? ''); ?></div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-success text-white"><strong>INCOME</strong></div>
    <div class="card-body p-0">
        <table class="table pl-table mb-0">
            <tbody>
                <tr><td>Plot Bookings (active, in period)</td><td class="text-end"><?php echo inr($inc['booking_value'] ?? 0); ?></td></tr>
                <tr><td>Down Payments / EMI Collections (cleared receipts)</td><td class="text-end"><?php echo inr($inc['emi_collections'] ?? 0); ?></td></tr>
                <tr><td>Transfer Fees <small class="text-muted">(not tracked separately)</small></td><td class="text-end"><?php echo inr($inc['transfer_fees'] ?? 0); ?></td></tr>
                <tr><td>Interest Income <small class="text-muted">(not tracked separately)</small></td><td class="text-end"><?php echo inr($inc['interest'] ?? 0); ?></td></tr>
                <tr class="table-success fw-bold"><td>Total Income</td><td class="text-end"><?php echo inr($inc['total'] ?? 0); ?></td></tr>
            </tbody>
        </table>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-danger text-white"><strong>EXPENSES</strong></div>
    <div class="card-body p-0">
        <table class="table pl-table mb-0">
            <tbody>
                <tr><td>Land Purchases</td><td class="text-end"><?php echo inr($exp['land'] ?? 0); ?></td></tr>
                <tr><td>Colony Development</td><td class="text-end"><?php echo inr($exp['development'] ?? 0); ?></td></tr>
                <tr><td>Associate Commissions Paid</td><td class="text-end"><?php echo inr($exp['commissions'] ?? 0); ?></td></tr>
                <tr><td>Staff Salaries — salary payments</td><td class="text-end"><?php echo inr($exp['salaries_payments'] ?? 0); ?></td></tr>
                <tr><td>Staff Salaries — payslips</td><td class="text-end"><?php echo inr($exp['salaries_payslips'] ?? 0); ?></td></tr>
                <tr><td>Office &amp; Operational Overheads</td><td class="text-end"><?php echo inr($exp['overheads'] ?? 0); ?></td></tr>
                <tr class="table-danger fw-bold"><td>Total Expenses</td><td class="text-end"><?php echo inr($exp['total'] ?? 0); ?></td></tr>
            </tbody>
        </table>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body d-flex justify-content-between align-items-center <?php echo ($st['ebitda'] ?? 0) >= 0 ? 'bg-light' : ''; ?>">
        <div>
            <h5 class="mb-0">Net Operating Profit / EBITDA</h5>
            <small class="text-muted">Margin: <?php echo $st['margin'] ?? 0; ?>%</small>
        </div>
        <h4 class="mb-0 text-<?php echo ($st['ebitda'] ?? 0) >= 0 ? 'success' : 'danger'; ?>"><?php echo inr($st['ebitda'] ?? 0); ?></h4>
    </div>
</div>
