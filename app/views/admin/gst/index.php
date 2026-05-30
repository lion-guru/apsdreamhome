<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-file-invoice me-2"></i>GST Invoices</h1>
        <a href="<?= BASE_URL ?>/admin/gst/create" class="btn btn-primary"><i class="fas fa-plus me-1"></i>New GST Invoice</a>
    </div>
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Invoice #</th>
                            <th>Client</th>
                            <th>GSTIN</th>
                            <th>Amount</th>
                            <th>GST Type</th>
                            <th>GST Rate</th>
                            <th>E-Invoice</th>
                            <th>E-Way Bill</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($invoices ?? [])): ?>
                            <tr><td colspan="10" class="text-center text-muted py-5">
                                <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                                <h5>No GST Invoices</h5>
                                <p class="mb-3">Create your first GST invoice.</p>
                                <a href="<?= BASE_URL ?>/admin/gst/create" class="btn btn-primary"><i class="fas fa-plus me-1"></i>New GST Invoice</a>
                            </td></tr>
                        <?php else: ?>
                            <?php foreach ($invoices as $inv): ?>
                                <tr>
                                    <td><?= $inv['id'] ?? '' ?></td>
                                    <td><strong><?= htmlspecialchars($inv['invoice_number'] ?? '') ?></strong></td>
                                    <td><?= htmlspecialchars($inv['client_name'] ?? $inv['user_name'] ?? '') ?></td>
                                    <td><code><?= htmlspecialchars($inv['gstin'] ?? '—') ?></code></td>
                                    <td>₹<?= number_format($inv['total_amount'] ?? 0, 2) ?></td>
                                    <td><span class="badge bg-info"><?= strtoupper(str_replace('_', '/', $inv['gst_type'] ?? '—')) ?></span></td>
                                    <td><?= ($inv['gst_rate'] ?? '—') ?>%</td>
                                    <td><?= htmlspecialchars($inv['e_invoice_number'] ?? '—') ?></td>
                                    <td><?= htmlspecialchars($inv['e_way_bill'] ?? '—') ?></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/admin/gst/show/<?= $inv['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
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
