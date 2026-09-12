<?php
// Cash Flow statement (FinancialReportService breakdown).
// Vars from FinancialReportController::cashFlow(): $data, $start_date, $end_date.
$data = $data ?? ['inflows' => [], 'outflows' => [], 'net_cash_flow' => 0];
$in = $data['inflows'] ?? [];
$out = $data['outflows'] ?? [];
$start_date = $start_date ?? '';
$end_date = $end_date ?? '';
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Cash Flow Statement</h1>
        <a href="<?php echo BASE_URL; ?>/admin/reports/financial/export?type=cash_flow&format=csv&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>" class="btn btn-sm btn-outline-secondary">Export CSV</a>
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

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-success text-white"><strong>Cash Inflows</strong></div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <tbody>
                            <tr><td>Booking Payments</td><td class="text-end">₹<?= number_format((float)($in['booking_payments'] ?? 0), 2) ?></td></tr>
                            <tr><td>EMI Receipts</td><td class="text-end">₹<?= number_format((float)($in['emi_receipts'] ?? 0), 2) ?></td></tr>
                            <tr class="table-success fw-bold"><td>Total Inflows</td><td class="text-end">₹<?= number_format((float)($in['total'] ?? 0), 2) ?></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-danger text-white"><strong>Cash Outflows</strong></div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <tbody>
                            <tr><td>Expense Payments</td><td class="text-end">₹<?= number_format((float)($out['expenses'] ?? 0), 2) ?></td></tr>
                            <tr><td>Commission Payouts</td><td class="text-end">₹<?= number_format((float)($out['commissions'] ?? 0), 2) ?></td></tr>
                            <tr><td>Salary Payments</td><td class="text-end">₹<?= number_format((float)($out['salaries'] ?? 0), 2) ?></td></tr>
                            <tr class="table-danger fw-bold"><td>Total Outflows</td><td class="text-end">₹<?= number_format((float)($out['total'] ?? 0), 2) ?></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Net Cash Flow</h5>
            <h4 class="mb-0 text-<?php echo ((float)($data['net_cash_flow'] ?? 0) >= 0) ? 'success' : 'danger'; ?>">₹<?= number_format((float)($data['net_cash_flow'] ?? 0), 2) ?></h4>
        </div>
    </div>
</div>
