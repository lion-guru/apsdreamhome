<?php
/**
 * Associate Wallet Transactions Page
 * @var array $transactions
 * @var float $walletBalance
 * @var array $summary
 * @var string $base
 * @var string $csrf_token
 * @var array $pagination
 */
$base = BASE_URL;
$page_title = $page_title ?? 'Wallet Transactions';
$pagination = $pagination ?? ['current_page' => 1, 'per_page' => 20, 'total' => 0, 'total_pages' => 1];
?>
<?php include __DIR__ . '/../layouts/associate.php'; ?>
<?php ob_start(); ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h4 class="mb-0"><i class="fas fa-history me-2 text-info"></i>Wallet Transactions</h4>
        <div class="d-flex gap-2">
            <a href="<?= $base ?>/associate/wallet/transactions/export" class="btn btn-outline-success btn-sm"><i class="fas fa-download me-1"></i>Export CSV</a>
            <a href="<?= $base ?>/associate/wallet/my" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back to Wallet</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center bg-gradient-info text-white h-100">
                <div class="card-body">
                    <div class="fs-4 fw-bold">₹<?= number_format($walletBalance ?? 0, 2) ?></div>
                    <small>Available Balance</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center bg-gradient-success text-white h-100">
                <div class="card-body">
                    <div class="fs-4 fw-bold"><?= $summary['total_transactions'] ?? 0 ?></div>
                    <small>Total Transactions</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center bg-gradient-primary text-white h-100">
                <div class="card-body">
                    <div class="fs-4 fw-bold">₹<?= number_format($summary['total_credited'] ?? 0, 2) ?></div>
                    <small>Total Credited</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small">Type</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="credit" <?= ($_GET['type'] ?? '') === 'credit' ? 'selected' : '' ?>>Credit</option>
                        <option value="debit" <?= ($_GET['type'] ?? '') === 'debit' ? 'selected' : '' ?>>Debit</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Category</label>
                    <select name="category" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="referral" <?= ($_GET['category'] ?? '') === 'referral' ? 'selected' : '' ?>>Referral</option>
                        <option value="commission" <?= ($_GET['category'] ?? '') === 'commission' ? 'selected' : '' ?>>Commission</option>
                        <option value="bonus" <?= ($_GET['category'] ?? '') === 'bonus' ? 'selected' : '' ?>>Bonus</option>
                        <option value="emi_transfer" <?= ($_GET['category'] ?? '') === 'emi_transfer' ? 'selected' : '' ?>>EMI Transfer</option>
                        <option value="withdrawal" <?= ($_GET['category'] ?? '') === 'withdrawal' ? 'selected' : '' ?>>Withdrawal</option>
                        <option value="adjustment" <?= ($_GET['category'] ?? '') === 'adjustment' ? 'selected' : '' ?>>Adjustment</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">From Date</label>
                    <input type="date" name="from_date" class="form-control form-control-sm" value="<?= htmlspecialchars($_GET['from_date'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">To Date</label>
                    <input type="date" name="to_date" class="form-control form-control-sm" value="<?= htmlspecialchars($_GET['to_date'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Search</label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Description/Ref" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100 btn-sm"><i class="fas fa-filter me-1"></i>Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (!empty($transactions)): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Category</th>
                            <th>Amount</th>
                            <th>Description</th>
                            <th>Reference</th>
                            <th>Balance After</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $tx):
                            $type = $tx['transaction_type'] ?? 'credit';
                            $isCredit = ($type === 'credit');
                        ?>
                        <tr>
                            <td><?= date('d M Y H:i', strtotime($tx['created_at'] ?? 'now')) ?></td>
                            <td>
                                <span class="badge bg-<?= $isCredit ? 'success' : 'danger' ?>">
                                    <i class="fas fa-arrow-<?= $isCredit ? 'down' : 'up' ?> me-1"></i>
                                    <?= ucfirst($type) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?= 
                                    ($tx['transaction_category'] ?? '') === 'referral' ? 'info' : 
                                    (($tx['transaction_category'] ?? '') === 'commission' ? 'warning' : 
                                    (($tx['transaction_category'] ?? '') === 'bonus' ? 'success' : 
                                    (($tx['transaction_category'] ?? '') === 'withdrawal' ? 'danger' : 'secondary'))) ?>">
                                    <?= ucfirst($tx['transaction_category'] ?? 'N/A') ?>
                                </span>
                            </td>
                            <td class="<?= $isCredit ? 'text-success' : 'text-danger' ?> fw-bold">
                                <?= $isCredit ? '+' : '-' ?>₹<?= number_format((float)($tx['amount'] ?? 0), 2) ?>
                            </td>
                            <td><small class="text-muted"><?= htmlspecialchars($tx['description'] ?? '-') ?></small></td>
                            <td>
                                <?php if (!empty($tx['reference_id'])): ?>
                                    <code class="small"><?= htmlspecialchars($tx['reference_id']) ?></code>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="fw-bold">₹<?= number_format((float)($tx['balance_after'] ?? 0), 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                <p class="text-muted">No transactions found</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Pagination -->
    <?php if (!empty($pagination) && $pagination['total_pages'] > 1): ?>
        <nav aria-label="Transaction pagination" class="mt-4">
            <ul class="pagination pagination-sm justify-content-center mb-0">
                <?php 
                $page = $pagination['current_page'] ?? 1;
                $totalPages = $pagination['total_pages'] ?? 1;
                $params = array_filter($_GET);
                unset($params['page']);
                $query = http_build_query($params);
                $querySuffix = $query ? '&' . $query : '';
                ?>
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page - 1 ?><?= $query ? '&' . $query : '' ?>" aria-label="Previous">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
                <?php
                $start = max(1, $page - 2);
                $end = min($totalPages, $page + 2);
                for ($i = $start; $i <= $end; $i++):
                ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?><?= $query ? '&' . $query : '' ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page + 1 ?><?= $query ? '&' . $query : '' ?>" aria-label="Next">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
        <div class="text-center text-muted small mt-2">
            Showing page <?= $page ?> of <?= $totalPages ?> (<?= $pagination['total'] ?? 0 ?> total records)
        </div>
    <?php endif; ?>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layouts/associate.php'; ?>