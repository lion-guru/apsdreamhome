<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><i class="fas fa-home me-1"></i>Home</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/employee">Employee</a></li>
            <li class="breadcrumb-item active" aria-current="page">Lead Approvals</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-check-double me-2"></i>Pending Lead Approvals</h4>
        <span class="badge bg-warning fs-6"><?= count($pending_approvals) ?> pending</span>
    </div>
    <?php if (!empty($pending_approvals)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Source</th>
                            <th>Property Interest</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_approvals as $i => $lead): ?>
                        <tr>
                            <td><?= e($i + 1) ?></td>
                            <td><?= htmlspecialchars($lead['name'] ?? '') ?></td>
                            <td><?= htmlspecialchars($lead['phone'] ?? '') ?></td>
                            <td><?= htmlspecialchars($lead['email'] ?? '-') ?></td>
                            <td><span class="badge bg-info"><?= htmlspecialchars($lead['source'] ?? 'Direct') ?></span></td>
                            <td><?= htmlspecialchars($lead['property_interest'] ?? '-') ?></td>
                            <td class="small"><?= date('d M Y', strtotime($lead['created_at'] ?? 'now')) ?></td>
                            <td>
                                <button class="btn btn-sm btn-success me-1" onclick="alert('Approve lead #<?= e($lead['id']) ?>')"><i class="fas fa-check"></i></button>
                                <button class="btn btn-sm btn-danger" onclick="alert('Reject lead #<?= e($lead['id']) ?>')"><i class="fas fa-times"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No pending approvals</h5>
            <p class="text-muted mb-0">All leads have been processed. Check back later.</p>
        </div>
    </div>
    <?php endif; ?>
</div>
