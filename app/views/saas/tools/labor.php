<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-users me-2"></i><?= ($page_title ?? 'Labor Management') ?></h4>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addLaborModal"><i class="fas fa-plus me-1"></i>Add Labor</button>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body aps-cp-card-body">
            <?php if (!empty($labor_records ?? [])): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Phone</th>
                            <th>Wage</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($labor_records ?? []) as $labor): ?>
                        <tr>
                            <td><?= htmlspecialchars($labor['name'] ?? '') ?></td>
                            <td><?= htmlspecialchars($labor['role'] ?? '') ?></td>
                            <td><?= htmlspecialchars($labor['phone'] ?? '') ?></td>
                            <td>₹<?= number_format($labor['wage'] ?? 0) ?></td>
                            <td><span class="badge bg-<?= ($labor['status'] ?? 'active') === 'active' ? 'success' : 'danger' ?>"><?= ucfirst($labor['status'] ?? 'active') ?></span></td>
                            <td><a href="#" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-users fa-3x mb-3"></i>
                <p>No labor records yet. Add your first worker to get started.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
