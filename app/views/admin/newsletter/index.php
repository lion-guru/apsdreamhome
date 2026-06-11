<?php
$subscribers = $subscribers ?? [];
$page_title = $page_title ?? 'Newsletter Subscribers';
?>
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fas fa-envelope-open-text me-2"></i>Newsletter Subscribers</h4>
                <span class="badge bg-primary rounded-pill fs-6"><?= count($subscribers) ?> Total</span>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <?php if (empty($subscribers)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-4x d-block mb-3"></i>
                            <h5>No subscribers yet</h5>
                            <p>Newsletter signups will appear here.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Email</th>
                                        <th>Name</th>
                                        <th>Subscribed</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i = 1; foreach ($subscribers as $s): ?>
                                    <tr>
                                        <td><?= $i++ ?></td>
                                        <td><?= htmlspecialchars($s['email'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($s['name'] ?? '-') ?></td>
                                        <td><?= isset($s['subscribed_at']) ? date('d M Y', strtotime($s['subscribed_at'])) : '-' ?></td>
                                        <td>
                                            <span class="badge bg-<?= ($s['is_active'] ?? 1) ? 'success' : 'secondary' ?>">
                                                <?= ($s['is_active'] ?? 1) ? 'Active' : 'Inactive' ?>
                                            </span>
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
    </div>
</div>
