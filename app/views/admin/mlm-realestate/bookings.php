<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-file-invoice me-2"></i>Bookings</h1>
        <a href="<?= BASE_URL ?>/admin/mlm-realestate/bookings/create" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>New Booking</a>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>ID</th><th>Customer</th><th>Agent</th><th>Total</th><th>Paid</th><th>Mode</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td>#<?= $b['id'] ?></td>
                            <td><?= htmlspecialchars($b['customer_name'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($b['agent_name'] ?? 'N/A') ?></td>
                            <td>₹<?= number_format((float)($b['total_amount'] ?? 0), 2) ?></td>
                            <td>₹<?= number_format((float)($b['amount'] ?? 0), 2) ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($b['payment_mode'] ?? ($b['payment_status'] ?? 'N/A')) ?></span></td>
                            <td><span class="badge bg-<?= $b['status'] === 'completed' ? 'success' : ($b['status'] === 'cancelled' ? 'danger' : ($b['status'] === 'confirmed' ? 'primary' : 'warning')) ?>"><?= htmlspecialchars($b['status']) ?></span></td>
                            <td><?= htmlspecialchars($b['created_at'] ?? '') ?></td>
                            <td><a href="<?= BASE_URL ?>/admin/mlm-realestate/bookings/<?= $b['id'] ?>" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>