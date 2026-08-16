<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-cubes me-2"></i>Material Prices</h1>
        <a href="<?= BASE_URL ?>/admin/directory" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr><th>ID</th><th>Material</th><th>Category</th><th>Brand</th><th>Price</th><th>Unit</th><th>Supplier</th><th>Date</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($materials)): ?>
                            <tr><td colspan="10" class="text-center text-muted py-4">No material entries.</td></tr>
                        <?php else: ?>
                            <?php foreach ($materials as $m): ?>
                                <tr>
                                    <td><?= $m['id'] ?></td>
                                    <td><strong><?= htmlspecialchars($m['material_name'] ?? '') ?></strong></td>
                                    <td><?= htmlspecialchars($m['category'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($m['brand'] ?? '') ?></td>
                                    <td><strong>₹<?= number_format($m['price'], 2) ?></strong></td>
                                    <td><?= htmlspecialchars($m['unit'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($m['supplier_name'] ?? '') ?></td>
                                    <td><?= $m['price_date'] ? date('d M Y', strtotime($m['price_date'])) : '-' ?></td>
                                    <td><span class="badge bg-<?= $m['status'] === 'active' ? 'success' : 'secondary' ?>"><?= $m['status'] ?></span></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/admin/directory/delete-material/<?= $m['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a>
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
