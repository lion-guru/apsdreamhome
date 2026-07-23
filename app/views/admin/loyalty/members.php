<?php
$pageTitle = $pageTitle ?? 'Loyalty Members';
$base = $base ?? (defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/'));
$members = $members ?? [];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-users me-2 text-primary"></i>Loyalty Members</h1>
        <a href="<?= $base ?>/admin/loyalty" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="card shadow">
        <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">All Members</h6></div>
        <div class="card-body aps-cp-card-body">
            <?php if (empty($members)): ?>
                <p class="text-muted text-center py-4"><i class="fas fa-users fa-2x d-block mb-2"></i>No loyalty members found.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Points</th>
                                <th>Tier</th>
                                <th>Join Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($members as $m): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($m['name'] ?? '') ?></strong></td>
                                <td><?= htmlspecialchars($m['email'] ?? '') ?></td>
                                <td><span class="badge bg-warning text-dark"><?= number_format(intval($m['points'] ?? 0)) ?> pts</span></td>
                                <td><?php $tier = $m['tier'] ?? ''; ?>
                                    <span class="badge bg-<?= $tier === 'diamond' ? 'dark' : ($tier === 'platinum' ? 'primary' : ($tier === 'gold' ? 'warning' : ($tier === 'silver' ? 'secondary' : 'light text-dark'))) ?>"><?= ucfirst($tier) ?: 'Bronze' ?></span>
                                </td>
                                <td><?= htmlspecialchars($m['join_date'] ?? $m['created_at'] ?? '') ?></td>
                                <td><?php $s = $m['status'] ?? 'active'; ?>
                                    <span class="badge bg-<?= $s === 'active' ? 'success' : 'danger' ?>"><?= ucfirst($s) ?></span>
                                </td>
                                <td>
                                    <a href="<?= $base ?>/admin/loyalty/members/<?= $m['id'] ?? 0 ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
