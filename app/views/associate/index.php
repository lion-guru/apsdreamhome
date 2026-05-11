<?php $pageTitle = 'Associates'; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><i class="fas fa-home me-1"></i>Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Associates</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-handshake me-2"></i>Associates</h4>
        <a href="<?= BASE_URL ?>/associate/create" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Add Associate</a>
    </div>
    <?php if (!empty($associates)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Properties</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($associates as $i => $a): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><a href="<?= BASE_URL ?>/associate/show/<?= $a['id'] ?>"><?= htmlspecialchars($a['name'] ?? '') ?></a></td>
                            <td><?= htmlspecialchars($a['email'] ?? '') ?></td>
                            <td><?= htmlspecialchars($a['phone'] ?? '') ?></td>
                            <td><?= $a['property_count'] ?? 0 ?></td>
                            <td><span class="badge bg-<?= ($a['status'] ?? 'active') === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($a['status'] ?? 'active') ?></span></td>
                            <td class="small"><?= htmlspecialchars($a['created_at'] ?? '') ?></td>
                            <td>
                                <a href="<?= BASE_URL ?>/associate/show/<?= $a['id'] ?>" class="btn btn-sm btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                                <a href="<?= BASE_URL ?>/associate/edit/<?= $a['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
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
            <i class="fas fa-user-friends fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No Associates Yet</h5>
            <p class="text-muted mb-3">Start by adding your first associate.</p>
            <a href="<?= BASE_URL ?>/associate/create" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Add Associate</a>
        </div>
    </div>
    <?php endif; ?>
</div>
