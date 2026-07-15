<?php $associates = $associates ?? []; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">MLM Associates</h4>
    <a href="<?= BASE_URL ?>admin/mlm/users/create" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add Associate</a>
</div>
<div class="card aps-cp-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Sponsor</th>
                        <th>Level</th>
                        <th>Total Downline</th>
                        <th>Earnings</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($associates)): ?>
                        <tr><td colspan="9" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No associates found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($associates as $a): ?>
                            <tr>
                                <td><?= htmlspecialchars($a['name'] ?? $a['full_name'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($a['email'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($a['phone'] ?? $a['mobile'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($a['sponsor_name'] ?? $a['sponsor'] ?? 'N/A') ?></td>
                                <td><span class="badge bg-<?= strtolower($a['level'] ?? 'bronze') === 'platinum' ? 'dark' : (strtolower($a['level'] ?? 'bronze') === 'gold' ? 'warning' : (strtolower($a['level'] ?? 'bronze') === 'silver' ? 'secondary' : 'info')) ?>">
                                    <?= htmlspecialchars(ucfirst($a['level'] ?? 'Bronze')) ?>
                                </span></td>
                                <td><?= $a['total_downline'] ?? $a['downline_count'] ?? 0 ?></td>
                                <td>₹<?= number_format($a['earnings'] ?? $a['total_earnings'] ?? 0, 2) ?></td>
                                <td><?php $s = $a['status'] ?? 'active'; ?>
                                    <span class="badge bg-<?= $s === 'active' ? 'success' : ($s === 'inactive' ? 'secondary' : 'warning') ?>">
                                        <?= ucfirst($s) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= BASE_URL ?>admin/users/<?= $a['user_id'] ?? $a['id'] ?? 0 ?>" class="btn btn-sm btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                                    <a href="<?= BASE_URL ?>admin/users/<?= $a['user_id'] ?? $a['id'] ?? 0 ?>/wallet" class="btn btn-sm btn-outline-success" title="Wallet"><i class="fas fa-wallet"></i></a>
                                    <a href="<?= BASE_URL ?>admin/users/<?= $a['user_id'] ?? $a['id'] ?? 0 ?>/edit" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
