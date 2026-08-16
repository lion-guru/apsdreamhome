<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-users text-primary me-2"></i>Associates</h4>
        <a href="<?= BASE_URL ?>/admin/business/associates/create" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>New Associate
        </a>
    </div>
    <?php if (!empty($success)): ?><div class="alert alert-success"><?= htmlspecialchars($success ?? '') ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="alert alert-danger"><?= htmlspecialchars($error ?? '') ?></div><?php endif; ?>
    <div class="aps-cp-card">
        <div class="aps-cp-card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($associates)): ?>
                            <?php foreach ($associates as $i => $a): ?>
                                <tr>
                                    <td><?= $a['id'] ?? $i + 1 ?></td>
                                    <td><a href="<?= BASE_URL ?>/admin/business/associates/show/<?= $a['id'] ?>"><?= htmlspecialchars($a['name'] ?? '-') ?></a></td>
                                    <td><?= htmlspecialchars($a['email'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($a['phone'] ?? '-') ?></td>
                                    <td><span class="badge bg-<?= ($a['status'] ?? '') === 'active' ? 'success' : 'secondary' ?>"><?= $a['status'] ?? 'unknown' ?></span></td>
                                    <td><?= date('d M Y', strtotime($a['created_at'] ?? 'now')) ?></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/admin/business/associates/edit/<?= $a['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">No associates found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php if (!empty($pagination) && $pagination['last_page'] > 1): ?>
        <nav class="mt-3"><ul class="pagination pagination-sm justify-content-center">
            <?php for ($p = 1; $p <= $pagination['last_page']; $p++): ?>
                <li class="page-item <?= $p === $pagination['current_page'] ? 'active' : '' ?>">
                    <a class="page-link" href="<?= BASE_URL ?>/admin/business/associates?page=<?= $p ?>"><?= $p ?></a>
                </li>
            <?php endfor; ?>
        </ul></nav>
    <?php endif; ?>
</div>
