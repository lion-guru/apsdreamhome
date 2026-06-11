<?php
$vendor = $vendor ?? [];
$contracts = $contracts ?? [];
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="<?= BASE_URL ?>/admin/vendors/show/<?= $vendor['id'] ?? 0 ?>" class="text-decoration-none text-muted">
                <i class="fas fa-arrow-left me-2"></i>Back to Vendor
            </a>
            <h1 class="h3 mt-2 mb-0">Contracts & Purchase Orders</h1>
            <p class="text-muted mb-0"><?= htmlspecialchars($vendor['vendor_name'] ?? 'Vendor') ?> (<?= htmlspecialchars($vendor['vendor_type'] ?? '') ?>)</p>
        </div>
    </div>

    <?php if (empty($contracts)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-file-contract fa-3x text-muted mb-3"></i>
            <p class="text-muted">No contracts or purchase orders found for this vendor</p>
            <a href="<?= BASE_URL ?>/admin/vendors/show/<?= $vendor['id'] ?? 0 ?>" class="btn btn-primary">Back to Vendor</a>
        </div>
    </div>
    <?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body aps-cp-card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>PO / Contract #</th>
                            <th>Title</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contracts as $c): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($c['po_number'] ?? $c['contract_number'] ?? '#'.$c['id']) ?></strong></td>
                            <td><?= htmlspecialchars($c['title'] ?? $c['description'] ?? '-') ?></td>
                            <td>₹<?= number_format(floatval($c['total_amount'] ?? $c['amount'] ?? 0), 2) ?></td>
                            <td>
                                <span class="badge bg-<?= ($c['status'] ?? '') === 'completed' ? 'success' : (($c['status'] ?? '') === 'pending' ? 'warning' : (($c['status'] ?? '') === 'cancelled' ? 'danger' : 'info')) ?>">
                                    <?= ucfirst($c['status'] ?? 'draft') ?>
                                </span>
                            </td>
                            <td><?= !empty($c['start_date']) ? date('d M Y', strtotime($c['start_date'])) : '-' ?></td>
                            <td><?= !empty($c['end_date']) ? date('d M Y', strtotime($c['end_date'])) : '-' ?></td>
                            <td><?= isset($c['created_at']) ? date('d M Y', strtotime($c['created_at'])) : '-' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
