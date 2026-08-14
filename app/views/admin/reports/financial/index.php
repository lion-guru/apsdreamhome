<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Financial Reports</h1>
        <div>
            <a href="/admin/reports/financial/export?type=profit_loss&format=csv" class="btn btn-sm btn-outline-secondary">Export CSV</a>
            <a href="/admin/reports/financial/export?type=profit_loss&format=excel" class="btn btn-sm btn-outline-secondary">Export Excel</a>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
    <?php echo CSRFProtection::csrfField(); ?>
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

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6>Total Revenue</h6>
                    <h3>₹<?= number_format($profit_loss['revenue']['total']) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h6>Total Expenses</h6>
                    <h3>₹<?= number_format($profit_loss['expenses']['total']) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6>Net Profit</h6>
                    <h3>₹<?= number_format($profit_loss['net_profit']) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6>Profit Margin</h6>
                    <h3><?= $profit_loss['profit_margin'] ?>%</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Profit & Loss -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><h5>Profit & Loss</h5></div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr><td>Bookings</td><td class="text-end">₹<?= number_format($profit_loss['revenue']['bookings']) ?></td></tr>
                        <tr><td>EMI Collections</td><td class="text-end">₹<?= number_format($profit_loss['revenue']['emi_collections']) ?></td></tr>
                        <tr class="table-success fw-bold"><td>Total Revenue</td><td class="text-end">₹<?= number_format($profit_loss['revenue']['total']) ?></td></tr>
                        <tr><td>Operational</td><td class="text-end">₹<?= number_format($profit_loss['expenses']['operational']) ?></td></tr>
                        <tr><td>Commissions</td><td class="text-end">₹<?= number_format($profit_loss['expenses']['commissions']) ?></td></tr>
                        <tr><td>Salaries</td><td class="text-end">₹<?= number_format($profit_loss['expenses']['salaries']) ?></td></tr>
                        <tr class="table-danger fw-bold"><td>Total Expenses</td><td class="text-end">₹<?= number_format($profit_loss['expenses']['total']) ?></td></tr>
                        <tr class="table-primary fw-bold"><td>Net Profit</td><td class="text-end">₹<?= number_format($profit_loss['net_profit']) ?></td></tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><h5>Balance Sheet (as of <?= $balance_sheet['as_of_date'] ?>)</h5></div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr><td>Cash in Bank</td><td class="text-end">₹<?= number_format($balance_sheet['assets']['cash_in_bank']) ?></td></tr>
                        <tr><td>Receivables</td><td class="text-end">₹<?= number_format($balance_sheet['assets']['receivables']) ?></td></tr>
                        <tr class="table-success fw-bold"><td>Total Assets</td><td class="text-end">₹<?= number_format($balance_sheet['assets']['total']) ?></td></tr>
                        <tr><td>Pending Payouts</td><td class="text-end">₹<?= number_format($balance_sheet['liabilities']['pending_payouts']) ?></td></tr>
                        <tr><td>Pending Salaries</td><td class="text-end">₹<?= number_format($balance_sheet['liabilities']['pending_salaries']) ?></td></tr>
                        <tr class="table-danger fw-bold"><td>Total Liabilities</td><td class="text-end">₹<?= number_format($balance_sheet['liabilities']['total']) ?></td></tr>
                        <tr class="table-primary fw-bold"><td>Equity</td><td class="text-end">₹<?= number_format($balance_sheet['equity']) ?></td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Cash Flow -->
    <div class="card">
        <div class="card-header"><h5>Cash Flow Statement</h5></div>
        <div class="card-body">
            <table class="table table-sm">
                <tr><td>Booking Payments</td><td class="text-end">₹<?= number_format($cash_flow['inflows']['booking_payments']) ?></td></tr>
                <tr><td>EMI Receipts</td><td class="text-end">₹<?= number_format($cash_flow['inflows']['emi_receipts']) ?></td></tr>
                <tr class="table-success fw-bold"><td>Total Inflows</td><td class="text-end">₹<?= number_format($cash_flow['inflows']['total']) ?></td></tr>
                <tr><td>Expenses</td><td class="text-end">₹<?= number_format($cash_flow['outflows']['expenses']) ?></td></tr>
                <tr><td>Commissions</td><td class="text-end">₹<?= number_format($cash_flow['outflows']['commissions']) ?></td></tr>
                <tr><td>Salaries</td><td class="text-end">₹<?= number_format($cash_flow['outflows']['salaries']) ?></td></tr>
                <tr class="table-danger fw-bold"><td>Total Outflows</td><td class="text-end">₹<?= number_format($cash_flow['outflows']['total']) ?></td></tr>
                <tr class="table-primary fw-bold"><td>Net Cash Flow</td><td class="text-end">₹<?= number_format($cash_flow['net_cash_flow']) ?></td></tr>
            </table>
        </div>
    </div>
</div>
