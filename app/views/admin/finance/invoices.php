<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-file-invoice me-2"></i>All Invoices</h1>
        <div>
            <a href="<?= BASE_URL ?>/admin/finance/create-invoice" class="btn btn-primary"><i class="fas fa-plus me-1"></i>New Invoice</a>
            <a href="<?= BASE_URL ?>/admin/finance" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
        </div>
    </div>

    <?php
    try {
        $db = \App\Core\Database\Database::getInstance()->getConnection();
        $where = []; $params = [];
        if (!empty($_GET['status'])) { $where[] = "status = ?"; $params[] = $_GET['status']; }
        if (!empty($_GET['search'])) { $where[] = "(invoice_number LIKE ? OR client_name LIKE ?)"; $s = '%'.$_GET['search'].'%'; $params[] = $s; $params[] = $s; }
        $wc = $where ? 'WHERE '.implode(' AND ', $where) : '';
        $invoices = $db->prepare("SELECT i.*, COALESCE((SELECT SUM(amount) FROM invoice_payments WHERE invoice_id = i.id), 0) as paid_amount FROM invoices i {$wc} ORDER BY i.created_at DESC LIMIT 100");
        $invoices->execute($params);
        $invoices = $invoices->fetchAll(\PDO::FETCH_ASSOC);

        $counts = $db->query("SELECT status, COUNT(*) as cnt FROM invoices GROUP BY status")->fetchAll(\PDO::FETCH_KEY_PAIR);
        $totalAmount = $db->query("SELECT COALESCE(SUM(total_amount), 0) as t FROM invoices WHERE status NOT IN ('cancelled','draft')")->fetch();
        $totalOutstanding = $db->query("SELECT COALESCE(SUM(total_amount), 0) as t FROM invoices WHERE status IN ('sent','viewed','overdue')")->fetch();
    } catch (\Exception $e) {
        $invoices = []; $counts = []; $totalAmount = ['t' => 0]; $totalOutstanding = ['t' => 0];
    }
    ?>

    <div class="row mb-4">
        <div class="col-md-3"><div class="card bg-primary text-white"><div class="card-body"><h6>Total</h6><h3 class="mb-0"><?= $counts['draft'] ?? 0 + $counts['sent'] ?? 0 + $counts['viewed'] ?? 0 + $counts['paid'] ?? 0 + $counts['overdue'] ?? 0 ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-success text-white"><div class="card-body"><h6>Total Amount</h6><h3 class="mb-0">₹<?= number_format($totalAmount['t'] ?? 0, 2) ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-warning text-dark"><div class="card-body"><h6>Outstanding</h6><h3 class="mb-0">₹<?= number_format($totalOutstanding['t'] ?? 0, 2) ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-info text-white"><div class="card-body"><h6>Paid</h6><h3 class="mb-0"><?= $counts['paid'] ?? 0 ?></h3></div></div></div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr><th>#</th><th>Invoice No</th><th>Date</th><th>Client</th><th>Amount</th><th>Paid</th><th>Due</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($invoices)): ?>
                            <tr><td colspan="9" class="text-center text-muted py-4">No invoices yet. <a href="<?= BASE_URL ?>/admin/finance/create-invoice">Create your first invoice</a></td></tr>
                        <?php else: ?>
                            <?php foreach ($invoices as $inv): $paid = floatval($inv['paid_amount'] ?? 0); $due = floatval($inv['total_amount'] ?? 0) - $paid; ?>
                                <tr>
                                    <td><?= $inv['id'] ?></td>
                                    <td><strong><?= htmlspecialchars($inv['invoice_number']) ?></strong></td>
                                    <td><?= htmlspecialchars($inv['invoice_date']) ?></td>
                                    <td><?= htmlspecialchars($inv['client_name']) ?></td>
                                    <td>₹<?= number_format($inv['total_amount'] ?? 0, 2) ?></td>
                                    <td>₹<?= number_format($paid, 2) ?></td>
                                    <td>₹<?= number_format($due, 2) ?></td>
                                    <td><span class="badge bg-<?= match($inv['status']) { 'paid' => 'success', 'overdue' => 'danger', 'sent' => 'info', 'viewed' => 'warning', 'cancelled' => 'secondary', default => 'secondary' } ?>"><?= $inv['status'] ?></span></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/admin/invoices/view/<?= $inv['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                                        <a href="<?= BASE_URL ?>/admin/invoices/download/<?= $inv['id'] ?>" class="btn btn-sm btn-outline-success"><i class="fas fa-download"></i></a>
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
