ï»¿<?php
$certs = $certs ?? [];
$csrf = $_SESSION['csrf_token'] ?? '';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-certificate me-2 text-success"></i>Green Certifications</h2>
    <a href="<?= BASE_URL ?>/admin/sustainable/certification/form" class="btn btn-success"><i class="fas fa-plus me-1"></i> Add Certification</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <?php if (empty($certs)): ?>
            <p class="text-muted text-center py-4">No certifications yet.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>Name</th><th>Code</th><th>Authority</th><th>Level</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                    <tbody>
                    <?php foreach ($certs as $c): ?>
                        <tr>
                            <td><i class="fas <?= htmlspecialchars($c['icon'] ?? 'fa-leaf') ?> me-2 style-85168"></i><strong><?= htmlspecialchars($c['name'] ?? '') ?></strong></td>
                            <td><?= htmlspecialchars($c['code'] ?? '') ?></td>
                            <td><?= htmlspecialchars($c['authority'] ?? '') ?></td>
                            <td><?= htmlspecialchars($c['level'] ?? '') ?></td>
                            <td><span class="badge bg-<?= ($c['is_active'] ?? 1) ? 'success' : 'secondary' ?>"><?= ($c['is_active'] ?? 1) ? 'Active' : 'Inactive' ?></span></td>
                            <td class="text-end">
                                <a href="<?= BASE_URL ?>/admin/sustainable/certification/form/<?= $c['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i></a>
                                <form method="POST" action="<?= BASE_URL ?>/admin/sustainable/certification/delete/<?= $c['id'] ?>" class="d-inline" data-aps-confirm="Delete this certification?">
                                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                    <button class="btn btn-sm btn-outline-danger" aria-label="Delete"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
