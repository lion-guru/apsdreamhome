<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-book me-2"></i>Khatabook Sales Records</h1>
        <div>
            <a href="<?= BASE_URL ?>/admin/import-export/import" class="btn btn-primary"><i class="fas fa-upload me-1"></i>Import CSV</a>
            <a href="<?= BASE_URL ?>/admin/import-export/template/khatabook_sales" class="btn btn-outline-secondary"><i class="fas fa-download me-1"></i>Template</a>
            <a href="<?= BASE_URL ?>/admin/khatabook-sales/export" class="btn btn-success"><i class="fas fa-file-export me-1"></i>Export</a>
        </div>
    </div>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?> alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['flash_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body aps-cp-card-body">
                    <h6>Total Records</h6>
                    <h3 class="mb-0"><?= number_format((int)($summary['total_records'] ?? 0)) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body aps-cp-card-body">
                    <h6>Total Amount</h6>
                    <h3 class="mb-0">₹<?= number_format((float)($summary['total_amount'] ?? 0), 2) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card aps-cp-card">
                <div class="card-body aps-cp-card-body">
                    <form method="GET" class="row g-2">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search name/phone/ref..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="date_from" class="form-control form-control-sm" value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>">
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="date_to" class="form-control form-control-sm" value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">
                        </div>
                        <div class="col-md-2">
                            <select name="batch" class="form-select form-select-sm">
                                <option value="">All Batches</option>
                                <?php foreach ($batches as $b): ?>
                                    <option value="<?= htmlspecialchars($b['import_batch']) ?>" <?= ($_GET['batch'] ?? '') === $b['import_batch'] ? 'selected' : '' ?>><?= htmlspecialchars(substr($b['import_batch'], 0, 20)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-sm btn-primary w-100"><i class="fas fa-search"></i> Filter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Phone</th>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Rate</th>
                            <th>Amount</th>
                            <th>Payment</th>
                            <th>Ref No</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($sales)): ?>
                            <tr><td colspan="11" class="text-center text-muted py-4">No sales records found. <a href="<?= BASE_URL ?>/admin/import-export/import">Import CSV data</a></td></tr>
                        <?php else: ?>
                            <?php $i = ($page - 1) * $perPage; foreach ($sales as $s): $i++; ?>
                                <tr>
                                    <td><?= $i ?></td>
                                    <td><?= htmlspecialchars($s['transaction_date']) ?></td>
                                    <td><strong><?= htmlspecialchars($s['customer_name']) ?></strong></td>
                                    <td><?= htmlspecialchars($s['customer_phone'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars(mb_substr($s['item_description'] ?? '-', 0, 40)) ?></td>
                                    <td><?= $s['quantity'] > 0 ? number_format($s['quantity'], 2) : '-' ?></td>
                                    <td>₹<?= number_format($s['rate'] ?? 0, 2) ?></td>
                                    <td><strong>₹<?= number_format($s['amount'] ?? 0, 2) ?></strong></td>
                                    <td><?= htmlspecialchars($s['payment_method'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($s['reference_no'] ?? '-') ?></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/admin/khatabook-sales/view/<?= $s['id'] ?>" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php if ($totalPages > 1): ?>
        <nav class="mt-3">
            <ul class="pagination pagination-sm justify-content-center">
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $p ?>&search=<?= urlencode($_GET['search'] ?? '') ?>&date_from=<?= urlencode($_GET['date_from'] ?? '') ?>&date_to=<?= urlencode($_GET['date_to'] ?? '') ?>"><?= $p ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>
