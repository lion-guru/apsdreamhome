<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-file-invoice me-2"></i>Invoices</h1>
        <div>
            <a href="<?= BASE_URL ?>/admin/finance/create-invoice" class="btn btn-primary"><i class="fas fa-plus me-1"></i>New Invoice</a>
            <a href="<?= BASE_URL ?>/admin/finance" class="btn btn-outline-secondary"><i class="fas fa-chart-line me-1"></i>Finance Dashboard</a>
        </div>
    </div>

    <?php
    try {
        $db = \App\Core\Database\Database::getInstance()->getConnection();
        $invoices = $db->query("SELECT i.*, COALESCE((SELECT SUM(amount) FROM invoice_payments WHERE invoice_id = i.id), 0) as paid_amount FROM invoices i ORDER BY i.created_at DESC LIMIT 50")->fetchAll(\PDO::FETCH_ASSOC);
    } catch (\Exception $e) { $invoices = []; }
    ?>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr><th>#</th><th>Invoice No</th><th>Date</th><th>Due Date</th><th>Client</th><th>Amount</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($invoices)): ?>
                            <tr><td colspan="8" class="text-center text-muted py-5">
                                <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                                <h5>No Invoices Yet</h5>
                                <p class="mb-3">Create your first invoice to get started</p>
                                <a href="<?= BASE_URL ?>/admin/finance/create-invoice" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Create Invoice</a>
                            </td></tr>
                        <?php else: ?>
                            <?php foreach ($invoices as $inv): ?>
                                <tr>
                                    <td><?= $inv['id'] ?></td>
                                    <td><strong><?= htmlspecialchars($inv['invoice_number']) ?></strong></td>
                                    <td><?= htmlspecialchars($inv['invoice_date']) ?></td>
                                    <td><?= htmlspecialchars($inv['due_date']) ?></td>
                                    <td><?= htmlspecialchars($inv['client_name']) ?></td>
                                    <td><strong>₹<?= number_format($inv['total_amount'] ?? 0, 2) ?></strong></td>
                                    <td><span class="badge bg-<?= match($inv['status']) { 'paid' => 'success', 'overdue' => 'danger', 'sent' => 'info', 'viewed' => 'warning', 'draft' => 'secondary', 'cancelled' => 'dark', default => 'secondary' } ?>"><?= $inv['status'] ?></span></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/admin/finance/invoices" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i> View</a>
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
