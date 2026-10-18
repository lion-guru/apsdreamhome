<?php
$fy = $fy ?? $taxService->currentFinancialYear();
$summary = $summary ?? [];
$schedule = $schedule ?? [];
$stats = $stats ?? [];
$tds = $tds ?? ['challans' => [], 'total_paid' => 0, 'count' => 0];
?>
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Tax Planning</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/admin/accounting">Accounting</a></li>
                        <li class="breadcrumb-item active">Tax Planning</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col-md-3">
                    <form method="get" action="/admin/tax-planning">
                        <div class="input-group">
                            <select name="fy" class="form-control">
                                <?php
                                $y = (int)substr($fy, 0, 4);
                                if ($y < 2000) { $y = (int)date('Y'); }
                                for ($i = $y; $i >= $y - 3; $i--) :
                                    $val = $i . '-' . ($i + 1);
                                ?>
                                    <option value="<?php echo $val; ?>" <?php if ($val === $fy) echo 'selected'; ?>><?php echo $val; ?></option>
                                <?php endfor; ?>
                            </select>
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit">Load</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-9 text-right">
                    <form id="generateForm" class="d-inline">
                        <input type="hidden" name="financial_year" value="<?php echo htmlspecialchars($fy); ?>">
                        <button type="submit" class="btn btn-success"><i class="fas fa-cogs"></i> Generate Schedule</button>
                    </form>
                </div>
            </div>

            <div class="row">
                <div class="col-md-2 col-6">
                    <div class="card text-center">
                        <div class="card-body py-2">
                            <div class="text-muted small">Projected Tax</div>
                            <div class="h5 mb-0 font-weight-bold"><?php echo number_format((float)($stats['projected_tax'] ?? 0), 2); ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-6">
                    <div class="card text-center">
                        <div class="card-body py-2">
                            <div class="text-muted small">Paid</div>
                            <div class="h5 mb-0 font-weight-bold text-success"><?php echo number_format((float)($stats['paid'] ?? 0), 2); ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-6">
                    <div class="card text-center">
                        <div class="card-body py-2">
                            <div class="text-muted small">Balance</div>
                            <div class="h5 mb-0 font-weight-bold text-warning"><?php echo number_format((float)($stats['balance'] ?? 0), 2); ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-6">
                    <div class="card text-center">
                        <div class="card-body py-2">
                            <div class="text-muted small">Outstanding</div>
                            <div class="h5 mb-0 font-weight-bold text-danger"><?php echo number_format((float)($stats['outstanding'] ?? 0), 2); ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-6">
                    <div class="card text-center">
                        <div class="card-body py-2">
                            <div class="text-muted small">Overdue</div>
                            <div class="h5 mb-0 font-weight-bold"><?php echo (int)($stats['overdue_count'] ?? 0); ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-6">
                    <div class="card text-center">
                        <div class="card-body py-2">
                            <div class="text-muted small">TDS Collected</div>
                            <div class="h5 mb-0 font-weight-bold text-info"><?php echo number_format((float)($stats['tds_collected'] ?? 0), 2); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Profit Summary (<?php echo htmlspecialchars($fy); ?>)</h3></div>
                        <div class="card-body p-0">
                            <table class="table mb-0">
                                <tr><th>Income</th><td class="text-right"><?php echo number_format((float)($summary['income'] ?? 0), 2); ?></td></tr>
                                <tr><th>Expense</th><td class="text-right"><?php echo number_format((float)($summary['expense'] ?? 0), 2); ?></td></tr>
                                <tr><th>Profit</th><td class="text-right font-weight-bold"><?php echo number_format((float)($summary['profit'] ?? 0), 2); ?></td></tr>
                                <tr><th>Tax @ <?php echo round(((float)($summary['rate'] ?? 0)) * 100, 2); ?>%</th><td class="text-right font-weight-bold"><?php echo number_format((float)($summary['tax'] ?? 0), 2); ?></td></tr>
                                <tr><th>TDS Collected</th><td class="text-right"><?php echo number_format((float)($summary['tds_collected'] ?? 0), 2); ?></td></tr>
                                <tr><th>Income Records</th><td class="text-right"><?php echo (int)($summary['income_count'] ?? 0); ?></td></tr>
                                <tr><th>Expense Records</th><td class="text-right"><?php echo (int)($summary['expense_count'] ?? 0); ?></td></tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">TDS Challans</h3></div>
                        <div class="card-body p-0">
                            <table class="table table-sm mb-0">
                                <thead><tr><th>Quarter</th><th>Deposit Date</th><th>Amount</th><th>Status</th></tr></thead>
                                <tbody>
                                <?php if (empty($tds['challans'])) : ?>
                                    <tr><td colspan="4" class="text-center text-muted py-2">No TDS challans</td></tr>
                                <?php else : ?>
                                    <?php foreach ($tds['challans'] as $c) : ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($c['quarter'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($c['deposit_date'] ?? ''); ?></td>
                                            <td><?php echo number_format((float)($c['total_amount'] ?? 0), 2); ?></td>
                                            <td><?php echo htmlspecialchars($c['status'] ?? ''); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header"><h3 class="card-title">Advance Tax Schedule</h3></div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Q</th>
                                <th>Due Date</th>
                                <th>Cumulative %</th>
                                <th>Due Amount</th>
                                <th>Paid</th>
                                <th>Status</th>
                                <th>Challan Ref</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($schedule as $q) : ?>
                            <tr>
                                <td><?php echo (int)$q['quarter']; ?></td>
                                <td><?php echo htmlspecialchars($q['due_date']); ?></td>
                                <td><?php echo (float)$q['cumulative_pct']; ?>%</td>
                                <td><?php echo number_format((float)$q['due_amount'], 2); ?></td>
                                <td><?php echo number_format((float)$q['paid_amount'], 2); ?></td>
                                <td>
                                    <?php if ($q['status'] === 'paid') : ?>
                                        <span class="badge bg-success">Paid</span>
                                    <?php elseif ($q['status'] === 'overdue') : ?>
                                        <span class="badge bg-danger">Overdue</span>
                                    <?php else : ?>
                                        <span class="badge bg-warning">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($q['challan_ref'] ?? ''); ?></td>
                                <td class="text-right">
                                    <?php if (!empty($q['db_id'])) : ?>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="openPay(<?php echo (int)$q['db_id']; ?>, <?php echo (float)$q['due_amount']; ?>)">Record Payment</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($schedule)) : ?>
                            <tr><td colspan="8" class="text-center text-muted py-3">No schedule. Generate first.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </section>
</div>

<div class="modal fade" id="payModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Record Advance Tax Payment</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="payForm">
                    <input type="hidden" name="id" id="payId">
                    <div class="form-group">
                        <label>Amount</label>
                        <input type="number" step="0.01" class="form-control" name="amount" id="payAmount" required>
                    </div>
                    <div class="form-group">
                        <label>Challan Ref</label>
                        <input type="text" class="form-control" name="challan_ref">
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea class="form-control" name="notes" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitPay()">Save</button>
            </div>
        </div>
    </div>
</div>

<script nonce="<?php echo htmlspecialchars($csp_nonce ?? ''); ?>">
function openPay(id, due) {
    document.getElementById('payId').value = id;
    document.getElementById('payAmount').value = due;
    $('#payModal').modal('show');
}
function submitPay() {
    var id = document.getElementById('payId').value;
    var form = document.getElementById('payForm');
    var data = new URLSearchParams(new FormData(form));
    data.append('csrf_token', '<?php echo $_SESSION['csrf_token'] ?? ''; ?>');
    fetch('/admin/tax-planning/record-payment/' + id, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
        body: data.toString()
    }).then(function(r){return r.json();}).then(function(d){
        if (d.success) { location.reload(); }
        else { alert(d.error || 'Failed'); }
    }).catch(function(){ alert('Request failed'); });
}
document.getElementById('generateForm').addEventListener('submit', function(e){
    e.preventDefault();
    var data = new URLSearchParams();
    data.append('csrf_token', '<?php echo $_SESSION['csrf_token'] ?? ''; ?>');
    data.append('financial_year', document.querySelector('input[name="financial_year"]').value);
    fetch('/admin/tax-planning/generate', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
        body: data.toString()
    }).then(function(r){return r.json();}).then(function(d){
        if (d.success) { location.reload(); }
        else { alert(d.error || 'Failed'); }
    }).catch(function(){ alert('Request failed'); });
});
</script>

<?php include __DIR__ . '/../../layouts/admin_footer.php'; ?>
