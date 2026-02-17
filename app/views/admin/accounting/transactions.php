<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><?= h($mlSupport->translate('All Transactions')) ?></h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard"><?= h($mlSupport->translate('Dashboard')) ?></a></li>
                    <li class="breadcrumb-item"><a href="/admin/accounting"><?= h($mlSupport->translate('Accounting')) ?></a></li>
                    <li class="breadcrumb-item active"><?= h($mlSupport->translate('Transactions')) ?></li>
                </ul>
            </div>
            <div class="col-auto">
                <a href="/admin/accounting/income/add" class="btn btn-success btn-sm me-2">
                    <i class="fas fa-plus me-1"></i> <?= h($mlSupport->translate('Add Income')) ?>
                </a>
                <a href="/admin/accounting/expense/add" class="btn btn-danger btn-sm">
                    <i class="fas fa-minus me-1"></i> <?= h($mlSupport->translate('Add Expense')) ?>
                </a>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 datatable">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4"><?= h($mlSupport->translate('Date')) ?></th>
                            <th><?= h($mlSupport->translate('Description')) ?></th>
                            <th><?= h($mlSupport->translate('Category/Source')) ?></th>
                            <th><?= h($mlSupport->translate('Type')) ?></th>
                            <th class="text-end pe-4"><?= h($mlSupport->translate('Amount')) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($transactions)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <?= h($mlSupport->translate('No transactions found')) ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($transactions as $transaction): ?>
                                <tr>
                                    <td class="ps-4"><?= h(date('d M Y', strtotime($transaction['date']))) ?></td>
                                    <td><?= h($transaction['description']) ?></td>
                                    <td><?= h($transaction['category'] ?? '-') ?></td>
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