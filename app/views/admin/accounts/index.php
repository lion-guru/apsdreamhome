<?php

/**
 * Accounts/Financial Management - APS Dream Home Admin
 * Dynamic stats from DB
 */
$page_title = $page_title ?? 'Accounts & Finance';
$page_description = $page_description ?? 'Financial management and accounting overview';

$totalBalance = $total_balance ?? 0;
$totalIncome = $total_income ?? 0;
$totalExpenses = $total_expenses ?? 0;
$netProfit = $net_profit ?? 0;
$margin = $margin ?? 0;
$recentIncome = $recent_income ?? [];
$recentExpenses = $recent_expenses ?? [];
$expenseByCategory = $expense_by_category ?? [];
$incomeByCategory = $income_by_category ?? [];
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-2">Accounts & Finance</h1>
            <p class="text-muted">Financial management and accounting overview</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-success bg-opacity-10 text-success rounded p-3">
                                <i class="fas fa-rupee-sign fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Total Income</h6>
                            <h3 class="mb-0">₹<?= number_format($totalIncome / 100000, 2) ?>L</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-danger bg-opacity-10 text-danger rounded p-3">
                                <i class="fas fa-money-bill-wave fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Expenses</h6>
                            <h3 class="mb-0">₹<?= number_format($totalExpenses / 100000, 2) ?>L</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-primary bg-opacity-10 text-primary rounded p-3">
                                <i class="fas fa-piggy-bank fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Net Profit</h6>
                            <h3 class="mb-0 <?= $netProfit >= 0 ? 'text-success' : 'text-danger' ?>">₹<?= number_format(abs($netProfit) / 100000, 2) ?>L</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-info bg-opacity-10 text-info rounded p-3">
                                <i class="fas fa-university fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Bank Balance</h6>
                            <h3 class="mb-0">₹<?= number_format($totalBalance, 0) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Income by Category + Expense by Category -->
    <div class="row mb-4">
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-chart-bar text-success me-2"></i>Income by Category</h5>
                </div>
                <div class="card-body aps-cp-card-body p-0">
                    <?php if (empty($incomeByCategory)): ?>
                        <p class="text-muted text-center py-3">No income records yet</p>
                    <?php else: ?>
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Category</th><th class="text-end">Amount</th><th class="text-end">Count</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($incomeByCategory as $inc): ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $inc['income_category']))) ?></td>
                                <td class="text-end text-success fw-bold">₹<?= number_format((float)$inc['total'], 0) ?></td>
                                <td class="text-end"><?= (int)$inc['cnt'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-chart-bar text-danger me-2"></i>Expenses by Category</h5>
                </div>
                <div class="card-body aps-cp-card-body p-0">
                    <?php if (empty($expenseByCategory)): ?>
                        <p class="text-muted text-center py-3">No expenses recorded yet</p>
                    <?php else: ?>
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Category</th><th class="text-end">Amount</th><th class="text-end">Count</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($expenseByCategory as $exp): ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($exp['category'] ?? '') ?></td>
                                <td class="text-end text-danger fw-bold">₹<?= number_format((float)$exp['total'], 0) ?></td>
                                <td class="text-end"><?= (int)$exp['cnt'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="row mb-4">
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-arrow-up text-success me-2"></i>Recent Income</h5>
                    <a href="<?= BASE_URL ?>/admin/accounting/income" class="btn btn-sm btn-outline-success">View All</a>
                </div>
                <div class="card-body aps-cp-card-body p-0">
                    <?php if (empty($recentIncome)): ?>
                        <p class="text-muted text-center py-3">No income records</p>
                    <?php else: ?>
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Date</th><th>Category</th><th>Customer</th><th class="text-end">Amount</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($recentIncome as $inc): ?>
                            <tr>
                                <td><small class="text-muted"><?= date('d M Y', strtotime($inc['income_date'])) ?></small></td>
                                <td><span class="badge bg-success bg-opacity-10 text-success"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $inc['income_category']))) ?></span></td>
                                <td><?= htmlspecialchars($inc['customer_name'] ?? '—') ?></td>
                                <td class="text-end fw-bold">₹<?= number_format((float)$inc['amount'], 0) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-arrow-down text-danger me-2"></i>Recent Expenses</h5>
                    <a href="<?= BASE_URL ?>/admin/accounting/expenses" class="btn btn-sm btn-outline-danger">View All</a>
                </div>
                <div class="card-body aps-cp-card-body p-0">
                    <?php if (empty($recentExpenses)): ?>
                        <p class="text-muted text-center py-3">No expenses recorded</p>
                    <?php else: ?>
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Date</th><th>Category</th><th>Description</th><th class="text-end">Amount</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($recentExpenses as $exp): ?>
                            <tr>
                                <td><small class="text-muted"><?= date('d M Y', strtotime($exp['created_at'] ?? $exp['expense_date'] ?? 'now')) ?></small></td>
                                <td><span class="badge bg-danger bg-opacity-10 text-danger"><?= htmlspecialchars($exp['category'] ?? '') ?></span></td>
                                <td><?= htmlspecialchars(mb_strimwidth($exp['description'] ?? '', 0, 40, '...')) ?></td>
                                <td class="text-end fw-bold">₹<?= number_format((float)$exp['amount'], 0) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Financial Tools</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="<?= BASE_URL ?>/admin/accounting" class="btn btn-outline-primary w-100 py-3">
                                <i class="fas fa-book mb-2 d-block style-41417"></i>
                                Accounting
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?= BASE_URL ?>/admin/accounting/income" class="btn btn-outline-success w-100 py-3">
                                <i class="fas fa-arrow-up mb-2 d-block style-41417"></i>
                                Income
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?= BASE_URL ?>/admin/accounting/expenses" class="btn btn-outline-danger w-100 py-3">
                                <i class="fas fa-arrow-down mb-2 d-block style-41417"></i>
                                Expenses
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?= BASE_URL ?>/admin/finance/bank-accounts" class="btn btn-outline-info w-100 py-3">
                                <i class="fas fa-university mb-2 d-block style-41417"></i>
                                Bank Accounts
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
