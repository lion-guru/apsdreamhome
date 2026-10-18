<?php
$data = $data ?? [];
$start_date = $start_date ?? date('Y-m-01');
$end_date = $end_date ?? date('Y-m-d');
$sources = $data['sources'] ?? [];
$uses = $data['uses'] ?? [];
$components = $data['components'] ?? [];
?>
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Fund Flow Statement</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/admin/reports/financial">Financial Reports</a></li>
                        <li class="breadcrumb-item active">Fund Flow</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card mb-3">
                <div class="card-body">
                    <form method="get" class="row">
                        <div class="col-md-3">
                            <label>Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($start_date); ?>">
                        </div>
                        <div class="col-md-3">
                            <label>End Date</label>
                            <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($end_date); ?>">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn btn-primary w-100">Generate</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header bg-success text-white"><strong>Sources of Funds</strong></div>
                        <div class="card-body p-0">
                            <table class="table mb-0">
                                <tbody>
                                    <tr><td>Net Profit</td><td class="text-right"><?php echo number_format((float)($sources['net_profit'] ?? 0), 2); ?></td></tr>
                                    <tr><td>Depreciation</td><td class="text-right"><?php echo number_format((float)($sources['depreciation'] ?? 0), 2); ?></td></tr>
                                    <tr><td>Decrease in Receivables</td><td class="text-right"><?php echo number_format((float)($sources['decrease_receivables'] ?? 0), 2); ?></td></tr>
                                    <tr><td>Decrease in Inventory</td><td class="text-right"><?php echo number_format((float)($sources['decrease_inventory'] ?? 0), 2); ?></td></tr>
                                    <tr><td>Increase in Payables</td><td class="text-right"><?php echo number_format((float)($sources['increase_payables'] ?? 0), 2); ?></td></tr>
                                    <tr><td>Fixed Asset Disposals</td><td class="text-right"><?php echo number_format((float)($sources['fixed_asset_disposals'] ?? 0), 2); ?></td></tr>
                                    <tr><td>Increase in Long-Term Liabilities</td><td class="text-right"><?php echo number_format((float)($sources['increase_long_term_liabilities'] ?? 0), 2); ?></td></tr>
                                    <tr><td>Issue of Capital</td><td class="text-right"><?php echo number_format((float)($sources['issue_of_capital'] ?? 0), 2); ?></td></tr>
                                    <tr class="table-success fw-bold"><td>Total Sources</td><td class="text-right"><?php echo number_format((float)($data['total_sources'] ?? 0), 2); ?></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header bg-danger text-white"><strong>Uses of Funds</strong></div>
                        <div class="card-body p-0">
                            <table class="table mb-0">
                                <tbody>
                                    <tr><td>Increase in Receivables</td><td class="text-right"><?php echo number_format((float)($uses['increase_receivables'] ?? 0), 2); ?></td></tr>
                                    <tr><td>Increase in Inventory</td><td class="text-right"><?php echo number_format((float)($uses['increase_inventory'] ?? 0), 2); ?></td></tr>
                                    <tr><td>Decrease in Payables</td><td class="text-right"><?php echo number_format((float)($uses['decrease_payables'] ?? 0), 2); ?></td></tr>
                                    <tr><td>Purchase of Fixed Assets</td><td class="text-right"><?php echo number_format((float)($uses['purchase_fixed_assets'] ?? 0), 2); ?></td></tr>
                                    <tr><td>Decrease in Long-Term Liabilities</td><td class="text-right"><?php echo number_format((float)($uses['decrease_long_term_liabilities'] ?? 0), 2); ?></td></tr>
                                    <tr><td>Dividends</td><td class="text-right"><?php echo number_format((float)($uses['dividends'] ?? 0), 2); ?></td></tr>
                                    <tr class="table-danger fw-bold"><td>Total Uses</td><td class="text-right"><?php echo number_format((float)($data['total_uses'] ?? 0), 2); ?></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Net Change in Working Capital</h5>
                    <h4 class="mb-0 text-<?php echo ((float)($data['net_change'] ?? 0) >= 0) ? 'success' : 'danger'; ?>"><?php echo number_format((float)($data['net_change'] ?? 0), 2); ?></h4>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3 class="card-title">Working Capital Components</h3></div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Component</th>
                                <th class="text-right">Opening</th>
                                <th class="text-right">Closing</th>
                                <th class="text-right">Change</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Receivables</td>
                                <td class="text-right"><?php echo number_format((float)($components['receivables']['opening'] ?? 0), 2); ?></td>
                                <td class="text-right"><?php echo number_format((float)($components['receivables']['closing'] ?? 0), 2); ?></td>
                                <td class="text-right"><?php echo number_format((float)($components['receivables']['change'] ?? 0), 2); ?></td>
                            </tr>
                            <tr>
                                <td>Inventory</td>
                                <td class="text-right"><?php echo number_format((float)($components['inventory']['opening'] ?? 0), 2); ?></td>
                                <td class="text-right"><?php echo number_format((float)($components['inventory']['closing'] ?? 0), 2); ?></td>
                                <td class="text-right"><?php echo number_format((float)($components['inventory']['change'] ?? 0), 2); ?></td>
                            </tr>
                            <tr>
                                <td>Payables</td>
                                <td class="text-right"><?php echo number_format((float)($components['payables']['opening'] ?? 0), 2); ?></td>
                                <td class="text-right"><?php echo number_format((float)($components['payables']['closing'] ?? 0), 2); ?></td>
                                <td class="text-right"><?php echo number_format((float)($components['payables']['change'] ?? 0), 2); ?></td>
                            </tr>
                            <tr class="fw-bold">
                                <td>Net Working Capital</td>
                                <td class="text-right"><?php echo number_format((float)($data['opening_working_capital'] ?? 0), 2); ?></td>
                                <td class="text-right"><?php echo number_format((float)($data['closing_working_capital'] ?? 0), 2); ?></td>
                                <td class="text-right"><?php echo number_format((float)($data['working_capital_change'] ?? 0), 2); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include __DIR__ . '/../../layouts/admin_footer.php'; ?>
