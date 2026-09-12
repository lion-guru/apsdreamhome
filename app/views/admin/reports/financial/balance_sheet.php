<?php
// Balance Sheet (FinancialReportService breakdown).
// Vars from FinancialReportController::balanceSheet(): $data, $as_of_date.
$data = $data ?? ['assets' => [], 'liabilities' => [], 'equity' => 0];
$assets = $data['assets'] ?? [];
$liab = $data['liabilities'] ?? [];
$as_of_date = $as_of_date ?? date('Y-m-d');
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Balance Sheet</h1>
        <a href="<?php echo BASE_URL; ?>/admin/reports/financial/export?type=balance_sheet&format=csv" class="btn btn-sm btn-outline-secondary">Export CSV</a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">As of Date</label>
                    <input type="date" name="as_of_date" class="form-control" value="<?= htmlspecialchars($as_of_date) ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Generate</button>
                </div>
                <div class="col-md-7 d-flex align-items-end justify-content-end text-muted">
                    As of <?= htmlspecialchars($as_of_date) ?>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-success text-white"><strong>Assets</strong></div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <tbody>
                            <tr><td>Cash in Bank</td><td class="text-end">₹<?= number_format((float)($assets['cash_in_bank'] ?? 0), 2) ?></td></tr>
                            <tr><td>Receivables (bookings due)</td><td class="text-end">₹<?= number_format((float)($assets['receivables'] ?? 0), 2) ?></td></tr>
                            <tr class="table-success fw-bold"><td>Total Assets</td><td class="text-end">₹<?= number_format((float)($assets['total'] ?? 0), 2) ?></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-danger text-white"><strong>Liabilities</strong></div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <tbody>
                            <tr><td>Pending Commission Payouts</td><td class="text-end">₹<?= number_format((float)($liab['pending_payouts'] ?? 0), 2) ?></td></tr>
                            <tr><td>Pending Salaries</td><td class="text-end">₹<?= number_format((float)($liab['pending_salaries'] ?? 0), 2) ?></td></tr>
                            <tr class="table-danger fw-bold"><td>Total Liabilities</td><td class="text-end">₹<?= number_format((float)($liab['total'] ?? 0), 2) ?></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Equity (Assets − Liabilities)</h5>
            <h4 class="mb-0 text-<?php echo ((float)($data['equity'] ?? 0) >= 0) ? 'success' : 'danger'; ?>">₹<?= number_format((float)($data['equity'] ?? 0), 2) ?></h4>
        </div>
    </div>
</div>
