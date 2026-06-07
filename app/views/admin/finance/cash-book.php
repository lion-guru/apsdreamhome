<?php $page_title = $page_title ?? 'Cash Book'; $page_heading = $page_heading ?? 'Daily Cash Book'; $from = $from ?? date('Y-m-01'); $to = $to ?? date('Y-m-t'); $bank_id = $bank_id ?? null; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-book me-2 text-primary"></i>Daily Cash Book</h2>
        <a href="<?= BASE_URL ?>/admin/finance/transaction-form" class="btn btn-primary"><i class="fas fa-plus me-1"></i>New Transaction</a>
    </div>

    <div class="aps-cp-card mb-4">
        <div class="aps-cp-card-body">
            <form method="get" class="row g-2 align-items-end">
                <input type="hidden" name="url" value="/admin/finance/cash-book">
                <div class="col-md-3"><label class="form-label small">From</label><input type="date" name="from" value="<?= htmlspecialchars($from) ?>" class="form-control form-control-sm"></div>
                <div class="col-md-3"><label class="form-label small">To</label><input type="date" name="to" value="<?= htmlspecialchars($to) ?>" class="form-control form-control-sm"></div>
                <div class="col-md-3">
                    <label class="form-label small">Bank Account</label>
                    <select name="bank_account_id" class="form-select form-select-sm">
                        <option value="">All</option>
                        <?php foreach (($banks ?? []) as $b): ?>
                            <option value="<?= (int)$b['id'] ?>" <?= $bank_id == $b['id'] ? 'selected' : '' ?>><?= htmlspecialchars($b['account_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3"><button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter me-1"></i>Filter</button></div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="aps-cp-card"><div class="aps-cp-card-body"><div class="aps-cp-stat-label">Receipts</div><div class="aps-cp-stat-value text-success">₹<?= number_format((float)($summary['receipt'] ?? 0), 0) ?></div></div></div></div>
        <div class="col-md-3"><div class="aps-cp-card"><div class="aps-cp-card-body"><div class="aps-cp-stat-label">Payments</div><div class="aps-cp-stat-value text-danger">₹<?= number_format((float)($summary['payment'] ?? 0), 0) ?></div></div></div></div>
        <div class="col-md-3"><div class="aps-cp-card"><div class="aps-cp-card-body"><div class="aps-cp-stat-label">Contra</div><div class="aps-cp-stat-value text-secondary">₹<?= number_format((float)($summary['contra'] ?? 0), 0) ?></div></div></div></div>
        <div class="col-md-3"><div class="aps-cp-card"><div class="aps-cp-card-body"><div class="aps-cp-stat-label">Net</div><div class="aps-cp-stat-value <?= ((float)($summary['net'] ?? 0)) >= 0 ? 'text-primary' : 'text-danger' ?>">₹<?= number_format((float)($summary['net'] ?? 0), 0) ?></div></div></div></div>
    </div>

    <div class="aps-cp-card">
        <div class="aps-cp-card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Date</th><th>Voucher</th><th>Type</th><th>Party</th><th>Mode</th><th>Narration</th><th class="text-end">Amount</th></tr>
                </thead>
                <tbody>
                <?php if (empty($entries)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No transactions in this period</td></tr>
                <?php else: foreach ($entries as $e): ?>
                    <tr>
                        <td><?= htmlspecialchars($e['transaction_date'] ?? '') ?></td>
                        <td><code><?= htmlspecialchars($e['voucher_number'] ?? '-') ?></code></td>
                        <td><span class="badge bg-<?= ($e['transaction_type'] ?? '') === 'receipt' ? 'success' : 'danger' ?>"><?= htmlspecialchars($e['transaction_type'] ?? '') ?></span></td>
                        <td><?= htmlspecialchars($e['party_name'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($e['payment_mode'] ?? '-') ?></td>
                        <td><small><?= htmlspecialchars($e['narration'] ?? '') ?></small></td>
                        <td class="text-end fw-bold">₹<?= number_format((float)($e['amount'] ?? 0), 2) ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
