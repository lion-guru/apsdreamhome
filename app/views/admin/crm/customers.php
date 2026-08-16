<?php $page_title = 'Customers'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-users me-2"></i>Customers</h2>
        <a href="<?= BASE_URL ?>/admin/crm/users/create" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Add Customer</a>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body aps-cp-card-body">
            <?php if (empty($customers)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-users fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No customers yet</h5>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Leads</th><th>Inquiries</th><th>Registered</th></tr></thead>
                        <tbody>
                        <?php foreach ($customers as $c): ?>
                            <tr>
                                <td><?= $c['id'] ?></td>
                                <td><strong><?= htmlspecialchars($c['name'] ?? '') ?></strong></td>
                                <td><?= htmlspecialchars($c['email'] ?? '') ?></td>
                                <td><?= htmlspecialchars($c['phone'] ?? '') ?></td>
                                <td><span class="badge bg-primary"><?= $c['lead_count'] ?></span></td>
                                <td><span class="badge bg-info"><?= $c['inquiry_count'] ?></span></td>
                                <td><?= date('d M Y', strtotime($c['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
