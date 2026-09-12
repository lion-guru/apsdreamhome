<?php
// Profit & Loss statement (FinancialReportService breakdown).
// Vars from FinancialReportController::profitLoss(): $data, $start_date, $end_date.
$data = $data ?? ['revenue' => [], 'expenses' => [], 'net_profit' => 0, 'profit_margin' => 0];
$rev = $data['revenue'] ?? [];
$exp = $data['expenses'] ?? [];
$start_date = $start_date ?? '';
$end_date = $end_date ?? '';
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Profit &amp; Loss Statement</h1>
        <div>
            <a href="<?php echo BASE_URL; ?>/admin/reports/financial/export?type=profit_loss&format=csv&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>" class="btn btn-sm btn-outline-secondary">Export CSV</a>
            <a href="<?php echo BASE_URL; ?>/admin/reports/profit-loss?start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>" class="btn btn-sm btn-outline-primary">Executive P&amp;L</a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Generate</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6>Total Revenue</h6>
                    <h3>₹<?= number_format((float)($rev['total'] ?? 0)) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h6>Total Expenses</h6>
                    <h3>₹<?= number_format((float)($exp['total'] ?? 0)) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6>Net Profit (<?= htmlspecialchars($data['profit_margin'] ?? 0) ?>%)</h6>
                    <h3>₹<?= number_format((float)($data['net_profit'] ?? 0)) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-success text-white"><strong>Revenue</strong></div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <tbody>
                            <tr><td>Plot Bookings</td><td class="text-end">₹<?= number_format((float)($rev['bookings'] ?? 0), 2) ?></td></tr>
                            <tr><td>EMI Collections</td><td class="text-end">₹<?= number_format((float)($rev['emi_collections'] ?? 0), 2) ?></td></tr>
                            <tr class="table-success fw-bold"><td>Total Revenue</td><td class="text-end">₹<?= number_format((float)($rev['total'] ?? 0), 2) ?></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-danger text-white"><strong>Expenses</strong></div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <tbody>
                            <tr><td>Operational Expenses</td><td class="text-end">₹<?= number_format((float)($exp['operational'] ?? 0), 2) ?></td></tr>
                            <tr><td>Commissions Paid</td><td class="text-end">₹<?= number_format((float)($exp['commissions'] ?? 0), 2) ?></td></tr>
                            <tr><td>Salaries Paid</td><td class="text-end">₹<?= number_format((float)($exp['salaries'] ?? 0), 2) ?></td></tr>
                            <tr class="table-danger fw-bold"><td>Total Expenses</td><td class="text-end">₹<?= number_format((float)($exp['total'] ?? 0), 2) ?></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
