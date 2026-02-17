<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><?= h($mlSupport->translate('Accounting Dashboard')) ?></h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard"><?= h($mlSupport->translate('Dashboard')) ?></a></li>
                    <li class="breadcrumb-item active"><?= h($mlSupport->translate('Accounting')) ?></li>
                </ul>
            </div>
            <div class="col-auto">
                <div class="btn-group">
                    <a href="/admin/accounting/income/add" class="btn btn-success">
                        <i class="fas fa-plus me-2"></i><?= h($mlSupport->translate('Add Income')) ?>
                    </a>
                    <a href="/admin/accounting/expense/add" class="btn btn-danger">
                        <i class="fas fa-minus me-2"></i><?= h($mlSupport->translate('Add Expense')) ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 col-lg-6 col-xl-6">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="card-title text-muted mb-0"><?= h($mlSupport->translate("Today's Income")) ?></h6>
                        <div class="icon-box bg-success-subtle rounded-circle p-2">
                            <i class="fas fa-arrow-down text-success"></i>
                        </div>
                    </div>
                    <h3 class="mb-0"><?= h($currency_symbol ?? '₹') ?><?= number_format($today_income ?? 0, 2) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-6 col-xl-6">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="card-title text-muted mb-0"><?= h($mlSupport->translate("Today's Expenses")) ?></h6>
                        <div class="icon-box bg-danger-subtle rounded-circle p-2">
                            <i class="fas fa-arrow-up text-danger"></i>
                        </div>
                    </div>
                    <h3 class="mb-0"><?= h($currency_symbol ?? '₹') ?><?= number_format($today_expenses ?? 0, 2) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0"><?= h($mlSupport->translate('Recent Transactions')) ?></h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4"><?= h($mlSupport->translate('Date')) ?></th>
                                    <th><?= h($mlSupport->translate('Description')) ?></th>
                                    <th><?= h($mlSupport->translate('Type')) ?></th>
                                    <th class="text-end pe-4"><?= h($mlSupport->translate('Amount')) ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recent_transactions)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            <?= h($mlSupport->translate('No recent transactions found')) ?>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recent_transactions as $transaction): ?>
                                        <tr>
                                            <td class="ps-4"><?= h(date('d M Y', strtotime($transaction['date']))) ?></td>
                                            <td><?= h($transaction['description']) ?></td>
                                            <td>
                                                <?php if ($transaction['type'] === 'Income'): ?>
                                                    <span class="badge bg-success-subtle text-success rounded-pill px-3"><?= h($mlSupport->translate('Income')) ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger-subtle text-danger rounded-pill px-3"><?= h($mlSupport->translate('Expense')) ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end pe-4 fw-bold <?= $transaction['type'] === 'Income' ? 'text-success' : 'text-danger' ?>">
                                                <?= $transaction['type'] === 'Income' ? '+' : '-' ?>
                                                <?= h($currency_symbol ?? '₹') ?><?= number_format($transaction['amount'], 2) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>